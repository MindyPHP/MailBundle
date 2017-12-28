<?php

declare(strict_types=1);

/*
 * This file is part of Mindy Framework.
 * (c) 2017 Maxim Falaleev
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mindy\Bundle\MailBundle\Tests;

use Mindy\Bundle\MailBundle\SwiftMailer\Plugins\DefaultSenderPlugin;
use Mindy\Bundle\MailBundle\SwiftMailer\Plugins\DefaultSubjectPrefixPlugin;
use PHPUnit\Framework\TestCase;

class DefaultSenderPluginTest extends TestCase
{
    public function testDefaultFrom()
    {
        $plugin = new DefaultSenderPlugin([
            'example@user.com' => 'example_user',
        ], [
            'info@user.com' => 'real_user',
        ]);
        $message = new \Swift_Message();
        $event = $this->createSendEvent($message);
        $plugin->beforeSendPerformed($event);
        $this->assertSame([
            'example@user.com' => 'example_user',
        ], $event->getMessage()->getFrom());
        $this->assertSame([
            'info@user.com' => 'real_user',
        ], $event->getMessage()->getReplyTo());
        $plugin->sendPerformed($event);
    }

    public function testDefaultSubjectPrefix()
    {
        $plugin = new DefaultSubjectPrefixPlugin('[YO]');
        $message = new \Swift_Message();
        $message->setSubject('Test message');
        $event = $this->createSendEvent($message);
        $plugin->beforeSendPerformed($event);
        $this->assertSame('[YO] Test message', $event->getMessage()->getSubject());
        $plugin->sendPerformed($event);
    }

    /**
     * @param \Swift_Mime_SimpleMessage $message
     *
     * @return \PHPUnit\Framework\MockObject\MockObject|\Swift_Events_SendEvent
     */
    protected function createSendEvent(\Swift_Mime_SimpleMessage $message)
    {
        $event = $this->getMockBuilder(\Swift_Events_SendEvent::class)
            ->disableOriginalConstructor()
            ->getMock();

        $event->method('getMessage')->willReturn($message);

        return $event;
    }
}
