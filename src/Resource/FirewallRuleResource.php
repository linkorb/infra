<?php

namespace Infra\Resource;

use Graphael\TypeRegistryInterface;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use Infra\Infra;

class FirewallRuleResource extends AbstractResource
{
    public function getHosts()
    {
        return $this->infra->getHosts($this->spec['hosts'] ?? null);
    }

    public function getTemplate()
    {
        return $this->spec['template'] ?? null;
    }

    public function getRemote()
    {
        return $this->spec['remote'] ?? null;
    }

    public static function getConfig(Infra $infra)
    {
        return [
            'name' => 'FirewallRule',
            'fields' => [
                'name' => Type::id(),
                'description' => [
                    'type' => Type::string(),
                    'description' => 'Description',
                    'resolve' => function ($resource, $args, $context, $info) use ($infra) {
                        return $resource->getTemplate();
                    }
                ],
                'remote' => [
                    'type' => Type::string(),
                    'description' => 'Remote (host, group, ip or *)',
                ],
                'template' => [
                    'type' => Type::string(),
                    'description' => 'iptables template',
                ],
                'fqdn' => [
                    'type' => Type::string(),
                    'description' => 'Description',
                    'resolve' => function ($resource, $args, $context, $info) use ($infra) {
                        return $resource['name'] . '.host.linkorb.cloud';
                    }
                ],
                'hosts' => [
                    'type' => Type::listOf($infra->getType('Host')),
                    'description' => 'Returns all hosts where this firewall rule is active',
                    'resolve' => function ($resource, $args, $context, $info) use ($infra) {
                        return $resource->getHosts();
                    },
                ],
            ],
        ];
    }
}
