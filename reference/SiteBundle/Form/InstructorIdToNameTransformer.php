<?php
/**
 * Created by PhpStorm.
 * User: dhawal
 * Date: 10/27/16
 * Time: 12:49 PM
 */

namespace ClassCentral\SiteBundle\Form;


use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\UnexpectedTypeException;

class InstructorIdToNameTransformer implements DataTransformerInterface
{
    private $manager;

    public function  __construct(ObjectManager $manager)
    {
        $this->manager = $manager;
    }

    public function transform($instructors)
    {
        if(null == $instructors)
        {
            return '';
        }

        $ids = array();
        foreach($instructors as $ins)
        {
            $ids[] = $ins->getId();
        }

        $ids = implode(",", $ids);

        return $ids;
    }


    public function reverseTransform($ids)
    {
        if ('' === $ids || null === $ids) {
            return array();
        }

        if (!is_string($ids)) {
            throw new UnexpectedTypeException($ids, 'string');
        }

        $idsArray = explode(",", $ids);
        $idsArray = array_filter ($idsArray, 'is_numeric');
        $instructors = $this->manager->getRepository('ClassCentralSiteBundle:Instructor')->findById($idsArray);

        if(null === $instructors)
        {
            throw new TransformationFailedException(
                "Instructor with $ids does not exit"
            );
        }

        return $instructors;
    }

}