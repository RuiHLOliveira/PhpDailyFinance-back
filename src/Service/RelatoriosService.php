<?php

namespace App\Service;

use App\Entity\User;
use App\Entity\Conta;
use DateTimeImmutable;
use App\Entity\Movimento;
use Doctrine\Persistence\ManagerRegistry;
use stdClass;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\VarDumper\VarDumper;

class RelatoriosService
{
    
    private $doctrine;

    /**
     * @var \App\Service\MovimentosService
     */
    protected $movimentosService;
    
    /**
     * @var \App\Service\TipoMovimentosService
     */
    protected $tipoMovimentosService;

    public function __construct(
        ManagerRegistry $doctrine,
        MovimentosService $movimentosService,
        TipoMovimentosService $tipoMovimentosService
    ) {
        $this->doctrine = $doctrine;
        $this->movimentosService = $movimentosService;
        $this->tipoMovimentosService = $tipoMovimentosService;
    }

    /**
     * RELATORIO DE TOTAL DE ENTRADAS E SAIDAS
    */
    private function relatorioEntradasSaidas ($movimentos)
    {
        $podeSerEntrada = ['Rendimento Automático', 'Salário Nasajon', 'Outras Fontes'];
        $naoPodeSerSaida = ['Transferência', 'Pagamento de Faturas', 'Investimento'];

        $relatorioEntradasSaidas = new stdClass();
        $relatorioEntradasSaidas->nome = 'Total de entradas e saídas';
        $relatorioEntradasSaidas->dados = [];

        $totalEntradas = new stdClass();
        $totalSaidas = new stdClass();
        $totalEntradas->nome = 'Total de Entradas';
        $totalEntradas->valor = 0;
        $totalSaidas->nome = 'Total de Saídas';
        $totalSaidas->valor = 0;

        /**
         * @var \App\Entity\Movimento $movimento
         */
        foreach ($movimentos as $key => $movimento) {
            
            $valor = $movimento->getValor();
            $valor = (float) $valor;
            $movimento->setValor($valor);

            if($movimento->getValor() > 0 && in_array($movimento->getTipomovimento()->getnome(), $podeSerEntrada) ) {
                $totalEntradas->valor += $movimento->getValor();
            } elseif ( !in_array($movimento->getTipomovimento()->getnome(), $naoPodeSerSaida)) {
                $totalSaidas->valor += $movimento->getValor();
            }
        }

        $totalEntradas->valor = number_format($totalEntradas->valor, 2);
        $totalSaidas->valor = number_format($totalSaidas->valor, 2);

        $relatorioEntradasSaidas->dados[] = $totalEntradas;
        $relatorioEntradasSaidas->dados[] = $totalSaidas;

        return $relatorioEntradasSaidas;
    }

    /**
     * RELATORIO DE TOTAIS POR TIPOS DE MOVIMENTOS
     */
    private function relatorioTotalPorTipoMovimento ($movimentos)
    {
        $relatorioTotalPorTipoMovimento = new stdClass();
        $relatorioTotalPorTipoMovimento->nome = 'Valor total por tipos de movimento';
        $relatorioTotalPorTipoMovimento->dados = [];

        /**
         * @var \App\Entity\Movimento $movimento
         */
        foreach ($movimentos as $key => $movimento) {
            
            $valor = $movimento->getValor();
            $valor = (float) $valor;
            $movimento->setValor($valor);

            $idTipo = $movimento->getTipomovimento()->getId();

            if(!isset($relatorioTotalPorTipoMovimento->dados[$idTipo])) {
                $relatorioTotalPorTipoMovimento->dados[$idTipo] = new stdClass();
                $relatorioTotalPorTipoMovimento->dados[$idTipo]->nome = $movimento->getTipomovimento()->getNome();
                $relatorioTotalPorTipoMovimento->dados[$idTipo]->valorEntradas = 0;
                $relatorioTotalPorTipoMovimento->dados[$idTipo]->valorSaidas = 0;
                $relatorioTotalPorTipoMovimento->dados[$idTipo]->valorTotal = 0;
            }

            $relatorioTotalPorTipoMovimento->dados[$idTipo]->valorTotal += $movimento->getValor();
            if($movimento->getValor() > 0 ) {
                $relatorioTotalPorTipoMovimento->dados[$idTipo]->valorEntradas += $movimento->getValor();
            } else {
                $relatorioTotalPorTipoMovimento->dados[$idTipo]->valorSaidas += $movimento->getValor();
            }
        }

        foreach ($relatorioTotalPorTipoMovimento->dados as $key => $dado) {
            $relatorioTotalPorTipoMovimento->dados[$key]->valorTotal = number_format($relatorioTotalPorTipoMovimento->dados[$key]->valorTotal, 2);
            $relatorioTotalPorTipoMovimento->dados[$key]->valorEntradas = number_format($relatorioTotalPorTipoMovimento->dados[$key]->valorEntradas, 2);
            $relatorioTotalPorTipoMovimento->dados[$key]->valorSaidas = number_format($relatorioTotalPorTipoMovimento->dados[$key]->valorSaidas, 2);
        }

        return $relatorioTotalPorTipoMovimento;
    }

    public function relatorioBase(User $usuario, array $auxFilter = []) {

        try {
            // $tipoMovimentos = $this->tipoMovimentosService->index($usuario);
            $movimentos = $this->movimentosService->index($usuario, null, [], $auxFilter);

            $relatorioEntradasSaidas = $this->relatorioEntradasSaidas($movimentos);
            $relatorioTotalPorTipoMovimento = $this->relatorioTotalPorTipoMovimento($movimentos);

            return [
                'relatorioEntradasSaidas' => $relatorioEntradasSaidas,
                'relatorioTotalPorTipoMovimento' => $relatorioTotalPorTipoMovimento
            ];
        } catch (\Exception $e) {
            throw $e;
        }
    }
}