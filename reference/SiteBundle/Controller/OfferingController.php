<?php

namespace ClassCentral\SiteBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use ClassCentral\SiteBundle\Entity\Offering;
use ClassCentral\SiteBundle\Form\OfferingType;
use Symfony\Component\HttpFoundation\Request;

/**
 * Offering controller.
 *
 */
class OfferingController extends Controller
{
    /**
     * Lists all Offering entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('ClassCentralSiteBundle:Offering')->findAll();

        return $this->render('ClassCentralSiteBundle:Offering:index.html.twig', array(
            'entities' => $entities
        ));
    }

    /**
     * Finds and displays a Offering entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('ClassCentralSiteBundle:Offering')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Offering entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('ClassCentralSiteBundle:Offering:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),

        ));
    }

    /**
     * Displays a form to create a new Offering entity.
     *
     */
    public function newAction(Request $request,$id)
    {
        $entity = new Offering();

        // Defaults
        $entity->setStatus(Offering::START_DATES_KNOWN);
        $startDate = new \DateTime();
        //$startDate->sub(new \DateInterval('P60D'));
        $entity->setStartDate( $startDate );

        $endDate = new \DateTime();
        $endDate->add(new \DateInterval('P30D'));
        $entity->setEndDate( $endDate );
        
        $em = $this->getDoctrine()->getManager();
        // Cloning the entity
        if($id) {
            $type = $request->query->get('type');
            if( $type == 'selfpaced' )
            {
                // Get the course
                $course = $em->getRepository('ClassCentralSiteBundle:Course')->find($id);
                $entity->setStatus( Offering::COURSE_OPEN );
                $entity->setCourse( $course );

            }
            else
            {
                $entity = $em->getRepository('ClassCentralSiteBundle:Offering')->find($id);
                $entity->setShortName( null );
                $entity->setUrl( null );
            }

        }
        $form   = $this->createForm(new OfferingType($em), $entity);

        return $this->render('ClassCentralSiteBundle:Offering:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView()
        ));
    }

    /**
     * Creates a new Offering entity.
     *
     */
    public function createAction()
    {
        $entity  = new Offering();
        $request = $this->getRequest();
        $form    = $this->createForm(new OfferingType($this->getDoctrine()->getManager()), $entity);
        $form->handleRequest($request);

        if ($form->isValid()) {                      
            $entity->setCreated(new \DateTime);
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            // invalidate the cache
            $this->get('cache')->deleteCache( 'course_'.$entity->getCourse()->getId() );

            return $this->redirect($this->generateUrl('offering_show', array('id' => $entity->getId())));
            
        }

        return $this->render('ClassCentralSiteBundle:Offering:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView()
        ));
    }

    /**
     * Displays a form to edit an existing Offering entity.
     *
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('ClassCentralSiteBundle:Offering')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Offering entity.');
        }

        $editForm = $this->createForm(new OfferingType($em), $entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('ClassCentralSiteBundle:Offering:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Edits an existing Offering entity.
     *
     */
    public function updateAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('ClassCentralSiteBundle:Offering')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Offering entity.');
        }

        $editForm   = $this->createForm(new OfferingType($em), $entity);
        $deleteForm = $this->createDeleteForm($id);

        $request = $this->getRequest();

        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->persist($entity);
            $em->flush();

            $this->get('cache')->deleteCache( 'course_'.$entity->getCourse()->getId() );

            return $this->redirect($this->generateUrl('offering_edit', array('id' => $id)));
        }

        return $this->render('ClassCentralSiteBundle:Offering:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a Offering entity.
     *
     */
    public function deleteAction($id)
    {
        $form = $this->createDeleteForm($id);
        $request = $this->getRequest();

        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('ClassCentralSiteBundle:Offering')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Offering entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('ClassCentralSiteBundle_admin'));
    }

    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(array('id' => $id))
            ->add('id', 'hidden')
            ->getForm()
        ;
    }
}
