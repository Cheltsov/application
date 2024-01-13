<?php

declare(strict_types=1);

namespace App\Command;

use App\Command\Exchange\BankAbstract;
use App\Command\Exchange\Mono;
use App\Command\Exchange\Privat;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsCommand(
    name       : 'app:exchange:check',
    description: 'Check currency exchange rates and notify if they change.',
)]
class Exchange extends Command
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly MailerInterface $mailer,
        private readonly BankAbstract $privat,
        private readonly BankAbstract $mono

    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('threshold', InputArgument::OPTIONAL, 'Threshold for rate change', 0.05);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $threshold = $input->getArgument('threshold');

        $privatbankRates = $this->privat
            ->setExchangeRates($this->request(Privat::API_ENDPOINT))
            ->getExchangeRates();

        $monobankRates = $this->mono
            ->setExchangeRates($this->request(Mono::API_ENDPOINT))
            ->getExchangeRates();

        foreach (['USD', 'EUR'] as $currency) {
            if (
                (abs($privatbankRates[$currency]['buy'] - $monobankRates[$currency]['buy']) > $threshold)
                || abs($privatbankRates[$currency]['sell'] - $monobankRates[$currency]['sell']) > $threshold
            ) {
                $this->sendNotification($privatbankRates[$currency], $monobankRates[$currency], $threshold);

                $output->writeln('A message has been sent');
            } else {
                $output->writeln('No significant changes in currency exchange rates.');
            }
        }

        return Command::SUCCESS;
    }

    private function request(string $apiEndpoint): array
    {
        $response = $this->httpClient->request('GET', $apiEndpoint, [
            'headers' => [
                'Content-Type' => 'application/json',
            ],
        ]);

        return $response->toArray();
    }

    /**
     * @param array{buy: float, sell: float} $privatCurrency
     * @param array{buy: float, sell: float} $monoCurrency
     */
    private function sendNotification(array $privatCurrency, array $monoCurrency, float $threshold): void
    {
        $body = sprintf(
            "The exchange rate difference is higher than %s\nPrivatBank: buy = %s; sell = %s\nMonoBank: buy = %s; sell = %s",
            $threshold,
            $privatCurrency['buy'],
            $privatCurrency['sell'],
            $monoCurrency['buy'],
            $monoCurrency['sell']
        );
        $email = (new Email())
            ->from('your_email@example.com')
            ->to('recipient@example.com')
            ->subject('Exchange PrivatBank and Monobank')
            ->text($body);

        $this->mailer->send($email);
    }
}