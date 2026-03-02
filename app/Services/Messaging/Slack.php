<?php

namespace App\Services\Messaging;

use App\Services\Interfaces\MessageInterface;
use Psr\Log\LoggerInterface;

class Slack implements MessageInterface
{
    private $token;
    private LoggerInterface $logger;

    public function __construct($token, LoggerInterface $logger = null)
    {
        $this->token = $token;
        $this->logger = $logger ?? app(LoggerInterface::class);
        $this->logger->debug('messaging.initialized', [
            'service' => 'slack',
        ]);
    }

    public function setFrom(string $from)
    {
    }
    public function setTo(array $to)
    {
    }
    public function setMessage(string $message)
    {
    }
    public function setTemplateMessage(array $data)
    {
    }
    public function setTemplate(string $message)
    {
    }

    public function send()
    {
        $this->logger->info('messaging.sent', [
            'service' => 'slack',
        ]);
    }
}
