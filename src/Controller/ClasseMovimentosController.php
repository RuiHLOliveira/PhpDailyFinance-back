<?php

namespace App\Controller;

use App\Entity\ClasseMovimento;
use App\Entity\Conta;
use App\Entity\TipoMovimento;
use App\Form\Validator;
use App\Service\ClasseMovimentosService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ClasseMovimentosController extends AbstractController
{

    /**
     * @var \App\Service\ClasseMovimentosService;
     */
    private $classeMovimentosService;

    public function __construct(ClasseMovimentosService $classeMovimentosService)
    {
        $this->classeMovimentosService = $classeMovimentosService;
    }

    /**
     * @Route("/classemovimentos", name="app_classemovimentos_list", methods={"GET","HEAD"})
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

            $classeMovimentos = $this->classeMovimentosService->index($usuario, $orderBy);

            return new JsonResponse($classeMovimentos);
        } catch (\Exception $e) {
            return new JsonResponse(['message' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @Route("/classemovimentos", name="app_classemovimentos_create", methods={"POST"})
     */
    public function create(Request $request): JsonResponse
    {
        try {
            $requestObj = json_decode($request->getContent());

            $usuario = $this->getUser();

            Validator::validate($requestObj, 'nome');

            // $dataCompleta = new DateTimeImmutable($requestObj->dataCompleta);

            $classeMovimento = new ClasseMovimento();
            $classeMovimento->setNome($requestObj->nome);

            $classeMovimento = $this->classeMovimentosService->create($classeMovimento, $usuario);

            return new JsonResponse($classeMovimento, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return new JsonResponse(['message' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @Route("/classemovimentos/{id}", name="app_classemovimentos_update", methods={"PUT"})
     */
    public function update(Request $request, int $id)
    {
        try {
            $requestObj = json_decode($request->getContent());
            $usuario = $this->getUser();

            Validator::validate($requestObj, 'nome', Validator::STRING);

            $classeMovimento = $this->classeMovimentosService->find($id, $usuario);
            $classeMovimento->setNome($requestObj->nome);
            
            $classeMovimento = $this->classeMovimentosService->update($classeMovimento, $usuario);

            return new JsonResponse($classeMovimento, Response::HTTP_OK);
        } catch (\Exception $e) {
            return new JsonResponse(['message' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @Route("/classemovimentos/{id}", name="app_classemovimentos_delete", methods={"DELETE"})
     */
    public function delete(Request $request, int $id)
    {
        try {
            $requestObj = json_decode($request->getContent());

            $usuario = $this->getUser();

            $classeMovimento = $this->classeMovimentosService->find($id, $usuario);
            
            $classeMovimento = $this->classeMovimentosService->delete($classeMovimento, $usuario);

            return new JsonResponse($classeMovimento, Response::HTTP_OK);
        } catch (\Exception $e) {
            return new JsonResponse(['message' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}