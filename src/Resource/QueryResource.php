<?php

namespace Infra\Resource;

use GraphQL\Type\Definition\Type;
use Graph\Graph;

class QueryResource
{
    public static function getConfig(Graph $graph)
    {
        $config = [
            'name'   => 'Query',
            'fields' => function () use (&$graph) {
                $fieldConfig = $graph->getGraphQlTypeFieldConfig();

                $fieldConfig['echo'] = [
                    'type'    => Type::string(),
                    'args'    => [
                        'message' => Type::nonNull(Type::string()),
                    ],
                    'resolve' => function ($value, $args, $context, $info) {
                        return $args['message'] . ', test';
                    },
                ];
                
                $fieldConfig = array_merge($fieldConfig, self::addGetHostsEndpoint($graph));

                return $fieldConfig;
            },
        ];

        return $config;
    }

    private static function addGetHostsEndpoint(Graph $graph): array
    {
        $res = [];
        $typeName = $graph->getTypeName(HostResource::class);
        $res['get' . $graph->getInflector()->pluralize($typeName)] = [
            'type'        => Type::listOf($graph->getType($typeName)),
            'args'        => [
                'names' => Type::nonNull(Type::string()),
            ],
            'description' => 'List hosts using the host expansion algorithm',
            'resolve'     => function ($root, $args) use ($graph, $typeName) {
                $infra = $graph->getContainer();
                return $infra->getHosts($args['names']);
            },
        ];

        return $res;
    }
}
