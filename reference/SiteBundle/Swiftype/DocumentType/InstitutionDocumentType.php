<?php
/**
 * Created by PhpStorm.
 * User: dhawal
 * Date: 12/15/13
 * Time: 7:02 PM
 */

namespace ClassCentral\SiteBundle\Swiftype\DocumentType;


use ClassCentral\SiteBundle\Swiftype\SwiftypeDocument;
use ClassCentral\SiteBundle\Swiftype\SwiftypeField;

class InstitutionDocumentType extends SwiftypeDocument {

    private $type = 'university';

    protected function getExternalId()
    {
        return $this->type . '_' . $this->getEntity()->getId();
    }

    protected function getFields()
    {
        $router = $this->getContainer()->get('router');
        $repository = $this->getContainer()->get('doctrine')->getManager()->getRepository('ClassCentralSiteBundle:Institution');

        $fields = array();
        $institution = $this->getEntity();


        // Institution name
        $fields[] = SwiftypeField::get('name', $institution->getName(), SwiftypeField::FIELD_STRING);

        // Institution course count
        $fields[] = SwiftypeField::get('courseCount',$repository->getCourseCountByInstitution($institution),SwiftypeField::FIELD_INTEGER);

        // Insititution slug
        $fields[] = SwiftypeField::get('slug', $institution->getSlug(), SwiftypeField::FIELD_STRING);


        // Institution url
        $route = 'ClassCentralSiteBundle_institution';
        if($institution->getIsUniversity())
        {
            $route = 'ClassCentralSiteBundle_university';
        }
        $url = $router->generate($route, array('slug' => $institution->getSlug()));
        $fields[] = SwiftypeField::get('url', $url, SwiftypeField::FIELD_ENUM);

        return $fields;
    }


}