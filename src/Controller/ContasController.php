<?php

namespace App\Controller;

use App\Entity\Conta;
use DateTimeImmutable;
use App\Form\Validator;
use App\Service\ContasService;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ContasController extends AbstractController
{

    /**
     * @var \App\Service\ContasService;
     */
    private $contasService;

    public function __construct(ContasService $contasService)
    {
        $this->contasService = $contasService;
    }

    /**
     * @Route("/contas", name="app_contas_list", methods={"GET","HEAD"})
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $usuario = $this->getUser();

            $orderBy = null;
            if($request->query->get('orderBy') != null){
                $orderBy = $request->query->get('orderBy');
                $orderBy = explode(',', $orderBy);
                $orderBy = [$orderBy[0] => $orderBy[1]];
            }

            $filter = [];
            
            $contas = $this->contasService->index($usuario, $filter, $orderBy);

            return new JsonResponse($contas);
        } catch (\Exception $e) {
            return new JsonResponse(['message' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    
    /**
     * @Route("/contas/{id}", name="app_contas_find", methods={"GET","HEAD"})
     */
    public function find(Request $request, int $id): JsonResponse
    {
        try {
            $usuario = $this->getUser();

            $conta = $this->contasService->find($id, $usuario);

            return new JsonResponse($conta);
        } catch (\Exception $e) {
            return new JsonResponse(['message' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @Route("/contas", name="app_contas_create", methods={"POST"})
     */
    public function create(Request $request): JsonResponse
    {
        try {
            $requestObj = json_decode($request->getContent());

            $usuario = $this->getUser();

            Validator::validate($requestObj, 'nome', Validator::STRING);
            Validator::validate($requestObj, 'saldo', Validator::NUMBER);

            // $dataCompleta = new DateTimeImmutable($requestObj->dataCompleta);

            $conta = new Conta();
            $conta->setNome($requestObj->nome);
            $conta->setSaldo($requestObj->saldo);

            $contas = $this->contasService->create($conta, $usuario);

            return new JsonResponse($contas, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return new JsonResponse(['message' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @Route("/contas/{id}", name="app_contas_update", methods={"PUT"})
     */
    public function update(Request $request, int $id)
    {
        try {
            $requestObj = json_decode($request->getContent());
            $usuario = $this->getUser();

            Validator::validate($requestObj, 'nome', Validator::STRING);

            $conta = $this->contasService->find($id, $usuario);
            $conta->setNome($requestObj->nome);
            
            $conta = $this->contasService->update($conta, $usuario);

            return new JsonResponse($conta, Response::HTTP_OK);
        } catch (\Exception $e) {
            return new JsonResponse(['message' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @Route("/contas/{id}", name="app_contas_delete", methods={"DELETE"})
     */
    public function delete(Request $request, int $id)
    {
        try {
            $requestObj = json_decode($request->getContent());

            $usuario = $this->getUser();

            $conta = $this->contasService->find($id, $usuario);
            
            $conta = $this->contasService->delete($conta, $usuario);

            return new JsonResponse($conta, Response::HTTP_OK);
        } catch (\Exception $e) {
            return new JsonResponse(['message' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}