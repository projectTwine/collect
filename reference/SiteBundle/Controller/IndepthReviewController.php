<?php

namespace ClassCentral\SiteBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use ClassCentral\SiteBundle\Entity\IndepthReview;
use ClassCentral\SiteBundle\Form\IndepthReviewType;

/**
 * IndepthReview controller.
 *
 */
class IndepthReviewController extends Controller
{

    /**
     * Lists all IndepthReview entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('ClassCentralSiteBundle:IndepthReview')->findAll();

        return $this->render('ClassCentralSiteBundle:IndepthReview:index.html.twig', array(
            'entities' => $entities,
        ));
    }
    /**
     * Creates a new IndepthReview entity.
     *
     */
    public function createAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $entity  = new IndepthReview();
        $form = $this->createForm(new IndepthReviewType(), $entity);


        $form->bind($request);
        $userId = $form->get('user_id')->getData();
        $user = $em->getRepository('ClassCentralSiteBundle:User')->find( $userId );

        if ($form->isValid() && $user ) {
            $entity->setUser( $user );
            $em->persist($entity);
            $em->flush();
            $this->get('cache')->deleteCache( 'course_'. $entity->getCourse()->getId() );

            return $this->redirect($this->generateUrl('indepthreview_show', array('id' => $entity->getId())));
        }

        return $this->render('ClassCentralSiteBundle:IndepthReview:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Displays a form to create a new IndepthReview entity.
     *
     */
    public function newAction()
    {
        $entity = new IndepthReview();
        $form   = $this->createForm(new IndepthReviewType(), $entity);

        return $this->render('ClassCentralSiteBundle:IndepthReview:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Finds and displays a IndepthReview entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('ClassCentralSiteBundle:IndepthReview')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find IndepthReview entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('ClassCentralSiteBundle:IndepthReview:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),        ));
    }

    /**
     * Displays a form to edit an existing IndepthReview entity.
     *
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('ClassCentralSiteBundle:IndepthReview')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find IndepthReview entity.');
        }

        $editForm = $this->createForm(new IndepthReviewType(), $entity);
        $editForm['user_id']->setData( $entity->getUser()->getId() );
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('ClassCentralSiteBundle:IndepthReview:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Edits an existing IndepthReview entity.
     *
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('ClassCentralSiteBundle:IndepthReview')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find IndepthReview entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createForm(new IndepthReviewType(), $entity);
        $editForm->bind($request);

        $userId = $editForm->get('user_id')->getData();
        $user = $em->getRepository('ClassCentralSiteBundle:User')->find( $userId );

        if ($editForm->isValid() && $user) {
            $entity->setUser( $user );
            $em->persist($entity);
            $em->flush();

            // invalidate the course cache
            $this->get('cache')->deleteCache( 'course_'. $entity->getCourse()->getId() );

            return $this->redirect($this->generateUrl('indepthreview_edit', array('id' => $id)));
        }

        return $this->render('ClassCentralSiteBundle:IndepthReview:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }
    /**
     * Deletes a IndepthReview entity.
     *
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->bind($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('ClassCentralSiteBundle:IndepthReview')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find IndepthReview entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('indepthreview'));
    }

    /**
     * Creates a form to delete a IndepthReview entity by id.
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
