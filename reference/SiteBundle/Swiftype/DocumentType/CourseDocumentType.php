<?php
/**
 * Created by PhpStorm.
 * User: dhawal
 * Date: 12/15/13
 * Time: 10:38 AM
 */

namespace ClassCentral\SiteBundle\Swiftype\DocumentType;

use ClassCentral\SiteBundle\Entity\Course;
use ClassCentral\SiteBundle\Swiftype\SwiftypeDocument;
use ClassCentral\SiteBundle\Swiftype\SwiftypeField;

/**
 * Course document type
 * Class CourseDocumentType
 * @package ClassCentral\SiteBundle\Swiftype\DocumentType
 */
class CourseDocumentType extends SwiftypeDocument {

    private $type = 'course';

    protected function getExternalId()
    {
        return $this->type . '_' . $this->getEntity()->getId();
    }

    protected function getFields()
    {
        $fields = array();
        $course = $this->getEntity();
        $router = $this->getContainer()->get('router');

        // Course name
        $fields[] = SwiftypeField::get('name', $course->getName(),SwiftypeField::FIELD_STRING);

        // Course url;
        $fields[] = SwiftypeField::get(
            'url',
            $router->generate('ClassCentralSiteBundle_mooc', array('id'=>$course->getId(), 'slug' => $course->getSlug() )),
            SwiftypeField::FIELD_ENUM
        );

        // provider
        $provider = 'Independent';
        if($course->getInitiative())
        {
            $provider = $course->getInitiative()->getName();
        }
        $fields[] = SwiftypeField::get('provider', $provider, SwiftypeField::FIELD_ENUM);

        // Institutions
        $institutions = array();
        if($course->getInstitutions())
        {
            foreach($course->getInstitutions() as $ins)
            {
                $institutions[] = $ins->getName();
            }
        }
        $fields[] = SwiftypeField::get('institutions', $institutions, SwiftypeField::FIELD_ENUM);

        // Instructors
        $instructors = array();
        if($course->getInstructors())
        {
            foreach($course->getInstructors() as $ins)
            {
                $instructors[] = $ins->getName();
            }
        }
        $fields[] = SwiftypeField::get('instructors', $instructors, SwiftypeField::FIELD_TEXT);

        // Status
        $fields[] = SwiftypeField::get('status', $course->getStatus(), SwiftypeField::FIELD_INTEGER);

        // Display date
        $nextOffering = $course->getNextOffering();
        if($nextOffering)
        {
            $fields[] = SwiftypeField::get('displayDate', $nextOffering->getDisplayDate(), SwiftypeField::FIELD_ENUM);
        }

        return $fields;
    }
}