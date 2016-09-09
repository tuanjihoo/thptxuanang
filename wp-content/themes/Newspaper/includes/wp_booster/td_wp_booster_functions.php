<?php
/**
 * WordPress booster V 3.0 by tagDiv
 */

do_action('td_wp_booster_before');


if (TD_DEPLOY_MODE == 'dev') {
    require_once('external/kint/Kint.class.php');
}


// theme specific config values
require_once('td_global.php');
require_once('td_util.php');

// load the api


class td_api_base {


    // flag marked by get_by_id and get_key function. It's used just for debugging
    const USED_ON_PAGE = 'used_on_page';

    const CLASS_AUTOLOADED = 'class_autoloaded'; // flag for marking autoloaded classes

    const TYPE = 'type';

    // the main array settings
    private static $components_list = array();



    /**
     * This method adds settings in the main settings array (self::$component_list)
     * An array of settings is set for the ($class_name, $id) key.
     * If there already exists the ($class_name, $id) key in the main settings array, an error exception is thrown. The update
     * method must be used instead, which ensures the settings are not previously loaded using self::get_by_id or self::get_key
     * method.
     *
     * @param $class_name string The array key in the self::$component_list
     * @param $id string string The array key in the self::$component_list[$class_name]
     * @param $params_array array The value set for the self::$component_list[$class_name][$id]
     * @throws ErrorException The exception thrown if the self::$component_list[$class_name][$id] is already set
     */
    protected static function add_component($class_name, $id, $params_array) {
        if (!isset(self::$components_list[$id])) {

            $params_array[self::TYPE] = $class_name;
	        self::$components_list[$id] = $params_array;

        } else {
            td_util::error(__FILE__, "td_api_base: A component with the ID: $id it's already registered in td_api_base", self::$components_list[$id]);
        }
    }



    /**
     * This method gets the value set for ($class_name) in the main settings array (self::$component_list)
     * This method does not set the self::USED_ON_PAGE flag, as self::get_by_id or self::get_key method does
     *
     * Important! As the flag self::USED_ON_PAGE is not marked, the 'file' parameter is removed to ensure that nobody can use (require) the component
     *
     * @param $class_name string The array key in the self::$component_list
     * @return mixed The value of the self::$component_list[$class_name]
     */
    static function get_all_components_metadata($class_name) {
        $final_array = array();

        foreach (self::$components_list as $component_key => $component_value) {
            if (isset($component_value[self::TYPE])
                and $component_value[self::TYPE] == $class_name) {

	            if (isset($component_value['file'])) {
		            unset($component_value['file']);
	            }

                $final_array[$component_key] = $component_value;
            }
        }
        return $final_array;
    }



    /**
     * returns the default component key value for a particular class. As of now, the default component is the first one that was added
     * we usually use this value when there is no setting in the database
     * Note: it marks the component as used on page
     *
     * @param $class_name
     * @param $key
     * @return mixed
     * @throws ErrorException
     */
    protected static function get_default_component_key($class_name, $key) {
	    foreach (self::$components_list as $component_id => $component_value) {

		    if (isset($component_value[self::TYPE])
		        and $component_value[self::TYPE] == $class_name) {

			    self::mark_used_on_page($component_id);

			    if ($key == 'file') {
				    self::locate_the_file($component_id);
			    }
			    return $component_value[$key];
		    }
	    }
	    td_util::error(__FILE__, "td_api_base::get_default_component_key : no component of type $class_name . Wp booster tried to get
        the default component (the first registered component) but there are no components registered.");
    }



	/**
	 * - returns the id of the default component for a particular class.
	 *
	 * @param $class_name - the class name of the component @see self::$components_list
	 *
	 * @return int|string - the id of the component @see self::$components_list
	 */
    protected static function get_default_component_id($class_name) {
        foreach (self::$components_list as $component_id => $component_value) {

            if (isset($component_value[self::TYPE])
                and $component_value[self::TYPE] == $class_name) {

                self::mark_used_on_page($component_id);
                return $component_id;
            }
        }
        td_util::error(__FILE__, "td_api_base::get_default_component_id  : no component of type $class_name . Wp booster tried to get
        the default component (the first registered component) but there are no components registered.");
    }



    /**
     * This method gets the value set for ($class_name, $id) in the main settings array (self::$component_list)
     * The self::USED_ON_PAGE flag is set accordingly, as updating and deleting operations using the same ($class_name, $id, $key) key
     * know about it and do not fulfill operations.
     * Updating or deleting must be done prior of this method or self::get_key method usage.
     *
     * @param $class_name string The array key in the self::$component_list
     * @param $id string string The array key in the self::$component_list[$class_name]
     * @return mixed The value of the self::$component_list[$class_name][$id]
     */
    static function get_by_id($id) {
        self::mark_used_on_page($id);
	    self::locate_the_file($id);
        return self::$components_list[$id];
    }



    /**
     * This method gets the value set for the ($class_name, $id, $key) key in the main array settings (self::$component_list)
     * The self::USED_ON_PAGE flag is set accordingly, as updating and deleting operations using the same ($class_name, $id, $key) key
     * know about it and do not fulfill operations.
     * Updating or deleting must be done prior of this method or self::get_key method usage.
     *
     * @param $class_name string The array key in the self::$component_list
     * @param $id string The array key in the self::$component_list[$class_name]
     * @param $key string The array key in the self::$component_list[$class_name][$id]
     * @return mixed mixed The value of the self::$component_list[$class_name][$id][$key]
     * @throws ErrorException The error exception thrown by check_used_on_page method call
     */
    static function get_key($id, $key) {
        self::mark_used_on_page($id);

	    if ($key == 'file') {
		    self::locate_the_file($id);
	    }

	    return self::$components_list[$id][$key];
    }



    /**
     * This method update the value for ($class_name, $id) in the main array settings (self::$component_list)
     * Updating and deleting a key value in the main settings array ensures that the value of the key is not already loaded by the theme.
     * Loaded by the theme means that is's used to set or to build some components.
     * So, the $id and the $key parameter must no be used previously by self::get_by_id or by self::get_key
     * method, otherwise it means that the settings are already loaded to build a component, and an error exception is thrown
     * informing the end user about it.
     *
     * @param $class_name string The array key in the self::$component_list
     * @param $id string The array key in the self::$component_list[$class_name]
     * @param $params_array array The array value set for the self::$component_list[$class_name][$id]
     * @throws ErrorException The error exception thrown by check_used_on_page method call
     */
    static function update_component($class_name, $id, $params_array) {
        self::check_used_on_page($id, 'update');
	    $params_array[self::TYPE] = $class_name;
        self::$components_list[$id] = $params_array;
    }



    /**
     * This method updates the value for the ($class_name, $id, $key) key in the main settings array (self::$component_list).
     * Updating and deleting a key value in the main settings array ensures that the value of the key is not already loaded by the theme.
     * Loaded by the theme means that is's used to set or to build some components.
     * So, the $id and the $key parameter must no be used previously by self::get_by_id or by self::get_key
     * method, otherwise it means that the settings are already loaded to build a component, and an error exception is thrown
     * informing the end user about it.
     *
     * @param $class_name string The array key in self::$component_list
     * @param $id string The array key in the self::$component_list[$class_name]
     * @param $key string The array key in the self::$component_list[$class_name][$id]
     * @param $value mixed The value set for the specified $key
     * @throws ErrorException The error exception thrown by check_used_on_page method call
     */
    static function update_key($id, $key, $value) {
        self::check_used_on_page($id, 'update_key');
        self::$components_list[$id][$key] = $value;
    }



    /**
     * This method unset value for the ($class_name, $id) key in the main settings array (self::$component_list).
     * Updating and deleting a key value in the main settings array ensures that the value of the key is not already loaded by the theme.
     * Loaded by the theme means that is's used to set or to build some components.
     * So, the $id and the $key parameter must no be used previously by self::get_by_id or by self::get_key
     * method, otherwise it means that the settings are already loaded to build a component, and an error exception is thrown
     * informing the end user about it.
     *
     * @param $class_name string The array key in self::$component_list
     * @param $id string The array key in the self::$component_list[$class_name]
     * @throws ErrorException The error exception thrown by check_used_on_page method call
     */
    static function delete($id) {
        self::check_used_on_page($id, 'delete');
        unset(self::$components_list[$id]);
    }



    /**
     * This is an internal function used just for debugging
     * @internal
     * @return array with all theme settings
     */
    static function _debug_get_components_list() {
        return self::$components_list;
    }




    /**
     * returns only the used on page component - useful for debug
     * @return array
     */
    static function _debug_show_autoloaded_components() {
        $buffy_array = array();
        foreach (self::$components_list as $component_id => $component) {

            if (isset($component[self::CLASS_AUTOLOADED]) and $component[self::CLASS_AUTOLOADED] === true) {
                $buffy_array [$component_id]= $component;
            }
        }


        ob_start();
        ?>

        <div style="pointer-events:none; z-index: 9999; width: 300px; position: absolute; left:0; top:50px; background-color: rgba(232, 232, 232, 0.35); font-size:12px; font-family: 'Lucida Sans Typewriter', 'Lucida Console', monaco, 'Bitstream Vera Sans Mono', monospace;; padding: 10px;">
            <?php
            foreach ($buffy_array as $component_id => $component) {
                echo str_pad($component_id, 20) . '<br>';
            }
            ?>
        </div>

        <?php
        echo ob_get_clean();



        return $buffy_array;
    }



    /**
     * sets the component's td_api_base::CLASS_AUTOLOADED key to true at runtime.
     * @param $component_id
     */
    static function _debug_set_class_is_autoloaded($component_id) {
        self::$components_list[$component_id][td_api_base::CLASS_AUTOLOADED] = true;
    }





    /**
     * This method sets the self::USED_ON_PAGE flag for the ($class_name, $id) key.
     * It's used by the get_by_id and get_key methods to mark settings as being loaded on page.
     * The main purpose of using this flag is for debugging the loaded components.
     *
     * @param $class_name string The array key in self::$component_list
     * @param $id string The array key in the self::$component_list[$class_name]
     * @throws ErrorException The error thrown when the ($class_name, id) key is not already set
     */
    private static function mark_used_on_page($id) {
        if (!isset(self::$components_list[$id])) {


            /**
             * @deprecated @todo should be removed in v2  compatiblity for social counter old old
             */

            if (($id == 'td_social_counter' or $id == 'td_block_social_counter')) {
                if (is_user_logged_in()) {
                    td_util::error('', "Please update your [tagDiv social counter] Plugin!");
                }
                return;
            }

            /**
             * show a soft error if
             * - the user is logged in
             * - the user is on the login page / register
             * - the user tries to log in via wp-admin (that is why is_admin() is required)
             */
            td_util::error(__FILE__, "td_api_base::mark_used_on_page : a component with the ID: $id is not set.");
        }
        self::$components_list[$id][self::USED_ON_PAGE] = true;
    }


	/**
	 * - it replaces the theme path with the child path, if the file api registered exists in the child theme
	 * - it tries to find the file in the child theme and if it's found, the 'located_in_child' is set
	 * - the check is done only when the child theme is activated and the 'located_in_child' hasn't set yet
	 * - the check is done only for the theme registered paths (those having TEMPLATEPATH), letting the plugins to register themselves paths
	 *
	 * @param string $id - the id of the component
	 * @param string $component - the component value
	 */
	private static function locate_the_file($id = '') {
		if (!is_child_theme()) {
			return;
		}

		$the_component = null;

		if (!empty($id)) {
			$the_component = &self::$components_list[$id];
		}

		if (($the_component != null)
		    and (stripos($the_component['file'], TEMPLATEPATH) == 0)
		    and !empty($the_component['file'])
            and !isset($the_component['located_in_child'])) {

			$child_path = STYLESHEETPATH . str_replace(TEMPLATEPATH, '', $the_component['file']);

			if (file_exists($child_path)) {
				$the_component['file'] = $child_path;
			}
			$the_component['located_in_child'] = true;
		}
	}




    /**
     * This method check the self::USED_ON_PAGE flag for the ($class_name, $id) key and it throws an exception
     * if it's already set, that means the settings are already used to build a component in the user interface.
     *
     * @param $id string The array key in the self::$component_list[$class_name]
     * @param $requested_operation string (delete|update|update_key)
     * @internal param string $class_name The array key in self::$component_list
     */
    private static function check_used_on_page($id, $requested_operation) {
        if (array_key_exists(self::USED_ON_PAGE, self::$components_list[$id])) {
            td_util::error(__FILE__, "td_api_base::check_used_on_page: You requested a $requested_operation for ID: $id BUT it's already used on page. This usually means that you are using a wrong hook - you are trying to modify the component after it already rendered / was used.", self::$components_list[$id]);
        }
    }

}
/**
 * Created by ra on 2/13/2015.
 */



/**
 * The theme's block api, usable via the td_global_after hook
 * Class td_api_block static block api
 */
class td_api_block extends td_api_base {

    /**
     * This method to register a new block
     *
     * @param $id string The block id. It must be unique
     * @param $params_array array The block_parameter array
     *
     *      $params_array = array( - A wp_map array @link https://wpbakery.atlassian.net/wiki/pages/viewpage.action?pageId=524332
     *          'map_in_visual_composer' => ,
     *          'file' => '',                   - Where we can find the shortcode class
     *          'name' => '',                   - string Name of your shortcode for human reading inside element list
     *          'base' => '',                   - string Shortcode tag. For [my_shortcode] shortcode base is my_shortcode
     *          'class' => '',                  - string CSS class which will be added to the shortcode's content element in the page edit screen in Visual Composer backend edit mode
     *          'controls' => '',               - string ?? no used?
     *          'category' => '',               - string Category which best suites to describe functionality of this shortcode. Default categories: Content, Social, Structure. You can add your own category, simply enter new category title here
     *          'icon' => '',                   - URL or CSS class with icon image
     *          'params' => array ()            - array List of shortcode attributes. Array which holds your shortcode params, these params will be editable in shortcode settings page
     *      )
     *
     * @throws ErrorException new exception, fatal error if the $id already exists
     */
    static function add($block_id, $params_array = '') {
        parent::add_component(__CLASS__, $block_id, $params_array);
    }


	static function update($block_id, $params_array = '') {
		parent::update_component(__CLASS__, $block_id, $params_array);
	}


    /**
     * This method gets the value for the ('td_api_block') key in the main settings array of the theme.
     *
     * @return mixed array The value set for the 'td_api_block' in the main settings array of the theme
     */
    static function get_all() {
        return parent::get_all_components_metadata(__CLASS__);
    }
}


/**
 * Created by ra on 2/13/2015.
 */


/**
 * The theme's block template api, usable via the td_global_after hook
 * Class td_api_block static block api
 */
class td_api_block_template extends td_api_base{

    /**
     * This method to register a new block
     *
     * @param $id string The block template id. It must be unique
     * @param $params_array array The block template array
     *
     *      $params_array = array(
     *          'file' => '',           - Where we can find the shortcode class
     *      )
     *
     * @throws ErrorException new exception, fatal error if the $id already exists
     */
    static function add($id, $params_array) {
        parent::add_component(__CLASS__, $id, $params_array);
    }


	static function update($id, $params_array) {
		parent::update_component(__CLASS__, $id, $params_array);
	}


    static function get_all() {
        return parent::get_all_components_metadata(__CLASS__);
    }
}


/**
 * Created by ra on 2/13/2015.
 */


/**
 * The theme's category template api, usable via the td_global_after hook
 * Class td_api_category_template
 */
class td_api_category_template extends td_api_base {

    /**
     * This method to register a new category template
     *
     * @param $id string The category template id. It must be unique
     * @param $params_array array The category template array
     *
     *      $params_array = array(
     *          'file' => '',           - Where we can find the file
     *      )
     *
     * @throws ErrorException new exception, fatal error if the $id already exists
     */
    static function add($id, $params_array) {
        parent::add_component(__CLASS__, $id, $params_array);
    }


	static function update($id, $params_array) {
		parent::update_component(__CLASS__, $id, $params_array);
	}


    static function get_all() {
        return parent::get_all_components_metadata(__CLASS__);
    }



    static function _helper_get_active_id() {

        $template_id = td_util::get_category_option(td_global::$current_category_obj->cat_ID, 'tdc_category_template');  // read the category setting

        if (empty($template_id)) { // if no category setting, read the global template setting
            $template_id = td_util::get_option('tds_category_template');
        }

        if (empty($template_id)) { // nothing is set, check the default value
            $template_id = parent::get_default_component_id(__CLASS__);
        }

        return $template_id;
    }



    static function render_category_template_by_id($template_id) {
        if (class_exists($template_id)) {
            /** @var $td_category_template td_category_template */
            $td_category_template = new $template_id();
            $td_category_template->render();
        } else {
            td_util::error(__FILE__, "The category template $template_id doesn't exist. Did you disable a tagDiv plugin?");
        }
    }

    /**
     * get the category template, this function has to look at the global theme setting and at the category setting
     */
    static function _helper_show_category_template() {
        $template_id = self::_helper_get_active_id();
        self::render_category_template_by_id($template_id);
    }






    static function _helper_to_panel_values($view_name = 'get_all') {
        $buffy_array = array();


        switch ($view_name) {
            case 'default+get_all':

                //add default style
                $buffy_array[] = array(
                    'text' => 'Default',
                    'title' => '',
                    'val' => '',
                    'img' => get_template_directory_uri() . '/includes/wp_booster/wp-admin/images/panel/module-default.png'
                );

                // add the rest
                foreach (self::get_all() as $id => $config) {
                    $buffy_array[] = array(
                        'text' => $config['text'],
                        'title' => '',
                        'val' => $id,
                        'img' => $config['img']
                    );
                }
                break;

            case 'get_all':

                //get all the top post styles, the first one is with an empty value
                foreach (self::get_all() as $id => $config) {
                    $buffy_array[] = array(
                        'text' => $config['text'],
                        'title' => '',
                        'val' => $id,
                        'img' => $config['img']
                    );
                }

                // the first template is the default one, ex: it has no value in the database
                $buffy_array[0]['val'] = '';
                break;
        }



        return $buffy_array;
    }
}




/**
 * Created by ra on 2/13/2015.
 */



/**
 * The theme's category toop loop style api, usable via the td_global_after hook
 * Class td_api_category_top_posts_style
 */
class td_api_category_top_posts_style extends td_api_base {

    /**
     * This method to register a new category template
     *
     * @param $id string The category template id. It must be unique
     * @param $params_array array The category template array
     *
     *      $params_array = array(
     *          'file' => '',           - Where we can find the file
     *      )
     *
     * @throws ErrorException new exception, fatal error if the $id already exists
     */
    static function add($id, $params_array) {
        parent::add_component(__CLASS__, $id, $params_array);
    }


	static function update($id, $params_array) {
		parent::update_component(__CLASS__, $id, $params_array);
	}


    static function get_all() {
        return parent::get_all_components_metadata(__CLASS__);
    }



    static function _helper_get_active_id() {
        $template_id = td_util::get_category_option(td_global::$current_category_obj->cat_ID, 'tdc_category_top_posts_style'); // first check the category setting

        if (empty($template_id)) { // now check the global setting - we have "default" selected on the category, the theme now looks for the global setting
            $template_id = td_util::get_option('tds_category_top_posts_style');
        }

        if (empty($template_id)) { // nothing is set, check the default value
            $template_id = parent::get_default_component_id(__CLASS__);
        }

        return $template_id;
    }


    /**
     * get the category template, this function has to look at the global theme setting and at the category setting
     */
    static function _helper_show_category_top_posts_style() {
        $template_id = self::_helper_get_active_id();

        if (class_exists($template_id)) {

            /** @var $td_category_template td_category_top_posts_style */
            $td_category_template = new $template_id();
            $td_category_template->show_top_posts();
        } else {
            td_util::error(__FILE__, "The category template $template_id doesn't exist. Did you disable a tagDiv plugin?");
        }

    }


    static function _helper_get_posts_shown_in_the_loop() {
        $template_id = self::_helper_get_active_id(); //@todo we may need a 'better' error checking here
        return parent::get_key($template_id, 'posts_shown_in_the_loop');
    }



    static function _helper_to_panel_values($view_name = 'get_all') {
        $buffy_array = array();

        switch ($view_name) {
            case 'default+get_all':

                //add default style
                $buffy_array[] = array(
                    'text' => 'Default',
                    'title' => '',
                    'val' => '',
                    'img' => get_template_directory_uri() . '/includes/wp_booster/wp-admin/images/panel/module-default.png'
                );

                // add the rest
                foreach (self::get_all() as $id => $config) {
                    $buffy_array[] = array(
                        'text' => $config['text'],
                        'title' => '',
                        'val' => $id,
                        'img' => $config['img']
                    );
                }
                break;

            case 'get_all':

                //get all the top post styles, the first one is with an empty value
                foreach (self::get_all() as $id => $config) {
                    $buffy_array[] = array(
                        'text' => $config['text'],
                        'title' => '',
                        'val' => $id,
                        'img' => $config['img']
                    );
                }

                // the first template is the default one, ex: it has no value in the database
                $buffy_array[0]['val'] = '';
                break;
        }

        return $buffy_array;
    }
}







/**
 * Created by ra on 2/13/2015.
 */


class td_api_footer_template extends td_api_base {
    static function add($template_id, $params_array = '') {
        parent::add_component(__CLASS__, $template_id, $params_array);
    }

	static function update($template_id, $params_array = '') {
		parent::update_component(__CLASS__, $template_id, $params_array);
	}

    static function get_all() {
        return parent::get_all_components_metadata(__CLASS__);
    }

    static function _helper_show_footer() {

        $template_path = '';

        // find the current active template's id
        $template_id = self::_helper_get_active_id();
        try {
            $template_path = self::get_key($template_id, 'file');
        } catch (ErrorException $ex) {
            td_util::error(__FILE__, "td_api_footer_template::_helper_show_footer : $template_id isn't set. Did you disable a tagDiv plugin?");  //does not stop execution
        }

        // load the template
        if (!empty($template_path) and file_exists($template_path)) {
            load_template($template_path);
        } else {
            td_util::error(__FILE__, "The path $template_path of the template id: $template_id not found.");   //shoud be fatal?
        }

    }

    static function _helper_to_panel_values() {
        // add the rest
        foreach (self::get_all() as $id => $config) {
            $buffy_array[] = array(
                'text' => $config['text'],
                'title' => '',
                'val' => $id,
                'img' => $config['img']
            );
        }

        // the first template is the default one, ex: it has no value in the database
        $buffy_array[0]['val'] = '';

        return $buffy_array;
    }




    private static function _helper_get_active_id() {
        $template_id = td_util::get_option('tds_footer_template');
        if (empty($template_id)) { // nothing is set, check the default value
            $template_id = parent::get_default_component_id(__CLASS__);
        }

        return $template_id;
    }

}
/**
 * Created by ra on 2/13/2015.
 */

class td_api_header_style extends td_api_base {

    /**
     * This method to register a new header style
     *
     * @param $id string The header style id. It must be unique
     * @param $params_array array The heade style parameter array
     *
     *      $params_array = array (
     *          'text' => '',   - [string] the text used inside
     *      )
     *
     * @throws ErrorException new exception, fatal error if the $id already exists
     */
    static function add($thumb_id, $params_array = '') {
        parent::add_component(__CLASS__, $thumb_id, $params_array);
    }

	static function update($thumb_id, $params_array = '') {
		parent::update_component(__CLASS__, $thumb_id, $params_array);
	}

    static function get_all() {
        return parent::get_all_components_metadata(__CLASS__);
    }


    /**
     * get all the header styles as a array for the panel ui controll. It also adds the default value
     *
     * @internal
     * @return array
     */
    static function _helper_generate_tds_header_style() {
        $buffy_array = array();


        //add each value
        foreach (self::get_all() as $id => $config) {
            $buffy_array[] = array(
                'text' => $config['text'],
                'val' => $id,
            );
        }

        // the first template is the default one, ex: it has no value in the database
        $buffy_array[0]['val'] = '';
        return $buffy_array;
    }


    /**
     * helper function to show the header of the theme.
     *
     * @internal
     */
    static function _helper_show_header() {
        $tds_header_style = self::_helper_get_active_id();
        $template_path = '';

        // look for the user selected template
        try {
            $template_path = self::get_key($tds_header_style, 'file');
        } catch (ErrorException $ex) {
            td_util::error(__FILE__, "The header style: $tds_header_style isn't set. Did you disable a tagDiv plugin?");  //does not stop execution
        }


        // load the template
        if (!empty($template_path) and file_exists($template_path)) {
            load_template($template_path);
        } else {
            td_util::error(__FILE__, "The path $template_path of the $tds_header_style header style not found. Did you disable a tagDiv plugin?");
        }
    }


    private static function _helper_get_active_id() {
        $tds_header_style = td_util::get_option('tds_header_style');

        if (empty($tds_header_style)) { // nothing is set, check the default value
            $tds_header_style = parent::get_default_component_id(__CLASS__);
        }

        return $tds_header_style;
    }
}


/**
 * Created by ra on 2/13/2015.
 */



/**
 * The theme's module api, usable via the td_global_after hook
 * Class td_api_module static module api
 */
class td_api_module extends td_api_base {

    /**
     * This method to register a new module
     *
     * @param $id string The module id. It must be unique
     * @param $params_array array The module_parameter array
     *
     *      $params_array = array (
     *          'file' => '',                               - [string] the path to the module class
     *          'text' => '',                               - [string] module name text used in the theme panel
     *          'img' => '',                                - [string] the path to the image icon
     *          'used_on_blocks' => array(),                - [array of strings] block names where this module is used or leave blank if it's used internally (ex. it's not used on any category)
     *          'excerpt_title' => '',                      - [int] leave empty '' if you don't want a setting in the panel -> excerpts for this module
     *          'excerpt_content' => '',                    - [int] leave empty ''  ----||----
     *          'enabled_on_more_articles_box' => ,         - [boolean] show the module in the more articles box in panel -> post settings -> more articles box
     *          'enabled_on_loops' => ,                     - [boolean] show the module in panel on loops
     *          'uses_columns' => ,                         - [boolean] if the module uses columns on the page template + loop (if the modules has columns, enable this)
     *          'category_label' =>                         - [boolean] show the module in panel -> block_settings -> category label ?
     *      )
     *
     * @throws ErrorException new exception, fatal error if the $id already exists
     */
    static function add($module_id, $params_array = '') {
        parent::add_component(__CLASS__, $module_id, $params_array);
    }

	static function update($module_id, $params_array = '') {
		parent::update_component(__CLASS__, $module_id, $params_array);
	}


    /**
     * This method gets the value for the ('td_api_module') key in the main settings array of the theme.
     *
     * @return mixed array The value set for the 'td_api_module' in the main settings array of the theme
     */
    static function get_all() {
        return parent::get_all_components_metadata(__CLASS__);
    }


    /**
     * This method is an internal helper function used to check 'excerpt_title' property of a module
     *
     * @internal
     * @param $module_id string Unique module id
     * @return bool True if the 'excerpt_title' property is set, false otherwise
     */
    static function _check_excerpt_title($module_id) {
        $module_settings = self::get_by_id($module_id);

        if (isset($module_settings) and !empty($module_settings['excerpt_title'])) {
            return true;
        }
        return false;
    }



    /**
     * This method is an internal helper function used to check 'excerpt_content' property of a module
     *
     * @internal
     * @param $module_id string Unique module id
     * @return bool True if the 'excerpt_content' property is set, false otherwise
     */

    static function _check_excerpt_content($module_id) {
        $module_settings = self::get_by_id($module_id);

        if (isset($module_settings) and !empty($module_settings['excerpt_content'])) {
            return true;
        }
        return false;
    }
}

/**
 * Created by ra on 2/13/2015.
 */


class td_api_single_template extends td_api_base {

    /**
     * This method to register a new single template
     *
     * @param $id string The single template id. It must be unique
     * @param $params_array array The single_template_parameter array
     *
     *      $params_array = array (
     *          'file' => '',                               - [string] the path to the template file
     *          'text' => '',                               - [string] name text used in the theme panel
     *          'img' => '',                                - [string] the path to the image icon
     *          'show_featured_image_on_all_pages' => ''    - [boolean]
     *      )
     *
     * @throws ErrorException new exception, fatal error if the $id already exists
     */
    static function add($single_template_id, $params_array = '') {
        parent::add_component(__CLASS__, $single_template_id, $params_array);
    }

	static function update($single_template_id, $params_array = '') {
		parent::update_component(__CLASS__, $single_template_id, $params_array);
	}

    static function get_all() {
        return parent::get_all_components_metadata(__CLASS__);
    }

    /**
     * checks the show_featured_image_on_all_pages for a template
     *
     * @internal
     * @param $single_template_id
     * @return bool true if we have to show the featured image on all pages
     */
    static function _check_show_featured_image_on_all_pages($single_template_id) {
        // on the default template, hide the featured image on page 2
        if (empty($single_template_id)) {  //$single_template_id is empty if we're on the default template
            return false;
        }

        // check the show_featured_image_on_all_pages key of each template
        if (self::get_key($single_template_id, 'show_featured_image_on_all_pages') === false) {
            return false;
        }

        return true;
    }


    /**
     *  returns all the single post templates in a format that is usable for the panel
     *
     *  @internal
     *  @return array
     *
     *      array(
     *          array('text' => '', 'title' => '', 'val' => 'single_template_6', 'img' => get_template_directory_uri() . '/includes/wp_booster/wp-admin/images/post-templates/post-templates-icons-6.png'),
     *      )
     */
    static function _helper_td_global_list_to_panel_values() {
        $buffy_array = array();

        foreach (self::get_all() as $template_value => $template_config) {
	        if ($template_value == 'single_template') {
		        $buffy_array[] = array(
			        'text' => '',
			        'title' => '',
			        'val' => '',
			        'img' => $template_config['img']
		        );
		        continue;
	        }
            $buffy_array[] = array(
                'text' => '',
                'title' => '',
                'val' => $template_value,
                'img' => $template_config['img']
            );
        }

//        // add the default template at the beginning
//        array_unshift (
//            $buffy_array,
//            array(
//                'text' => '',
//                'title' => '',
//                'val' => '',
//                'img' => td_global::$get_template_directory_uri . '/images/panel/single_templates/single_template_default.png'
//            )
//        );
        return $buffy_array;
    }


	static function _helper_td_global_list_to_metaboxes() {
		$buffy_array = array();

		foreach (self::get_all() as $template_value => $template_config) {
			$buffy_array[] = array(
				'text' => '',
				'title' => '',
				'val' => $template_value,
				'img' => $template_config['img']
			);
		}

        // add the default template at the beginning
        array_unshift (
            $buffy_array,
            array(
                'text' => '',
                'title' => '',
                'val' => '',
                'img' => td_global::$get_template_directory_uri . '/images/panel/single_templates/single_template_default.png'
            )
        );
		return $buffy_array;
	}


    /**
     * @deprecated Important! Its functionality was replaced by the booster 'template_include' wordpress hook. It's susceptible to be removed in the next api versions.
     *
     * shows a single template (echos it). NOTE: it also loads the WordPress globals in that template!
     *
     * @internal
     * @param $template_id
     */
    static function _helper_show_single_template($template_id) {
        $template_path = '';

        // try to get the key from the api
        try {
            $template_path = self::get_key($template_id, 'file');
        } catch (ErrorException $ex) {
            td_util::error(__FILE__, "The template $template_id isn't set. Did you disable a tagDiv plugin?"); // this does not stop execution
        }


        // load the template
        if (!empty($template_path) and file_exists($template_path)) {
            load_template($template_path);
        } else {
            td_util::error(__FILE__, "The path $template_path of the $template_id template not found. Did you disable a tagDiv plugin?");  // this does not stop execution
        }


    }
}




/**
 * Created by ra on 2/13/2015.
 */

/**
 * Note: the smart lists are loaded via autoload
 * Class td_api_smart_list
 */
class td_api_smart_list extends td_api_base {

    /**
     * This method to register a new smart list
     *
     * @param $id string The smart list id. It must be unique
     * @param $params_array array The smart_list_parameter array
     *
     *      $params_array = array (
     *          'file' => '',                               - [string] the path to the smart list file
     *          'text' => '',                               - [string] name text used in the theme panel
     *          'img' => '',                                - [string] the path to the image icon
     *      )
     *
     * @throws ErrorException new exception, fatal error if the $id already exists
     */
    static function add($smart_list_id, $params_array = '') {
        parent::add_component(__CLASS__, $smart_list_id, $params_array);
    }

	static function update($smart_list_id, $params_array = '') {
		parent::update_component(__CLASS__, $smart_list_id, $params_array);
	}

    static function get_all() {
        return parent::get_all_components_metadata(__CLASS__);
    }

    /**
     *  returns all the single post templates in a format that is usable for the panel
     *
     *  @internal
     *  @return array
     *
     *      array(
     *          array('text' => '', 'title' => '', 'val' => 'single_template_6', 'img' => get_template_directory_uri() . '/includes/wp_booster/wp-admin/images/post-templates/post-templates-icons-6.png'),
     *      )
     */
    static function _helper_td_smart_list_api_to_panel_values() {
        $buffy_array = array();

        // add the default smart list
        $buffy_array[] =  array(
            'text' => '',
            'title' => '',
            'val' => '',
            'img' => td_global::$get_template_directory_uri . '/images/panel/smart_lists/td_smart_list_default.png'
        );

        foreach (self::get_all() as $template_value => $template_config) {
            $buffy_array[] = array(
                'text' => '',
                'title' => '',
                'val' => $template_value,
                'img' => $template_config['img']
            );
        }



        return $buffy_array;
    }
}


/**
 * Created by ra on 2/13/2015.
 */

class td_api_thumb extends td_api_base {

    /**
     * This method to register a new thumb
     *
     * @param $id string The single template id. It must be unique
     * @param $params_array array The single_template_parameter array
     *
     *      $params_array = array (
     *          'name' => 'td_0x420',                       - [string] the thumb name
     *          'width' => ,                                - [int] the thumb width
     *          'height' => ,                               - [int] the thumb height
     *          'crop' => array('center', 'top'),           - [array of string] what crop to use (center, top, etc)
     *          'post_format_icon_size' => '',              - [string] what play icon to load (small or normal)
     *          'used_on' => array('')                      - [array of string] description where the thumb is used
     *      )
     *
     * @throws ErrorException new exception, fatal error if the $id already exists
     */
    static function add($thumb_id, $params_array = '') {
        parent::add_component(__CLASS__, $thumb_id, $params_array);
    }

	static function update($thumb_id, $params_array = '') {
		parent::update_component(__CLASS__, $thumb_id, $params_array);
	}

    static function get_all() {
        return parent::get_all_components_metadata(__CLASS__);
    }
}




/**
 * Created by ra on 2/13/2015.
 */

class td_api_top_bar_template extends td_api_base {
    static function add($template_id, $params_array = '') {
        parent::add_component(__CLASS__, $template_id, $params_array);
    }

	static function update($template_id, $params_array = '') {
		parent::update_component(__CLASS__, $template_id, $params_array);
	}

    static function get_all() {
        return parent::get_all_components_metadata(__CLASS__);
    }

    static function _helper_show_top_bar() {

        // find the current active template's id
        $template_id = self::_helper_get_active_id();
        try {
            $template_path = self::get_key($template_id, 'file');
        } catch (ErrorException $ex) {
            td_util::error(__FILE__, "td_api_top_bar_template::_helper_show_top_bar : $template_id isn't set. Did you disable a tagDiv plugin?");  //does not stop execution
        }

        // load the template
        if (!empty($template_path) and file_exists($template_path)) {
            load_template($template_path);
        } else {
            td_util::error(__FILE__, "The path $template_path of the template id: $template_id not found.");   //shoud be fatal?
        }

    }

    static function _helper_to_panel_values() {
        // add the rest
        foreach (self::get_all() as $id => $config) {
            $buffy_array[] = array(
                'text' => '',
                'title' => '',
                'val' => $id,
                'img' => $config['img']
            );
        }

        // the first template is the default one, ex: it has no value in the database
        $buffy_array[0]['val'] = '';

        return $buffy_array;
    }






    private static function _helper_get_active_id() {
        $template_id = td_util::get_option('tds_top_bar_template');

        if (empty($template_id)) { // nothing is set, check the default value
            $template_id = parent::get_default_component_id(__CLASS__);
        }

        return $template_id;
    }

}


/**
 * Created by ra on 2/13/2015.
 */



class td_api_tinymce_formats extends td_api_base {

    private static $tiny_mce_format_list = array ();


    static function add($tinymce_format_id, $params_array = '') {
        parent::add_component(__CLASS__, $tinymce_format_id, $params_array);
        /*
        if (empty($params_array['parent_id'])) {
            // root level
            self::$tiny_mce_format_list[$tinymce_format_id] = $params_array;
        } else {

            // first level
            if (isset(self::$tiny_mce_format_list[$params_array['parent_id']])) {
                self::$tiny_mce_format_list[$params_array['parent_id']]['items'][] = $params_array;
            } else {
                echo 'errrrrr';
            }
        }
        */
    }

    static function update($tinymce_format_id, $params_array = '') {
        parent::update_component(__CLASS__, $tinymce_format_id, $params_array);
    }

    static function get_all() {
        return parent::get_all_components_metadata(__CLASS__);
    }


    static function _helper_get_tinymce_format() {
	    self::$tiny_mce_format_list = self::get_all();

        array_walk(self::$tiny_mce_format_list, array('td_api_tinymce_formats', '_connect_to_parent'));

	    td_global::$tiny_mce_style_formats = array_filter(self::$tiny_mce_format_list, array('td_api_tinymce_formats', '_get_root_elements'));
    }


	static function _connect_to_parent($value, $key) {

		if (!empty($value['parent_id'])
		    && isset(self::$tiny_mce_format_list[$value['parent_id']])
		    && isset(self::$tiny_mce_format_list[$key])) {

			self::$tiny_mce_format_list[$value['parent_id']]['items'][] = &self::$tiny_mce_format_list[$key];
		}
	}


	static function _get_root_elements($value) {
		return empty($value['parent_id']);
	}

}





class td_api_autoload extends td_api_base {
    static function add($class_id, $file) {
        $params_array['file'] = $file;
        parent::add_component(__CLASS__, $class_id, $params_array);
    }



    static function get_all() {
        return parent::get_all_components_metadata(__CLASS__);
    }







}

do_action('td_global_after');


require_once('td_global_blocks.php');
require_once('td_menu.php');            //theme menu support
require_once('td_social_icons.php');    // The social icons
require_once('td_review.php');          // Review js buffer class      //@todo de vazut pt autoload
require_once('td_js_buffer.php');       // page generator
require_once('td_unique_posts.php');    //unique posts (uses hooks + do_action('td_wp_boost_new_module'); )
require_once('td_data_source.php');      // data source
require_once('td_page_views.php'); // page views counter
require_once('td_module.php');           // module builder
require_once('td_block.php');            // block builder
require_once('td_cake.php');
require_once('td_widget_builder.php');  // widget builder
require_once('td_first_install.php');  //the code that runs on the first install of the theme
require_once("td_fonts.php"); //fonts support
require_once("td_ajax.php"); // ajax
require_once('td_video_support.php');  // video thumbnail support
require_once('td_video_playlist_support.php'); //video playlist support
require_once('td_css_buffer.php'); // css buffer class
require_once('td_js_generator.php');  // ~ app config ~ css generator
require_once('td_more_article_box.php');  //handles more articles box
require_once('td_block_widget.php');  //used to make widgets from our blocks
// background support - is not autoloaded
require_once('td_background.php');
require_once('td_background_render.php');


// Every class after this (that has td_ in the name) is auto loaded
require_once('td_autoload_classes.php');  //used to autoload classes [modules, blocks]
// add auto loading classes
td_api_autoload::add('td_css_inline', td_global::$get_template_directory . '/includes/wp_booster/td_css_inline.php');
td_api_autoload::add('td_login', td_global::$get_template_directory . '/includes/wp_booster/td_login.php');
td_api_autoload::add('td_category_template', td_global::$get_template_directory . '/includes/wp_booster/td_category_template.php');
td_api_autoload::add('td_category_top_posts_style', td_global::$get_template_directory . '/includes/wp_booster/td_category_top_posts_style.php');
td_api_autoload::add('td_page_generator', td_global::$get_template_directory . '/includes/wp_booster/td_page_generator.php');   //not used on some homepages
td_api_autoload::add('td_block_layout', td_global::$get_template_directory . '/includes/wp_booster/td_block_layout.php');
td_api_autoload::add('td_template_layout', td_global::$get_template_directory . '/includes/wp_booster/td_template_layout.php');
td_api_autoload::add('td_css_compiler', td_global::$get_template_directory . '/includes/wp_booster/td_css_compiler.php');
td_api_autoload::add('td_module_single_base', td_global::$get_template_directory . '/includes/wp_booster/td_module_single_base.php');
td_api_autoload::add('td_smart_list', td_global::$get_template_directory . '/includes/wp_booster/td_smart_list.php');




/*
add_action('wp_footer', 'td_wp_footer_debug');
function td_wp_footer_debug() {
    td_api_base::_debug_show_autoloaded_components();
}
*/



if (TD_DEBUG_IOS_REDIRECT) {
    require_once('td_ios_redirect.php' );
}


do_action('td_wp_booster_loaded'); //used by our plugins



/* ----------------------------------------------------------------------------
 * Add theme support for features
 */
add_theme_support('post-thumbnails');
add_theme_support('post-formats', array('video'));
add_theme_support('automatic-feed-links');
add_theme_support('html5', array('comment-list', 'comment-form', 'search-form', 'gallery', 'caption'));
add_theme_support('woocommerce');




/* ----------------------------------------------------------------------------
 * front end js composer file @todo - check it why is this way
 */
add_action('wp_enqueue_scripts',  'load_js_composer_front', 1000);
function load_js_composer_front() {
    wp_enqueue_style('js_composer_front');
}




/* ----------------------------------------------------------------------------
 * front end css files
 */
add_action('wp_enqueue_scripts', 'load_front_css', 1001);   // 1001 priority because visual composer uses 1000
function load_front_css() {
    if (TD_DEBUG_USE_LESS) {
        wp_enqueue_style('td-theme', td_global::$get_template_directory_uri . '/td_less_style.css.php',  '', TD_THEME_VERSION, 'all' );
    } else {
        wp_enqueue_style('td-theme', get_stylesheet_uri(), '', TD_THEME_VERSION, 'all' );
    }
}

/** ---------------------------------------------------------------------------
 * front end user compiled css @see  td_css_generator.php
 */
function td_include_user_compiled_css() {
    if (!is_admin()) {
        td_css_buffer::add_to_header(td_util::get_option('tds_user_compile_css'));
    }
}
add_action('wp_head', 'td_include_user_compiled_css', 10);


/* ----------------------------------------------------------------------------
 * CSS fonts / google fonts in front end
 */
add_action('wp_enqueue_scripts', 'td_load_css_fonts');
function td_load_css_fonts() {

    $td_user_fonts_list = array();
    $td_user_fonts_db = td_util::get_option('td_fonts');

    if(!empty($td_user_fonts_db)) {
        foreach($td_user_fonts_db as $td_font_setting_key => $td_font_setting_val) {
            if(!empty($td_font_setting_val) and !empty($td_font_setting_val['font_family'])) {
                $td_user_fonts_list[] = $td_font_setting_val['font_family'];
            }
        }
    }


    foreach (td_global::$default_google_fonts_list as $default_font_id => $default_font_params) {
        if(!in_array('g_' . $default_font_id, $td_user_fonts_list)) {
            wp_enqueue_style($default_font_params['css_style_id'], $default_font_params['url'] . td_fonts::get_google_fonts_subset_query()); //used on menus/small text
        }
    }

    /*
     * add the google link for fonts used by user
     *
     * td_fonts_css_files : holds the link to fonts.googleapis.com in the database
     *
     * this section will appear in the header of the source of the page
     */
    $td_fonts_css_files = td_util::get_option('td_fonts_css_files');
    if(!empty($td_fonts_css_files)) {
        wp_enqueue_style('google-fonts-style', td_global::$http_or_https . $td_fonts_css_files);
    }
}




/* ----------------------------------------------------------------------------
 * front end javascript files
 */
add_action('wp_enqueue_scripts', 'load_front_js');
function load_front_js() {
    $td_deploy_mode = TD_DEPLOY_MODE;

    //switch the deploy mode to demo if we have tagDiv speed booster
    if (defined('TD_SPEED_BOOSTER')) {
        $td_deploy_mode = 'demo';
    }

    switch ($td_deploy_mode) {
        default: //deploy
            wp_enqueue_script('td-site', td_global::$get_template_directory_uri . '/js/tagdiv_theme.js', array('jquery'), TD_THEME_VERSION, true);
            break;

        case 'demo':
            wp_enqueue_script('td-site-min', td_global::$get_template_directory_uri . '/js/tagdiv_theme.min.js', array('jquery'), TD_THEME_VERSION, true);
            break;

        case 'dev':
            // dev version - load each file separately
            $last_js_file_id = '';
            foreach (td_global::$js_files as $js_file_id => $js_file) {
                if ($last_js_file_id == '') {
                    wp_enqueue_script($js_file_id, td_global::$get_template_directory_uri . $js_file, array('jquery'), TD_THEME_VERSION, true); //first, load it with jQuery dependency
                } else {
                    wp_enqueue_script($js_file_id, td_global::$get_template_directory_uri . $js_file, array($last_js_file_id), TD_THEME_VERSION, true);  //not first - load with the last file dependency
                }
                $last_js_file_id = $js_file_id;
            }
            break;

    }

    //add the comments reply to script on single pages
    if (is_singular()) {
        wp_enqueue_script('comment-reply');
    }
}




/* ----------------------------------------------------------------------------
 * css for wp-admin / backend
 */
add_action('admin_enqueue_scripts', 'load_wp_admin_css');
function load_wp_admin_css() {
    //load the panel font in wp-admin
    $td_protocol = is_ssl() ? 'https' : 'http';
    wp_enqueue_style('google-font-ubuntu', $td_protocol . '://fonts.googleapis.com/css?family=Ubuntu:300,400,500,700,300italic,400italic,500italic,700italic&amp;subset=latin,cyrillic-ext,greek-ext,greek,latin-ext,cyrillic'); //used on content
    wp_enqueue_style('td-wp-admin-td-panel-2', td_global::$get_template_directory_uri . '/includes/wp_booster/wp-admin/css/wp-admin.css', false, TD_THEME_VERSION, 'all' );

    //load the colorpicker
    wp_enqueue_style( 'wp-color-picker' );
}




/* ----------------------------------------------------------------------------
 * js for wp-admin / backend   admin js
 */
add_action('admin_enqueue_scripts', 'load_wp_admin_js');
function load_wp_admin_js() {

// dev version - load each file separately
    $last_js_file_id = '';
    foreach (td_global::$js_files_for_wp_admin as $js_file_id => $js_file) {
        if ($last_js_file_id == '') {
            wp_enqueue_script($js_file_id, td_global::$get_template_directory_uri . $js_file, array('jquery', 'wp-color-picker'), TD_THEME_VERSION, false); //first, load it with jQuery dependency
        } else {
            wp_enqueue_script($js_file_id, td_global::$get_template_directory_uri . $js_file, array($last_js_file_id), TD_THEME_VERSION, false);  //not first - load with the last file dependency
        }
        $last_js_file_id = $js_file_id;
    }

    wp_enqueue_script('thickbox');
    add_thickbox();
}




/* ----------------------------------------------------------------------------
 * Prepare head related links
 */
add_action('wp_head', 'hook_wp_head', 1);  //hook on wp_head -> 1 priority, we are first
function hook_wp_head() {
    if (is_single()) {
        global $post;

        // facebook sharing fix for videos, we add the custom meta data
        if (has_post_thumbnail($post->ID)) {
            $td_image = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'full' );
            if (!empty($td_image[0])) {
                echo '<meta property="og:image" content="' .  $td_image[0] . '" />';
            }
        }

        // show author meta tag on sigle pages
        $td_post_author = get_the_author_meta('display_name', $post->post_author);
        if ($td_post_author) {
            echo '<meta name="author" content="'.$td_post_author.'">'."\n";
        }
    }

    // fav icon support
    $tds_favicon_upload = td_util::get_option('tds_favicon_upload');
    if (!empty($tds_favicon_upload)) {
        echo '<link rel="icon" type="image/png" href="' . $tds_favicon_upload . '">';
    }


    // ios bookmark icon support
    $tds_ios_76 = td_util::get_option('tds_ios_icon_76');
    $tds_ios_120 = td_util::get_option('tds_ios_icon_120');
    $tds_ios_152 = td_util::get_option('tds_ios_icon_152');
    $tds_ios_114 = td_util::get_option('tds_ios_icon_114');
    $tds_ios_144 = td_util::get_option('tds_ios_icon_144');

    if(!empty($tds_ios_76)) {
        echo '<link rel="apple-touch-icon-precomposed" sizes="76x76" href="' . $tds_ios_76 . '"/>';
    }

    if(!empty($tds_ios_120)) {
        echo '<link rel="apple-touch-icon-precomposed" sizes="120x120" href="' . $tds_ios_120 . '"/>';
    }

    if(!empty($tds_ios_152)) {
        echo '<link rel="apple-touch-icon-precomposed" sizes="152x152" href="' . $tds_ios_152 . '"/>';
    }

    if(!empty($tds_ios_114)) {
        echo '<link rel="apple-touch-icon-precomposed" sizes="114x114" href="' . $tds_ios_114 . '"/>';
    }

    if(!empty($tds_ios_144)) {
        echo '<link rel="apple-touch-icon-precomposed" sizes="144x144" href="' . $tds_ios_144 . '"/>';
    }



	// js variable td_viewport_interval_list added to the window object
	td_js_buffer::add_variable('td_viewport_interval_list', td_global::$td_viewport_intervals);



	// @todo aici se va schimba setarea, iar userii isi pierd setarea existenta
	// lazy loading images - animation effect
	//$tds_lazy_loading_image = td_util::get_option('tds_lazy_loading_image');
	$tds_animation_stack = td_util::get_option('tds_animation_stack');

	// the body css supplementary classes and the global js animation effects variables are set only if the option 'tds_animation_stack' is set
	if (empty($tds_animation_stack)) {

		// js variable td_animation_stack_effect added to the window object
		$td_animation_stack_effect_type = 'type0';
		if (!empty(td_global::$td_options['tds_animation_stack_effect'])) {
			$td_animation_stack_effect_type = td_global::$td_options['tds_animation_stack_effect'];
		}

		td_js_buffer::add_variable('td_animation_stack_effect', $td_animation_stack_effect_type);
		td_js_buffer::add_variable('tds_animation_stack', true);

		foreach (td_global::$td_animation_stack_effects as $td_animation_stack_effect) {
			if ((($td_animation_stack_effect['val'] == '') and ($td_animation_stack_effect_type == 'type0')) ||
			    ($td_animation_stack_effect['val'] == $td_animation_stack_effect_type)) {

				td_js_buffer::add_variable('td_animation_stack_specific_selectors', $td_animation_stack_effect['specific_selectors']);
				td_js_buffer::add_variable('td_animation_stack_general_selectors', $td_animation_stack_effect['general_selectors']);

				break;
			}
		}
        add_filter('body_class','td_hook_add_custom_body_class');
	}

	// if speed booster is active, the body animation classes are added into the head style, otherwise they are applied too late (the same is td-backstretch)
//	if (defined('TD_SPEED_BOOSTER')) {
//		echo '<style type="text/css">
//			body.td-animation-stack-type0 .td-animation-stack .entry-thumb,
//			body.td-animation-stack-type0 .post img {
//			  opacity: 0;
//			}
//
//			body.td-animation-stack-type1 .td-animation-stack .entry-thumb,
//			body.td-animation-stack-type1 .post .entry-thumb,
//			body.td-animation-stack-type1 .post img[class*="wp-image-"],
//			body.td-animation-stack-type1 .post a.td-sml-link-to-image > img {
//			  opacity: 0;
//			  transform: scale(0.95);
//			}
//
//			body.td-animation-stack-type2 .td-animation-stack .entry-thumb,
//			body.td-animation-stack-type2 .post .entry-thumb,
//			body.td-animation-stack-type2 .post img[class*="wp-image-"],
//			body.td-animation-stack-type2 .post a.td-sml-link-to-image > img {
//			  opacity: 0;
//			  .transform(translate(0px,10px));
//			}
//
//			.td-backstretch {
//			    display: block;
//			    max-width: none;
//			    opacity: 0;
//			    transition: opacity 2s ease 0s;
//			}</style>';
//	}



	$tds_general_modal_image = td_util::get_option('tds_general_modal_image');

	if (!empty($tds_general_modal_image)) {
		td_js_buffer::add_variable('tds_general_modal_image', $tds_general_modal_image);
	}
}


/** ----------------------------------------------------------------------------
 * The function hook to alter body css classes.
 * It applies the necessary animation images effect to body @see animation-stack.less
 *
 * @param $classes
 *
 * @return array
 */
function td_hook_add_custom_body_class($classes) {

	if (empty(td_global::$td_options['tds_animation_stack'])) {

		$td_animation_stack_effect_type = 'type0';
		if (!empty(td_global::$td_options['tds_animation_stack_effect'])) {
			$td_animation_stack_effect_type = td_global::$td_options['tds_animation_stack_effect'];
		}

		$classes[] = 'td-animation-stack-' . $td_animation_stack_effect_type;
	}
	return $classes;
}



/* ----------------------------------------------------------------------------
 * localization
 */
add_action('after_setup_theme', 'td_load_text_domains');
function td_load_text_domains() {
    load_theme_textdomain(TD_THEME_NAME, get_template_directory() . '/translation');

    // theme specific config values
    require_once('td_translate.php');

}




/* ----------------------------------------------------------------------------
    Custom <title> wp_title - seo
 */
add_filter( 'wp_title', 'td_wp_title', 10, 2 );
function td_wp_title( $title, $sep ) {
    global $paged, $page;

    if ( is_feed() )
        return $title;

    // Add the site name.
    $title .= get_bloginfo( 'name' );

    // Add the site description for the home/front page.
    $site_description = get_bloginfo( 'description', 'display' );
    if ( $site_description && ( is_home() || is_front_page() ) )
        $title = "$title $sep $site_description";

    // Add a page number if necessary.
    if ( $paged >= 2 || $page >= 2 )
        $title = "$title $sep " . __td('Page', TD_THEME_NAME) . ' ' .  max( $paged, $page );

    return $title;
}

/**
 * - filter 'wpseo_title' is used by WordPress SEO plugin and, by default, it returns a seo title that hasn't the page number inside of it,
 * when it's used on td pages [those who have a custom pagination]. At that seo title is added the page info, and just for pages greater than 1
 */
add_filter('wpseo_title', 'td_wpseo_title', 10, 1);
function td_wpseo_title($seo_title) {

	// outside the loop, it's reliable to check the page template
	if (!in_the_loop() && is_page_template('page-pagebuilder-latest.php')) {

		$td_page = (get_query_var('page')) ? get_query_var('page') : 1; //rewrite the global var
		$td_paged = (get_query_var('paged')) ? get_query_var('paged') : 1; //rewrite the global var

		if ($td_paged > $td_page) {
			$local_paged = $td_paged;
		} else {
			$local_paged = $td_page;
		}

		// the custom title is when the pagination is greater than 1
		if ($local_paged > 1) {
			return $seo_title . ' - ' . __td('Page', TD_THEME_NAME) . ' ' . $local_paged;
		}
	}

	// otherwise, the param $seo_title is returned as it is
	return $seo_title;
}




/**  ----------------------------------------------------------------------------
    archive widget - adds .current class in the archive widget and maybe it's used in other places too!
 */
add_filter('get_archives_link', 'theme_get_archives_link');
function theme_get_archives_link ( $link_html ) {
    global $wp;
    static $current_url;
    if ( empty( $current_url ) ) {
        $current_url = esc_url(add_query_arg( $_SERVER['QUERY_STRING'], '', home_url( $wp->request ) ));
    }
    if ( stristr( $current_url, 'page' ) !== false ) {
        $current_url = substr($current_url, 0, strrpos($current_url, 'page'));
    }
    if ( stristr( $link_html, $current_url ) !== false ) {
        $link_html = preg_replace( '/(<[^\s>]+)/', '\1 class="current"', $link_html, 1 );
    }
    return $link_html;
}




/*  ----------------------------------------------------------------------------
    add span wrap for category number in widget
 */
add_filter('wp_list_categories', 'cat_count_span');
function cat_count_span($links) {
    $links = str_replace('</a> (', '<span class="td-widget-no">', $links);
    $links = str_replace(')', '</span></a>', $links);
    return $links;
}




/*  ----------------------------------------------------------------------------
    remove gallery style css
 */
add_filter( 'use_default_gallery_style', '__return_false' );




/*  ----------------------------------------------------------------------------
    more text
 */
add_filter('excerpt_more', 'new_excerpt_more');
function new_excerpt_more($text){
    return ' ';
}




/*  ----------------------------------------------------------------------------
    editor style
 */
add_action( 'after_setup_theme', 'my_theme_add_editor_styles' );
function my_theme_add_editor_styles() {
    add_editor_style('td_less_editor-style.php');
}




/*  ----------------------------------------------------------------------------
    the bottom code for css
 */
add_action('wp_footer', 'td_bottom_code');
function td_bottom_code() {
    global $post;

    // try to detect speed booster
    $speed_booster = '';
    if (defined('TD_SPEED_BOOSTER')) {
        $speed_booster = 'Speed booster: ' . TD_SPEED_BOOSTER . "\n";
    }

    echo '

    <!--

        Theme: ' . TD_THEME_NAME .' by tagDiv 2015
        Version: ' . TD_THEME_VERSION . ' (rara)
        Deploy mode: ' . TD_DEPLOY_MODE . '
        ' . $speed_booster . '
        uid: ' . uniqid() . '
    -->

    ';


    // get and paste user custom css
    $td_custom_css = stripslashes(td_util::get_option('tds_custom_css'));


    // get the custom css for the responsive values
    $responsive_css_values = array();
    foreach (td_global::$theme_panel_custom_css_fields_list as $option_id => $css_params) {
        $responsive_css = td_util::get_option($option_id);
        if ($responsive_css != '') {
            $responsive_css_values[$css_params['media_query']] = $responsive_css;
        }
    }



    // check if we have to show any css
    if (!empty($td_custom_css) or count($responsive_css_values) > 0) {
        $css_buffy = PHP_EOL . '<!-- Custom css form theme panel -->';
        $css_buffy .= PHP_EOL . '<style type="text/css" media="screen">';

        //paste custom css
        if(!empty($td_custom_css)) {
            $css_buffy .= PHP_EOL . '/* custom css theme panel */' . PHP_EOL;
            $css_buffy .= $td_custom_css . PHP_EOL;
        }

        foreach ($responsive_css_values as $media_query => $responsive_css) {
            $css_buffy .= PHP_EOL . PHP_EOL . '/* custom responsive css from theme panel (Advanced CSS) */';
            $css_buffy .= PHP_EOL . $media_query . ' {' . PHP_EOL;
            $css_buffy .= $responsive_css;
            $css_buffy .= PHP_EOL . '}' . PHP_EOL;
        }
        $css_buffy .= '</style>' . PHP_EOL . PHP_EOL;

        // echo the css buffer
        echo $css_buffy;
    }


    //get and paste user custom javascript
    $td_custom_javascript = stripslashes(td_util::get_option('tds_custom_javascript'));
    if(!empty($td_custom_javascript)) {
        echo '<script type="text/javascript">'
            .$td_custom_javascript.
            '</script>';
    }

    //AJAX POST VIEW COUNT
    if(td_util::get_option('tds_ajax_post_view_count') == 'enabled') {

        //Ajax get & update counter views
        if(is_single()) {
            //echo 'post page: '.  $post->ID;
            if($post->ID > 0) {
                td_js_buffer::add_to_footer('
                jQuery().ready(function jQuery_ready() {
                    td_ajax_count.td_get_views_counts_ajax("post",' . json_encode('[' . $post->ID . ']') . ');
                });
            ');
            }
        }
    }




	if (TD_DEBUG_USE_LESS) {
		$style_sheet_path = td_global::$get_template_directory_uri . '/td_less_style.css.php';
	} else {
		$style_sheet_path = get_stylesheet_uri();
	}

	/**
	 * javascript splitter js split for IE8 and IE9.
	 * It searches in the stylesheet for #td_css_split_separator and adds it in two pieces for ie8 ie9 selector bug
	 */
	ob_start();
	?>
	<script>

		(function(){
			var html_jquery_obj = jQuery('html');

			if (html_jquery_obj.length && (html_jquery_obj.is('.ie8') || html_jquery_obj.is('.ie9'))) {

				var path = '<?php echo $style_sheet_path; ?>';

				jQuery.get(path, function(data) {

					var str_split_separator = '#td_css_split_separator';
					var arr_splits = data.split(str_split_separator);
					var arr_length = arr_splits.length;

					if (arr_length > 1) {

						var dir_path = '<?php echo get_template_directory_uri() ?>';
						var splited_css = '';

						for (var i = 0; i < arr_length; i++) {
							if (i > 0) {
								arr_splits[i] = str_split_separator + ' ' + arr_splits[i];
							}
							//jQuery('head').append('<style>' + arr_splits[i] + '</style>');

							var formated_str = arr_splits[i].replace(/\surl\(\'(?!data\:)/gi, function regex_function(str) {
								return ' url(\'' + dir_path + '/' + str.replace(/url\(\'/gi, '').replace(/^\s+|\s+$/gm,'');
							});

							splited_css += "<style>" + formated_str + "</style>";
						}

						var td_theme_css = jQuery('link#td-theme-css');

						if (td_theme_css.length) {
							td_theme_css.after(splited_css);
						}
					}
				});
			}
		})();

	</script>
	<?php
	$script_buffer = ob_get_clean();
	$js_script = "\n". td_util::remove_script_tag($script_buffer);
	td_js_buffer::add_to_footer($js_script);
}




/*  ----------------------------------------------------------------------------
    google analytics
 */
add_action('wp_head', 'td_header_analytics_code', 40);
function td_header_analytics_code() {
    $td_analytics = td_util::get_option('td_analytics');
    echo stripslashes($td_analytics);

}




/*  ----------------------------------------------------------------------------
    Append page slugs to the body class
 */
add_filter('body_class', 'add_slug_to_body_class');
function add_slug_to_body_class( $classes ) {
    global $post;
    if( is_home() ) {
        $key = array_search( 'blog', $classes );
        if($key > -1) {
            unset( $classes[$key] );
        };
    } elseif( is_page() ) {
        $classes[] = sanitize_html_class( $post->post_name );
    } elseif(is_singular()) {
        $classes[] = sanitize_html_class( $post->post_name );
    };

    $i = 0;
    foreach ($classes as $key => $value) {
        $pos = strripos($value, 'span');
        if ($pos !== false) {
            unset($classes[$i]);
        }

        $pos = strripos($value, 'row');
        if ($pos !== false) {
            unset($classes[$i]);
        }

        $pos = strripos($value, 'container');
        if ($pos !== false) {
            unset($classes[$i]);
        }
        $i++;
    }
    return $classes;
}




/*  ----------------------------------------------------------------------------
    remove span row container classes from post_class()
 */
add_filter('post_class', 'add_slug_to_post_class');
function add_slug_to_post_class($classes) {
    global $post;

    // on custom post types, we add the .post class for better css compatibility
    if (is_single() and $post->post_type != 'post') {
        $classes[]= 'post';
    }

    $i = 0;
    foreach ($classes as $key => $value) {
        $pos = strripos($value, 'span');
        if ($pos !== false) {
            unset($classes[$i]);
        }

        $pos = strripos($value, 'row');
        if ($pos !== false) {
            unset($classes[$i]);
        }

        $pos = strripos($value, 'container');
        if ($pos !== false) {
            unset($classes[$i]);
        }
        $i++;
    }
    return $classes;
}




/*  -----------------------------------------------------------------------------
    Our custom admin bar
 */
add_action('admin_bar_menu', 'td_custom_menu', 1000);
function td_custom_menu() {
    global $wp_admin_bar;
    if(!is_super_admin() || !is_admin_bar_showing()) return;

    $wp_admin_bar->add_menu(array(
        'parent' => 'site-name',
        'title' => '<span class="td-admin-bar-red">Theme panel</span>',
        'href' => admin_url('admin.php?page=td_theme_panel'),
        'id' => 'td-menu1'
    ));

    $wp_admin_bar->add_menu( array(
        'id'   => 'our_support_item',
        'meta' => array('title' => 'Theme support', 'target' => '_blank'),
        'title' => 'Theme support',
        'href' => 'http://forum.tagdiv.com' ));

}




/*  -----------------------------------------------------------------------------
 * Add prev and next links to a numbered link list - the pagination on single.
 */
add_filter('wp_link_pages_args', 'wp_link_pages_args_prevnext_add');
function wp_link_pages_args_prevnext_add($args)
{
    global $page, $numpages, $more, $pagenow;

    if (!$args['next_or_number'] == 'next_and_number')
        return $args; # exit early

    $args['next_or_number'] = 'number'; # keep numbering for the main part
    if (!$more)
        return $args; # exit early

    if($page-1) # there is a previous page
        $args['before'] .= _wp_link_page($page-1)
            . $args['link_before']. $args['previouspagelink'] . $args['link_after'] . '</a>'
        ;

    if ($page<$numpages) # there is a next page
        $args['after'] = _wp_link_page($page+1)
            . $args['link_before'] . $args['nextpagelink'] . $args['link_after'] . '</a>'
            . $args['after']
        ;

    return $args;
}




/*  -----------------------------------------------------------------------------
 * Add, on theme body element, the custom classes from Theme Panel -> Custom Css -> Custom Body class(s)
 */
add_filter('body_class','td_my_custom_class_names_on_body');
function td_my_custom_class_names_on_body($classes) {
    //get the custom classes from theme options
    $custom_classes = td_util::get_option('td_body_classes');

    if(!empty($custom_classes)) {
        // add 'custom classes' to the $classes array
        $classes[] = $custom_classes;
    }

    // return the $classes array
    return $classes;
}




/*  ----------------------------------------------------------------------------
    used by ie8 - there is no other way to add js for ie8 only
 */
add_action('wp_head', 'add_ie_html5_shim');
function add_ie_html5_shim () {
    echo '<!--[if lt IE 9]>';
    echo '<script src="' . td_global::$http_or_https . '://html5shim.googlecode.com/svn/trunk/html5.js"></script>';
    echo '<![endif]-->
    ';
}







/*  ----------------------------------------------------------------------------
    add extra contact information for author in wp-admin -> users -> your profile
 */
add_filter('user_contactmethods', 'td_extra_contact_info_for_author');
function td_extra_contact_info_for_author($contactmethods) {
    unset($contactmethods['aim']);
    unset($contactmethods['yim']);
    unset($contactmethods['jabber']);
    foreach (td_social_icons::$td_social_icons_array as $td_social_id => $td_social_name) {
        $contactmethods[$td_social_id] = $td_social_name;
    }
    return $contactmethods;
}








/* ----------------------------------------------------------------------------
 * shortcodes in widgets
 */
add_filter('widget_text', 'do_shortcode');




/* ----------------------------------------------------------------------------
 * FILTER - the_content_more_link - read more - ?
 */
add_filter('the_content_more_link', 'td_remove_more_link_scroll');
function td_remove_more_link_scroll($link) {
    $link = preg_replace('|#more-[0-9]+|', '', $link);
    $link = '<div class="more-link-wrap">' . $link . '</div>';
    return $link;
}




/* ----------------------------------------------------------------------------
 * Visual Composer init
 */
register_activation_hook('js_composer/js_composer.php', 'td_vc_kill_welcome', 11);
function td_vc_kill_welcome() {
    remove_action('vc_activation_hook', 'vc_page_welcome_set_redirect');
}


/**
 * visual composer rewrite classes
 * Filter to Replace default css class for vc_row shortcode and vc_column
 */
add_filter('vc_shortcodes_css_class', 'custom_css_classes_for_vc_row_and_vc_column', 10, 2);
function custom_css_classes_for_vc_row_and_vc_column($class_string, $tag) {
    //vc_span4
    if ($tag == 'vc_row' || $tag == 'vc_row_inner') {
        $class_string = str_replace('vc_row-fluid', 'td-pb-row', $class_string);
    }
    if ($tag == 'vc_column' || $tag == 'vc_column_inner') {
        $class_string = preg_replace('/vc_col-sm-(\d{1,2})/', 'td-pb-span$1', $class_string);
        //$class_string = preg_replace('/vc_span(\d{1,2})/', 'td-pb-span$1', $class_string);
    }
    return $class_string;
}

add_action('vc_load_default_templates','my_custom_template_for_vc');
function my_custom_template_for_vc($templates) {

    require_once(get_template_directory() . '/includes/td_templates_builder.php');

	global $td_vc_templates;
	global $vc_manager;

	if (isset($vc_manager) and is_object($vc_manager) and method_exists($vc_manager, 'vc')) {
		$vc = $vc_manager->vc();

		if (isset($vc) and is_object($vc) and method_exists($vc, 'templatesPanelEditor')) {
			$vc_template_panel_editor = $vc->templatesPanelEditor();

			if (isset($vc_template_panel_editor)
			    and is_object($vc_template_panel_editor)
		        and has_filter('vc_load_default_templates_welcome_block', array($vc_template_panel_editor, 'loadDefaultTemplatesLimit'))) {

				remove_filter('vc_load_default_templates_welcome_block', array($vc_template_panel_editor, 'loadDefaultTemplatesLimit'));
			}
		}
	}
	return $td_vc_templates;
}


td_vc_init();
function td_vc_init() {

    // Force Visual Composer to initialize as "built into the theme". This will hide certain tabs under the Settings->Visual Composer page
    if (function_exists('vc_set_as_theme')) {
        vc_set_as_theme(true);
    }

    if (function_exists('wpb_map')) {
        //map all of our blocks in page builder
        td_global_blocks::wpb_map_all();
    }

    if (function_exists('vc_disable_frontend')) {
        vc_disable_frontend();
    }

    if (class_exists('WPBakeryVisualComposer')) { //disable visual composer updater
        $td_composer = WPBakeryVisualComposer::getInstance();
        $td_composer->disableUpdater();
    }
}




/* ----------------------------------------------------------------------------
 * TagDiv gallery - tinyMCE hooks
 */
add_action('print_media_templates', 'td_custom_gallery_settings_hook');
add_action('print_media_templates', 'td_change_backbone_js_hook');
/**
 * custom gallery setting
 */
function td_custom_gallery_settings_hook () {
    // define your backbone template;
    // the "tmpl-" prefix is required,
    // and your input field should have a data-setting attribute
    // matching the shortcode name
    ?>
    <script type="text/html" id="tmpl-td-custom-gallery-setting">
        <label class="setting">
            <span>Gallery Type</span>
            <select data-setting="td_select_gallery_slide">
                <option value="">Default </option>
                <option value="slide">TagDiv Slide Gallery</option>
            </select>
        </label>

        <label class="setting">
            <span>Gallery Title</span>
            <input type="text" value="" data-setting="td_gallery_title_input" />
        </label>
    </script>

    <script>

        jQuery(document).ready(function(){

            // add your shortcode attribute and its default value to the
            // gallery settings list; $.extend should work as well...
            _.extend(wp.media.gallery.defaults, {
                td_select_gallery_slide: '', td_gallery_title_input: ''
            });

            // merge default gallery settings template with yours
            wp.media.view.Settings.Gallery = wp.media.view.Settings.Gallery.extend({
                template: function(view){
                    return wp.media.template('gallery-settings')(view)
                        + wp.media.template('td-custom-gallery-setting')(view);
                }
            });

            //console.log();
            // wp.media.model.Attachments.trigger('change')
        });

    </script>
<?php
}


/**
 * td-modal-image support in tinymce
 */
function td_change_backbone_js_hook() {
    //change the backbone js template


    // make the buffer for the dropdown
    $image_styles_buffer_for_select = '';
    $image_styles_buffer_for_switch = '';


    foreach (td_global::$tiny_mce_image_style_list as $tiny_mce_image_style_id => $tiny_mce_image_style_params) {
        $image_styles_buffer_for_select .= "'<option value=\"" . $tiny_mce_image_style_id . "\">" . $tiny_mce_image_style_params['text'] . "</option>' + ";
        $image_styles_buffer_for_switch .= "
        case '$tiny_mce_image_style_id':
            td_clear_all_classes(); //except the modal one
            td_add_image_css_class('" . $tiny_mce_image_style_params['class'] . "');
            break;
        ";
    }


    ?>
    <script type="text/javascript">

        (function (){

            var td_template_content = jQuery('#tmpl-image-details').text();

            var td_our_content = '' +
                '<div class="setting">' +
                '<span>Modal image</span>' +
                '<div class="button-large button-group" >' +
                '<button class="button active td-modal-image-off" value="left">Off</button>' +
                '<button class="button td-modal-image-on" value="left">On</button>' +
                '</div><!-- /setting -->' +
                '<div class="setting">' +
                '<span>tagDiv image style</span>' +
                '<select class="size td-wp-image-style">' +
                '<option value="">Default</option>' +
                <?php echo $image_styles_buffer_for_select ?>
                '</select>' +
                '</div>' +
                '</div>';

            //inject our settings in the template - before <div class="setting align">
            td_template_content = td_template_content.replace('<div class="setting align">', td_our_content + '<div class="setting align">');

            //save the template
            jQuery('#tmpl-image-details').html(td_template_content);

            //modal off - click event
            jQuery(".td-modal-image-on").live( "click", function() {
                if (jQuery(this).hasClass('active')) {
                    return;
                }
                td_add_image_css_class('td-modal-image');

                jQuery(".td-modal-image-off").removeClass('active');
                jQuery(".td-modal-image-on").addClass('active');
            });

            //modal on - click event
            jQuery(".td-modal-image-off").live( "click", function() {
                if (jQuery(this).hasClass('active')) {
                    return;
                }

                td_remove_image_css_class('td-modal-image');

                jQuery(".td-modal-image-off").addClass('active');
                jQuery(".td-modal-image-on").removeClass('active');
            });

            // select change event
            jQuery(".td-wp-image-style").live( "change", function() {
                switch (jQuery( ".td-wp-image-style").val()) {

                    <?php echo $image_styles_buffer_for_switch; ?>

                    default:
                        td_clear_all_classes(); //except the modal one
                        jQuery('*[data-setting="extraClasses"]').change(); //trigger the change event for backbonejs
                }
            });

            //util functions to edit the image details in wp-admin
            function td_add_image_css_class(new_class) {
                var td_extra_classes_value = jQuery('*[data-setting="extraClasses"]').val();
                jQuery('*[data-setting="extraClasses"]').val(td_extra_classes_value + ' ' + new_class);
                jQuery('*[data-setting="extraClasses"]').change(); //trigger the change event for backbonejs
            }

            function td_remove_image_css_class(new_class) {
                var td_extra_classes_value = jQuery('*[data-setting="extraClasses"]').val();

                //try first with a space before the class
                var td_regex = new RegExp(" " + new_class,"g");
                td_extra_classes_value = td_extra_classes_value.replace(td_regex, '');

                var td_regex = new RegExp(new_class,"g");
                td_extra_classes_value = td_extra_classes_value.replace(td_regex, '');

                jQuery('*[data-setting="extraClasses"]').val(td_extra_classes_value);
                jQuery('*[data-setting="extraClasses"]').change(); //trigger the change event for backbonejs
            }

            //clears all classes except the modal image one
            function td_clear_all_classes() {
                var td_extra_classes_value = jQuery('*[data-setting="extraClasses"]').val();
                if (td_extra_classes_value.indexOf('td-modal-image') > -1) {
                    //we have the modal image one - keep it, remove the others
                    jQuery('*[data-setting="extraClasses"]').val('td-modal-image');
                } else {
                    jQuery('*[data-setting="extraClasses"]').val('');
                }
            }

            //monitor the backbone template for the current status of the picture
            setInterval(function(){
                var td_extra_classes_value = jQuery('*[data-setting="extraClasses"]').val();
                if (typeof td_extra_classes_value !== 'undefined' && td_extra_classes_value != '') {
                    // if we have modal on, switch the toggle
                    if (td_extra_classes_value.indexOf('td-modal-image') > -1) {
                        jQuery(".td-modal-image-off").removeClass('active');
                        jQuery(".td-modal-image-on").addClass('active');
                    }

                    <?php

                    foreach (td_global::$tiny_mce_image_style_list as $tiny_mce_image_style_id => $tiny_mce_image_style_params) {
                        ?>
                        //change the select
                        if (td_extra_classes_value.indexOf('<?php echo $tiny_mce_image_style_params['class'] ?>') > -1) {
                            jQuery(".td-wp-image-style").val('<?php echo $tiny_mce_image_style_id ?>');
                        }
                        <?php
                    }

                    ?>

                }
            }, 1000);
        })(); //end anon function
    </script>
<?php
}




/* ----------------------------------------------------------------------------
 * TagDiv gallery - front end hooks
 */
add_filter('post_gallery', 'my_gallery_shortcode', 10, 4);
/**
 * @param string $output - is empty !!!
 * @param $atts
 * @param bool $content
 * @param bool $tag
 * @return mixed
 */
function my_gallery_shortcode($output = '', $atts, $content = false, $tag = false) {

    //print_r($atts);
    $buffy = '';

    //check for gallery  = slide
    if(!empty($atts) and !empty($atts['td_select_gallery_slide']) and $atts['td_select_gallery_slide'] == 'slide') {

        $td_double_slider2_no_js_limit = 7;
        $td_nr_columns_slide = 'td-slide-on-2-columns';
        $nr_title_chars = 95;

        //check to see if we have or not sidebar on the page, to set the small images when need to show them on center
        if(td_global::$cur_single_template_sidebar_pos == 'no_sidebar') {
            $td_double_slider2_no_js_limit = 11;
            $td_nr_columns_slide = 'td-slide-on-3-columns';
            $nr_title_chars = 170;
        }

        $title_slide = '';
        //check for the title
        if(!empty($atts['td_gallery_title_input'])) {
            $title_slide = $atts['td_gallery_title_input'];

            //check how many chars the tile have, if more then 84 then that cut it and add ... after
            if(mb_strlen ($title_slide, 'UTF-8') > $nr_title_chars) {
                $title_slide = mb_substr($title_slide, 0, $nr_title_chars, 'UTF-8') . '...';
            }
        }

        $slide_images_thumbs_css = '';
        $slide_display_html  = '';
        $slide_cursor_html = '';

        $image_ids = explode(',', $atts['ids']);

        //check to make sure we have images
        if (count($image_ids) == 1 and !is_numeric($image_ids[0])) {
            return;
        }

        $image_ids = array_map('trim', $image_ids);//trim elements of the $ids_gallery array

        //generate unique gallery slider id
        $gallery_slider_unique_id = td_global::td_generate_unique_id();

        $cur_item_nr = 1;
        foreach($image_ids as $image_id) {

            //get the info about attachment
            $image_attachment = td_util::attachment_get_full_info($image_id);

            //get images url
            $td_temp_image_url_80x60 = wp_get_attachment_image_src($image_id, 'td_80x60'); //for the slide - for small images slide popup
            $td_temp_image_url_full = $image_attachment['src'];                            //default big image - for magnific popup

            //if we are on full wight (3 columns use the default images not the resize ones)
            if(td_global::$cur_single_template_sidebar_pos == 'no_sidebar') {

                $td_temp_image_url = wp_get_attachment_image_src($image_id, 'td_1021x580');       //1021x580 images - for big slide
            } else {
                $td_temp_image_url = wp_get_attachment_image_src($image_id, 'td_0x420');       //0x420 image sizes - for big slide
            }


            //check if we have all the images
            if(!empty($td_temp_image_url[0]) and !empty($td_temp_image_url_80x60[0]) and !empty($td_temp_image_url_full)) {

                //css for display the small cursor image
                $slide_images_thumbs_css .= '
                    #' . $gallery_slider_unique_id . '  .td-doubleSlider-2 .td-item' . $cur_item_nr . ' {
                        background: url(' . $td_temp_image_url_80x60[0] . ') 0 0 no-repeat;
                    }';

                //html for display the big image
                $class_post_content = '';

                if(!empty($image_attachment['description']) or !empty($image_attachment['caption'])) {
                    $class_post_content = 'td-gallery-slide-content';
                }

                //if picture has caption & description
                $figcaption = '';

                if(!empty($image_attachment['caption']) or !empty($image_attachment['description'])) {
                    $figcaption = '<figcaption class = "td-slide-caption ' . $class_post_content . '">';

                    if(!empty($image_attachment['caption'])) {
                        $figcaption .= '<div class = "td-gallery-slide-copywrite">' . $image_attachment['caption'] . '</div>';
                    }

                    if(!empty($image_attachment['description'])) {
                        $figcaption .= '<span>' . $image_attachment['description'] . '</span>';
                    }

                    $figcaption .= '</figcaption>';
                }

                $slide_display_html .= '
                    <div class = "td-slide-item td-item' . $cur_item_nr . '">
                        <figure class="td-slide-galery-figure td-slide-popup-gallery">
                            <a class="slide-gallery-image-link" href="' . $td_temp_image_url_full . '" title="' . $image_attachment['title'] . '"  data-caption="' . esc_attr($image_attachment['caption'], ENT_QUOTES) . '"  data-description="' . htmlentities($image_attachment['description'], ENT_QUOTES) . '">
                                <img src="' . $td_temp_image_url[0] . '" alt="' . htmlentities($image_attachment['alt'], ENT_QUOTES) . '">
                            </a>
                            ' . $figcaption . '
                        </figure>
                    </div>';

                //html for display the small cursor image
                $slide_cursor_html .= '
                    <div class = "td-button td-item' . $cur_item_nr . '">
                        <div class = "td-border"></div>
                    </div>';

                $cur_item_nr++;
            }//end check for images
        }//end foreach

        //check if we have html code for the slider
        if(!empty($slide_display_html) and !empty($slide_cursor_html)) {

            //get the number of slides
            $nr_of_slides = count($image_ids);
            if($nr_of_slides < 0) {
                $nr_of_slides = 0;
            }

            $buffy = '
                <style type="text/css">
                    ' .
                $slide_images_thumbs_css . '
                </style>

                <div id="' . $gallery_slider_unique_id . '" class="' . $td_nr_columns_slide . '">
                    <div class="post_td_gallery">
                        <div class="td-gallery-slide-top">
                           <div class="td-gallery-title">' . $title_slide . '</div>

                            <div class="td-gallery-controls-wrapper">
                                <div class="td-gallery-slide-count"><span class="td-gallery-slide-item-focus">1</span> ' . __td('of', TD_THEME_NAME) . ' ' . $nr_of_slides . '</div>
                                <div class="td-gallery-slide-prev-next-but">
                                    <i class = "td-icon-left doubleSliderPrevButton"></i>
                                    <i class = "td-icon-right doubleSliderNextButton"></i>
                                </div>
                            </div>
                        </div>

                        <div class = "td-doubleSlider-1 ">
                            <div class = "td-slider">
                                ' . $slide_display_html . '
                            </div>
                        </div>

                        <div class = "td-doubleSlider-2">
                            <div class = "td-slider">
                                ' . $slide_cursor_html . '
                            </div>
                        </div>

                    </div>

                </div>
                ';

            $slide_javascript = '
                    //total number of slides
                    var ' . $gallery_slider_unique_id . '_nr_of_slides = ' . $nr_of_slides . ';

                    jQuery(document).ready(function() {
                        //magnific popup
                        jQuery("#' . $gallery_slider_unique_id . ' .td-slide-popup-gallery").magnificPopup({
                            delegate: "a",
                            type: "image",
                            tLoading: "Loading image #%curr%...",
                            mainClass: "mfp-img-mobile",
                            gallery: {
                                enabled: true,
                                navigateByImgClick: true,
                                preload: [0,1],
                                tCounter: \'%curr% ' . __td('of', TD_THEME_NAME) . ' %total%\'
                            },
                            image: {
                                tError: "<a href=\'%url%\'>The image #%curr%</a> could not be loaded.",
                                    titleSrc: function(item) {//console.log(item.el);
                                    //alert(jQuery(item.el).data("caption"));
                                    return item.el.attr("data-caption") + "<div>" + item.el.attr("data-description") + "<div>";
                                }
                            },
                            zoom: {
                                    enabled: true,
                                    duration: 300,
                                    opener: function(element) {
                                        return element.find("img");
                                    }
                            },

                            callbacks: {
                                change: function() {
                                    // Will fire when popup is closed
                                    jQuery("#' . $gallery_slider_unique_id . ' .td-doubleSlider-1").iosSlider("goToSlide", this.currItem.index + 1 );
                                }
                            }

                        });

                        jQuery("#' . $gallery_slider_unique_id . ' .td-doubleSlider-1").iosSlider({
                            scrollbar: true,
                            snapToChildren: true,
                            desktopClickDrag: true,
                            infiniteSlider: true,
                            responsiveSlides: true,
                            navPrevSelector: jQuery("#' . $gallery_slider_unique_id . ' .doubleSliderPrevButton"),
                            navNextSelector: jQuery("#' . $gallery_slider_unique_id . ' .doubleSliderNextButton"),
                            scrollbarHeight: "2",
                            scrollbarBorderRadius: "0",
                            scrollbarOpacity: "0.5",
                            onSliderResize: td_gallery_resize_update_vars_' . $gallery_slider_unique_id . ',
                            onSliderLoaded: doubleSlider2Load_' . $gallery_slider_unique_id . ',
                            onSlideChange: doubleSlider2Load_' . $gallery_slider_unique_id . ',
                            keyboardControls:true
                        });

                        //small image slide
                        jQuery("#' . $gallery_slider_unique_id . ' .td-doubleSlider-2 .td-button").each(function(i) {
                            jQuery(this).bind("click", function() {
                                jQuery("#' . $gallery_slider_unique_id . ' .td-doubleSlider-1").iosSlider("goToSlide", i+1);
                            });
                        });

                        //check the number of slides
                        if(' . $gallery_slider_unique_id . '_nr_of_slides > ' . $td_double_slider2_no_js_limit . ') {
                            jQuery("#' . $gallery_slider_unique_id . ' .td-doubleSlider-2").iosSlider({
                                desktopClickDrag: true,
                                snapToChildren: true,
                                snapSlideCenter: true,
                                infiniteSlider: true
                            });
                        } else {
                            jQuery("#' . $gallery_slider_unique_id . ' .td-doubleSlider-2").addClass("td_center_slide2");
                        }

                        function doubleSlider2Load_' . $gallery_slider_unique_id . '(args) {
                            //var currentSlide = args.currentSlideNumber;
                            jQuery("#' . $gallery_slider_unique_id . ' .td-doubleSlider-2").iosSlider("goToSlide", args.currentSlideNumber);


                            //put a transparent border around all small sliders
                            jQuery("#' . $gallery_slider_unique_id . ' .td-doubleSlider-2 .td-button .td-border").css("border", "3px solid #ffffff").css("opacity", "0.5");
                            jQuery("#' . $gallery_slider_unique_id . ' .td-doubleSlider-2 .td-button").css("border", "0");

                            //put a white border around the focused small slide
                            jQuery("#' . $gallery_slider_unique_id . ' .td-doubleSlider-2 .td-button:eq(" + (args.currentSlideNumber-1) + ") .td-border").css("border", "3px solid #ffffff").css("opacity", "1");
                            //jQuery("#' . $gallery_slider_unique_id . ' .td-doubleSlider-2 .td-button:eq(" + (args.currentSlideNumber-1) + ")").css("border", "3px solid #ffffff");

                            //write the current slide number
                            td_gallery_write_current_slide_' . $gallery_slider_unique_id . '(args.currentSlideNumber);
                        }

                        //writes the current slider beside to prev and next buttons
                        function td_gallery_write_current_slide_' . $gallery_slider_unique_id . '(slide_nr) {
                            jQuery("#' . $gallery_slider_unique_id . ' .td-gallery-slide-item-focus").html(slide_nr);
                        }


                        /*
                        * Resize the iosSlider when the page is resided (fixes bug on Android devices)
                        */
                        function td_gallery_resize_update_vars_' . $gallery_slider_unique_id . '(args) {
                            if(td_detect.is_android) {
                                setTimeout(function(){
                                    jQuery("#' . $gallery_slider_unique_id . ' .td-doubleSlider-1").iosSlider("update");
                                }, 1500);
                            }
                        }
                    });';

            td_js_buffer::add_to_footer($slide_javascript);
        }//end check if we have html code for the slider
    }//end if slide

    //!!!!!! WARNING
    //$return has to be != empty to overwride the default output
    return $buffy;
}




/* ----------------------------------------------------------------------------
 * filter the gallery shortcode
 */
add_filter('shortcode_atts_gallery', 'my_gallery_atts_modifier', 1); //run with 1 priority, allow anyone to overwrite our hook.
/**
 * @todo trebuie fixuite toate tipurile de imagini din gallerie in functie de setarile template-ului
 */
function my_gallery_atts_modifier($out) {

    // td_global::$cur_single_template_sidebar_pos; //is set in single.php @todo set it also on the page template
    // link to files instead of no link or attachement. The file is used by magnific pupup
    $out['link'] = 'file';

    if (!isset($out['columns'])) {
        $out['columns'] = '';
    }

    if (td_global::$cur_single_template_sidebar_pos == 'no_sidebar') {
        if ($out['columns'] == 1) {
            $out['size'] = 'td_1021x580';
        }
    } else {
        if ($out['columns'] == 1) {
            $out['size'] = 'td_640x350';
        }
    }
    return $out;
}




/* ----------------------------------------------------------------------------
 * add custom classes to the single templates, also mix fixes for white menu and white grid
 */
add_filter('body_class', 'td_add_single_template_class');
function td_add_single_template_class($classes) {

    if (is_single()) {
        global $post;

        $active_single_template = '';
        $td_post_theme_settings = get_post_meta($post->ID, 'td_post_theme_settings', true);

        if (!empty($td_post_theme_settings['td_post_template'])) {
            // we have a post template set in the post
            $active_single_template = $td_post_theme_settings['td_post_template'];
        } else {
            // we may have a global post template form td panel
            $td_default_site_post_template = td_util::get_option('td_default_site_post_template');
            if(!empty($td_default_site_post_template)) {
                $active_single_template = $td_default_site_post_template;
            }
        }


        // add the class if we have a post template
        if (!empty($active_single_template)) {
            td_global::$cur_single_template = $active_single_template;
            $classes []= sanitize_html_class($active_single_template);
        }

    }

    // if main menu background color is white to fix the menu appearance on all headers
    if (td_util::get_option('tds_menu_color') == '#ffffff' or td_util::get_option('tds_menu_color') == 'ffffff') {
        $classes[] = 'white-menu';
    }

    // if grid color is white to fix the menu appearance on all headers
    if (td_util::get_option('tds_grid_line_color') == '#ffffff' or td_util::get_option('tds_grid_line_color') == 'ffffff') {
        $classes[] = 'white-grid';
    }
    return $classes;
}




/* ----------------------------------------------------------------------------
 * add custom classes to the single templates, also mix fixes for white menu and white grid
 */
add_filter('body_class', 'td_add_category_template_class');
function td_add_category_template_class($classes) {
    if(!is_admin() and is_category()) {
        $classes [] = sanitize_html_class(td_api_category_template::_helper_get_active_id());
        $classes [] = sanitize_html_class(td_api_category_top_posts_style::_helper_get_active_id());
    }
    return $classes;
}




/* ----------------------------------------------------------------------------
 * add `filter_by` URL variable so I can retrieve it with  `get_query_var` function
 */
add_filter('query_vars', 'td_category_big_grid_add_query_vars_filter');
function td_category_big_grid_add_query_vars_filter($vars) {
    $vars[] = "filter_by";
    return $vars;
}




/* ----------------------------------------------------------------------------
 * modify the main query for category pages
 */
add_action('pre_get_posts', 'td_modify_main_query_for_category_page');
function td_modify_main_query_for_category_page($query) {


    //checking for category page and main query
    if(!is_admin() and is_category() and $query->is_main_query()) {

        //get the number of page where on
        $paged = get_query_var('paged');

        //get the `filter_by` URL($_GET) variable
        $filter_by = get_query_var('filter_by');

        //get the limit of posts on the category page
        $limit = get_option('posts_per_page');

        // get the category object - with or without permalinks
        if (empty($query->query_vars['cat'])) {
            td_global::$current_category_obj = get_category_by_path(get_query_var('category_name'), false);  // when we have permalinks, we have to get the category object like this.
        } else {
            td_global::$current_category_obj = get_category($query->query_vars['cat']);
        }

        //offset is hardcoded because of big grid
        $offset = td_api_category_top_posts_style::_helper_get_posts_shown_in_the_loop();


        //echo $filter_by;
        switch ($filter_by) {
            case 'featured':
                //get the category object
                $query->set('category_name',  td_global::$current_category_obj->slug);
                $query->set('cat', get_cat_ID(TD_FEATURED_CAT)); //add the fetured cat
                break;

            case 'popular':
                $query->set('meta_key', td_page_views::$post_view_counter_key);
                $query->set('orderby', 'meta_value_num');
                $query->set('order', 'DESC');
                break;

            case 'popular7':
                $query->set('meta_key', td_page_views::$post_view_counter_7_day_total);
                $query->set('orderby', 'meta_value_num');
                $query->set('order', 'DESC');
                break;

            case 'review_high':
                $query->set('meta_key', td_review::$td_review_key);
                $query->set('orderby', 'meta_value_num');
                $query->set('order', 'DESC');
                break;

            case 'random_posts':
                $query->set('orderby', 'rand');
                break;
        }//end switch

	    // offset + custom pagination - if we have offset, WordPress overwrites the pagination and works with offset + limit
	    if(empty($query->is_feed)) {
		    if ( ! empty( $offset ) and $paged > 1 ) {
			    $query->set( 'offset', $offset + ( ( $paged - 1 ) * $limit ) );
		    } else {
			    $query->set( 'offset', $offset );
		    }
	    }
        //print_r($query);
    }//end if main query
}




/** ----------------------------------------------------------------------------
 *  update category shared terms
 *  @since WordPress 4.2
 *  @link https://make.wordpress.org/core/2015/02/16/taxonomy-term-splitting-in-4-2-a-developer-guide/
 */
add_action('split_shared_term', 'td_category_split_shared_term', 10, 4);
function td_category_split_shared_term($term_id, $new_term_id, $term_taxonomy_id, $taxonomy) {
	if (($taxonomy === 'category') and (isset(td_global::$td_options['category_options'][$term_id]))) {

		$current_settings = td_global::$td_options['category_options'][$term_id];
		td_global::$td_options['category_options'][$new_term_id] = $current_settings;
		unset(td_global::$td_options['category_options'][$term_id]);

		update_option(TD_THEME_OPTIONS_NAME, td_global::$td_options);
	}
}




/* ----------------------------------------------------------------------------
 *   TagDiv WordPress booster init
 */

td_init_booster();
function td_init_booster() {

    global $content_width;

    // content width - this is overwritten in post
    if (!isset($content_width)) {
        $content_width = 640;
    }

    /* ----------------------------------------------------------------------------
     * add_image_size for WordPress - register all the thumbs from the thumblist
     */
    foreach (td_api_thumb::get_all() as $thumb_array) {
        if (td_util::get_option('tds_thumb_' . $thumb_array['name']) != '') {
            add_image_size($thumb_array['name'], $thumb_array['width'], $thumb_array['height'], $thumb_array['crop']);
        }
    }


    /* ----------------------------------------------------------------------------
     * Add lazy shortcodes of the registered blocks
     */
    foreach (td_api_block::get_all() as $block_settings_key => $block_settings_value) {
        td_global_blocks::add_lazy_shortcode($block_settings_key);
    }


    /* ----------------------------------------------------------------------------
    * register the default sidebars + dynamic ones
    */
    register_sidebar(array(
        'name'=> TD_THEME_NAME . ' default',
        'id' => 'td-default', //the id is used by the importer
        'before_widget' => '<aside class="widget %2$s">',
        'after_widget' => '</aside>',
        'before_title' => '<div class="block-title"><span>',
        'after_title' => '</span></div>'
    ));

    register_sidebar(array(
        'name'=>'Footer 1',
        'id' => 'td-footer-1',
        'before_widget' => '<aside class="widget %2$s">',
        'after_widget' => '</aside>',
        'before_title' => '<div class="block-title"><span>',
        'after_title' => '</span></div>'
    ));

    register_sidebar(array(
        'name'=>'Footer 2',
        'id' => 'td-footer-2',
        'before_widget' => '<aside class="widget %2$s">',
        'after_widget' => '</aside>',
        'before_title' => '<div class="block-title"><span>',
        'after_title' => '</span></div>'
    ));

    register_sidebar(array(
        'name'=>'Footer 3',
        'id' => 'td-footer-3',
        'before_widget' => '<aside class="widget %2$s">',
        'after_widget' => '</aside>',
        'before_title' => '<div class="block-title"><span>',
        'after_title' => '</span></div>'
    ));

    //get our custom dynamic sidebars
    $currentSidebars = td_util::get_option('sidebars');

    //if we have user made sidebars, register them in wp
    if (!empty($currentSidebars)) {
        foreach ($currentSidebars as $sidebar) {
            register_sidebar(array(
                'name' => $sidebar,
                'id' => 'td-' . td_util::sidebar_name_to_id($sidebar),
                'before_widget' => '<aside class="widget %2$s">',
                'after_widget' => '</aside>',
                'before_title' => '<div class="block-title"><span>',
                'after_title' => '</span></div>',
            ));
        } //end foreach
    }

	$smooth_scroll = td_util::get_option('tds_smooth_scroll');

	if (!empty($smooth_scroll)) {
		td_js_buffer::add_variable('tds_smooth_scroll', true);
	}
}




/*  ----------------------------------------------------------------------------
    check to see if we are on the backend
 */
if (is_admin()) {
    // the two files are required by wp_admin -> to make the page / posts metaboxes
    require_once('wp-admin/panel/td_panel_generator.php');
    require_once('wp-admin/panel/td_panel_data_source.php');

    // demo inmporter
    require_once('wp-admin/panel/td_demo_installer.php');
    require_once('wp-admin/panel/td_demo_util.php');

    if (current_user_can('switch_themes')) {
        // the panel
        require_once('wp-admin/panel/td_panel.php');
    }


    /**
     * the wp-admin TinyMCE editor buttons
     */
    require_once('wp-admin/tinymce/tinymce.php');

	/**
	 * get tinymce formats
	 */
	td_api_tinymce_formats::_helper_get_tinymce_format();

    /**
     * Custom content metaboxes (the select sidebar dropdown/post etc)
     */
    require_once('td_metabox_generator.php');
    require_once('wp-admin/content-metaboxes/td_templates_settings.php');

    /**
     * Helper pointers
     */
    require_once('td_help_pointers.php');

    add_action('admin_enqueue_scripts', 'td_help_pointers');
    function td_help_pointers()
    {
        //First we define our pointers
        $pointers = array(
            array(
                'id' => 'vc_columns_pointer',   // unique id for this pointer
                'screen' => 'page', // this is the page hook we want our pointer to show on
                'target' => '.composer-switch', // the css selector for the pointer to be tied to, best to use ID's
                'title' => TD_THEME_NAME . ' (tagDiv) tip',
                'content' => '<img class="td-tip-vc-columns" style="max-width:100%" src="' . td_global::$get_template_directory_uri . '/includes/wp_booster/wp-admin/images/td_helper_pointers/vc-columns.png' . '">',
                'position' => array(
                    'edge' => 'top', //top, bottom, left, right
                    'align' => 'left' //top, bottom, left, right, middle
                )
            )
            // more as needed
        );
        //Now we instantiate the class and pass our pointer array to the constructor
        $myPointers = new td_help_pointers($pointers);
    }

    /*  -----------------------------------------------------------------------------
        TGM_Plugin_Activation
     */
    require_once 'external/class-tgm-plugin-activation.php';




    add_action('tgmpa_register', 'td_required_plugins');
    function td_required_plugins() {
        $config = array(
            'domain' => TD_THEME_NAME,            // Text domain - likely want to be the same as your theme.
            'default_path' => '',                            // Default absolute path to pre-packaged plugins
            'parent_menu_slug' => 'themes.php',                // Default parent menu slug
            'parent_url_slug' => 'themes.php',                // Default parent URL slug
            'menu' => 'td_plugins',    // Menu slug
            'has_notices' => false,                        // Show admin notices or not
            'is_automatic' => true,                        // Automatically activate plugins after installation or not
            'message' => '',                            // Message to output right before the plugins table
            'strings' => array(
                'page_title' => __('Install Required Plugins', TD_THEME_NAME),
                'menu_title' => __('Install Plugins', TD_THEME_NAME),
                'installing' => __('Installing Plugin: %s', TD_THEME_NAME), // %1$s = plugin name
                'oops' => __('Something went wrong with the plugin API.', TD_THEME_NAME),
                'notice_can_install_required' => _n_noop('<span class="td-tgma-tip">' . TD_THEME_NAME . ' theme:</span> Hi, sorry to bother you but please install %1$s plugin. It\'s included with the theme and no aditional purchase is requiered!', 'This theme requires the following plugins: %1$s.'), // %1$s = plugin name(s)
                'notice_can_install_recommended' => _n_noop('If you need social icons, you can install %1$s.', 'This theme recommends the following plugins: %1$s.'), // %1$s = plugin name(s)
                'notice_cannot_install' => _n_noop('Sorry, but you do not have the correct permissions to install the %s plugin. Contact the administrator of this site for help on getting the plugin installed.', 'Sorry, but you do not have the correct permissions to install the %s plugins. Contact the administrator of this site for help on getting the plugins installed.'), // %1$s = plugin name(s)
                'notice_can_activate_required' => _n_noop('<span class="td-tgma-tip">' . TD_THEME_NAME . ' theme:</span> Hi, please activate %1$s. Our theme works best with it.', 'The following required plugins are currently inactive: %1$s.'), // %1$s = plugin name(s)
                'notice_can_activate_recommended' => _n_noop('<span class="td-tgma-tip">' . TD_THEME_NAME . ' theme:</span> The following recommended plugin is currently inactive: %1$s.', 'The following recommended plugins are currently inactive: %1$s.'), // %1$s = plugin name(s)
                'notice_cannot_activate' => _n_noop('Sorry, but you do not have the correct permissions to activate the %s plugin. Contact the administrator of this site for help on getting the plugin activated.', 'Sorry, but you do not have the correct permissions to activate the %s plugins. Contact the administrator of this site for help on getting the plugins activated.'), // %1$s = plugin name(s)
                'notice_ask_to_update' => _n_noop('The following plugin needs to be updated to its latest version to ensure maximum compatibility with this theme: %1$s.', 'The following plugins need to be updated to their latest version to ensure maximum compatibility with this theme: %1$s.'), // %1$s = plugin name(s)
                'notice_cannot_update' => _n_noop('Sorry, but you do not have the correct permissions to update the %s plugin. Contact the administrator of this site for help on getting the plugin updated.', 'Sorry, but you do not have the correct permissions to update the %s plugins. Contact the administrator of this site for help on getting the plugins updated.'), // %1$s = plugin name(s)
                'install_link' => _n_noop('Go to plugin instalation', ' Begin installing plugins'),
                'activate_link' => _n_noop('Go to activation panel', 'Activate installed plugins'),
                'return' => __('Return to tagDiv plugins panel', TD_THEME_NAME),
                'plugin_activated' => __('Plugin activated successfully.', TD_THEME_NAME),
                'complete' => __('All plugins installed and activated successfully. %s', TD_THEME_NAME), // %1$s = dashboard link
                'nag_type' => 'updated' // Determines admin notice type - can only be 'updated' or 'error'
            )
        );
        tgmpa(td_global::$theme_plugins_list, $config);


    }
}


/**
 * - wordpress filter hook used to switch single theme post types and custom post types, and also woocommerce single products
 * - we need to use 'template_include' and not 'single_template' (which runs just for single and before 'template_include'),
 * because of not using more than one hook for this split operation and because the $template_path is the path already
 * established by wordpress and woocommerce plugin
 */
add_filter( 'template_include', 'td_template_include_filter');
function td_template_include_filter( $template_path ) {

	if (is_single()
	    and (($template_path == TEMPLATEPATH . '/single.php')
	         or ($template_path == STYLESHEETPATH . '/single.php'))) {

		global $post;

		$td_post_theme_settings = get_post_meta($post->ID, 'td_post_theme_settings', true);

		if (empty($td_post_theme_settings['td_post_template'])) {

			$td_default_site_post_template = td_util::get_option('td_default_site_post_template');

			if (!empty($td_default_site_post_template)) {
				$td_post_theme_settings['td_post_template'] = $td_default_site_post_template;
			}
		}

		if (!empty($td_post_theme_settings['td_post_template'])) {

			//the user has selected a different template & we make sure we only load our templates - the list of allowed templates is in includes/wp_booster/td_global.php
			//td_api_single_template::_helper_show_single_template(td_global::$td_template_var['td_post_theme_settings']['td_post_template']);

			try {
				$template_id = $td_post_theme_settings['td_post_template'];
				$single_template_path = td_api_single_template::get_key($template_id, 'file');
			} catch (ErrorException $ex) {
				td_util::error(__FILE__, "The template $template_id isn't set. Did you disable a tagDiv plugin?"); // this does not stop execution
			}

			// load the template
			if (!empty($single_template_path) and file_exists($single_template_path)) {
				$template_path = $single_template_path;
			} else {
				td_util::error(__FILE__, "The path $single_template_path of the $template_id template not found. Did you disable a tagDiv plugin?");  // this does not stop execution
			}
		}

	} else if (td_global::$is_woocommerce_installed
	           and is_single()
               and (($template_path == TEMPLATEPATH . '/woocommerce/single-product.php')
                    or ($template_path == STYLESHEETPATH . '/woocommerce/single-product.php'))) {


		//echo 'SINGLE PRODUCT detected<br>';
	}

	return $template_path;
}