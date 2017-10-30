<?php

namespace ClassCentral\SiteBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class InstitutionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('url')
            ->add('slug')
            ->add('isUniversity', null, array('required' => false))
            ->add('description')
            ->add('imageUrl')
            ->add('country', null, array('required' => false))
            ->add('continent', null, array('required' => false))
        ;
    }

    public function getName()
    {
        return 'classcentral_sitebundle_institutiontype';
    }
}
