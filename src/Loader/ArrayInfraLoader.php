<?php

namespace Infra\Loader;

use Doctrine\Common\Inflector\Inflector;
use RuntimeException;
use Boost\Populator\ProtectedPopulator;
use ReflectionObject;
use Infra\Model\Infra;
use Infra\Model\Host;
use Infra\Model\HostGroup;
use Infra\Model\Rule;
use Infra\Model\Property;
use InvalidArgumentException;

class ArrayInfraLoader
{
    public function load(Infra $infra, $config = [])
    {
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

            if (isset($hostGroupData['firewall_rules'])) {
                foreach ($hostGroupData['firewall_rules'] as $ruleName => $ruleData) {
                    $rule = new Rule();
                    if (!$ruleName) {
                        throw new InvalidArgumentException("Firewall rule without a name on group: " . $hostGroup->getName());
                    }
                    $rule->setName($ruleName);
                    $rule->setRemote($ruleData['remote']);
                    $rule->setTemplate($ruleData['template']);
                    $hostGroup->getRules()->add($rule);
                }
            }

            $infra->getHostGroups()->add($hostGroup);
        }
        // Run through host_groups again to enrich `extends` groups
        foreach ($config['host_groups'] as $name => $hostGroupData) {
            $hostGroup = $infra->getHostGroups()->get($name);
            if (isset($hostGroupData['extends'])) {
                $names = explode(',', $hostGroupData['extends']);
                foreach ($names as $name2) {
                    $name2 = trim($name2);
                    if (!$infra->getHostGroups()->hasKey($name2)) {
                        throw new InvalidArgumentException("Host group `" . $name . '` extends `' . $name2 . '`, but that group does not exist');
                    }

                    $hostGroup2 = $infra->getHostGroups()->get(trim($name2));
                    $hostGroup->getExtends()->add($hostGroup2);
                }
            }
        }

        return $infra;
    }
}
