<?php
/**
 * Created by PhpStorm.
 * User: dhawal
 * Date: 3/12/17
 * Time: 11:15 AM
 */

namespace ClassCentral\SiteBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class RedirectingController extends Controller
{
    public function removeTrailingSlashAction(Request $request)
    {
        $pathInfo = $request->getPathInfo();
        $requestUri = $request->getRequestUri();

        $url = str_replace($pathInfo, rtrim($pathInfo, ' /'), $requestUri);

        return $this->redirect($url, 301);
    }
}