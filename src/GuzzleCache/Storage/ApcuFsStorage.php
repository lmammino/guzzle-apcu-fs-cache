<?php

namespace LM\GuzzleCache\Storage;

use Kevinrob\GuzzleCache\CacheEntry;
use Kevinrob\GuzzleCache\Storage\CacheStorageInterface;
use Symfony\Component\Cache\Simple\ChainCache;
use Symfony\Component\Cache\Simple\ArrayCache;
use Symfony\Component\Cache\Simple\ApcuCache;
use Symfony\Component\Cache\Simple\FilesystemCache;

class ApcuFsStorage implements CacheStorageInterface {
    private $cache;
    private $ttl;
    private $onHit;
    private $onMiss;
    public $usingApcu = false;
    public $usingFilesystem = true;

    public function __construct($dir = null, $namespace = 'default', $ttl = 60, $onHit = null, $onMiss = null) {
        $this->ttl = $ttl;
        $this->onHit = $onHit;
        $this->onMiss = $onMiss;

        $cacheLayers = [
            new ArrayCache($ttl, false)
        ];

        if(extension_loaded('apc') && ini_get('apc.enabled'))
        {
            array_push($cacheLayers, new ApcuCache($namespace, $ttl));
            $this->usingApcu = true;
            $this->usingFilesystem = false;
        } else {
            if (!$dir) {
                $dir = sys_get_temp_dir();
            }
            array_push($cacheLayers, new FilesystemCache($namespace, $ttl, $dir));
        }

        $this->cache = new ChainCache($cacheLayers, $ttl);
    }
    
    /**
     * @param string $key
     *
     * @return CacheEntry|null the data or false
     */
    public function fetch($key) {
        $entry = $this->cache->get($key);
        $isHit = $entry instanceof CacheEntry;

        if ($isHit) {
            if (is_callable($this->onHit)) {
                call_user_func($this->onHit, $key);
            }

            return $entry;
        }
    
        if (is_callable($this->onMiss)) {
            call_user_func($this->onMiss, $key);
        }
    
        return;
    }

    /**
     * @param string     $key
     * @param CacheEntry $data
     *
     * @return bool
     */
    public function save($key, CacheEntry $data) {
        if ($this->cache->has($key)) {
            return false;
        }
        
        $this->cache->set($key, $data, $this->ttl);
        
        return true;
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    public function delete($key) {
        return $this->cache->delete($key);
    }
}