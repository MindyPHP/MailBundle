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

use Mindy\Bundle\MailBundle\Helper\Mail;
use Mindy\Template\Finder\StaticTemplateFinder;
use Mindy\Template\LoaderMode;
use Mindy\Template\TemplateEngine;
use PHPUnit\Framework\TestCase;

class MailHelperTest extends TestCase
{
    /**
     * @var Mail
     */
    protected $mail;
    /**
     * @var \Swift_Mailer
     */
    protected $mailer;

    public function setUp()
    {
        $this->mailer = new \Swift_Mailer(new \Swift_NullTransport());
        $templateFinder = new StaticTemplateFinder([
            'mail.html' => 'html {{ foo }}',
            'mail.txt' => 'text {{ foo }}',
            'html_only.html' => 'html_only {{ foo }}',
            'text_only.txt' => 'text_only {{ foo }}',
        ]);
        $templateEngine = new TemplateEngine(
            $templateFinder,
            __DIR__.'/var/mail',
            LoaderMode::RECOMPILE_ALWAYS
        );

        $this->mail = new Mail($this->mailer, $templateEngine);
    }

    public function testBasicMessage()
    {
        $message = $this->mail->createMessage('test', 'test@user.com', 'mail', []);

        $this->assertSame('test', $message->getSubject());
        $this->assertSame(['test@user.com' => null], $message->getTo());
    }

    public function testCreateComplexMessage()
    {
        $message = $this->mail->createMessage('test', 'test@user.com', 'mail', ['foo' => 'bar']);

        $this->assertSame('html bar', $message->getBody());

        $this->assertCount(1, $message->getChildren());

        $txtPart = current($message->getChildren());
        /* @var \Swift_MimePart $txtPart */
        $this->assertSame('text bar', $txtPart->getBody());
    }

    public function testCreateHtmlMessage()
    {
        $message = $this->mail->createMessage('test', 'test@user.com', 'html_only', ['foo' => 'bar']);
        $this->assertSame('html_only bar', $message->getBody());
    }

    public function testCreatePlainTextMessage()
    {
        $message = $this->mail->createMessage('test', 'test@user.com', 'text_only', ['foo' => 'bar']);
        $this->assertSame('text_only bar', $message->getBody());
    }

    public function testSend()
    {
        $message = $this->mail->createMessage('test', 'test@user.com', 'text_only', ['foo' => 'bar']);
        $this->assertSame(1, $this->mailer->send($message));
        $this->assertSame(1, $this->mail->send('test', 'test@user.com', 'text_only'));
    }

    public function testAttachments()
    {
        $message = $this->mail->createMessage('test', 'test@user.com', 'text_only', ['foo' => 'bar']);
        $message = $this->mail->attach($message, [__FILE__]);
        $message = $this->mail->attach($message, [__FILE__ => ['contentType' => 'text/plain', 'fileName' => 'text.txt']]);
        $this->assertCount(2, $message->getChildren());
    }
}
