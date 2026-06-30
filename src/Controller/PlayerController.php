<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\CardStop;
use App\Entity\PokerHand;
use App\Entity\PlayingCard;
use App\Entity\PlayerLocation;
use App\Enum\CardNumber;
use App\Enum\CardSuit;
use App\Form\CheckInFormType;
use App\Message\PlayerMessage;
use App\DataFixtures\PlayerAction;
use App\Repository\CardStopRepository;
use App\Repository\PlayingCardRepository;
use App\Repository\PokerHandRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Messenger\MessageBusInterface;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;

final class PlayerController extends AbstractController {
    public function __construct(
        private MessageBusInterface $messageBus,
        private LoggerInterface $logger,
        private CardStopRepository $cardStopRepository,
        private PokerHandRepository $pokerHandRepository)
    {
    }

    #[Route('/player', name: 'app_player')]
    #[AdminRoute('/', name: 'app_player')]
    public function index(): Response
    {
        return $this->render('home/index.html.twig', [
            'controller_name' => 'PlayerController',
        ]);
    }

    #[Route('/player/profile', name: 'app_profile_show')]
    #[AdminRoute('/profile', name: 'app_profile_show')]
    public function profile(): Response
    {
        return $this->render('home/player.html.twig', [
            'controller_name' => 'PlayerController',
        ]);
    }

    #[Route('/player/check-in', name: 'app_check_in')]
    #[AdminRoute('/check-in', name: 'app_check_in')]
    public function checkIn(Request $request, ?CardStop $cardStop, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        // Get the user object
        /** @var User $user */
        $user = $this->getUser();
        $user_is_verified = $user->isVerified();
        $availableCardStops = $this->cardStopRepository
            ->findAllUnvisitedCardStopsForPlayerQB($user->getId())
            ->getQuery()
            ->getResult();

        if ($availableCardStops === [])
        {
            $this->addFlash('warning', $availableCardStops === []
                ? 'No card stop was selected and no card stops are currently available.'
                : 'No card stop was selected. Please choose an available card stop to continue.');

            return $this->render('home/check_in.html.twig', [
                'form' => null,
            ]);
        }


        $new_location = new PlayerLocation();
        $new_location->setIsVerified(false);
        $new_location->setPlayer($user);
        $new_location->setCardStop($cardStop);
        $new_location->setCheckinTime(new \DateTime());

        $twig = 'home/check_in.html.twig';
        $form = $this->createForm(CheckInFormType::class, $new_location, ['user_is_verified' => $user_is_verified]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            // Save location
            $entityManager->persist($new_location);
            $user->setLocation($new_location);
            $entityManager->persist($user);
            $entityManager->flush();

            // Send message to queue for check in
            $this->messageBus->dispatch(new PlayerMessage($user, $new_location, PlayerAction::$CheckIn));

            $twig = 'home/index.html.twig';
        }

        return $this->render($twig, [
            'form' => $form->createView()
        ]);
    }

    #[Route('/player/pick-card', name: 'app_pick_card')]
    #[AdminRoute('/pick-card', name: 'app_pick_card')]
    public function pickCard(EntityManagerInterface $entityManager, PlayingCardRepository $playingCardRepository): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        /** @var User $user */
        $user = $this->getUser();
        $location = $user->getLocation();

        if (!$location || !$location->isVerified())
        {
            $this->addFlash('warning', 'Your check-in must be verified to pick a card.');

            return $this->redirectToRoute('app_player');
        }

        if ($location->getFirstCard() !== null)
        {
            $this->addFlash('info', 'You already picked a card at this location.');

            return $this->redirectToRoute('app_player');
        }

        $card = $this->drawAvailableCard($playingCardRepository);

        $this->logger->info('Player picked a card.', ['user_id' => $user->getId(), 'card_number' => $card->getCardNumber()->name, 'card_suit' => $card->getCardSuit()->value]);

        $user->addCardList($card);
        $card->setLocation($location);

        $pokerHand = $user->getPokerHand();
        if (!$pokerHand)
        {
            $pokerHand = new PokerHand();
            $pokerHand->setPlayer($user);
            $user->setPokerHand($pokerHand);
            $entityManager->persist($pokerHand);
        }

        $currentCards = array_values(array_filter([
            $pokerHand->getCardOne(),
            $pokerHand->getCardTwo(),
            $pokerHand->getCardThree(),
            $pokerHand->getCardFour(),
            $pokerHand->getCardFive(),
        ]));

        if (count($currentCards) < 5)
        {
            $currentCards[] = $card;
            $this->syncPokerHand($pokerHand, $currentCards);
        }

        $entityManager->persist($card);
        $user->setLocation(null);
        $entityManager->persist($user);
        $entityManager->flush();

        $this->addFlash('success', 'Your card has been picked.');

        return $this->redirectToRoute('app_player');
    }

    #[Route('/player/check-out', name: 'app_check_out')]
    #[AdminRoute('/check-out', name: 'app_check_out')]
    public function checkOut(EntityManagerInterface $entityManager): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $user->setLocation(null);
        $entityManager->persist($user);
        $entityManager->flush();

        return $this->redirectToRoute('app_check_in');
    }

    #[Route('/player/best-hand', name: 'app_best_hand')]
    #[AdminRoute('/best-hand', name: 'app_best_hand')]
    public function bestHand(EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        /** @var User $user */
        $user = $this->getUser();
        $cards = array_values($user->getCardList()->toArray());

        if (count($cards) < 5)
        {
            $this->addFlash('warning', 'You need at least five cards before a best hand can be selected.');

            return $this->redirectToRoute('app_player');
        }

        $bestCards = $this->selectBestPokerHand($cards);
        $bestCards = $this->orderCardsForDisplay($bestCards);

        $pokerHand = $user->getPokerHand();
        if (!$pokerHand)
        {
            $pokerHand = new PokerHand();
            $pokerHand->setPlayer($user);
            $entityManager->persist($pokerHand);
        }

        $this->syncPokerHand($pokerHand, $bestCards);
        $user->setPokerHand($pokerHand);

        $entityManager->persist($pokerHand);
        $entityManager->flush();

        $this->addFlash('success', 'Your best poker hand has been selected.');

        return $this->redirectToRoute('app_player');
    }

    private function drawAvailableCard(PlayingCardRepository $playingCardRepository): PlayingCard
    {
        $usedCards = [];

        foreach ($playingCardRepository->findAll() as $existingCard)
        {
            if (!$existingCard->getCardNumber() || !$existingCard->getCardSuit())
            {
                continue;
            }

            $usedCards[$existingCard->getCardNumber()->name . '|' . $existingCard->getCardSuit()->value] = true;
        }

        $availableCards = [];
        foreach (CardNumber::cases() as $cardNumber)
        {
            if ($cardNumber === null || $cardNumber->name === 'joker')
            {
                continue;
            }

            foreach (CardSuit::cases() as $cardSuit)
            {
                $cardKey = $cardNumber->name . '|' . $cardSuit->value;
                if (!isset($usedCards[$cardKey]))
                {
                    $availableCards[] = [$cardNumber, $cardSuit];

                    $this->logger->info('Adding card to available cards.', ['card_number' => $cardNumber, 'card_suit' => $cardSuit]);
                }
            }
        }

        if ($availableCards === [])
        {
            throw new \RuntimeException('There are no cards left to pick.');
        }

        [$cardNumber, $cardSuit] = $availableCards[array_rand($availableCards)];

        $this->logger->info('Random card selected.', ['card_number' => $cardNumber, 'card_suit' => $cardSuit]);

        $card = new PlayingCard();
        $card->setCardNumber($cardNumber);
        $card->setCardSuit($cardSuit);
        $card->setImage(sprintf('images/PlayingCards/%s_of_%s.png', strtolower($cardNumber->name), $cardSuit->value));

        return $card;
    }

    private function syncPokerHand(PokerHand $pokerHand, array $cards): void
    {
        $cards = array_values(array_filter($cards));

        $pokerHand->setCardOne($cards[0] ?? null);
        $pokerHand->setCardTwo($cards[1] ?? null);
        $pokerHand->setCardThree($cards[2] ?? null);
        $pokerHand->setCardFour($cards[3] ?? null);
        $pokerHand->setCardFive($cards[4] ?? null);
    }

    /**
     * @param array<int, PlayingCard> $cards
     * @return array<int, PlayingCard>
     */
    private function selectBestPokerHand(array $cards): array
    {
        $bestCards = [];
        $bestEvaluation = null;
        $pokerHand = new PokerHand();

        foreach ($this->generateCardCombinations($cards, 5) as $combination)
        {
            $evaluation = $pokerHand->evaluatePokerHand($combination);

            if ($bestEvaluation === null || $this->compareEvaluations($evaluation, $bestEvaluation) > 0)
            {
                $bestEvaluation = $evaluation;
                $bestCards = $combination;
            }
        }

        return $bestCards;
    }

    /**
     * @param array<int, PlayingCard> $cards
     * @return array<int, array<int, PlayingCard>>
     */
    private function generateCardCombinations(array $cards, int $handSize, int $start = 0, array $current = []): array
    {
        if (count($current) === $handSize)
        {
            return [$current];
        }

        $combinations = [];
        for ($index = $start; $index < count($cards); $index++)
        {
            $current[] = $cards[$index];
            foreach ($this->generateCardCombinations($cards, $handSize, $index + 1, $current) as $combination)
            {
                $combinations[] = $combination;
            }
            array_pop($current);
        }

        return $combinations;
    }

    /**
     * @param array<int, PlayingCard> $cards
     * @return array<int, PlayingCard>
     */
    private function orderCardsForDisplay(array $cards): array
    {
        usort($cards, function (PlayingCard $left, PlayingCard $right): int
        {
            $hand = new PokerHand();
            $leftRank = $hand->cardNumberRank($left->getCardNumber());
            $rightRank = $hand->cardNumberRank($right->getCardNumber());

            if ($leftRank === $rightRank)
            {
                return $right->getCardSuit()->value <=> $left->getCardSuit()->value;
            }

            return $rightRank <=> $leftRank;
        });

        return $cards;
    }

    /**
     * @param array<int, PlayingCard> $cards
     * @return array{label:string,value:int,tieBreaker:array<int,int>}
     */
    // private function evaluatePokerHand(array $cards): array
    // {
    //     $values = array_map(fn(PlayingCard $card) => $this->cardNumberRank($card->getCardNumber()), $cards);
    //     $suits = array_map(fn(PlayingCard $card) => $card->getCardSuit()->value, $cards);

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

    //     if (($countValues[0] ?? 0) === 4)
    //     {
    //         $quad = $countKeys[0];
    //         $kicker = array_values(array_diff($sortedHighValues, array_fill(0, 4, $quad)))[0];
    //         return ['label' => 'Four of a Kind', 'value' => 8, 'tieBreaker' => [$quad, $kicker]];
    //     }

    //     if (($countValues[0] ?? 0) === 3 && ($countValues[1] ?? 0) === 2)
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

    //     if (($countValues[0] ?? 0) === 3)
    //     {
    //         $kickers = array_reverse(array_diff($sortedHighValues, array_fill(0, 3, $countKeys[0])));
    //         return ['label' => 'Three of a Kind', 'value' => 4, 'tieBreaker' => array_merge([$countKeys[0]], $kickers)];
    //     }

    //     if (($countValues[0] ?? 0) === 2 && ($countValues[1] ?? 0) === 2)
    //     {
    //         $pairs = array_slice($countKeys, 0, 2);
    //         sort($pairs);
    //         $kicker = array_values(array_diff($sortedHighValues, [$pairs[0], $pairs[0], $pairs[1], $pairs[1]]))[0];
    //         return ['label' => 'Two Pair', 'value' => 3, 'tieBreaker' => [max($pairs), min($pairs), $kicker]];
    //     }

    //     if (($countValues[0] ?? 0) === 2)
    //     {
    //         $pairValue = $countKeys[0];
    //         $kickers = array_reverse(array_diff($sortedHighValues, array_fill(0, 2, $pairValue)));
    //         return ['label' => 'Pair', 'value' => 2, 'tieBreaker' => array_merge([$pairValue], $kickers)];
    //     }

    //     return ['label' => 'High Card', 'value' => 1, 'tieBreaker' => array_reverse($sortedHighValues)];
    // }

    /**
     * @param array{label:string,value:int,tieBreaker:array<int,int>} $left
     * @param array{label:string,value:int,tieBreaker:array<int,int>} $right
     */
    private function compareEvaluations(array $left, array $right): int
    {
        if ($left['value'] !== $right['value'])
        {
            return $left['value'] <=> $right['value'];
        }

        return $this->compareTieBreakers($left['tieBreaker'], $right['tieBreaker']);
    }

    /**
     * @param array<int, int> $left
     * @param array<int, int> $right
     */
    private function compareTieBreakers(array $left, array $right): int
    {
        $length = max(count($left), count($right));

        for ($index = 0; $index < $length; $index++)
        {
            $leftValue = $left[$index] ?? 0;
            $rightValue = $right[$index] ?? 0;

            if ($leftValue !== $rightValue)
            {
                return $leftValue <=> $rightValue;
            }
        }

        return 0;
    }

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

    #[Route('/rules', name: 'app_rules')]
    #[AdminRoute('/rules', name: 'app_rules')]
    public function rules(): Response
    {
        return $this->render('home/rules.html.twig', [
            'controller_name' => 'PlayerController',
        ]);
    }

    #[Route('/map', name: 'app_map')]
    #[AdminRoute('/map', name: 'app_map')]
    public function map(): Response
    {
        return $this->render('home/map.html.twig', [
            'controller_name' => 'PlayerController',
        ]);
    }
}
