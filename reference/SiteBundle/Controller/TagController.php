<?php
/**
 * Created by PhpStorm.
 * User: dhawal
 * Date: 9/2/16
 * Time: 10:50 PM
 */

namespace ClassCentral\SiteBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use ClassCentral\SiteBundle\Entity\Item;


class TagController extends Controller
{
    
    /**
     * Lists all the tags
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $tags = $em->getRepository('ClassCentralSiteBundle:Tag')->findAll();

        return $this->render('ClassCentralSiteBundle:Tag:index.html.twig', array(
            'tags' => $tags,
        ));

    }

    public function deleteAction($id)
    {
        $form = $this->createDeleteForm($id);
        $request = $this->getRequest();

        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('ClassCentralSiteBundle:Tag')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Tag entity.');
            }

            // delete follows when deleting tags
            $query = $em->getConnection()->prepare(
                    'DELETE FROM follows WHERE item_id=:item_id and item=:item'
                    );
            $params = array(
                    'item_id' => $id,
                    'item'=> Item::ITEM_TYPE_TAG
            );
    
            $query->execute($params);


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

    /**
     * Finds and displays a Institution entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('ClassCentralSiteBundle:Tag')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Tag entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('ClassCentralSiteBundle:Tag:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    public function copyCoursesAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $msg = null;

        if($request->isMethod('POST'))
        {
            $tagService = $this->get('tag');
            $orig = $em->getRepository('ClassCentralSiteBundle:Tag')->find( $request->request->get('orig') );
            $dup = $em->getRepository('ClassCentralSiteBundle:Tag')->find( $request->request->get('dup') );

            if( !$orig || !$dup)
            {
                $msg = 'One of the tags is invalid';
            }
            else
            {
                $count = $tagService->copyCourses($orig,$dup);
                $msg = 'Number of Courses copied - ' . $count;
            }
        }

        return $this->render('ClassCentralSiteBundle:Tag:copy.courses.html.twig',array(
            'msg' => $msg
        ));

    }
}