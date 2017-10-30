<?php

namespace ClassCentral\SiteBundle\Controller;

use ClassCentral\SiteBundle\Utility\Breadcrumb;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use ClassCentral\SiteBundle\Entity\News;
use ClassCentral\SiteBundle\Form\NewsType;

/**
 * News controller.
 *
 */
class NewsController extends Controller
{
    /**
     * Shows the recent news item on /news
     */
    public function homeAction()
    {
        $cache = $this->get('Cache');
        $news = $cache->get('recent_news',array($this,'getRecentNews'), array($this->getDoctrine()->getManager()));
        $breadcrumbs = array(
            Breadcrumb::getBreadCrumb('News')
        );
        return $this->render('ClassCentralSiteBundle:News:home.html.twig', array(
            'news' => $news, 'page' => 'news', 'breadcrumbs' => $breadcrumbs
        ));
    }

    public function getRecentNews($em, $limit = 6)
    {
        $news = $em->getRepository('ClassCentralSiteBundle:News')->findAll();

        $query = $em->createQueryBuilder();
        $query->add('select', 'n')
            ->add('from', 'ClassCentralSiteBundle:News n')
            ->add('orderBy', 'n.id DESC')
            ->setMaxResults($limit);
        $news = $query->getQuery()->getResult();

        $newsArray = array();
        foreach($news as $newsItem)
        {
            $item = array();
            $item['title'] = $newsItem->getTitle();
            $item['url'] = $newsItem->getUrl();
            $item['description'] = $newsItem->getDescription();
            $item['localImageUrl'] = $newsItem->getLocalImageUrl();
            $item['remoteImageUrl'] = $newsItem->getRemoteImageUrl();
            $newsArray[] = $item;
        }

        return $newsArray;
    }


    /**
     * Lists all News entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('ClassCentralSiteBundle:News')->findAll();

        return $this->render('ClassCentralSiteBundle:News:index.html.twig', array(
            'entities' => $entities
        ));
    }

    /**
     * Finds and displays a News entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('ClassCentralSiteBundle:News')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find News entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('ClassCentralSiteBundle:News:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),

        ));
    }

    /**
     * Displays a form to create a new News entity.
     *
     */
    public function newAction()
    {
        $entity = new News();
        $form   = $this->createForm(new NewsType(), $entity);

        return $this->render('ClassCentralSiteBundle:News:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView()
        ));
    }

    /**
     * Creates a new News entity.
     *
     */
    public function createAction()
    {
        $entity  = new News();
        $request = $this->getRequest();
        $form    = $this->createForm(new NewsType(), $entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('news_show', array('id' => $entity->getId())));
            
        }

        return $this->render('ClassCentralSiteBundle:News:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView()
        ));
    }

    /**
     * Displays a form to edit an existing News entity.
     *
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('ClassCentralSiteBundle:News')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find News entity.');
        }

        $editForm = $this->createForm(new NewsType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('ClassCentralSiteBundle:News:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Edits an existing News entity.
     *
     */
    public function updateAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('ClassCentralSiteBundle:News')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find News entity.');
        }

        $editForm   = $this->createForm(new NewsType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        $request = $this->getRequest();

        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('news_edit', array('id' => $id)));
        }

        return $this->render('ClassCentralSiteBundle:News:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a News entity.
     *
     */
    public function deleteAction($id)
    {
        $form = $this->createDeleteForm($id);
        $request = $this->getRequest();

        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('ClassCentralSiteBundle:News')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find News entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('news'));
    }

    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(array('id' => $id))
            ->add('id', 'hidden')
            ->getForm()
        ;
    }
}
