<?php

namespace Infra;

class Script
{
    protected $name;
    protected $filename;
    protected $doc;

    public function __construct(string $name, string $filename, ?string $doc = null)
    {
        $this->name = $name;
        $this->filename = $filename;
        $this->doc = $doc;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getFilename()
    {
        return $this->filename;
    }
    public function getDoc()
    {
        return $this->doc;
    }
}