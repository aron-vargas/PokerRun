<?php
// src/Message/CardStopMessage.php
namespace App\Message;

class CardStopMessage
{
    private string $userId;
    private string $action;

    public function __construct(string $userId, string $action)
    {
        $this->userId = $userId;
        $this->action = $action;
    }

    public function getUserId(): string { return $this->userId; }
    public function getAction(): string { return $this->action; }
}