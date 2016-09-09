<?php
/**
 * A library for uploading image to some hosting services
 * like picasa, imgur, imageshack, imgur etc..
 *
 * @author     Phan Thanh Cong <ptcong90@gmail.com>
 * @copyright  2010-2014 Phan Thanh Cong.
 * @license    http://www.opensource.org/licenses/mit-license.php  MIT License
 * @version    5.1
 * @release    Apr 08, 2014
 * @website    http://ptcong.com
 */

class ChipVN_ImageUploader_Manager
{
    const VERSION = '5.1';

    /**
     * Create a plugin for uploading.
     *
     * @param  string                             $plugin
     * @return ChipVN_ImageUploaderPlugins_Plugin
     */
    public static function make($plugin)
    {
        $class = 'ChipVN_ImageUploader_Plugins_' . ucfirst($plugin);

        return new $class;
    }
}
