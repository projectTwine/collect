<?php

namespace ClassCentral\SiteBundle\Controller;

use ClassCentral\SiteBundle\Entity\Item;
use ClassCentral\SiteBundle\Entity\UserCourse;
use ClassCentral\SiteBundle\Utility\Breadcrumb;
use ClassCentral\SiteBundle\Utility\PageHeader\PageHeaderFactory;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use ClassCentral\SiteBundle\Entity\Stream;
use ClassCentral\SiteBundle\Form\StreamType;
use ClassCentral\SiteBundle\Entity\Offering;

/**
 * Stream controller.
 *
 */
class StreamController extends Controller
{
    /**
     * Lists all Stream entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('ClassCentralSiteBundle:Stream')->findAll();

        return $this->render('ClassCentralSiteBundle:Stream:index.html.twig', array(
            'entities' => $entities
        ));
    }

    /**
     * Finds and displays a Stream entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('ClassCentralSiteBundle:Stream')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Stream entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('ClassCentralSiteBundle:Stream:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),

        ));
    }

    /**
     * Displays a form to create a new Stream entity.
     *
     */
    public function newAction()
    {
        $entity = new Stream();
        $form   = $this->createForm(new StreamType(), $entity);

        return $this->render('ClassCentralSiteBundle:Stream:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView()
        ));
    }

    /**
     * Creates a new Stream entity.
     *
     */
    public function createAction()
    {
        $entity  = new Stream();
        $request = $this->getRequest();
        $form    = $this->createForm(new StreamType(), $entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('stream_show', array('id' => $entity->getId())));
            
        }

        return $this->render('ClassCentralSiteBundle:Stream:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView()
        ));
    }

    /**
     * Displays a form to edit an existing Stream entity.
     *
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('ClassCentralSiteBundle:Stream')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Stream entity.');
        }

        $editForm = $this->createForm(new StreamType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('ClassCentralSiteBundle:Stream:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Edits an existing Stream entity.
     *
     */
    public function updateAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('ClassCentralSiteBundle:Stream')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Stream entity.');
        }

        $editForm   = $this->createForm(new StreamType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        $request = $this->getRequest();

        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('stream_edit', array('id' => $id)));
        }

        return $this->render('ClassCentralSiteBundle:Stream:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a Stream entity.
     *
     */
    public function deleteAction($id)
    {
        $form = $this->createDeleteForm($id);
        $request = $this->getRequest();

        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('ClassCentralSiteBundle:Stream')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Stream entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('stream'));
    }

    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(array('id' => $id))
            ->add('id', 'hidden')
            ->getForm()
        ;
    }
    
    public function viewAction(Request $request,$slug)
    {
        $cache = $this->get('cache');

        $cl = $this->get('course_listing');
        $data = $cl->bySubject($slug,$request);
        $subject = $data['subject'];

        // Popular Subjects shown as
        $related  = $cache->get('related_popular_subjects',function (){
            $popularSubjectsSlug = array('management-and-leadership','data-science','health','education','art-and-design','nutrition-and-wellness',
                'cs','business','ai','statistics');
            $related = array();
            $related['items'] = array();
            $em = $this->getDoctrine()->getManager();
            $esCourses = $this->container->get('es_courses');
            $count = $esCourses->getCounts();
            $subjectsCount = $count['subjects'];
            $followService = $this->container->get('follow');
            $router =  $this->container->get('router');
            foreach ($popularSubjectsSlug as $slug)
            {
                $relatedItem = array();
                $subject = $em->getRepository('ClassCentralSiteBundle:Stream')->findOneBy(array('slug'=>$slug));
                $count = $subjectsCount[$subject->getId()];

                $relatedItem['name'] = $subject->getName();
                $relatedItem['numCourses'] = $count;
                $relatedItem['numFollows'] = $followService->getNumFollowers(Item::ITEM_TYPE_SUBJECT,$subject->getId());
                $relatedItem['url'] = $router->generate('ClassCentralSiteBundle_stream',
                    array('slug' => $subject->getSlug() ));
                $related['items'][] = $relatedItem;
            }
            $related['type'] = Item::ITEM_TYPE_SUBJECT;
            $related['name'] = 'Subjects';
            $related['view_all_url'] = $router->generate('subjects');
            $related['header'] = 'Popular Subjects';

            return $related;
        },array());

        $related['skipName'] = $subject->getName(); // Does not show this subject
        $subject = $data['subject'];
        $pageMetadata = [
            'subject_id' => $subject->getId(),
            'subject_slug'=> $subject->getSlug(),
            'subject_name' => $subject->getName()
        ];


        return $this->render('ClassCentralSiteBundle:Stream:view.html.twig', array(
                'subject' => $subject,
                'page' => 'subject',
                'slug' => $slug,
                'offeringTypes' => Offering::$types,
                'results' => $data['courses'],
                'listTypes' => UserCourse::$lists,
                'allSubjects' => $data['allSubjects'],
                'allLanguages' => $data['allLanguages'],
                'numCoursesWithCertificates' => $data['numCoursesWithCertificates'],
                'pageInfo' => $data['pageInfo'],
                'allSessions' => $data['allSessions'],
                'breadcrumbs' => $data['breadcrumbs'],
                'sortField' => $data['sortField'],
                'sortClass' => $data['sortClass'],
                'pageNo' => $data['pageNo'],
                'showHeader' => true,
                'followItem' => Item::ITEM_TYPE_SUBJECT,
                'followItemId' => $subject->getId(),
                'followItemName' => $subject->getName(),
                'credentials' => $data['credentials'],
                'numCredentials' => $data['numCredentials'],
                'related' => $related,
                'tagCounts' => $data['tags'],
                'pageMetadata' => $pageMetadata
            ));
    }

    /**
     * Renders the subjects page which shows a list of all Class Central Subjects
     */
    public function subjectsAction(Request $request)
    {
        $this->get('user_service')->autoLogin($request);

        $cache = $this->get('Cache');
        $subjects = $cache->get('stream_list_count', array($this, 'getSubjectsList'),array($this->container));
        $breadcrumbs = array(
            Breadcrumb::getBreadCrumb('Subjects')
        );
        return $this->render('ClassCentralSiteBundle:Stream:subjects.html.twig',array(
                'page' => 'subjects',
                'subjects' => $subjects,
                'breadcrumbs' => $breadcrumbs
            ));
    }

    public function getSubjectsList($container)
    {
        // counts
        $em = $container->get('doctrine')->getManager();
        $esCourses = $container->get('es_courses');

        $count = $esCourses->getCounts();
        $subjectsCount = $count['subjects'];

        $allSubjects = $em->getRepository('ClassCentralSiteBundle:Stream')->findBy(
                array(), array('displayOrder' => 'DESC')
                // fixing stream order to DESC
            );
        $parentSubjects = array();
        $childSubjects = array();

        foreach($allSubjects as $subject)
        {
            if(!isset($subjectsCount[$subject->getId()]))
            {
                continue; // no count exists. Do not show the subject
            }
            $count = $subjectsCount[$subject->getId()];
            $subject->setCourseCount($count);
            if($subject->getParentStream())
            {
                $childSubjects[$subject->getParentStream()->getId()][] = $subject->getArray();
            }
            else
            {
                $parentSubjects[$subject->getId()] = $subject->getArray();
            }

        }

        return array('parent'=>$parentSubjects,'children'=>$childSubjects);
    }

    public function textViewAction(Request $request,$slug)
    {
        $em = $this->getDoctrine()->getManager();
        $subject = $em->getRepository('ClassCentralSiteBundle:Stream')->findOneBy(array('slug'=>$slug));
        if($subject)
        {
            $courses = $em->getRepository('ClassCentralSiteBundle:Course')->findBy(array('stream' => $subject));

            return $this->render('ClassCentralSiteBundle:Stream:text.view.html.twig', array(
                'subject' => $subject,
                'courses' => $courses,
            ));
        }


    }

}
