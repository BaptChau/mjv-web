<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

/**
 * Provides the public-facing login page that shows the "Se connecter avec Google" button.
 *
 * The actual OAuth flow starts when the user clicks the button (→ GoogleController::connect).
 * This controller only renders the login page and passes any authentication error back to Twig.
 */
class SecurityController extends AbstractController
{
    #[Route('/connexion', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // If already logged in, skip the login page.
        if ($this->getUser()) {
            return $this->redirectToRoute('app_forum');
        }

        return $this->render('security/login.html.twig', [
            'error' => $authenticationUtils->getLastAuthenticationError(),
        ]);
    }
}
