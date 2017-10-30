<?php
/**
 * Created by PhpStorm.
 * User: dhawal
 * Date: 5/25/15
 * Time: 9:35 PM
 */

namespace ClassCentral\SiteBundle\Command;


use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateProfileScoreCommand extends ContainerAwareCommand {

    protected function configure()
    {
        $this
            ->setName('classcentral:user:profilescore')
            ->setDescription('Updates the profile score for all users')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine')->getManager();
        $limit = 500;
        $offset = 0;
        $profilesUpdated = 0;
        $usersExamined = 0;

        $userService = $this->getContainer()->get('user_service');


        $users = $em->getRepository('ClassCentralSiteBundle:User')->findBy(
            array(), array(), $limit, $offset
        );

        while($users)
        {
            foreach($users as $user)
            {
                $usersExamined++;
                $profile = $user->getProfile();
                if( !$profile )
                {
                    continue;
                }

                $score = $userService->calculateProfileScore($user);
                if ( $score != $profile->getScore() )
                {
                    $profile->setScore( $score );
                    $em->persist($profile);
                    $profilesUpdated++;
                }
            }
            $em->flush();
            $em->clear();
            $offset += $limit;
            unset( $users );
            $users = $em->getRepository('ClassCentralSiteBundle:User')->findBy(
                array(), array(), $limit, $offset
            );
            $output->writeln("Processed $offset users");
        }

        $em->flush();
        $output->writeln("Users Examined : " . $usersExamined);
        $output->writeln("Profiles Updated : " . $profilesUpdated);
    }
} 