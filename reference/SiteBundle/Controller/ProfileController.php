<?php

namespace ClassCentral\SiteBundle\Controller;

use ClassCentral\SiteBundle\Entity\User;
use ClassCentral\SiteBundle\Entity\UserCourse;
use ClassCentral\SiteBundle\Entity\UserPreference;
use ClassCentral\SiteBundle\Entity\VerificationToken;
use ClassCentral\SiteBundle\Services\Kuber;
use ClassCentral\SiteBundle\Services\UserSession;
use ClassCentral\SiteBundle\Utility\ReviewUtility;
use ClassCentral\SiteBundle\Utility\UniversalHelper;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use ClassCentral\SiteBundle\Entity\Profile;
use ClassCentral\SiteBundle\Form\ProfileType;
use Symfony\Component\Validator\Constraints\Email;

/**
 * Profile controller.
 *
 */
class ProfileController extends Controller
{

    /**
     * Lists all Profile entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('ClassCentralSiteBundle:Profile')->findAll();

        return $this->render('ClassCentralSiteBundle:Profile:index.html.twig', array(
            'entities' => $entities,
        ));
    }
    /**
     * Creates a new Profile entity.
     *
     */
    public function createAction(Request $request)
    {
        $entity  = new Profile();
        $form = $this->createForm(new ProfileType(), $entity);
        $form->bind($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('profile_show', array('id' => $entity->getId())));
        }

        return $this->render('ClassCentralSiteBundle:Profile:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Displays a form to create a new Profile entity.
     *
     */
    public function newAction()
    {
        $entity = new Profile();
        $form   = $this->createForm(new ProfileType(), $entity);

        return $this->render('ClassCentralSiteBundle:Profile:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Finds and displays a Profile entity.
     *
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('ClassCentralSiteBundle:Profile')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Profile entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('ClassCentralSiteBundle:Profile:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),        ));
    }

    /**
     * Displays a form to edit an existing Profile entity.
     *
     */
    public function editAdminAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('ClassCentralSiteBundle:Profile')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Profile entity.');
        }

        $editForm = $this->createForm(new ProfileType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('ClassCentralSiteBundle:Profile:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Edits an existing Profile entity.
     *
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('ClassCentralSiteBundle:Profile')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Profile entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createForm(new ProfileType(), $entity);
        $editForm->bind($request);

        if ($editForm->isValid()) {
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('profile_edit', array('id' => $id)));
        }

        return $this->render('ClassCentralSiteBundle:Profile:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }
    /**
     * Deletes a Profile entity.
     *
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->bind($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('ClassCentralSiteBundle:Profile')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Profile entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('profile'));
    }

    /**
     * Creates a form to delete a Profile entity by id.
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
     * Renders the users profile
     * @param $slug user id or username
     */
    public function profileAction(Request $request,$slug, $tab)
    {
        $tabs = array('transcript','interested','reviews','edit-profile');
        $em = $this->getDoctrine()->getManager();
        $cl = $this->get('course_listing');
        $userService = $this->get('user_service');

        $loggedInUser = $this->getUser();

        if(! in_array($tab,$tabs) )
        {
            // Invalid tab. Do a 301 redirect
            return $this->redirect(
                $this->get('router')->generate('user_profile', array('slug' => $slug)),
                301
            );
        }
        if(is_numeric($slug))
        {
            $user_id = intval( $slug );

            // Do not show profile pages for these user ids
            if( $user_id == User::SPECIAL_USER_ID || $user_id == User::REVIEW_USER_ID )
            {
                // User not found
                throw new \Exception("User $slug not found");
            }

            $user = $em->getRepository('ClassCentralSiteBundle:User')->find( $user_id );
            if($user->getHandle())
            {
                // Redirect the user to the profile url

                $url = $this->get('router')->generate('user_profile_handle', array(
                    'slug' => $user->getHandle(),
                    'tab' => ($tab == 'transcript') ? null : $tab // Avoid showing transcript in the url
                ));
                return $this->redirect($url,301);
            }
        }
        else
        {
            $user = $em->getRepository('ClassCentralSiteBundle:User')->findOneBy( array('handle'=> $slug) );
        }

        if(!$user)
        {
            // User not found
            throw new \Exception("User $slug not found");
        }

       // if tab is edit-profile. Do some security checks
        if( $tab == 'edit-profile' )
        {
            if($this->get('security.context')->isGranted('IS_AUTHENTICATED_FULLY'))
            {
                // Logged in user
                $loggedInUser = $this->get('security.context')->getToken()->getUser();
                if( $user->getId() != $loggedInUser->getId() )
                {
                    // Does not have access to the edit profile tab. Redirect to transcript
                    return $this->redirect(
                        $this->get('router')->generate('user_profile', array('slug' => $user->getId() )),
                        301
                    );
                }
            }
            else
            {
                // Does not have access to the edit profile tab. Redirect to transcript
                return $this->redirect(
                    $this->get('router')->generate('user_profile', array('slug' =>  $user->getId())),
                    301
                );
            }
        }

        // User might have a private prifle
        if( $user->getIsPrivate() && !$this->isCurrentUser($user) )
        {
            throw $this->createNotFoundException("Page does not exist");
        }

        // User might not have a profile
        $profile = ($user->getProfile()) ? $user->getProfile() : new Profile();

        // Get users course listing. This is the same function on My Courses page
        // and contains additional information related to pagination
        $clDetails = $cl->userLibrary( $user, $request);

        $reviews = array();
        foreach($user->getReviews() as $review)
        {
            $r = ReviewUtility::getReviewArray($review);
            $reviews[$r['course']['id']] = $r;
        }

        $reviewedCourses = array();
        if( !empty($clDetails['reviewedCourses']['hits']['hits']) )
        {
            foreach( $clDetails['reviewedCourses']['hits']['hits'] as $reviewedCourse )
            {
                $reviewedCourses[ $reviewedCourse['_source']['id'] ] = $reviewedCourse['_source'];
            }
        }


        // Check if a change of email has been issued
        $changeEmail = null;
        $userPrefs = $user->getUserPreferencesByTypeMap();
        if( $this->isCurrentUser($user) && isset($userPrefs[ UserPreference::USER_PROFILE_UPDATE_EMAIL ]) )
        {
            // An change of email request was issued. Check whether it is still valid
            $pref = $userPrefs[ UserPreference::USER_PROFILE_UPDATE_EMAIL ];
            $values = json_decode ( $pref->getValue(),true );
            $verifyTokenService = $this->get('verification_token');
            $tokenEntity = $verifyTokenService->get( $values['token'] );
            if( $tokenEntity )
            {
                // Email has been changed and the token is still valid
                $changeEmail = $values['email'];
            }
        }

        // Show a message if the profile is marked for deletion
        $deleteAccount = false;
        if( $this->isCurrentUser($user) && isset( $userPrefs[ UserPreference::USER_PROFILE_DELETE_ACCOUNT] ) )
        {
            $deleteAccount = true;
        }

        // Build an array for credential details.
        $credService = $this->get('es_credentials');
        $credReviews = $user->getCredentialReviews();
        $creds = array();
        foreach( $credReviews as $credReview)
        {
            $creds[] = array(
                'cred' => $credService->findBySlug( $credReview->getCredential()->getSlug() ),
                'review' => $credReview
            );
        }

        return $this->render('ClassCentralSiteBundle:Profile:profile.html.twig', array(
                'user' => $user,
                'profile'=> $profile,
                'listTypes' => UserCourse::$transcriptList,
                'coursesByLists' => $clDetails['coursesByLists'],
                'reviews' => $reviews,
                'reviewedCourses' => $reviewedCourses,
                'degrees' => Profile::$degrees,
                'profilePic' => $userService->getProfilePic($user->getId()),
                'changeEmail' => $changeEmail,
                'deleteAccount' => $deleteAccount,
                'tab' => $tab,
                'userCreds' => $creds
            )
        );
    }

    /**
     * Checks whether the user is current users
     * @param User $user
     */
    private  function isCurrentUser(User $user)
    {
        $loggedInUser = $this->getUser();
        return $loggedInUser && $loggedInUser->getId() == $user->getId();
    }

    /**
     * Renders a page for the user  show the edit their profile
     * Note: The firewall takes care of whether the user is logged in
     * @param Request $request
     */
    public function editAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->get('security.context')->getToken()->getUser();

        return $this->render('ClassCentralSiteBundle:Profile:profile.edit.html.twig',array(
            'page' => 'edit_profile',
            'degrees' => Profile::$degrees,
        ));
    }

    /**
     * Ajax call that takes a
     * @param Request $request
     */
    public function saveAction(Request $request )
    {
        $user = $this->container->get('security.context')->getToken()->getUser();
        $userService = $this->get('user_service');

        // Get the json post data
        $content = $this->getRequest("request")->getContent();
        if(empty($content))
        {
            return $this->getAjaxResponse(false, "Error retrieving profile details from form");
        }
        $profileData = json_decode($content, true);
        $isAdmin = $this->get('security.context')->isGranted('ROLE_ADMIN');

        $response = $userService->saveProfile( $user, $profileData);

        return UniversalHelper::getAjaxResponse($response);
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function profileImageStep1Action(Request $request)
    {
        $kuber = $this->get('kuber');
        $user = $this->container->get('security.context')->getToken()->getUser();

        // Get the image
        $img = $request->files->get('profile-pic-uploaded');
        if( empty($img) )
        {
            return UniversalHelper::getAjaxResponse(false,'No image found');
        }

        // Check mime type
        $mimeType = $img->getMimeType();
        if( !in_array($mimeType,array("image/jpeg","image/png")) )
        {
            return UniversalHelper::getAjaxResponse(false,'Only image of type jpeg and png allowed');
        }

        // File size limit check
        $fileSize = $img->getClientSize()/1024;
        // 1 mb limit
        if($fileSize > 1024 )
        {
            return UniversalHelper::getAjaxResponse(false,'Max file size is 1 mb');
        }

        $file = $kuber->upload( $img->getPathname(), Kuber::KUBER_ENTITY_USER,Kuber::KUBER_TYPE_USER_PROFILE_PIC_TMP,$user->getId(),$img->getClientOriginalExtension());

        if($file)
        {
            return UniversalHelper::getAjaxResponse( true, array(
                'imgUrl' => $kuber->getUrlFromFile($file)
            ) );
        }
        else
        {
            return UniversalHelper::getAjaxResponse( false,"Sorry we are having technical difficulties. Please try again later" );
        }

    }

    /**
     * Receives the co-ordinates, crops the image and saves it as
     * profile image
     * @param Request $request
     */
    public function profileImageStep2Action(Request $request)
    {
        $kuber = $this->get('kuber');
        $user = $this->container->get('security.context')->getToken()->getUser();

        $content = $this->getRequest("request")->getContent();
        if(empty($content))
        {
            return UniversalHelper::getAjaxResponse(false, "Crop photo failed. Please try again later");
        }
        $data = json_decode($content, true);

        // Check if the image from step1 exists
        $file = $kuber->getFile( Kuber::KUBER_ENTITY_USER,Kuber::KUBER_TYPE_USER_PROFILE_PIC_TMP,$user->getId() );
        if(!$file)
        {
            return UniversalHelper::getAjaxResponse(false, "Crop photo failed. Please try again later");
        }

        $croppedImage = $this->cropImage($file,$data);
        // Upload the file as profile image
        $newProfilePic = $kuber->upload( $croppedImage, Kuber::KUBER_ENTITY_USER,Kuber::KUBER_TYPE_USER_PROFILE_PIC,$user->getId());
        unlink($croppedImage); // Delete the temporary file

        // Delete the temporary file from S3 and the database
        $kuber->delete( $file );

        // Clear the cache for profile pic
        $this->get('cache')->deleteCache('user_profile_pic_' . $user->getId());

        if(!$newProfilePic)
        {
            return UniversalHelper::getAjaxResponse(false, "Crop photo failed. Please try again later");
        }
        else
        {
            // Set a notification message
            $this->get('user_session')->notifyUser(
                UserSession::FLASH_TYPE_SUCCESS,
                'Profile Photo updated',
                ''
            );



            return UniversalHelper::getAjaxResponse(true);
        }
    }

    /**
     * Crops the image and returns a filepath with the location of a temporary
     * image
     * @param $file
     * @param $coords
     * @return string
     */
    private function cropImage($file, $coords)
    {
        $kuber = $this->get('kuber');

        //Download the file and create a temporary copy of the original
        $imgUrl = $kuber->getUrlFromFile($file);
        $imgFile = '/tmp/'.$file->getFileName();
        file_put_contents($imgFile,file_get_contents($imgUrl));


        $img = new \Imagick($imgFile);
        $img->cropimage(
            $coords['w'],
            $coords['h'],
            $coords['x'],
            $coords['y']
        );
        $croppedFile = '/tmp/crop-'. $file->getFileName();
        $img->writeimage( $croppedFile );

        // Delete the original file
        unlink($imgFile);
        return $croppedFile;
    }

    /**
     * Ajax call to update the password. User is authenticated by the route config
     */
    public function updatePasswordAction(Request $request) {

        $user = $this->container->get('security.context')->getToken()->getUser();
        $em   = $this->getDoctrine()->getManager();

        // Get the json request
        $content = $this->getRequest("request")->getContent();
        if(empty($content))
        {
            return UniversalHelper::getAjaxResponse(false, "Invalid Request. Please try again later");
        }
        $data = json_decode($content, true);
        $currentPassword = $data['currentPassword'];
        $newPassword =  $data['newPassword'];
        $confirmPassword = $data['confirmPassword'];

        if ( !$this->isPasswordValid($currentPassword,$user) )
        {
            return UniversalHelper::getAjaxResponse(false, 'Invalid current password');

        }

        // Check if the new and confirm password are the same
        if( $newPassword != $confirmPassword )
        {
            return UniversalHelper::getAjaxResponse(false, 'Passwords do not match');
        }

        // All good - update the password
        $factory = $this->get('security.encoder_factory');
        $encoder = $factory->getEncoder($user);
        $password = $encoder->encodePassword($newPassword, $user->getSalt());
        $user->setPassword($password);
        $em->persist($user);
        $em->flush();

        // Notify the user of successful password reset
        // Set a notification message
        $this->get('user_session')->notifyUser(
            UserSession::FLASH_TYPE_SUCCESS,
            'Password Updated Successfully',
            'Use the new password when you login next time'
        );

        return UniversalHelper::getAjaxResponse(true);
    }

    /**
     * Ajax call to update the email
     * @param Request $request
     */
    public function updateEmailAction(Request $request)
    {
        $user = $this->container->get('security.context')->getToken()->getUser();
        $em   = $this->getDoctrine()->getManager();
        $userService = $this->get('user_service');

        // Get the json request
        $content = $this->getRequest("request")->getContent();
        if(empty($content))
        {
            return UniversalHelper::getAjaxResponse(false, "Invalid Request. Please try again later");
        }
        $data = json_decode($content, true);
        $currentPassword = $data['currentPassword'];
        $email = strtolower($data['email']);

        // Confirm if the current password is valid
        if ( !$this->isPasswordValid($currentPassword, $user) )
        {
            return UniversalHelper::getAjaxResponse(false,'Invalid current password');
        }

        // Confirm if the email address is valid
        $emailConstraint = new Email();
        $emailConstraint->message = 'Please enter a valid email address';
        $errors = $this->get('validator')->validateValue(
            $email,
            $emailConstraint
        );
        if($errors->count() > 0) {
            foreach ($errors as $error)
            {
                return UniversalHelper::getAjaxResponse(false,$error->getMessage());
            }
        }

        // Confirm whether the email address does not exits
        $u = $em->getRepository('ClassCentralSiteBundle:User')->findOneBy( array('email'=>$email) );
        if($u)
        {
            return UniversalHelper::getAjaxResponse(false,'An account with this email address already exists');
        }

        // Send an email to confirm the new email address
       $token = $this->sendChangeEmailAddressVerificationEmail( $user, $email);

        // Save the token and email
        $userService->updatePreference(
            $user,
            UserPreference::USER_PROFILE_UPDATE_EMAIL,
            json_encode(array(
                'email' => $email,
                'token' => $token
            ))
        );


        // Notify the user of change of email address
        $this->get('user_session')->notifyUser(
            UserSession::FLASH_TYPE_SUCCESS,
            'Verification Email Sent to '. $email,
            'Please click the link in the email within 7 days to verify and update your email address to '. $email
        );

        return UniversalHelper::getAjaxResponse(true);
    }

    private  function isPasswordValid($password, User $user)
    {
        // If the user logged in without a password ie. facebook. Don't validate the password
        return $this->get('user_session')->isPasswordLessLogin() ||
                    $this->getPasswordEncoder($user)->isPasswordValid( $user->getPassword(), $password,$user->getSalt() ) ;
    }

    private function getPasswordEncoder(User $user)
    {
        return $this->get('security.encoder_factory')->getEncoder($user);
    }

    /**
     * Sends an email to verify the email address
     * @param User $user
     * @param $newEmail
     */
    private function sendChangeEmailAddressVerificationEmail(User $user, $newEmail)
    {
        $tokenService = $this->get('verification_token');
        $mailgun = $this->get('mailgun');
        $templating = $this->get('templating');
        $logger = $this->get('logger');

        $value = array(
            'email' => $newEmail,
            'verify_change' => 1,
            'user_id' => $user->getId()
        );
        $token = $tokenService->create($value, VerificationToken::EXPIRY_1_WEEK);
        if ($this->container->getParameter('kernel.environment') != 'test' )
        {
            // Don't send email in test environment
            $html = $templating->renderResponse('ClassCentralSiteBundle:Mail:changeOfEmailAddressVerification.html.twig', array('token' => $token->getToken()))->getContent();
            $mailgunResponse = $mailgun->sendSimpleText($newEmail,"no-reply@class-central.com","Change of email address",$html);
            if( !isset($mailgunResponse['id']) )
            {
                $logger->error('Error sending change of email address verification email', array('user_id'=>$user->getId(),'mailgun_response' => $mailgunResponse));
            }
            else
            {
                $logger->info('Change of email address verification mail sent', array('user_id'=>$user->getId(),'mailgun_response' => $mailgunResponse));
            }
        }

        return $token->getToken();
    }

    /**
     *
     * @param Request $request
     * @param $token
     */
    public function verifyNewEmailAction( Request $request, $token)
    {
        $em = $this->getDoctrine()->getManager();
        $verifyTokenService = $this->get('verification_token');
        $newsletterService = $this->get('newsletter');
        $logger = $this->get('logger');

        // check if the token is valid
        $tokenEntity = $verifyTokenService->get($token);
        if( !$tokenEntity  )
        {
            // Token is not valid
            return $this->renderChangeEmailVerificationPage("Invalid or expired token");
        }

        $tokenValue = $tokenEntity->getTokenValueArray();
        if( !$tokenValue['verify_change'] || !$tokenValue['email'] || !$tokenValue['user_id'] )
        {
            // Invalid token
            return $this->renderChangeEmailVerificationPage("Invalid or expired token");
        }

        $newEmail = $tokenValue['email'];
        $uid = $tokenValue['user_id'];
        $user = $em->getRepository('ClassCentralSiteBundle:user')->find( $uid );
        if($user)
        {
            $oldEmail = $user->getEmail();
            $user->setIsverified(1);
            $user->setEmail( $newEmail );
            $em->persist($user);
            $em->flush();

            // Subscribe the email to different mailing list and unsubscribe from the old ones
            foreach($user->getNewsletters() as $newsletter)
            {
                if ($this->container->getParameter('kernel.environment') != 'test')
                {
                    // Subscribe to the new newsletter
                   $newsletterService->subscribe($newsletter->getCode(), $newEmail);

                    // Unsubscribe from the old newsletter
                    $newsletterService->unSubscribe($newsletter->getCode(), $oldEmail);
                }
            }
        }
        $verifyTokenService->delete($tokenEntity);
        return $this->renderChangeEmailVerificationPage("Email address successfully updated. You can now log in with the new email address");
    }

    private function renderChangeEmailVerificationPage( $msg )
    {
        return $this->render('ClassCentralSiteBundle:Profile:updateEmailAddress.html.twig',array(
            'msg' => $msg
        ));
    }

    /**
     * Marks a user record for deletion. This is an Ajax call
     */
    public function deleteProfileAction(Request $request)
    {
        $user = $this->container->get('security.context')->getToken()->getUser();
        $em   = $this->getDoctrine()->getManager();
        $userService = $this->get('user_service');

        // Get the json request
        $content = $this->getRequest("request")->getContent();
        if(empty($content))
        {
            return UniversalHelper::getAjaxResponse(false, "Invalid Request. Please try again later");
        }
        $data = json_decode($content, true);
        $currentPassword = $data['currentPassword'];

        // Confirm if the current password is valid
        if ( !$this->isPasswordValid($currentPassword, $user) )
        {
            return UniversalHelper::getAjaxResponse(false,'Invalid current password');

        }

        // Mark the user for deletion
        $userService->updatePreference(
            $user,
            UserPreference::USER_PROFILE_DELETE_ACCOUNT,
            json_encode(array(
                'user_id' => $user->getId(),
            ))
        );

        // Notify the user that he has 7 days to live
        $this->get('user_session')->notifyUser(
            UserSession::FLASH_TYPE_SUCCESS,
            'Account marked for deletion',
            'Your profile and all related data will be deleted within 7 days'
        );

        return UniversalHelper::getAjaxResponse(true);
    }

}
