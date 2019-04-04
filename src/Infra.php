<?php

namespace Infra;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Schema;
use Infra\Resource\ResourceInterface;
use Symfony\Component\Yaml\Yaml;
use Infra\Exception;
use Infra\Resource\HostResource;
use Doctrine\Common\Inflector\Inflector;
use SSHClient\ClientConfiguration\ClientConfiguration;
use SSHClient\ClientBuilder\ClientBuilder;
use RuntimeException;

class Infra
{
    protected $types = [];
    protected $typeClassMap = [];
    protected $resources = [];
    protected $scripts = [];
    protected $schema;
    protected $scriptPaths = [];

    public function __construct()
    {
        $this->registerType(Resource\HostResource::class);
        $this->registerType(Resource\HostGroupResource::class);
        $this->registerType(Resource\FirewallRuleResource::class);
        $this->registerType(Resource\UserResource::class);
        $this->registerType(Resource\MonitoringCheckResource::class);
        $this->registerType(Resource\DnsDomainResource::class);
        $this->registerType(Resource\DnsRecordResource::class);
        $this->registerType(Resource\QueryResource::class);
        $this->registerType(Resource\GitRepositoryResource::class);
        $this->registerType(Resource\CronJobResource::class);
        $this->registerType(Resource\FileResource::class);
        $this->inflector = new Inflector();

        $this->schema = new Schema([
            'query' => $this->getType('Query'),
        ]);
    }

    public function getInflector()
    {
        return $this->inflector;
    }

    public function getSchema()
    {
        return $this->schema;
    }

    public function registerType(string $className): void
    {
        $name = $this->getTypeName($className);
        $this->typeClassMap[$name] = $className;
    }

    public function getTypeName(string $className): string
    {
        $name = (new \ReflectionClass($className))->getShortName();
        $name = str_replace('Resource', '', $name);

        return $name;
    }

    public function getTypeNames(): array
    {
        $res = [];
        foreach ($this->typeClassMap as $key => $value) {
            $res[] = $key;
        }

        return $res;
    }

    public function getType($name): ObjectType
    {
        if (!isset($this->types[$name])) {
            if (!isset($this->typeClassMap[$name])) {
                throw new Exception\UnknownResourceTypeException($name);
            }
            $className = $this->typeClassMap[$name];
            $config = $className::getConfig($this);
            $obj = new ObjectType($config);
            $this->types[$name] = $obj;
        }

        return $this->types[$name];
    }

    public function hasType($name): bool
    {
        return isset($this->typeClassMap[$name]);
    }

    public function getTypeClass($name): string
    {
        if (!$this->hasType($name)) {
            throw new Exception\UnknownResourceTypeException($name);
        }

        return $this->typeClassMap[$name];
    }

    public function getCapitals($str)
    {
        if (preg_match_all('#([A-Z]+)#', $str, $matches)) {
            return implode('', $matches[1]);
        } else {
            return false;
        }
    }

    public function getTypeAliases($typeName)
    {
        $capitals = $this->getCapitals($typeName);
        $res = [
            $capitals,
            strtolower($capitals),
            $typeName,
            lcfirst($typeName),
            $this->inflector->pluralize($typeName),
            lcfirst($this->inflector->pluralize($typeName)),
        ];

        return $res;
    }

    public function getCanonicalTypeName($name)
    {
        foreach ($this->getTypeNames() as $typeName) {
            $aliases = $this->getTypeAliases($typeName);
            if (in_array($name, $aliases)) {
                return $typeName;
            }
        }

        return null;
    }

    public function getResourcesByType(string $typeName): array
    {
        return $this->resources[$typeName] ?? [];
    }

    public function getResource(string $typeName, string $name): ?ResourceInterface
    {
        if (!$this->hasResource($typeName, $name)) {
            throw new Exception\UnknownResourceException("$typeName/$name");
        }
        $typeResources = $this->getResourcesByType($typeName);

        return $typeResources[$name] ?? null;
    }

    public function hasResource(string $typeName, string $name): bool
    {
        $typeResources = $this->getResourcesByType($typeName);

        return isset($typeResources[$name]);
    }

    public function addResource(ResourceInterface $resource): void
    {
        $this->resources[$resource->getTypeName()][$resource->getName()] = $resource;
    }

    // public function getResources(): 
    // {
    //     return $this->resources;
    // }

    private function rglob($pattern, $flags = 0)
    {
        $files = glob($pattern, $flags);
        foreach (glob(dirname($pattern) . '/*', GLOB_ONLYDIR | GLOB_NOSORT) as $dir) {
            $files = array_merge($files, $this->rglob($dir . '/' . basename($pattern), $flags));
        }

        return $files;
    }

    public function load(string $location)
    {
        if (!is_dir($location)) {
            throw new RuntimeException("Location is not a directory: " . $location);
        }
        // === Load resources ===
        $filenames = $this->rglob($location . '/resources/*.yaml');
        foreach ($filenames as $filename) {
            if (basename($filename)[0] != '_') { // allow to quickly disable a configuration by prefixing it with an underscore
                $this->loadResourceFile($filename);
            }
        }

        // === Load scripts ===

        $this->scriptPaths = [
            __DIR__ . '/../scripts',
            $location . '/scripts',
        ];

        $this->scanScripts();

        return true;

    }

    public function validate()
    {
        foreach ($this->getResourcesByType('Host') as $host) {
            if ($this->hasResource('HostGroup', $host->getName())) {
                throw new RuntimeException("Host with same name as a HostGroup detected: " . $host->getName());
            }
        }
    }

    public function loadResourceFile(string $filename): void
    {
        if (!file_exists($filename)) {
            throw new Exception\FileNotFoundException($filename);
        }
        $yaml = file_get_contents($filename);

        $documents = explode("\n---\n", $yaml);

        foreach ($documents as $yaml) {
            if (trim($yaml, " \n\r")) {
                $config = Yaml::parse($yaml);
                $this->loadResourceConfig($config);
            }
        }
    }

    public function loadResourceConfig(array $config): void
    {
        $kind = $config['kind'];
        $className = $this->getTypeClass($kind);
        $resource = $className::fromConfig($this, $config);
        $this->addResource($resource);
    }

    /**
     * Returns array of hostnames matched by host name or host group name
     */
    private function getHostsAuto(string $name): array
    {
        if (!$name) {
            return [];
        }
        if ($this->hasResource('HostGroup', $name)) {
            $hostGroup = $this->getResource('HostGroup', $name);

            return $hostGroup->getHosts();
        }
        if ($this->hasResource('Host', $name)) {
            return [$this->getResource('Host', $name)];
        }
        throw new Exception\UnknownHostsException($name);
    }

    /**
     * Pass in name(s) as a string, csv or array of strings. Names can be host and/or hostgroup names
     */
    public function getHosts($names): array
    {
        if (is_null($names)) {
            return [];
        }
        if ($names == '*') {
            return $this->getResourcesByType('Host');
        }
        if (is_string($names)) {
            $names = explode(',', $names); // turn into array
            foreach ($names as $i => $name) {
                $names[$i] = trim($name);
            }
        }
        if (!is_array($names)) {
            throw new RuntimeException('undefined type (not string or array) passed to infra getHosts');
        }
        $res = [];
        foreach ($names as $i => $name) {
            $hosts = $this->getHostsAuto($name);
            foreach ($hosts as $host) {
                $res[$host->getName()] = $host;
            }
        }

        return $res;
    }

    public function getSshBuilder(HostResource $host)
    {
        $config = new ClientConfiguration($host->getSshAddress(), $host->getSshUsername());
        // $config->setOptions(array(
        //     'IdentityFile' => '~/.ssh/id_rsa',
        //     'IdentitiesOnly' => 'yes',
        // ));
        $builder = new ClientBuilder($config);

        return $builder;
    }

    public function copyTemplate(HostResource $host, $template, $destination)
    {
        $loader = new \Twig_Loader_Filesystem(__DIR__ . '/../../templates');
        $twig = new \Twig_Environment($loader, []);
        $data = [];
        $data['host'] = $host;
        $data['infra'] = $this;
        $tmpfile = tempnam(sys_get_temp_dir(), 'infra_');
        $content = $twig->render($template, $data);
        file_put_contents($tmpfile, $content);
        $scpBuilder = $this->getSshBuilder($host);
        $scp = $scpBuilder->buildSecureCopyClient();
        $scp->copy(
            $tmpfile,
            $scp->getRemotePath($destination)
        );
        if ($scp->getExitCode() != 0) {
            throw new RuntimeException($scp->getErrorOutput());
        }
        unlink($tmpfile);
    }

    public function getScripts()
    {
        return $this->scripts;
    }

    public function scanScripts()
    {
        foreach ($this->scriptPaths as $path) {
            $filenames = glob($path . '/{**/*,*}', GLOB_BRACE);
            foreach ($filenames as $filename) {
                $filename = realpath($filename);
                if (is_executable($filename)) {
                    $info = pathinfo($filename);

                    $name = $info['filename'];
                    $prefix = basename($info['dirname']);;
                    if ($prefix != 'scripts') {
                        $name = $prefix . ':' . $name;
                    }

                    $doc = null;
                    if (file_exists($filename . '.md')) {
                        $doc = file_get_contents($filename . '.md');
                    }

                    $script = new Script($name, $filename, $doc);
                    $this->scripts[$script->getName()] = $script;
                }
                // echo $filename . PHP_EOL;
            }
        }
    }
}
