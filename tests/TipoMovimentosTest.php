<?php

namespace App\Tests;

use App\Entity\Conta;
use App\Entity\TipoMovimento;
use App\Service\AuthService;
use App\Service\ContasService;
use App\Service\TipoMovimentosService;
use Doctrine\Persistence\ManagerRegistry;

class TipoMovimentosTest extends AppWebTestCase
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
        $this->tipoMovimentosService = $kernel->getContainer()->get(TipoMovimentosService::class);
    }
    
    protected function tearDown(): void
    {
        parent::tearDown();
        // doing this is recommended to avoid memory leaks
        // $this->entityManager->clear();
        // $this->entityManager = null;
    }

    public function testNaoPodeListarTipoMovimentosNaoLogado(): void
    {
        [$response, $json] = $this->request('GET', '/tipomovimentos', []);

        $this->assertResponseStatusCodeSame(401);
    }

    public function testListarTipoMovimentos(): void
    {
        $this->serviceLoggedInUser();
        $tipoMovimentos[] = $this->haveInDatabaseTipoMovimento();
        $tipoMovimentos[] = $this->haveInDatabaseTipoMovimento();

        [$response, $data] = $this->request('GET', '/tipomovimentos', []);

        $this->assertResponseStatusCodeSame(200);
        $this->assertCount(count($tipoMovimentos), $data);

        foreach ($data as $key => $tipoMovimento) {
            $this->assertEquals($tipoMovimentos[$key]->getId(), $tipoMovimento->id);
            $this->assertEquals($tipoMovimentos[$key]->getNome(), $tipoMovimento->nome);
        }
    }

    public function testCriarTipoMovimento(): void
    {
        $this->serviceLoggedInUser();
        $dados = [
            'nome' => 'tipo movimento A',
        ];
        [$response, $json] = $this->request('POST', '/tipomovimentos', $dados);

        $this->assertResponseStatusCodeSame(201);
        $tipoMovimentoDb = $this->entityManager->getRepository(TipoMovimento::class)->findOneBy(['id' => $json->id, 'usuario' => $this->user]);
        $this->assertNotNull($tipoMovimentoDb);
        
        $this->assertEquals($dados['nome'], $json->nome);

        $this->assertEquals($tipoMovimentoDb->getId(), $json->id);
        $this->assertEquals($tipoMovimentoDb->getNome(), $json->nome);
    }

    public function testEditarTipoMovimento(): void
    {
        $this->serviceLoggedInUser();
        $tipoMovimentos[] = $this->haveInDatabaseTipoMovimento();
        $tipoMovimentos[] = $this->haveInDatabaseTipoMovimento();
        $tipoMovimento = $tipoMovimentos[0];
        $dados = [
            'nome' => 'novo nome'
        ];
        
        [$response, $json] = $this->request('PUT', '/tipomovimentos/'.$tipoMovimento->getId(), $dados);

        $this->assertResponseStatusCodeSame(200);
        $tipoMovimentoDb = $this->grabOneFromDatabase(TipoMovimento::class, ['id' => $tipoMovimentos[0]->getId(), 'usuario' => $this->user]);
        $this->assertEquals($dados['nome'], $tipoMovimentoDb->getNome());
    }

    public function testApagarClasseMovimento(): void
    {
        $this->serviceLoggedInUser();
        $tipoMovimentos[] = $this->haveInDatabaseTipoMovimento();
        $tipoMovimentos[] = $this->haveInDatabaseTipoMovimento();
        
        [$response, $json] = $this->request('DELETE', '/tipomovimentos/'.$tipoMovimentos[0]->getId(), []);

        $this->assertResponseStatusCodeSame(200);
        $tipoDeletadaDb = $this->grabOneFromDatabase(TipoMovimento::class, ['id' => $tipoMovimentos[0]->getId(), 'usuario' => $this->user]);
        $this->assertNotEquals(null, $tipoDeletadaDb->getDeletedAt());
    }
}
