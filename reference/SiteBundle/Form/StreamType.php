<?php

namespace ClassCentral\SiteBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Doctrine\ORM\EntityRepository;

class StreamType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('slug')
            ->add('description')
            ->add('imageUrl')
            ->add('parentStream','entity',array(
                'required' => false,
                'empty_value' => true,
                'class'=>'ClassCentralSiteBundle:Stream',
                'query_builder' => function(EntityRepository $er) {
                        return $er->createQueryBuilder('s')
                            ->orderBy('s.name', 'ASC')->where('s.parentStream is NULL');
                },
             ))
             ->add('color')
             ->add('childColor')
             ->add('displayOrder')
        ;
    }

    public function getName()
    {
        return 'classcentral_sitebundle_streamtype';
    }
}
