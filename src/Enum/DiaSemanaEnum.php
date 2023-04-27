<?php

namespace App\Enum;

class DiaSemanaEnum
{
    const LISTA = [
        1 => "Segunda-feira",
        2 => "Terça-feira",
        3 => "Quarta-feira",
        4 => "Quinta-feira",
        5 => "Sexta-feira",
        6 => "Sábado",
        7 => "Domingo",
    ];

    const SEM_FEIRA = [
        1 => "Segunda",
        2 => "Terça",
        3 => "Quarta",
        4 => "Quinta",
        5 => "Sexta",
        6 => "Sábado",
        7 => "Domingo",
    ];

    const ABREVIADO = [
        1 => "seg",
        2 => "ter",
        3 => "qua",
        4 => "qui",
        5 => "sex",
        6 => "sáb",
        7 => "dom",
    ];
}
