<?php

namespace Infra\Model;

use Collection\TypedArray;

class Host extends BaseModel
{
    protected $hostGroups;
    protected $properties;
    protected $rules;
    protected $users;

    public function __construct()
    {
        $this->hostGroups = new TypedArray(HostGroup::class);
        $this->properties = new TypedArray(Property::class);
        $this->rules = new TypedArray(Rule::class);
        $this->users = new TypedArray(User::class);
    }

    public function getConnectionAddress()
    {
        return (string)$this->properties->get('public_ip')->getValue();
    }
    public function getConnectionPort()
    {
        return 22;
    }
    public function getConnectionUsername()
    {
        $username = 'root';
        if ($this->properties->hasKey('automation_user')) {
            $username = $this->properties->get('automation_user')->getValue();
        }
        return $username;
    }

}
