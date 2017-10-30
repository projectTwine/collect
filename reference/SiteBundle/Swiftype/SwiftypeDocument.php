<?php
/**
 * Created by PhpStorm.
 * User: dhawal
 * Date: 12/15/13
 * Time: 11:00 AM
 */

namespace ClassCentral\SiteBundle\Swiftype;

use Symfony\Component\DependencyInjection\ContainerInterface;


abstract class SwiftypeDocument {

    public $external_id;

    public $fields = array();

    private $entity;

    private $container;

    const TYPE_COURSE = 'courses';
    const TYPE_UNIVERSITY = 'universities';
    const TYPE_PROVIDER = 'providers';

    public function __construct($entity, ContainerInterface $container )
    {
        $this->entity = $entity;
        $this->container = $container;
    }

    abstract protected function getExternalId();

    abstract protected function getFields();

    protected function getEntity()
    {
        return $this->entity;
    }

    protected function getContainer()
    {
        return $this->container;
    }

    public function getDocument()
    {
        $this->external_id = $this->getExternalId();
        $this->fields = $this->getFields();

        return $this;
    }

} 