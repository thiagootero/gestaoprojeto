<?php

namespace App\Support;

use Carbon\Carbon;

class RecorrenciaDateGenerator
{
    private const INTERVALOS = [
        'mensal' => 1,
        'bimestral' => 2,
        'trimestral' => 3,
        'semestral' => 6,
        'anual' => 12,
    ];

    /**
     * Gera datas conforme a frequência selecionada.
     *
     * @return Carbon[]
     */
    public static function gerar(string $frequencia, string $inicio, int $dia, int $repeticoes): array
    {
        $intervalo = self::INTERVALOS[$frequencia] ?? null;

        if ($intervalo === null) {
            return [];
        }

        $datas = [];
        $inicioDate = Carbon::parse($inicio)->startOfDay();
        $cursor = $inicioDate->copy()->day(min($dia, $inicioDate->daysInMonth));

        if ($cursor->lt($inicioDate)) {
            $cursor->addMonthsNoOverflow($intervalo);
            $cursor->day(min($dia, $cursor->daysInMonth));
        }

        $repeticoes = max(1, $repeticoes);

        while (count($datas) < $repeticoes) {
            $datas[] = $cursor->copy();
            $cursor->addMonthsNoOverflow($intervalo);
            $cursor->day(min($dia, $cursor->daysInMonth));
        }

        return $datas;
    }

    /**
     * Gera datas conforme a frequência até uma data limite (ex: fim do projeto).
     *
     * @return Carbon[]
     */
    public static function gerarAteData(string $frequencia, string $inicio, int $dia, Carbon|string $dataFim): array
    {
        $intervalo = self::INTERVALOS[$frequencia] ?? null;

        if ($intervalo === null) {
            return [];
        }

        $datas = [];
        $inicioDate = Carbon::parse($inicio)->startOfDay();
        $limite = Carbon::parse($dataFim)->endOfDay();
        $cursor = $inicioDate->copy()->day(min($dia, $inicioDate->daysInMonth));

        if ($cursor->lt($inicioDate)) {
            $cursor->addMonthsNoOverflow($intervalo);
            $cursor->day(min($dia, $cursor->daysInMonth));
        }

        while ($cursor->lte($limite)) {
            $datas[] = $cursor->copy();
            $cursor->addMonthsNoOverflow($intervalo);
            $cursor->day(min($dia, $cursor->daysInMonth));
        }

        return $datas;
    }

    public static function isFrequenciaPeriodica(string $tipo): bool
    {
        return isset(self::INTERVALOS[$tipo]);
    }
}
