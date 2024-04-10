<?php

declare(strict_types=1);

namespace App\Service;

use Pipedrive\Configuration;
use GuzzleHttp\Client;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class PipedriveService
{
    private Configuration $configuration;

    public function __construct(ParameterBagInterface $parameterBag)
    {
        $this->configuration = (new Configuration())->setApiKey('api_token', $parameterBag->get('pipedrive_api_token'));
    }

    public function getApiInstance(string $resource): mixed
    {
        $class = $this->resolveClassPath($resource);

        return new $class(
            new Client(),
            $this->configuration
        );
    }

    private function resolveClassPath(string $resource): string
    {
        return 'Pipedrive\Api\\' . $resource . 'Api';
    }
}
