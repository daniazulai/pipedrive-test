<?php

declare(strict_types=1);

namespace App\Command;

use GuzzleHttp\Exception\GuzzleException;
use Pipedrive\Api\DealsApi;
use Pipedrive\ApiException;
use Pipedrive\Model\NewDeal;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:create-deal')]
class CreateDealCommand extends PipedriverCommand
{
    protected function configure(): void
    {
        $this
            ->addArgument('title', InputArgument::REQUIRED, 'Deal title')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var DealsApi $apiInstance */
        $apiInstance = $this->pipedriveService->getApiInstance('Deals');
        $deal = new NewDeal([
            'title' => $input->getArgument('title'),
        ]);

        try {
            $apiInstance->addDeal($deal);
        } catch (GuzzleException $e) {
            return Command::FAILURE;
        } catch (ApiException $e) {
            return Command::INVALID;
        }

        return Command::SUCCESS;
    }
}
