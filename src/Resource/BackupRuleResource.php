<?php

namespace Infra\Resource;

use GraphQL\Type\Definition\Type;
use Infra\Infra;

class BackupRuleResource extends AbstractResource
{
    public function getHosts(): ?string
    {
        return $this->spec['hosts'] ?? null;
    }

    public function getPath(): ?string
    {
        return $this->spec['path'] ?? null;
    }

    public static function getConfig(Infra $infra): array
    {
        return [
            'name'   => 'BackupRule',
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
                'path'        => [
                    'type'        => Type::string(),
                    'description' => 'Path',
                ],
            ],
        ];
    }
}
