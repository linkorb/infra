<?php

namespace Infra\Resource;

use Graphael\TypeRegistryInterface;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use Infra\Infra;

class MonitoringCheckResource extends AbstractResource
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
    
    public function getCommand()
    {
        return $this->spec['command'] ?? null;
    }

    public function getSubscribers()
    {
        $hosts = $this->spec['hosts'] ?? null;
        if (is_array($hosts)) {
            $hosts = implode(', ', $hosts);
        }
        return explode(',', $hosts);
    }


    public function getInterval()
    {
        return $this->spec['interval'] ?? null;
    }

    public function getOccurrences()
    {
        return $this->spec['occurrences'] ?? null;
    }

    public function getHandlers()
    {
        return $this->spec['handlers'] ?? null;
    }

    public static function getConfig(Infra $infra)
    {
        return [
            'name' => 'MonitoringCheck',
            'fields' => [
                'name' => Type::id(),
                'description' => [
                    'type' => Type::string(),
                    'description' => 'Description',
                ],
                'command' => [
                    'type' => Type::string(),
                    'description' => 'Command',
                ],
                'interval' => [
                    'type' => Type::string(),
                    'description' => 'interval',
                ],
                'hosts' => [
                    'type' => Type::listOf($infra->getType('Host')),
                    'description' => 'Returns all hosts that should subscribe to this rule',
                ],
            ],
        ];
    }
}
