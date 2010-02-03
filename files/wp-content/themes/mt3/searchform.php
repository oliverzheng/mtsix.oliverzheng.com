	<ul class="subheader_items_search">
		<li>
			<form method="get" id="searchform" action="<?php bloginfo('home'); ?>/">
				<p><label class="form_label form_label_search" for="s">Search</label>
				<input type="text" id="s" class="form_text_search" value="<?php echo wp_specialchars($s, 1); ?>" name="s" /><input type="image" name="submit" src="/wp-content/themes/mt3/images/search_icon.png" class="form_search_image" /></p>
			</form>
		</li>
	</ul><!-- .subheader_items -->
