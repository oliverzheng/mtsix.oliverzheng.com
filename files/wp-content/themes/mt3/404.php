<?php get_header(); ?>

	<div class="single_column">
	<h1>Not Found</h1>
	<div id="subheader_content">
		<p class="warning">The request URL was not found.</p>
	</div>
	</div>
</div><!-- #subheader -->

<div id="content">
<div class="single_column real_content">
<p>This is a HTTP 404. Your requested page
<p><code>http://<? echo $_SERVER["HTTP_HOST"].$_SERVER['REQUEST_URI']; ?></code></p>
<p>was not found.</p>
<p><a href="/">Return to home page.</a>
</div><!-- .single_column -->
<?php get_footer(); ?>