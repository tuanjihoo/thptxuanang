<?php

$root = dirname(dirname(__FILE__));

return array(
    // If your hosting enable SAFE MODE and don't allows to write cache file.
    // Change 'File' to 'Session'.
    'cache_adapter' => 'File', // 'File' or 'Session'
    
    // If your hosting have not enabled socket, change "false" to "true"
    // to use cURL for sending request alternative.
    'use_curl' => false,
    
    // CHMOD to 0777
    'temp_dir'    => $root . '/temp',
    // CHMOD to 0777
    'session_dir' => $root . '/sessions',

    'logo_dir' => $root . '/logos',

    'upload' => array(
        'allow_file_types' => array('jpg', 'jpeg', 'gif', 'png'),
        'max_file_size'    => 2097152, // 2 * 1024 * 1024 // 2mb
    ),
    /**
     * If set, system will only add logo to images that have minimum size:
     * Ex: 300x200
     * Width of image must be greater than 300px, Height of images must be greater than 200px
     * If want to watermark to all images use this:
     * 'watermark_minimum_size' => '',
     */
    'watermark_minimum_size' => '300x200',

    'options' => array(
        'watermark' => array(
            'label'   => 'Watermark',
            'default' => 1,
            'options' => array(
                1 => 'Yes',
                0 => 'No',
            )
        ),
        'watermark_position' => array(
            'label'   => 'Watermark position',
            'default' => 'br',
            'type' => 'select',
            'options' => array(
                'tl' => 'top-left',
                'tr' => 'top-right',
                'bl' => 'bottom-left',
                'br' => 'bottom-right',
                'mc' => 'middle-center',
                'rd' => 'random'
            ),
        ),
        'watermark_logo' => array(
            'label'   => 'Logo',
            'default' => '1',
            'options' => array(
                '1' => 'Logo script',  // mean {logo_dir}/1.png
            )
        ),
        'resize' => array(
            'label'   => 'Resize',
            'default' => 0,
            'type'    => 'select',
            'options' => array(
                0    => 'Full size',
                100  => '100x',
                150  => '150x',
                320  => '320x',
                640  => '640x',
                800  => '800x',
                1024 => '1024x'
            )
        ),
        'server' => array(
            'label'   => 'Server',
            'default' => 'imgur',
            'options' => array(
                'imgur'      => 'Imgur',
                'flickr'     => 'Flickr',
                'imageshack' => 'Imageshack',
                'picasa'     => 'Picasa',
                'postimage'  => 'Postimage',
            )
        ),
    ),
    'postimage' => array(
        // not required, but recommend should have
        'accounts' => array(
            // array(
            //     'username' => 'user1',
            //     'password' => 'pass1',
            // ),
            // array(
            //     'username' => 'user1',
            //     'password' => 'pass1',
            // ),
        ),
    ),

    'imageshack' => array(
        // required
        // Register: {@link https://imageshack.com/contact/api}.
        'api_keys' => array(
            // 'other API',
            // 'other API',
        ),
        // required
        'accounts' => array(
            // array(
            //     'username' => 'user1',
            //     'password' => 'pass1',
            // ),
            // array(
            //     'username' => 'user2',
            //     'password' => 'pass2',
            // ),
        ),
    ),
    'imgur' => array(
        // not required, but recommend should have
        'accounts' => array(
            // array(
            //     'username' => 'user1',
            //     'password' => 'pass1',
            // ),
            // array(
            //     'username' => 'user1',
            //     'password' => 'pass1',
            // ),
        ),
    ),

    'picasa' => array(
        // required
        'accounts' => array(
            array(
                'username' => 'your account',
                'password' => 'your password',
                // not required, but recommend should have
                'album_ids' => array(
                ),
            ),
            // array(
                // 'username' => 'user2',
                // 'password' => 'pass2',
                // 'album_ids' => array(
                // ),
            // ),
        ),
    ),

    'flickr' => array(
        /**
         * May use TOKEN or ACCOUNT to upload image to Flickr.
         * RECOMMEND: use token to ensure the script run in stable.
         *
         * Run script "get_flickr_token.php" to get and automatic add TOKEN to config file
         *
         */
        // CHMOD to 0777.
        'token_file' => $root . '/includes/flickr_token.php',

        /**
         * Required
         * Register {@link https://www.flickr.com/services/apps/create/noncommercial/}
         */
        'api_keys' => array(
            array(
                'key'    => '',
                'secret' => '',
            ),
            // array(
            //     'key'    => 'your value',
            //     'secret' => 'your value',
            // ),
        ),
        // Should not use yahoo account because it perform slower than use get_flickr_token.
        // And sometimes, yahoo restrict automatic login, so the feature can't work well.
        // RECOMMEND: after you put apikey above, run the script: domain/get_flickr_token.php
        // and signin to yahoo, the script will automatic get and add token for you
        'accounts' => array(
            // array(
            //     // 'username' => 'your yahoo account',
            //     // 'password' => 'your yahoo password',
            // ),
        ),
    ),
);
