<?php

namespace ClassCentral\SiteBundle\Services;

use Guzzle\Http\Client;
use Symfony\Component\DependencyInjection\ContainerInterface;

class React
{
    private $container;
    private $cacheReact;

    public function __construct(ContainerInterface $container, $cacheReact)
    {
        $this->container = $container;
        $this->cacheReact = $cacheReact;
    }

    public function component($name, $request_type = false, $data = [])
    {
        $cache = $this->container->get('cache');
        return $this->getComponent($name, $request_type, $data);

        if($this->cacheReact && !$data['user']['loggedIn'])
        {
            return $cache->get('react_component_' . $name . '_' . $request_type, [$this, 'getComponent'], [$name, $request_type, $data]);
        }
        else
        {
            return $this->getComponent($name, $request_type, $data);
        }
    }

    public function getComponent($name, $request_type, $data)
    {
        try
        {
            $reactServerUrl = $this->container->getParameter('react_server');
            $httpRequestObject = $this->container->get('request');
            $client = new Client();
            $request = $client->post($reactServerUrl.'/' . $name, array(
                'content-type' => 'application/json',
                'User-Agent' => $httpRequestObject->headers->get('User-Agent')
            ));
            $request->setBody(json_encode($data));
            $response = $request->send();
            $content = json_decode($response->getBody(), true);

            if ($request_type == 'state') {
                return json_encode($content['initialState']);
            } else {
                return $content['componentString'];
            }
        } catch ( \Exception $e)
        {

          if ($request_type == 'state') {
            return json_encode($this->container->get('api')->getNavbarData());
          } else {
            return "";
          }
        }
    }
}
