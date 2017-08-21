<?php

namespace Infra\Loader;

use Doctrine\Common\Inflector\Inflector;
use RuntimeException;
use Boost\Populator\ProtectedPopulator;
use ReflectionObject;
use Infra\Model\Infra;
use Infra\Model\Host;
use Infra\Model\HostGroup;
use Infra\Model\User;
use Infra\Model\Rule;
use Infra\Model\Property;
use InvalidArgumentException;

class ArrayInfraLoader
{
    public function load(Infra $infra, $config = [])
    {
        if (isset($config['properties'])) {
            foreach ($config['properties'] as $k=>$v) {
                $property = new Property();
                $property->setName($k);
                $property->setValue($v);
                $infra->getProperties()->add($property);
            }
        }

        foreach ($config['users'] as $name => $userData) {
            $user = new User();
            $user->setName($name);
            if (isset($userData['properties'])) {
                foreach ($userData['properties'] as $k=>$v) {
                    $property = new Property();
                    $property->setName($k);
                    $property->setValue($v);
                    $user->getProperties()->add($property);
                }
            }

            $infra->getUsers()->add($user);
        }

        foreach ($config['hosts'] as $name => $hostData) {
            $host = new Host();
            $host->setName($name);
            if (isset($hostData['properties'])) {
                foreach ($hostData['properties'] as $k=>$v) {
                    $property = new Property();
                    $property->setName($k);
                    $property->setValue($v);
                    $host->getProperties()->add($property);
                }
            }
            $this->loadFirewallRules($host, $hostData);
            if (isset($hostData['users'])) {
                foreach ($hostData['users'] as $username) {
                    $user = $infra->getUsers()->get($username);
                    $host->getUsers()->add($user);
                    $user->getHosts()->add($host);
                }
            }

            $infra->getHosts()->add($host);
        }

        foreach ($config['host_groups'] as $name => $hostGroupData) {
            $hostGroup = new HostGroup();
            $hostGroup->setName($name);
            if (isset($hostGroupData['description'])) {
                $hostGroup->setDescription($hostGroupData['description']);
            }

            if (isset($hostGroupData['hosts'])) {
                foreach ($hostGroupData['hosts'] as $hostName) {
                    $host = $infra->getHosts()->get($hostName);
                    $hostGroup->getHosts()->add($host);
                    $host->getHostGroups()->add($hostGroup);
                }
            }

            $this->loadFirewallRules($hostGroup, $hostGroupData);
            //$this->loadUsers($infra, $host, $hostData);

            $infra->getHostGroups()->add($hostGroup);
        }

        // Run through host_groups a few times to enrich `extends` groups
        for ($i=0; $i<10; $i++) {
            foreach ($config['host_groups'] as $name => $hostGroupData) {
                $hostGroup = $infra->getHostGroups()->get($name);
                if (isset($hostGroupData['extends'])) {
                    $names = explode(',', $hostGroupData['extends']);
                    foreach ($names as $name2) {
                        $name2 = trim($name2);
                        if (!$infra->getHostGroups()->hasKey($name2)) {
                            throw new InvalidArgumentException("Host group `" . $name . '` extends `' . $name2 . '`, but that group does not exist');
                        }
                        $extendedHostGroup = $infra->getHostGroups()->get($name2);

                        foreach ($hostGroup->getHosts() as $host) {
                            $host->getHostGroups()->add($extendedHostGroup);
                            $extendedHostGroup->getHosts()->add($host);
                        }
                    }
                }
            }
        }

        foreach ($config['host_groups'] as $hostGroupName => $hostGroupData) {
            $hostGroup = $infra->getHostGroups()->get($hostGroupName);
            if (isset($hostGroupData['users'])) {
                foreach ($hostGroupData['users'] as $username) {
                    $user = $infra->getUsers()->get($username);
                    foreach ($infra->getHosts() as $host) {
                        if ($host->getHostGroups()->hasKey($hostGroupName)) {
                            $host->getUsers()->add($user);
                            $user->getHosts()->add($host);
                        }
                    }
                }
            }
        }


        return $infra;
    }

    protected function loadFirewallRules($obj, $data)
    {
        if (isset($data['firewall_rules'])) {
            foreach ($data['firewall_rules'] as $ruleName => $ruleData) {
                $rule = new Rule();
                if (!$ruleName) {
                    throw new InvalidArgumentException("Firewall rule without a name on: " . $obj->getName());
                }
                $rule->setName($ruleName);
                if (isset($ruleData['remote'])) {
                    $rule->setRemote($ruleData['remote']);
                }
                if (isset($ruleData['template'])) {
                    $rule->setTemplate($ruleData['template']);
                }
                $obj->getRules()->add($rule);
            }
        }
    }

}
