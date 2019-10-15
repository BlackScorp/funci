<?php
declare(strict_types=1);

function sendMail(array $to, string $subject, string $body, array $files = null) {
    static $mailer = null;
  
    if(!defined('MAILER_CONNECTION')){
        $message = sprintf('MAILER_CONNECTION not defined, please check "%s"',CONFIG_DIR.'/mailer.php');
        trigger_error($message,E_USER_ERROR);
    }
    if (!$mailer) {
        $mailer = createMailer(MAILER_CONNECTION);
    }
    $message = (new \Swift_Message())
            ->setSubject($subject)
            ->setFrom(MAILER_CONNECTION['defaultFrom'])
            ->setTo($to)
            ->setBody($body, 'text/html')
            ->addPart(strip_tags(str_replace(['<br>', '<br/>'], "\n", $body)), 'text/plain');
    if ($files) {
        foreach ($files as $file) {
            $message->attach(Swift_Attachment::fromPath($file));
        }
    }
    try {
        return $mailer->send($message);
    } catch (Exception $e) {
        trigger_error($e->getMessage(), E_USER_ERROR);
    }
}

function createMailer($config) {

    $transport = (new \Swift_SmtpTransport($config['host'], $config['port'], $config['ssl']))
            ->setUsername($config['username'])
            ->setPassword($config['password'])
    ;

    return new \Swift_Mailer($transport);
}