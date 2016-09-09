<?php

class ChipVN_Cache_Manager
{
    /**
     * Create new ChipVN_Cache_Adapter_Interface instrance.
     *
     * @param  string|ChipVN_Cache_Adapter_Interface $adapter
     * @param  array                                 $options
     * @return ChipVN_Cache_Adapter_Interface
     */
    public static function make($adapter = 'Session', array $options = array())
    {
        $class = 'ChipVN_Cache_Adapter_' . ucfirst($adapter);

        return new $class($options);
    }
}
