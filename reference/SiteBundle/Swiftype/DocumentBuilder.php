<?php
/**
 * Created by PhpStorm.
 * User: dhawal
 * Date: 12/15/13
 * Time: 12:09 PM
 */

namespace ClassCentral\SiteBundle\Swiftype;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * A builder is used to build all docs for a particular DocumentType
 * Class DocumentBuilder
 * @package ClassCentral\SiteBundle\Swiftype
 */
abstract class DocumentBuilder {

    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    protected function getContainer()
    {
        return $this->container;
    }

    abstract public function getDocuments();
} 