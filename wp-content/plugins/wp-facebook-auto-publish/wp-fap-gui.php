<?php
 $url = plugin_dir_url(__FILE__);
 $post_types = get_post_types();

$id = get_option('wfap_app_id');
$sec = get_option('wfap_sec_id');
$page = get_option('wfap_page_id');
//$user_tkn = get_option('wfap_user_tkn');
$default_img = get_option('wfap_default_img');
if($default_img == 0)
	echo '<style type="text/css">#default_img_link {display:none;}</style>';
$default_img_link = get_option('wfab_default_img_link');
$post_list = array();
$post = get_option('wfap_post_type');
$post_list  = explode(",", $post );
?>
<script type="text/javascript">
	var url = '"<?php echo $url; ?>';
	var admin_url = '<?php echo admin_url('admin-ajax.php'); ?>';
</script>
<link href="<?php echo $url; ?>css/fbstyle.css" type="text/css" rel="stylesheet" />
<script type="text/javascript">
// JavaScript Document

function add_label(text)
{
		var txt_val = jQuery("#default_img_link").val();
		if(txt_val.trim() == "")
		{
			jQuery("#default_img_link").val(text);
			jQuery("#default_img_link").css('color','#CCC');
		}
}
jQuery(document).ready(function(){
		
	if(jQuery("#default_img_link").val().trim() != "Add the link of the default image")
			jQuery("#default_img_link").css('color','#000');
	
	jQuery("#default_img_link").focus(function(){
		var ini_val = jQuery(this).val();											 
		if(ini_val.trim() == "Add the link of the default image")
		{	jQuery(this).val('');
			jQuery(this).css('color','#000');
		}
	});								

	jQuery("#default_img").click(function(){
		if(jQuery(this).is(':checked'))									  
			jQuery("#default_img_link").show();
		else
			jQuery("#default_img_link").hide();
	});							
	jQuery("#save_btn").click(function(){
		var app_id = jQuery("#fb_app_id").val();
		var secret_id = jQuery("#fb_app_sec_id").val();
		var page_id = jQuery("#fb_page_id").val();

		if(app_id == '')
		{ 

		alert("<?php echo _e('Application ID cannot be blank', 'wp-facebook-auto-publish' ) ?>");
		//alert(gt.gettext('Hello world'));
		 jQuery("#fb_app_id").focus();
		  return false; }

		if(secret_id == '')
		{ alert("<?php echo _e('Secret ID cannot be blank', 'wp-facebook-auto-publish' ) ?>");
		 jQuery("#fb_app_sec_id").focus(); return false; }

		if(page_id == '')
		{ //alert('Page or Profile Link cannot be blank'); jQuery("#fb_page_id").focus(); return false;
		}

		var app_id = jQuery("#fb_app_id").val();
		var sec_id = jQuery("#fb_app_sec_id").val();
		var page_id = jQuery("#fb_page_id").val();
		var post_list = new Array();
		jQuery("input[name='post_ty[]']:checkbox:checked").each( function () {
    		post_list.push(jQuery(this).val());
   		});
		var default_img = 0;
		var default_img_link = '';
		if(jQuery("#default_img").is(':checked'))
		{	default_img  = 1;
			if(jQuery("#default_img_link").val().trim() == "Add the link of the default image")
			{		
				default_img_link = "";	
			}
			else
			{
				default_img_link = jQuery("#default_img_link").val();
			}
		}
		else
			default_img_link='';
		//alert(app_id);alert(sec_id);alert(page_id);alert(post_list); alert(cat_list);
		jQuery.ajax({
				type:'post',
				url: admin_url,
				data: { action: 'fbsettings',app_id:app_id,sec_id:sec_id, page_id:page_id,post_list:post_list,default_img:default_img,default_img_link:default_img_link},
				dataType: 'html',
				beforeSend: function(){ jQuery("#load_gif").show();},
				complete: function(){jQuery("#load_gif").hide(); jQuery("#status_msg").fadeIn(1000); setTimeout('jQuery("#status_msg").fadeOut(500);',3000); 
				},
				success:function(data){
					jQuery("#status_msg").html(data);
					jQuery('html, body').stop().animate({ scrollTop: 0}, 800);
				},
				error:function(){
						alert('fail');
					}
			});
		return false;
	});


jQuery("#wfap_authenticate").click(function(){
		var app_id = jQuery("#fb_app_id").val();
		var sec_id = jQuery("#fb_app_sec_id").val();
		jQuery.ajax({
				type:'post',
				url: admin_url,
				data: { action: 'authenticate',app_id:app_id,sec_id:sec_id},
				dataType: 'html',
				beforeSend: function(){ jQuery("#auth_gif").show();},
				complete: function(){},
				success:function(data){
					jQuery("#auth_gif").show();
						if(data != 'done')
						{
							var win=window.open(data, '_self');
						  	win.focus();
							return false;
						}
					},
				error:function(){
						alert('fail');
					}
			});
		return false;
	});
});
</script>
<div id="wrapper">

<span id="status_msg" class=""><?php _e( 'Setting saved', 'wp-facebook-auto-publish' ) ?></span>

	<div class="container">
		<form action="" method="post">
	        <div class="fb_form">
	           <h2><?php _e( 'WP Facebook Auto Publish', 'wp-facebook-auto-publish' ) ?></h2>
	           <?php if(isset($_REQUEST['msg']) !='')
{?>
	<div class="message">Thank you for authenticate.</div>
	<?php }
	?>
                <table>
                    <tr>
						<td><?php _e( 'Facebook App ID', 'wp-facebook-auto-publish' ) ?><span class="create_app"><?php _e( 'To create facebook app click', 'wp-facebook-auto-publish' ) ?> <a href="https://developers.facebook.com/apps/"> <?php _e( 'here', 'wp-facebook-auto-publish' ) ?></a></span></td>                    
                        <td><input type="text" name="fb_app_id" id="fb_app_id" value="<?php echo $id; ?>" /> </td>
                    </tr>
                    <tr>
						<td><?php _e( 'Facebook App Secret', 'wp-facebook-auto-publish' ) ?></td>                    
                        <td><input type="text" name="fb_app_sec_id" id="fb_app_sec_id" value="<?php echo $sec; ?>" /> 
                        <input type="button" id="wfap_authenticate" value="<?php _e( 'Authenticate', 'wp-facebook-auto-publish' ) ?>"  class="button-primary" /><span id="auth_gif"><img src="<?php echo $url;?>images/loading_gif.gif" /> <?php _e( 'Authenticating...', 'wp-facebook-auto-publish' ) ?> </span> 
                        </td>
                    </tr>
					<tr>
						<td><?php _e( 'Page or Profile', 'wp-facebook-auto-publish' ) ?></td>                    
                        <td><input type="text" name="fb_page_id" id="fb_page_id" value="<?php echo $page; ?>" /> 
                        	<span class="note"><?php _e( "Note: You can use 'me' or leave blank for your own profile ID", "wp-facebook-auto-publish" ) ?></span>
                        </td>
                    </tr>
                    <tr>
						<td><?php _e( 'Post Type', 'wp-facebook-auto-publish' ) ?></td>                    
                        <td>
                        	<div class="list_custom">
                            <?php foreach($post_types as $pt){ 

								if($pt != "attachment" && $pt != "revision" && $pt != "nav_menu_item" ) {?>                            	
                                <span><input type="checkbox" name="post_ty[]" value="<?php echo $pt; ?>" <?php if (in_array($pt, $post_list)) echo ' checked="checked"'; ?> /><?php echo $pt; ?></span>
                            <?php } } ?>
                            </div>
                        </td>
                    </tr>
				 <tr>
						<td><?php _e( 'Show Default Image If no Image available', 'wp-facebook-auto-publish' ) ?></td>                    
                        <td>
	                        <div>
                                <span><input type="checkbox" name="default_img" id="default_img" value="1"  <?php if ($default_img == 1) echo ' checked="checked"'; ?> /></span>
                                <span><input type="text" name="default_img_link" id="default_img_link" value="<?php if (empty($default_img_link)) _e( 'Add the link of the default image', 'wp-facebook-auto-publish' ) ; else echo $default_img_link;?>" onblur="add_label(__( 'Add the link of the default image', 'wp-facebook-auto-publish' ))"  /></span>
                           </div>
                        </td>
                    </tr>
               </table>
                <input type="submit" value="<?php _e( 'Save Changes', 'wp-facebook-auto-publish' ) ?>" id="save_btn" class="button-primary" /><span id="load_gif"><img src="<?php echo $url;?>images/loading_gif.gif" /> <?php _e( 'Saving Settings ...', 'wp-facebook-auto-publish' ) ?> </span>
            </div>
        </form>
        
    </div>
    
    <div class="sidebar">
      
			<h3 class="hndle"><span><?php _e( 'Important Note', 'wp-facebook-auto-publish' ) ?></span></h3>
			<div class="inside">
				<p><?php _e( 'According to the new security policy of the Facebook, the App needs to be submitted and verified by Facebook team.', 'wp-facebook-auto-publish' ) ?></p>
				<p><?php _e( 'Please follow the bellow instructions to submit an app.', 'wp-facebook-auto-publish' ) ?></p>
				<ol>
					<li><?php _e( 'A logo must be provided to the App. In order to add a logo, go your Facebook', 'wp-facebook-auto-publish' ) ?> <strong><?php _e( 'App->App Details->Icons', 'wp-facebook-auto-publish' ) ?></strong>.</li>
					<li><?php _e( 'Go to your Facebook', 'wp-facebook-auto-publish' ) ?> <strong><?php _e( 'App->Status & Review->Start a Submission', 'wp-facebook-auto-publish' ) ?></strong>. <?php _e( 'Give the required', 'wp-facebook-auto-publish' ) ?> "<strong><?php _e( 'LOGIN PERMISSIONS', 'wp-facebook-auto-publish' ) ?></strong>" <?php _e( 'to the app to be used and accessed by your domain and by our plugin.', 'wp-facebook-auto-publish' ) ?>
						<p><?php _e( 'Your app must have below permissions to use auto post feature', 'wp-facebook-auto-publish' ) ?></p>
						<ul>
							<li><?php _e( 'manage_pages', 'wp-facebook-auto-publish' ) ?></li>
							<li><?php _e( 'publish_actions', 'wp-facebook-auto-publish' ) ?></li>
						</ul>
						<p><?php _e( 'You also need to explain the use of the above permissions on Facebook.', 'wp-facebook-auto-publish' ) ?></p>
					</li>
					<li><?php _e( 'You must provide a long description of your app. Go to you Facebook', 'wp-facebook-auto-publish' ) ?> <strong><?php _e( 'App->App Details->Long Description', 'wp-facebook-auto-publish' ) ?></strong>. <?php _e( 'This description must explain a valid reason or purpose to use this app for your domain.', 'wp-facebook-auto-publish' ) ?>
					<br/><br/> <?php _e( 'Later all the above settings will be reviewed by the Facebook team with in next 7 business days.', 'wp-facebook-auto-publish' ) ?></li>
				</ol>
							
			</div>
			
    </div>
</div>
