<?php
/**
 * Created by PhpStorm.
 * User: dhawal
 * Date: 12/16/13
 * Time: 9:53 PM
 */

namespace ClassCentral\SiteBundle\Swiftype\DocumentBuilders;


use ClassCentral\SiteBundle\Controller\StreamController;
use ClassCentral\SiteBundle\Swiftype\DocumentBuilder;
use ClassCentral\SiteBundle\Swiftype\DocumentType\SubjectDocumentType;

class SubjectDocumentBuilder extends DocumentBuilder{

    public function getDocuments()
    {
        $em = $this->getContainer()->get('doctrine')->getManager();

        // Use the same count as
        $controller = new StreamController();
        $subjects = $controller->getSubjectsList( $this->getContainer() );
        $docs = array();

        foreach($subjects['parent'] as $subject)
        {
            $doc = new SubjectDocumentType($subject, $this->getContainer());
            $docs[] = $doc->getDocument();
        }

        foreach($subjects['children'] as $childSubjects)
        {
            foreach($childSubjects as $subject)
            {
                $doc = new SubjectDocumentType($subject, $this->getContainer());
                $docs[] = $doc->getDocument();
            }
        }

        return $docs;
    }
}