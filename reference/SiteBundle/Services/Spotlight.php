<?php

namespace ClassCentral\SiteBundle\Services;

use Symfony\Component\DependencyInjection\ContainerInterface;

class Spotlight {

    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     *
     */
    public function spotlightCopy($fromSpotlightId, $toSpotlightId)
    {
        $em = $this->container->get('doctrine')->getManager();
        $spotlight = $em->getRepository('ClassCentralSiteBundle:Spotlight');

        $from = $spotlight->find( $fromSpotlightId );
        $to = $spotlight->find($toSpotlightId);
        if(!$from || !$to)
        {
            throw new \Exception("Spotlight does not exist");
        }
        $oldName = $to->getTitle();

        $to->setTitle( $from->getTitle() );
        $to->setDescription( $from->getDescription() );
        $to->setUrl( $from->getUrl() );
        $to->setType($from->getType() );
        $to->setImageUrl( $from->getImageUrl() );

        $em->persist( $to );
        $em->flush();

        $this->spotlightClearCache();

        return array(
            $from->getTitle(),
            $oldName
        );
    }

    /**
     * Clears the spotlight Cache
     */
    public function spotlightClearCache()
    {
        $cache = $this->container->get('Cache');
        $cache->deleteCache ('spotlight_cache');
    }
}