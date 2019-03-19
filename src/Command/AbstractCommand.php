<?php

namespace Infra\Command;

use RuntimeException;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Infra\Infra;


abstract class AbstractCommand extends Command
{
    protected $infra;

    public function __construct(Infra $infra)
    {
        $this->infra = $infra;
        parent::__construct();
    }

    public function writeYaml(OutputInterface $output, $yaml)
    {
        $output->write($this->highlighter->highlight($yaml, 'yaml'));
    }
}
