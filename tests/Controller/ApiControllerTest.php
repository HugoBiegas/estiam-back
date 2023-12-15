<?php 
namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Repository\DocumentRepository;
use App\Document\Logiciel;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use App\Controller\ApiController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\Serializer\SerializerInterface;
use App\Document\ComptoirDuLibreSoftware;


class ApiControllerTest extends WebTestCase
{
    private $documentManagerMock;
    private $JWTManagerMock;
    private $jwtEncoderMock;
    private $apiController;
    private static $logicielData;


    // Centralisation de l'initialisation des mocks
    protected function setUp(): void
    {
        parent::setUp();

        $this->documentManagerMock = $this->createMock(DocumentManager::class);
        $this->JWTManagerMock = $this->createMock(JWTTokenManagerInterface::class);
        $this->jwtEncoderMock = $this->createMock(JWTEncoderInterface::class);
        $this->jwtEncoderMock->method('decode')->willReturnCallback(fn($token) => $token === 'valid_token' ? ['valid' => true] : null);

        $this->apiController = new ApiController($this->documentManagerMock, $this->JWTManagerMock, $this->jwtEncoderMock);

        // Initialisation des données de test
        self::initializeLogicielData();
    }

    private static function initializeLogicielData(): void
    {
        self::$logicielData = [
            "logoUrl" => "https://play-lh.googleusercontent.com/YKJ6HkGHY-C38aXQpS2CgyvxwAhEKymn--ArPElfFOZqLWp2JDT0afepWnJqt_kT_w",
            "name" => "Agent OC",
            "description" => "Inventaire, télédeploiement et découverte du réseau",
            "keywords" => ["inventaire", "gestion", "inventory", "management", "agent"],
            "versionMin" => "2.4",
            "softwareType" => [
                "type" => "stack",
                "os" => [
                    "windows" => false,
                    "linux" => false,
                    "mac" => true,
                    "android" => false,
                    "ios" => false
                ]
            ],
            "hasExpertReferent" => false,
            "license" => "GPL-2.0-only",
            "comptoirDuLibreSoftware" => [
                "providers" => [
                    ["url" => "https://comptoir-du-libre.org/fr/users/3284", "name" => "FactorFX", "type" => "Company"]
                ],
                "users" => [
                    ["url" => "https://comptoir-du-libre.org/fr/users/1078", "name" => "Abbeville - Mairie d'Abbeville", "type" => "Administration"]
                ]
            ]
        ];
    }

    // Utilisation d'une méthode privée pour créer un mock Logiciel
    private function createLogicielMock($id): Logiciel
    {
        $logiciel = new Logiciel();
        $logiciel->setId($id);
        // Configuration supplémentaire si nécessaire
        return $logiciel;
    }
    
    public function testGetLogicielDetails()
    {
        // Création d'un mock pour DocumentRepository
        $logicielRepo = $this->createMock(DocumentRepository::class);

        // Configuration du mock pour DocumentManager pour retourner le mock de DocumentRepository
        $this->documentManagerMock->method('getRepository')
                                ->with(Logiciel::class)
                                ->willReturn($logicielRepo);

        // Test case 1: Valid software ID
        $logiciel = new Logiciel();
        $logiciel->setId('653bac764ebbb5ee8097e742');
        $logicielRepo->method('findOneBy')
                    ->with(['id' => '653bac764ebbb5ee8097e742'])
                    ->willReturn($logiciel);

        // Création de la requête
        $request = Request::create('/api/logiciels/details/653bac764ebbb5ee8097e742', 'GET',[],['auth_token'=>'valid_token']);

        // Appel de la méthode du contrôleur avec le mock de DocumentManager
        $response = $this->apiController->getLogicielDetails('653bac764ebbb5ee8097e742', $request);
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);
        $this->assertIsArray($responseData);
        $this->assertArrayHasKey('id', $responseData);
        $this->assertEquals('653bac764ebbb5ee8097e742', $responseData['id']);
    
    }
            
    public function testShowLogiciels()
    {
        // Création d'un mock pour DocumentRepository
        $documentRepositoryMock = $this->createMock(DocumentRepository::class);
    
        // Configuration du mock pour DocumentRepository avec la méthode findAll
        $logicielMock = $this->createMock(Logiciel::class);
        $logicielMock->method('toArray')
                     ->willReturn(['name' => 'Logiciel Test', 'description' => 'Description Test']);
        $documentRepositoryMock->method('findAll')
                               ->willReturn([$logicielMock]);
    
        // Configuration du mock pour DocumentManager pour retourner le repository mocké
        $this->documentManagerMock->method('getRepository')
                                  ->willReturn($documentRepositoryMock);
    
        // Appel de la méthode showLogiciels du contrôleur
        $response = $this->apiController->showLogiciels();
    
        // Assertions pour vérifier le comportement attendu
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(JsonResponse::HTTP_OK, $response->getStatusCode());
        $this->assertTrue($response->headers->contains('Content-Type', 'application/json'));
    
        $content = json_decode($response->getContent(), true);
        $this->assertIsArray($content);
        $this->assertArrayHasKey('logiciels', $content);
        $this->assertNotEmpty($content['logiciels']);
        $this->assertIsArray($content['logiciels']);
        $this->assertCount(1, $content['logiciels']);
        $this->assertEquals('Logiciel Test', $content['logiciels'][0]['name']);
        $this->assertEquals('Description Test', $content['logiciels'][0]['description']);
    }
    
    public function testCreateLogiciel()
    {
        // Création d'un mock partiel pour Request
        $requestMock = $this->createMock(Request::class);
        $requestMock->method('getContent')->willReturn(json_encode(self::$logicielData));
    
        // Configuration du mock de Request pour simuler un token JWT valide
        $cookies = new ParameterBag(['auth_token' => 'valid_token']);
        $requestMock->cookies = $cookies;
    
        // Création d'un mock pour DocumentManager
        $documentManagerMock = $this->createMock(DocumentManager::class);

        $documentManagerMock->expects($this->once())
                            ->method('persist')
                            ->with($this->isInstanceOf(Logiciel::class));

        $documentManagerMock->expects($this->once())
                            ->method('flush');
    
        // Création d'un mock pour SerializerInterface
        $serializerMock = $this->createMock(SerializerInterface::class);
        $serializerMock->method('deserialize')
                       ->willReturn(new Logiciel());
        
        // Appel de la méthode createLogiciel avec tous les arguments nécessaires
        $response = $this->apiController->createLogiciel($requestMock, $documentManagerMock, $serializerMock);
    
        // Vérifications
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());
        $content = json_decode($response->getContent(), true);
        $this->assertEquals("Logiciel créé avec succès", $content);
    }
        
    public function testUpdateLogiciel()
    {
        // Créez un logiciel fictif existant pour le test
        $existingLogiciel = self::createLogicielMock('653bac764ebbb5ee8097e742');
        $existingLogiciel->setLogoUrl('https://example.com/logo.png');
        $existingLogiciel->setName('Original Software Name');
        $existingLogiciel->setDescription('Original Description');
        $existingLogiciel->setKeywords(['original', 'software']);
        $existingLogiciel->setVersionMin('1.0');
        $existingLogiciel->setSoftwareType(['type' => 'desktop', 'os' => ['windows' => true]]);
        $existingLogiciel->setHasExpertReferent(true);
        $existingLogiciel->setLicense('MIT');
        $updatedProviders = [
            ['url' => 'https://newprovider.com', 'name' => 'New Provider', 'type' => 'Company']
        ];
        $updatedUsers = [
            ['url' => 'https://newuser.com', 'name' => 'New User', 'type' => 'User']
        ];
    
        $updatedComptoirDuLibreSoftware = new ComptoirDuLibreSoftware();
        $updatedComptoirDuLibreSoftware->setProviders($updatedProviders);
        $updatedComptoirDuLibreSoftware->setUsers($updatedUsers);
    
        $existingLogiciel->setComptoirDuLibreSoftware($updatedComptoirDuLibreSoftware);
        // Créez un mock pour le DocumentRepository
        $documentRepositoryMock = $this->createMock(DocumentRepository::class);
        $documentRepositoryMock->method('find')->willReturn($existingLogiciel);
        $serializerMock = $this->createMock(SerializerInterface::class);

        // Configurez le mock du DocumentManager pour renvoyer le mock du DocumentRepository
        $this->documentManagerMock->method('getRepository')->willReturn($documentRepositoryMock);
        $updatedLogiciel = new Logiciel();
        $updatedLogiciel->setName("Updated Test Software");
        $updatedLogiciel->setDescription("Updated Test Description");
        $updatedLogiciel->setName("Updated Test Software");
        $updatedLogiciel->setDescription("Updated Test Description");
        $updatedLogiciel->setLogoUrl('https://example.com/updated_logo.png');
        $updatedLogiciel->setKeywords(['updated', 'test', 'software']);
        $updatedLogiciel->setVersionMin('2.0');
        $updatedLogiciel->setSoftwareType(['type' => 'web', 'os' => ['linux' => true, 'mac' => true]]);
        $updatedLogiciel->setHasExpertReferent(false);
        $updatedLogiciel->setLicense('GPL-3.0');
    
        // Utilisez le SerializerInterface pour convertir l'objet en JSON
        $updatedDataJson = $serializerMock->serialize($updatedLogiciel, 'json');
        
        
        // Créez une requête fictive avec un token JWT valide
        $request = new Request([], [], [], [], [], ['CONTENT_TYPE' => 'application/json'], $updatedDataJson);
        $request->cookies->set('auth_token', 'valid_token');
    
        // Configurez le mock SerializerInterface pour renvoyer l'objet Logiciel désérialisé
        $serializerMock->method('deserialize')
                       ->willReturn($updatedLogiciel);
    
        // Appelez la méthode updateLogiciel en passant le DocumentManager, la requête et le SerializerInterface
        $response = $this->apiController->updateLogiciel('653bac764ebbb5ee8097e742', $request, $serializerMock);
    
        // Vérifiez que la réponse est un JsonResponse avec un statut HTTP 200 (OK)
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        // Vérifiez que le contenu de la réponse indique que le logiciel a été mis à jour avec succès
        $content = json_decode($response->getContent(), true);
        $this->assertEquals("Logiciel mis à jour avec succès", $content);
    
        // Vérifiez que les propriétés du logiciel existant ont été mises à jour correctement
        $this->assertEquals("Updated Test Software", $existingLogiciel->getName());
        $this->assertEquals("Updated Test Description", $existingLogiciel->getDescription());
        // Vérifier les autres propriétés mises à jour
        $this->assertEquals('https://example.com/updated_logo.png', $existingLogiciel->getLogoUrl());
        $this->assertEquals(['updated', 'test', 'software'], $existingLogiciel->getKeywords());
        $this->assertEquals('2.0', $existingLogiciel->getVersionMin());
        $this->assertEquals(['type' => 'web', 'os' => ['linux' => true, 'mac' => true]], $existingLogiciel->getSoftwareType());
        $this->assertFalse($existingLogiciel->getHasExpertReferent());
        $this->assertEquals('GPL-3.0', $existingLogiciel->getLicense());
        $comptoirDuLibreSoftware = $existingLogiciel->getComptoirDuLibreSoftware();
        $this->assertIsArray($comptoirDuLibreSoftware->getProviders());
        $this->assertIsArray($comptoirDuLibreSoftware->getUsers());
    }
                
    public function testDeleteLogiciel()
    {
        // Créez un logiciel fictif pour le test
        $logiciel = self::createLogicielMock('653bac764ebbb5ee8097e742');

    
        // Créez un mock pour le DocumentRepository
        $documentRepositoryMock = $this->createMock(DocumentRepository::class);
        $documentRepositoryMock->method('find')->willReturn($logiciel);
    
        // Configurez le mock du DocumentManager pour renvoyer le mock du DocumentRepository
        $this->documentManagerMock->method('getRepository')->willReturn($documentRepositoryMock);
    
        // Créez une requête fictive avec un token JWT valide
        $request = new Request();
        $request->cookies->set('auth_token', 'valid_token');
    
        // Appelez la méthode deleteLogiciel en passant le DocumentManager comme deuxième argument
        $response = $this->apiController->deleteLogiciel('653bac764ebbb5ee8097e742', $this->documentManagerMock, $request);
    
        // Vérifiez que la réponse est un JsonResponse avec un statut HTTP 200 (OK)
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        // Vérifiez que le contenu de la réponse indique que le logiciel a été supprimé avec succès
        $content = json_decode($response->getContent(), true);
        $this->assertEquals("Logiciel supprimé avec succès", $content);
    }
                
    
    
}
