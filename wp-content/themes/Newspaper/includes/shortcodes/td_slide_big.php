<?php


class td_slide_big extends td_block {
    public $td_create_slider;

    function __construct() {
        $this->block_id = 'td_slide_big';
        add_shortcode('td_slide_big', array($this, 'render'));
    }


    function render($atts) {
        $this->block_uid = td_global::td_generate_unique_id(); //update unique id on each render

        //global $post;
        extract(shortcode_atts(
            array(
                'limit' => 4,
                'sort' => '',
                'category_id' => '',
                'category_ids' => '',
                'tag_slug' => '',
                'force_columns' => '',
                'autoplay' => ''
            ),$atts));

        //echo $atts['limit'];
        //$atts['limit'] = 4;//set 4 to remove the slide

        $buffy = ''; //output buffer
        $id_slider = '';
        $class_iosSlider = '';
        $this->td_create_slider = 0;


        //if there are more then 4 images then make big grid a slide
        if($atts['limit'] > 4) {
            $this->td_create_slider = 1;
            $id_slider = 'id="iosSlider_' . $this->block_uid . '"';
            $class_iosSlider = 'iosSlider';
        }

        $buffy .= '<div class="'. $class_iosSlider .' td_block_wrap td_block_big_grid" '. $id_slider .'>';
        $buffy .= '<div class="slider td_block_inner" id="' . $this->block_uid . '">';



        //do the query
        $td_query = td_data_source::get_wp_query($atts); //by ref  do the query



        //get the js for this block - last parm is block id
        $buffy .= $this->get_block_js($atts, $td_query);

        //$buffy .= '<div class="td_block_wrap td_block_big_grid">';

        //get the sub category filter for this block
        $buffy .= $this->get_block_sub_cats($atts);

        //$buffy .= '<div id=' . $this->block_uid . ' class="td_block_inner">';

        //inner content of the block
        $buffy .= $this->inner($td_query->posts, $force_columns);

        //$buffy .= '</div>';

        //$buffy .= '</div> <!-- ./td_block_big_grid -->';
        $buffy .= '</div>';//end slider (if slider)

        if($this->td_create_slider == 1) {
            $buffy .= '<i class = "td-icon-left"></i>';
            $buffy .= '<i class = "td-icon-right"></i>';
        }

        $buffy .= '</div>';//end iosSlider (if slider)

        //close the ios slider wrappers
        if($this->td_create_slider == 1) {

            //autoplay option for
            $autoplay_string = '';
            if(!empty($autoplay)) {
                $autoplay_string = 'autoSlide: true,
                            autoSlideTimer: ' . $autoplay * 1000 . ',';
            }

            $slide_javascript = '
                    jQuery(document).ready(function() {
                        jQuery("#iosSlider_' . $this->block_uid . '").iosSlider({
                            snapToChildren: true,
                            desktopClickDrag: true,
                            keyboardControls: false,
                            responsiveSlides: true,
                            infiniteSlider: true,
                            ' . $autoplay_string . '
                            navPrevSelector: jQuery("#iosSlider_' . $this->block_uid . ' .td-icon-left"),
                            navNextSelector: jQuery("#iosSlider_' . $this->block_uid . ' .td-icon-right"),
                            onSliderLoaded : td_resize_page_sliders,
		                    onSliderResize : td_resize_big_grid_update_vars_' . $this->block_uid . ',//td_resize_slide,
		                    onSlideChange : td_resize_slide
                        });
                    });


                    /*
                    * Resize the iosSlider when the page is resided (fixes bug on Android devices)
                    * For big grid the code runs on all devices because the big slider changes its height
                    * and we change the td_resize_slide function with this one
                    */
                    function td_resize_big_grid_update_vars_' . $this->block_uid . '(args) {
                        //if(td_detect.is_android) {
                            setTimeout(function(){
                                jQuery("#iosSlider_' . $this->block_uid . '").iosSlider("update");
                            }, 1000);
                        //}
                    }
            ';

            td_js_buffer::add_to_footer($slide_javascript);
        }

        return $buffy;
    }

    function inner($posts, $td_column_number = '') {
        //global $post;
        $buffy = '';

        $td_block_layout = new td_block_layout();

        if (empty($td_column_number)) {
            $td_column_number = $td_block_layout->get_column_number(); // get the column width of the block
        }

//echo $td_column_number;
        if ($td_column_number == 3 and !empty($posts)) {
            //$td_module_big_grid = new td_module_big_grid();
            $td_module_big_grid = new td_module_slide_big();

            $buffy .= $td_module_big_grid->render(
                array($posts,
                    $this->td_create_slider,
                    'iosSlider_' . $this->block_uid
                )
            );
        }

        $buffy .= $td_block_layout->close_all_tags();
        return $buffy;
    }

    function get_map() {
        //get the generic filter array
        $generic_filter_array = td_generic_filter_array::get_array();

        unset($generic_filter_array[5]);

        //add custom filter fields to generic filter array
        array_push ($generic_filter_array,
            array(
                "param_name" => "autoplay",
                "type" => "textfield",
                "value" => '',
                "heading" => 'Autoplay slider (at x seconds)',
                "description" => "Leave empty do disable autoplay",
                "holder" => "div",
                "class" => ""
            ),
            array(
                "param_name" => "limit",
                "type" => "textfield",
                "value" => __("4", TD_THEME_NAME),
                "heading" => __("Limit post number:", TD_THEME_NAME),
                "description" => "",
                "holder" => "div",
                "class" => ""
            ),
            array(

                "param_name" => "offset",
                "type" => "textfield",
                "value" => __("", TD_THEME_NAME),
                "heading" => __("Offset posts:", TD_THEME_NAME),
                "description" => "",
                "holder" => "div",
                "class" => ""
            )
        );

        return array(
            "name" => __("Big Slide", TD_THEME_NAME),
            "base" => "td_slide_big",
            "class" => "td_slide_big",
            "controls" => "full",
            "category" => __('Blocks', TD_THEME_NAME),
            'icon' => 'icon-pagebuilder-slide_big',
            "params" => $generic_filter_array
        );

    }
}

td_global_blocks::add_instance('Slide big', new td_slide_big());