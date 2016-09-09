<?php
// read the mobile logo + retina logo
$td_mobile_customLogo = td_util::get_option('tds_logo_menu_upload');
$td_mobile_customLogoR = td_util::get_option('tds_logo_menu_upload_r');

// read the header logo + retina logo
$td_header_logo = td_util::get_option('tds_logo_upload');
$td_header_logoR = td_util::get_option('tds_logo_upload_r');

$td_logo_alt = td_util::get_option('tds_logo_alt');
$td_logo_title = td_util::get_option('tds_logo_title');

if (!empty($td_logo_title)) {
	$td_logo_title = ' title="' . $td_logo_title . '"';
}

// if show sticky menu show both logos
if (td_util::get_option('tds_logo_on_sticky') != '') {

	// read what logo to load - header logo or mobile logo - used for css
	$td_sticky_option = '';
	if (td_util::get_option('tds_logo_on_sticky') == 'show') {
		$td_sticky_option = 'td-sticky-mobile';
	} else {
		$td_sticky_option = 'td-sticky-header';
	}


	// mobile logo here
	if (!empty($td_mobile_customLogoR)) {
		//if retina
		?>
		<a itemprop="url" class="td-mobile-logo <?php echo $td_sticky_option?>" href="<?php echo esc_url(home_url( '/' )); ?>">
			<img class="td-retina-data" data-retina="<?php echo esc_attr($td_mobile_customLogoR) ?>" src="<?php echo $td_mobile_customLogo?>" alt="<?php echo $td_logo_alt ?>"<?php echo $td_logo_title ?>/>
		</a>
		<meta itemprop="name" content="<?php bloginfo('name')?>">
	<?php
	} else {
		//not retina
		if (!empty($td_mobile_customLogo)) {
			?>
			<a itemprop="url" class="td-mobile-logo <?php echo $td_sticky_option?>" href="<?php echo esc_url(home_url( '/' )); ?>"><img src="<?php echo $td_mobile_customLogo?>" alt="<?php echo $td_logo_alt ?>"<?php echo $td_logo_title ?>/></a>
			<meta itemprop="name" content="<?php bloginfo('name')?>">
		<?php
		}
	}

	// header logo here
	if (!empty($td_header_logoR)) {
		//if retina
		?>
		<a itemprop="url" class="td-header-logo <?php echo $td_sticky_option?>" href="<?php echo esc_url(home_url( '/' )); ?>">
			<img class="td-retina-data" data-retina="<?php echo esc_attr($td_header_logoR) ?>" src="<?php echo $td_header_logo?>" alt="<?php echo $td_logo_alt ?>"<?php echo $td_logo_title ?>/>
		</a>
		<meta itemprop="name" content="<?php bloginfo('name')?>">
	<?php
	} else {
		//not retina
		if (!empty($td_header_logo)) {
			?>
			<a itemprop="url" class="td-header-logo <?php echo $td_sticky_option?>" href="<?php echo esc_url(home_url( '/' )); ?>"><img src="<?php echo $td_header_logo?>" alt="<?php echo $td_logo_alt ?>"<?php echo $td_logo_title ?>/></a>
			<meta itemprop="name" content="<?php bloginfo('name')?>">
		<?php
		}
	}
} else { // else load logo mobile only without header logo - no need the header logo - sticky menu disabled
	if (!empty($td_mobile_customLogoR)) {
		//if retina
		?>
		<a itemprop="url" href="<?php echo esc_url(home_url( '/' )); ?>">
			<img class="td-retina-data" data-retina="<?php echo esc_attr($td_mobile_customLogoR) ?>" src="<?php echo $td_mobile_customLogo?>" alt="<?php echo $td_logo_alt ?>"<?php echo $td_logo_title ?>/>
		</a>
		<meta itemprop="name" content="<?php bloginfo('name')?>">
	<?php
	} else {
		//not retina
		if (!empty($td_mobile_customLogo)) {
			?>
			<a itemprop="url" href="<?php echo esc_url(home_url( '/' )); ?>"><img src="<?php echo $td_mobile_customLogo?>" alt="<?php echo $td_logo_alt ?>"<?php echo $td_logo_title ?>/></a>
			<meta itemprop="name" content="<?php bloginfo('name')?>">
		<?php
		}
	}
}