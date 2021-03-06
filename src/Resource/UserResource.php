<?php

namespace Infra\Resource;

use GraphQL\Type\Definition\Type;
use Graph\Graph;

class UserResource extends AbstractResource
{
    public function getHosts(): array
    {
        return $this->infra->getHosts($this->spec['hosts'] ?? null);
    }

    public function getGithubUsername()
    {
        return $this->spec['githubUsername'] ?? null;
    }

    public function getSshPublicKey()
    {
        return $this->spec['sshPublicKey'] ?? null;
    }

    public function getHasSshPublicKey(): bool
    {
        return null !== $this->getSshPublicKey();
    }

    public function getGithubLink()
    {
        if (null === $this->getGithubUsername()) {
            return null;
        }

        return 'https://github.com/' . $this->getGithubUsername();
    }

    public static function getConfig(Graph $graph): array
    {
        return [
            'name'   => 'User',
            'fields' => function () use (&$graph) {
                return [
                    'name'            => Type::id(),
                    'description'     => [
                        'type'        => Type::string(),
                        'description' => 'Description',
                    ],
                    'labels' => [
                        'type'        => Type::listOf($graph->getType('Label')),
                        'description' => 'Returns all labels',
                    ],
                    'githubUsername'  => [
                        'type'        => Type::string(),
                        'description' => 'Github Username',
                    ],
                    'githubLink'      => [
                        'type'        => Type::string(),
                        'description' => 'Github Link',
                    ],
                    'hosts'           => [
                        'type'        => Type::listOf($graph->getType('Host')),
                        'description' => 'Returns all hosts where this user has an account',
                    ],
                    'hasSshPublicKey' => [
                        'type'        => Type::boolean(),
                        'description' => 'Returns whether user has Ssh Public Key or not',
                    ],
                    'sshPublicKey'    => [
                        'type'        => Type::string(),
                        'description' => 'Returns Ssh Public Key',
                    ],
                ];
            },
        ];
    }
}
