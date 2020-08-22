<?php

namespace Infra\Resource;

use GraphQL\Type\Definition\Type;
use Graph\Graph;

class HostGroupResource extends AbstractResource
{
    public function getHosts()
    {
        $hosts = $this->graph->getResourcesByType('Host');
        $res = [];
        foreach ($hosts as $host) {
            if ($host->hasHostGroupName($this->getName())) {
                $res[] = $host;
            }
        }
        return $res;
    }

    /**
     * Recursively traverse parentGroups to get a consolidated array of hostgroups
     */
    public function getHostGroups(): array
    {
        $hostGroups = [];
        $parentHostGroup = $this->getParentHostGroup();
        if ($parentHostGroup) {
            $hostGroups[$parentHostGroup->getName()] = $parentHostGroup;
            foreach ($parentHostGroup->getHostGroups() as $hostGroup) {
                if (!isset($hostGroups[$hostGroup->getName()])) {
                    $hostGroups[$hostGroup->getName()] = $hostGroup;
                }
            }
        }
        return $hostGroups;
    }

    public function getChildHostGroups()
    {
        $res = [];
        foreach ($this->graph->getResourcesByType('HostGroup') as $hostGroup) {
            $parentHostGroup = $hostGroup->getParentHostGroup();
            if ($parentHostGroup) {
                if ($parentHostGroup->getName() == $this->getName()) {
                    $res[] = $hostGroup;
                }
            }
        }
        return $res;
    }

    public function getParentHostGroup()
    {
        $name = $this->spec['parentHostGroup'] ?? null;
        if (!$name) {
            return null;
        }
        $hostGroup = $this->graph->getResource('HostGroup', $name);
        return $hostGroup;
    }

    public static function getConfig(Graph $graph)
    {
        return [
            'name' => 'HostGroup',
            'fields' => function() use (&$graph) {
                return [
                    'name' => Type::id(),
                    'description' => [
                        'type' => Type::string(),
                        'description' => 'Description',
                    ],
                    'labels' => [
                        'type'        => Type::listOf($graph->getType('Label')),
                        'description' => 'Returns all labels',
                    ],
                    'parentHostGroup' => [
                        'type' => $graph->getType('HostGroup'),
                        'description' => 'Parent host group',
                    ],
                    'hosts' => [
                        'type' => Type::listOf($graph->getType('Host')),
                        'description' => 'Returns all hosts in this group',
                    ],
                ];
            }
        ];
    }
}
