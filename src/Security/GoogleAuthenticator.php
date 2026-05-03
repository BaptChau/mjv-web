<?php

namespace App\Security;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Security\Authenticator\OAuth2Authenticator;
use League\OAuth2\Client\Provider\GoogleUser;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;

/**
 * Handles the OAuth2 callback from Google and authenticates (or registers) the public user.
 *
 * Flow:
 *   1. User clicks "Se connecter avec Google" → /connect/google (GoogleController::connect)
 *   2. Google redirects back to /connect/google/check
 *   3. This authenticator's supports() returns true for that path
 *   4. authenticate() fetches the Google user token, finds or creates the User entity
 *   5. On success, the user is redirected to the forum (or the originally requested URL)
 */
class GoogleAuthenticator extends OAuth2Authenticator implements AuthenticationEntryPointInterface
{
    public function __construct(
        private readonly ClientRegistry $clientRegistry,
        private readonly EntityManagerInterface $em,
        private readonly RouterInterface $router,
        private readonly UserRepository $userRepository,
    ) {
    }

    /**
     * Only activate this authenticator on the Google OAuth callback route.
     */
    public function supports(Request $request): ?bool
    {
        return $request->attributes->get('_route') === 'connect_google_check';
    }

    public function authenticate(Request $request): Passport
    {
        $client = $this->clientRegistry->getClient('google');
        $accessToken = $this->fetchAccessToken($client);

        return new SelfValidatingPassport(
            new UserBadge($accessToken->getToken(), function () use ($accessToken, $client): User {
                /** @var GoogleUser $googleUser */
                $googleUser = $client->fetchUserFromToken($accessToken);

                $googleId = $googleUser->getId();
                $email    = $googleUser->getEmail();

                // 1. Try to find an existing user by their Google ID (most stable lookup).
                $user = $this->userRepository->findOneByGoogleId($googleId);

                // 2. If no Google-ID match, try by email so we can link an existing account.
                if ($user === null) {
                    $user = $this->userRepository->findOneByEmail($email);
                }

                // 3. No existing account at all — create a new one.
                if ($user === null) {
                    $user = new User();
                    $user->setEmail($email);
                }

                // Always keep the Google profile data fresh.
                $user->setGoogleId($googleId);
                $user->setName($googleUser->getName() ?? $email);
                $user->setPicture($googleUser->getAvatar());

                $this->em->persist($user);
                $this->em->flush();

                return $user;
            })
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        // If the user was trying to reach a protected page before being redirected to login,
        // send them there. Otherwise fall back to the forum.
        $targetUrl = $this->router->generate('app_forum');

        return new RedirectResponse($targetUrl);
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $message = strtr($exception->getMessageKey(), $exception->getMessageData());

        return new Response('Erreur d\'authentification Google : ' . $message, Response::HTTP_FORBIDDEN);
    }

    /**
     * Called when an anonymous user tries to access a route guarded by IS_AUTHENTICATED.
     * Redirects them to the Google sign-in page.
     */
    public function start(Request $request, ?AuthenticationException $authException = null): Response
    {
        return new RedirectResponse(
            $this->router->generate('connect_google_start'),
            Response::HTTP_TEMPORARY_REDIRECT
        );
    }
}
