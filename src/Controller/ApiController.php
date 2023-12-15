<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ODM\MongoDB\DocumentManager;
use App\Document\Logiciel;
use Symfony\Component\Serializer\SerializerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use App\Entity\User;
use Symfony\Component\HttpFoundation\JsonResponse;

class ApiController extends AbstractController
{
    private $documentManager;
    private $JWTManager;
    private $jwtEncoder;

    public function __construct(DocumentManager $documentManager,JWTTokenManagerInterface $JWTManager,JWTEncoderInterface $jwtEncoder)
    {
        $this->documentManager = $documentManager;
        $this->JWTManager = $JWTManager;
        $this->jwtEncoder = $jwtEncoder;


    }
    
    #[Route("/api/logiciels", name: "app_api_logiciels", methods: ["GET"])]
    public function showLogiciels(): Response
    {
        $logiciels = $this->documentManager
            ->getRepository(Logiciel::class)
            ->findAll();
            $logicielsArray = array_map(function ($logiciel) {
                return $logiciel->toArray();
            }, $logiciels);
            
        // Manually create a JWT token
        $user = new User();
        $user->setUsername("username");
        $user->setPassword("password_here");
        $user->setRoles(['ROLE_USER']);

        $token = $this->generateTokenForTestUser($user);
        // Create a cookie
        $cookie = new Cookie(
            'auth_token',  // Cookie name
            $token,        // Cookie value
            strtotime('now + 1 hour'),  // Expire time
            '/',           // Path
            null,          // Domain
            false,         // Secure
            true,          // HttpOnly
            false,         // Raw
            'strict'       // SameSite attribute
        );
        
        // Attach the cookie to the response
        $response = new JsonResponse([
            "logiciels" => $logicielsArray,
            'token' => $token
        ]);

        $response->headers->setCookie($cookie);

        // Set the response status code to 200 OK (optional, since it's the default)
        $response->setStatusCode(Response::HTTP_OK);

        return $response;
    }

    #[Route("/api/logiciels/{id}",name: "app_api_logiciels_update",methods: ["PUT"])]
    public function updateLogiciel(string $id,Request $request,SerializerInterface $serializer): Response {
    
        // Validate JWT token
        if ($response = $this->checkToken($request)) {
            return $response;
        }
    
        // Find the existing Logiciel object by its MongoDB ID
        $existingLogiciel = $this->documentManager
            ->getRepository(Logiciel::class)
            ->find($id);

        // Check if the Logiciel exists
        self::tcheckLogicielTrouve($existingLogiciel);

        // Désérialisation de la requête
        $updatedLogiciel = $serializer->deserialize($request->getContent(), Logiciel::class, "json");

        // Récupération de l'objet ReflectionClass pour Logiciel
        $reflectionClass = new \ReflectionClass(Logiciel::class);

        // Parcourir chaque propriété de l'objet mis à jour
        foreach ($reflectionClass->getProperties() as $property) {
            $property->setAccessible(true); // Permet l'accès à la propriété privée

            $updatedValue = $property->getValue($updatedLogiciel);
            if ($updatedValue !== null) {
                $property->setValue($existingLogiciel, $updatedValue);
            }
        }

        // Enregistrer les modifications
        $this->documentManager->flush();

        return new JsonResponse("Logiciel mis à jour avec succès", Response::HTTP_OK);
    }

    #[Route("/api/logiciels/details/{id}",name: "app_api_logiciels_details", methods: ["GET"] )]
    public function getLogicielDetails(string $id, Request $request): Response
    {
        // Valider le token JWT
        if ($response = $this->checkToken($request)) {
            return $response;
        }
    
        // Trouver le logiciel par son identifiant MongoDB
        $logiciel = $this->documentManager
            ->getRepository(Logiciel::class)
            ->findOneBy(['id' => $id]);
        // Vérifier si le logiciel existe
        if (!$logiciel) {
            return new JsonResponse(
                ["message" => "Logiciel non trouver", "logicielle" => $logiciel],
                Response::HTTP_NOT_FOUND
            );
        }

        // Préparer la réponse en utilisant la méthode toArray()
        $response = $logiciel->toArray();
        return new JsonResponse(
            $response,
            Response::HTTP_OK
        );
    }

    #[Route("/api/logiciels",name: "app_api_logiciels_create",methods: ["POST"])]
    public function createLogiciel(Request $request, DocumentManager $dm, SerializerInterface $serializer): Response {
        
        // Validate JWT token
        if ($response = $this->checkToken($request)) {
            return $response;
        }
    
        try {
            // Deserialize JSON content to a Logiciel object
            $logiciel = $serializer->deserialize(
                $request->getContent(),
                Logiciel::class,
                "json"
            );

            // Save to MongoDB
            $dm->persist($logiciel);
            $dm->flush();

            return new JsonResponse(
                "Logiciel créé avec succès",
                Response::HTTP_CREATED
            );
        } catch (\Exception $e) {
            return new JsonResponse(
                "Erreur: " . $e->getMessage(),
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    #[Route("/api/logiciels/{id}",name: "app_api_logiciels_delete",methods: ["DELETE"])]
    public function deleteLogiciel($id, DocumentManager $dm, Request $request): Response
    {
        // Validate JWT token
        if ($response = $this->checkToken($request)) {
            return $response;
        }
    
        $logiciel = $dm->getRepository(Logiciel::class)->find($id);

        self::tcheckLogicielTrouve($logiciel);

        $dm->remove($logiciel);
        $dm->flush();

        return new JsonResponse("Logiciel supprimé avec succès", Response::HTTP_OK);
    }

    private function validateToken(Request $request): bool
    {
        try {
            $token = $request->cookies->get('auth_token');
            $decoded = $this->jwtEncoder->decode($token);

            if (!$decoded || $token == null) {
                return false;
            }
    
            // Vous pouvez également vérifier des claims spécifiques du token ici, si nécessaire
            return true;
        } catch (\Exception $e) {
            echo 'Exception lors de la validation du token : ' . $e->getMessage();
            return false;
        }
    }

    function generateTokenForTestUser(User $user){
        try {
            $token = $this->JWTManager->create($user);
            return $token;
        } catch (\Exception $e) {
            var_dump($e->getMessage());  // Pour le débogage
            return null;
        }
    }

    private function checkToken(Request $request): ?JsonResponse
    {
        if (!$this->validateToken($request)) {
            return new JsonResponse("Accès non autorisé", Response::HTTP_UNAUTHORIZED);
        }
    
        return null;
    }

    function tcheckLogicielTrouve(Logiciel $logiciel){
        if (!$logiciel) {
            return new JsonResponse(
                "Logiciel non trouvé",
                Response::HTTP_NOT_FOUND
            );
        }
    }
}
