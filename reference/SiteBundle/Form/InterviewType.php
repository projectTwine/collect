<?php

namespace ClassCentral\SiteBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class InterviewType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('summary')
            ->add('title')
            ->add('url')
            ->add('instructorName')
            ->add('instructorPhoto',null,array('required' => true ))
            ->add('courses',null,array('required' => false ))
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'ClassCentral\SiteBundle\Entity\Interview'
        ));
    }

    public function getName()
    {
        return 'classcentral_sitebundle_interviewtype';
    }
}
