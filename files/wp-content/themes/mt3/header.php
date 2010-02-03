<?php

require( TEMPLATEPATH."/sajax/php/Sajax.php");

function get_laid($num) {
	global $tableposts, $wpdb;
	$to_return = '';
	$results = $wpdb->get_results("SELECT ID, post_title, comment_count, post_date FROM $tableposts WHERE post_status = 'publish' ORDER BY post_date DESC LIMIT ".((($num+1) * 7) + 3).", 7");
	foreach ($results as $result) {
		$post_title = substr($result->post_title, 0, 45);
		$urlperma = get_permalink($result->ID);
		$to_return .= "<li><a href=".$urlperma."><span class=\"posts_list_date\">".mysql2date('M j', $result->post_date)."</span><span class=\"posts_list_comment\">".$result->comment_count."</span><span class=\"posts_list_title\">".$post_title."</span></a></li>";		
	}
	return $to_return;
}

sajax_init();
sajax_export("get_laid");
sajax_handle_client_request();

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php
if ( is_page('articles') ) {
	echo 'Articles';
	echo " &#8212; ";
} else if ( is_single() ) {
	wp_title('');
	echo " &#8212; ";
} else if (is_date()) {
	echo "Articles: ";
	the_time('F Y');
	echo " &#8212; ";
} else if (jkeywords_is_keyword()) {
	echo "Tag: ";
	jkeywords_search_keyword();
	echo " &#8212; ";
} else if (is_page('about')) {
	echo "About ";
	echo " &#8212; ";
} else if (is_page('contact')) {
	echo "Contact ";
	echo " &#8212; ";
} else if (is_page('links')) {
	echo "Links ";
	echo " &#8212; ";
} else if (is_404()) {
	echo "Page Not Found ";
	echo " &#8212; ";
} else if (is_search()) {
	echo "Search ";
	echo " &#8212; ";
}
?>MTsix [Oliver Zheng]</title>
<link rel="stylesheet" href="<?php bloginfo('stylesheet_url'); ?>" type="text/css" media="screen" />
<link rel="alternate" type="application/rss+xml" title="mtsix RSS Feed" href="<?php bloginfo('rss2_url'); ?>" />
<link rel="pingback" href="<?php bloginfo('pingback_url'); ?>" />
<script language="JavaScript" type="text/javascript" src="<?php bloginfo('template_directory'); ?>/neweffects.js"></script>
<?php wp_head(); ?>
</head>

<body>

<div class="page_container" id="<?

if (is_page('articles') || is_search() || is_date() || is_keyword()) {
	echo "articles";
} else if (is_page('about')) {
	echo 'about';
} else if (is_page('contact')) {
	echo 'contact';
} else if (is_page('links')) {
	echo 'links';
} else if (is_404()) {
} else if(!is_single()) {
	echo "home";
} else if (is_single()) {
	echo "articles";
}
?>">
<div id="page">

<div id="page_header">
	<h1>MTsix</h1>
	<ul id="page_menu">
		<li id="menu_home"><a href="/">Home</a></li>
		<li id="menu_articles"><a href="/articles/">Articles</a></li>
		<li id="menu_about"><a href="/about/">About</a></li>
		<li id="menu_links"><a href="/links/">Links</a></li>
		<li id="menu_contact"><a href="/contact/">Contact</a></li>
	</ul>
	<p id="tagline"><a href="/">MTsix - Neat things and free monkeys.</a></p>	
</div><!-- #page_header -->

<hr class="hide" />

<div id="main">

<div id="subheader">
