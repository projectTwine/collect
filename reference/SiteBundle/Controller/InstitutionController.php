<?php

namespace ClassCentral\SiteBundle\Controller;

use ClassCentral\SiteBundle\Entity\Item;
use ClassCentral\SiteBundle\Entity\UserCourse;
use ClassCentral\SiteBundle\Utility\PageHeader\PageHeaderFactory;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use ClassCentral\SiteBundle\Entity\Institution;
use ClassCentral\SiteBundle\Form\InstitutionType;
use ClassCentral\SiteBundle\Entity\Offering;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Institution controller.
 *
 */
class InstitutionController extends Controller
{
    /**
     * Lists all Institution entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('ClassCentralSiteBundle:Institution')->findAll();

        return $this->render('ClassCentralSiteBundle:Institution:index.html.twig', array(
            'entities' => $entities
        ));
    }

    /**
     * Finds and displays a Institution entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('ClassCentralSiteBundle:Institution')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Institution entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('ClassCentralSiteBundle:Institution:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),

        ));
    }

    /**
     * Displays a form to create a new Institution entity.
     *
     */
    public function newAction()
    {
        $entity = new Institution();
        $form   = $this->createForm(new InstitutionType(), $entity);

        return $this->render('ClassCentralSiteBundle:Institution:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView()
        ));
    }

    /**
     * Creates a new Institution entity.
     *
     */
    public function createAction()
    {
        $entity  = new Institution();
        $request = $this->getRequest();
        $form    = $this->createForm(new InstitutionType(), $entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('institution_show', array('id' => $entity->getId())));

        }

        return $this->render('ClassCentralSiteBundle:Institution:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView()
        ));
    }

    /**
     * Displays a form to edit an existing Institution entity.
     *
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('ClassCentralSiteBundle:Institution')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Institution entity.');
        }

        $editForm = $this->createForm(new InstitutionType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('ClassCentralSiteBundle:Institution:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Edits an existing Institution entity.
     *
     */
    public function updateAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('ClassCentralSiteBundle:Institution')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Institution entity.');
        }

        $editForm   = $this->createForm(new InstitutionType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        $request = $this->getRequest();

        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('institution_edit', array('id' => $id)));
        }

        return $this->render('ClassCentralSiteBundle:Institution:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a Institution entity.
     *
     */
    public function deleteAction($id)
    {
        $form = $this->createDeleteForm($id);
        $request = $this->getRequest();

        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('ClassCentralSiteBundle:Institution')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Institution entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('institution'));
    }

    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(array('id' => $id))
            ->add('id', 'hidden')
            ->getForm()
        ;
    }

    public function viewAction(Request $request, $slug)
    {
        $cache = $this->get('cache');
        $routeName = $request->get('_route');

        // only use lower case slug
        if($slug !== strtolower($slug))
        {
            // Do a 301 redirect
            $url = $this->get('router')->generate($routeName, array('slug' => strtolower($slug) ));
            return $this->redirect( $url, 301);
        }

        $cl = $this->get('course_listing');
        $data = $cl->byInstitution($slug,$request);
        $institution = $data['institution'];

        // route says institution but it is a university
        if($institution->getIsUniversity() && $routeName == 'ClassCentralSiteBundle_institution' )
        {
            // Do a 301 redirect
            $url = $this->get('router')->generate('ClassCentralSiteBundle_university', array('slug' => $slug ));
            return $this->redirect( $url, 301);
        }

        // route says university but it as institution
        if(!$institution->getIsUniversity() && $routeName == 'ClassCentralSiteBundle_university' )
        {
            // Do a 301 redirect
            $url = $this->get('router')->generate('ClassCentralSiteBundle_institution', array('slug' => $slug ));
            return $this->redirect( $url, 301);
        }

        $free = true;
        if($slug == 'keiser')
        {
            $free = false;
        }


        $related = $cache->get('related_popular_ins',function () {

            $popularIns = array(
                'stanford','harvard','mit','berkeley','utoronto','yale','gatech','penn','umich','iitb','jhu', 'google','worldbank'
            );
            $related = array();
            $related['items'] = array();
            $em = $this->getDoctrine()->getManager();
            $esCourses = $this->container->get('es_courses');
            $universityCounts = $esCourses->getInstitutionCounts(true);
            $institutionCounts = $esCourses->getInstitutionCounts(false);
            $followService = $this->container->get('follow');
            $router =  $this->container->get('router');
            foreach ($popularIns as $insSlug)
            {
                $relatedItem = array();
                $ins = $em->getRepository('ClassCentralSiteBundle:Institution')->findOneBy(array('slug' => $insSlug));
                $isUniversity = $ins->getIsUniversity();
                $relatedItem['name'] = $ins->getName();
                $relatedItem['numFollows'] = $followService->getNumFollowers(Item::ITEM_TYPE_INSTITUTION,$ins->getId());
                if($isUniversity)
                {
                    $relatedItem['url'] = $router->generate('ClassCentralSiteBundle_university',
                        array('slug' => $insSlug ));
                    $count = $universityCounts['institutions'][$insSlug];
                }
                else
                {
                    $relatedItem['url'] = $router->generate('ClassCentralSiteBundle_institution',
                        array('slug' => $insSlug ));
                    $count = $institutionCounts['institutions'][$insSlug];
                }
                $relatedItem['numCourses'] = $count;
                $related['items'][] = $relatedItem;
            }
            $related['type'] = Item::ITEM_TYPE_INSTITUTION;
            $related['name'] = 'Organizations';
            $related['view_all_url'] = $router->generate('universities');
            $related['header'] = 'Popular Organizations Creating MOOCs';

            return $related;

        },array());

        $related['skipName'] = $institution->getName();

        $institution = $data['institution'];
        $pageMetadata = [
            'institution_id' => $institution->getId(),
            'institution_name' => $institution->getName(),
            'institution_slug' => strtolower($institution->getSlug())
        ];


        return $this->render('ClassCentralSiteBundle:Institution:view.html.twig',
                array(
                    'institution' => $institution,
                    'page'=>'institution',
                    'slug' => $slug,
                    'results' => $data['courses'],
                    'listTypes' => UserCourse::$lists,
                    'allSubjects' => $data['allSubjects'],
                    'allLanguages' => $data['allLanguages'],
                    'allSessions' => $data['allSessions'],
                    'numCoursesWithCertificates' => $data['numCoursesWithCertificates'],
                    'breadcrumbs' => $data['breadcrumbs'],
                    'pageInfo' => $data['pageInfo'],
                    'sortField' => $data['sortField'],
                    'sortClass' => $data['sortClass'],
                    'pageNo' => $data['pageNo'],
                    'showHeader' => true,
                    'followItem' => Item::ITEM_TYPE_INSTITUTION,
                    'followItemId' => $institution->getId(),
                    'followItemName' => $institution->getName(),
                    'free' => $free,
                    'related' => $related,
                    'pageMetadata' => $pageMetadata
                ));
    }

    /**
     * Show list of universities
     * @param Request $request
     */
    public function universitiesAction(Request $request)
    {
        // Autologin if a token exists
        $this->get('user_service')->autoLogin($request);

        return $this->getInstitutionsView(true);
    }

    public function institutionsAction(Request $request)
    {
        return $this->getInstitutionsView(false);
    }

    /**
     * Returns a view listing universities or institutions
     * @param bool $isUniversity
     * @return \Symfony\Component\HttpFoundation\Response
     */
    private function getInstitutionsView($isUniversity = true)
    {
        $data = $this->getInstitutions( $this->container, $isUniversity);

        return $this->render('ClassCentralSiteBundle:Institution:institutions.html.twig',array(
            'institutions' => $data['institutions'],
            'isUniversity' => $isUniversity,
            'followItem' => Item::ITEM_TYPE_INSTITUTION
        ));
    }

    /**
     * Returns a list of institutions/universities with count
     * @param ContainerInterface $container
     * @param bool $isUniversity
     */
    public function getInstitutions(ContainerInterface $container,$isUniversity = true)
    {
        $cache = $container->get('cache');

        $data = $cache->get('institutions_with_count_' . $isUniversity, function($container, $isUniversity){
            $esCourses = $container->get('es_courses');
            $counts = $esCourses->getInstitutionCounts( $isUniversity );
            $em = $container->get('doctrine')->getManager();

            arsort( $counts['institutions'] );
            $institutions = array();
            foreach( $counts['institutions'] as $slug => $count )
            {
                $entity = $em->getRepository('ClassCentralSiteBundle:Institution')->findOneBy( array('slug' => $slug) );
                if(empty($entity))
                {
                    continue;
                }
                $institution = array();
                $institution['id'] = $entity->getId();
                $institution['count'] = $count;
                $institution['courseCount'] = $count;
                $institution['slug'] = $slug;
                $institution['name'] = $entity->getName();
                $institutions[ $slug ] = $institution;

            }

            return compact('institutions');

        }, array($container,$isUniversity));

        return $data;
    }

}
