<?php

namespace ClassCentral\SiteBundle\Controller;

use ClassCentral\SiteBundle\Entity\Item;
use ClassCentral\SiteBundle\Entity\Offering;
use ClassCentral\SiteBundle\Entity\UserCourse;
use ClassCentral\SiteBundle\Services\Filter;
use ClassCentral\SiteBundle\Utility\PageHeader\PageHeaderFactory;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use ClassCentral\SiteBundle\Entity\Initiative;
use ClassCentral\SiteBundle\Form\InitiativeType;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Initiative controller.
 *
 */
class InitiativeController extends Controller
{
    /**
     * Lists all Initiative entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('ClassCentralSiteBundle:Initiative')->findAll();

        return $this->render('ClassCentralSiteBundle:Initiative:index.html.twig', array(
            'entities' => $entities
        ));
    }

    /**
     * Finds and displays a Initiative entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('ClassCentralSiteBundle:Initiative')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Initiative entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('ClassCentralSiteBundle:Initiative:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),

        ));
    }

    /**
     * Displays a form to create a new Initiative entity.
     *
     */
    public function newAction()
    {
        $entity = new Initiative();
        $form   = $this->createForm(new InitiativeType(), $entity);

        return $this->render('ClassCentralSiteBundle:Initiative:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView()
        ));
    }

    /**
     * Creates a new Initiative entity.
     *
     */
    public function createAction()
    {
        $entity  = new Initiative();
        $request = $this->getRequest();
        $form    = $this->createForm(new InitiativeType(), $entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('initiative_show', array('id' => $entity->getId())));
            
        }

        return $this->render('ClassCentralSiteBundle:Initiative:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView()
        ));
    }

    /**
     * Displays a form to edit an existing Initiative entity.
     *
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('ClassCentralSiteBundle:Initiative')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Initiative entity.');
        }

        $editForm = $this->createForm(new InitiativeType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('ClassCentralSiteBundle:Initiative:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Edits an existing Initiative entity.
     *
     */
    public function updateAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('ClassCentralSiteBundle:Initiative')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Initiative entity.');
        }

        $editForm   = $this->createForm(new InitiativeType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        $request = $this->getRequest();

        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('initiative_edit', array('id' => $id)));
        }

        return $this->render('ClassCentralSiteBundle:Initiative:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a Initiative entity.
     *
     */
    public function deleteAction($id)
    {
        $form = $this->createDeleteForm($id);
        $request = $this->getRequest();

        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('ClassCentralSiteBundle:Initiative')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Initiative entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('initiative'));
    }

    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(array('id' => $id))
            ->add('id', 'hidden')
            ->getForm()
        ;
    }


    /**
     * Display the provider page
     * @param $slug
     */
    public function providerAction(Request $request, $type)
    {
        $cache = $this->get('cache');

        $cl = $this->get('course_listing');
        $data = $cl->byProvider($type,$request);
        $provider = $data['provider'];

        $related = $cache->get('related_popular_providers',function () {
            $followService = $this->container->get('follow');
            $router =  $this->container->get('router');
            $popularProviders = array('coursera','udacity','edx','futurelearn');
            $related = array();
            $related['items'] = array();
            $em = $this->getDoctrine()->getManager();

            // Get provider course counts:
            $esCourses = $this->container->get('es_courses');
            $counts = $esCourses->getCounts();

            foreach ($popularProviders as $providerSlug)
            {
                $relatedItem = array();
                $prov = $em->getRepository('ClassCentralSiteBundle:Initiative')->findOneBy(array('code' => $providerSlug));
                $relatedItem['name'] = $prov->getName();
                $relatedItem['numFollows'] = $followService->getNumFollowers(Item::ITEM_TYPE_PROVIDER,$prov->getId());
                $relatedItem['url'] = $router->generate('ClassCentralSiteBundle_initiative',
                    array('type' => $providerSlug ));
                $count = $counts['providers'][$providerSlug];
                $relatedItem['numCourses'] = $count;
                $related['items'][] = $relatedItem;
            }
            $related['type'] = Item::ITEM_TYPE_PROVIDER;
            $related['name'] = 'Providers';
            $related['view_all_url'] = $router->generate('providers');
            $related['header'] = 'Popular MOOC Providers';

            return $related;
        },array());

        $related['skipName'] = $provider->getName();
        $provider = $data['provider'];
        $pageMetadata = [
            'provider_id' => $provider->getId(),
            'provider_name' => $provider->getName(),
            'provider_slug' => strtolower($provider->getCode())
        ];

        return $this->render('ClassCentralSiteBundle:Initiative:provider.html.twig',array(
            'results' => $data['courses'],
            'listTypes' => UserCourse::$lists,
            'allSubjects' => $data['allSubjects'],
            'allLanguages' => $data['allLanguages'],
            'allSessions' => $data['allSessions'],
            'numCoursesWithCertificates' => $data['numCoursesWithCertificates'],
            'page' => 'provider',
            'provider' => $provider,
            'pageInfo' => $data['pageInfo'],
            'sortField' => $data['sortField'],
            'sortClass' => $data['sortClass'],
            'pageNo' => $data['pageNo'],
            'showHeader' => true,
            'breadcrumbs' => $data['breadcrumbs'],
            'followItem' => Item::ITEM_TYPE_PROVIDER,
            'followItemId' => $provider->getId(),
            'followItemName' => $provider->getName(),
            'credentials' => $data['credentials'],
            'numCredentials' => $data['numCredentials'],
            'related' => $related,
            'pageMetadata' => $pageMetadata
        ));
    }

    /**
     * Shows a list with all the providers page
     * @param Request $request
     */
    public function providersAction(Request $request)
    {

        $data = $this->getProvidersList( $this->container );
        return $this->render('ClassCentralSiteBundle:Initiative:providers.html.twig',array(
            'providers' => $data['providers'],
            'followItem' =>  Item::ITEM_TYPE_PROVIDER,
            'page' => 'providers'
        ));
    }

    /**
     * Builds a list of providers with counts
     * @param ContainerInterface $container
     * @return mixed
     */
    public function getProvidersList( ContainerInterface $container)
    {
        $cache = $container->get('cache');

        $data = $cache->get('providers_with_count', function($container){
            $esCourses = $container->get('es_courses');
            $counts = $esCourses->getCounts();
            $em = $container->get('doctrine')->getManager();

            arsort( $counts['providers'] );
            $providers = array();
            foreach( $counts['providers'] as $code => $count )
            {
                if($code == 'independent')
                {
                    $entity = new Initiative();
                    $entity->setCode($code);
                    $entity->setName( 'Independent' );
                }
                else
                {
                    $entity = $em->getRepository('ClassCentralSiteBundle:Initiative')->findOneBy( array('code' => $code) );
                }

                $provider = array();
                $provider['id'] = $entity->getId();
                $provider['count'] = $count;
                $provider['code'] = $code;
                $provider['name'] = $entity->getName();
                $providers[ $code ] = $provider;
            }

            return compact('providers');

        }, array($container));

        return $data;
    }
}
