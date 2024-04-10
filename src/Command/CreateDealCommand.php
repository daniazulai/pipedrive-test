<?php

declare(strict_types=1);

namespace App\Command;

use GuzzleHttp\Exception\GuzzleException;
use Pipedrive\Api\DealsApi;
use Pipedrive\Api\ProductsApi;
use Pipedrive\ApiException;
use Pipedrive\Model\NewDeal;
use Pipedrive\Model\NewDealProduct;
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
            ->addArgument('product_id', InputArgument::OPTIONAL, 'Product ID')
            ->addArgument('quantity', InputArgument::OPTIONAL, 'Quantity', 1)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var ProductsApi $productsApi */
        $productsApi = $this->pipedriveService->getApiInstance('Products');
        /** @var DealsApi $dealsApi */
        $dealsApi = $this->pipedriveService->getApiInstance('Deals');

        try {
            $deal = new NewDeal([
                'title' => $input->getArgument('title'),
            ]);
            $deal = $dealsApi->addDeal($deal);

            if ($productId = $input->getArgument('product_id')) {
                $product = $productsApi->getProduct($productId);
                if ($product->getSuccess()) {
                    $price = 0;
                    foreach ($product->getData()->getPrices() as $currentPrice) {
                        if ($currentPrice['currency'] === 'EUR') {
                            $price = (int)$currentPrice['price'];
                            break;
                        }
                    }

                    $dealProduct = new NewDealProduct([
                        'product_id' => $product->getData()->getId(),
                        'item_price' => $price,
                        'quantity' => (int) $input->getArgument('quantity'),
                    ]);
                    $dealsApi->addDealProduct($deal->getData()->getId(), $dealProduct);
                }
            }
        } catch (GuzzleException $e) {
            return Command::FAILURE;
        } catch (ApiException $e) {
            return Command::INVALID;
        }

        return Command::SUCCESS;
    }
}
