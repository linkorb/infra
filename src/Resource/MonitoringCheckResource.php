<?php

namespace Infra\Resource;

use Graphael\TypeRegistryInterface;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use Graph\Graph;

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

    public static function getConfig(Graph $graph)
    {
        return [
            'name' => 'MonitoringCheck',
            'fields' => [
                'name' => Type::id(),
                'description' => [
                    'type' => Type::string(),
                    'description' => 'Description',
                ],
                'labels' => [
                    'type'        => Type::listOf($graph->getType('Label')),
                    'description' => 'Returns all labels',
                ],
                'command' => [
                    'type' => Type::string(),
                    'description' => 'Command',
                ],
                'interval' => [
                    'type' => Type::int(),
                    'description' => 'interval',
                ],
                'occurrences' => [
                    'type' => Type::int(),
                    'description' => 'occurrences',
                ],
                'subscribers' => [
                    'type' => Type::listOf(Type::string()),
                    'description' => 'subscribers',
                ],
                'handlers' => [
                    'type' => Type::listOf(Type::string()),
                    'description' => 'handlers',
                ],
                'hosts' => [
                    'type' => Type::listOf($graph->getType('Host')),
                    'description' => 'Returns all hosts that should subscribe to this rule',
                ],
            ],
        ];
    }
}
