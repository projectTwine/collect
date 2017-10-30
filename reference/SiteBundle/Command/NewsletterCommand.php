<?php

namespace ClassCentral\SiteBundle\Command;

use ClassCentral\SiteBundle\Entity\NewsletterLog;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class NewsletterCommand extends ContainerAwareCommand {

    protected function configure()
    {
        $this
            ->setName('classcentral:newsletter')
            ->setDescription('Sends a newsletter')
            ->addArgument('code', InputArgument::REQUIRED,"Newsletter code e.g. mooc-report")
            ->addArgument('template', InputArgument::REQUIRED, "Newsletter template name eg. nov2013 -> views/Mail/mooc-report/nov2013.html.twig ")
            ->addArgument('subject',InputArgument::REQUIRED,"eg. List of 73 MOOCs starting in November")
            ->addArgument('deliverytime',InputArgument::REQUIRED, "datetime at which email is to be sent(uses local machine timezone) i.e 2015-12-27 21:45:00")
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // TODO: FORCE frequency constraint

        $em = $this->getContainer()->get('doctrine')->getManager();
        $ns = $this->getContainer()->get('newsletter');
        $templating = $this->getContainer()->get('templating');

        $code = $input->getArgument('code');
        $template = $input->getArgument('template');
        $subject = $input->getArgument('subject');
        $deliveryTime = new \DateTime($input->getArgument('deliverytime'));

        $newsletter = $em->getRepository('ClassCentralSiteBundle:Newsletter')->findOneByCode($code);
        if(!$newsletter)
        {
            $output->writeln("Please enter a valid newsletter code");
        }
        $html = $templating->renderResponse(sprintf('ClassCentralSiteBundle:Mail:%s/%s.html.twig',$code,$template));

        $result = $ns->sendNewsletter($newsletter,$html->getContent(), $subject,null,$deliveryTime->format(\DateTime::RFC2822));
        if($result)
        {
            $output->writeln("Newsletter successfully sent");
            // Save it in the log
            $nlog = new NewsletterLog();
            $nlog->setNewsletter($newsletter);
            $em->persist($nlog);
            $em->flush();
        }
        else
        {
            $output->writeln("Newsletter was not sent");
        }
    }
} 