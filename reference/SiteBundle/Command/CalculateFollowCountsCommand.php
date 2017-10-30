<?php

namespace ClassCentral\SiteBundle\Command;


use ClassCentral\SiteBundle\Entity\FollowCounts;
use ClassCentral\SiteBundle\Entity\Item;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CalculateFollowCountsCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('classcentral:follows:calculatecount');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine')->getManager();
        $fs = $this->getContainer()->get('follow');


        $output->writeln("Updating folow counts for institutions");
        $this->saveAndUpdateCountByItemType(Item::ITEM_TYPE_INSTITUTION);

        $output->writeln("Updating folow counts for providers");
        $this->saveAndUpdateCountByItemType(Item::ITEM_TYPE_PROVIDER);

        $output->writeln("Updating folow counts for ollections");
        $this->saveAndUpdateCountByItemType(Item::ITEM_TYPE_COLLECTION);

        $output->writeln("Updating folow counts for credentials");
        $this->saveAndUpdateCountByItemType(Item::ITEM_TYPE_CREDENTIAL);

        $output->writeln("Updating folow counts for subjects");
        $this->saveAndUpdateCountByItemType(Item::ITEM_TYPE_SUBJECT);

        $output->writeln("Updating folow counts for tags");
        $this->saveAndUpdateCountByItemType(Item::ITEM_TYPE_TAG);

        $output->writeln("Updating folow counts for languages");
        $this->saveAndUpdateCountByItemType(Item::ITEM_TYPE_LANGUAGE);

    }


    public function saveAndUpdateCountByItemType($itemType)
    {
        $fs = $this->getContainer()->get('follow');
        $em = $this->getContainer()->get('doctrine')->getManager();
        $results = $fs->returnFollowCountByItemType($itemType);
        foreach ($results as $result)
        {
            $item = Item::getItem($itemType,$result['id']);
            $followCountObj = $fs->getFollowCountsObjectFromItem($item);
            if(!$followCountObj)
            {
                $followCountObj = new FollowCounts();
                $followCountObj->setItem($item->getType());
                $followCountObj->setItemId($item->getId());
            }
            $followCountObj->setFollowed($result['num_follows']);
            $em->persist($followCountObj);
        }

        $em->flush();
    }
}