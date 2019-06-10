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
        array_map('unlink', glob("$this->pathOutput/*"));
    }

    public function generateIndex(): void
    {
        file_put_contents(
            $this->pathOutput . '/index.html',
            $this->twig->render('index.html.twig')
        );
    }

    public function generateHosts($hosts): void
    {
        file_put_contents(
            $this->pathOutput . '/hosts.html',
            $this->twig->render(
                'hosts.html.twig',
                [
                    'hosts' => $hosts,
                ]
            )
        );

        foreach ($hosts as $host) {
            file_put_contents(
                $this->pathOutput . '/hosts:' . $host['name'] . '.html',
                $this->twig->render(
                    'host.html.twig',
                    [
                        'host' => $host,
                    ]
                )
            );
        }
    }

    public function generateHostGroups($hostGroups): void
    {
        file_put_contents(
            $this->pathOutput . '/host-groups.html',
            $this->twig->render(
                'host-groups.html.twig',
                [
                    'records' => $hostGroups,
                ]
            )
        );

        foreach ($hostGroups as $group) {
            file_put_contents(
                $this->pathOutput . '/host-groups:' . $group['name'] . '.html',
                $this->twig->render(
                    'host-group.html.twig',
                    [
                        'record' => $group,
                    ]
                )
            );
        }
    }

    public function generateOsReleases($osReleases): void
    {
        file_put_contents(
            $this->pathOutput . '/os-releases.html',
            $this->twig->render(
                'os-releases.html.twig',
                [
                    'records' => $osReleases,
                ]
            )
        );

        foreach ($osReleases as $release) {
            file_put_contents(
                $this->pathOutput . '/os-releases:' . $release['name'] . '.html',
                $this->twig->render(
                    'os-release.html.twig',
                    [
                        'osRelease' => $release,
                    ]
                )
            );
        }
    }

    public function generateFirewallRules($firewallRules): void
    {
        file_put_contents(
            $this->pathOutput . '/firewall-rules.html',
            $this->twig->render(
                'firewall-rules.html.twig',
                [
                    'records' => $firewallRules,
                ]
            )
        );

        foreach ($firewallRules as $rule) {
            file_put_contents(
                $this->pathOutput . '/firewall-rules:' . $rule['name'] . '.html',
                $this->twig->render(
                    'firewall-rule.html.twig',
                    [
                        'record' => $rule,
                    ]
                )
            );
        }
    }
}
