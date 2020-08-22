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

    public function generateHosts($resources): void
    {
        file_put_contents(
            $this->pathOutput . '/hosts.html',
            $this->twig->render(
                'hosts.html.twig',
                [
                    'resources' => $resources,
                ]
            )
        );

        file_put_contents(
            $this->pathOutput . '/hosts.csv',
            $this->twig->render(
                'hosts.csv.twig',
                [
                    'resources' => $resources,
                ]
            )
        );

        foreach ($resources as $resource) {
            file_put_contents(
                $this->pathOutput . '/hosts:' . $resource['name'] . '.html',
                $this->twig->render(
                    'host.html.twig',
                    [
                        'resource' => $resource,
                    ]
                )
            );
        }
    }

    public function generateHostGroups($resources): void
    {
        file_put_contents(
            $this->pathOutput . '/host-groups.html',
            $this->twig->render(
                'host-groups.html.twig',
                [
                    'resources' => $resources,
                ]
            )
        );

        foreach ($resources as $resource) {
            file_put_contents(
                $this->pathOutput . '/host-groups:' . $resource['name'] . '.html',
                $this->twig->render(
                    'host-group.html.twig',
                    [
                        'resource' => $resource,
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

    public function generateRepositories($resources): void
    {
        file_put_contents(
            $this->pathOutput . '/repositories.html',
            $this->twig->render(
                'repositories.html.twig',
                [
                    'resources' => $resources,
                ]
            )
        );

        foreach ($resources as $resource) {
            file_put_contents(
                $this->pathOutput . '/repositories:' . $resource['name'] . '.html',
                $this->twig->render(
                    'repository.html.twig',
                    [
                        'resource' => $resource,
                    ]
                )
            );
        }
    }

    public function generateUsers($users): void
    {
        file_put_contents(
            $this->pathOutput . '/users.html',
            $this->twig->render(
                'users.html.twig',
                [
                    'records' => $users,
                ]
            )
        );

        foreach ($users as $user) {
            file_put_contents(
                $this->pathOutput . '/users:' . $user['name'] . '.html',
                $this->twig->render(
                    'user.html.twig',
                    [
                        'record' => $user,
                    ]
                )
            );
        }
    }

    public function generateDnsDomains($domains): void
    {
        file_put_contents(
            $this->pathOutput . '/domains.html',
            $this->twig->render(
                'domains.html.twig',
                [
                    'records' => $domains,
                ]
            )
        );

        foreach ($domains as $domain) {
            file_put_contents(
                $this->pathOutput . '/domains:' . $domain['name'] . '.html',
                $this->twig->render(
                    'domain.html.twig',
                    [
                        'record' => $domain,
                    ]
                )
            );
        }
    }

    public function generateMonitoringChecks($monitoringChecks): void
    {
        file_put_contents(
            $this->pathOutput . '/monitoring-checks.html',
            $this->twig->render(
                'monitoring-checks.html.twig',
                [
                    'records' => $monitoringChecks,
                ]
            )
        );

        foreach ($monitoringChecks as $monitoringCheck) {
            file_put_contents(
                $this->pathOutput . '/monitoring-checks:' . $monitoringCheck['name'] . '.html',
                $this->twig->render(
                    'monitoring-check.html.twig',
                    [
                        'record' => $monitoringCheck,
                    ]
                )
            );
        }
    }

    public function generateDockerEngines($dockerEngines): void
    {
        file_put_contents(
            $this->pathOutput . '/docker-engines.html',
            $this->twig->render(
                'docker-engines.html.twig',
                [
                    'records' => $dockerEngines,
                ]
            )
        );

        foreach ($dockerEngines as $dockerEngine) {
            file_put_contents(
                $this->pathOutput . '/docker-engines:' . $dockerEngine['name'] . '.html',
                $this->twig->render(
                    'docker-engine.html.twig',
                    [
                        'record' => $dockerEngine,
                    ]
                )
            );
        }
    }

    public function generateServices(array $resources): void
    {
        file_put_contents(
            $this->pathOutput . '/services.html',
            $this->twig->render(
                'services.html.twig',
                [
                    'resources' => $resources,
                ]
            )
        );

        foreach ($resources as $resource) {
            file_put_contents(
                $this->pathOutput . '/services:' . $resource['name'] . '.html',
                $this->twig->render(
                    'service.html.twig',
                    [
                        'resource' => $resource,
                    ]
                )
            );
        }
    }
}
