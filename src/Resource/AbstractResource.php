<?php

namespace Infra\Resource;

use Graph\Graph;
use Graph\Resource\AbstractResource as GraphResource;

abstract class AbstractResource extends GraphResource
{
    protected $infra;

    protected function __construct(Graph $graph)
    {
        parent::__construct($graph);
        $this->infra = $graph->getContainer();
    }
}