<?php
/**
 * Created by PhpStorm.
 * User: dhawal
 * Date: 10/18/14
 * Time: 9:46 PM
 */

namespace ClassCentral\SiteBundle\Command;


use ClassCentral\SiteBundle\Entity\UserPreference;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DeleteUserCommand extends ContainerAwareCommand {

    protected function configure()
    {
        $this
            ->setName("classcentral:deleteuser")
            ->setDescription("Given a user id, deletes it")
            ->addArgument('uid', InputArgument::REQUIRED,"Which user id? i.e 1");
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine')->getManager();
        $userService = $this->getContainer()->get('user_service');

        $uid = $input->getArgument('uid');
        if($uid == 'all')
        {
            $userPreferences = $em->getRepository('ClassCentralSiteBundle:UserPreference')->findBy(array(
                'type' => UserPreference::USER_PROFILE_DELETE_ACCOUNT
            ));
            $output->writeln(count($userPreferences) . 'found');
            foreach($userPreferences as $userPreference)
            {
                $user = $userPreference->getUser();
                $output->writeln( "Deleting user {$user->getId()} with name " . $user->getDisplayName() );
                $userService->deleteUser($user);
            }
        }
        else
        {
            $user = $em->getRepository('ClassCentralSiteBundle:User')->find( $uid );
            if( !$user )
            {
                $output->writeln("User $uid does not exist");
                return;
            }
            $output->writeln( "Deleting user with name " . $user->getDisplayName() );
            // Delete the user
            $userService->deleteUser($user);
            $output->writeLn("User $uid deleted");
        }
    }

} 