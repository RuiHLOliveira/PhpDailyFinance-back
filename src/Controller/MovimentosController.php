<?php

namespace App\Controller;

use App\Entity\Conta;
use DateTimeImmutable;
use App\Form\Validator;
use App\Entity\Movimento;
use App\Entity\TipoMovimento;
use App\Service\ContasService;
use App\Service\MovimentosService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Service\ClasseMovimentosService;
use App\Service\TipoMovimentosService;

class MovimentosController extends AbstractController
{

    /**
     * @var \App\Service\MovimentosService;
     */
    private $movimentosService;

    /**
     * @var \App\Service\ContasService;
     */
    private $contasService;
    
    /**
     * @var \App\Service\ClasseMovimentosService;
     */
    private $classeMovimentosService;
    
    /**
     * @var \App\Service\TipoMovimentosService;
     */
    private $tipoMovimentosService;

    public function __construct(
        MovimentosService $movimentosService,
        ContasService $contasService,
        ClasseMovimentosService $classeMovimentosService,
        TipoMovimentosService $tipoMovimentosService
    ) {
        $this->movimentosService = $movimentosService;
        $this->contasService = $contasService;
        $this->classeMovimentosService = $classeMovimentosService;
        $this->tipoMovimentosService = $tipoMovimentosService;
    }

    /**
     * @Route("/movimentos", name="app_movimentos_list", methods={"GET","HEAD"})
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $usuario = $this->getUser();

            $auxFilter = [];
            if($request->query->get('dataIni') != null && $request->query->get('dataFim') != null){
                $auxFilter['dataMovimento']['dataIni'] = $request->query->get('dataIni');
                $auxFilter['dataMovimento']['dataFim'] = $request->query->get('dataFim');
            }

            $orderBy = null;
            if($request->query->get('orderBy') != null){
                $orderBy = $request->query->get('orderBy');
                $orderBy = explode(',', $orderBy);
                $orderBy = [$orderBy[0] => $orderBy[1]];
            }

            $conta = null;
            if($request->query->get('conta') != null){
                $conta = $request->query->get('conta');
                $conta = $this->contasService->find($conta, $usuario);
            }

            $movimentos = $this->movimentosService->index($usuario, $conta, [], $auxFilter, $orderBy);

            return new JsonResponse($movimentos);
        } catch (\Exception $e) {
            return new JsonResponse(['message' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    
    /**
     * @Route("/movimentos/{id}", name="app_movimentos_find", methods={"GET","HEAD"})
     */
    public function find(Request $request, int $id): JsonResponse
    {
        try {
            $usuario = $this->getUser();

            $movimentos = $this->movimentosService->find($id, $usuario);

            return new JsonResponse($movimentos);
        } catch (\Exception $e) {
            return new JsonResponse(['message' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    /**
     * @Route("/movimentos", name="app_movimentos_create", methods={"POST"})
     */
    public function create(Request $request): JsonResponse
    {
        try {
            $requestObj = json_decode($request->getContent());

            $usuario = $this->getUser();

            Validator::validate($requestObj, 'descricao');
            Validator::validate($requestObj, 'valor', Validator::NUMBER);
            Validator::validate($requestObj, 'dataMovimento');
            Validator::validate($requestObj, 'conta', Validator::NUMBER);
            Validator::validate($requestObj, 'tipoMovimento', Validator::NUMBER);

            // $dataCompleta = new DateTimeImmutable($requestObj->dataCompleta);
            $data = new DateTimeImmutable($requestObj->dataMovimento);

            $conta = $this->contasService->find($requestObj->conta, $usuario);
            $tipoMovimento = $this->tipoMovimentosService->find($requestObj->tipoMovimento, $usuario);

            $movimento = new Movimento();
            $movimento->setDescricao($requestObj->descricao);
            $movimento->setValor($requestObj->valor);
            $movimento->setDataMovimento($data);
            $movimento->setConta($conta);
            $movimento->setTipomovimento($tipoMovimento);

            $movimento = $this->movimentosService->create($movimento, $usuario);

            return new JsonResponse($movimento, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return new JsonResponse(['message' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @Route("/movimentos/{id}", name="app_movimentos_update", methods={"PUT"})
     */
    public function update(Request $request, int $id)
    {
        try {
            $requestObj = json_decode($request->getContent());
            $usuario = $this->getUser();

            Validator::validate($requestObj, 'descricao');
            Validator::validate($requestObj, 'valor', Validator::NUMBER);
            Validator::validate($requestObj, 'dataMovimento');
            Validator::validate($requestObj, 'tipoMovimento', Validator::NUMBER);

            $data = new DateTimeImmutable($requestObj->dataMovimento);
            $tipoMovimento = $this->tipoMovimentosService->find($requestObj->tipoMovimento, $usuario);

            $movimento = $this->movimentosService->find($id, $usuario);
            $movimento->setDescricao($requestObj->descricao);
            $movimento->setValor($requestObj->valor);
            $movimento->setDataMovimento($data);
            $movimento->setTipomovimento($tipoMovimento);
            
            $movimento = $this->movimentosService->update($movimento, $usuario);

            return new JsonResponse($movimento, Response::HTTP_OK);
        } catch (\Exception $e) {
            return new JsonResponse(['message' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    
    /**
     * @Route("/movimentos/{id}", name="app_movimentos_delete", methods={"DELETE"})
     */
    public function delete(Request $request, int $id)
    {
        try {
            $requestObj = json_decode($request->getContent());

            $usuario = $this->getUser();

            $movimento = $this->movimentosService->find($id, $usuario);
            
            $movimento = $this->movimentosService->delete($movimento, $usuario);

            return new JsonResponse($movimento, Response::HTTP_OK);
        } catch (\Exception $e) {
            return new JsonResponse(['message' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}