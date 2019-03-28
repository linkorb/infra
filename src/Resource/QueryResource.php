<?php

namespace Infra\Resource;

use GraphQL\Type\Definition\Type;
use Infra\Infra;

class QueryResource
{
    public static function getConfig(Infra $infra)
    {
        $config = [
            'name'   => 'Query',
            'fields' => function () use (&$infra) {
                return self::getFieldConfig($infra);
            },
        ];

        return $config;
    }

    public static function getFieldConfig(Infra $infra)
    {
        $fieldConfig = [];
        $fieldConfig['echo'] = [
            'type'    => Type::string(),
            'args'    => [
                'message' => Type::nonNull(Type::string()),
            ],
            'resolve' => function ($value, $args, $context, $info) {
                return $args['message'] . ', test';
            },
        ];
        foreach ($infra->getTypeNames() as $typeName) {
            $fieldConfig[lcfirst($typeName)] = [
                'type'        => $infra->getType($typeName),
                'description' => 'Returns ' . $typeName . ' by name',
                'args'        => [
                    'name' => Type::nonNull(Type::string()),
                ],
                'resolve'     => function ($root, $args) use ($infra, $typeName) {
                    $resource = $infra->getResource($typeName, $args['name']);

                    return $resource;
                },
            ];
            $fieldConfig['all' . $infra->getInflector()->pluralize($typeName)] = [
                'type'        => Type::listOf($infra->getType($typeName)),
                'description' => 'Returns all ' . $infra->getInflector()->pluralize($typeName),
                'resolve'     => function ($root, $args) use ($infra, $typeName) {
                    $resources = $infra->getResourcesByType($typeName);

                    return $resources;
                },
            ];
        }

        $fieldConfig = array_merge($fieldConfig, self::addGetHostsEndpoint($infra));

        return $fieldConfig;
    }

    private static function addGetHostsEndpoint(Infra $infra): array
    {
        $res = [];
        $typeName = $infra->getTypeName(HostResource::class);
        $res['get' . $infra->getInflector()->pluralize($typeName)] = [
            'type'        => Type::listOf($infra->getType($typeName)),
            'args'        => [
                'name' => Type::nonNull(Type::string()),
            ],
            'description' => 'List hosts using the host expansion algorithm',
            'resolve'     => function ($root, $args) use ($infra, $typeName) {
                return $infra->getHosts($args['name']);
            },
        ];

        return $res;
    }
}
