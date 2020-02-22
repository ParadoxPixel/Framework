<?php
namespace Fontibus\Database\Cache;

class Cache {

    protected $cacheDir = null;
    protected ?int $cache = null;
    protected $finish = null;

    function __construct($dir = null, $time = 0) {
        if (!file_exists($dir)) {
            mkdir($dir, 0755);
        }

        $this->cacheDir = $dir;
        $this->cache = $time;
        $this->finish = time() + $time;
    }

    /**
     *  Get cache with key
     *
     * @param string $key
     * @param bool $array
     */
    public function getCache(string $key, bool $array = false): array {
        if (is_null($this->cache)) {
            return false;
        }

        $cacheFile = $this->cacheDir . $this->fileName($key) . '.cache';
        if (file_exists($cacheFile)) {
            $cache = json_decode(file_get_contents($cacheFile), $array);
            if (($array ? $cache['finish'] : $cache->finish) < time()) {
                unlink($cacheFile);
                return false;
            }

            return ($array ? $cache['data'] : $cache->data);
        }

        return false;
    }

    /**
     * Set the cache
     *
     * @param string $key
     * @param string $value
     * @return bool
     */
    public function setCache(string $key, string $value): bool {
        if(is_null($this->cache)) {
            return false;
        }

        $cacheFile = $this->cacheDir . $this->fileName($key) . '.cache';
        $cacheFile = fopen($cacheFile, 'w');
        if ($cacheFile) {
            fputs($cacheFile, json_encode(['data' => $value, 'finish' => $this->finish]));
        }

        return true;
    }

    protected function fileName(string $name): string {
        return md5($name);
    }

}