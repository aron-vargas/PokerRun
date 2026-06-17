<?php

// src/Message/PlayerMessage.php
namespace App\Message;

use App\DataFixtures\PlayerAction;

class PlayerMessage
{
    private int $id;
    private string $email;
    private ?string $firstName;
    private ?string $lastName;

    private ?PlayerAction $action;

    public function __construct(int $id, string $email, ?string $firstName = null, ?string $lastName = null, ?PlayerAction $action = null)
    {
        $this->id = $id;
        $this->email = $email;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->action = $action;
    }

    public function getId(): int { return $this->id; }
    public function getEmail(): string { return $this->email; }
    public function getFirstName(): ?string { return $this->firstName; }
    public function getLastName(): ?string { return $this->lastName; }
    public function getAction(): ?PlayerAction { return $this->action; }
}