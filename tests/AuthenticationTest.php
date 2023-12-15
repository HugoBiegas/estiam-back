<?php

namespace App\Tests\Controller;

use App\Controller\ApiController;
use App\Entity\User;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use PHPUnit\Framework\TestCase;
use Doctrine\ODM\MongoDB\DocumentManager;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;

class AuthenticationTest extends TestCase
{
    public function testLoginReturnsJwtToken()
    {
        // Mocks pour chaque dépendance
        $documentManager = $this->createMock(DocumentManager::class);
        $jwtManager = $this->createMock(JWTTokenManagerInterface::class);
        $jwtEncoder = $this->createMock(JWTEncoderInterface::class);
    
        // Simuler le comportement de la méthode `create`
        $jwtManager->method('create')->willReturn('sometoken123');
        // Création de l'objet User
        $user = new User();
        $user->setUsername('testuser');
        $user->setPassword('testpass');
        $user->setRoles(['ROLE_USER']);
    
        // Création du contrôleur avec les mocks
        $controller = new ApiController($documentManager, $jwtManager, $jwtEncoder);
    
        // Appel de la méthode à tester (à ajouter dans votre contrôleur)
        $token = $controller->generateTokenForTestUser($user);
    
        // Assertion
        $this->assertNotEmpty($token);
        $this->assertIsString($token);
    }
    }
