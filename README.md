How run php AMQP client comsumer : 
====

Command
---
````
$ bin/console m12u:amqp-stream:consumer --host=[HOST] --username=[USERNAME] --queue=[QUEUE] --port=[POST] <--vhost=[VHOST]> <--ssl>
````

Events availables
===

| name | Interfaces |
|--------------|--------------------------|
| m12u.amqp.message |  M12U\Bundle\AmqpBundle\Event\AMQPMessageEventInterface |

Create listener
---

First step, create class
````
<?php

namespace AppBundle\Listener;

use stdClass;
use M12U\Bundle\AmqpBundle\Event\AMQPMessageEventInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class AMQPMessageListener
 * @package AppBundle\Listener
 */
class AMQPMessageListener
{
    /**
     * @param AMQPMessageEventInterface $event
     */
    public function onM12uAmqpMessage(AMQPMessageEventInterface $event)
    {
        // exemple ...
        
        $message = $event->getMessage();
        switch ($event->getQueue())
        {
            case '1234567890987654321':
                // todo ...
                // operation on $message
                break;
            case '0987654321234567890':
                // todo ...
                // operation on $message
                break;
        }
    }
}
````

Last step, declare service
````
# app/config/services.yml
services:
    AppBundle\Listener\AMQPMessageListener:
        tags:
            - { name: kernel.event_listener, event: m12u.amqp.message }
````