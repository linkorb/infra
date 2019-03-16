<?php

namespace Infra\Resource;

use Infra\Infra;
use ArrayAccess;
use RuntimeException;

abstract class AbstractResource implements ResourceInterface
{
    protected $name;
    protected $description;
    protected $infra;
    protected $typeName;
    protected $spec = [];

    private function __construct(Infra $infra)
    {
        $this->infra = $infra;
    }
    
    public static function fromConfig(Infra $infra, array $config)
    {
        $resource = new static($infra);
        $resource->typeName = $config['kind'];
        $metadata = $config['metadata'] ?? [];
        $resource->name = $metadata['name'] ?? null;
        if (!$resource->name) {
            throw new RuntimeException("No name specified in resource config");
        }
        $resource->description = $config['metadata']['description'] ?? null;
        $resource->spec = $config['spec'];
        return $resource;
    }

    public function getTypeName()
    {
        return $this->typeName;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function offsetSet($offset, $value) {
        throw new RuntimeException("Read only array access");
    }

    public function offsetUnset($offset) {
        throw new RuntimeException("Read only array access");
    }

    public function offsetExists($offset) {
        return true;
    }

    public function offsetGet($offset) {
        $offset = $this->infra->inflector->camelize($offset);
        $method = 'get' . ucfirst($offset);
        return $this->{$method}();
    }

    public function serialize()
    {
        $data = [
            'kind' => $this->getTypeName(),
            'metadata' => [
                'name' => $this->getName()
            ],
            'spec' => $this->spec
        ];
        return $data;
    }


}