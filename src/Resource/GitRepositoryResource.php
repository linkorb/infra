<?php

namespace Infra\Resource;

use GraphQL\Type\Definition\Type;
use Graph\Resource\AbstractResource;
use Graph\Graph;

class GitRepositoryResource extends AbstractResource
{
    public function getUrl()
    {
        return $this->spec['url'] ?? null;
    }

    public function getRepositoryName()
    {
        $parsedUrl = $this->parseRepositoryUrl();

        return $parsedUrl['name'];
    }

    public function getRepositoryOwnerName()
    {
        $parsedUrl = $this->parseRepositoryUrl();

        return $parsedUrl['ownerName'];
    }

    public function getSshUrl()
    {
        $parsedUrl = $this->parseRepositoryUrl();

        return 'git@' .
            $parsedUrl['provider'] . ':' .
            $parsedUrl['ownerName'] . '/' .
            $parsedUrl['name'] . '.git';
    }

    public function getHttpUrl()
    {
        $parsedUrl = $this->parseRepositoryUrl();

        return 'https://' .
            $parsedUrl['provider'] . '/' .
            $parsedUrl['ownerName'] . '/' .
            $parsedUrl['name'] . '.git';
    }

    public function getViewUrl()
    {
        $parsedUrl = $this->parseRepositoryUrl();

        return 'https://' .
            $parsedUrl['provider'] . '/' .
            $parsedUrl['ownerName'] . '/' .
            $parsedUrl['name'];
    }

    public static function getConfig(Graph $graph)
    {
        return [
            'name'   => 'GitRepository',
            'fields' => [
                'name'                => Type::id(),
                'url'                 => Type::string(),
                'description'         => Type::string(),
                'repositoryName'      => Type::string(),
                'repositoryOwnerName' => Type::string(),
                'sshUrl'              => Type::string(),
                'httpUrl'             => Type::string(),
                'viewUrl'             => Type::string(),

            ],
        ];
    }

    private function parseRepositoryUrl()
    {
        $url = $this->getUrl();

        preg_match('~^(.+)(@|\://)([^/\:@]+)(\:|/)([^/\.\:]+)/([^/]+)\.git$~', $url, $matches);

        if (7 !== count($matches)) {
            throw new \RuntimeException("Can't parse repository url");
        }

        return [
            'schema'    => $matches[1],
            'provider'  => $matches[3],
            'ownerName' => $matches[5],
            'name'      => $matches[6],
        ];
    }
}
