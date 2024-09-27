<?php

namespace App\Security;

use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class JWTAuthenticator extends AbstractAuthenticator
{
    private JWTEncoderInterface $jwtEncoder;
    private UserProviderInterface $userProvider;
    private LoggerInterface $logger;

    public function __construct(JWTEncoderInterface $jwtEncoder, UserProviderInterface $userProvider, LoggerInterface $logger)
    {
        $this->jwtEncoder = $jwtEncoder;
        $this->userProvider = $userProvider;
        $this->logger = $logger;
    }

    public function supports(Request $request): ?bool
    {
        return $request->headers->has('Authorization') && str_starts_with($request->headers->get('Authorization'), 'Bearer ');
    }

    public function authenticate(Request $request): SelfValidatingPassport
    {
        $authHeader = $request->headers->get('Authorization');
        $token = substr($authHeader, 7); // Remove 'Bearer ' from the token string

        if (empty($token)) {
            throw new CustomUserMessageAuthenticationException('No JWT token found in the Authorization header');
        }

        try {
            $data = $this->jwtEncoder->decode($token);
            if (!isset($data['username'])) {
                throw new CustomUserMessageAuthenticationException('JWT token is missing required "username" claim.');
            }

            // Create a UserBadge using the username
            $userBadge = new UserBadge($data['username'], function ($username) {
                return $this->userProvider->loadUserByIdentifier($username);
            });
        } catch (\Exception $e) {
            throw new CustomUserMessageAuthenticationException('Invalid JWT token: ' . $e->getMessage());
        }

        // Pass the UserBadge to SelfValidatingPassport
        return new SelfValidatingPassport($userBadge);
    }

    public function onAuthenticationSuccess(Request $request, $token, string $firewallName): ?Response
    {
        return null; // Continue the request on successful authentication
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        return new Response('Authentication failed: ' . $exception->getMessage(), Response::HTTP_UNAUTHORIZED);
    }

    public function start(Request $request, AuthenticationException $authException = null): Response
    {
        return new Response('Authentication required', Response::HTTP_UNAUTHORIZED);
    }
}
