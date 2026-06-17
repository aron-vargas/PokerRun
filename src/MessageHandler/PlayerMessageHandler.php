<?php
// src/MessageHandler/SyncContactMessageHandler.php
namespace App\MessageHandler;

use App\Message\PlayerMessage;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;


#[AsMessageHandler]
class PlayerMessageHandler
{
    private HttpClientInterface $client;
    private string $apiKey;

    public function __construct(HttpClientInterface $client, ParameterBagInterface $parameterBag)
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
                    'address' => $message->getEmail(),
                    'permission_to_send' => 'explicit'
                ],
                'first_name' => $message->getFirstName(),
                'last_name' => $message->getLastName(),
            ]
        ]);
    }

    private function handleCheckin(PlayerMessage $message): void
    {
        // Send message to queue for check in
        $this->messageBus->dispatch(new CardStopMessage($message->GetUser()->getId(), $message->getLocation->getId(), $message->GetCardStop()->GetId(), PlayerAction::$CheckIn));
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
}