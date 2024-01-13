<?php

declare(strict_types=1);

namespace App\Command\Exchange;

class Mono extends BankAbstract
{
    private const ISO_EUR = 978;
    private const ISO_USD = 840;
    private const ISO_UAH = 980;

    public const API_ENDPOINT = 'https://api.monobank.ua/bank/currency';

    public function parseExchangeRates(array $response): array
    {
        $exchangeRates = [];
        foreach ($response as $exchange) {
            $currencyCodeA = (int) $exchange['currencyCodeA'];
            $currencyCodeB = (int) $exchange['currencyCodeB'];

            if ($currencyCodeA === self::ISO_USD && $currencyCodeB === self::ISO_UAH) {
                $exchangeRates['USD'] = [
                    'buy'  => (float) $exchange['rateBuy'],
                    'sell' => (float) $exchange['rateSell'],
                ];
            }
            if ($currencyCodeA === self::ISO_EUR && $currencyCodeB === self::ISO_UAH) {
                $exchangeRates['EUR'] = [
                    'buy'  => (float) $exchange['rateBuy'],
                    'sell' => (float) $exchange['rateSell'],
                ];
            }
        }

        return $exchangeRates;
    }
}