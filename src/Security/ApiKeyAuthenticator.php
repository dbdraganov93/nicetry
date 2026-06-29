<?php

declare(strict_types=1);

namespace GeoProxy\Security;

use GeoProxy\Repository\ApiKeyRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

final class ApiKeyAuthenticator extends AbstractAuthenticator
{
    public function __construct(private readonly ?ApiKeyRepository $apiKeys = null) {}

    public function supports(Request $request): ?bool
    {
        return $this->extractApiKey($request) !== null;
    }

    public function authenticate(Request $request): Passport
    {
        $apiKey = $this->extractApiKey($request);
        if ($apiKey === null) {
            throw new AuthenticationException('Missing API key.');
        }

        return new SelfValidatingPassport(new UserBadge($apiKey, function (string $plainKey) {
            if ($this->apiKeys === null) {
                throw new AuthenticationException('API key repository is not configured.');
            }

            $record = $this->apiKeys->findActiveByPlainTextKey($plainKey);
            if ($record === null) {
                throw new AuthenticationException('Invalid API key.');
            }

            return $record->getUser();
        }));
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        return new JsonResponse(['error' => 'unauthorized', 'message' => $exception->getMessageKey()], Response::HTTP_UNAUTHORIZED);
    }

    public function isHeaderPresent(?string $authorization): bool
    {
        return is_string($authorization) && str_starts_with($authorization, 'Bearer gp_');
    }

    private function extractApiKey(Request $request): ?string
    {
        $authorization = $request->headers->get('Authorization');
        if ($this->isHeaderPresent($authorization)) {
            return substr((string) $authorization, 7);
        }

        $header = $request->headers->get('X-API-Key');
        return is_string($header) && str_starts_with($header, 'gp_') ? $header : null;
    }
}
