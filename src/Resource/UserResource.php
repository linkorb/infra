<?php

namespace Infra\Resource;

use GraphQL\Type\Definition\Type;
use Infra\Infra;

class UserResource extends AbstractResource
{
    public function getHosts()
    {
        return $this->infra->getHosts($this->spec['hosts'] ?? null);
    }
    public static function getConfig(Infra $infra)
    {
        return [
            'name' => 'User',
            'fields' => function() use (&$infra) {
                return [
                    'name' => Type::id(),
                    'description' => [
                        'type' => Type::string(),
                        'description' => 'Description',
                    ],
                    'hosts' => [
                        'type' => Type::listOf($infra->getType('Host')),
                        'description' => 'Returns all hosts where this user has an account',
                    ],
                    
                ];
            }
        ];
    }

}
