<?php

namespace Infra\Model;

use Collection\TypedArray;

class UserGroup extends BaseModel
{
    protected $name;
    
    protected $hosts;
    protected $properties;
    protected $users = [];
    

    public function __construct()
    {
        $this->hosts = new TypedArray(Host::class);
        $this->properties = new TypedArray(Property::class);
        $this->users = new TypedArray(User::class);
    }
}
