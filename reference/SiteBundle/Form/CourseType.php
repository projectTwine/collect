<?php

namespace ClassCentral\SiteBundle\Form;

use ClassCentral\SiteBundle\Entity\Course;
use ClassCentral\SiteBundle\Entity\CourseStatus;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class CourseType extends AbstractType {

    /**
     * If true does not show fields like instructors, institutions, tags, to load faster
     * @var bool
     */
    private $lite = false;
    private $manager;

    public function __construct(ObjectManager $manager)
    {
        $this->manager = $manager;
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {

        $entity = $builder->getData();
        $instructors = array();
        foreach($entity->getInstructors() as $ins)
        {
            $instructors[] = $ins->getName();
        }

        $builder
            ->add('name')
            ->add('isMOOC','checkbox', array('label' => 'IS MOOC','required'=>false))
            ->add('description', null, array('required'=>false))
            ->add('longDescription', null, array('required'=>false))
            ->add('syllabus', null, array('required'=>false))
            ->add('shortName',null, array('required'=>false))
            ->add('status','choice',array('choices' => CourseStatus::getStatuses()))
            ->add('stream', 'entity', array(
                'label' => 'Primary Subject',
                'class' => 'ClassCentralSiteBundle:Stream',
                'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('s')
                        ->orderBy('s.name', 'ASC');
                },
            ))
            ->add('subjects',null,array(
                'label' => 'Secondary Subjects',
                'required' => false,
                'empty_value'=>true,
            ))

            ->add('initiative', 'entity', array(
                'required'=>false,
                'empty_value' => true,
                'class' => 'ClassCentralSiteBundle:Initiative',
                'query_builder' => function(EntityRepository $er){
                    return $er->createQueryBuilder('i')->orderBy('i.name','ASC');
                }
             ));

            $builder->add('institutions', null, array(
                'required'=>false,
                'empty_value'=>true,
                'class' => 'ClassCentralSiteBundle:Institution',
                'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('i')->orderBy('i.name','ASC');
                }
            ))
                ->add('instructors', 'text', array(
                'required'=>false,
                'label' =>'Instructor Ids',
                'read_only' => true,
                'attr' => array('style' => 'color: #DCDAD1')

            ))
                ->add('instructors_search', 'text', array(
                    'required'=>false, 'mapped'=>false,
                    'data' => implode(', ',$instructors),
                    'attr' => array('style' => 'width: 400px'),
                    'required'=>false

                ))
                ->add('careers', null, array(
                    'required'=>false,
                    'empty_value'=>true,
                    'class' => 'ClassCentralSiteBundle:Career',
                    'query_builder' => function(EntityRepository $er) {
                        return $er->createQueryBuilder('i')->orderBy('i.name','DESC');
                    }
                ))
            ;


        $builder->add('language',null,array('required'=>false,'empty_value' => true))
            ->add('url')
            ->add('videoIntro')
            ->add('price')
            ->add('pricePeriod','choice', array('choices'=> Course::$PRICE_PERIODS))
            ->add('certificate')
            ->add('certificatePrice')
            ->add('workloadType','choice', array('choices'=> Course::$WORKLOAD))
            ->add('workloadMin',null,array('label'=>'Min Effort Required (in hours)'))
            ->add('workloadMax',null,array('label'=>'Max Effort Required (in hours)'))
            ->add('durationMin',null,array('label' => 'Min Duration Min (in Weeks)' ))
            ->add('durationMax',null,array('label' => 'Max Duration Max (in Weeks)' ))
            //->add('searchDesc')
            ->add('one_liner',null,array('required' => false))
            ->add('thumbnail')
            ->add('interview')
        ;

        $builder->get('instructors')
            ->addModelTransformer(new InstructorIdToNameTransformer($this->manager));
      
    }

    public function getName() {
        return 'classcentral_sitebundle_coursetype';
    }
        
}
