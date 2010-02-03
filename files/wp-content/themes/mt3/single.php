<?php
get_header();
if (have_posts()) : while (have_posts()) : the_post(); 
if($id > 0) { add_count($id);}
?>
	<div class="single_column">
	<?
		$my_old_tags = get_the_post_keytags();
		if(!empty($my_old_tags)) {
			//$my_new_tags = str_replace("<a href", "<li><a href", $my_old_tags);
			//$my_new_tags = str_replace("</a>, ", "</a></li>\n", $my_new_tags);
			//$my_new_tags .= "</li>\n";
			?>
	<ul class="subheader_items">
		<li class="article_tags">Tags: <? echo $my_old_tags; ?></li>
	</ul><!-- .subheader_items -->
	<? 
		}
	?>
	<h1><?php the_title(); ?></h1>
	<div id="subheader_content">
		<p class="article_date">Published on <?php the_time('l, F jS, Y') ?></p>
	</div>
	</div>
</div><!-- #subheader -->

<div id="content">
<div class="single_column real_content">

<?php the_content('<p>Read the rest of this entry &raquo;</p>'); ?>

</div>

<? comments_template(); ?>
<?php endwhile; else: ?>
<p>Sorry, no posts matched your criteria.</p>
<?php endif; ?>
<?php get_footer(); ?>
