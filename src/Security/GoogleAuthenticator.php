<?php

namespace App\Security;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Google_Client;
use Google_Service_Oauth2;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

final class GoogleAuthenticator extends AbstractAuthenticator implements AuthenticationEntryPointInterface
{
    use TargetPathTrait;

    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly string $googleClientId,
        private readonly string $googleClientSecret,
        private readonly string $googleRedirectUri,
    ) {
    }

    public function supports(Request $request): ?bool
    {
        return $request->attributes->get('_route') === 'app_auth_google_callback';
    }

    public function authenticate(Request $request): SelfValidatingPassport
    {
        $session = $request->getSession();
        $state = $request->query->get('state');
        if (!$state || $state !== $session->get('google_oauth_state')) {
            throw new AuthenticationException('Invalid OAuth state.');
        }

        $code = $request->query->get('code');
        if (!is_string($code) || $code === '') {
            throw new AuthenticationException('Missing Google authorization code.');
        }

        $client = $this->createGoogleClient();
        $token = $client->fetchAccessTokenWithAuthCode($code);
        if (isset($token['error'])) {
            throw new AuthenticationException($token['error_description'] ?? 'Google authentication failed.');
        }

        $oauth2 = new Google_Service_Oauth2($client);
        $googleUser = $oauth2->userinfo->get();
        $email = $googleUser->getEmail();
        if (!$email) {
            throw new AuthenticationException('No email returned by Google.');
        }

        $googleId = $googleUser->getId();
        $name = $googleUser->getName() ?: $googleUser->getGivenName() ?: $email;
        $avatar = $googleUser->getPicture();

        $user = null;
        if ($googleId) {
            $user = $this->userRepository->findOneBy(['googleId' => $googleId]);
        }
        if (!$user) {
            $user = $this->userRepository->findOneBy(['email' => $email]);
        }

        if (!$user) {
            $user = new User();
            $user->setEmail($email);
            $user->setGoogleId($googleId);
            $user->setName($name);
            $user->setAvatarUrl($avatar);
        } else {
            $user->setEmail($email);
            if ($googleId && $user->getGoogleId() !== $googleId) {
                $user->setGoogleId($googleId);
            }
            $user->setName($name);
            $user->setAvatarUrl($avatar);
        }

        $this->entityManager->persist($user);
        $this->entityManager->flush();
        $session->remove('google_oauth_state');

        return new SelfValidatingPassport(
            new UserBadge($user->getUserIdentifier(), static fn () => $user)
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?RedirectResponse
    {
        $targetPath = $this->getTargetPath($request->getSession(), $firewallName);
        if ($targetPath) {
            return new RedirectResponse($targetPath);
        }

        return new RedirectResponse($this->urlGenerator->generate('app_main'));
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?RedirectResponse
    {
        $request->getSession()->getFlashBag()->add('error', 'Connexion Google impossible.');

        return new RedirectResponse($this->urlGenerator->generate('app_main'));
    }

    public function start(Request $request, AuthenticationException $authException = null): RedirectResponse
    {
        $redirect = $request->headers->get('referer') ?? $request->getUri();
        if ($request->hasSession()) {
            $this->saveTargetPath($request->getSession(), 'main', $redirect);
        }

        return new RedirectResponse($this->urlGenerator->generate('app_auth_google', [
            'redirect' => $redirect,
        ]));
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
