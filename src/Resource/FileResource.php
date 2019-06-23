<?php

namespace Infra\Resource;

use GraphQL\Type\Definition\Type;
use Graph\Graph;

class FileResource extends AbstractResource
{
    public function getHosts(): ?string
    {
        return $this->spec['hosts'] ?? null;
    }

    public function getFilename(): ?string
    {
        return $this->spec['filename'] ?? null;
    }

    public function getContent(): ?string
    {
        return $this->spec['content'] ?? null;
    }

    public static function getConfig(Graph $graph): array
    {
        return [
            'name'   => 'File',
            'fields' => [
                'name'        => Type::id(),
                'description' => [
                    'type'        => Type::string(),
                    'description' => 'Description',
                ],
                'hosts'       => [
                    'type'        => Type::string(),
                    'description' => 'Returns all hosts where this file should be placed',
                ],
                'filename'    => [
                    'type'        => Type::string(),
                    'description' => 'Full absolute file path',
                ],
                'content'     => [
                    'type'        => Type::string(),
                    'description' => 'File content',
                ],
            ],
        ];
    }
}
