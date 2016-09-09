<?php 
/*
 * Plugin Name: WP Facebook Auto Publish
 * Plugin URI: http://vivacityinfotech.net
 * Description: A Simple wordpress plugin to automatically post your wordpress posts and pages on Facebook along with their featured image.
 * Version: 1.5
 * Author: Vivacity Infotech Pvt. Ltd.
 * Author URI: http://vivacityinfotech.net
 * Author Email: vivacityinfotech.net/support
	Text Domain: wp-facebook-auto-publish
	Domain Path: /languages/
 * License: GPL2
*/
/*
Copyright 2014  Vivacity InfoTech Pvt. Ltd. 
    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.


    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
 add_action('init', 'viva_fbautopublish_trans');
    function viva_fbautopublish_trans(){  
      
       load_plugin_textdomain('wp-facebook-auto-publish', FALSE, dirname(plugin_basename(__FILE__)).'/languages/');
        }
 //add Jquery to settings page
add_action( 'admin_menu', 'autopublishjquery' );
function autopublishjquery() {
    wp_register_script( 'my_autopublishjquery', plugins_url('/js/gettext.js', __FILE__), array('jquery'));
    wp_enqueue_script( 'my_autopublishjquery' );
}  
include('lib/facebook.php');
define('WP_POST_REVISIONS', false);
function wfap_get_access_token()
{
		
	if(isset($_REQUEST['code']))
	{	
 $code= $_REQUEST['code'];
	$id = get_option('wfap_app_id');
			$sec = get_option('wfap_sec_id');
		$page_url = get_site_url();
	$auth_url='https://graph.facebook.com/oauth/access_token?client_id='.$id.'&client_secret='.$sec.'&redirect_uri='.$page_url.'&code='.$code;	
	//echo $auth_url;exit;
		//$accesscode = file_get_contents($auth_url);
	 
	$curl_handle=curl_init();
curl_setopt($curl_handle, CURLOPT_URL,$auth_url);
curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 2);
curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($curl_handle, CURLOPT_USERAGENT, 'fb_auto');
$query = curl_exec($curl_handle);
curl_close($curl_handle);

	  $allval = array(); 
	  $token = array();
		$allval =  explode("=",$query );
		$token = explode("&", $allval[1]);
		$message ="Thank you for authenticate";
		if($token !='')
		{
		update_option('wfap_user_tkn',$token[0]);
		}
		//echo $query;exit; 
		$location = admin_url( 'admin.php?page=wp-facebook-auto-publish/wp-fap-gui.php&msg='.$message); 
		wp_redirect( $location);
		exit;
	}
}
add_action( 'wp_ajax_fbsettings', 'wfap_save_settings' );
add_action( 'init', 'wfap_get_access_token' );
add_action( 'wp_ajax_authenticate', 'wfap_authenticate' );
add_action('admin_menu', 'wp_fap_on_admin');
add_action( 'publish_post', 'wfap_post_on_facebook' ); 
add_action( 'save_post', 'wfap_post_on_facebook' ); 

function wp_fap_on_admin() {
    add_menu_page( __( 'WP Fb Auto Publish', 'wp-facebook-auto-publish' ), __( 'WP2FB Auto Post', 'wp-facebook-auto-publish' ), 'manage_options', 'wp-facebook-auto-publish/wp-fap-gui.php', '', plugins_url( 'wp-facebook-auto-publish/images/icon.png' ),99);
}
function wfap_save_settings(){
		$wfab_app_id = $_REQUEST['app_id'];
		$wfab_sec_id = $_REQUEST['sec_id'];
		$wfab_page_id = $_REQUEST['page_id'];
		$wfab_user_tkn = $_REQUEST['user_tkn'];
		$wfab_default_img = $_REQUEST['default_img'];
		$wfab_default_img_link = $_REQUEST['default_img_link'];
		if(!empty($_REQUEST['post_list']))
			$wfab_post_type = implode(",",$_REQUEST['post_list']);
		else
			$wfab_post_type = 0;
			
		if(empty($wfab_app_id)) $wfab_app_id=0;
		if(empty($wfab_sec_id)) $wfab_sec_id=0;
		if(empty($wfab_page_id)) $wfab_page_id='me';
		//echo $wfab_app_id."   ---  ".$wfab_sec_id."  ---   ".$wfab_page_id."  ---  ".$wfab_post_type;
		update_option('wfap_app_id',$wfab_app_id);
		update_option('wfap_sec_id',$wfab_sec_id);
		update_option('wfap_page_id',$wfab_page_id);
		//update_option('wfap_user_tkn',$wfab_user_tkn);
		update_option('wfap_post_type',$wfab_post_type);
		update_option('wfap_default_img',$wfab_default_img);
		update_option('wfab_default_img_link',$wfab_default_img_link);

		echo __( ' Settings Saved Successfully', 'wp-facebook-auto-publish' );
		die();
}
function wfap_authenticate(){

		Facebook::$CURL_OPTS[CURLOPT_SSL_VERIFYPEER] = false;
		Facebook::$CURL_OPTS[CURLOPT_SSL_VERIFYHOST] = 2;

		$id =  $_REQUEST['app_id'];
		$sec =$_REQUEST['sec_id'];

		
		$fb_config = array(
				'appId'  => $id,
				'secret' => $sec,
				'cookie' => true
		);
		$fb = new Facebook($fb_config);
		$access_token = $fb->getAccessToken();
		$user = $fb->getUser();
		//$adminurl = admin_url( 'admin.php?page=wp-facebook-auto-publish/wp-fap-gui.php'); 
		$adminurl =  get_home_url();
		if($user == 0)
		{
		   $params = array( 'scope' => 'publish_actions, email, read_stream, user_interests, user_likes, user_location, user_status','redirect_uri'=>$adminurl);
			echo $fbook_login_url = $fb->getLoginUrl($params);
			$access_token = $fb->getAccessToken();
			$fb->setAccessToken($access_token);
			///echo $id." - ".$sec;
			die();
		}
		else
		{
			update_option('wfap_app_id',$id);
			update_option('wfap_sec_id',$sec);
			echo "done";

		}
}
function wfap_post_on_facebook($post_id){
	
		$hook =  current_filter();
		$post_status = get_post_status( $post_id );
		if( $post_status == 'inherit' || $post_status == 'publish')
		{	//echo "checkpoint 1 <br/>";
	$post_id = get_the_ID();
	$facebook_auto = get_post_meta($post_id, "facebook_auto", true);
 if($facebook_auto =='')
 {
$facebook_auto ='yes'; 	
 	}
if($facebook_auto != 'yes')
{
return $post_id; 
}
			Facebook::$CURL_OPTS[CURLOPT_SSL_VERIFYPEER] = false;
			Facebook::$CURL_OPTS[CURLOPT_SSL_VERIFYHOST] = 2;
			
			$id = get_option('wfap_app_id');
			$sec = get_option('wfap_sec_id');
			$page = get_option('wfap_page_id');
			$post_types = get_option('wfap_post_type');
			$user_tkn = get_option('wfap_user_tkn');
			$default_img = get_option('wfap_default_img');
			$default_img_link = get_option('wfab_default_img_link');	
			if(empty($page))
				$page ='me';
			// check if the authentication is done and the user token is set.
			if(!empty($user_tkn))
			{	//echo "checkpoint 2 <br/>";
				// check if the published post is of the category which is set by user
				$check = wfap_check_post_type( $post_id, $post_types, $hook );
					if( $check )
					{	
						$fb_config = array(
								'appId'  => $id,
								'secret' => $sec,
								'cookie' => true
						);
						$fb = new Facebook($fb_config);
						$access_token = $fb->getAccessToken();
						$user = $fb->getUser();
				
						$access_token = $fb->getAccessToken();
				
						$fb->setAccessToken($access_token);
							$title = get_the_title($post_id);
							$post = get_post( $post_id );
							$decription = $post->post_content;
							$decription = trim($decription);
							$caption = '';
							
							$params = array(
									"access_token"=> $user_tkn,
									"message" => $title,
									"name" => $title, 
									"caption" => $caption,
									"description" => $decription
							);

							$img_url = get_post_thumbnail_id( $post_id );
						if($default_img == 1)
						{
							if(!empty( $img_url ))
							{
								$picture = wp_get_attachment_image_src($img_url, 'single-post-thumbnail');
								$img_url = $picture[0];
							}
							else
							{
								if(!empty($default_img_link))
									$img_url = $default_img_link;
								else
									$img_url = site_url().'/wp-content/plugins/wp-facebook-auto-publish/images/default.png';
							}
							$params["picture"] = $img_url;
						}
						else
						{
							if(!empty( $img_url ))
							{
								$picture = wp_get_attachment_image_src($img_url, 'single-post-thumbnail');
								$img_url = $picture[0];
								$params["picture"] = $img_url;
							}
							else
							{
								$params["message"] = $decription;
							}
						}
				
						$ret_obj = $fb->api('/'.$page.'/feed', 'POST', $params);
						//echo "gone FB <br/> "; 
					}
			}	
	} 
}
function wfap_check_post_type( $post_id, $post_types, $hook ){

		$exist = false;
		$post_type_list = array();
		$post_type_list  = explode(",",$post_types);
		if( isset( $post_id ) && !empty( $post_id ))
		{	
			$post_type = $_POST['post_type'];
			if( in_array( $post_type ,$post_type_list ) )	
			{
				// for default page and post of wordpress
				if( $post_type == 'post' && $hook== 'publish_post' )
				{	$exist = true;		}
				else if( $post_type =='page' )
				{	$exist = true;		}
				// for custom post and their taxonomies
				else if( $post_type != 'post' &&  $post_type != 'page' && $hook == 'save_post')
				{
					$exist = true;
				}
			}
		}
		return $exist;
}
function custom_meta_box_markup($object)
{
	wp_nonce_field(basename(__FILE__), "meta-box-nonce");
	 $post_id = get_the_ID();
	$facebook_auto = get_post_meta($post_id, "facebook_auto", true);?>
	<input name="pageid" type="hidden" value="<?php echo  $post_id; ?>">
		Facebook Autopost on update	<br>
	<input type="radio" <?php if($facebook_auto=='yes'){ ?> checked ="checked" <?php } ?>] name="autoupdate" id="autoupdate1" value="yes"> Yes 
	<input type="radio" <?php if($facebook_auto=='no'){ ?> checked ="checked" <?php } ?>] name="autoupdate" id="autoupdate2" value="no">No<?php }
function add_custom_meta_box()
{
    add_meta_box("header-meta-box", "Facebook auto post", "custom_meta_box_markup", "page", "side", "high", null);
  add_meta_box("header-meta-box", "Facebook auto post", "custom_meta_box_markup", "post", "side", "high", null);
}
 
add_action("add_meta_boxes", "add_custom_meta_box");

function save_custom_meta_box($post_id, $post, $update)
{
    if (!isset($_POST["meta-box-nonce"]) || !wp_verify_nonce($_POST["meta-box-nonce"], basename(__FILE__)))
        return $post_id;
    if(!current_user_can("edit_post", $post_id))
        return $post_id;
    if(defined("DOING_AUTOSAVE") && DOING_AUTOSAVE)
        return $post_id;
    $pageid= "";
    $imagename = "";
 $autoupdate =$_REQUEST['autoupdate'];
    if(isset($_POST["pageid"]))
    {
        $pageid = $_POST["pageid"];
    }  
    update_post_meta($post_id, "facebook_auto",  $autoupdate);
}
add_action("save_post", "save_custom_meta_box", 10, 3);?>
