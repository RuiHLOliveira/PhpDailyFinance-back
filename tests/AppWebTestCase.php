<?php

namespace App\Tests;

use App\Entity\User;
use App\Entity\Conta;
use DateTimeImmutable;
use App\Entity\TipoMovimento;
use App\Entity\ClasseMovimento;
use App\Entity\InvitationToken;
use App\Entity\Movimento;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AppWebTestCase extends WebTestCase
{

    protected $token;
    protected $refreshToken;

    protected function request($method, $uri, $data){
        $headers = [];
        if($this->token != null){
            $headers['HTTP_AUTHORIZATION'] = $this->token;
        }
        $this->httpClient->jsonRequest($method, $uri, $data, $headers);
        $response = $this->httpClient->getResponse();
        $responseData = json_decode($response->getContent());
        if($responseData != null) $response->setData($responseData);
        return [$response,$responseData];
    }

    /**
     * @return InvitationToken
     */
    protected function serviceCreateInvitationToken($dados = []){

        $invitationToken = new InvitationToken();
        $invitationToken->setInvitationToken(isset($dados['token']) ? $dados['token'] : '123456');
        $invitationToken->setEmail(isset($dados['email']) ? $dados['email'] : null);

        if(isset($dados['active'])) {
            $invitationToken->setActive($dados['active']);
        }
        
        // $this->entityManager = $this->doctrine->getManager();
        $this->entityManager->persist($invitationToken);
        $this->entityManager->flush();
        return $invitationToken;
    }

    /**
     * @return InvitationToken
     */
    protected function serviceCreateUser($dados = []){

        $token = $this->serviceCreateInvitationToken();
        $user = new User();
        $user->setEmail('rui@rui');
        $user->setPassword(isset($dados['password']) ? $dados['password'] : '123456');
        $user = $this->authService->registerUser($user, $token->getInvitationToken());
        // $this->entityManager = $this->doctrine->getManager();
        $this->entityManager->persist($user);
        $this->entityManager->flush();
        return $user;
    }

    protected function serviceLoggedInUser(){
        $this->user = $this->serviceCreateUser();
        $data = [
            'password' => '123456',
            'email' => $this->user->getEmail()
        ];
        [$response, $json] = $this->request('POST','/auth/login', $data);
        $this->token = $json->token;
        $this->refreshToken = $json->refreshToken;
        return $json;
    }

    protected function haveInDatabaseConta($dados = []){
        $nome = isset($dados['nome']) ? $dados['nome'] : 'Conta 1';
        $saldo = isset($dados['saldo']) ? $dados['saldo'] : '0';
        $conta = new Conta();
        $conta->setNome($nome);
        $conta->setSaldo($saldo);
        return $this->contasService->create($conta, $this->user);
    }

    protected function haveInDatabaseTipoMovimento($dados =[]){
        $nome = isset($dados['nome']) ? $dados['nome'] : 'tipo movimento 1';
        $tipomovimento = new TipoMovimento();
        $tipomovimento->setNome($nome);
        return $this->tipoMovimentosService->create($tipomovimento, $this->user);
    }
    
    protected function haveInDatabaseClasseMovimento($dados = []){
        $nome = isset($dados['nome']) ? $dados['nome'] : 'classe movimento 1';
        $classeMovimento = new ClasseMovimento();
        $classeMovimento->setNome($nome);
        return $this->classeMovimentosService->create($classeMovimento, $this->user);
    }
    
    protected function haveInDatabaseMovimento($dados){
        $descricao = isset($dados['descricao']) ? $dados['descricao'] : 'movimento 1';
        $valor = isset($dados['valor']) ? $dados['valor'] : '100.00';
        $dataMovimento = isset($dados['dataMovimento']) ? $dados['dataMovimento'] : '2022-01-01';
        $descricao = isset($dados['descricao']) ? $dados['descricao'] : 'movimento 1';

        $conta = $dados['conta'];
        $classeMovimento = isset($dados['classeMovimento']) ? $dados['classeMovimento'] : null;
        $tipoMovimento = $dados['tipoMovimento'];

        $dataMovimento = new DateTimeImmutable($dataMovimento);

        $movimento = new Movimento();
        $movimento->setDescricao($descricao);
        $movimento->setValor($valor);
        $movimento->setDataMovimento($dataMovimento);
        $movimento->setDescricao($descricao);

        $movimento->setConta($conta);
        $movimento->setClasse($classeMovimento);
        $movimento->setTipomovimento($tipoMovimento);

        return $this->movimentosService->create($movimento, $this->user);
    }

    protected function grabOneFromDatabase($class, $filter){
        $this->entityManager->clear();
        return $this->entityManager->getRepository($class)->findOneBy($filter);
    }

    protected function grabFromDatabase($class, $filter){
        $this->entityManager->clear();
        return $this->entityManager->getRepository($class)->findBy($filter);
    }

}
