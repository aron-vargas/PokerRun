<?php
// src/MessageHandler/SyncContactMessageHandler.php
namespace App\MessageHandler;

use App\Message\PlayerMessage;
use App\Message\CardStopMessage;
use App\DataFixtures\PlayerAction;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Notifier\Recipient\Recipient;
use Symfony\Component\Notifier\Notification\Notification;
use Symfony\Component\Notifier\NotifierInterface;

#[AsMessageHandler]
class PlayerMessageHandler
{
    private HttpClientInterface $client;
    private string $apiKey;

    public function __construct(
        HttpClientInterface $client,
        ParameterBagInterface $parameterBag,
        private MessageBusInterface $messageBus,
        private NotifierInterface $notifier, )
    {
        $this->client = $client;
        $this->apiKey = $parameterBag->get('app.constant_contact.api_key');
    }

    public function __invoke(PlayerMessage $message): void
    {
        match ($message->getAction())
        {
            PlayerAction::$Register => $this->handleRegister($message),
            PlayerAction::$CheckIn => $this->handleCheckin($message),
            PlayerAction::$Update => $this->handleUpdate($message),
            PlayerAction::$Delete => $this->handleDelete($message),
            PlayerAction::$MakePurchase => $this->handlePurchase($message),
            PlayerAction::$ApproveCheckin => $this->handleApproveCheckin($message),
            PlayerAction::$DenyCheckin => $this->handleDenyCheckin($message),
            default => throw new \InvalidArgumentException('Unknown type'),
        };

        // Handle rate limits or errors appropriately here
    }

    private function handleRegister(PlayerMessage $message): void
    {
        // Make asynchronous API call to Constant Contact
        $response = $this->client->request('POST', 'https://cc.email', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'email_address' => [
                    'address' => $message->getPlayer()->getEmail(),
                    'permission_to_send' => 'explicit'
                ],
                'first_name' => $message->getPlayer()->getFirstName(),
                'last_name' => $message->getPlayer()->getLastName(),
            ]
        ]);
    }

    private function handleCheckin(PlayerMessage $message): void
    {
        // Send message to queue for check in
        $this->messageBus->dispatch(new CardStopMessage($message->getPlayer(), $message->getLocation(), PlayerAction::$CheckIn));
    }

    private function handleUpdate(PlayerMessage $message): void
    {
        // Handle update logic
    }

    private function handleDelete(PlayerMessage $message): void
    {
        // Handle delete logic
    }

    private function handlePurchase(PlayerMessage $message): void
    {
        // Handle purchase logic
    }
    private function handleApproveCheckin(PlayerMessage $message): void
    {
        // Handle approve check-in logic
        // 1. Fetch user entity using $notification->userId
        // 2. Instantiate notification
        $note = new Notification('Check-in Verified');

        // 3. Send it (example using email)
        $recipient = new Recipient($message->getPlayer()->getEmail());
        $this->notifier->send($note, $recipient);
    }

    private function handleDenyCheckin(PlayerMessage $message): void
    {
        // Handle deny check-in logic
        $note = new Notification('Check-in Denied');

        // 3. Send it (example using email)
        $recipient = new Recipient($message->getPlayer()->getEmail());
        $this->notifier->send($note, $recipient);
    }
}