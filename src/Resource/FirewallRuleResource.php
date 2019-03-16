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
        return $this->infra->getHosts($this->spec['hosts'] ?? []);
    }

    public function getHostsAsString()
    {
        $hosts = $this->spec['hosts'] ?? null;
        if (is_array($hosts)) {
            $hosts = implode(', ', $hosts);
        }
        return $hosts;
    }
    public function getRemoteHostsAsString()
    {
        $hosts = $this->spec['remoteHosts'] ?? null;
        if (is_array($hosts)) {
            $hosts = implode(', ', $hosts);
        }
        return $hosts;
    }

    public function getTemplate()
    {
        return $this->spec['template'] ?? null;
    }

    public function getRemoteHosts()
    {
        return $this->infra->getHosts($this->spec['remoteHosts'] ?? []);
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
                'remoteHosts' => [
                    'type' => Type::string(),
                    'description' => 'Remote hosts',
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
