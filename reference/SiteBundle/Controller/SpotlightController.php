<?php

namespace ClassCentral\SiteBundle\Controller;

use ClassCentral\SiteBundle\Entity\Course;
use ClassCentral\SiteBundle\Services\Kuber;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use ClassCentral\SiteBundle\Entity\Spotlight;
use ClassCentral\SiteBundle\Form\SpotlightType;

/**
 * Spotlight controller.
 *
 */
class SpotlightController extends Controller
{

    /**
     * Lists all Spotlight entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('ClassCentralSiteBundle:Spotlight')->findAll();

        return $this->render('ClassCentralSiteBundle:Spotlight:index.html.twig', array(
            'entities' => $entities,
        ));
    }

    /**
     * Finds and displays a Spotlight entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('ClassCentralSiteBundle:Spotlight')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Spotlight entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('ClassCentralSiteBundle:Spotlight:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),        ));
    }

    /**
     * Displays a form to edit an existing Spotlight entity.
     *
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('ClassCentralSiteBundle:Spotlight')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Spotlight entity.');
        }


        $editForm = $this->createForm(new SpotlightType( $this->getValidCourses() ), $entity);

        return $this->render('ClassCentralSiteBundle:Spotlight:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
        ));
    }

    /**
     * Edits an existing Spotlight entity.
     *
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('ClassCentralSiteBundle:Spotlight')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Spotlight entity.');
        }

        $editForm = $this->createForm(new SpotlightType( $this->getValidCourses() ), $entity);
        $editForm->bind($request);

        if ($editForm->isValid()) {

            // Update the spotlight fields from the course
            if( $entity->getType() == Spotlight::SPOTLIGHT_TYPE_COURSE && $entity->getCourse() )
            {

                $course = $entity->getCourse();
                if( $entity->getTitle() == '' ) // Allow for overwriting of title
                {
                    $entity->setTitle( $course->getName() );
                }
                $entity->setDescription ( $course->getOneliner() );
                $url =  $this->get('router')->generate('ClassCentralSiteBundle_mooc', array('id' => $course->getId(),'slug' => $course->getSlug() ));
                $entity->setUrl( $url );

                // Set the image url either from the course that was provider provided or the one manually updated in the thumbnail
                if($this->getCourseImage( $course->getId() ))
                {
                    $entity->setImageUrl( $this->getCourseImage( $course->getId())  );
                }
                else
                {
                    // Directly put url in the thumbnail field
                    $entity->setImageUrl( $course->getThumbnail() );
                }
            }

            $em->persist($entity);
            $em->flush();

            // Flush the cache
            $cache = $this->get('Cache');
            $cache->deleteCache ('spotlight_cache');



            return $this->redirect($this->generateUrl('spotlight_edit', array('id' => $id)));
        }

        return $this->render('ClassCentralSiteBundle:Spotlight:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
        ));
    }

    private function getCourseImage( $cid )
    {
        $kuber = $this->container->get('kuber');
        $url = $kuber->getUrl( Kuber::KUBER_ENTITY_COURSE ,Kuber::KUBER_TYPE_COURSE_IMAGE, $cid );
        return $url;
    }

    private function getValidCourses()
    {
        // Filter to show only courses which have one line and image field set
        $em = $this->getDoctrine()->getManager();
        $coursesQuery = $em->createQueryBuilder();
        $coursesQuery
            ->add('select', 'c')
            ->add('from','ClassCentralSiteBundle:Course c')
            ->where("c.oneliner != ''");
        $courses = $coursesQuery->getQuery()->getResult();

        return $courses;
    }

    /**
     * Shows a page which shows the current mooc report spotlight
     * abd a button to right shift this spotlight
     * @param Request $request
     */
    public function moocReportSpotlightAction(Request $request)
    {
        $spotlights = $this->get('cache')->get('spotlight_cache',function(){
            $s = $this
                ->getDoctrine()->getManager()
                ->getRepository('ClassCentralSiteBundle:Spotlight')->findAll();

            $spotlights = array();
            foreach($s as $item)
            {
                $spotlights[$item->getPosition()] = $item;
            }

            return $spotlights;
        }, array());

        return $this->render('ClassCentralSiteBundle:Spotlight:mooc.report.spotlight.html.twig', array(
            'spotlights' => $spotlights,
            'spotlightMap' => Spotlight::$spotlightMap,
        ));
    }

    /**
     * Moves all the spotlight cards in MOOC Report section to 1 spot in the right
     * this makes space for a new spotlight
     * @param Request $request
     */
    public function moocReportSpotlightRightShiftAction(Request $request)
    {
        $spotlightService = $this->container->get('spotlight');

        $response = $spotlightService->spotlightCopy(16, 17);
        $response = $spotlightService->spotlightCopy(15, 16);
        $response = $spotlightService->spotlightCopy(14, 15);
        $response = $spotlightService->spotlightCopy(13, 14);

        return $this->redirect( $this->generateUrl('spotlight_mooc_report'));
    }

}
