<?php

namespace ClassCentral\SiteBundle\Formatters;

use ClassCentral\SiteBundle\Entity\Course;

class DefaultCourseFormatter extends CourseFormatterAbstract
{

    public function getPrice()
    {
        if($this->course->getPrice() > 0)
        {
            switch($this->course->getPricePeriod())
            {
                case Course::PRICE_PERIOD_MONTHLY:
                    return '$' . $this->course->getPrice(). '/month';
                case Course::PRICE_PERIOD_TOTAL:
                    return'$'. $this->course->getPrice();
            }
        }

        return '0';
    }

    public function getDuration()
    {
        if( $this->course->getDurationMin() && $this->course->getDurationMax() )
        {
            if ($this->course->getDurationMin() == $this->course->getDurationMax() )
            {
                return "{$this->course->getDurationMin()} weeks long";
            }
            else
            {
                return "{$this->course->getDurationMin()}-{$this->course->getDurationMax()} weeks long";
            }

        }
        return '';
    }

    public function getWorkload()
    {
        $effort = '';
        if( $this->course->getWorkloadMin() && $this->course->getWorkloadMax() )
        {
            if( $this->course->getWorkloadMin() == $this->course->getWorkloadMax() )
            {
                $effort = $this->course->getWorkloadMin();
            }
            else
            {
                $effort = "{$this->course->getWorkloadMin()}-{$this->course->getWorkloadMax()}";
            }

            switch($this->course->getWorkloadType())
            {
                case Course::WORKLOAD_TYPE_HOURS_PER_WEEK:
                    $effort .= ' hours a week';
                    break;
                case Course::WORKLOAD_TYPE_TOTAL_HOURS:
                    $effort .= ' hours worth of material';
                    break;
            }
        }

        return $effort;
    }

    public function getCertificate()
    {
        $str = '';

        if($this->course->getCertificate())
        {
            if($this->course->getCertificatePrice() == Course::PAID_CERTIFICATE)
            {
                $str = 'Paid Certificate Available';
            }
            elseif ($this->course->getCertificatePrice() > 0)
            {
                $str = '$' . $this->course->getCertificatePrice() . ' Certificate Available';
            }
            else
            {
                $str = 'Certificate Available';
            }
        }

        return $str;
    }

    /**
     *  Get all the organzations
     */
    public function getSchemaOrgs()
    {
        // Start with institutions
        $orgs = array();
        foreach($this->course->getInstitutions() as $ins)
        {
            $type =  "Organization";
            if($ins->getIsUniversity())
            {
                $type='CollegeOrUniversity';
            }

            $orgs[] = array(
                "@type" => $type,
                "name" => "{$ins->getName()}",
                "sameAs" => "{$ins->getUrl()}"
            );
        }
        if($this->course->getInitiative())
        {
            $provider = $this->course->getInitiative();
            $orgs[] = array(
                "@type" => "Organization",
                "name" => "{$provider->getName()}",
                "sameAs" => "{$provider->getUrl()}"
            );
        }
        else
        {
            if( empty($orgs) )
            {
                // create an independent provider
                $orgs[] = array(
                    "@type" => "Organization",
                    "name" => "Independent",
                    "sameAs" => "https://www.class-central.com/provider/independent"
                );
            }

        }

        return $orgs;
    }
}