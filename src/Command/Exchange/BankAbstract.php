<?php

declare(strict_types=1);

namespace App\Command\Exchange;

abstract class BankAbstract
{
    protected array $exchangeRates = [];

    abstract protected function parseExchangeRates(array $response): array;

    /**
     * @param array{USD: array{buy: float, sell: float}, EUR: array{buy: float, sell: float}} $exchangeRates
     */
    public function setExchangeRates(array $exchangeRates): self
    {
        $this->exchangeRates = $exchangeRates;

        return $this;
    }

    /**
     * @return array{USD: array{buy: float, sell: float}, EUR: array{buy: float, sell: float}}
     */
    public function getExchangeRates(): array
    {
        return $this->exchangeRates;
    }
}