<?php

namespace App\Controller;

use App\Entity\ClasseMovimento;
use App\Entity\Conta;
use App\Entity\Movimento;
use App\Entity\TipoMovimento;
use DateTimeZone;
use DateTimeImmutable;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

class BackupController extends AbstractController
{
    /**
     * @Route("/backup/export", name="backupExport")
     */
    public function index(Request $request, ManagerRegistry $doctrine): JsonResponse
    {
        try {
            $usuario = $this->getUser();

            $classeMovimentos = $doctrine->getRepository(ClasseMovimento::class)->findBy([
                'usuario' => $usuario
            ]);
            $tipoMovimentos = $doctrine->getRepository(TipoMovimento::class)->findBy([
                'usuario' => $usuario
            ]);
            $contas = $doctrine->getRepository(Conta::class)->findBy([
                'usuario' => $usuario
            ]);
            $movimentos = $doctrine->getRepository(Movimento::class)->findBy([
                'usuario' => $usuario
            ]);

            $array = compact('classeMovimentos', 'tipoMovimentos', 'contas', 'movimentos');

            return new JsonResponse($array, 200);
        } catch (\Exception $e) {
            return new JsonResponse(['message' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @Route("/backup/import", name="backupImport")
     */
    public function import(Request $request, ManagerRegistry $doctrine): JsonResponse
    {
        try {
            $usuario = $this->getUser();
            $requestData = $request->getContent();
            $requestData = json_decode($requestData);
            
            $entityManager = $doctrine->getManager();
            $entityManager->getConnection()->beginTransaction();


            $contas = $doctrine->getRepository(Conta::class)->findBy([
                'usuario' => $usuario
            ]);
            $tipoMovimentos = $doctrine->getRepository(TipoMovimento::class)->findBy([
                'usuario' => $usuario
            ]);
            $classeMovimentos = $doctrine->getRepository(ClasseMovimento::class)->findBy([
                'usuario' => $usuario
            ]);
            foreach ($contas as $key => $conta) {
                foreach ($conta->getMovimentos() as $key => $movimento) {
                    $entityManager->remove($movimento);
                }
                $entityManager->remove($conta);
            }
            foreach ($tipoMovimentos as $key => $tipo) {
                $entityManager->remove($tipo);
            }
            foreach ($classeMovimentos as $key => $classe) {
                $entityManager->remove($classe);
            }

            $listaClasses = [];
            $listaTipos = [];
            $listaContas = [];

            foreach ($requestData->classeMovimentos as $key => $classe) {
                // $classe->nome .= ' bkp'; //padrão backup
                $classeObj = new ClasseMovimento();
                // $classeObj->setId($classe->id);
                $classeObj->setNome($classe->nome);

                $timezone = new DateTimeZone($classe->createdAt->timezone);
                $createdAt = new DateTimeImmutable($classe->createdAt->date, $timezone);
                $classeObj->setCreatedAt($createdAt);
                if($classe->updatedAt != null) {
                    $timezone = new DateTimeZone($classe->updatedAt->timezone);
                    $updatedAt = new DateTimeImmutable($classe->updatedAt->date, $timezone);
                    $classeObj->setUpdatedAt($updatedAt);
                }
                if($classe->deletedAt != null) {
                    $timezone = new DateTimeZone($classe->deletedAt->timezone);
                    $deletedAt = new DateTimeImmutable($classe->deletedAt->date, $timezone);
                    $classeObj->setDeletedAt($deletedAt);
                }

                $classeObj->setUsuario($usuario);
                $entityManager->persist($classeObj);
                $classeObj->getId();
                $listaClasses[] = $classeObj;
            }
            
            foreach ($requestData->tipoMovimentos as $key => $tipo) {
                // $tipo->nome .= ' bkp'; //padrão backup
                $tipoObj = new TipoMovimento();
                // $tipoObj->setId($tipo->id);
                $tipoObj->setNome($tipo->nome);

                $timezone = new DateTimeZone($tipo->createdAt->timezone);
                $createdAt = new DateTimeImmutable($tipo->createdAt->date, $timezone);
                $tipoObj->setCreatedAt($createdAt);
                if($tipo->updatedAt != null) {
                    $timezone = new DateTimeZone($tipo->updatedAt->timezone);
                    $updatedAt = new DateTimeImmutable($tipo->updatedAt->date, $timezone);
                    $tipoObj->setUpdatedAt($updatedAt);
                }
                if($tipo->deletedAt != null) {
                    $timezone = new DateTimeZone($tipo->deletedAt->timezone);
                    $deletedAt = new DateTimeImmutable($tipo->deletedAt->date, $timezone);
                    $tipoObj->setDeletedAt($deletedAt);
                }

                $tipoObj->setUsuario($usuario);
                $entityManager->persist($tipoObj);
                $tipoObj->getId();
                $listaTipos[] = $tipoObj;
            }

            foreach ($requestData->contas as $key => $conta) {
                // $conta->nome .= ' bkp'; //padrão backup
                $contaObj = new Conta();
                $contaObj->setNome($conta->nome);
                $contaObj->setSaldo($conta->saldo);

                $timezone = new DateTimeZone($conta->createdAt->timezone);
                $createdAt = new DateTimeImmutable($conta->createdAt->date, $timezone);
                $contaObj->setCreatedAt($createdAt);
                if($conta->updatedAt != null) {
                    $timezone = new DateTimeZone($conta->updatedAt->timezone);
                    $updatedAt = new DateTimeImmutable($conta->updatedAt->date, $timezone);
                    $contaObj->setUpdatedAt($updatedAt);
                }
                if($conta->deletedAt != null) {
                    $timezone = new DateTimeZone($conta->deletedAt->timezone);
                    $deletedAt = new DateTimeImmutable($conta->deletedAt->date, $timezone);
                    $contaObj->setDeletedAt($deletedAt);
                }

                $contaObj->setUsuario($usuario);
                $entityManager->persist($contaObj);
                $contaObj->getId();
                $listaContas[] = $contaObj;
            }

            foreach ($requestData->movimentos as $key => $movimento) {
                // $movimento->descricao .= ' bkp'; //padrão backup
                $movimentoObj = new Movimento();
                $movimentoObj->setDescricao($movimento->descricao);
                $movimentoObj->setValor($movimento->valor);

                $timezone = new DateTimeZone($movimento->createdAt->timezone);
                $dataMovimento = new DateTimeImmutable($movimento->dataMovimento->date, $timezone);
                $movimentoObj->setDataMovimento($dataMovimento);

                $timezone = new DateTimeZone($movimento->createdAt->timezone);
                $createdAt = new DateTimeImmutable($movimento->createdAt->date, $timezone);
                $movimentoObj->setCreatedAt($createdAt);
                if($movimento->updatedAt != null) {
                    $timezone = new DateTimeZone($movimento->updatedAt->timezone);
                    $updatedAt = new DateTimeImmutable($movimento->updatedAt->date, $timezone);
                    $movimentoObj->setUpdatedAt($updatedAt);
                }
                if($movimento->deletedAt != null) {
                    $timezone = new DateTimeZone($movimento->deletedAt->timezone);
                    $deletedAt = new DateTimeImmutable($movimento->deletedAt->date, $timezone);
                    $movimentoObj->setDeletedAt($deletedAt);
                }

                if($movimento->classeMovimento != null){
                    $classeRelacao = array_filter($listaClasses, function ($c) use ($movimento) {
                        return $movimento->classeMovimento->nome == $c->getNome();
                    });
                    $classeRelacao = array_values($classeRelacao)[0];
                    $movimentoObj->setClasse($classeRelacao);
                }

                $tipoRelacao = array_filter($listaTipos, function ($t) use ($movimento) {
                    return $movimento->tipoMovimento->nome == $t->getNome();
                });
                $tipoRelacao = array_values($tipoRelacao)[0];
                $movimentoObj->setTipomovimento($tipoRelacao);

                $contaRelacao = array_filter($listaContas, function ($c) use ($movimento) {
                    return $movimento->conta->nome == $c->getNome();
                });
                $contaRelacao = array_values($contaRelacao)[0];
                $movimentoObj->setConta($contaRelacao);

                $movimentoObj->setUsuario($usuario);
                $entityManager->persist($movimentoObj);
                $movimentoObj->getId();
            }

            
            $entityManager->flush();
            $entityManager->getConnection()->commit();
            $mensagem = "Backup successfully restored";
            return new JsonResponse(compact('mensagem'), 200);
            
        } catch (\Exception $e) {
            $entityManager->getConnection()->rollback();
            return new JsonResponse(['message' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
