<?php
/**
 * Created by PhpStorm.
 * User: dhawal
 * Date: 12/15/13
 * Time: 7:44 PM
 */

namespace ClassCentral\SiteBundle\Swiftype\DocumentBuilders;


use ClassCentral\SiteBundle\Swiftype\DocumentBuilder;
use ClassCentral\SiteBundle\Swiftype\DocumentType\InstitutionDocumentType;

class InstitutionDocumentBuilder extends DocumentBuilder{

    public function getDocuments()
    {
        $em = $this->getContainer()->get('doctrine')->getManager();

        // Get all courses and create the documents
        $docs = array();
        $institutions = $em->getRepository('ClassCentralSiteBundle:Institution')->findAll();

        foreach($institutions as $ins)
        {
            $doc = new InstitutionDocumentType($ins, $this->getContainer());
            $docs[] = $doc->getDocument();
        }

        return $docs;
    }
}