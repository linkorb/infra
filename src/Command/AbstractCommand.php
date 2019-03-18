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
use CliHighlighter\Service\Highlighter;

abstract class AbstractCommand extends Command
{
    protected $infra;

    public function __construct()
    {
        parent::__construct();
        $this->infra = new Infra();
        $infraConfig = getenv('INFRA_CONFIG');
        if (!$infraConfig) {
            $infraConfig = __DIR__ . '/../../example';
        }
        $this->infra->load($infraConfig);
        $this->infra->validate();

        $options = [
            'json' => [
                'keys'   => 'magenta',
                'values' => 'green',
                'braces' => 'light_white',
            ],
        
            'xml' => [
                'elements'   => 'yellow',
                'attributes' => 'green',
                'values'     => 'green',
                'innerText'  => 'light_white',
                'comments'   => 'gray',
                'meta'       => 'yellow',
            ],
        
            'yaml' => [
                'separators' => 'blue',
                'keys'       => 'green',
                'values'     => 'light_white',
                'comments'   => 'red',
            ],
        ];        
        $this->highlighter = new Highlighter($options);
    }

    public function writeYaml(OutputInterface $output, $yaml)
    {
        $output->write($this->highlighter->highlight($yaml, 'yaml'));
    }
}
