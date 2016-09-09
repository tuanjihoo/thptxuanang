<?php


class td_smart_list_5 extends td_smart_list {

    function render_table_of_contents_before($item_id_2_item_array) {
        $buffy = '';

        return $buffy;
    }

    function render_before_list_wrap() {
        if(td_global::$cur_single_template_sidebar_pos == 'no_sidebar') {
            $td_class_nr_of_columns = ' td-3-columns ';
        } else {
            $td_class_nr_of_columns = ' td-2-columns ';
        }

        $buffy = '';

        //wrapper with id for smart list wrapper type 5
        $buffy .= '<div class="td_smart_list_5' . $td_class_nr_of_columns . '">';

        return $buffy;
    }


    function render_list_item($item_array, $current_item_id, $current_item_number, $total_items_number) {
        //print_r($item_array);
        $buffy = '';

        //checking the width of the tile
        $smart_list_5_title = '';
        if(!empty($item_array['title'])) {
            if(mb_strlen($item_array['title'], 'UTF-8') > 55) {
                $smart_list_5_title = mb_substr($item_array['title'], 0, 55, 'UTF-8' ) . '...';
            } else {
                $smart_list_5_title = $item_array['title'];
            }
        }

        //creating each slide
        $buffy .= '<div class="td-item">';
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
                            <figure class="td-slide-smart-list-figure td-slide-smart-list-5">
                            <span class="td-sml-current-item-nr">' . $current_item_number. '</span>
                                <a class="td-sml-link-to-image" href="' . $first_img_all_info['src'] . '" data-caption="' . esc_attr($first_img_all_info['caption'], ENT_QUOTES) . '">
                                    <img src="' . $first_img_info[0] . '"/>
                                </a>
                                <figcaption class="td-sml-caption"><div>' . $first_img_caption . '</div></figcaption>
                            </figure>';
                }

            //title
            $buffy .= '<div class="td-number-and-title"><h2 class="td-sml-current-item-title">' . $smart_list_5_title . '</h2></div>';



             //adding description
                if(!empty($item_array['description'])) {
                    $buffy .= '<span class="td-sml-description">' . $item_array['description'] . '</span>';
                }

        $buffy .= '</div>';

        return $buffy;
    }


    function render_after_list_wrap() {
        $buffy = '';
        $buffy .= '</div>'; // /.td_smart_list_5  wrapper with id

        return $buffy;
    }
}