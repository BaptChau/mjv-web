<?php

namespace App\Controller;

use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Handles the two OAuth2 endpoints required for Google Sign-In:
 *
 *  - /connect/google       → redirects the user to Google's consent screen
 *  - /connect/google/check → Google redirects back here after the user authorises;
 *                            the actual authentication is handled by GoogleAuthenticator,
 *                            so this method body is intentionally empty (never reached).
 */
class GoogleController extends AbstractController
{
    /**
     * Initiates the Google OAuth2 authorisation flow.
     * The scopes requested are the minimum needed: openid, profile, email.
     */
    #[Route('/connect/google', name: 'connect_google_start')]
    public function connect(ClientRegistry $clientRegistry): RedirectResponse
    {
        return $clientRegistry
            ->getClient('google')
            ->redirect(['openid', 'profile', 'email'], []);
    }

    /**
     * Google redirects back to this URL after the user grants (or denies) access.
     * The firewall's GoogleAuthenticator handles the token exchange and user hydration.
     * This action body will never be executed.
     */
    #[Route('/connect/google/check', name: 'connect_google_check')]
    public function check(Request $request): Response
    {
        // This code is intentionally unreachable.
        // Symfony's security system intercepts this route via GoogleAuthenticator::supports().
        throw new \LogicException('This route is handled by the GoogleAuthenticator security guard.');
    }

    /**
     * Logs the public user out of the main firewall.
     * The firewall's logout listener intercepts this route — the method body is never reached.
     */
    #[Route('/deconnexion', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank — it will be intercepted by the logout key on your firewall.');
    }
}
