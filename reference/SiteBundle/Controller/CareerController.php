<?php

namespace ClassCentral\SiteBundle\Controller;

use ClassCentral\SiteBundle\Entity\Item;
use ClassCentral\SiteBundle\Entity\UserCourse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use ClassCentral\SiteBundle\Entity\Career;
use ClassCentral\SiteBundle\Form\CareerType;

/**
 * Career controller.
 *
 */
class CareerController extends Controller
{

    /**
     * Lists all Career entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('ClassCentralSiteBundle:Career')->findAll();

        return $this->render('ClassCentralSiteBundle:Career:index.html.twig', array(
            'entities' => $entities,
        ));
    }
    /**
     * Creates a new Career entity.
     *
     */
    public function createAction(Request $request)
    {
        $entity  = new Career();
        $form = $this->createForm(new CareerType(), $entity);
        $form->bind($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('career_show', array('id' => $entity->getId())));
        }

        return $this->render('ClassCentralSiteBundle:Career:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Displays a form to create a new Career entity.
     *
     */
    public function newAction()
    {
        $entity = new Career();
        $form   = $this->createForm(new CareerType(), $entity);

        return $this->render('ClassCentralSiteBundle:Career:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Finds and displays a Career entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('ClassCentralSiteBundle:Career')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Career entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('ClassCentralSiteBundle:Career:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),        ));
    }

    /**
     * Displays a form to edit an existing Career entity.
     *
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('ClassCentralSiteBundle:Career')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Career entity.');
        }

        $editForm = $this->createForm(new CareerType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('ClassCentralSiteBundle:Career:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Edits an existing Career entity.
     *
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('ClassCentralSiteBundle:Career')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Career entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createForm(new CareerType(), $entity);
        $editForm->bind($request);

        if ($editForm->isValid()) {
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('career_edit', array('id' => $id)));
        }

        return $this->render('ClassCentralSiteBundle:Career:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }
    /**
     * Deletes a Career entity.
     *
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->bind($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('ClassCentralSiteBundle:Career')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Career entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('career'));
    }

    /**
     * Creates a form to delete a Career entity by id.
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

    /**
     * A way to update courses in bulk
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function bulkUpdateAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $careers = $em->getRepository('ClassCentralSiteBundle:Career')->findAll();

        $postFields = $request->request->all();
        $succeeded = array();
        $failed = array();

        if(isset($postFields["career"]) && isset($postFields["courses"]))
        {

            // Form has been posted. Update the subject
            $career = $em->getRepository('ClassCentralSiteBundle:Career')->findOneBy(array('id' => $postFields['career']));
            if($career && $postFields["courses"] )
            {
                $courses = explode(PHP_EOL, $postFields["courses"]);
                foreach($courses as $courseRow)
                {
                    $courseParts = explode('|||', $courseRow);
                    $courseId = $courseParts[0];
                    $course = $em->getRepository('ClassCentralSiteBundle:Course')->find($courseId);
                    if($course)
                    {
                        if(!$course->getCareers()->contains($career))
                        {
                            $course->addCareer($career);
                            $em->persist( $course );
                            $this->get('cache')->deleteCache( 'course_'.$courseId );
                        }
                        $succeeded[ $courseId ] = $courseParts[1];
                    }
                    else
                    {
                        $failed[ $courseId ] = $courseParts[1];
                    }
                }
                $em->flush();
            }
        }

        return $this->render('ClassCentralSiteBundle:Career:bulkUpdate.html.twig', array(
            'careers' => $careers,
            'succeeded' => $succeeded,
            'failed' => $failed
        ));
    }

    /**
     * Adding a careers action
     * @param Request $request
     */
    public function careerAction(Request $request, $slug)
    {
        $cl = $this->get('course_listing');
        $data = $cl->byCareer($slug,$request);
        $career = $data['career'];

        $pageMetadata = [
            'career_id' => $career->getId(),
            'career_name' => $career->getName(),
            'career_slug' => strtolower($career->getSlug())
        ];



        return $this->render('ClassCentralSiteBundle:Career:career.html.twig',
            array(
                'career' => $career,
                'page'=>'career',
                'slug' => $slug,
                'results' => $data['courses'],
                'listTypes' => UserCourse::$lists,
                'allSubjects' => $data['allSubjects'],
                'allLanguages' => $data['allLanguages'],
                'allSessions' => $data['allSessions'],
                'breadcrumbs' => $data['breadcrumbs'],
                'numCoursesWithCertificates' => $data['numCoursesWithCertificates'],
                'pageInfo' => $data['pageInfo'],
                'sortField' => $data['sortField'],
                'sortClass' => $data['sortClass'],
                'pageNo' => $data['pageNo'],
                'showHeader' => true,
                'followItem' => Item::ITEM_TYPE_CAREER,
                'followItemId' => $career->getId(),
                'followItemName' => $career->getName(),
                'pageMetadata' => $pageMetadata,
            ));
    }

    /**
     * Adding a careers action
     * @param Request $request
     */
    public function careersAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $categories = $em->getRepository('ClassCentralSiteBundle:Category')->findAll();
        return $this->render('ClassCentralSiteBundle:Career:careers.html.twig', array(
            'categories' => $categories
        ));
    }
}
