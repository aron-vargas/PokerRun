<?php
// src/Service/ConstantContactService.php
namespace App\Service;

use ConstantContactApi\Client\Configuration;
use ConstantContactApi\Client\Api\ContactsApi;
use ConstantContactApi\Client\Model\CreateContactRequest;
use ConstantContactApi\Client\Model\CreateContactRequestEmailAddress;
use GuzzleHttp\Client;

class ConstantContactService
{
    private string $apiKey;

    public function __construct(string $app_constant_contact_api_key)
    {
        $this->apiKey = $app_constant_contact_api_key;
    }

    public function addContact(string $accessToken, string $email, string $firstName, string $lastName, string $phone): void
    {
        // Configure the SDK with the user's access token
        $config = Configuration::getDefaultConfiguration()->setAccessToken($accessToken);
        
        $apiInstance = new ContactsApi(
            new Client(),
            $config
        );

        // \ConstantContactApi\Client\Model\AddAccountEmailAddressRequest
        // A JSON request payload containing the new email address you want to add to the Constant Contact account.
        $add_account_email_address_request = new CreateContactRequestEmailAddress([
            "address" => $email
        ]); 
   
        $contact = new CreateContactRequest();
        $contact->setEmailAddress($add_account_email_address_request);
        $contact->setFirstName($firstName);
        $contact->setLastName($lastName);
        $contact->setPhoneNumbers([$phone]);

        // Post contact to Constant Contact account
        $apiInstance->createContact($contact);
    }
}
