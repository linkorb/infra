<?php

namespace Infra\Model;

use RuntimeException;
use Collection\TypedArray;
use SSHClient\ClientConfiguration\ClientConfiguration;
use SSHClient\ClientBuilder\ClientBuilder;


class Infra extends BaseModel
{
    protected $firewallRules;
    protected $hosts;
    protected $hostGroups;

    public function __construct()
    {
        $this->firewallRules = new TypedArray(FirewallRule::class);
        $this->hosts = new TypedArray(Host::class);
        $this->hostGroups = new TypedArray(HostGroup::class);
    }

    public function getHostsByExpression($expression)
    {
        $part = explode(':', $expression);
        if (count($part)!=2) {
            throw new RuntimeException("Expression should be exactly 2 parts: " . $expression);
        }
        $type = $part[0];
        $name = $part[1];
        switch ($type) {
            case 'host':
                $host = $this->getHosts()->get($name);
                return [$host];
            case 'group':
                $group = $this->getHostGroups()->get($name);
                return $group->getHosts();
            default:
                throw new RuntimeException("Unknown host expression type: " . $type);
        }
    }

    public function getSshBuilder($hostname)
    {
        $host = $this->getHosts()->get($hostname);

        $config = new ClientConfiguration($host->getConnectionAddress(), $host->getConnectionUsername());
        // $config->setOptions(array(
        //     'IdentityFile' => '~/.ssh/id_rsa',
        //     'IdentitiesOnly' => 'yes',
        // ));
        $builder = new ClientBuilder($config);
        return $builder;
    }
}
