<?php

namespace ClassCentral\SiteBundle\Services;

use Symfony\Component\DependencyInjection\ContainerInterface;

class Tag {

    private $container;
    private $cache;
    private $em;

    const ALL_TAGS_CACHE_KEY = 'all_tags_array';

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->cache = $container->get('cache');
        $this->em = $container->get('doctrine')->getManager();
    }

    /**
     * Returns an array of all tags from the cache or database
     */
    public function getAllTags()
    {
       return $this->cache->get(
           self::ALL_TAGS_CACHE_KEY,
           array($this,'getAllTagsFromDatabase')
       );
    }

    public  function getAllTagsFromDatabase()
    {
        $tags  = array();
        $tagEntities = $this->em->getRepository('ClassCentralSiteBundle:Tag')->findAll();

        foreach($tagEntities as $tag)
        {
            $tags[] = $tag->getName();
        }

        return $tags;
    }

    /**
     * Updates all the tags for the courses.
     * Removes existing tags if its not part $tags array
     * @param Course $c
     * @param array $tags
     */
    public function saveCourseTags(\ClassCentral\SiteBundle\Entity\Course $c, array $tags)
    {
        $newTag = false;
        $tagsToBeRemoved = array();
        // Get existing tags for this course
        $ct = array();
        foreach($c->getTags() as $cTag)
        {
            $tName = $cTag->getName();
            $ct[] = $tName;

            if(!in_array($tName, $tags))
            {
                $tagsToBeRemoved[]= $cTag;
            }
        }

        foreach($tagsToBeRemoved as $tr)
        {
            $c->removeTag($tr);
        }

        // Create new tags if necessary
        foreach($tags as $tag)
        {
            if(in_array($tag,$ct))
            {
                // nothing to do here
                continue;
            }

            // Check if the tag exists
            $t = $this->em->getRepository('ClassCentralSiteBundle:Tag')->findOneBy( array('name'=> $tag) );
            if(!$t)
            {
                $newTag = true;

                $t = new \ClassCentral\SiteBundle\Entity\Tag();
                $t->setName($tag);
                $this->em->persist($t);
            }

            $c->addTag($t);
        }


        $this->em->persist($c);
        $this->em->flush();

        // Flush the tag cache if a new tag was created
        if($newTag)
        {
            $this->cache->deleteCache(self::ALL_TAGS_CACHE_KEY);
        }
    }

    /**
     * Only adds new tags if not already added
     * @param Course $c
     * @param array $tags
     */
    public function addCourseTags(Course $c, array $tags)
    {
        $existingTags = array();
        foreach($c->getTags() as $cTag)
        {
            $existingTags[] = strtolower(trim($cTag->getName()));
        }

        foreach ($tags as $tag)
        {
            if(in_array($tag,$existingTags))
            {
                continue;
            }

            // Check if the tag exists
            $t = $this->em->getRepository('ClassCentralSiteBundle:Tag')->findOneBy( array('name'=> $tag) );
            if(!$t)
            {
                $newTag = true;

                $t = new \ClassCentral\SiteBundle\Entity\Tag();
                $t->setName($tag);
                $this->em->persist($t);
            }

            $c->addTag($t);
        }

        $this->em->persist($c);
        $this->em->flush();

    }

    public function copyCourses(\ClassCentral\SiteBundle\Entity\Tag $tagOrig, \ClassCentral\SiteBundle\Entity\Tag $tagDup)
    {
        $copied = 0;
        foreach($tagDup->getCourses() as $course)
        {
            $course->removeTag($tagOrig); // if it exists to avoid duplicates
            $course->addTag($tagOrig);
            $this->em->persist($course);
            $copied++;
        }
        $this->em->flush();

        // Update the follows table
        $conn = $this->em->getConnection();
        $conn->exec("
            UPDATE follows SET item_id ={$tagOrig->getId()} WHERE item='tag' and item_id = {$tagDup->getId()}
        ");

        return $copied;
    }


} 