<?php

namespace ClassCentral\SiteBundle\Services;

class Cache {
    
    private $doctrineCache;
    
    private $prefix;
    
    public function setCache( \Doctrine\Common\Cache\Cache $doctrineCache)
    {
        $this->doctrineCache = $doctrineCache;
    }
    
    public function setCacheKeyPrefix($prefix){
        $this->prefix = $prefix;
    }


    public function get($cacheKey, $callback, $params = array())
    {
        $cache = $this->doctrineCache;
        
        // Append the key with the host
        $key = $this->prefix . '_' . $cacheKey;
        
        if($cache->contains($key))
        {
            return unserialize($cache->fetch($key));
        } 
        else 
        {
            $data = call_user_func_array($callback,$params);
            $cache->save($key, serialize($data), 3600);
            return $data;
        }
    }

    public function deleteCache($cacheKey)
    {
        $this->doctrineCache->delete($this->prefix . '_' .$cacheKey);
    }
    
    public function clear()
    {
        $cache = $this->doctrineCache;
        $cache->deleteAll();
    }
}

?>
