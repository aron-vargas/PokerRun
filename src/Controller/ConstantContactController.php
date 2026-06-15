<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use ConstantContactApi\Client\Configuration;

class ConstantContactController extends AbstractController
{
    #[Route('/constant-contact/connect', name: 'cc_connect')]
    public function connect(): Response
    {
        $clientId = $this->getParameter('app.constant_contact.api_key');
        $redirectUri = $this->getParameter('app.constant_contact.redirect_uri');

        // Redirect user to Constant Contact's authorization page
        $authUrl = "https://constantcontact.com?" . http_build_query([
            'client_id' => $clientId,
            'response_type' => 'code',
            'redirect_uri' => $redirectUri,
            'scope' => 'contact_data email_marketing'
        ]);

        return $this->redirect($authUrl);
    }

    #[Route('/constant-contact/callback', name: 'cc_callback')]
    public function callback(Request $request): Response
    {
        $code = $request->query->get('code');
        // Here, use your chosen HTTP client (e.g., Guzzle) to exchange the $code 
        // for an access token via a POST request to Constant Contact's token endpoint.
        // Store the returned Access Token in your database for this user.

        return $this->redirectToRoute('some_dashboard_route');
    }
}
