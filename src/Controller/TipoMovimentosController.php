<?php

namespace App\Controller;

use App\Entity\Conta;
use App\Entity\TipoMovimento;
use App\Form\Validator;
use App\Service\TipoMovimentosService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class TipoMovimentosController extends AbstractController
{

    /**
     * @var \App\Service\TipoMovimentosService;
     */
    private $tipoMovimentosService;

    public function __construct(TipoMovimentosService $tipoMovimentosService)
    {
        $this->tipoMovimentosService = $tipoMovimentosService;
    }

    /**
     * @Route("/tipomovimentos", name="app_tipomovimentos_list", methods={"GET","HEAD"})
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

            $tipomovimentos = $this->tipoMovimentosService->index($usuario, $orderBy);

            return new JsonResponse($tipomovimentos);
        } catch (\Exception $e) {
            return new JsonResponse(['message' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @Route("/tipomovimentos", name="app_tipomovimentos_create", methods={"POST"})
     */
    public function create(Request $request): JsonResponse
    {
        try {
            $requestObj = json_decode($request->getContent());

            $usuario = $this->getUser();

            Validator::validate($requestObj, 'nome');

            // $dataCompleta = new DateTimeImmutable($requestObj->dataCompleta);

            $tipomovimento = new TipoMovimento();
            $tipomovimento->setNome($requestObj->nome);

            $tipomovimento = $this->tipoMovimentosService->create($tipomovimento, $usuario);

            return new JsonResponse($tipomovimento, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return new JsonResponse(['message' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @Route("/tipomovimentos/{id}", name="app_tipomovimentos_update", methods={"PUT"})
     */
    public function update(Request $request, int $id)
    {
        try {
            $requestObj = json_decode($request->getContent());
            $usuario = $this->getUser();

            Validator::validate($requestObj, 'nome', Validator::STRING);

            $tipomovimento = $this->tipoMovimentosService->find($id, $usuario);
            $tipomovimento->setNome($requestObj->nome);
            
            $tipomovimento = $this->tipoMovimentosService->update($tipomovimento, $usuario);

            return new JsonResponse($tipomovimento, Response::HTTP_OK);
        } catch (\Exception $e) {
            return new JsonResponse(['message' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @Route("/tipomovimentos/{id}", name="app_tipomovimentos_delete", methods={"DELETE"})
     */
    public function delete(Request $request, int $id)
    {
        try {
            $requestObj = json_decode($request->getContent());

            $usuario = $this->getUser();

            $tipomovimento = $this->tipoMovimentosService->find($id, $usuario);
            
            $tipomovimento = $this->tipoMovimentosService->delete($tipomovimento, $usuario);

            return new JsonResponse($tipomovimento, Response::HTTP_OK);
        } catch (\Exception $e) {
            return new JsonResponse(['message' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}