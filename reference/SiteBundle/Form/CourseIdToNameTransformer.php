<?php
/**
 * Created by PhpStorm.
 * User: dhawal
 * Date: 10/27/16
 * Time: 1:00 AM
 */

namespace ClassCentral\SiteBundle\Form;


use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class CourseIdToNameTransformer implements DataTransformerInterface
{
    private $manager;

    public function  __construct(ObjectManager $manager)
    {
        $this->manager = $manager;
    }

    public function transform($course)
    {
        if(null == $course)
        {
            return '';
        }

        return $course->getId();
    }


    public function reverseTransform($id)
    {
        if( !$id )
        {
            return ;
        }

        $course = $this->manager->getRepository('ClassCentralSiteBundle:Course')->find($id);

        if(null === $course)
        {
            throw new TransformationFailedException(
                "Course with $id does not exit"
            );
        }

        return $course;
    }
}