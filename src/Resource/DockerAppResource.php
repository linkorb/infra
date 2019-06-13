<?php

namespace Infra\Resource;

use GraphQL\Type\Definition\Type;
use Infra\Infra;

class DockerAppResource extends AbstractResource
{
    public function getEngine()
    {
        return $this->spec['engine'];
    }

    public function getAppConfig()
    {
        return json_encode($this->spec['config']) ?? null;
    }

    public function hasDockerEngineName($name): bool
    {
        return $this->getEngine() === $name;
    }

    public static function getConfig(Infra $infra): array
    {
        return [
            'name'   => $infra->getTypeName(self::class),
            'fields' => [
                'name'        => Type::id(),
                'description' => [
                    'type'        => Type::string(),
                    'description' => 'Description',
                ],
                'engine'      => [
                    'type'        => Type::string(),
                    'description' => 'Docker Engine Name',
                ],
                'appConfig'   => [
                    'type'        => Type::string(),
                    'description' => 'Docker App Config',
                ],
            ],
        ];
    }
}
