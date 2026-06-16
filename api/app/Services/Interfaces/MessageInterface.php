<?php

namespace App\Services\Interfaces;

interface MessageInterface
{
    public function setFrom(string $From);
    public function setTo(array $to);
    public function setMessage(string $message);
    public function setTemplateMessage(array $data);
    public function setTemplate(string $message);
    public function send();
}
