<?php

namespace Infra\Model;

use Collection\TypedArray;

class Property extends BaseModel
{
    protected $name;
    protected $value;

    public function __construct($name = null, $value = null)
    {
        $this->name = $name;
        $this->value = $value;
    }
}
