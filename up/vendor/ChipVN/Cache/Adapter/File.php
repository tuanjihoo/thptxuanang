<?php

class ChipVN_Cache_Adapter_File extends ChipVN_Cache_Adapter_Abstract
{
    const FILE_EXTENSION = '.cache';

    /**
     * Cache options.
     *
     * @var array
     */
    protected $options = array(
        'cache_dir' => ''
    );

    /**
     * Determine the $options is verified.
     *
     * @var boolean
     */
    protected $directoryVerified = false;

    /**
     * {@inheritdoc}
     */
    protected function sanitize($id)
    {
        return parent::sanitize(md5($id)) . self::FILE_EXTENSION;
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
        $key       = $this->sanitize($key);
        $expires   = $expires ? $expires : $this->options['expires'];
        $directory = $this->getDirectoryForEntry(true);
        $data      = ($expires + time()) . "\r\n" . serialize($value);

        return file_put_contents($directory . $key, $data);
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
        $key       = $this->sanitize($key);
        $directory = $this->getDirectoryForEntry(true);

        if (file_exists($file = $directory . $key)) {
            $fp = fopen($file, 'r');
            $lifetime = (int) fgets($fp);
            if ($lifetime >= time()) {
                $data = '';
                while (($buffer = fgets($fp, 4096)) !== false) {
                    $data .= $buffer;
                }
                fclose($fp);

                return unserialize($data);
            }
            fclose($fp);
            unlink($file);
        }

        return $default;
    }

    /**
     * Delete a cache entry.
     *
     * @param  string  $name
     * @return boolean
     */
    public function delete($key)
    {
        $key       = $this->sanitize($key);
        $directory = $this->getDirectoryForEntry(true);

        if (file_exists($file = $directory . $key)) {
            @unlink($file);

            return true;
        }

        return false;
    }

    /**
     * Delete a group cache.
     *
     * @param  null|string $name Null to delete entries in current group
     * @return boolean
     */
    public function deleteGroup($name = null)
    {
        $name      = ($name === null ? $this->options['group'] : $name);
        $index     = $this->getGroupIndex($name);
        $directory = $this->getDirectory();

        if (is_dir($directory . $index)) {
            $this->deleteDirectory($directory . $index);

            return true;
        }

        return false;
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
        $prefix    = ($prefix === null ? $this->options['prefix'] : $prefix);
        $group     = $this->getGroupIndex(($group === null ? $this->options['group'] : $group));
        $directory = $this->getDirectory();

        if (is_dir($directory . $group)) {
            $this->deleteDirectory($directory . $group, false, $prefix . '*');

            return true;
        }

        return false;
    }

    /**
     * Delete all cache entries in cache directory.
     *
     * @return boolean
     */
    public function flush()
    {
        $this->deleteDirectory(rtrim($this->getDirectory(), '\/'), false);

        return true;
    }

    /**
     * Delete a directory.
     *
     * @param  string  $directory  Without endwish DIRECTORY_SEPARATOR
     * @param  boolean $selfDelete
     * @param  string  $pattern
     * @return void
     */
    protected function deleteDirectory($directory, $selfDelete = false, $pattern = '*')
    {
        foreach ((array) glob($directory . DIRECTORY_SEPARATOR . $pattern) as $file) {
            if (is_dir($file)) {
                $this->deleteDirectory($file, true);
            } else {
                if (substr($file, -strlen(self::FILE_EXTENSION)) == self::FILE_EXTENSION) {
                    @unlink($file);
                }
            }
        }
        if ($selfDelete) {
            @rmdir($directory);
        }
    }

    /**
     * Gets cache directory.
     *
     * @return string
     */
    protected function getDirectory()
    {
        if (!$this->directoryVerified) {
            $directory = $this->options['cache_dir'];

            if (!$directory = realpath($directory)) {
                throw new Exception(sprintf('Cache directory "%s" must be a directory.'));
            }
            if (!is_writable($directory)) {
                throw new Exception(sprintf('Cache directory "%s" must be writeable.', $directory));
            }
            $this->directoryVerified = true;

            $this->options['cache_dir'] = $directory . DIRECTORY_SEPARATOR;
        }

        return $this->options['cache_dir'];
    }

    /**
     * Get directory for cache file.
     *
     * @return string
     */
    protected function getDirectoryForEntry($create = false)
    {
        $directory = $this->getDirectory();

        if ($group = $this->options['group']) {
            $index = $this->getGroupIndex($group);
            $directory .= $index . DIRECTORY_SEPARATOR;
        }
        if ($create && !is_dir($directory)) {
            mkdir($directory, 0777, true);
        }

        return $directory;
    }

    /**
     * Run garbage collect.
     *
     * @return void
     */
    public function garbageCollect()
    {
        $this->runGarbageCollect($this->getDirectory());
    }

    /**
     * Run garbage collect recusive.
     *
     * @param  string $directory
     * @return void
     */
    protected function runGarbageCollect($directory)
    {
        foreach ((array) glob($directory . '*', GLOB_MARK) as $file) {
            if (is_dir($file)) {
                $this->runGarbageCollect($file);
                if (!glob($file . '*')) {
                    rmdir($file);
                }
            } elseif (is_file($file) && substr($file, -strlen(self::FILE_EXTENSION)) == self::FILE_EXTENSION) {
                $fp = fopen($file, 'r');
                $lifetime = (int) fgets($fp);
                fclose($fp);

                if ($lifetime < time()) {
                    unlink($file);
                }
            }
        }
    }
}
