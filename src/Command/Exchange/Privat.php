<?php

declare(strict_types=1);

namespace App\Command\Exchange;

class Privat extends BankAbstract
{
    private const ISO_EUR = 'EUR';
    private const ISO_USD = 'USD';
    private const ISO_UAH = 'UAH';

    public const API_ENDPOINT = 'https://api.privatbank.ua/p24api/pubinfo?json&exchange&coursid=5';

    public function parseExchangeRates(array $response): array
    {
        $exchangeRates = [];
        foreach ($response as $exchange) {
            $currencyCodeA = $exchange['ccy'];
            $currencyCodeB = $exchange['base_ccy'];

            if ($currencyCodeA === self::ISO_USD && $currencyCodeB === self::ISO_UAH) {
                $exchangeRates['USD'] = [
                    'buy'  => (float) $exchange['buy'],
                    'sell' => (float) $exchange['sale'],
                ];
            }
            if ($currencyCodeA === self::ISO_EUR && $currencyCodeB === self::ISO_UAH) {
                $exchangeRates['EUR'] = [
                    'buy'  => (float) $exchange['buy'],
                    'sell' => (float) $exchange['sale'],
                ];
            }
        }

        return $exchangeRates;
    }
}