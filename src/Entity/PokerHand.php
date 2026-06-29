<?php

namespace App\Entity;

use App\Enum\CardNumber;
use App\Enum\CardSuit;
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

    public function getBestHandLabel(): string
    {
        $cards = array_filter([
            $this->getCardOne(),
            $this->getCardTwo(),
            $this->getCardThree(),
            $this->getCardFour(),
            $this->getCardFive(),
        ]);
        $detail = $this->evaluatePokerHand($cards);
        return $detail['label'];
    }

    /**
     * @param array<int, PlayingCard> $cards
     * @return array{label:string,value:int,tieBreaker:array<int,int>}
     */
    public function evaluatePokerHand(array $cards): array
    {
        $values = array_map(fn(PlayingCard $card) => $this->cardNumberRank($card->getCardNumber()), $cards);
        $suits = array_map(fn(PlayingCard $card) => $card->getCardSuit()->value, $cards);

        $sortedValues = $values;
        sort($sortedValues);

        $sortedHighValues = array_map(fn(int $value) => $value === 1 ? 14 : $value, $sortedValues);
        sort($sortedHighValues);

        $uniqueValues = array_unique($sortedValues);
        $isFlush = count(array_unique($suits)) === 1;
        $isStraight = false;
        $straightHigh = max($sortedHighValues);

        if (count($uniqueValues) === 5)
        {
            if ($sortedValues === [1, 2, 3, 4, 5])
            {
                $isStraight = true;
                $straightHigh = 5;
            }
            elseif ($sortedHighValues === [10, 11, 12, 13, 14])
            {
                $isStraight = true;
                $straightHigh = 14;
            }
            elseif (max($sortedValues) - min($sortedValues) === 4)
            {
                $isStraight = true;
                $straightHigh = max($sortedHighValues);
            }
        }

        $counts = array_count_values($sortedHighValues);
        arsort($counts);
        $countValues = array_values($counts);
        $countKeys = array_keys($counts);

        if ($isStraight && $isFlush && $straightHigh === 14)
        {
            return ['label' => 'Royal Flush', 'value' => 10, 'tieBreaker' => [14]];
        }

        if ($isStraight && $isFlush)
        {
            return ['label' => 'Straight Flush', 'value' => 9, 'tieBreaker' => [$straightHigh]];
        }

        if ($countValues[0] === 4)
        {
            $quad = $countKeys[0];
            $kicker = array_values(array_diff($sortedHighValues, array_fill(0, 4, $quad)))[0];
            return ['label' => 'Four of a Kind', 'value' => 8, 'tieBreaker' => [$quad, $kicker]];
        }

        if ($countValues[0] === 3 && $countValues[1] === 2)
        {
            return ['label' => 'Full House', 'value' => 7, 'tieBreaker' => [$countKeys[0], $countKeys[1]]];
        }

        if ($isFlush)
        {
            return ['label' => 'Flush', 'value' => 6, 'tieBreaker' => array_reverse($sortedHighValues)];
        }

        if ($isStraight)
        {
            return ['label' => 'Straight', 'value' => 5, 'tieBreaker' => [$straightHigh]];
        }

        if ($countValues[0] === 3)
        {
            $kickers = array_reverse(array_diff($sortedHighValues, array_fill(0, 3, $countKeys[0])));
            return ['label' => 'Three of a Kind', 'value' => 4, 'tieBreaker' => array_merge([$countKeys[0]], $kickers)];
        }

        if ($countValues[0] === 2 && $countValues[1] === 2)
        {
            $pairs = array_slice($countKeys, 0, 2);
            sort($pairs);
            $kicker = array_values(array_diff($sortedHighValues, [$pairs[0], $pairs[0], $pairs[1], $pairs[1]]))[0];
            return ['label' => 'Two Pair', 'value' => 3, 'tieBreaker' => [max($pairs), min($pairs), $kicker]];
        }

        if ($countValues[0] === 2)
        {
            $pairValue = $countKeys[0];
            $kickers = array_reverse(array_diff($sortedHighValues, array_fill(0, 2, $pairValue)));
            return ['label' => 'Pair', 'value' => 2, 'tieBreaker' => array_merge([$pairValue], $kickers)];
        }

        return ['label' => 'High Card', 'value' => 1, 'tieBreaker' => array_reverse($sortedHighValues)];
    }

    public function cardNumberRank(CardNumber $cardNumber): int
    {
        return match ($cardNumber)
        {
            CardNumber::ace => 1,
            CardNumber::two => 2,
            CardNumber::three => 3,
            CardNumber::four => 4,
            CardNumber::five => 5,
            CardNumber::six => 6,
            CardNumber::seven => 7,
            CardNumber::eight => 8,
            CardNumber::nine => 9,
            CardNumber::ten => 10,
            CardNumber::jack => 11,
            CardNumber::queen => 12,
            CardNumber::king => 13,
            CardNumber::joker => 0,
        };
    }
}
