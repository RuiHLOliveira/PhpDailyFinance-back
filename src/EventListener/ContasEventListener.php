<?php

namespace App\EventListener;

use App\Service\ContasService;

class ContasEventListener {

    /**
     * @var \App\Service\ContasService
     */
    protected $contasService;

    public function __construct(
        ContasService $contasService
    ) {
        $this->contasService = $contasService;
    }

    public function onMovimentoCriado($event)
    {
        /**
         * @var \App\Entity\Conta $conta
         */
        $this->contasService->atualizaSaldoTotalConta($conta, $conta->getUsuario());
    }
}