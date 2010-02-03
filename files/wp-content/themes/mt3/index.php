<?php
get_header();
?>
	<script>
	<?
	sajax_show_javascript();
	?>
	</script>
	<div class="single_column">
	<!--<div class="right header_image"><img src="" alt="" /></div>-->
	<h1>What is "the bigger picture"?</h1>
	<div id="subheader_content"><p>It's bigger than you think.</p></div>
	</div>
</div><!-- #subheader -->
<div id="content">
<div class="single_column">

<?php if (have_posts()) :
$counter = 0;
$home_play = array('', '<div class="home_second_article">', '<div class="home_third_article">');
$home_play_end = array('', '</div>', '</div>');
query_posts('showposts=3');
while (have_posts()) : the_post();

	$continue_reading = get_post_custom_values('continue_reading');
	if($continue_reading[0] == 'true' && $counter != 0) {
		$display_text = "Continue Reading";
	} else {
		$display_text = "View Article";
	}
	echo $home_play[$counter];
?>

<h2><?php the_title(); ?></h2>

<div class="real_content"><?php
if($counter == 0) {
	the_content('', true, '', 2);
} else {
	the_content('', true);
} ?></div>

<p class="button_view_article"><a href="<?php the_permalink(); ?>" class="mtbutton2 mtbutton_16272e" title="<?php echo $display_text; ?>"><span class="mtbutton_text mtbutton_right right"><?php echo $display_text; ?> &raquo;</span><span class="mtbutton_comment_right right"></span><span class="mtbutton_comment_text right"><?php comments_number('No comments','1 comment','% comments'); ?></span><span class="mtbutton_comment_left right"></span></a></p>

<div class="float_clearer"><hr /></div>

<?php
	echo $home_play_end[$counter];

	$counter++;
endwhile;
endif; ?>

<div class="float_clearer"><hr /></div>

</div>

<div id="subcontent">
<div id="home_subcontent_more">
<h2>Recent Posts</h2>
<div class="posts_list posts_list_dark posts_list_7" id="home_subcontent_posts">
<ul class="posts_list_ul" id="recent_posts_list">
<?php $sub_more = new WP_Query('showposts=10'); 
$counter = 0;
while ($sub_more->have_posts()) : $sub_more->the_post();
	$counter++;
	if($counter <= 3) {
		continue;
	} else {
?>
<li><a href="<?php the_permalink(); ?>"><span class="posts_list_date"><?php the_time('M j'); ?></span><span class="posts_list_comment"><?php comments_number('0','1','%'); ?></span><span class="posts_list_title"><?php the_title(); ?></span></a></li>
<?php
	}
endwhile; ?>
</ul>
</div><!-- .posts_list -->
<ul class="posts_list_control left" id="home_subcontent_posts_control">
	<li class="older left"><a onClick="javascript:scroll_up(this); return false;" class="mtbutton2 mtbutton_cccccc"><span class="mtbutton_text mtbutton_older left">Newer</span></a></li>
	<li class="newer left"><a onClick="javascript:scroll_down_call(this); return false;" class="mtbutton2 mtbutton_cccccc"><span class="mtbutton_text mtbutton_newer left">Older</span></a></li>
	<li class="left ajaxload" id="ajaxload_home"></li>
</ul>
<p><a href="/articles/" class="mtbutton2 mtbutton_cccccc"><span class="mtbutton_right right"></span><span class="mtbutton_text mtbutton_left right">View Archive</span></a></p>
<div class="float_clearer"><hr /></div>
</div><!-- #home_subcontent_more -->
<div id="home_subcontent_links">
<h2>Recent Bookmarks</h2>
<ul>
<?php 
//ioz_dl_get_links(5, '<li>', '</li>', 'user', '', 'mtsix');
/*
// Include the RSS functions of wordpress
include_once (ABSPATH . WPINC . '/rss.php');
// Grab my RSS feed
$feed = fetch_rss("http://del.icio.us/rss/mtsix");
// I want 5 results please
$maxitems = 5;
$items = array_slice($feed->items, 0, $maxitems);
	// Output the results!
if(!empty($items)) {
	foreach ($items as $item) {
		echo '<li>';
		echo '<a href="';
		// This is a bit messy, but it makes the output valid XHTML strict by removing ampersands
		$item['link'] = str_replace("&", "&amp;", $item['link']);
		$item['link'] = str_replace("&amp;&amp;", "&amp;", $item['link']);
		// End of messyness. Output the link
		echo $item['link'];
		echo '">';
		// Output the title
		echo $item['title'];
		echo '</a>';
		// If i've written a description, output it
		if (isset($item['description'])) {
				echo ' &#8212; ';
			echo $item['description'];
		}
		echo '</li>';
	}
}*/

//delicious_pp('mtsix', 5, 1, 0, '<li>', '', ' &#8212; ', '</li>');

?>
<li>No bookmarks shall ever please your perusal, my sire, as this functionality is actually deeply broken.</li>
</ul>
<p class="right"><a href="http://del.icio.us/mtsix/" class="mtbutton2 mtbutton_cccccc"><span class="mtbutton_right right"></span><span class="mtbutton_text mtbutton_left right">See More Bookmarks</span></a></p>
</div><!-- #home_subcontent_links -->
<div class="float_clearer"><hr /></div>
</div><!--#subcontent -->

<?php get_footer(); ?>
