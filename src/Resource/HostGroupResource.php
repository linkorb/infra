<?php

namespace Infra\Resource;

use GraphQL\Type\Definition\Type;
use Infra\Infra;

class HostGroupResource extends AbstractResource
{
    public function getHosts()
    {
        $hosts = $this->infra->getResourcesByType('Host');
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
        foreach ($this->infra->getResourcesByType('HostGroup') as $hostGroup) {
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
        $hostGroup = $this->infra->getResource('HostGroup', $name);
        return $hostGroup;
    }

    public static function getConfig(Infra $infra)
    {
        return [
            'name' => 'HostGroup',
            'fields' => function() use (&$infra) {
                return [
                    'name' => Type::id(),
                    'description' => [
                        'type' => Type::string(),
                        'description' => 'Description',
                    ],
                    'parentHostGroup' => [
                        'type' => $infra->getType('HostGroup'),
                        'description' => 'Parent host group',
                        'resolve' => function ($resource, $args, $context, $info) use ($infra) {
                            return $resource->getParentHostGroup();
                        },
                    ],
                    'hosts' => [
                        'type' => Type::listOf($infra->getType('Host')),
                        'description' => 'Returns all hosts in this group',
                        'resolve' => function ($resource, $args, $context, $info) use ($infra) {
                            return $resource->getHosts();
                        },
                    ],
                ];
            }
        ];
    }
}
