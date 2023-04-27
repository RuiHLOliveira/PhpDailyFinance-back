<?php

namespace App\Tests;

use App\Entity\Movimento;
use App\Service\AuthService;
use App\Service\ContasService;
use App\Service\MovimentosService;
use App\Service\TipoMovimentosService;
use DateTimeImmutable;
use Doctrine\Persistence\ManagerRegistry;

class MovimentosTest extends AppWebTestCase
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
        $this->tipoMovimentosService = $kernel->getContainer()->get(TipoMovimentosService::class);
        $this->movimentosService = $kernel->getContainer()->get(MovimentosService::class);
    }
    
    protected function tearDown(): void
    {
        parent::tearDown();
        // doing this is recommended to avoid memory leaks
        // $this->entityManager->clear();
        // $this->entityManager = null;
    }

    public function testNaoPodeListarMovimentosNaoLogado(): void
    {
        [$response, $json] = $this->request('GET', '/movimentos', []);

        $this->assertResponseStatusCodeSame(401);
    }

    public function testListarMovimentos(): void
    {
        $this->serviceLoggedInUser();
        
        $conta = $this->haveInDatabaseConta();
        $tipoMovimento = $this->haveInDatabaseTipoMovimento();
        $dados = [
            'conta' => $conta,
            'tipoMovimento' => $tipoMovimento,
        ];

        $movimentos[] = $this->haveInDatabaseMovimento($dados);
        $movimentos[] = $this->haveInDatabaseMovimento($dados);

        [$response, $data] = $this->request('GET', '/movimentos', []);

        $this->assertResponseStatusCodeSame(200);
        $this->assertCount(count($movimentos), $data);

        foreach ($data as $key => $movimento) {
            $this->assertEquals($movimentos[$key]->getId(), $movimento->id);
            $this->assertEquals($movimentos[$key]->getDescricao(), $movimento->descricao);
            $this->assertEquals($movimentos[$key]->getValor(), $movimento->valor);
            $data = new DateTimeImmutable($movimento->dataMovimento->date);
            $data = $data->format('Y-m-d');
            $this->assertEquals($movimentos[$key]->getDataMovimento()->format('Y-m-d'), $data);
            $this->assertEquals($movimentos[$key]->getConta()->getId(), $movimento->conta->id);
            $this->assertEquals($movimentos[$key]->getTipomovimento()->getId(), $movimento->tipoMovimento->id);
        }
    }

    public function testCriarMovimento(): void
    {
        $this->serviceLoggedInUser();
        $conta = $this->haveInDatabaseConta();
        $tipoMovimento = $this->haveInDatabaseTipoMovimento();
        $dados = [
            'descricao' => 'movimento A',
            'valor' => '100.00',
            'dataMovimento' => '2022-01-01',
            'conta' => $conta->getId(),
            'tipoMovimento' => $tipoMovimento->getId(),
        ];
        [$response, $json] = $this->request('POST', '/movimentos', $dados);

        $this->assertResponseStatusCodeSame(201);
        $movimentoDb = $this->entityManager->getRepository(Movimento::class)->findOneBy(['id' => $json->id, 'usuario' => $this->user]);
        $this->assertNotNull($movimentoDb);
        
        $this->assertEquals($dados['descricao'], $json->descricao);

        $this->assertEquals($movimentoDb->getId(), $json->id);
        $this->assertEquals($movimentoDb->getDescricao(), $json->descricao);
    }

    public function testEditarClasseMovimento(): void
    {
        $this->serviceLoggedInUser();

        $conta = $this->haveInDatabaseConta();
        $tipoMovimento = $this->haveInDatabaseTipoMovimento();
        $dados = [
            'conta' => $conta,
            'tipoMovimento' => $tipoMovimento,
        ];

        $movimentos[] = $this->haveInDatabaseMovimento($dados);
        $movimentos[] = $this->haveInDatabaseMovimento($dados);

        $movimento = $movimentos[0];
        $dados = [
            'descricao' => 'novo descricao',
            'valor' => 3.33,
            'dataMovimento' => date('Y-m-d'),
            'tipoMovimento' => $tipoMovimento->getId(),
        ];

        [$response, $json] = $this->request('PUT', '/movimentos/'. $movimento->getId(), $dados);

        $this->assertResponseStatusCodeSame(200);
        $movimentoDb = $this->grabOneFromDatabase(Movimento::class, ['id' => $movimentos[0]->getId(), 'usuario' => $this->user]);
        $this->assertEquals($dados['descricao'], $movimentoDb->getDescricao());
        $this->assertEquals($dados['valor'], $movimentoDb->getValor());
    }

    public function testDeletarMovimento(): void
    {
        $this->serviceLoggedInUser();
        
        $conta = $this->haveInDatabaseConta();
        $tipoMovimento = $this->haveInDatabaseTipoMovimento();
        $dados = [
            'conta' => $conta,
            'tipoMovimento' => $tipoMovimento,
        ];

        $movimentos[] = $this->haveInDatabaseMovimento($dados);
        $movimentos[] = $this->haveInDatabaseMovimento($dados);

        [$response, $data] = $this->request('DELETE', "/movimentos/" . $movimentos[0]->getId(), []);

        $this->assertResponseStatusCodeSame(200);
        
        $movimentoDeletadoDb = $this->grabOneFromDatabase(Movimento::class, ['id' => $movimentos[0]->getId(), 'usuario' => $this->user]);
        
        $this->assertNotEquals(null, $movimentoDeletadoDb->getDeletedAt());
    }
}
