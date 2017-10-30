<?php
/**
 * Created by PhpStorm.
 * User: dhawal
 * Date: 12/16/13
 * Time: 9:44 PM
 */

namespace ClassCentral\SiteBundle\Swiftype\DocumentType;


use ClassCentral\SiteBundle\Swiftype\SwiftypeDocument;
use ClassCentral\SiteBundle\Swiftype\SwiftypeField;

class SubjectDocumentType extends SwiftypeDocument {

    private $type = 'slug';

    protected function getExternalId()
    {
        return $this->type . '_' . $this->getEntity()->getSlug();
    }

    protected function getFields()
    {
        $router = $this->getContainer()->get('router');
        $fields = array();
        $subject = $this->getEntity();

        // Subject name
        $fields[] = SwiftypeField::get('name', $subject->getName(), SwiftypeField::FIELD_STRING);

        // Slug
        $fields[] = SwiftypeField::get('slug',$subject->getSlug(),SwiftypeField::FIELD_STRING);

        // Course count
        $fields[] = SwiftypeField::get('courseCount', $subject->getCourseCount(), SwiftypeField::FIELD_INTEGER);

        // url
        $fields[] = SwiftypeField::get('url',$router->generate('ClassCentralSiteBundle_stream',array('slug' => $subject->getSlug())), SwiftypeField::FIELD_ENUM);

        return $fields;
    }
} 