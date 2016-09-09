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
		{ alert('Application ID cannot be blank'); jQuery("#fb_app_id").focus(); return false; }

		if(secret_id == '')
		{ alert('Secret ID cannot be blank'); jQuery("#fb_app_sec_id").focus(); return false; }

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