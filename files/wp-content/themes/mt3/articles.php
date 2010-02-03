<?php
/*
Template Name: Articles
*/

get_header();
?>
	<div class="single_column">
<? include (TEMPLATEPATH . '/searchform.php'); ?>
	<h1>Articles</h1>
	<div id="subheader_content">
		<p class="article_calendar">Browse through the archive by date, tags, or search.</p>
	</div>
	</div>
</div><!-- #subheader -->

<div id="content">

<div class="single_column real_content">

<h2>Archive</h2>
<ul class="archive_float">
<? get_archives('monthly'); ?>
</ul>
<div class="float_clearer"><hr /></div>
<h2>Tags</h2>
<ul class="cosmos">
	<?php echo all_keywords('<li class="cosmos keyword%count%"><a href="/tag/%keylink%">%keyword%</a></li>',
							'', 1, 10); ?>
</ul>
<div class="float_clearer"><hr /></div>
<div class="home_second_article">
<h2>Most Viewed</h2>
<ul>
<? show_pop_posts(); ?>
</ul>
</div>
<div class="home_third_article">
<h2>Most Commented</h2>
<ul>
<? show_comments_posts(); ?>
</ul>
</div>
<div class="float_clearer"><hr /></div>
</div>
<div class="float_clearer"><hr /></div>
<?php get_footer(); ?>
