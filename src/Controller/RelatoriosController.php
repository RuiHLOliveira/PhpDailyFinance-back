<?php

namespace App\Controller;

use App\Service\RelatoriosService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class RelatoriosController extends AbstractController
{

    /**
     * @var \App\Service\RelatoriosService;
     */
    private $relatoriosService;

    public function __construct(
        RelatoriosService $relatoriosService
    ) {
        $this->relatoriosService = $relatoriosService;
    }

    /**
     * @Route("/relatorios/relatorioBase", name="app_relatorios_relatorioBase", methods={"GET","HEAD"})
     */
    public function relatorioBase(Request $request): JsonResponse
    {
        try {
            $usuario = $this->getUser();

            $auxFilter = [];
            if($request->query->get('dataIni') != null && $request->query->get('dataFim') != null){
                $auxFilter['dataMovimento']['dataIni'] = $request->query->get('dataIni');
                $auxFilter['dataMovimento']['dataFim'] = $request->query->get('dataFim');
            }

            $dadosRelatorio = $this->relatoriosService->relatorioBase($usuario, $auxFilter);

            return new JsonResponse($dadosRelatorio);
        } catch (\Exception $e) {
            return new JsonResponse(['message' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

}