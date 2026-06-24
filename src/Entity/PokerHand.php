<?php

namespace App\Entity;

use App\Repository\PokerHandRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PokerHandRepository::class)]
class PokerHand
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'pokerHand', cascade: ['persist', 'remove'])]
    private ?PlayingCard $cardOne = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    private ?PlayingCard $cardTwo = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    private ?PlayingCard $cardThree = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    private ?PlayingCard $cardFour = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    private ?PlayingCard $cardFive = null;

    #[ORM\OneToOne(inversedBy: 'pokerHand', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $Player = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getCardOne(): ?PlayingCard
    {
        return $this->cardOne;
    }

    public function setCardOne(?PlayingCard $cardOne): static
    {
        $this->cardOne = $cardOne;

        return $this;
    }

    public function getCardTwo(): ?PlayingCard
    {
        return $this->cardTwo;
    }

    public function setCardTwo(?PlayingCard $cardTwo): static
    {
        $this->cardTwo = $cardTwo;

        return $this;
    }

    public function getCardThree(): ?PlayingCard
    {
        return $this->cardThree;
    }

    public function setCardThree(?PlayingCard $cardThree): static
    {
        $this->cardThree = $cardThree;

        return $this;
    }

    public function getCardFour(): ?PlayingCard
    {
        return $this->cardFour;
    }

    public function setCardFour(?PlayingCard $cardFour): static
    {
        $this->cardFour = $cardFour;

        return $this;
    }

    public function getCardFive(): ?PlayingCard
    {
        return $this->cardFive;
    }

    public function setCardFive(?PlayingCard $cardFive): static
    {
        $this->cardFive = $cardFive;

        return $this;
    }

    public function getPlayer(): ?User
    {
        return $this->Player;
    }

    public function setPlayer(User $Player): static
    {
        $this->Player = $Player;

        return $this;
    }
}
