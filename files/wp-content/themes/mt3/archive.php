<?php get_header(); ?>
	<div class="single_column">
<? include (TEMPLATEPATH . '/searchform.php'); ?>
	<h1><?php the_time('F Y'); ?></h1>
	<div id="subheader_content">
		<p>Articles published in <?php the_time('F, Y'); ?>.</p>
	</div>
	</div>
</div><!-- #subheader -->

<div id="content">

<div class="single_column real_content">

<?php
	$counter = 0;
	if (have_posts()) {
	$even = 0;
	$class_odd = array("home_second_article", "home_third_article");
	$float_odd = array('', '<div class="float_clearer"><hr /></div>');
	
	while (have_posts()) : the_post();
		$even = $counter % 2;
		$continue_reading = get_post_custom_values('continue_reading');
		if($continue_reading[0] == 'true') {
			$display_text = "Continue Reading";
		} else {
			$display_text = "View Article";
		}
?>
<div class="<? echo $class_odd[$even]; ?>">

<h2><?php the_title(); ?></h2>

<div class="real_content"><?php the_content('', true); ?></div>

<p class="button_view_article"><a href="<?php the_permalink(); ?>" class="mtbutton2 mtbutton_16272e" title="<?php echo $display_text; ?>"><span class="mtbutton_text mtbutton_right right"><?php echo $display_text; ?> &raquo;</span><span class="mtbutton_comment_right right"></span><span class="mtbutton_comment_text right"><?php comments_number('No comments','1 comment','% comments'); ?></span><span class="mtbutton_comment_left right"></span></a></p>

</div>

<?
	echo $float_odd[$even];
	$counter++;
endwhile;
} else { ?>
<p>Sorry, no articles match your criteria.</p>
<? } ?>

</div>

<div id="subcontent" class="sub_regular">
<div class="single_column center">
<?
$toempty = mt_next_posts();
$toempty_prev = mt_previous_posts();
$tomax = mt_max_pages();
$mt_li_class = array(" class=\"static\"", "");
$mt_a_1 = array("<span>", '<a href="'.$toempty.'">');
$mt_a_2 = array("</span>", '</a>');
$mt_b_1 = array("<span>", '<a href="'.get_pagenum_link($tomax).'">');
$mt_b_2 = array("</span>", '</a>');
$mt_c_1 = array("<span>", '<a href="'.$toempty_prev.'">');
$mt_c_2 = array("</span>", '</a>');
$mt_d_1 = array("<span>", '<a href="'.get_pagenum_link(1).'">');
$mt_d_2 = array("</span>", '</a>');
if(empty($toempty)) {
	$mt_nextpage = 0;
} else {
	$mt_nextpage = 1;
}
if(empty($toempty_prev)) {
	$mt_prevpage = 0;
} else {
	$mt_prevpage = 1;
}

if($tomax == $paged) {
	$mt_maxpage = 0;
} else {
	$mt_maxpage = 1;
}
if(1 == $paged) {
	$mt_prevmaxpage = 0;
} else {
	$mt_prevmaxpage = 1;
}

?>
<ul class="page_number">
	<li<? echo $mt_li_class[$mt_prevmaxpage]; ?>><? echo $mt_d_1[$mt_prevmaxpage]; ?>&laquo;<? echo $mt_d_2[$mt_prevmaxpage]; ?></li>
	<li<? echo $mt_li_class[$mt_prevpage]; ?>><? echo $mt_c_1[$mt_prevpage]; ?>&#8249; Prev<? echo $mt_c_2[$mt_prevpage]; ?></li>
	<li class="current"><span>Page <? echo $paged; ?></span></li>
	<li<? echo $mt_li_class[$mt_nextpage]; ?>><? echo $mt_a_1[$mt_nextpage]; ?>Next &#8250;<? echo $mt_a_2[$mt_nextpage]; ?></li>
	<li<? echo $mt_li_class[$mt_maxpage]; ?>><? echo $mt_b_1[$mt_maxpage]; ?>&raquo;<? echo $mt_b_2[$mt_maxpage]; ?></li>
</ul>
</div>
</div><!--#subcontent -->
<?php

function mt_max_pages() {
	global $result, $request, $posts_per_page, $wpdb, $max_num_pages;
	if ( isset($max_num_pages) ) {
		$max_page = $max_num_pages;
	} else {
		preg_match('#FROM\s(.*)\sLIMIT#siU', $request, $matches);
		$fromwhere = $matches[1];
		$numposts = $wpdb->get_var("SELECT COUNT(DISTINCT ID) FROM $fromwhere");
		$max_page = $max_num_pages = ceil($numposts / $posts_per_page);
	}
	return $max_page;
}

function mt_next_posts($max_page = 0) { // original by cfactor at cooltux.org
	global $paged, $pagenow;
	if ( !$max_page ) {
		$max_page = mt_max_pages();
	}
	if ( !is_single() ) {
		if ( !$paged )
			$paged = 1;
		$nextpage = intval($paged) + 1;
		if ( !$max_page || $max_page >= $nextpage )
			return get_pagenum_link($nextpage);
	}
	return '';
}
function mt_previous_posts() { // original by cfactor at cooltux.org
	global $paged, $pagenow;

	if ( !is_single() ) {
		$nextpage = intval($paged) - 1;
		if ( $nextpage < 1 ) {
			return '';
		} else {
			return get_pagenum_link($nextpage);
		}
	}
}

get_footer(); ?>