<?php

namespace ClassCentral\SiteBundle\Utility;


class Breadcrumb {

    /**
     * Returns an array representing an item in the breadcrumb.
     * The page being displayed should be entered last
     * @param $name display name for the page
     * @param string $url for the page
     * @return array
     */
    public static function  getBreadCrumb($name, $url = '')
    {
        return array(
            'name' => $name,
            'url' => $url
        );
    }

}