<?php

namespace Infra\Loader;

use Infra\Model\Infra;
use Symfony\Component\Yaml\Yaml;

class AutoInfraLoader
{
    public function load()
    {
        $config = getenv('INFRA_CONFIG');
        if (!$config) {
            $config = 'infra.yml';
        }
        $infra = new Infra();
        if (substr($config, 0, 4)=='http') {
            // assume it's a http(s) url
            $yaml = file_get_contents($config);
        } else {
            // assume it's a file
            $yaml = file_get_contents($config);
        }
        $data = Yaml::parse($yaml);
        $loader = new ArrayInfraLoader();
        return $loader->load($infra, $data);
    }
}
