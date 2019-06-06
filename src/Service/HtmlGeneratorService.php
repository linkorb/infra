<?php

namespace Infra\Service;

use RuntimeException;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class HtmlGeneratorService
{
    private const PATH_TEMPLATES = __DIR__ . '/../../templates';

    /**
     * @var string
     */
    private $pathOutput;

    /**
     * @var Environment
     */
    private $twig;

    public function __construct($pathOutput)
    {
        $this->pathOutput = $pathOutput;

        $loader = new FilesystemLoader(self::PATH_TEMPLATES);
        $this->twig = new Environment($loader);
    }

    public function checkDirectory(): void
    {
        if (
            !is_dir($this->pathOutput) &&
            !mkdir($concurrentDirectory = $this->pathOutput, 0777, true) &&
            !is_dir($concurrentDirectory)
        ) {
            throw new RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
        }
    }

    public function deleteObsoleteFiles(): void
    {
        array_map('unlink', glob("$this->pathOutput/*.*"));
    }

    public function generateIndex(): void
    {
        file_put_contents(
            $this->pathOutput . '/index.html',
            $this->twig->render(
                'index.html.twig', [
                'title' => 'Index',
            ]));
    }

    public function generateHosts($hosts): void
    {
        file_put_contents(
            $this->pathOutput . '/hosts.html',
            $this->twig->render(
                'hosts.html.twig', [
                'hosts' => $hosts,
                'title' => 'Hosts',
            ]));
    }

    public function generateHostGroups($hostGroups): void
    {
        file_put_contents(
            $this->pathOutput . '/host-groups.html',
            $this->twig->render(
                'hostGroups.html.twig', [
                'hostGroups' => $hostGroups,
                'title' => 'Host Groups',
            ]));
    }

    public function generateFirewallRules($firewallRules): void
    {
        file_put_contents(
            $this->pathOutput . '/firewall-rules.html',
            $this->twig->render(
                'firewallRules.html.twig', [
                'firewallRules' => $firewallRules,
                'title' => 'Firewall Rules',
            ]));
    }
}
