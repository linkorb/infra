<?php

namespace Infra\Model;

use Collection\TypedArray;

class User extends BaseModel
{
    protected $name;
    protected $hosts;
    protected $properties;

    public function __construct()
    {
        $this->hosts = new TypedArray(Host::class);
        $this->properties = new TypedArray(Property::class);
    }
}
