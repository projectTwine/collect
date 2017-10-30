<?php

namespace ClassCentral\SiteBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use ClassCentral\SiteBundle\Entity\TextAd;
use ClassCentral\SiteBundle\Form\TextAdType;

/**
 * TextAd controller.
 *
 */
class TextAdController extends Controller
{

    /**
     * Lists all TextAd entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('ClassCentralSiteBundle:TextAd')->findAll();

        return $this->render('ClassCentralSiteBundle:TextAd:index.html.twig', array(
            'entities' => $entities,
        ));
    }



    /**
     * Finds and displays a TextAd entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('ClassCentralSiteBundle:TextAd')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find TextAd entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('ClassCentralSiteBundle:TextAd:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),        ));
    }

    /**
     * Displays a form to edit an existing TextAd entity.
     *
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('ClassCentralSiteBundle:TextAd')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find TextAd entity.');
        }

        $editForm = $this->createForm(new TextAdType(), $entity);

        return $this->render('ClassCentralSiteBundle:TextAd:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
        ));
    }

    /**
     * Edits an existing TextAd entity.
     *
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $cache = $this->get('cache');

        $entity = $em->getRepository('ClassCentralSiteBundle:TextAd')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find TextAd entity.');
        }

        $editForm = $this->createForm(new TextAdType(), $entity);
        $editForm->bind($request);

        if ($editForm->isValid()) {
            $em->persist($entity);
            $em->flush();

            $cache->deleteCache('ads_cache');
            return $this->redirect($this->generateUrl('textad_edit', array('id' => $id)));
        }

        return $this->render('ClassCentralSiteBundle:TextAd:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
        ));
    }

    public function showAdsAction(Request $request, $pageName)
    {
        $cache = $this->get('Cache');

        $ads = $cache->get('ads_cache',function(){
            $a = $this
                ->getDoctrine()->getManager()
                ->getRepository('ClassCentralSiteBundle:TextAd')
                ->findAll();

            return $a;
        });

        return $this->render('ClassCentralSiteBundle:TextAd:showAds.html.twig',array(
            'ads' => $ads,
            'pageName' => $pageName
        ));
    }

    public function textRowAdsAction(Request $request)
    {
        $adsInfo = $this->get('advertising')->getTextRowAds();

        return $this->render('ClassCentralSiteBundle:TextAd:text.row.ads.html.twig',array(
            'ads' => $adsInfo['ads']
        ));
    }

}
