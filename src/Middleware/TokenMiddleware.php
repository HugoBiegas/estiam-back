<?php

namespace App\Middleware;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class TokenMiddleware
{
    private $jwtEncoder;

    public function __construct(JWTEncoderInterface $jwtEncoder)
    {
        $this->jwtEncoder = $jwtEncoder;
    }

    public function onKernelRequest(RequestEvent $event)
    {
        $request = $event->getRequest();
        var_dump($request);
        // Vérifiez si la route est 'showLogiciels', si oui, laissez passer la requête
        if ($request->getPathInfo() === '/api/logiciels' && $request->getMethod() === 'GET') {
            return;
        }

        // Votre logique de vérification du token
        $token = $request->cookies->get('auth_token');
        if (!$token || !$this->validateToken($token)) {
            $response = new JsonResponse("Accès non autorisé", Response::HTTP_UNAUTHORIZED);
            $event->setResponse($response);
            return;
        }
    }

    private function validateToken($token): bool
    {
        try {
            $decoded = $this->jwtEncoder->decode($token);
            return $decoded && $token != null;
        } catch (\Exception $e) {
            return false;
        }
    }
}
