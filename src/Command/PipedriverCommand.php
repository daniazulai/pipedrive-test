<?php

namespace App\Command;

use App\Service\PipedriveService;
use Symfony\Component\Console\Command\Command;

abstract class PipedriverCommand extends Command
{
    public function __construct(
        protected readonly PipedriveService $pipedriveService,
        ?string $name = null
    )
    {
        parent::__construct($name);
    }
}