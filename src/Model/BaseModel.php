<?php

namespace Infra\Model;

use Boost\BoostTrait;
use Boost\Accessors\ProtectedAccessorsTrait;
use Collection\Identifiable;

class BaseModel implements Identifiable
{
    protected $name;

    use BoostTrait;
    use ProtectedAccessorsTrait;

    public function identifier()
    {
        return $this->name;
    }

    public function getPropertyValue($name)
    {
        $value = null;
        if (isset($this->properties[$name])) {
            $value = $this->properties[$name]->getValue();
        }
        return $value;
    }
}
