<?php

namespace M12U\Bundle\AmqpBundle\Wire\IO;

use PhpAmqpLib\Wire\IO\StreamIO as BaseStreamIO;

/**
 * Class StreamIO
 * @package M12U\Bundle\AmqpBundle\Wire\IO
 */
class StreamIO extends BaseStreamIO
{
    /** @var resource */
    private $sock;

    /** @var bool */
    private $canSelectNull;

    /** @var bool */
    private $canDispatchPcntlSignal;

    public function __construct(
        $host,
        $port,
        $ssl = false,
        $connection_timeout,
        $read_write_timeout,
        $context = null,
        $keepalive = false,
        $heartbeat = 0)
    {
        $this->protocol = ($ssl) ? 'ssl' : 'tcp';
        if ($heartbeat !== 0 && ($read_write_timeout < ($heartbeat * 2))) {
            throw new \InvalidArgumentException('read_write_timeout must be at least 2x the heartbeat');
        }
        $this->host = $host;
        $this->port = $port;
        $this->connection_timeout = $connection_timeout;
        $this->read_write_timeout = $read_write_timeout;
        $this->context = $context;
        $this->keepalive = $keepalive;
        $this->heartbeat = $heartbeat;
        $this->canSelectNull = true;
        $this->canDispatchPcntlSignal = $this->isPcntlSignalEnabled();

        if (is_null($this->context)) {
            $this->context = stream_context_create();
        } else {
            $this->protocol = 'ssl';
            // php bugs 41631 & 65137 prevent select null from working on ssl streams
            if (PHP_VERSION_ID < 50436) {
                $this->canSelectNull = false;
            }
        }
    }

    /**
     * @return bool
     */
    private function isPcntlSignalEnabled()
    {
        return extension_loaded('pcntl')
            && function_exists('pcntl_signal_dispatch')
            && (defined('AMQP_WITHOUT_SIGNALS') ? !AMQP_WITHOUT_SIGNALS : true);
    }
}