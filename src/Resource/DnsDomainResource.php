<?php

namespace Infra\Resource;

use GraphQL\Type\Definition\Type;
use Infra\Infra;

class DnsDomainResource extends AbstractResource
{
    public function getDnsAccount()
    {
        return $this->spec['dnsAccount'] ?? null;
    }

    public function getDnsRecords(): array
    {
        /** @var DnsRecordResource[] $records */
        $records = $this->infra->getResourcesByType('DnsRecord');
        $res = [];
        foreach ($records as $record) {
            if (
                $record->getDnsDomain() &&
                $record->getDnsDomain()->getName() === $this->getName()
            ) {
                $res[] = $record;
            }
        }

        return $res;
    }

    public static function getConfig(Infra $infra): array
    {
        return [
            'name'   => 'DnsDomain',
            'fields' => function () use (&$infra) {
                return [
                    'name'       => Type::id(),
                    'dnsAccount' => [
                        'type'        => Type::string(),
                        'description' => 'DNS account',
                    ],
                    'dnsRecords' => [
                        'type'        => Type::listOf($infra->getType('DnsRecord')),
                        'description' => 'Returns all dns records for this domain',
                    ],
                ];
            },
        ];
    }
}
