<?php

declare(strict_types=1);

/*
 * This file is part of Mindy Framework.
 * (c) 2017 Maxim Falaleev
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mindy\Bundle\MailBundle\Helper;

use Mindy\Template\TemplateEngineInterface;
use RuntimeException;
use Swift_Attachment;
use Swift_Message;

class Mail
{
    /**
     * @var \Swift_Mailer
     */
    protected $mailer;
    /**
     * @var TemplateEngineInterface
     */
    protected $templateEngine;

    /**
     * Mail constructor.
     *
     * @param \Swift_Mailer           $mailer
     * @param TemplateEngineInterface $templateEngine
     */
    public function __construct(\Swift_Mailer $mailer, TemplateEngineInterface $templateEngine)
    {
        $this->mailer = $mailer;
        $this->templateEngine = $templateEngine;
    }

    /**
     * @param string $subject
     * @param string $to
     * @param string $template
     * @param array  $data
     *
     * @throws \Exception
     *
     * @return \Swift_Message
     */
    public function createMessage(string $subject, string $to, string $template, array $data = [])
    {
        $message = new Swift_Message();

        $message->setSubject($subject);
        $message->setTo($to);

        $tpl = pathinfo(basename($template), PATHINFO_FILENAME);

        $htmlBody = null;
        try {
            $htmlBody = $this->templateEngine->render(sprintf('%s.html', $tpl), $data);
            // @codeCoverageIgnoreStart
        } catch (RuntimeException $e) {
            // @codeCoverageIgnoreEnd
        }

        $txtBody = null;
        try {
            $txtBody = $this->templateEngine->render(sprintf('%s.txt', $tpl), $data);
            // @codeCoverageIgnoreStart
        } catch (RuntimeException $e) {
            // @codeCoverageIgnoreEnd
        }

        if ($htmlBody && $txtBody) {
            $message->setBody($htmlBody, 'text/html');
            $message->addPart($txtBody, 'text/plain');
        } else {
            if ($htmlBody) {
                $message->setBody($htmlBody, 'text/html');
            } else {
                $message->setBody($txtBody, 'text/plain');
            }
        }

        return $message;
    }

    /**
     * @param Swift_Message $message
     * @param array         $attachments
     *
     * @return Swift_Message
     */
    public function attach(Swift_Message $message, array $attachments = []): Swift_Message
    {
        foreach ($attachments as $fileName => $options) {
            if (is_string($options)) {
                $fileName = $options;
                $options = [];
            }

            $attachment = Swift_Attachment::fromPath($fileName);
            if (!empty($options['fileName'])) {
                $attachment->setFilename($options['fileName']);
            }
            if (!empty($options['contentType'])) {
                $attachment->setContentType($options['contentType']);
            }
            $message->attach($attachment);
        }

        return $message;
    }

    /**
     * @param $subject
     * @param $to
     * @param $template
     * @param array $data
     * @param array $attachments
     *
     * @throws \Exception
     *
     * @return int
     */
    public function send($subject, $to, $template, array $data = [], array $attachments = []): int
    {
        $message = $this->createMessage($subject, $to, $template, $data);
        $message = $this->attach($message, $attachments);

        return $this->mailer->send($message);
    }
}
