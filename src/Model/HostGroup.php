<?php

namespace Infra\Model;

use Collection\TypedArray;

class HostGroup extends BaseModel
{
    protected $name;
    protected $description;
    protected $hosts;
    protected $rules;

    public function __construct()
    {
        $this->hosts = new TypedArray(Host::class);
        $this->rules = new TypedArray(Rule::class);
    }
}
