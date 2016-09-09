<?php
if (!defined('ABSPATH')) {
    exit();
}
?>
<div>
    <h2 style="padding:5px 10px 10px 10px; margin:0px;"><?php _e('Background and Colors', 'wpdiscuz'); ?></h2>
    <table class="wp-list-table widefat plugins" style="margin-top:10px; border:none;">
        <tbody>            
            <tr valign="top">
                <th colspan="2">
                    <span class="wpdiscuz-option-title"><?php _e('Comment Form Background Color', 'wpdiscuz'); ?></span>
                </th>
                <td>
                    <?php $formBGColor = isset($this->optionsSerialized->formBGColor) ? $this->optionsSerialized->formBGColor : '#F9F9F9'; ?>
                    <input type="text" class="wpdiscuz-color-picker regular-text" value="<?php echo $formBGColor; ?>" id="wc_form_bg_color" name="wc_form_bg_color" placeholder="<?php _e('Example: #00FF00', 'wpdiscuz'); ?>"/>                    
                </td>                
            </tr>
            <tr valign="top">
                <th colspan="2">
                    <span class="wpdiscuz-option-title"><?php _e('Comment Background Color', 'wpdiscuz'); ?></span>
                </th>
                <td>
                    <?php $commentBGColor = isset($this->optionsSerialized->commentBGColor) ? $this->optionsSerialized->commentBGColor : '#FEFEFE'; ?>
                    <input type="text" class="wpdiscuz-color-picker regular-text" value="<?php echo $commentBGColor; ?>" id="wc_comment_bg_color" name="wc_comment_bg_color" placeholder="<?php _e('Example: #00FF00', 'wpdiscuz'); ?>"/>
                </td>
            </tr>
            <tr valign="top">
                <th colspan="2">
                    <span class="wpdiscuz-option-title"><?php _e('Reply Background Color', 'wpdiscuz'); ?></span>
                </th>
                <td>
                    <?php $replyBGColor = isset($this->optionsSerialized->replyBGColor) ? $this->optionsSerialized->replyBGColor : '#F8F8F8'; ?>
                    <input type="text" class="wpdiscuz-color-picker regular-text" value="<?php echo $replyBGColor; ?>" id="wc_reply_bg_color" name="wc_reply_bg_color" placeholder="<?php _e('Example: #00FF00', 'wpdiscuz'); ?>"/>
                </td>                
            </tr>
            <tr valign="top">
                <th colspan="2">
                    <span class="wpdiscuz-option-title"><?php _e('Comment Text Color', 'wpdiscuz'); ?></span>
                </th>
                <td>
                    <?php $commentTextColor = isset($this->optionsSerialized->commentTextColor) ? $this->optionsSerialized->commentTextColor : '#555555'; ?>
                    <input type="text" class="wpdiscuz-color-picker regular-text" value="<?php echo $commentTextColor; ?>" id="wc_comment_text_color" name="wc_comment_text_color" placeholder="<?php _e('Example: #00FF00', 'wpdiscuz'); ?>"/>
                </td>
            </tr>
            <tr valign="top">
                <th colspan="2">
                    <span class="wpdiscuz-option-title"><?php _e('Vote, Reply, Share, Edit links text colors', 'wpdiscuz'); ?></span>
                </th>
                <td>
                    <?php $voteReplyColor = isset($this->optionsSerialized->voteReplyColor) ? $this->optionsSerialized->voteReplyColor : '#666666'; ?>
                    <input type="text" class="wpdiscuz-color-picker regular-text" value="<?php echo $voteReplyColor; ?>" id="wc_vote_reply_color" name="wc_vote_reply_color" placeholder="<?php _e('Example: #00FF00', 'wpdiscuz'); ?>"/>
                </td>
            </tr>
            <tr valign="top">
                <th colspan="2">
                    <span class="wpdiscuz-option-title"><?php _e('Comment form fields border color', 'wpdiscuz'); ?></span>
                </th>
                <td>
                    <?php $inputBorderColor = isset($this->optionsSerialized->inputBorderColor) ? $this->optionsSerialized->inputBorderColor : '#D9D9D9'; ?>
                    <input type="text" class="wpdiscuz-color-picker regular-text" value="<?php echo $inputBorderColor; ?>" id="wc_input_border_color" name="wc_input_border_color" placeholder="<?php _e('Example: #00FF00', 'wpdiscuz'); ?>"/>
                </td>
            </tr>
            <tr valign="top">
                <th colspan="2">
                    <span class="wpdiscuz-option-title"><?php _e('New loaded comments\' background color', 'wpdiscuz'); ?></span>
                </th>
                <td>
                    <?php $newLoadedCommentBGColor = isset($this->optionsSerialized->newLoadedCommentBGColor) ? $this->optionsSerialized->newLoadedCommentBGColor : '#FEFEFE'; ?>
                    <input type="text" class="wpdiscuz-color-picker regular-text" value="<?php echo $newLoadedCommentBGColor; ?>" id="wc_new_loaded_comment_bg_color" name="wc_new_loaded_comment_bg_color" placeholder="<?php _e('Example: #00FF00', 'wpdiscuz'); ?>"/>
                </td>
            </tr>
            <tr valign="top">
                <th colspan="2">
                    <span class="wpdiscuz-option-title"><?php _e('Primary Color', 'wpdiscuz'); ?></span>
                </th>
                <td>
                    <?php $primaryColor = isset($this->optionsSerialized->primaryColor) ? $this->optionsSerialized->primaryColor : '#00B38F'; ?>
                    <input type="text" class="wpdiscuz-color-picker regular-text" value="<?php echo $primaryColor; ?>" id="wc_comment_username_color" name="wc_comment_username_color" placeholder="<?php _e('Example: #00FF00', 'wpdiscuz'); ?>"/>
                </td>
            </tr>
            <?php
            $blogRoles = $this->optionsSerialized->blogRoles;
            foreach ($blogRoles as $roleName => $color) {
                $blogRoleColor = isset($this->optionsSerialized->blogRoles[$roleName]) ? $this->optionsSerialized->blogRoles[$roleName] : '#00B38F';
                ?>
                <tr valign="top">
                    <th colspan="2">
                        <span class="wpdiscuz-option-title"><?php echo '<span style="font-weight:bold;color:' . $blogRoleColor . ';">' . ucfirst(str_replace('_', ' ', $roleName)) . '</span> ' . __('label color', 'wpdiscuz'); ?></span>
                    </th>
                    <td>                        
                        <input type="text" class="wpdiscuz-color-picker regular-text" value="<?php echo $blogRoleColor; ?>" id="wc_blog_roles_<?php echo $roleName; ?>" name="wc_blog_roles[<?php echo $roleName; ?>]" placeholder="<?php _e('Example: #00FF00', 'wpdiscuz'); ?>"/>
                    </td>
                </tr>
                <?php
            }
            ?>
            <tr valign="top">
                <th scope="row" colspan="2">
                    <label for="disableFontAwesome"><?php _e('Disable font awesome css loading', 'wpdiscuz'); ?></label>
                </th>
                <td>                    
                    <input type="checkbox" <?php checked($this->optionsSerialized->disableFontAwesome == 1) ?> value="1" name="disableFontAwesome" id="disableFontAwesome" />                    
                </td>
            </tr>
            <tr valign="top">
                <th colspan="2">
                    <span class="wpdiscuz-option-title"><?php _e('Custom CSS Code', 'wpdiscuz'); ?></span>
                </th>
                <td>
                    <textarea cols="40" rows="8" class="regular-text" id="wc_custom_css" name="wc_custom_css" placeholder=""><?php echo stripslashes($this->optionsSerialized->customCss); ?></textarea>
                </td>   
            </tr>           
        </tbody>
    </table>
</div>
