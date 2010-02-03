<?php
/*
Template Name: About
*/
?>
<?php
get_header();
?>
	<div class="single_column">
	<h1>About</h1>
	<div id="subheader_content">
		<p class="about">Me, myself, and I.</p>
	</div>
	</div>
</div><!-- #subheader -->

<div id="content">
<div class="single_column real_content">
<?php  if (have_posts()) : while (have_posts()) : the_post();
the_content();
endwhile; endif; ?></div>

<?php get_footer(); ?>