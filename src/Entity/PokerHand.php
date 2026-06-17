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
    private ?PlayingCard $card_one = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    private ?PlayingCard $card_two = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    private ?PlayingCard $card_three = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    private ?PlayingCard $card_four = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    private ?PlayingCard $card_five = null;

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
        return $this->card_one;
    }

    public function setCardOne(?PlayingCard $card_one): static
    {
        $this->card_one = $card_one;

        return $this;
    }

    public function getCardTwo(): ?PlayingCard
    {
        return $this->card_two;
    }

    public function setCardTwo(?PlayingCard $card_two): static
    {
        $this->card_two = $card_two;

        return $this;
    }

    public function getCardThree(): ?PlayingCard
    {
        return $this->card_three;
    }

    public function setCardThree(?PlayingCard $card_three): static
    {
        $this->card_three = $card_three;

        return $this;
    }

    public function getCardFour(): ?PlayingCard
    {
        return $this->card_four;
    }

    public function setCardFour(?PlayingCard $card_four): static
    {
        $this->card_four = $card_four;

        return $this;
    }

    public function getCardFive(): ?PlayingCard
    {
        return $this->card_five;
    }

    public function setCardFive(?PlayingCard $card_five): static
    {
        $this->card_five = $card_five;

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
