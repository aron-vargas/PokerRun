<?php

namespace App\Entity;

use App\Enum\CardSuit;
use App\Enum\CardNumber;
use App\Repository\PlayingCardRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PlayingCardRepository::class)]
class PlayingCard
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(enumType: CardNumber::class)]
    private ?CardNumber $cardNumber = null;

    #[ORM\Column(enumType: CardSuit::class)]
    private ?CardSuit $cardSuit = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $image = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    private ?PokerHand $pokerHand = null;

    #[ORM\ManyToOne(inversedBy: 'cardList')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $player = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    private ?PlayerLocation $location = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getCardNumber(): ?CardNumber
    {
        return $this->cardNumber;
    }

    public function setCardNumber(CardNumber $cardNumber): static
    {
        $this->cardNumber = $cardNumber;

        return $this;
    }

    public function getCardSuit(): ?CardSuit
    {
        return $this->cardSuit;
    }

    public function setCardSuit(CardSuit $cardSuit): static
    {
        $this->cardSuit = $cardSuit;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): static
    {
        $this->image = $image;

        return $this;
    }

    public function getPokerHand(): ?PokerHand
    {
        return $this->pokerHand;
    }

    public function setPokerHand(?PokerHand $pokerHand): static
    {
        // unset the owning side of the relation if necessary
        if ($pokerHand === null && $this->pokerHand !== null) {
            $this->pokerHand->setCardOne(null);
        }

        // set the owning side of the relation if necessary
        if ($pokerHand !== null && $pokerHand->getCardOne() !== $this) {
            $pokerHand->setCardOne($this);
        }

        $this->pokerHand = $pokerHand;

        return $this;
    }

    public function getPlayer(): ?User
    {
        return $this->player;
    }

    public function setPlayer(?User $player): static
    {
        $this->player = $player;

        return $this;
    }

    public function getLocation(): ?PlayerLocation
    {
        return $this->location;
    }
    public function getLocationName(): ?string
    {
        return $this->location?->getCardStop()?->getCardStopName();
    }
    public function getLocationLogo(): ?string
    {
        return $this->location?->getCardStop()?->getLogo();
    }

    public function setLocation(?PlayerLocation $location): static
    {
        // unset the owning side of the relation if necessary
        if ($location === null && $this->location !== null) {
            $this->location->setFirstCard(null);
        }

        // set the owning side of the relation if necessary
        if ($location !== null && $location->getFirstCard() !== $this) {
            $location->setFirstCard($this);
        }

        $this->location = $location;

        return $this;
    }

    public function __toString(): string
    {
        return (ucfirst($this->cardNumber?->name) . ' of ' . ucfirst($this->cardSuit?->name)) ?? '';
    }
}
