<?php

namespace Infra\Model;

use RuntimeException;
use Collection\TypedArray;
use SSHClient\ClientConfiguration\ClientConfiguration;
use SSHClient\ClientBuilder\ClientBuilder;


class Infra extends BaseModel
{
    protected $hosts;
    protected $hostGroups;
    protected $firewallRules;
    protected $users;
    protected $properties;

    public function __construct()
    {
        $this->hosts = new TypedArray(Host::class);
        $this->hostGroups = new TypedArray(HostGroup::class);
        $this->properties = new TypedArray(Property::class);
        $this->firewallRules = new TypedArray(FirewallRule::class);
        $this->users = new TypedArray(User::class);
    }

    public function getHostsByExpression($expression)
    {
        $part = explode(':', $expression);
        if (count($part)!=2) {
            throw new RuntimeException("Expression should be exactly 2 parts: " . $expression);
        }
        $type = $part[0];
        $name = $part[1];
        switch ($type) {
            case 'host':
                $host = $this->getHosts()->get($name);
                return [$host];
            case 'group':
                $group = $this->getHostGroups()->get($name);
                return $group->getHosts();
            default:
                throw new RuntimeException("Unknown host expression type: " . $type);
        }
    }

    public function getSshBuilder($hostname)
    {
        $host = $this->getHosts()->get($hostname);

        $config = new ClientConfiguration($host->getConnectionAddress(), $host->getConnectionUsername());
        // $config->setOptions(array(
        //     'IdentityFile' => '~/.ssh/id_rsa',
        //     'IdentitiesOnly' => 'yes',
        // ));
        $builder = new ClientBuilder($config);
        return $builder;
    }

    public function copyTemplate($hostname, $template, $destination)
    {
        $loader = new \Twig_Loader_Filesystem(__DIR__ . '/../../templates');
        $twig = new \Twig_Environment($loader, []);
        $host = $this->getHosts()->get($hostname);
        $data = [];
        $data['host'] = $host;
        $data['infra'] = $this;

        $tmpfile = tempnam(sys_get_temp_dir(), 'infra_');

        $content = $twig->render($template, $data);
        file_put_contents($tmpfile, $content);

        $scpBuilder = $this->getSshBuilder($hostname);
        $scp = $scpBuilder->buildSecureCopyClient();
        $scp->copy(
            $tmpfile,
            $scp->getRemotePath($destination)
        );
        if ($scp->getExitCode()!=0) {
            throw new RuntimeException($scp->getErrorOutput());
        }

        unlink($tmpfile);
    }
}
