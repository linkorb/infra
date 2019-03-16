<?php

namespace Infra\Resource;

use Graphael\TypeRegistryInterface;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use Infra\Infra;

class DnsRecordResource extends AbstractResource
{
    public function getDnsDomain()
    {
        return $this->infra->getResource('DnsDomain', $this->spec['dnsDomain'] ?? null);
    }

    public static function getConfig(Infra $infra)
    {
        return [
            'name' => 'DnsRecord',
            'fields' => [
                'name' => Type::id(),
                'dnsDomain' => [
                    'type' => $infra->getType('DnsDomain'),
                    'description' => 'DNS domain',
                ],
                'type' => Type::string(),
                'ttl' => Type::int(),
                'value' => Type::string(),
            ],
        ];
    }
}
