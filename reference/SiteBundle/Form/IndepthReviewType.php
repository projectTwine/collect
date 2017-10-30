<?php

namespace ClassCentral\SiteBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class IndepthReviewType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('course')
            ->add('summary')
            ->add('url')
            ->add('rating')
            ->add('user_id','integer',array('mapped'=>false,'required' => true))
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'ClassCentral\SiteBundle\Entity\IndepthReview'
        ));
    }

    public function getName()
    {
        return 'classcentral_sitebundle_indepthreviewtype';
    }
}
