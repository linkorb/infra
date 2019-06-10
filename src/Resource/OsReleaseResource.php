<?php

namespace Infra\Resource;

use GraphQL\Type\Definition\Type;
use Infra\Infra;

class OsReleaseResource extends AbstractResource
{
    public function getDisplayName()
    {
        return $this->spec['displayName'] ?? null;
    }

    public function getHosts(): array
    {
        /** @var HostResource[] $hosts */
        $hosts = $this->infra->getResourcesByType('Host');
        $res = [];
        foreach ($hosts as $host) {
            if ($host->hasOsReleaseName($this->getName())) {
                $res[] = $host;
            }
        }

        return $res;
    }

    public static function getConfig(Infra $infra): array
    {
        return [
            'name'   => 'OsRelease',
            'fields' => [
                'name'        => Type::id(),
                'description' => [
                    'type'        => Type::string(),
                    'description' => 'Description',
                ],
                'displayName' => [
                    'type'        => Type::string(),
                    'description' => 'OS Display Name',
                ],
                'hosts'       => [
                    'type'        => Type::listOf($infra->getType('Host')),
                    'description' => 'Returns all hosts in this group',
                ],
            ],
        ];
    }
}
