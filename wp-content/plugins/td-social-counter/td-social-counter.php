<?php
/*
	Plugin Name: tagDiv Social Counter
	Plugin URI: http://tagdiv.com
	Description: Social counter for WordPress. Widget and visual composer block.
	Author: tagDiv
	Version: 3.0
	Author URI: http://tagdiv.com
*/


// load the api
require_once 'td_social_api.php';


//$td_social_api = new td_social_api();
//print_r($td_social_api->get_google_plus('106192958286631454676'));
//print_r($td_social_api->get_twitter('tagDiv'));
//print_r($td_social_api->get_vimeo('brad'));
//print_r($td_social_api->td_cache);


//$td_social_api->set_cache('test1', 'user_id', 'data');
//echo $td_social_api->get_cache('test1', 'user_id');
//$td_social_api->set_cache('ra2', 'raid', 'data');




//print_r($td_social_api->get_facebook('tagdiv'));
//print_r($td_social_api->get_youtube('rathegod'));
//$td_social_api->save_transient();


class td_social_counter_plugin {

    var $plugin_path = '';

    function __construct($load_before_theme = false, $siblings_priority_level = 0) {
        $this->plugin_path =  dirname(__FILE__);
        add_action('td_global_after', array($this, 'hook_td_global_after'));
        add_action('td_wp_booster_loaded', array($this, 'td_wp_booster_loaded'));

    }

    function td_wp_booster_loaded() {
        require_once 'widget/td_block_social_counter_widget.php';
    }

    function hook_td_global_after() {
        $block_id = 'td_block_social_counter';

        $block_settings = array(
            'map_in_visual_composer' => true,
            "name" => 'Social Counter',
            "base" => 'td_block_social_counter',
            "class" => 'td_block_social_counter',
            "controls" => "full",
            "category" => __('Blocks', TD_THEME_NAME),
            'icon' => 'icon-pagebuilder-td_social_counter',
            "params" => array(
                array(
                    "param_name" => "custom_title",
                    "type" => "textfield",
                    "value" => "STAY CONNECTED",
                    "heading" => __("Optional - custom title for this block:", TD_THEME_NAME),
                    "description" => "",
                    "holder" => "div",
                    "class" => ""
                ),
                array(
                    "type" => "colorpicker",
                    "holder" => "div",
                    "class" => "",
                    "heading" => __("Header color", TD_THEME_NAME),
                    "param_name" => "header_color",
                    "value" => '', //Default Red color
                    "description" => __("Choose a custom header color for this block", TD_THEME_NAME)
                ),
                array(
                    "type" => "colorpicker",
                    "holder" => "div",
                    "class" => "",
                    "heading" => __("Header text color", TD_THEME_NAME),
                    "param_name" => "header_text_color",
                    "value" => '', //Default Red color
                    "description" => __("Choose a custom header color for this block", TD_THEME_NAME)
                ),

                array(
                    "param_name" => "facebook",
                    "type" => "textfield",
                    "value" => "",
                    "heading" => __("Facebook id:", TD_THEME_NAME),
                    "description" => "",
                    "holder" => "div",
                    "class" => ""
                ),
                array(
                    "param_name" => "twitter",
                    "type" => "textfield",
                    "value" => "",
                    "heading" => __("Twitter id:", TD_THEME_NAME),
                    "description" => "",
                    "holder" => "div",
                    "class" => ""
                ),
                array(
                    "param_name" => "youtube",
                    "type" => "textfield",
                    "value" => "",
                    "heading" => __("Youtube id:", TD_THEME_NAME),
                    "description" => "User: www.youtube.com/user/<b style='color: #000'>ENVATO</b><br/>Channel: www.youtube.com/<b style='color: #000'>channel/UCJr72fY4cTaNZv7WPbvjaSw</b>",
                    "holder" => "div",
                    "class" => ""
                ),
                array(
                    "param_name" => "vimeo",
                    "type" => "textfield",
                    "value" => "",
                    "heading" => __("Vimeo id:", TD_THEME_NAME),
                    "description" => "",
                    "holder" => "div",
                    "class" => ""
                ),
                array(
                    "param_name" => "googleplus",
                    "type" => "textfield",
                    "value" => '',
                    "heading" => __("Google Plus User:", TD_THEME_NAME),
                    "description" => "",
                    "holder" => "div",
                    "class" => ""
                ),
                array(
                    "param_name" => "instagram",
                    "type" => "textfield",
                    "value" => '',
                    "heading" => __("Instagram User:", TD_THEME_NAME),
                    "description" => "",
                    "holder" => "div",
                    "class" => ""
                ),
                array(
                    "param_name" => "soundcloud",
                    "type" => "textfield",
                    "value" => '',
                    "heading" => __("Soundcloud User:", TD_THEME_NAME),
                    "description" => "",
                    "holder" => "div",
                    "class" => ""
                ),
                array(
                    "param_name" => "rss",
                    "type" => "textfield",
                    "value" => '',
                    "heading" => __("Feed subscriber count:", TD_THEME_NAME),
                    "description" => "Write the number of followers",
                    "holder" => "div",
                    "class" => ""
                ),
                array(
                    "param_name" => "open_in_new_window",
                    "type" => "dropdown",
                    "value" => array('- Same window -' => '', 'New window' => 'y'),
                    "heading" => __("Open in:", TD_THEME_NAME),
                    "description" => "",
                    "holder" => "div",
                    "class" => ""
                )
            )
        );

        $td_theme_name = '';
        if (defined('TD_THEME_NAME')) {
            $td_theme_name = TD_THEME_NAME;
        }



        $block_settings['file'] = $this->plugin_path . '/shortcode/td_block_social_counter.php';

        if ($td_theme_name == 'Newsmag') {
            // on 010 add the border_top parameter
	        $block_settings['params'][] =
                array(
	                "param_name" => "border_top",
	                "type" => "dropdown",
	                "value" => array('- With border -' => '', 'no border' => 'no_border_top'),
	                "heading" => __("Border top:", TD_THEME_NAME),
	                "description" => "",
	                "holder" => "div",
	                "class" => ""
                );

        }

        td_api_block::add($block_id, $block_settings);



    }


}

new td_social_counter_plugin();


