<?php

namespace ClassCentral\SiteBundle\Form;

use ClassCentral\SiteBundle\Entity\Spotlight;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class SpotlightType extends AbstractType
{
    private $courses;

    public function __construct( $courses )
    {
        $this->courses = $courses;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            //->add('position')
            ->add('title',null,array('required' => false))
            ->add('description')
            ->add('url')
            ->add('imageUrl')
            ->add('type','choice', array('choices'=>Spotlight::$spotlights))
            ->add('course', 'entity', array(
                'choices' => $this->courses,
                'empty_value' => true,
                'required'  => false,
                'class' => 'ClassCentralSiteBundle:Course'
            ))
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'ClassCentral\SiteBundle\Entity\Spotlight'
        ));
    }

    public function getName()
    {
        return 'classcentral_sitebundle_spotlighttype';
    }
}
