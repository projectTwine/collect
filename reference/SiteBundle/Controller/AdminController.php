<?php
/**
 * Created by JetBrains PhpStorm.
 * User: dhawal
 * Date: 6/13/13
 * Time: 9:11 PM
 * To change this template use File | Settings | File Templates.
 */

namespace ClassCentral\SiteBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class AdminController extends Controller {

    public function indexAction()
    {
        return $this->render('ClassCentralSiteBundle:Admin:index.html.twig');
    }
}