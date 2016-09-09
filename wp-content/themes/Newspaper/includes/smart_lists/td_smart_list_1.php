<?php


class td_smart_list_1 extends td_smart_list {
    //holds the unique id of current smart list slide
    private $smart_list_tip_1_unique_id;
    private $nr_slide_on_smart_list = 0;

    function render_table_of_contents_before($item_id_2_item_array) {
        // placeholder function, it's overwritten - do not delete
        $buffy = '';

        /*foreach ($item_id_2_item_array as $item_id => $item_name) {
            $buffy .= '<a href="#" class="td_history" data-history-id="' . get_permalink() . '/' .  $item_id . '">' . $item_name['title'] . '</a>';
            //$buffy .= '<a href="#' . $item_id . '">' . $item_name['title'] . '</a><br>';
        }*/

        return $buffy;
    }

    function render_before_list_wrap() {

        if(td_global::$cur_single_template_sidebar_pos == 'no_sidebar') {
            $td_class_nr_of_columns = ' td-3-columns ';
        } else {
            $td_class_nr_of_columns = ' td-2-columns ';
        }

        $buffy = '';

        //generate unique gallery slider id
        $this->smart_list_tip_1_unique_id = 'smart_list_tip1_' . td_global::td_generate_unique_id();

        //wrapper with id for smart list wrapper type 1
        $buffy .= '<div class="td_smart_list_1' . $td_class_nr_of_columns . '">';
            $buffy .= '<div class="td-controls"><a class="td-left-smart-list doubleSliderPrevButton" href="#" onclick="return false;"><i class = "td-icon-left"></i>' .  __td('Prev', TD_THEME_NAME). '</a><a class="td-right-smart-list doubleSliderNextButton" href="#" onclick="return false;">' .  __td('Next', TD_THEME_NAME). '<i class = "td-icon-right"></i></a></div>';
                $buffy .= '<div class="td-iosSlider td-smart-list-slider" id="' . $this->smart_list_tip_1_unique_id. '">';
                    $buffy .= '<div class ="td-slider">';


        return $buffy;
    }


    function render_list_item($item_array, $current_item_id, $current_item_number, $total_items_number) {
        //print_r($item_array);
        $buffy = '';

        //checking the width of the tile
        $smart_list_1_title = '';
        if(!empty($item_array['title'])) {
            if(mb_strlen($item_array['title'], 'UTF-8') > 55) {
                $smart_list_1_title = mb_substr($item_array['title'], 0, 55, 'UTF-8' ) . '...';
            } else {
                $smart_list_1_title = $item_array['title'];
            }
        }

        //creating each slide
        $buffy .= '<div class="td-item" id="' . $this->smart_list_tip_1_unique_id . '_item_' . $current_item_id . '">';
            $buffy .= '<div class="td-number-and-title"><h2 class="td-sml-current-item-title">' . $current_item_number . '. ' . $smart_list_1_title . '</h2></div>';

            //get image info
            $first_img_all_info = td_util::attachment_get_full_info($item_array['first_img_id']);

            //image caption
            $first_img_caption = '';
            if(!empty($first_img_all_info['caption'])) {
                $first_img_caption = $first_img_all_info['caption'];
            }

            if(td_global::$cur_single_template_sidebar_pos == 'no_sidebar') {
                $first_img_info = wp_get_attachment_image_src($item_array['first_img_id'], 'td_1068x0');
            } else {
                $first_img_info = wp_get_attachment_image_src($item_array['first_img_id'], 'td_696x0');
            }


            if (!empty($first_img_info[0])) {
                $buffy .= '
                        <figure class="td-slide-smart-list-figure td-slide-smart-list-1">
                            <a class="td-sml-link-to-image" href="' . $first_img_all_info['src'] . '" id="td-sml1-slide_' . $this->nr_slide_on_smart_list . '" data-caption="' . esc_attr($first_img_all_info['caption'], ENT_QUOTES) . '">
                                <img src="' . $first_img_info[0] . '" />
                            </a>
                            <figcaption class="td-sml-caption"><div>' . $first_img_caption . '</div></figcaption>
                        </figure>
                        ';
            }

            //adding description
            if(!empty($item_array['description'])) {
                $buffy .= '<span class="td-sml-description">' . $item_array['description'] . '</span>';
            }
        $buffy .= '</div>';

        $this->nr_slide_on_smart_list++;

        return $buffy;
    }


    function render_after_list_wrap() {
        $buffy = '';
                $buffy .= '</div>';
            $buffy .= '</div>'; // end ios slider
        $buffy .= '</div>'; // /.td_smart_list_1  wrapper with id

        // @todo fix the moving from left to right from the controls, now the slide only works from right to left,
        td_js_buffer::add_to_footer('

jQuery(document).ready(function() {
    jQuery("#' . $this->smart_list_tip_1_unique_id . '").iosSlider({
        snapToChildren: true,
        desktopClickDrag: true,
        startAtSlide:td_history.get_current_page("slide"),
        keyboardControls: false,
        infiniteSlider: true,
        navPrevSelector: jQuery(".td_smart_list_1 .doubleSliderPrevButton"),
        navNextSelector: jQuery(".td_smart_list_1 .doubleSliderNextButton"),
        onSliderLoaded : td_resize_smartlist_slides,
		onSliderResize : td_resize_smartlist_sliders_and_update,
		onSlideChange : td_resize_smartlist_slides,
		onSlideComplete : td_history.slide_changed_callback,
		snapVelocityThreshold:550,
		slideStartVelocityThreshold:5,
		horizontalSlideLockThreshold:100
    });

    // add current page history
    td_history.replace_history_entry({current_slide:td_history.get_current_page("slide"), slide_id:"' . $this->smart_list_tip_1_unique_id . '"});
});
    ');



        return $buffy;
    }
}