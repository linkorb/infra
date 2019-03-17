<?php

namespace Infra\Resource;

use GraphQL\Type\Definition\Type;
use Infra\Infra;

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

    public function getSshAddress()
    {
        return $this->getPublicIp();
    }

    public function getSshUsername()
    {
        return $this->spec['sshUsername'] ?? null;
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
            throw new RuntimeException("undefined type for hostGroups");
        }
        foreach ($hostGroups as $i=>$name) {
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
            $res[$groupName] = $this->infra->getResource('HostGroup', $groupName);
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
        $rules = $this->infra->getResourcesByType('FirewallRule');
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

    public static function getConfig(Infra $infra)
    {
        return [
            'name' => 'Host',
            'fields' => function() use (&$infra) {
                return [
                    'name' => Type::id(),
                    'publicIp' => [
                        'type' => Type::string(),
                        'description' => 'Public IPv4 address',
                    ],
                    'privateIp' => [
                        'type' => Type::string(),
                        'description' => 'Private IPv4 address',
                    ],
                    'description' => [
                        'type' => Type::string(),
                        'description' => 'Description',
                    ],
                    'fqdn' => [
                        'type' => Type::string(),
                        'description' => 'Fully Qualified Domain Name',
                    ],
                    'hostGroups' => [
                        'type' => Type::listOf($infra->getType('HostGroup')),
                        'description' => 'Returns all hostgroups (recursively)',
                    ],
                    'localHostGroups' => [
                        'type' => Type::listOf($infra->getType('HostGroup')),
                        'description' => 'Returns all hostgroups (local only)',
                    ],
                ];
            }
        ];
    }

}
