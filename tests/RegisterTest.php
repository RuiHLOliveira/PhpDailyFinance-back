<?php

namespace App\Tests;

use App\Entity\InvitationToken;
use App\Entity\User;
use DateTimeImmutable;
use App\Repository\UserRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class RegisterTest extends AppWebTestCase //webTestCase
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
    }
    
    protected function tearDown(): void
    {
        parent::tearDown();
        // doing this is recommended to avoid memory leaks
        // $this->entityManager->clear();
        // $this->entityManager = null;
    }

    public function testRegistrar(): void
    {
        $token = $this->serviceCreateInvitationToken();
        $data = [
            'invitationToken' => $token->getInvitationToken(),
            'password' => '123456',
            'repeatPassword' => '123456',
            'email' => 'rui@rui.com'
        ];
        [$response, $json] = $this->request('POST','/auth/register', $data);
        $this->assertResponseStatusCodeSame(201);
        $userDB = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $json->email]);
        $this->assertNotNull($userDB);
        $this->assertEquals($userDB->getId(), $json->id);
    }

    
    public function testNaoPodeRegistrarSemToken(): void
    {
        $data = [
            'password' => '123456',
            'repeatPassword' => '123456',
            'email' => 'rui@rui.com'
        ];
        [$response, $json] = $this->request('POST','/auth/register', $data);
        $this->assertResponseStatusCodeSame(500);
        $userDB = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $data['email']]);
        $this->assertNull($userDB);
    }

    public function testNaoPodeRegistrarComTokenNaoAtivo(): void
    {
        $token = $this->serviceCreateInvitationToken(['active' => false]);
        $data = [
            'invitationToken' => $token->getInvitationToken(),
            'password' => '123456',
            'repeatPassword' => '123456',
            'email' => 'rui@rui.com'
        ];
        [$response, $json] = $this->request('POST','/auth/register', $data);
        $this->assertResponseStatusCodeSame(500);
        $userDB = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $data['email']]);
        $this->assertNull($userDB);
    }
    
    public function testNaoPodeRegistrarComTokenNaoExistente(): void
    {
        $token = $this->serviceCreateInvitationToken(['active' => false]);
        $data = [
            'invitationToken' => '321654',
            'password' => '123456',
            'repeatPassword' => '123456',
            'email' => 'rui@rui.com'
        ];
        [$response, $json] = $this->request('POST','/auth/register', $data);
        $this->assertResponseStatusCodeSame(500);
        $userDB = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $data['email']]);
        $this->assertNull($userDB);
    }
    
    public function testNaoPodeRegistrarComTokenParaOutroEmail(): void
    {
        $token = $this->serviceCreateInvitationToken(['email' => 'rui@rui.com.br']);
        $data = [
            'invitationToken' => '321654',
            'password' => '123456',
            'repeatPassword' => '123456',
            'email' => 'rui@rui.com'
        ];
        [$response, $json] = $this->request('POST','/auth/register', $data);
        $this->assertResponseStatusCodeSame(500);
        $userDB = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $data['email']]);
        $this->assertNull($userDB);
    }
}
