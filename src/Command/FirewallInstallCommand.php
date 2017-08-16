<?php

namespace Infra\Command;

use RuntimeException;

use Infra\Model\Infra;
use Infra\Loader\AutoInfraLoader;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Infra\Firewall\IptablesFirewall;

class FirewallInstallCommand extends Command
{
    public function configure()
    {
        $this->setName('firewall:install')
            ->setDescription('Install firewall on given host')
            ->addArgument(
                'host',
                InputArgument::REQUIRED,
                'Name of the host'
            )
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $hostName = $input->getArgument('host');

        $output->writeLn("<info>Generating firewall rules for {$hostName}</info>");

        $loader = new AutoInfraLoader();
        $infra = $loader->load();
        $host = $infra->getHosts()->get($hostName);

        $firewall = new IptablesFirewall();
        $rules = $firewall->generateRules($infra, $host);

        $tmpFilename = tempnam(sys_get_temp_dir(), 'infra_');
        file_put_contents($tmpFilename, $rules);

        // Create ssh and scp clients
        $sshBuilder = $infra->getSshBuilder($hostName);
        $scpBuilder = $infra->getSshBuilder($hostName);
        $ssh = $sshBuilder->buildClient();
        $scp = $scpBuilder->buildSecureCopyClient();

        // backup
        $backupFilename = '/tmp/iptables-backup-' . date('Ymd-His') . '.rules';
        $output->writeLn("<info>Backing up current firewall to {$backupFilename}</info>");
        $ssh->exec(['sudo iptables-save > ' . $backupFilename]);
        if ($ssh->getExitCode()!=0) {
            throw new RuntimeException($ssh->getErrorOutput());
        }

        // upload
        $remoteRulesFilename = '/tmp/rules.v4';
        $output->writeLn("<info>Uploading new rules to {$host->getPublicIp()}:{$remoteRulesFilename}</info>");
        $res = $scp->copy(
            $tmpFilename,
            $scp->getRemotePath($remoteRulesFilename)
        );
        if ($scp->getExitCode()!=0) {
            throw new RuntimeException($scp->getErrorOutput());
        }

        $output->writeLn("<info>Copying $remoteRulesFilename to '/etc/iptables/rules.v4'</info>");
        $ssh->exec(["sudo cp {$remoteRulesFilename} /etc/iptables/rules.v4"]);
        if ($ssh->getExitCode()!=0) {
            throw new RuntimeException($ssh->getErrorOutput());
        }
        $output->write($ssh->getOutput());
        $output->writeLn("<info>Done</info>");


        $output->writeLn("<info>Activating new firewall</info>");
        $ssh->exec(['sudo iptables-restore --verbose < ' . $remoteRulesFilename]);
        if ($ssh->getExitCode()!=0) {
            throw new RuntimeException($ssh->getErrorOutput());
        }
        $output->write($ssh->getOutput());
        $output->writeLn("<info>Done</info>");

        //echo $tmpFilename . "\n";
        //unlink($tmpFilename);
    }
}
