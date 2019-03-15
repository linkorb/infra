<?php

namespace Infra\Resource;

use ArrayAccess;

interface ResourceInterface extends ArrayAccess
{
    public function getName();
}