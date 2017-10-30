<?php

namespace ClassCentral\SiteBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use ClassCentral\SiteBundle\Entity\Interview;
use ClassCentral\SiteBundle\Form\InterviewType;

/**
 * Interview controller.
 *
 */
class InterviewController extends Controller
{

    /**
     * Lists all Interview entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('ClassCentralSiteBundle:Interview')->findAll();

        return $this->render('ClassCentralSiteBundle:Interview:index.html.twig', array(
            'entities' => $entities,
        ));
    }
    /**
     * Creates a new Interview entity.
     *
     */
    public function createAction(Request $request)
    {
        $entity  = new Interview();
        $form = $this->createForm(new InterviewType(), $entity);
        $form->bind($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            // Crop the instructor photo
            $this->get('image_service')->getInterviewImage( $entity->getInstructorPhoto(), $entity->getId() );

            return $this->redirect($this->generateUrl('interview_show', array('id' => $entity->getId())));
        }

        return $this->render('ClassCentralSiteBundle:Interview:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Displays a form to create a new Interview entity.
     *
     */
    public function newAction()
    {
        $entity = new Interview();
        $form   = $this->createForm(new InterviewType(), $entity);

        return $this->render('ClassCentralSiteBundle:Interview:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Finds and displays a Interview entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('ClassCentralSiteBundle:Interview')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Interview entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('ClassCentralSiteBundle:Interview:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),        ));
    }

    /**
     * Displays a form to edit an existing Interview entity.
     *
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('ClassCentralSiteBundle:Interview')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Interview entity.');
        }

        $editForm = $this->createForm(new InterviewType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('ClassCentralSiteBundle:Interview:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Edits an existing Interview entity.
     *
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('ClassCentralSiteBundle:Interview')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Interview entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createForm(new InterviewType(), $entity);
        $editForm->bind($request);

        if ($editForm->isValid()) {

            $em->persist($entity);
            $em->flush();

            // Crop the instructor photo
            $this->get('image_service')->getInterviewImage( $entity->getInstructorPhoto(), $entity->getId() );

            return $this->redirect($this->generateUrl('interview_edit', array('id' => $id)));
        }

        return $this->render('ClassCentralSiteBundle:Interview:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }
    /**
     * Deletes a Interview entity.
     *
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->bind($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('ClassCentralSiteBundle:Interview')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Interview entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('interview'));
    }

    /**
     * Creates a form to delete a Interview entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(array('id' => $id))
            ->add('id', 'hidden')
            ->getForm()
        ;
    }
}
