<?php

namespace Infra\Model;

use Collection\TypedArray;

class Host extends BaseModel
{
    protected $hostGroups;
    protected $properties;

    public function __construct()
    {
        $this->hostGroups = new TypedArray(HostGroup::class);
        $this->properties = new TypedArray(Property::class);
    }

    public function getPublicIp()
    {
        return (string)$this->properties->get('public_ip')->getValue();
    }

    public function getAutomationUser()
    {
        $username = 'root';
        if ($this->properties->hasKey('automation_user')) {
            $username = $this->properties->get('automation_user')->getValue();
        }
        return $username;
    }
}
