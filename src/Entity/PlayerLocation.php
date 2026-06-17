<?php

namespace App\Entity;

use App\Repository\PlayerLocationRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PlayerLocationRepository::class)]
class PlayerLocation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'location', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $Player = null;

    #[ORM\ManyToOne]
    private ?CardStop $CardStop = null;

    #[ORM\Column]
    private ?bool $isVerified = null;

    #[ORM\OneToOne(inversedBy: 'location', cascade: ['persist', 'remove'])]
    private ?PlayingCard $first_card = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    private ?PlayingCard $extra_card = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $checkin_time = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $verified_on = null;

    #[ORM\ManyToOne]
    private ?User $verified_by = null;

    public function reset(): PlayerLocation
    {
        $this->CardStop = null;
        $this->isVerified = false;
        $this->first_card = null;
        $this->extra_card = null;
        $this->checkin_time = null;
        $this->verified_on = null;
        $this->verified_by = null;
        $this->id = null;

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;

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

    public function getCardStop(): ?CardStop
    {
        return $this->CardStop;
    }

    public function setCardStop(?CardStop $CardStop): static
    {
        $this->CardStop = $CardStop;

        return $this;
    }

    public function isVerified(): ?bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): static
    {
        $this->isVerified = $isVerified;

        return $this;
    }

    public function getFirstCard(): ?PlayingCard
    {
        return $this->first_card;
    }

    public function setFirstCard(?PlayingCard $first_card): static
    {
        $this->first_card = $first_card;

        return $this;
    }

    public function getExtraCard(): ?PlayingCard
    {
        return $this->extra_card;
    }

    public function setExtraCard(?PlayingCard $extra_card): static
    {
        $this->extra_card = $extra_card;

        return $this;
    }

    public function getCheckinTime(): ?\DateTime
    {
        return $this->checkin_time;
    }

    public function setCheckinTime(?\DateTime $checkin_time): static
    {
        $this->checkin_time = $checkin_time;

        return $this;
    }

    public function getVerifiedOn(): ?\DateTime
    {
        return $this->verified_on;
    }

    public function setVerifiedOn(?\DateTime $verified_on): static
    {
        $this->verified_on = $verified_on;

        return $this;
    }

    public function getVerifiedBy(): ?User
    {
        return $this->verified_by;
    }

    public function setVerifiedBy(?User $verified_by): static
    {
        $this->verified_by = $verified_by;

        return $this;
    }
}
