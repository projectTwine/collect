<?php

namespace ClassCentral\SiteBundle\Services;

use Symfony\Component\DependencyInjection\ContainerInterface;

class Asset
{
    private $container;


    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getBundledAssetFileName( $key )
    {
        $webPackDevServer = $this->container->getParameter('webpack_dev_server');
        $env = $this->container->getParameter('kernel.environment');
        if($webPackDevServer && $env != 'prod')
        {
            return "https://localhost:8081/" . $this->getAssetFileName($key,$this->getManifest('manifest.dev.json'));
        }

        return "/webpack/" . $this->getAssetFileName($key,$this->getManifest('manifest.prod.json'));
    }

    private function getManifest($fileName)
    {
        return json_decode(file_get_contents(__DIR__."/../../../../web/webpack/$fileName"),true);
    }

    private function getAssetFileName($key, $manifest)
    {
        if(isset($manifest[$key]))
        {
            return $manifest[$key];
        }

        return '';
    }

}