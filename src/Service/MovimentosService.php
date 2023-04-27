<?php

namespace App\Service;

use App\Entity\User;
use App\Entity\Conta;
use DateTimeImmutable;
use App\Entity\Movimento;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\VarDumper\VarDumper;

class MovimentosService
{
    
    private $doctrine;
    private $encoder;

    public function __construct(
        ManagerRegistry $doctrine,
        UserPasswordEncoderInterface $encoder,
        ContasService $contasService
    ) {
        $this->doctrine = $doctrine;
        $this->encoder = $encoder;
        $this->contasService = $contasService;
    }

    private function processAuxFilterData(array $auxFilter, array $movimentos){
        if(isset($auxFilter['dataMovimento'])) {
            $movimentos = array_filter($movimentos, function( $movimento ) use ($auxFilter) {
                $dataMovimento = $movimento->getDataMovimento()->format('Y-m-d');
                if($dataMovimento >= $auxFilter['dataMovimento']['dataIni']
                && $dataMovimento <= $auxFilter['dataMovimento']['dataFim']){
                    return true;
                }
                return false;
            });
        }
        $movimentos = array_values($movimentos);
        return $movimentos;
    }

    private function processAuxFilter(array $auxFilter, array $movimentos) {
        
        $movimentos = $this->processAuxFilterData($auxFilter, $movimentos);

        return $movimentos;
    }

    public function index(User $usuario, Conta $conta = null, array $filter = [], array $auxFilter = [], array $orderBy = null) {

        try {
            $filter['usuario'] = $usuario;
            $filter['deleted_at'] = null;
            if($conta != null) $filter['conta'] = $conta;
            $movimentos = $this->doctrine->getRepository(Movimento::class)->findBy($filter, $orderBy);
            $movimentos = $this->processAuxFilter($auxFilter, $movimentos);
            return $movimentos;
        } catch (\Exception $e) {
            throw $e;
        }
    }


    public function find(int $id, User $usuario): Movimento {
        try {
            $movimento = $this->doctrine->getRepository(Movimento::class)->find($id);
            if($movimento == null) {
                throw new NotFoundHttpException('Movimento não encontrada');
            }
            return $movimento;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function create(Movimento $movimento, User $usuario)
    {
        try {
            $entityManager = $this->doctrine->getManager();
            $entityManager->getConnection()->beginTransaction();

            $movimento->setCreatedAt(new DateTimeImmutable());
            $movimento->setUsuario($usuario);
            
            $entityManager->persist($movimento);
            $entityManager->flush();

            $this->atualizaSaldoTotalConta($movimento->getConta(), $usuario);

            $entityManager->getConnection()->commit();

            return $movimento;

        } catch (\Throwable $th) {
            $entityManager->getConnection()->rollback();
            throw $th;
        }
    }

    public function update(Movimento $movimento, User $usuario) {
        try {
            $entityManager = $this->doctrine->getManager();
            $entityManager->getConnection()->beginTransaction();

            $movimento->setUpdatedAt(new DateTimeImmutable());

            $entityManager->persist($movimento);
            $entityManager->flush();
            
            if($movimento->isValorDirty()){
                $this->atualizaSaldoTotalConta($movimento->getConta(), $usuario);
            }

            $entityManager->getConnection()->commit();

            return $movimento;

        } catch (\Throwable $th) {
            $entityManager->getConnection()->rollback();
            throw $th;
        }
    }

    public function delete(Movimento $movimento, User $usuario) {
        
        try {
            $entityManager = $this->doctrine->getManager();
            $entityManager->getConnection()->beginTransaction();

            $movimento->setDeletedAt(new DateTimeImmutable());
            
            $entityManager->persist($movimento);
            $entityManager->flush();

            $conta = $movimento->getConta();

            VarDumper::dump($movimento->getValor());
            VarDumper::dump($conta->getSaldo());
            $conta->setSaldo($conta->getSaldo() + $movimento->getValor());
            VarDumper::dump($conta->getSaldo());
            
            $this->contasService->update($conta, $usuario);
            $entityManager->flush();

            // $this->atualizaSaldoTotalConta($movimento->getConta(), $usuario);

            $entityManager->getConnection()->commit();

            return $movimento;

        } catch (\Throwable $th) {
            $entityManager->getConnection()->rollback();
            throw $th;
        }
    }

    protected function atualizaSaldoTotalConta (Conta $conta, User $usuario) {
        try {
            $entityManager = $this->doctrine->getManager();
            $entityManager->getConnection()->beginTransaction();

            $listaMovimentos = $this->index($usuario, $conta, [], [], null);
            $total = 0;
            /**
             * @var \App\Entity\Movimento $mov
             */
            foreach ($listaMovimentos as $key => $mov) {
                VarDumper::dump($mov->getValor());
                $total += $mov->getValor();
            }

            $conta->setSaldo($total);

            $this->contasService->update($conta, $usuario);

            $entityManager->flush();
            $entityManager->getConnection()->commit();

            return $conta;

        } catch (\Throwable $th) {
            $entityManager->getConnection()->rollback();
            throw $th;
        }
    }
}