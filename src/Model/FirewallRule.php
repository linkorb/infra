<?php

namespace Infra\Model;

class FirewallRule extends BaseModel
{
    protected $name;
    protected $remote;
    protected $template;
    protected $comment;
}
