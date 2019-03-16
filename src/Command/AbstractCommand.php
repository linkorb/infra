<?php

namespace Infra\Command;

use RuntimeException;

use Infra\Infra;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Infra\Firewall\IptablesFirewall;

abstract class AbstractCommand extends Command
{
    protected $infra;

    public function __construct()
    {
        parent::__construct();
        $this->infra = new Infra();
        $infraConfig = getenv('INFRA_CONFIG');
        if (!$infraConfig) {
            throw new RuntimeException("INFRA_CONFIG environment variable is undefined");
        }
        $this->infra->load($infraConfig);
        $this->infra->validate();
    }
}
