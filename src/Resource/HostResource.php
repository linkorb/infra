<?php

namespace Infra\Resource;

use GraphQL\Type\Definition\Type;
use Graph\Graph;
use RuntimeException;

class HostResource extends AbstractResource
{
    protected $hostGroups = null; // cache

    public function getPublicIp()
    {
        return $this->spec['publicIp'] ?? null;
    }

    public function getFqdn()
    {
        return $this->getName() . '.example.com';
    }

    public function getPrivateIp()
    {
        return $this->spec['privateIp'] ?? null;
    }

    public function getStatus()
    {
        return $this->spec['status'] ?? null;
    }

    public function getSshAddress()
    {
        return $this->getPublicIp();
    }

    public function getSshUsername()
    {
        return $this->spec['sshUsername'] ?? 'root';
    }

    public function getOs()
    {
        return $this->spec['os'] ?? null;
    }

    public function getOsRelease()
    {
        $name = $this->getOs();

        if (null === $name) {
            return null;
        }

        return $this->graph->getResource('OsRelease', $name);
    }

    public function getLocalHostGroupNames(): array
    {
        $hostGroups = $this->spec['hostGroups'];
        if (is_null($hostGroups)) {
            return [];
        }
        if (is_string($hostGroups)) {
            $hostGroups = explode(',', $hostGroups);
        }
        if (!is_array($hostGroups)) {
            throw new RuntimeException('undefined type for hostGroups');
        }
        foreach ($hostGroups as $i => $name) {
            $name = trim($name);
            $hostGroups[$i] = $name;
        }

        return $hostGroups;
    }

    public function hasLocalHostGroupName(string $groupName): bool
    {
        if (in_array($groupName, $this->getLocalHostGroupNames())) {
            return true;
        }

        return false;
    }

    public function getLocalHostGroups()
    {
        $groupNames = $this->getLocalHostGroupNames();
        $res = [];
        foreach ($groupNames as $groupName) {
            $res[$groupName] = $this->graph->getResource('HostGroup', $groupName);
        }

        return $res;
    }


    public function getServices()
    {
        $services = $this->graph->getResourcesByType('Service');

        $res = [];
        foreach ($services as $service) {
            foreach ($service->getHosts() as $host) {
                if ($host->getName()  == $this->getName()) {
                    $res[$service->getName()] = $service;
                }
            }
        }

        return $res;
    }

    public function getHostGroups()
    {
        $hostGroups = $this->getLocalHostGroups();
        foreach ($hostGroups as $hostGroupName => $hostGroup) {
            $parent = $hostGroup->getParentHostGroup();
            if ($parent) {
                $hostGroups[$parent->getName()] = $parent;
            }
        }

        return $hostGroups;
    }

    public function hasHostGroupName(string $name): bool
    {
        foreach ($this->getHostGroups() as $hostGroup) {
            if ($hostGroup->getName() == $name) {
                return true;
            }
        }

        return false;
    }

    public function getFirewallRules()
    {
        $rules = $this->graph->getResourcesByType('FirewallRule');
        $res = [];
        foreach ($rules as $rule) {
            $hosts = $rule->getHosts();
            foreach ($hosts as $host) {
                if ($host->getName() == $this->getName()) {
                    $res[$rule->getName()] = $rule;
                }
            }
        }

        return $res;
    }

    public function hasOsReleaseName(string $name): bool
    {
        return $this->getOs() === $name;
    }

    public static function getConfig(Graph $graph)
    {
        return [
            'name'   => 'Host',
            'fields' => function () use (&$graph) {
                return [
                    'name'            => Type::id(),
                    'os'              => [
                        'type'        => Type::string(),
                        'description' => 'Operating system code',
                    ],
                    'osRelease'       => [
                        'type'        => $graph->getType('OsRelease'),
                        'description' => 'Operating system',
                    ],
                    'status'       => [
                        'type'        => Type::string(),
                        'description' => 'Status',
                    ],
                    'publicIp'        => [
                        'type'        => Type::string(),
                        'description' => 'Public IPv4 address',
                    ],
                    'privateIp'       => [
                        'type'        => Type::string(),
                        'description' => 'Private IPv4 address',
                    ],
                    'sshUsername'     => [
                        'type'        => Type::string(),
                        'description' => 'SSH username',
                    ],
                    'sshAddress'      => [
                        'type'        => Type::string(),
                        'description' => 'SSH address',
                    ],
                    'description'     => [
                        'type'        => Type::string(),
                        'description' => 'Description',
                    ],
                    'fqdn'            => [
                        'type'        => Type::string(),
                        'description' => 'Fully Qualified Domain Name',
                    ],
                    'services'      => [
                        'type'        => Type::listOf($graph->getType('Service')),
                        'description' => 'Returns all services on this host',
                    ],
                    'hostGroups'      => [
                        'type'        => Type::listOf($graph->getType('HostGroup')),
                        'description' => 'Returns all hostgroups (recursively)',
                    ],
                    'localHostGroups' => [
                        'type'        => Type::listOf($graph->getType('HostGroup')),
                        'description' => 'Returns all hostgroups (local only)',
                    ],
                    'labels' => [
                        'type'        => Type::listOf($graph->getType('Label')),
                        'description' => 'Returns all labels',
                    ],
                ];
            },
        ];
    }

}
