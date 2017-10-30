<?php
/**
 * Created by PhpStorm.
 * User: dhawal
 * Date: 12/15/13
 * Time: 12:11 PM
 */

namespace ClassCentral\SiteBundle\Swiftype\DocumentBuilders;


use ClassCentral\SiteBundle\Swiftype\DocumentBuilder;
use ClassCentral\SiteBundle\Swiftype\DocumentType\CourseDocumentType;

class CourseDocumentBuilder extends DocumentBuilder {


    public function getDocuments()
    {
        $em = $this->getContainer()->get('doctrine')->getManager();

        // Get all courses and create the documents
        $docs = array();
        $courses = $em->getRepository('ClassCentralSiteBundle:Course')->findAll();

        foreach($courses as $course)
        {
            $doc = new CourseDocumentType($course, $this->getContainer());
            $docs[] = $doc->getDocument();
        }

        return $docs;

    }
}