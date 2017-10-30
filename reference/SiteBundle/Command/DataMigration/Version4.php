<?php

namespace ClassCentral\SiteBundle\Command\DataMigration;

/**
 * Class Version4
 * Migration related to newsletters
 *  - Add "mooc-tracker" to users newsletter preferences
 *  - Subscribe to newsletters i.e add to mailgun for users with
 * @package ClassCentral\SiteBundle\Command\DataMigration
 */
class Version4 extends VersionAbstractInterface{

    public function migrate()
    {
        $this->output->writeln("Starting data migration version 4");

        $em = $this->container->get('Doctrine')->getManager();
        $newsletterService = $this->container->get('newsletter');
        $mrNewsletter = $em->getRepository('ClassCentralSiteBundle:Newsletter')->findOneByCode("mooc-report");


        // Subscribe users first
        $users = $em->getRepository('ClassCentralSiteBundle:User')->findAll();
        $this->output->writeLn("Users found " . count($users));
        foreach($users as $user)
        {
            $newsletters = $user->getNewsletters();
            if(count($newsletters) == 0)
            {
                $this->output->writeLn("Subscribing to MOOC Tracker. User id " . $user->getId());
                // Subscribe to mooc-report newsletter
                $user->subscribe($mrNewsletter);
                $em->persist($user);
                $newsletterService->subscribeUser($mrNewsletter,$user);
            }
            else
            {
                $this->output->writeLn("Updating subscriptions. User id ". $user->getId());
                // Subscribe to newsletter
                foreach($newsletters as $newsletter)
                {
                    $newsletterService->subscribeUser($newsletter,$user);
                }
            }
        }

        // Subscribe only emails
        $emails = $em->getRepository('ClassCentralSiteBundle:Email')->findAll();
        foreach($emails as $email)
        {
            $this->output->writeLn("Updating subscriptions. Emaik id ". $email->getId());
            $newsletters = $email->getNewsletters();
            // Subscribe to newsletter
            foreach($newsletters as $newsletter)
            {
                $newsletterService->subscribeEmail($newsletter,$email);
            }
        }

        $em->flush();
    }
} 