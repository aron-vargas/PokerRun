<?php

namespace App\Repository;

use App\Entity\PokerHand;
use App\Entity\PlayingCard as PlayingCardEntity;
use App\Enum\CardNumber;
use App\Enum\CardSuit;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PokerHand>
 */
class PokerHandRepository extends ServiceEntityRepository {
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PokerHand::class);
    }

    public function findCurrentHighestHands(int $limit = 10): array
    {
        $hands = $this->createQueryBuilder('h')
            ->addSelect('p, c1, c2, c3, c4, c5')
            ->join('h.Player', 'p')
            ->leftJoin('h.cardOne', 'c1')
            ->leftJoin('h.cardTwo', 'c2')
            ->leftJoin('h.cardThree', 'c3')
            ->leftJoin('h.cardFour', 'c4')
            ->leftJoin('h.cardFive', 'c5')
            ->getQuery()
            ->getResult();

        $stats = [];
        foreach ($hands as $hand)
        {
            $cards = array_filter([
                $hand->getCardOne(),
                $hand->getCardTwo(),
                $hand->getCardThree(),
                $hand->getCardFour(),
                $hand->getCardFive(),
            ]);

            if (count($cards) < 2)
            {
                continue;
            }

            $evaluation = $hand->evaluatePokerHand($cards);
            $player = $hand->getPlayer();

            $stats[] = [
                'playerName' => trim(sprintf('%s %s', $player?->getFirstName() ?? '', $player?->getLastName() ?? '')),
                'pokerHand' => $this->formatPokerCards($cards),
                'handRank' => $evaluation['label'],
                'handValue' => $evaluation['value'],
                'tieBreaker' => $evaluation['tieBreaker'],
            ];
        }

        usort($stats, function (array $a, array $b)
        {
            if ($a['handValue'] !== $b['handValue'])
            {
                return $b['handValue'] <=> $a['handValue'];
            }

            return $b['tieBreaker'] <=> $a['tieBreaker'];
        });

        return array_slice($stats, 0, $limit);
    }

    private function formatPokerCards(array $cards): string
    {
        return implode(', ', array_map(function (PlayingCardEntity $card)
        {
            return sprintf('%s of %s', $card->getCardNumber()->value, $card->getCardSuit()->value);
        }, $cards));
    }

    /**
     * @param array<int, PlayingCard> $cards
     * @return array{label:string,value:int,tieBreaker:array<int,int>}
     */
    // public function evaluatePokerHand(array $cards): array
    // {
    //     $values = array_map(fn(PlayingCardEntity $card) => $this->cardNumberRank($card->getCardNumber()), $cards);
    //     $suits = array_map(fn(PlayingCardEntity $card) => $card->getCardSuit()->value, $cards);

    //     $sortedValues = $values;
    //     sort($sortedValues);

    //     $sortedHighValues = array_map(fn(int $value) => $value === 1 ? 14 : $value, $sortedValues);
    //     sort($sortedHighValues);

    //     $uniqueValues = array_unique($sortedValues);
    //     $isFlush = count(array_unique($suits)) === 1;
    //     $isStraight = false;
    //     $straightHigh = max($sortedHighValues);

    //     if (count($uniqueValues) === 5)
    //     {
    //         if ($sortedValues === [1, 2, 3, 4, 5])
    //         {
    //             $isStraight = true;
    //             $straightHigh = 5;
    //         }
    //         elseif ($sortedHighValues === [10, 11, 12, 13, 14])
    //         {
    //             $isStraight = true;
    //             $straightHigh = 14;
    //         }
    //         elseif (max($sortedValues) - min($sortedValues) === 4)
    //         {
    //             $isStraight = true;
    //             $straightHigh = max($sortedHighValues);
    //         }
    //     }

    //     $counts = array_count_values($sortedHighValues);
    //     arsort($counts);
    //     $countValues = array_values($counts);
    //     $countKeys = array_keys($counts);

    //     if ($isStraight && $isFlush && $straightHigh === 14)
    //     {
    //         return ['label' => 'Royal Flush', 'value' => 10, 'tieBreaker' => [14]];
    //     }

    //     if ($isStraight && $isFlush)
    //     {
    //         return ['label' => 'Straight Flush', 'value' => 9, 'tieBreaker' => [$straightHigh]];
    //     }

    //     if ($countValues[0] === 4)
    //     {
    //         $quad = $countKeys[0];
    //         $kicker = array_values(array_diff($sortedHighValues, array_fill(0, 4, $quad)))[0];
    //         return ['label' => 'Four of a Kind', 'value' => 8, 'tieBreaker' => [$quad, $kicker]];
    //     }

    //     if ($countValues[0] === 3 && $countValues[1] === 2)
    //     {
    //         return ['label' => 'Full House', 'value' => 7, 'tieBreaker' => [$countKeys[0], $countKeys[1]]];
    //     }

    //     if ($isFlush)
    //     {
    //         return ['label' => 'Flush', 'value' => 6, 'tieBreaker' => array_reverse($sortedHighValues)];
    //     }

    //     if ($isStraight)
    //     {
    //         return ['label' => 'Straight', 'value' => 5, 'tieBreaker' => [$straightHigh]];
    //     }

    //     if ($countValues[0] === 3)
    //     {
    //         $kickers = array_reverse(array_diff($sortedHighValues, array_fill(0, 3, $countKeys[0])));
    //         return ['label' => 'Three of a Kind', 'value' => 4, 'tieBreaker' => array_merge([$countKeys[0]], $kickers)];
    //     }

    //     if ($countValues[0] === 2 && $countValues[1] === 2)
    //     {
    //         $pairs = array_slice($countKeys, 0, 2);
    //         sort($pairs);
    //         $kicker = array_values(array_diff($sortedHighValues, [$pairs[0], $pairs[0], $pairs[1], $pairs[1]]))[0];
    //         return ['label' => 'Two Pair', 'value' => 3, 'tieBreaker' => [max($pairs), min($pairs), $kicker]];
    //     }

    //     if ($countValues[0] === 2)
    //     {
    //         $pairValue = $countKeys[0];
    //         $kickers = array_reverse(array_diff($sortedHighValues, array_fill(0, 2, $pairValue)));
    //         return ['label' => 'Pair', 'value' => 2, 'tieBreaker' => array_merge([$pairValue], $kickers)];
    //     }

    //     return ['label' => 'High Card', 'value' => 1, 'tieBreaker' => array_reverse($sortedHighValues)];
    // }

    // private function cardNumberRank(CardNumber $cardNumber): int
    // {
    //     return match ($cardNumber)
    //     {
    //         CardNumber::ace => 1,
    //         CardNumber::two => 2,
    //         CardNumber::three => 3,
    //         CardNumber::four => 4,
    //         CardNumber::five => 5,
    //         CardNumber::six => 6,
    //         CardNumber::seven => 7,
    //         CardNumber::eight => 8,
    //         CardNumber::nine => 9,
    //         CardNumber::ten => 10,
    //         CardNumber::jack => 11,
    //         CardNumber::queen => 12,
    //         CardNumber::king => 13,
    //         CardNumber::joker => 0,
    //     };
    // }
}
