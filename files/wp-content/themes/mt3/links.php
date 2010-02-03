<?php
/*
Template Name: Links
*/
?>
<?php get_header(); ?>

	<div class="single_column">
	<h1>Links</h1>
	<div id="subheader_content">
		<p class="world">Introducing you to the web.</p>
	</div>
	</div>
</div><!-- #subheader -->

<div id="content">
<div class="single_column real_content">
<p>If you are bored with this amazing site, take a minute and take a look at these.</p>
<?php 

$order = 'name';
$order = strtolower($order);
$hide_if_empty = 'obsolete';

// Handle link category sorting
$direction = 'ASC';
if ( '_' == substr($order,0,1) ) {
   $direction = 'DESC';
   $order = substr($order,1);
}

if ( !isset($direction) )
   $direction = '';

$cats = get_categories("type=link&orderby=$order&order=$direction&hierarchical=0");

// Display each category
if ( $cats ) {
   foreach ( (array) $cats as $cat ) {
      // Handle each category.

      // Display the category name
      echo '<div class="links_cat"><h3>' . $cat->cat_name . "</h3>\n\t<ul>\n";
      // Call get_links() with all the appropriate params
      get_links($cat->cat_ID, '<li>', "</li>", "\n", true, 'name', false);

      // Close the last category
		echo "\n\t</ul></div>\n";
   }
}


?>
<div class="float_clearer"><hr /></div>
<p>If you have a good link, <a href="/contact/">send it in</a>.</p>
</div><!-- .single_column -->
<?php get_footer(); ?>