<?php

namespace Infra\Command;

use Docopt;
use Infra\Script;
use ReflectionClass;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class CustomCommand extends AbstractCommand
{
    /**
     * @var Script
     */
    protected $script;

    public static function fromScript($infra, Script $script)
    {
        $command = new self($infra);
        $command->setName($script->getName());

        $doc = $script->getDoc();
        $arguments = self::getArgumentsFromDoc($doc);

        foreach ($arguments as $argument) {
            $command->addArgument($argument, InputArgument::REQUIRED);
        }

        $command->script = $script;

        return $command;
    }

    public function configure()
    {
        $this->setName('custom')
            ->setDescription('');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $argv = $input->getArguments();

        $params = [$this->script->getFilename()];

        foreach ($argv as $name => $item) {
            if ('command' === $name) {
                continue;
            }

            $params[] = $item;
        }

        $process = new Process($params);
        $process->run();
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
        echo $process->getOutput();
    }

    private static function getArgumentsFromDoc($doc)
    {
        $reflector = new ReflectionClass(Docopt::class);
        $filepath = $reflector->getFileName();
        require_once $filepath;

        $usageSections = Docopt\parse_section('usage:', $doc);

        if (count($usageSections) === 0) {
            throw new Docopt\LanguageError('"usage:" (case-insensitive) not found.');
        } else if (count($usageSections) > 1) {
            throw new Docopt\LanguageError('More than one "usage:" (case-insensitive).');
        }

        $usage = $usageSections[0];
        $options = Docopt\parse_defaults($doc);

        $formalUse = Docopt\formal_usage($usage);
        $pattern = Docopt\parse_pattern($formalUse, $options);
        $fix = $pattern->fix();

        $argsArray = [];
        if (!empty($args = $fix->children[0]->children[0]->children)) {
            foreach ($args as $arg) {
                if ($arg instanceof Docopt\Argument) {
                    $argsArray[] = str_replace(['>', '<'], '', $arg->name);
                }
            }
        }

        return $argsArray;
    }
}
