<?php

namespace M12U\Bundle\AmqpBundle\Event;

use stdClass;

/**
 * Interface AMQPMessageEventInterface
 * @package AppBundle\Event
 */
interface AMQPMessageEventInterface
{
    /**
     * @return mixed
     */
    public function getMessage();

    /**
     * @return string queue name
     */
    public function getQueue(): string;
}