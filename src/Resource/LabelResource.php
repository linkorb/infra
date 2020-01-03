<?php

namespace Infra\Resource;

use GraphQL\Type\Definition\Type;
use Graph\Graph;
use RuntimeException;

class LabelResource extends AbstractResource
{
    public function getKey()
    {
        return $this->spec['key'] ?? null;
    }

    public function getValue()
    {
        return $this->spec['value'] ?? null;
    }

    public static function getConfig(Graph $graph)
    {
        return [
            'name'   => 'Label',
            'fields' => function () use (&$graph) {
                return [
                    'key'             => [
                        'type'        => Type::string(),
                        'description' => 'Key',
                    ],
                    'value'           => [
                        'type'        => Type::string(),
                        'description' => 'Value',
                    ],
                ];
            },
        ];
    }

}
