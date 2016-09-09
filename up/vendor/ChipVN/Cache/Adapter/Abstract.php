<?php

abstract class ChipVN_Cache_Adapter_Abstract
{
    /**
     * Default value expires
     */
    const DEFAULT_EXPIRES = 900; // seconds

    /**
     * Cache options.
     *
     * @var array
     */
    protected $defaultOptions = array(
        'group'   => '',
        'prefix'  => '',
        'expires' => self::DEFAULT_EXPIRES,
    );

    /**
     * Create a storage instance.
     *
     * @param array $options
     */
    public function __construct(array $options = array())
    {
        $this->setOptions($options);
    }

    /**
     * Cache options.
     *
     * @var array
     */
    protected $options = array();

    /**
     * Sanitize cache key.
     *
     * @param  string $id
     * @return string
     */
    protected function sanitize($id)
    {
        return $this->options['prefix'] . str_replace(array('/', '\\', ' '), '_', $id);
    }

    /**
     * Set cache options.
     *
     * @param  array $options
     * @return array
     */
    public function setOptions(array $options)
    {
        return $this->options = array_merge($this->defaultOptions, $this->options, $options);
    }

    /**
     * Set cache option by name, value.
     *
     * @param string $name
     * @param mixed  $value
     */
    public function setOption($name, $value)
    {
        $this->options[$name] = $value;
    }

    /**
     * Get cache options.
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Get group index by name.
     *
     * @param  string $name
     * @return string
     */
    public function getGroupIndex($name)
    {
        return '__GROUP_' . $name;
    }

    /**
     * Set a cache entry.
     *
     * @param  strign       $key
     * @param  mixed        $value
     * @param  null|integer $expires In seconds
     * @return boolean
     */
    abstract public function set($key, $value, $expires = null);

    /**
     * Get a cache entry.
     *
     * @param  string $key
     * @param  mixed  $default
     * @return mixed
     */
    abstract public function get($key, $default = null);

    /**
     * Delete a cache entry.
     *
     * @param  string  $name
     * @return boolean
     */
    abstract public function delete($key);

    /**
     * Delete a group cache.
     *
     * @param  null|string $name Null to delete entries in current group
     * @return boolean
     */
    abstract public function deleteGroup($name = null);

    /**
     * Delete all cache entries with a prefix.
     * If $prefix is "null", the method will delete all entries use options[prefix].
     * If $group is not specified, options[group] will be used to execution.
     *
     * @param  string      $prefix
     * @param  null|string $group
     * @return boolean
     */
    abstract public function deletePrefix($prefix = null, $group = null);

    /**
     * Delete all cache entries.
     *
     * @return boolean
     */
    abstract public function flush();

    /**
     * Run garbage collect.
     *
     * @return void
     */
    abstract public function garbageCollect();
}
