<?php

namespace Infra\Model;

use Collection\TypedArray;

class HostGroup extends BaseModel
{
    protected $name;
    protected $description;
    protected $hosts;
    protected $firewallRules;

    public function __construct()
    {
        $this->hosts = new TypedArray(Host::class);
        $this->firewallRules = new TypedArray(FirewallRule::class);
    }
}
