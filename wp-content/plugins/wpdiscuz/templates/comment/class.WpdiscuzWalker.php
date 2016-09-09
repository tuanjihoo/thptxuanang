<?php

/** COMMENTS WALKER */
class WpdiscuzWalker extends Walker_Comment {

    public $tree_type = 'comment';
    public $db_fields = array('parent' => 'comment_parent', 'id' => 'comment_ID');
    private $helper;
    private $optimizationHelper;
    private $dbManager;
    private $optionsSerialized;

    public function __construct($helper, $optimizationHelper, $dbManager, $optionsSerialized) {
        $this->helper = $helper;
        $this->optimizationHelper = $optimizationHelper;
        $this->dbManager = $dbManager;
        $this->optionsSerialized = $optionsSerialized;
    }

    /** START_EL */
    public function start_el(&$output, $comment, $depth = 0, $args = array(), $id = 0) {
        $depth++;
        $GLOBALS['comment_depth'] = $depth;
        $GLOBALS['comment'] = $comment;
        // BEGIN
        $current_user = $args['current_user'];
        $depth = isset($args['addComment']) ? $args['addComment'] : $depth;
        $uniqueId = $comment->comment_ID . '_' . $comment->comment_parent;
        $commentContent = $comment->comment_content;
        $commentWrapperClass = '';
        $commentContent = wp_kses($commentContent, $this->helper->wc_allowed_tags);
        $commentContent = apply_filters('wpdiscuz_before_comment_text', $commentContent, $comment);
        if ($this->optionsSerialized->enableImageConversion) {
            $commentContent = $this->helper->makeClickable($commentContent);
        }
        $commentContent = apply_filters('comment_text', $commentContent, $comment, $args);
        if ($this->optionsSerialized->commentReadMoreLimit && count(explode(' ', strip_tags($commentContent))) > $this->optionsSerialized->commentReadMoreLimit) {
            $commentContent = $this->helper->getCommentExcerpt($commentContent, $uniqueId);
        }
        $commentContent .= $comment->comment_approved == 0 ? '<p class="wc_held_for_moderate">' . $this->optionsSerialized->phrases['wc_held_for_moderate'] . '</p>' : '';

        $hideAvatarStyle = $this->optionsSerialized->wordpressShowAvatars ? '' : 'style = "margin-left : 0;"';
        if ($this->optionsSerialized->wordpressIsPaginate && $comment->comment_parent) {
            $rootComment = $this->optimizationHelper->getCommentRoot($comment->comment_parent);
        }
        if (isset($args['new_loaded_class'])) {
            $commentWrapperClass .= $args['new_loaded_class'] . ' ';
            if ($args['isSingle']) {
                $commentWrapperClass .= ' wpdiscuz_single ';
            } else {
                $depth = $this->optimizationHelper->getCommentDepth($comment->comment_ID);
            }
        }

        $commentAuthorUrl = ('http://' == $comment->comment_author_url) ? '' : $comment->comment_author_url;
        $commentAuthorUrl = esc_url($commentAuthorUrl, array('http', 'https'));
        $commentAuthorUrl = apply_filters('get_comment_author_url', $commentAuthorUrl, $comment->comment_ID, $comment);

        if (isset($this->optionsSerialized->isUserByEmail) && $this->optionsSerialized->isUserByEmail) {
            $user = get_user_by('email', $comment->comment_author_email);
        } else {
            $user = $comment->user_id ? get_user_by('id', $comment->user_id) : null;
        }

        if ($user) {
            $authorName = $user->display_name ? $user->display_name : $comment->comment_author;
            $authorAvatarField = $user->ID;
            $profileUrl = in_array($user->ID, $args['posts_authors']) ? get_author_posts_url($user->ID) : '';
            $commentAuthorUrl = $commentAuthorUrl ? $commentAuthorUrl : $user->user_url;
            if ($user->ID == $args['post_author']) {
                $authorClass = 'wc-blog-post_author';
                $author_title = $this->optionsSerialized->phrases['wc_blog_role_post_author'];
            } else {
                $authorClass = 'wc-blog-guest';
                $author_title = $this->optionsSerialized->phrases['wc_blog_role_guest'];
                $blogRoles = $this->optionsSerialized->blogRoles;
                if ($blogRoles) {
                    if ($user->roles && is_array($user->roles)) {
                        foreach ($user->roles as $role) {
                            if (array_key_exists($role, $blogRoles)) {
                                $authorClass = 'wc-blog-' . $role;
                                $author_title = $this->optionsSerialized->phrases['wc_blog_role_' . $role];
                                break;
                            }
                        }
                    }
                }
            }
        } else {
            $authorName = $comment->comment_author ? $comment->comment_author : $this->optionsSerialized->phrases['wc_anonymous'];
            $authorAvatarField = $comment->comment_author_email;
            $profileUrl = '';
            $authorClass = 'wc-blog-guest';
            $author_title = $this->optionsSerialized->phrases['wc_blog_role_guest'];
        }

        if ($this->optionsSerialized->simpleCommentDate) {
            $dateFormat = $this->optionsSerialized->wordpressDateFormat;
            $timeFormat = $this->optionsSerialized->wordpressTimeFormat;
            if (wpdiscuzHelper::isPostedToday($comment)) {
                $posted_date = $this->optionsSerialized->phrases['wc_posted_today_text'] . ' ' . mysql2date($timeFormat, $comment->comment_date);
            } else {
                $posted_date = get_comment_date($dateFormat . ' ' . $timeFormat, $comment->comment_ID);
            }
        } else {
            $posted_date = $this->helper->dateDiff(time(), strtotime($comment->comment_date_gmt), 2);
        }

        $replyText = $this->optionsSerialized->phrases['wc_reply_text'];
        $shareText = $this->optionsSerialized->phrases['wc_share_text'];
        if (isset($rootComment) && $rootComment->comment_approved != 1) {
            $commentWrapperClass .= 'wc-comment';
        } else {
            $commentWrapperClass .= ($comment->comment_parent && $this->optionsSerialized->wordpressThreadComments) ? 'wc-comment wc-reply' : 'wc-comment';
        }

        $authorName = apply_filters('wpdiscuz_comment_author', $authorName, $comment);
        $profileUrl = apply_filters('wpdiscuz_profile_url', $profileUrl, $user);
        $authorAvatarField = apply_filters('wpdiscuz_author_avatar_field', $authorAvatarField, $comment, $user, $profileUrl);
        $authorAvatar = $this->optionsSerialized->wordpressShowAvatars ? get_avatar($authorAvatarField, 48, '', $authorName) : '';
        $trackOrPingback = $comment->comment_type == 'pingback' || $comment->comment_type == 'trackback' ? true : false;
        if ($trackOrPingback) {
            $authorAvatar = '<img class="avatar avatar-48 photo" width="48" height="48" src="' . plugins_url(WPDISCUZ_DIR_NAME . '/assets/img/trackback.png') . '" alt="trackback">';
        }

        if ($profileUrl) {
            $commentAuthorAvatar = "<a href='$profileUrl' target='_blank'>$authorAvatar</a>";
        } else {
            $commentAuthorAvatar = $authorAvatar;
        }

        if ($commentAuthorUrl) {
            $authorName = "<a rel='nofollow' href='$commentAuthorUrl' target='_blank'>$authorName</a>";
        } else if ($profileUrl) {
            $authorName = "<a rel='nofollow' href='$profileUrl' target='_blank'>$authorName</a>";
        }

        if (!$this->optionsSerialized->isGuestCanVote && !$current_user->ID) {
            $voteClass = ' wc_tooltipster';
            $voteTitleText = $this->optionsSerialized->phrases['wc_login_to_vote'];
            $voteUp = $voteTitleText;
            $voteDown = $voteTitleText;
        } else {
            $voteClass = ' wc_vote wc_not_clicked wc_tooltipster';
            $voteUp = $this->optionsSerialized->phrases['wc_vote_up'];
            $voteDown = $this->optionsSerialized->phrases['wc_vote_down'];
        }

        $commentContentClass = '';
        // begin printing comment template
        $output .= '<div id="wc-comm-' . $uniqueId . '" class="' . $commentWrapperClass . ' ' . $authorClass . ' wc_comment_level-' . $depth . '">';
        if ($this->optionsSerialized->wordpressShowAvatars) {
            $output .= '<div class="wc-comment-left">' . $commentAuthorAvatar;
            if (!$this->optionsSerialized->authorTitlesShowHide && !$trackOrPingback) {
                $output .= '<div class="' . $authorClass . ' wc-comment-label">' . $author_title . '</div>';
            }
            $afterLabelHtml = apply_filters('wpdiscuz_after_label', $afterLabelHtml = '', $comment);
            $output .= $afterLabelHtml;
            $output .= '</div>';
        }

        $commentLink = get_comment_link($comment);
        $output .= '<div id="comment-' . $comment->comment_ID . '" class="wc-comment-right ' . $commentContentClass . '" ' . $hideAvatarStyle . '>';
        $output .= '<div class="wc-comment-header">';
        $output .= '<div class="wc-comment-author">' . $authorName . '</div>';
        if (!$this->optionsSerialized->showHideCommentLink) {
            $commentLinkImg = '<img src="' . plugins_url(WPDISCUZ_DIR_NAME . '/assets/img/icon-link.gif') . '" class="wc-comment-img-link" title="&lt;input type=&quot;text&quot; class=&quot;wc-comment-link-input&quot; value=&quot;' . $commentLink . '&quot; /&gt;" />';
            $output .= '<div class="wc-comment-link">';
            $output .= apply_filters('wpdiscuz_comment_link_img', $commentLinkImg, $comment);
            $output .= '</div>';
        }
        $output = apply_filters('wpdiscuz_after_comment_link', $output, $comment);
        $output .= '<div class="wc-comment-date">' . $posted_date . '</div><div class="wpdiscuz_clear_right"></div>';
        $output .= '</div>';
        $output .= '<div class="wc-comment-text">' . $commentContent . '</div>';
        $output = apply_filters('wpdiscuz_after_comment_text', $output, $comment);
        if (isset($args['comment_status']) && is_array($args['comment_status']) && in_array($comment->comment_approved, $args['comment_status'])) {
            $output .= '<div class="wc-comment-footer">';
            if (!$this->optionsSerialized->votingButtonsShowHide) {
                if ($this->optionsSerialized->votingButtonsStyle) {
                    $votesArr = $this->dbManager->getVotes($comment->comment_ID);
                    if ($votesArr && count($votesArr) == 1) {
                        $like = 0;
                        $dislike = 0;
                    } else {
                        $like = isset($votesArr[0]) ? intval($votesArr[0]) : 0;
                        $dislike = isset($votesArr[1]) ? intval($votesArr[1]) : 0;
                    }
                    $output .= '<div class="wc-vote-result wc-vote-result-like'.(($like)?' wc-positive':'').'">' . $like . '</div>';
                    $output .= '<span class="wc-vote-link wc-up wc-separate ' . $voteClass . '" title="' . $voteUp . '">';
                    $voteFaUpImg = '<i class="fa fa-thumbs-up fa-flip-horizontal wc-vote-img-up"></i>';
                    $output .= apply_filters('wpdiscuz_vote_up_icon', $voteFaUpImg, $comment, $current_user);
                    $output .= '</span> &nbsp;|&nbsp; ';
                    $output .= '<span class="wc-vote-link wc-down wc-separate' . $voteClass . '" title="' . $voteDown . '">';
                    $voteFaDownImg = '<i class="fa fa-thumbs-down wc-vote-img-down"></i>';
                    $output .= apply_filters('wpdiscuz_vote_down_icon', $voteFaDownImg, $comment, $current_user);
                    $output .= '</span>';
                    $output .= '<div class="wc-vote-result wc-vote-result-dislike'.(($dislike)?' wc-negative':'').'">' . $dislike . '</div>';
                    $output = apply_filters('wpdiscuz_voters', $output, $uniqueId, $comment, $user, $current_user);
                } else {
                    $voteCount = isset($comment->meta_value) ? $comment->meta_value : get_comment_meta($comment->comment_ID, WpdiscuzCore::META_KEY_VOTES, true);
                    $output .= '<div class="wc-vote-result">' . intval($voteCount) . '</div>';
                    $output = apply_filters('wpdiscuz_voters', $output, $uniqueId, $comment, $user, $current_user);
                    $output .= '<span class="wc-vote-link wc-up ' . $voteClass . '" title="' . $voteUp . '">';
                    $voteFaUpImg = '<i class="fa fa-thumbs-up fa-flip-horizontal wc-vote-img-up"></i>';
                    $output .= apply_filters('wpdiscuz_vote_up_icon', $voteFaUpImg, $comment, $current_user);
                    $output .= '</span> &nbsp;|&nbsp; ';
                    $output .= '<span class="wc-vote-link wc-down ' . $voteClass . '" title="' . $voteDown . '">';
                    $voteFaDownImg = '<i class="fa fa-thumbs-down wc-vote-img-down"></i>';
                    $output .= apply_filters('wpdiscuz_vote_down_icon', $voteFaDownImg, $comment, $current_user);
                    $output .= '</span>&nbsp;';
                }
            }

            if (comments_open($comment->comment_post_ID) && $this->optionsSerialized->wordpressThreadComments) {
                if ($this->optionsSerialized->wordpressCommentRegistration) {
                    if (!$this->optionsSerialized->replyButtonMembersShowHide && $current_user->ID) {
                        $output .= '&nbsp;&nbsp;<span  class="wc-reply-link" title="' . $replyText . '">' . $replyText . '</span> &nbsp;&nbsp;';
                    } else if (in_array('administrator', $current_user->roles)) {
                        $output .= '&nbsp;&nbsp;<span  class="wc-reply-link" title="' . $replyText . '">' . $replyText . '</span> &nbsp;&nbsp;';
                    }
                } else {
                    if (!$this->optionsSerialized->replyButtonMembersShowHide && !$this->optionsSerialized->replyButtonGuestsShowHide) {
                        $output .= '&nbsp;&nbsp;<span class="wc-reply-link" title="' . $replyText . '">' . $replyText . '</span> &nbsp;&nbsp;';
                    } else if (!$this->optionsSerialized->replyButtonMembersShowHide && $current_user->ID) {
                        $output .= '&nbsp;&nbsp;<span class="wc-reply-link" title="' . $replyText . '">' . $replyText . '</span> &nbsp;&nbsp;';
                    } else if (!$this->optionsSerialized->replyButtonGuestsShowHide && !$current_user->ID) {
                        $output .= '&nbsp;&nbsp;<span class="wc-reply-link" title="' . $replyText . '">' . $replyText . '</span> &nbsp;&nbsp;';
                    } else if (in_array('administrator', $current_user->roles)) {
                        $output .= '&nbsp;&nbsp;<span class="wc-reply-link" title="' . $replyText . '">' . $replyText . '</span> &nbsp;&nbsp;';
                    }
                }
            }

            if ($this->optionsSerialized->shareButtons) {
                $output .= '-&nbsp;&nbsp; <span class="wc-share-link" title="' . $shareText . '">' . $shareText . '</span> &nbsp;&nbsp;';
                $twitt_content = strip_tags($commentContent) . ' ' . $commentLink;
                $output .= '<span class="share_buttons_box">';
                $output .= in_array('fb', $this->optionsSerialized->shareButtons) ? '<a class="wc_tooltipster" target="_blank" href="http://www.facebook.com/sharer.php" title="' . $this->optionsSerialized->phrases['wc_share_facebook'] . '"><img src="' . plugins_url(WPDISCUZ_DIR_NAME . '/assets/img/social-icons/fb-18x18.png') . '" onmouseover="this.src=\'' . plugins_url(WPDISCUZ_DIR_NAME . '/assets/img/social-icons/fb-18x18-orig.png') . '\'" onmouseout="this.src=\'' . plugins_url(WPDISCUZ_DIR_NAME . '/assets/img/social-icons/fb-18x18.png') . '\'"/></a>&nbsp;&nbsp;' : '';
                $output .= in_array('twitter', $this->optionsSerialized->shareButtons) ? '<a class="wc_tooltipster" target="_blank" href="https://twitter.com/home?status=' . $twitt_content . '" title="' . $this->optionsSerialized->phrases['wc_share_twitter'] . '"><img src="' . plugins_url(WPDISCUZ_DIR_NAME . '/assets/img/social-icons/twitter-18x18.png') . '" onmouseover="this.src=\'' . plugins_url(WPDISCUZ_DIR_NAME . '/assets/img/social-icons/twitter-18x18-orig.png') . '\'" onmouseout="this.src=\'' . plugins_url(WPDISCUZ_DIR_NAME . '/assets/img/social-icons/twitter-18x18.png') . '\'"/></a>&nbsp;&nbsp;' : '';
                $output .= in_array('google', $this->optionsSerialized->shareButtons) ? '<a class="wc_tooltipster" target="_blank" href="https://plus.google.com/share?url=' . get_permalink($comment->comment_post_ID) . '" title="' . $this->optionsSerialized->phrases['wc_share_google'] . '"><img src="' . plugins_url(WPDISCUZ_DIR_NAME . '/assets/img/social-icons/google-18x18.png') . '" onmouseover="this.src=\'' . plugins_url(WPDISCUZ_DIR_NAME . '/assets/img/social-icons/google-18x18-orig.png') . '\'" onmouseout="this.src=\'' . plugins_url(WPDISCUZ_DIR_NAME . '/assets/img/social-icons/google-18x18.png') . '\'"/></a>&nbsp;&nbsp;' : '';
                $output .= in_array('vk', $this->optionsSerialized->shareButtons) ? '<a class="wc_tooltipster" target="_blank" href="http://vk.com/share.php?url=' . get_permalink($comment->comment_post_ID) . '" title="' . $this->optionsSerialized->phrases['wc_share_vk'] . '"><img src="' . plugins_url(WPDISCUZ_DIR_NAME . '/assets/img/social-icons/vk-18x18.png') . '" onmouseover="this.src=\'' . plugins_url(WPDISCUZ_DIR_NAME . '/assets/img/social-icons/vk-18x18-orig.png') . '\'" onmouseout="this.src=\'' . plugins_url(WPDISCUZ_DIR_NAME . '/assets/img/social-icons/vk-18x18.png') . '\'"/></a>&nbsp;&nbsp;' : '';
                $output .= in_array('ok', $this->optionsSerialized->shareButtons) ? '<a class="wc_tooltipster" target="_blank" href="http://www.odnoklassniki.ru/dk?st.cmd=addShare&st.s=1&st._surl=' . get_permalink($comment->comment_post_ID) . '" title="' . $this->optionsSerialized->phrases['wc_share_ok'] . '"><img src="' . plugins_url(WPDISCUZ_DIR_NAME . '/assets/img/social-icons/ok-18x18.png') . '" onmouseover="this.src=\'' . plugins_url(WPDISCUZ_DIR_NAME . '/assets/img/social-icons/ok-18x18-orig.png') . '\'" onmouseout="this.src=\'' . plugins_url(WPDISCUZ_DIR_NAME . '/assets/img/social-icons/ok-18x18.png') . '\'"/></a>&nbsp;&nbsp;' : '';
                $output .= '</span>';
            }

            if (current_user_can('edit_comment', $comment->comment_ID)) {
                $output .= '<span class="wc_editable_comment">-&nbsp;&nbsp;' . $this->optionsSerialized->phrases['wc_edit_text'] . '</span>';
                $output .= '<span class="wc_cancel_edit">-&nbsp;&nbsp;' . $this->optionsSerialized->phrases['wc_comment_edit_cancel_button'] . '</span>';
                $output .= '<span class="wc_save_edited_comment" style="display:none;">&nbsp;&nbsp;-&nbsp;&nbsp;' . $this->optionsSerialized->phrases['wc_comment_edit_save_button'] . '</span>';
            } else {
                $isInRange = $this->helper->isContentInRange($commentContent);
                $isEditable = $this->optionsSerialized->commentEditableTime == 'unlimit' ? true && $isInRange : $this->helper->isCommentEditable($comment) && $isInRange;
                if ($current_user && $current_user->ID && $current_user->ID == $comment->user_id && $isEditable) {
                    $output .= '<span class="wc_editable_comment">-&nbsp;&nbsp;' . $this->optionsSerialized->phrases['wc_edit_text'] . '</span>';
                    $output .= '<span class="wc_cancel_edit">-&nbsp;&nbsp;' . $this->optionsSerialized->phrases['wc_comment_edit_cancel_button'] . '</span>';
                    $output .= '<span class="wc_save_edited_comment" style="display:none;">&nbsp;&nbsp;-&nbsp;&nbsp;' . $this->optionsSerialized->phrases['wc_comment_edit_save_button'] . '</span>';
                }
            }

            $output = apply_filters('wpdiscuz_comment_buttons', $output, $comment, $user, $current_user);

            if ($depth < $this->optionsSerialized->wordpressThreadCommentsDepth && $this->optionsSerialized->wordpressThreadComments) {
                $output .= '<span class="wc-toggle wpdiscuz-hidden">' . $this->optionsSerialized->phrases['wc_hide_replies_text'] . ' &and;' . '</span>';
            }
            $output .= '</div>';
        }
        $output .= '</div>';
        $output .= '<div class="wpdiscuz-comment-message"></div>';
        $output .= '<div id="wpdiscuz_form_anchor-' . $uniqueId . '"  style="clear:both"></div>';
    }

    public function end_el(&$output, $comment, $depth = 0, $args = array()) {
        $output .= '</div>';
        return $output;
    }

}
