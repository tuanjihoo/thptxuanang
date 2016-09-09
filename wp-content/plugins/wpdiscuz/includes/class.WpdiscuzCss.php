<?php

if (!defined('ABSPATH')) {
    exit();
}

class WpdiscuzCss {

    private $optionsSerialized;
    private $helper;

    function __construct($optionsSerialized, $helper) {
        $this->optionsSerialized = $optionsSerialized;
        $this->helper = $helper;
    }

    /**
     * init wpdiscuz styles
     */
    public function initCustomCss() {
        global $post;
        if ($this->helper->isLoadWpdiscuz($post)) {
            ?>
<style type="text/css">#wpcomm .wc_new_comment{background:<?php echo $this->optionsSerialized->primaryColor; ?>;}#wpcomm .wc_new_reply{background:<?php echo $this->optionsSerialized->primaryColor; ?>;}#wpcomm .wc-form-wrapper{background:<?php echo isset($this->optionsSerialized->formBGColor)?$this->optionsSerialized->formBGColor:'#f9f9f9'; ?>;}#wpcomm select,#wpcomm input[type="text"],#wpcomm input[type="email"],#wpcomm input[type="url"]{border:<?php echo $this->optionsSerialized->inputBorderColor; ?> 1px solid;}#wpcomm .wc-comment .wc-comment-right{background:<?php echo $this->optionsSerialized->commentBGColor; ?>;}#wpcomm .wc-reply .wc-comment-right{background:<?php echo $this->optionsSerialized->replyBGColor; ?>;}#wpcomm .wc-comment-text{font-size:<?php echo isset($this->optionsSerialized->commentTextSize)?$this->optionsSerialized->commentTextSize:'14px'; ?>;color:<?php echo $this->optionsSerialized->commentTextColor; ?>;}<?php $blogRoles=$this->optionsSerialized->blogRoles;if(!$blogRoles){echo '.wc-comment-author a{color:#00B38F;} .wc-comment-label{background:#00B38F;}';}foreach($blogRoles as $role=>$color){echo '#wpcomm .wc-blog-'.$role.' > .wc-comment-right .wc-comment-author,#wpcomm .wc-blog-'.$role.' > .wc-comment-right .wc-comment-author a{color:'.$color.';}';echo '#wpcomm .wc-blog-'.$role.' > .wc-comment-left .wc-comment-label{background:'.$color.';}';}?>#wpcomm .wc-comment-footer a,#wpcomm .wc-comment-footer span.wc_editable_comment,#wpcomm .wc-comment-footer span.wc_save_edited_comment,#wpcomm span.wc_cancel_edit{color:<?php echo $this->optionsSerialized->voteReplyColor; ?>;}#wpcomm .wc-comment-footer .wc-vote-result{background:<?php echo $this->optionsSerialized->voteReplyColor; ?>;}#wpcomm .wc-reply-link,#wpcomm .wc-vote-link,#wpcomm .wc-share-link{color:<?php echo $this->optionsSerialized->voteReplyColor; ?>;}.wc-load-more-submit{border:1px solid <?php echo $this->optionsSerialized->inputBorderColor; ?>;}#wpcomm .wc-new-loaded-comment > .wc-comment-right{background:<?php echo $this->optionsSerialized->newLoadedCommentBGColor; ?>;}<?php echo stripslashes($this->optionsSerialized->customCss); ?>.wpdiscuz-front-actions{background:<?php echo isset($this->optionsSerialized->formBGColor)?$this->optionsSerialized->formBGColor:'#f9f9f9'; ?>;}.wpdiscuz-subscribe-bar{background:<?php echo isset($this->optionsSerialized->formBGColor)?$this->optionsSerialized->formBGColor : '#f9f9f9'; ?>;}.wpdiscuz-sort-buttons{color:<?php echo $this->optionsSerialized->voteReplyColor; ?>;}.wpdiscuz-sort-button{color:<?php echo $this->optionsSerialized->voteReplyColor; ?>; cursor:pointer;}.wpdiscuz-sort-button:hover{color:<?php echo $this->optionsSerialized->primaryColor; ?>;cursor:pointer;}.wpdiscuz-sort-button-active{color:<?php echo $this->optionsSerialized->primaryColor; ?>!important;cursor:default!important;}#wpcomm .page-numbers{color:<?php echo $this->optionsSerialized->commentTextColor; ?>;border:<?php echo $this->optionsSerialized->commentTextColor; ?> 1px solid;}#wpcomm span.current{background:<?php echo $this->optionsSerialized->commentTextColor; ?>;}#wpcomm .wpdiscuz-readmore{cursor:pointer;color:<?php echo $this->optionsSerialized->primaryColor; ?>;}<?php do_action('wpdiscuz_dynamic_css'); ?> #wpcomm .wpdiscuz-textarea-wrap{border:<?php echo $this->optionsSerialized->inputBorderColor; ?> 1px solid;}</style>
            <?php
        }
    }

}
?>