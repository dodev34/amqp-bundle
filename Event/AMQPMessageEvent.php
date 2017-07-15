<?php

namespace M12U\Bundle\AmqpBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use stdClass;

/**
 * Class AMQPMessageEvent
 * @package AppBundle\Event
 */
class AMQPMessageEvent extends Event implements AMQPMessageEventInterface
{
    /**
     * @var string
     */
    const NAME = 'm12u.amqp.message';

    /**
     * @var string
     */
    protected $queue;

    /**
     * @var mixed
     */
    protected $message;

    /**
     * AMQPMessageEvent constructor.
     * @param string $queue
     * @param mixed $message
     */
    public function __construct(string $queue, $message)
    {
        $this->queue = $queue;
        $this->message = $message;
    }

    /**
     * @inheritdoc
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @inheritdoc
     */
    public function getQueue(): string
    {
        return $this->queue;
    }
}