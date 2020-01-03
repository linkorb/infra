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
use Graph\Graph;

class Infra
{
    protected $graph;

    public function __construct(Graph $graph)

    {
        $this->graph = $graph;

        $graph->registerType(Resource\LabelResource::class);
        $graph->registerType(Resource\HostResource::class);
        $graph->registerType(Resource\HostGroupResource::class);
        $graph->registerType(Resource\FirewallRuleResource::class);
        $graph->registerType(Resource\UserResource::class);
        $graph->registerType(Resource\MonitoringCheckResource::class);
        $graph->registerType(Resource\DnsDomainResource::class);
        $graph->registerType(Resource\DnsRecordResource::class);
        $graph->registerType(Resource\GitRepositoryResource::class);
        $graph->registerType(Resource\CronJobResource::class);
        $graph->registerType(Resource\FileResource::class);
        $graph->registerType(Resource\OsReleaseResource::class);
        $graph->registerType(Resource\DockerEngineResource::class);
        $graph->registerType(Resource\DockerAppResource::class);
        $graph->registerType(Resource\QueryResource::class);
        $graph->registerType(Resource\BackupRuleResource::class);

        $graph->init($this);
    }

    public function getGraph()
    {
        return $this->graph;
    }

    public function getInflector()
    {
        return $this->inflector;
    }

    // public function getSchema()
    // {
    //     return $this->schema;
    // }

    public function validate()
    {
        foreach ($this->graph->getResourcesByType('Host') as $host) {
            if ($this->graph->hasResource('HostGroup', $host->getName())) {
                throw new RuntimeException("Host with same name as a HostGroup detected: " . $host->getName());
            }
        }
    }

    /**
     * Returns array of hostnames matched by host name or host group name
     */
    private function getHostsAuto(string $name): array
    {
        if (!$name) {
            return [];
        }
        if ($this->graph->hasResource('HostGroup', $name)) {
            $hostGroup = $this->graph->getResource('HostGroup', $name);

            return $hostGroup->getHosts();
        }
        if ($this->graph->hasResource('Host', $name)) {
            return [$this->graph->getResource('Host', $name)];
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

    
}
