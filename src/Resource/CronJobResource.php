<?php

namespace Infra\Resource;

use GraphQL\Type\Definition\Type;
use Infra\Infra;

class CronJobResource extends AbstractResource
{
    public function getHosts(): ?string
    {
        return $this->spec['hosts'] ?? null;
    }

    public function getRule(): ?string
    {
        return $this->spec['rule'] ?? null;
    }

    public function getUser(): ?string
    {
        return $this->spec['user'] ?? null;
    }

    public function getCommand(): ?string
    {
        return $this->spec['command'] ?? null;
    }

    public static function getConfig(Infra $infra): array
    {
        return [
            'name'   => 'CronJob',
            'fields' => [
                'name'        => Type::id(),
                'description' => [
                    'type'        => Type::string(),
                    'description' => 'Description',
                ],
                'hosts'       => [
                    'type'        => Type::string(),
                    'description' => 'Returns all hosts where this cronjob rule is active',
                ],
                'rule'        => [
                    'type'        => Type::string(),
                    'description' => 'Cronjob rule',
                ],
                'user'        => [
                    'type'        => Type::string(),
                    'description' => 'Cronjob owner',
                ],
                'command'     => [
                    'type'        => Type::string(),
                    'description' => 'Cronjob command',
                ],
            ],
        ];
    }
}
