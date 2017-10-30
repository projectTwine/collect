<?php

namespace ClassCentral\SiteBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ProfileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('aboutMe')
            ->add('location')
            ->add('fieldOfStudy')
            ->add('highestDegree')
            ->add('twitter')
            ->add('coursera')
            ->add('website')
            ->add('gplus')
            ->add('linkedin')
            ->add('facebook')
            ->add('photo')
            ->add('user')
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'ClassCentral\SiteBundle\Entity\Profile'
        ));
    }

    public function getName()
    {
        return 'classcentral_sitebundle_profiletype';
    }
}
