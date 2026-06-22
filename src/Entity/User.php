<?php

namespace App\Entity;

use App\Entity\Role;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    private ?string $email = null;

    #[ORM\Column(length: 180)]
    private ?string $firstName = null;

    #[ORM\Column(length: 180)]
    private ?string $lastName = null;

    #[ORM\Column(type: 'json')]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column]
    private bool $isVerified = false;

    #[ORM\Column]
    private ?bool $active = true;

    #[ORM\Column(length: 1024, nullable: true)]
    private ?string $avatar = null;

    #[ORM\ManyToOne(inversedBy: 'admins')]
    private ?CardStop $cardStop = null;

    #[ORM\OneToOne(mappedBy: 'Player', cascade: ['persist', 'remove'])]
    private ?PokerHand $pokerHand = null;

    /**
     * @var Collection<int, PlayingCard>
     */
    #[ORM\OneToMany(targetEntity: PlayingCard::class, mappedBy: 'player', orphanRemoval: true)]
    private Collection $card_list;

    #[ORM\OneToOne(mappedBy: 'Player', cascade: ['persist', 'remove'])]
    private ?PlayerLocation $location = null;

    public function __construct()
    {
        $this->card_list = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): static
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): static
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;

        // guarantee every user at least has ROLE_USER
        if (empty($roles)) {
            $roles[] = 'ROLE_USER';
        }

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Ensure the session doesn't contain actual password hashes by CRC32C-hashing them, as supported since Symfony 7.3.
     */
    public function __serialize(): array
    {
        $data = (array) $this;
        $data["\0".self::class."\0password"] = hash('crc32c', $this->password);

        return $data;
    }

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): static
    {
        $this->isVerified = $isVerified;

        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(bool $active): static
    {
        $this->active = $active;

        return $this;
    }

    public function getAvatar(): ?string
    {
        if (!$this->avatar) {
            return null;
        }
        if (strpos($this->avatar, '/') !== false) {
            return $this->avatar;
        }

        return "/uploads/avatars/{$this->avatar}";
    }

    public function setAvatar(?string $avatar): static
    {
        $this->avatar = $avatar;

        return $this;
    }

    public function getCardStop(): ?CardStop
    {
        return $this->cardStop;
    }

    public function setCardStop(?CardStop $cardStop): static
    {
        $this->cardStop = $cardStop ?? null;

        return $this;
    }

    public function getPokerHand(): ?PokerHand
    {
        return $this->pokerHand ;
    }

    public function setPokerHand(?PokerHand $pokerHand): static
    {
        // set the owning side of the relation if necessary
        if ($pokerHand && $pokerHand->getPlayer() !== $this) {
            $pokerHand->setPlayer($this);
        }

        $this->pokerHand = $pokerHand ?? null;

        return $this;
    }

    /**
     * @return Collection<int, PlayingCard>
     */
    public function getCardList(): Collection
    {
        return $this->card_list;
    }

    public function addCardList(PlayingCard $cardList): static
    {
        if (!$this->card_list->contains($cardList)) {
            $this->card_list->add($cardList);
            $cardList->setPlayer($this);
        }

        return $this;
    }

    public function removeCardList(PlayingCard $cardList): static
    {
        if ($this->card_list->removeElement($cardList)) {
            // set the owning side to null (unless already changed)
            if ($cardList->getPlayer() === $this) {
                $cardList->setPlayer(null);
            }
        }

        return $this;
    }

    public function getLocation(): ?PlayerLocation
    {
        return $this->location;
    }

    public function setLocation(?PlayerLocation $location): static
    {
        // set the owning side of the relation if necessary
        if ($location->getPlayer() !== $this) {
            $location->setPlayer($this);
        }

        $this->location = $location;

        return $this;
    }
}
