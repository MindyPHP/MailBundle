<?php

declare(strict_types=1);

/*
 * Studio 107 (c) 2017 Maxim Falaleev
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mindy\Bundle\MailBundle\SwiftMailer\Plugins;

use Swift_Events_SendEvent;
use Swift_Events_SendListener;

/*
 * This file is part of SwiftMailer.
 * (c) 2009 Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Sets the sender of a message if none is set
 *
 * @author     Adam Zielinski
 */
class DefaultSenderPlugin implements Swift_Events_SendListener
{
    /**
     * Email and name of sender to use in all messages that are sent
     * without any sender information.
     *
     * @var array
     */
    private $defaultFrom = [];

    /**
     * @var array
     */
    private $defaultReplyTo = [];

    /**
     * List if IDs of messages which got sender information set
     * by this plugin.
     *
     * @var string[]
     */
    private $handledMessageIds = [];

    /**
     * Create a new DefaultSenderPlugin to use a $defaultSenderEmail and $defaultSenderName
     * for all messages that are sent without any sender information.
     *
     * @param array $defaultFrom
     * @param array $defaultReplyTo
     */
    public function __construct(array $defaultFrom, array $defaultReplyTo = [])
    {
        $this->defaultFrom = $defaultFrom;
        $this->defaultReplyTo = $defaultReplyTo;
    }

    /**
     * Invoked immediately before the Message is sent.
     *
     * @param Swift_Events_SendEvent $evt
     */
    public function beforeSendPerformed(Swift_Events_SendEvent $evt)
    {
        $message = $evt->getMessage();

        // replace sender
        if (0 === count($message->getFrom())) {
            $message->setFrom($this->defaultFrom);
            $this->handledMessageIds[$message->getId()] = true;
        }

        // replace sender
        if (empty($message->getReplyTo())) {
            $message->setReplyTo($this->defaultReplyTo);
            $this->handledMessageIds[$message->getId()] = true;
        }
    }

    /**
     * Invoked immediately after the Message is sent.
     *
     * @param Swift_Events_SendEvent $evt
     */
    public function sendPerformed(Swift_Events_SendEvent $evt)
    {
        $message = $evt->getMessage();

        // restore original headers
        $id = $message->getId();
        if (array_key_exists($id, $this->handledMessageIds)) {
            $message->setFrom(null);
            unset($this->handledMessageIds[$id]);
        }
    }
}
