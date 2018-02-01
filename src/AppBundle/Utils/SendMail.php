<?php

namespace AppBundle\Utils;

use Symfony\Bundle\TwigBundle\TwigEngine;

class SendMail
{
    /**
     * @var \Swift_Mailer
     */
    public $mailer;

    /**
     * @var TwigEngine
     */
    public $templating;

    public function __construct(\Swift_Mailer $mailer, TwigEngine $template)
    {
        $this->mailer = $mailer;
        $this->templating = $template;
    }

    /**
     * Use SwiftMailer to send an email.
     *
     * @param string $subject
     * @param string $sendTo
     * @param array  $content
     * @param string $sendFrom
     * @param string $template
     */
    public function send($subject, $sendTo, $content, $template = 'forgot_password', $sendFrom = 'noreply@atmtaxi.co.uk')
    {
        // Build new SwiftMailer instance
        $message = \Swift_Message::newInstance()
            ->setSubject($subject)
            ->setFrom($sendFrom)
            ->setTo($sendTo)
            ->setBody(
                $this->templating->render(
                    'emails/'.$template.'.html.twig',
                    array('content' => $content)
                ),
                'text/html'
            );

        // Send email
        return $this->mailer->send($message);
    }
}