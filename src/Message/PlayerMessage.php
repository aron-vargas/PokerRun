<?php

// src/Message/PlayerMessage.php
namespace App\Message;

use App\Entity\User;
use App\Entity\PlayerLocation;
use App\Entity\CardStop;
use App\DataFixtures\PlayerAction;

class PlayerMessage
{
    private User $Player;
    private ?PlayerLocation $Location;

    private ?string $action;

    public function __construct(User $Player, ?PlayerLocation $Location, string $action)
    {
        $this->Player = $Player;
        $this->Location = $Location;
        $this->action = $action;
    }

    public function getPlayer(): User
    {
        return $this->Player;
    }
    
    public function getLocation(): PlayerLocation
    {
        return $this->Location;
    }

    public function getAction(): ?PlayerAction
    {
        return $this->action;
    }
}