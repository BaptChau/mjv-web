<?php

namespace App\Controller;

use Google_Client;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

final class AuthController extends AbstractController
{
    use TargetPathTrait;

    public function __construct(
        private readonly string $googleClientId,
        private readonly string $googleClientSecret,
        private readonly string $googleRedirectUri,
    ) {
    }

    #[Route('/auth/google', name: 'app_auth_google')]
    public function google(Request $request): Response
    {
        $client = $this->createGoogleClient();
        $state = bin2hex(random_bytes(16));
        $request->getSession()->set('google_oauth_state', $state);
        $client->setState($state);

        $redirect = $request->query->get('redirect') ?? $request->headers->get('referer');
        if (is_string($redirect) && $redirect !== '') {
            $this->saveTargetPath($request->getSession(), 'main', $redirect);
        }

        return $this->redirect($client->createAuthUrl());
    }

    #[Route('/auth/google/callback', name: 'app_auth_google_callback')]
    public function callback(): Response
    {
        return new Response('', Response::HTTP_NO_CONTENT);
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method is blank - it will be intercepted by the logout key on the firewall.');
    }

    private function createGoogleClient(): Google_Client
    {
        $client = new Google_Client();
        $client->setClientId($this->googleClientId);
        $client->setClientSecret($this->googleClientSecret);
        $client->setRedirectUri($this->googleRedirectUri);
        $client->setScopes(['email', 'profile']);
        $client->setAccessType('offline');
        $client->setPrompt('select_account');

        return $client;
    }
}
