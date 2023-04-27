<?php

namespace App\Tests;

use App\Entity\ClasseMovimento;
use App\Service\AuthService;
use App\Service\ClasseMovimentosService;
use Doctrine\Persistence\ManagerRegistry;

class ClasseMovimentosTest extends AppWebTestCase
{

    /**
     * @var ManagerRegistry
     */
    protected $doctrine;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $entityManager;

    /**
     * @var \Symfony\Bundle\FrameworkBundle\KernelBrowser;
     */
    protected $httpClient;

    protected function setUp(): void
    {
        $this->httpClient = static::createClient();
        $kernel = self::bootKernel();
        $this->doctrine = $kernel->getContainer()->get('doctrine');
        $this->entityManager = $this->doctrine->getManager();
        
        $this->authService = $kernel->getContainer()->get(AuthService::class);
        $this->classeMovimentosService = $kernel->getContainer()->get(ClasseMovimentosService::class);
    }
    
    protected function tearDown(): void
    {
        parent::tearDown();
        // doing this is recommended to avoid memory leaks
        // $this->entityManager->clear();
        // $this->entityManager = null;
    }

    public function testNaoPodeListarClasseMovimentosNaoLogado(): void
    {
        [$response, $json] = $this->request('GET', '/classemovimentos', []);

        $this->assertResponseStatusCodeSame(401);
    }

    public function testListarClasseMovimentos(): void
    {
        $this->serviceLoggedInUser();
        $classeMovimentos[] = $this->HaveInDatabaseClasseMovimento();
        $classeMovimentos[] = $this->HaveInDatabaseClasseMovimento();

        [$response, $data] = $this->request('GET', '/classemovimentos', []);

        $this->assertResponseStatusCodeSame(200);
        $this->assertCount(count($classeMovimentos), $data);

        foreach ($data as $key => $classeMovimento) {
            $this->assertEquals($classeMovimentos[$key]->getId(), $classeMovimento->id);
            $this->assertEquals($classeMovimentos[$key]->getNome(), $classeMovimento->nome);
        }
    }

    public function testCriarClasseMovimento(): void
    {
        $this->serviceLoggedInUser();
        $dados = [
            'nome' => 'classe movimento A',
        ];
        [$response, $json] = $this->request('POST', '/classemovimentos', $dados);

        $this->assertResponseStatusCodeSame(201);
        $classeMovimentoDB = $this->entityManager->getRepository(ClasseMovimento::class)->findOneBy(['id' => $json->id, 'usuario' => $this->user]);
        $this->assertNotNull($classeMovimentoDB);
        
        $this->assertEquals($dados['nome'], $json->nome);

        $this->assertEquals($classeMovimentoDB->getId(), $json->id);
        $this->assertEquals($classeMovimentoDB->getNome(), $json->nome);
    }

    public function testEditarClasseMovimento(): void
    {
        $this->serviceLoggedInUser();
        $classeMovimentos[] = $this->HaveInDatabaseClasseMovimento();
        $classeMovimentos[] = $this->HaveInDatabaseClasseMovimento();
        $classeMovimento = $classeMovimentos[0];
        $dados = [
            'nome' => 'novo nome'
        ];

        [$response, $json] = $this->request('PUT', '/classemovimentos/'. $classeMovimento->getId(), $dados);

        $this->assertResponseStatusCodeSame(200);
        $classeMovimentoDb = $this->grabOneFromDatabase(ClasseMovimento::class, ['id' => $classeMovimentos[0]->getId(), 'usuario' => $this->user]);
        $this->assertEquals($dados['nome'], $classeMovimentoDb->getNome());
    }

    public function testApagarClasseMovimento(): void
    {
        $this->serviceLoggedInUser();
        $classeMovimentos[] = $this->HaveInDatabaseClasseMovimento();
        $classeMovimentos[] = $this->HaveInDatabaseClasseMovimento();
        
        [$response, $json] = $this->request('DELETE', '/classemovimentos/'.$classeMovimentos[0]->getId(), []);

        $this->assertResponseStatusCodeSame(200);
        $classeDeletadaDb = $this->grabOneFromDatabase(ClasseMovimento::class, ['id' => $classeMovimentos[0]->getId(), 'usuario' => $this->user]);
        $this->assertNotEquals(null, $classeDeletadaDb->getDeletedAt());
    }
}
