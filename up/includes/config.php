<?php

$root = dirname(dirname(__FILE__));

return array(
    // Nếu hosting enable SAFE MODE và không ghi được file cache.
    // Sửa 'File' thành 'Session'
    'cache_adapter' => 'File', // 'File' or 'Session'
    
    // Nếu hosting không enable socket, change "false" to "true"
    // để dùng cURL thay thế cho việc upload
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
     * Hệ thống sẽ watermark vào file có kích cỡ tối thiểu widthxheight
     * VD: 300x200
     * => Chiều rộng của ảnh phải lớn hơn 300px, chiều cao của ảnh phải lớn hơn 200px
     * Nếu muốn ảnh nào cũng watermark thì để trống
     * 'watermark_minimum_size' => '',
     */
    'watermark_minimum_size' => '300x200',

    'options' => array(
        'watermark' => array(
            'label'   => 'Đóng dấu',
            'default' => 1,
            'options' => array(
                1 => 'Yes',
                // 0 => 'No', // xóa dòng này nếu bắt buộc user sử dụng watermark
            )
        ),
        'watermark_position' => array(
            'label'   => 'Vị trí đóng dấu',
            'default' => 'br',
            'type' => 'select',
            'options' => array(
                'tl' => 'Trên bên trái',
                'tr' => 'Trên bên phải',
                'bl' => 'Dưới bên trái',
                'br' => 'Dưới bên phải',
                'mc' => 'Giữa',
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
                'postimage'  => 'Postimage',
            )
        ),
    ),

    'postimage' => array(
        // Không bắt buộc
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

    'imgur' => array(
        /**
         * Không bắt buộc nhưng nên có để tránh bị giới hạn upload.
         */
        'accounts' => array(
            array(
                'username' => 'tuanlibra',
                'password' => 'tuan2015',
            ),
            /**
             * Có thể thêm nhiều account khác theo mẫu tương tự bên dưới
             */
            array(
                'username' => 'tuanjihoo',
                'password' => 'tuan2015',
            ),
        ),
    ),
);
