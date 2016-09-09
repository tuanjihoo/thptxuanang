<?php


//read the logo + retina logo
$td_customLogo = td_util::get_option('tds_logo_upload');
$td_customLogoR = td_util::get_option('tds_logo_upload_r');

$td_logo_text = td_util::get_option('tds_logo_text');
$td_tagline_text = td_util::get_option('tds_tagline_text');

$td_logo_alt = td_util::get_option('tds_logo_alt');
$td_logo_title = td_util::get_option('tds_logo_title');

if (!empty($td_logo_title)) {
	$td_logo_title = ' title="' . $td_logo_title . '"';
}

if (!empty($td_customLogoR)) {
	?>
	<a itemprop="url" href="<?php echo esc_url(home_url( '/' )); ?>">
		<img class="td-retina-data td-logo"  data-retina="<?php echo esc_attr($td_customLogoR) ?>" src="<?php echo $td_customLogo?>" alt="<?php echo $td_logo_alt ?>"<?php echo $td_logo_title ?>/>
	</a>
	<meta itemprop="name" content="<?php bloginfo('name')?>">
<?php

} else {
	if (!empty($td_customLogo)) {
		?>
		<a class="td-logo" itemprop="url" href="<?php echo esc_url(home_url( '/' )); ?>"><img src="<?php echo $td_customLogo?>" alt="<?php echo $td_logo_alt ?>"<?php echo $td_logo_title ?>/></a>
		<meta itemprop="name" content="<?php bloginfo('name')?>">

	<?php
	} else { ?>
    <span class="logo-text-container">
		<a itemprop="url" class="td-logo-wrap" href="<?php echo esc_url(home_url( '/' )); ?>">
			<span class="td-logo-text"><?php if(!$td_logo_text) { echo "NEWSPAPER"; } else { echo $td_logo_text; } ?></span>
			<span class="td-tagline-text"><?php if(!$td_tagline_text) { echo "DISCOVER THE ART OF PUBLISHING"; } else { echo $td_tagline_text; } ?></span>
		</a>
    </span>
	<?php
	}
}
?>