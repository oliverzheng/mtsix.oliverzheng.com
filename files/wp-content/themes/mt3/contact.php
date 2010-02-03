<?php
/*
Template Name: Contact
*/
?>
<?php
get_header();
?>
	<div class="single_column">
	<h1>Contact</h1>
	<div id="subheader_content">
		<p class="email">Email me.</p>
	</div>
	</div>
</div><!-- #subheader -->

<div id="content">
<div class="single_column real_content">
<p>Feel free to contact me about anything. If it's a request, please be as specific as possible.</p>
<p>And yes, this is an AJAX contact form, or whatever they call it nowadays.</p>
<div class="box_warning">Do not send dummy emails through this form. Here is a <a href="/mtcontactform/">test contact form</a> you can try out.</div>

<form class="light contact_form" action="<? echo $_SERVER["REQUEST_URI"]; ?>" method="POST">
	<div class="form_description">
		<p>Name and email are required (website is optional).</p>
	</div>
	<p><label class="form_label" for="email_name">Name</label>
	<input type="text" name="email_name" id="email_name" value="<?php echo $comment_author; ?>" class="form_text" tabindex="1" />
	</p>
	<p><label class="form_label" for="email_email">Email</label>
	<input type="text" name="email_email" id="email_email" value="<?php echo $comment_author_email; ?>" class="form_text" tabindex="2" />
	</p>
	<p><label class="form_label" for="email_website">Website</label>
	<input type="text" name="email_website" id="email_website" value="<?php echo $comment_author_url; ?>" class="form_text" tabindex="3" />
	</p>
	<p><label class="form_label" for="email_message">Message</label>
	<textarea cols="20" rows="5" name="email_message" id="email_message" class="form_textarea" tabindex="4"></textarea>
	</p>
	<p><input type="submit" name="submit" value="Submit" id="email_send" class="left" onclick="sendtheemail(); return false;" tabindex="5" /><span id="sending_email" class="ajaxload"></span><span class="contact_sent" id="email_sent">Email Sent</span></p>
	<div class="float_clearer"><hr /></div>
</form>

</div><!-- .single_column -->
<?php get_footer(); ?>
