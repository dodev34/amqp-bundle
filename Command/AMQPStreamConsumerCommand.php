<?php

namespace M12U\Bundle\AmqpBundle\Command;

use PhpAmqpLib\Channel\AMQPChannel;
use M12U\Bundle\AmqpBundle\Event\AMQPMessageEvent;
use M12U\Bundle\AmqpBundle\Connection\AMQPStreamConnection;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * Class AMQPStreamConsumerCommand
 * @package M12U\Bundle\AmqpBundle\Command
 */
class AMQPStreamConsumerCommand extends ContainerAwareCommand
{
    /**
     * @var bool
     */
    protected $started = false;

    /**
     * @var AMQPStreamConnection
     */
    protected $connection;

    /**
     * @var AMQPChannel
     */
    protected $chanel;

    /**
     * @var string
     */
    protected $queue;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('m12u:amqp-stream:consumer')
            ->addOption('host', null, InputOption::VALUE_REQUIRED, 'Host', null)
            ->addOption('username', null, InputOption::VALUE_REQUIRED, 'Username', null)
            ->addOption('vhost', null, InputOption::VALUE_OPTIONAL, 'vhost', '/')
            ->addOption('port', null, InputOption::VALUE_REQUIRED, 'port', null)
            ->addOption('ssl', null, InputOption::VALUE_NONE, 'Use SSL protocole')
            ->addOption('queue', null, InputOption::VALUE_REQUIRED, 'Queue name', null)
            ->addOption('locale', null, InputOption::VALUE_OPTIONAL, 'Locale', 'en_US')
            ->addOption('insist', null, InputOption::VALUE_NONE, 'insist')
            ->addOption('keep-alive', null, InputOption::VALUE_NONE, 'keep alive time')
            ->addOption('login-response', null, InputOption::VALUE_OPTIONAL, 'insist', null)
            ->addOption('login-method', null, InputOption::VALUE_OPTIONAL, 'Login method', 'AMQPLAIN')
            ->addOption('connection-timeout', null, InputOption::VALUE_OPTIONAL, 'Connection timeout', 3.0)
            ->addOption('read-write-timeout', null, InputOption::VALUE_OPTIONAL, 'Read write timeout', 3.0)
            ->addOption('heartbeat', null, InputOption::VALUE_OPTIONAL, 'heartbeat', 0)
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->queue  = $input->getOption('queue');
        $host = $input->getOption('host');
        $username = $input->getOption('username');
        $vhost = $input->getOption('vhost');
        $port = (int)$input->getOption('port');
        $locale = (string)$input->getOption('locale');
        $insist = (bool)$input->getOption('insist');
        $ssl = (bool)$input->getOption('ssl');
        $loginResponse = $input->getOption('login-response');
        $loginMethod = $input->getOption('login-method');
        $connectionTimeout = (float)$input->getOption('connection-timeout');
        $readWriteTimeout = (float)$input->getOption('read-write-timeout');
        $keepAlive = (bool)$input->getOption('keep-alive');
        $heartbeat = (int)$input->getOption('heartbeat');

        $question = new Question('<question>What is the AMQP password?</question>');
        $question->setHidden(true);
        $question->setHiddenFallback(false);
        $password = (string)$this->getHelper('question')->ask($input, $output, $question);
        if (empty($password)) {
            throw new \InvalidArgumentException("You must specified a password");
        }

        $this->connection = new AMQPStreamConnection(
            $host,
            $port,
            $username,
            $password,
            $vhost,
            $ssl,
            $insist,
            $loginMethod,
            $loginResponse,
            $locale,
            $connectionTimeout,
            $readWriteTimeout,
            null,
            $keepAlive,
            $heartbeat
        );
        $this->channel = $this->connection->channel();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var EventDispatcher $dispatcher */
        $dispatcher = $this->getContainer()->get('event_dispatcher');
        $queue = $this->queue;
        $this->channel->basic_consume($queue, '', false, true, false, false, function($msg) use($input, $output, $dispatcher, $queue) {
            // content message
            $message = $msg->body;
            // dispatch event
            $dispatcher->dispatch(AMQPMessageEvent::NAME, new AMQPMessageEvent($queue, $message));
            // output event
            $output->writeln(sprintf('>> <info>%s</info>', $msg->body));
        });

        // run AMQP server consumer
        $this->runConsumer($input, $output);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function runConsumer(InputInterface $input, OutputInterface $output)
    {
        while(count($this->channel->callbacks)) {
            if (false == $this->started) {
                $output->writeln('Server callbacks >> <info>wait</info>');
                $this->started = true;
            }
            $this->channel->wait();
        }
    }
}
