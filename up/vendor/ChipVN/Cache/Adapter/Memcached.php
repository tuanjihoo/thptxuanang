<?php

class ChipVN_Cache_Adapter_Memcached extends ChipVN_Cache_Adapter_Abstract
{
    /**
     * Cache options.
     *
     * @var array
     */
    protected $options = array(
        'host' => '127.0.0.1',
        'port' => 11211
    );

    /**
     * Memcached instance.
     *
     * @var Memcached
     */
    protected $memcached;

    /**
     * Create new memcache instance
     */
    public function __construct(array $options = array())
    {
        parent::__construct($options);
    }

    /**
     * Get Memcached instance.
     *
     * @return Memcached
     */
    public function getMemcached()
    {
        if (!isset($this->memcached)) {
            $this->memcached = new Memcached;
            $this->memcached->addServer($this->options['host'], $this->options['port']);
        }

        return $this->memcached;
    }

    /**
     * Set a cache entry.
     *
     * @param  strign       $key
     * @param  mixed        $value
     * @param  null|integer $expires In seconds
     * @return boolean
     */
    public function set($key, $value, $expires = null)
    {
        $key     = $this->getKeyGrouped($key);
        $expires = $expires ? $expires : $this->options['expires'];

        return $this->getMemcached()->set($key, $value, $expires);
    }

    /**
     * Get a cache entry.
     *
     * @param  string $key
     * @param  mixed  $default
     * @return mixed
     */
    public function get($key, $default = null)
    {
        $key  = $this->getKeyGrouped($key);
        $data = $this->getMemcached()->get($key);

        if ($data == false && !in_array($key, $this->getMemcached()->getAllKeys())) {
            return $default;
        }

        return $data;
    }

    /**
     * Delete a cache entry.
     *
     * @param  string  $name
     * @return boolean
     */
    public function delete($key)
    {
        $key = $this->getKeyGrouped($key);

        $this->getMemcached()->delete($key);
    }

    /**
     * Delete a group cache.
     *
     * @param  null|string $name Null to delete entries in current group
     * @return boolean
     */
    public function deleteGroup($name = null)
    {
        $group  = ($name === null ? $this->options['group'] : $name);
        $find   = ($group ? $this->getGroupIndex($group) . ':::' : '');
        $len    = strlen($find);

        foreach ($this->getMemcached()->getAllKeys() as $key) {
            if (substr($key, 0, $len) == $find
                && ($len == 0 && !strpos($key, ':::') || $len > 0 && strpos($key, ':::'))
            ) {
                $this->getMemcached()->delete($key);
            }
        }

        return true;
    }

    /**
     * Delete all cache entries with a prefix.
     * If $prefix is "null", the method will delete all entries use options[prefix].
     * If $group is not specified, options[group] will be used to execution.
     *
     * @param  string      $prefix
     * @param  null|string $group
     * @return boolean
     */
    public function deletePrefix($prefix = null, $group = null)
    {
        $prefix = ($prefix === null ? $this->options['prefix'] : $prefix);
        $group  = ($group === null ? $this->options['group'] : $group);
        $find   = ($group ? $this->getGroupIndex($group) . ':::' : '') . $prefix;
        $len    = strlen($find);

        foreach ($this->getMemcached()->getAllKeys() as $key) {
            if (substr($key, 0, $len) == $find) {
                $this->getMemcached()->delete($key);
            }
        }

        return true;
    }

    /**
     * Delete all cache entries.
     *
     * @return boolean
     */
    public function flush()
    {
        $this->getMemcached()->flush();
    }

    /**
     * Run garbage collect.
     *
     * @return void
     */
    public function garbageCollect()
    {
    }

    /**
     * Get cache key.
     *
     * @param  string $key
     * @return string
     */
    protected function getKeyGrouped($key)
    {
        $key = $this->sanitize($key);

        if ($group = $this->options['group']) {
            $index = $this->getGroupIndex($group);

            $key = $index . ':::' . $key;
        }

        return $key;
    }
}
