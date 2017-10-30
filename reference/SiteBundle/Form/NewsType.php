<?php

namespace ClassCentral\SiteBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class NewsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title')
            ->add('url')
            ->add('description')
            ->add('localImageUrl')
            ->add('remoteImageUrl')
        ;
    }

    public function getName()
    {
        return 'classcentral_sitebundle_newstype';
    }
}
