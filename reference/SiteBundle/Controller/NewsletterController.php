<?php

namespace ClassCentral\SiteBundle\Controller;

use ClassCentral\SiteBundle\Entity\User;
use ClassCentral\SiteBundle\Services\UserSession;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use ClassCentral\SiteBundle\Entity\VerificationToken;

use ClassCentral\SiteBundle\Entity\Newsletter;
use ClassCentral\SiteBundle\Form\NewsletterType;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Validator\Constraints\Email;

/**
 * Newsletter controller.
 *
 */
class NewsletterController extends Controller
{

    /**
     * Lists all Newsletter entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('ClassCentralSiteBundle:Newsletter')->findAll();

        return $this->render('ClassCentralSiteBundle:Newsletter:index.html.twig', array(
            'entities' => $entities,
        ));
    }
    /**
     * Creates a new Newsletter entity.
     *
     */
    public function createAction(Request $request)
    {
        $entity  = new Newsletter();
        $form = $this->createForm(new NewsletterType(), $entity);
        $form->bind($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('newsletter_show', array('id' => $entity->getId())));
        }

        return $this->render('ClassCentralSiteBundle:Newsletter:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Displays a form to create a new Newsletter entity.
     *
     */
    public function newAction()
    {
        $entity = new Newsletter();
        $form   = $this->createForm(new NewsletterType(), $entity);

        return $this->render('ClassCentralSiteBundle:Newsletter:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Finds and displays a Newsletter entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('ClassCentralSiteBundle:Newsletter')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Newsletter entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('ClassCentralSiteBundle:Newsletter:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),        ));
    }

    /**
     * Displays a form to edit an existing Newsletter entity.
     *
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('ClassCentralSiteBundle:Newsletter')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Newsletter entity.');
        }

        $editForm = $this->createForm(new NewsletterType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('ClassCentralSiteBundle:Newsletter:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Edits an existing Newsletter entity.
     *
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('ClassCentralSiteBundle:Newsletter')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Newsletter entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createForm(new NewsletterType(), $entity);
        $editForm->bind($request);

        if ($editForm->isValid()) {
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('newsletter_edit', array('id' => $id)));
        }

        return $this->render('ClassCentralSiteBundle:Newsletter:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }
    /**
     * Deletes a Newsletter entity.
     *
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->bind($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('ClassCentralSiteBundle:Newsletter')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Newsletter entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('newsletter'));
    }

    /**
     * Creates a form to delete a Newsletter entity by id.
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
     * Renders the subscribe newsletter page
     * @param $code
     */
    public function subscribeAction(Request $request, $code)
    {
        $em = $this->getDoctrine()->getManager();
        $newsletter = $em->getRepository('ClassCentralSiteBundle:Newsletter')->findOneByCode($code);
        if(!$newsletter)
        {
            // TODO: Show error
            return null;
        }

        return $this->render('ClassCentralSiteBundle:Newsletter:subscribe.html.twig',array(
                'newsletter' => $newsletter
            ));
    }

    /**
     * Saves the users subscrption for the newsletter
     * @param Request $request
     * @param $code
     */
    public function subscribeToAction(Request $request, $code)
    {
        $referUrl = $this->getRequest()->headers->get('referer'); // The url redirect
        if(strpos($referUrl,'/subscribe/') !== false)
        {
            // Came from the dedicated newsletter signup form and not popup.
            $referUrl = $this->generateUrl('ClassCentralSiteBundle_homepage');
        }
        $em = $this->getDoctrine()->getManager();
        $session = $this->get('session');
        $userSession = $this->get('user_session');
        $newsletterService = $this->get('newsletter');
        $logger = $this->get('logger');

        $newsletter = $em->getRepository('ClassCentralSiteBundle:Newsletter')->findOneByCode($code);
        if(!$newsletter)
        {
            // TODO: Show error
            return null;
        }

        $user = null;
        $email = null;
        // Check if the user is signed in
        if($this->get('security.context')->isGranted('IS_AUTHENTICATED_FULLY'))
        {
            $user = $this->get('security.context')->getToken()->getUser();
        }
        else
        {
            // Get the email for the user
            $email = $request->request->get('email');
            $email = strtolower($email);

            // Validate it
            $emailConstraint = new Email();
            $emailConstraint->message = "Invalid email address";
            $errorList = $this->get('validator')->validateValue($email,$emailConstraint);
            if(count($errorList) > 0)
            {
                // Invalid email
                $errorMessage = $errorList[0]->getMessage();
                $session->getFlashBag()->add('newsletter_invalid_email',$errorMessage);
                return $this->redirect($this->generateUrl('newsletter_subscribe',array('code' => $code)));
            }

            // email exists. Check if the user has an account
            $user = $em->getRepository('ClassCentralSiteBundle:User')->findOneByEmail($email);
            if(!$user)
            {
                // Check if the email is the database
                $emailEntity = $em->getRepository('ClassCentralSiteBundle:Email')->findOneByEmail($email);
                if(!$emailEntity)
                {
                    $emailEntity = new \ClassCentral\SiteBundle\Entity\Email();
                    $emailEntity->setEmail($email);
                }
            }
        }

        $redirectUrl = null;
        if($user)
        {
            // Save the subscription preferences
            $user->subscribe($newsletter);
            $em->persist($user);

            if(!$user->getIsverified())
            {
                // Send a email verification message
                $this->sendEmailVerification($user->getEmail());
            }
            else
            {
                // Subscribe
                if ($this->container->getParameter('kernel.environment') != 'test')
                {
                    $subscribed = $newsletterService->subscribeUser($newsletter, $user);
                    $logger->info("subscribeToAction : user newsletter subscription", array(
                            'user' => $user->getId(),
                            'newsletter' => $newsletter->getCode(),
                            'subscribed' => $subscribed
                        ));
                }
            }

            $redirectUrl = $referUrl;
            // Send a notification
            $userSession->notifyUser(
                UserSession::FLASH_TYPE_SUCCESS,
                'Subscribed to Newsletter',
                ''
            );
        }
        else
        {
            // Not a new email or is not verified
            if(!$emailEntity->getId() || !$emailEntity->getIsverified())
            {
                // Send a email verification message
                $this->sendEmailVerification($emailEntity->getEmail());
            }

            // Save the subscription preferences
            $emailEntity->subscribe($newsletter);
            $em->persist($emailEntity);
            $userSession->setNewsletterUserEmail($emailEntity->getEmail());

            // If verified add the user to the mailing list
            if($emailEntity->getIsverified())
            {
                // Subscribe
                if ($this->container->getParameter('kernel.environment') != 'test')
                {
                    $subscribed = $newsletterService->subscribeEmail($newsletter, $emailEntity);
                    $logger->info("subscribeToAction : email newsletter subscription", array(
                            'email' => $emailEntity->getId(),
                            'newsletter' => $newsletter->getCode(),
                            'subscribed' => $subscribed
                        ));
                }
            }

            $redirectUrl = $this->generateUrl('newsletter_subscribed');
            // Save the refer url in the session
            $session->set('newsletter_signup_refer_url',$referUrl);
        }

        $em->flush();

        return $this->redirect($redirectUrl);
    }

    private function sendEmailVerification($email)
    {
        if ($this->container->getParameter('kernel.environment') == 'test')
        {
            // Don't send emails in the test environment
            return;
        }

        $verifyTokenService = $this->get('verification_token');
        $templating = $this->get('templating');
        $mailgun = $this->get('mailgun');

        $value = array(
            'verify' => 1,
            'email' => $email
        );
        $tokenEntity = $verifyTokenService->create($value,VerificationToken::EXPIRY_1_YEAR);
        $html = $templating->renderResponse('ClassCentralSiteBundle:Mail:confirm.email.html.twig',array('token' => $tokenEntity->getToken()))->getContent();
        $mailgunResponse = $mailgun->sendSimpleText($email,"no-reply@class-central.com","Please confirm your email",$html);
    }

    /**
     * Shows the subscribed page
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function subscribedAction(Request $request)
    {
        $userSession = $this->get('user_session');
        $session = $this->get('session');
        $email = $userSession->getNewsletterUserEmail();
        $referUrl =   $session->get('newsletter_signup_refer_url');
        return $this->render('ClassCentralSiteBundle:Newsletter:subscribed.html.twig',array(
                'newsletterEmail' => $email,
                'referUrl' => $referUrl
            ));
    }

    public function moocTrackerSignupAction(Request $request)
    {
        $userSession = $this->get('user_session');
        $userService = $this->get('user_service');
        $session = $this->get('session');
        $em = $this->get('doctrine')->getManager();

        // Redirect user if already logged in
        if($this->get('security.context')->isGranted('IS_AUTHENTICATED_FULLY'))
        {
            return $this->redirect($this->generateUrl('user_library'));
        }


        $email = $userSession->getNewsletterUserEmail();
        $emailEntity = $em->getRepository('ClassCentralSiteBundle:Email')->findOneByEmail($email);
        if(empty($emailEntity))
        {
            // Redirect to signup page
            $this->redirect($this->generateUrl('signup'));
        }
        $password = $request->request->get('password');
        if(empty($password))
        {
            $session->getFlashBag()->add('newsletter_signup_invalid_password',"Please enter a password");
            return $this->redirect($this->generateUrl('newsletter_subscribed'));
        }

        $name = $request->request->get('name');
        if( empty($name) )
        {
            $session->getFlashBag()->add('newsletter_signup_invalid_password',"Please enter a name");
            return $this->redirect($this->generateUrl('newsletter_subscribed'));
        }

        // Password-Email are accurate. Create a user
        $user = new User();
        $user->setEmail($email);
        $user->setPassword($password);
        $user->setName($name);
        foreach($emailEntity->getNewsletters() as $newsletter)
        {
            $user->addNewsletter($newsletter);
        }
        $user = $userService->signup($user, false,'newsletter'); // don't send an email verification.

        // Clean the email entities subscriptions
        $newsletters = $emailEntity->getNewsletters();
        $newsletters->clear();
        $em->persist($emailEntity);
        $em->flush();

        // Get the refer url and redirect here after signup
        $referUrl =   $session->get('newsletter_signup_refer_url');
        $this->get('session')->getFlashBag()->set('show_post_signup_profile_modal',1);

        // Redirect to MOOC Tracker page
        return $this->redirect($referUrl);
    }

    public function moocWatchAction(Request $request)
    {
        return $this->render('ClassCentralSiteBundle:Newsletter:moocwatch.html.twig',array(

        ));
    }
}
