<?php
/**
 * Created by JetBrains PhpStorm.
 * User: dhawal
 * Date: 9/16/13
 * Time: 11:41 PM
 * To change this template use File | Settings | File Templates.
 */

namespace ClassCentral\SiteBundle\Controller;

use ClassCentral\SiteBundle\Entity\User;
use ClassCentral\SiteBundle\Entity\UserFb;
use ClassCentral\SiteBundle\Entity\UserGoogle;
use ClassCentral\SiteBundle\Entity\VerificationToken;
use ClassCentral\SiteBundle\Services\Kuber;
use ClassCentral\SiteBundle\Utility\UniversalHelper;
use Facebook\FacebookRedirectLoginHelper;
use Facebook\FacebookRequest;
use Facebook\FacebookRequestException;
use Facebook\FacebookSession;
use Facebook\GraphUser;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\HttpFoundation\Session;

class LoginController extends Controller{

    public function loginAction(Request $request)
    {
        // Check if user is not already logged in.
        if($this->get('security.context')->isGranted('IS_AUTHENTICATED_FULLY'))
        {
            return $this->redirect($this->generateUrl('ClassCentralSiteBundle_homepage'));
        }


        $session = $request->getSession();

        // get the login error if there is one
        if ($request->attributes->has(SecurityContext::AUTHENTICATION_ERROR))
        {
            $error = $request->attributes->get(
                SecurityContext::AUTHENTICATION_ERROR
            );
        }
        else
        {
            $error = $session->get(SecurityContext::AUTHENTICATION_ERROR);
            $session->remove(SecurityContext::AUTHENTICATION_ERROR);
        }


        return $this->render(
            'ClassCentralSiteBundle:Login:login.html.twig',
            array(
                'page' => 'auth',
                // last username entered by the user
                'last_username' => $session->get(SecurityContext::LAST_USERNAME),
                'error'         => $error,
                'redirectUrl' => $this->getLastAccessedPage($session),
            )
        );
    }

    private function getLastAccessedPage($session, $src = null)
    {
        $last_route = $session->get('this_route');
        $redirectUrl = null;
        if(! empty($last_route))
        {
            if(!empty($src)) {
                $last_route['params'] = array_merge( $last_route['params'], array(
                    'ref' => 'user_created',
                    'src' => $src
                ) );
            }
            $redirectUrl = $this->generateUrl($last_route['name'], $last_route['params']);
        }

        return $redirectUrl;
    }
    /**
     * Redirects the user to fb auth url
     * @param Request $request
     */
    public function redirectToAuthorizationAction(Request $request)
    {
        $src = $request->query->get('src');
        $this->get('session')->set('fb_auth_src', $src);
        $helper = $this->getFBLoginHelper();

        return $this->redirect( $helper->getLoginUrl(array(
            'public_profile',
            'email',
        )) );
    }

    private function getFBLoginHelper( $src = null)
    {
        FacebookSession::setDefaultApplication(
            $this->container->getParameter('fb_app_id'),
            $this->container->getParameter('fb_secret')
        );

        $redirectUrl = $this->generateUrl(
            'fb_authorize_redirect',
            array(),
            true
        );

        return new FacebookRedirectLoginHelper( $redirectUrl);
    }

    public function fbReceiveAuthorizationCodeAction(Request $request)
    {

        $em = $this->getDoctrine()->getManager();
        $userService = $this->get('user_service');
        $userSession = $this->get('user_session');
        $src =  $this->get('session')->get('fb_auth_src');
        $logger = $this->get('logger');

        $logger->info("FBAUTH: FB auth redirect");

        $helper = $this->getFBLoginHelper();

        try
        {
            $session = $helper->getSessionFromRedirect();
            if( !$session )
            {
                 // Redirect to the signup page
                $logger->info("FBAUTH: FB auth denied by the user");
                return $this->redirect($this->generateUrl('signup'));
            }



            $fbRequest = new FacebookRequest($session,'GET','/me');
            $fbUser = $fbRequest->execute()->getGraphObject(GraphUser::className());

            $email = $fbUser->getEmail();
            if(!$email)
            {
                // TODO : Render error page
                $logger->error("FBAUTH: Email missing");
                echo "Email is required. Please revoke Class Central App from your <a href='https://www.facebook.com/settings?tab=applications'>Facebook settings page</a> and then signup again.";
                exit();
            }
            $name = $fbUser->getName();
            $fbId = $fbUser->getId();

            // Check if the fb users has logged in before using the FB Id
            $usersFB = $em->getRepository('ClassCentralSiteBundle:UserFb')->findOneBy(array(
                'fbId' => $fbId
            ));

            if($usersFB)
            {
                $user = $usersFB->getUser();
            }
            else
            {
                // Check if an account with this email address exist. If it does then merge
                // these accounts
                $user = $em->getRepository('ClassCentralSiteBundle:User')->findOneBy(array(
                    'email' => $email
                ));
            }

            if($user)
            {
                $userService->login($user);

                // Record Logins
                $keen = $this->container->get('keen');
                $keen->recordLogins($user,'facebook');

                $userSession->setPasswordLessLogin(true);
                // Check whether the user has fb details
                $ufb = $user->getFb();
                if($ufb)
                {
                    $logger->info("FBAUTH: FB user exists");
                }
                else
                {
                    $logger->info("FBAUTH: Email exists but UserFb table is empty");
                    // Create a FB info
                    $ufb = new UserFb();
                    $ufb->setFbEmail($email);
                    $ufb->setFbId($fbId);
                    $ufb->setUser($user);
                }

                $em->persist($ufb);
                $em->flush();

                $userSession->login($user, User::SIGNUP_TYPE_FACEBOOK);

                $redirectUrl =
                    ($this->getLastAccessedPage($request->getSession())) ?
                        $this->getLastAccessedPage($request->getSession()):
                        $this->generateUrl('user_library');

                $logger->info(' LOGIN REDIRECT URL ' . $redirectUrl);

                return $this->redirect( $redirectUrl );
            }
            else
            {
                $logger->info("FBAUTH: New user");
                $newsletterService = $this->get('newsletter');
                $newsletter = $em->getRepository('ClassCentralSiteBundle:Newsletter')->findOneByCode('mooc-report');


                // Create a new account
                $user = new \ClassCentral\SiteBundle\Entity\User();
                $user->setEmail($email);
                $user->setName($name);
                $user->setPassword($this->getRandomPassword()); // Set a random password
                $user->setIsverified(true);
                $user->setSignupType(\ClassCentral\SiteBundle\Entity\User::SIGNUP_TYPE_FACEBOOK);

                $signupSrc = (empty($src)) ? 'facebook' : $src;
                $userService->createUser($user, false, $signupSrc );
                $userSession->setPasswordLessLogin(true); // Set the variable to show that the user didn't use a password to login

                // Note: A profile edit modal will be shown to the user
                $redirectUrl =
                    ($this->getLastAccessedPage($request->getSession(),$signupSrc)) ?
                        $this->getLastAccessedPage($request->getSession(),$signupSrc):
                        $this->generateUrl('user_library');

                // Create a FB info
                $ufb = new UserFb();
                $ufb->setFbEmail($email);
                $ufb->setFbId($fbId);
                $ufb->setUser($user);
                $em->persist($ufb);
                $em->flush();

                $this->uploadFacebookProfilePic( $user, $fbId);

                // Subscribe to newsletter
                $subscribed = $newsletterService->subscribeUser($newsletter, $user);
                $logger->info("preferences subscribed : email newsletter subscription", array(
                    'email' =>$user->getId(),
                    'newsletter' => $newsletter->getCode(),
                    'subscribed' => $subscribed
                ));

                // Show the user a profile edit window
                $this->get('session')->getFlashBag()->set('show_post_signup_profile_modal',1);

                return $this->redirect($redirectUrl);
            }

        }
        catch (FacebookRequestException $e)
        {
            $logger->info("FBAUTH: FB Auth error - " . $e->getMessage());
            return null;
        }
        catch (\Exception $e)
        {
            $logger->info("FBAUTH: Api exception" . $e->getMessage());
            return null;
        }

    }


    /**
     * Uploads the facebook profile picture as a users profile picture
     * @param User $user
     * @param $username
     */
    private function uploadFacebookProfilePic( \ClassCentral\SiteBundle\Entity\User $user, $fbId)
    {
        try{
            $kuber = $this->get('kuber');

            $url = sprintf("https://graph.facebook.com/v2.3/%s/picture?type=large",$fbId);

            //Get the extension
            $size = getimagesize($url);
            $extension = image_type_to_extension($size[2]);
            $imgFile = '/tmp/'. $fbId;
            file_put_contents( $imgFile, file_get_contents($url)); // Gets a silhouette if image does not exist

            // Upload the file to S3 using Kuber
            $kuber->upload( $imgFile, Kuber::KUBER_ENTITY_USER, Kuber::KUBER_TYPE_USER_PROFILE_PIC, $user->getId(), ltrim($extension,'.'));
            // Clear the cache for profile pic
            $this->get('cache')->deleteCache('user_profile_pic_' . $user->getId());
            // Delete the temporary file
            unlink( $imgFile );
        } catch ( \Exception $e ) {
            $this->get('logger')->error(
                "Failed uploading Facebook Profile Picture for user id " . $user->getId() .
                ' with error: ' . $e->getMessage()
            );
        }

    }

    private function uploadGoogleProfilePic( \ClassCentral\SiteBundle\Entity\User $user, $imageUrl)
    {
        try{
            $kuber = $this->get('kuber');

            //Get the extension
            $size = getimagesize($imageUrl);
            $extension = image_type_to_extension($size[2]);
            $imgFile = '/tmp/google_profile_'. $user->getId();
            file_put_contents( $imgFile, file_get_contents($imageUrl)); // Gets a silhouette if image does not exist

            // Upload the file to S3 using Kuber
            $kuber->upload( $imgFile, Kuber::KUBER_ENTITY_USER, Kuber::KUBER_TYPE_USER_PROFILE_PIC, $user->getId(), ltrim($extension,'.'));
            // Clear the cache for profile pic
            $this->get('cache')->deleteCache('user_profile_pic_' . $user->getId());
            // Delete the temporary file
            unlink( $imgFile );
        } catch ( \Exception $e ) {
            $this->get('logger')->error(
                "Failed uploading Google Profile Picture for user id " . $user->getId() .
                ' with error: ' . $e->getMessage()
            );
        }

    }


    private function getRandomPassword()
    {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $str = substr( str_shuffle( $chars ), 0, 20 );

        return $str;
    }

    /**
     * Called from the front end
     */
    public function googleAuthAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $data = $request->getContent();
        $logger = $this->get('logger');
        $userService = $this->get('user_service');
        $userSession = $this->get('user_session');
        $errorMsg = '';

        if(!empty($data))
        {
            $params = json_decode($data,true);
            $token = $params['token'];

            // if user account exists login, else signup
            $client = new \Google_Client();
            $client->setClientId($this->container->getParameter('google_auth_client_id'));
            $payload = $client->verifyIdToken( $token );
            if($payload)
            {
                // validate the token
                if( ($payload['iss'] == 'https://accounts.google.com' || $payload['iss'] == 'accounts.google.com') &&  $payload['aud'] == $this->container->getParameter('google_auth_client_id'))
                {

                    // Valid user
                    $email = $payload['email'];
                    $googleId = $payload['sub'];

                    // Check if the Google user has logged in before using the Google Id. Its being stored in the userFb table.
                    $usersGoogle = $em->getRepository('ClassCentralSiteBundle:UserGoogle')->findOneBy(array(
                        'googleId' => $googleId
                    ));

                    if($usersGoogle)
                    {
                        $user = $usersGoogle->getUser();
                    }
                    else
                    {
                        // Check if an account with this email address exist. If it does then merge
                        // these accounts
                        $user = $em->getRepository('ClassCentralSiteBundle:User')->findOneBy(array(
                            'email' => $email
                        ));
                    }

                    $newUser = false;

                    if($user)
                    {
                        $userService->login($user);

                        // Record Logins
                        $keen = $this->container->get('keen');
                        $keen->recordLogins($user,'google');

                        // Check whether the user has fb details
                        $ugoogle = $user->getGoogle();
                        if($ugoogle)
                        {
                            $logger->info("Google Auth: Google user exists");
                        }
                        else
                        {
                            $logger->info("Google Auth: Email exists but UserGoogle table is empty");
                            // Create Google info
                            $ugoogle = new UserGoogle();
                            $ugoogle->setGoogleEmail($email);
                            $ugoogle->setGoogleId($googleId);
                            $ugoogle->setUser($user);
                            $em->persist($ugoogle);
                            $em->flush();
                        }

                        $userSession->setPasswordLessLogin(true);
                        $userSession->login($user, User::SIGNUP_TYPE_GOOGLE);
                    }
                    else
                    {
                        // User signup. New user
                        $newsletterService = $this->get('newsletter');
                        $newsletter = $em->getRepository('ClassCentralSiteBundle:Newsletter')->findOneByCode('mooc-report');


                        // Create a new account
                        $user = new \ClassCentral\SiteBundle\Entity\User();
                        $user->setEmail($email);
                        $user->setName($payload['name']);
                        $user->setPassword($this->getRandomPassword()); // Set a random password
                        $user->setIsverified($payload['email_verified']);
                        $user->setSignupType(\ClassCentral\SiteBundle\Entity\User::SIGNUP_TYPE_GOOGLE);

                        $signupSrc = (empty($src)) ? 'google' : $src;
                        $userService->createUser($user, false, $signupSrc );
                        $userSession->setPasswordLessLogin(true); // Set the variable to show that the user didn't use a password to login

                        $ugoogle = new UserGoogle();
                        $ugoogle->setGoogleEmail($email);
                        $ugoogle->setGoogleId($googleId);
                        $ugoogle->setUser($user);
                        $em->persist($ugoogle);
                        $em->flush();

                        if($payload['picture'])
                        {
                            $this->uploadGoogleProfilePic($user,$payload['picture']);
                        }

                        // Subscribe to newsletter
                        $subscribed = $newsletterService->subscribeUser($newsletter, $user);

                        // Show the user a profile edit window
                        $this->get('session')->getFlashBag()->set('show_post_signup_profile_modal',1);
                    }

                    return UniversalHelper::getAjaxResponse(true,array('newUser'=>$newUser));
                }
                else
                {
                    $errorMsg = 'Token validation failed';
                }
            }
            else
            {
                $errorMsg = 'Authentication failed';
            }

        }
        else
        {
            $errorMsg = 'Invalid Request';
        }

        return UniversalHelper::getAjaxResponse(false,$errorMsg);

    }

    /**
     *  Users can request a link that will get them logged in via email
     */
    public function loginViaEmailAction(Request $request)
    {
        if($this->get('security.context')->isGranted('IS_AUTHENTICATED_FULLY'))
        {
            return $this->redirect($this->generateUrl('user_library'));
        }

        return $this->render('ClassCentralSiteBundle:Login:login.via.email.html.twig', array(
          'page' => 'auth',
        ));
    }

    /*
     * Sends an email that contains a link to login
     */
    public function loginViaEmailSendEmailAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $tokenService = $this->get('verification_token');
        $mailgun = $this->get('mailgun');
        $templating = $this->get('templating');
        $session = $this->get('session');
        $logger = $this->get('logger');
        $session->set('leUser',null);

        $email = $request->request->get('email');
        $session->set('leEmail',$email);

        if($email)
        {
            $user = $em->getRepository('ClassCentralSiteBundle:User')->findOneByEmail($email);
            if($user)
            {
                $token = $tokenService->create("login_token=1&user_id=" . $user->getId(), VerificationToken::EXPIRY_1_DAY);
                if ($this->container->getParameter('kernel.environment') != 'test')
                {
                    $html = $templating->renderResponse('ClassCentralSiteBundle:Mail:login.via.email.html.twig', array('token' => $token->getToken()))->getContent();
                    $mailgunResponse = $mailgun->sendSimpleText($user->getEmail(),"no-reply@class-central.com","Sign in to Class Central",$html);
                    if(!isset($mailgunResponse['id']))
                    {
                        $logger->error('Error sending login via email mail', array('user_id'=>$user->getId(),'mailgun_response' => $mailgunResponse));
                    }
                    else
                    {
                        $logger->info('Login via Email mail sent sent', array('user_id'=>$user->getId(),'mailgun_response' => $mailgunResponse));
                    }
                }
                $session->set('leUser', $user);
            }
        }



        return $this->redirect($this->generateUrl('loginViaEmail'));
    }

}
