<?php

namespace App\Tests;

use App\Entity\Conta;
use App\Service\AuthService;
use App\Service\ContasService;
use Doctrine\Persistence\ManagerRegistry;

class ContasTest extends AppWebTestCase
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
        $this->contasService = $kernel->getContainer()->get(ContasService::class);
    }
    
    protected function tearDown(): void
    {
        parent::tearDown();
        // doing this is recommended to avoid memory leaks
        // $this->entityManager->clear();
        // $this->entityManager = null;
    }

    public function testNaoPodeListarContasNaoLogado(): void
    {
        [$response, $json] = $this->request('GET', '/contas', []);

        $this->assertResponseStatusCodeSame(401);
    }

    public function testListarContas(): void
    {
        $this->serviceLoggedInUser();
        $contas[] = $this->haveInDatabaseConta();
        $contas[] = $this->haveInDatabaseConta();

        [$response, $data] = $this->request('GET', '/contas', []);

        $this->assertResponseStatusCodeSame(200);
        $this->assertCount(count($contas), $data);

        foreach ($data as $key => $conta) {
            $this->assertEquals($contas[$key]->getId(), $conta->id);
            $this->assertEquals($contas[$key]->getNome(), $conta->nome);
        }
    }

    public function testCriarConta(): void
    {
        $this->serviceLoggedInUser();
        $dados = [
            'nome' => 'conta A',
            'saldo' => '100.00',
        ];
        [$response, $json] = $this->request('POST', '/contas', $dados);

        $this->assertResponseStatusCodeSame(201);
        $contaDb = $this->entityManager->getRepository(Conta::class)->findOneBy(['id' => $json->id, 'usuario' => $this->user]);
        $this->assertNotNull($contaDb);
        
        $this->assertEquals($dados['nome'], $json->nome);
        $this->assertEquals($dados['saldo'], $json->saldo);

        $this->assertEquals($contaDb->getId(), $json->id);
        $this->assertEquals($contaDb->getNome(), $json->nome);
        $this->assertEquals($contaDb->getSaldo(), $json->saldo);
    }

    public function testApagarClasseMovimento(): void
    {
        $this->serviceLoggedInUser();
        $contas[] = $this->haveInDatabaseConta();
        $contas[] = $this->haveInDatabaseConta();
        
        [$response, $json] = $this->request('DELETE', '/contas/'.$contas[0]->getId(), []);

        $this->assertResponseStatusCodeSame(200);
        $contaDeletadaDb = $this->grabOneFromDatabase(Conta::class, ['id' => $contas[0]->getId(), 'usuario' => $this->user]);
        $this->assertNotEquals(null, $contaDeletadaDb->getDeletedAt());
    }
    
    public function testEditarConta(): void
    {
        $this->serviceLoggedInUser();
        $contas[] = $this->haveInDatabaseConta();
        $contas[] = $this->haveInDatabaseConta();
        $conta = $contas[0];
        $dados = [
            'nome' => 'novo nome'
        ];
        
        [$response, $json] = $this->request('PUT', '/contas/'.$conta->getId(), $dados);

        $this->assertResponseStatusCodeSame(200);
        $contaDb = $this->grabOneFromDatabase(Conta::class, ['id' => $contas[0]->getId(), 'usuario' => $this->user]);
        $this->assertEquals($dados['nome'], $contaDb->getNome());
    }
}
