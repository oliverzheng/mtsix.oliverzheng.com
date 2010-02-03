<?php // Do not delete these lines
	if ('comments.php' == basename($_SERVER['SCRIPT_FILENAME']))
		die ('Please do not load this page directly. Thanks!');

        if (!empty($post->post_password)) { // if there's a password
            if ($_COOKIE['wp-postpass_' . COOKIEHASH] != $post->post_password) {  // and it doesn't match the cookie
				?>
				
				<p class="nocomments">This post is password protected. Enter the password to view comments.<p>
				
				<?php
				return;
            }
        }

		/* This variable is for alternating comment background */
		$oddcomment = 'alt';
?>

<?php if (('open' == $post->comment_status) ||  $comments): ?>
<div id="subcontent" class="single_comments">
<div class="single_column">

<? if($comments) { ?>
<h2 class="comments_count"><a href="#comment_form" class="post_comment_link">post a comment</a><?php comments_number('No Comments', '1 Comment', '% Comments' );?></h2>

<ol class="comments">
<?php
$counter = 1;
foreach ($comments as $comment) : ?>
<? $post_comments_bg = 1; ?>
<li<?php echo (is_wpuser_comment() ? ' class="me"' : ''); ?>>
	<div class="author"><a href="#comment-<? comment_ID(); ?>" id="comment-<?php comment_ID() ?>" name="comment-<?php comment_ID() ?>" class="number"><? echo $counter; ?></a><span class="date"><?php comment_date('F jS, Y') ?></span><?
				$author_link = get_comment_author_url();
				if ('' != $author_link) {
					echo "<a href=\"$author_link\" class=\"link\">";
				} else {
					echo "<span class=\"link\">";
				}
				comment_author();
				if ('' != $author_link) {
					echo "</a>";
				} else {
					echo "</span>";
				}
				?> says</div>
	<div class="text">
		<?php comment_text(); ?>
	</div>
</li>
<? $counter++; ?>
<?php endforeach; /* end for each comment */ ?>
</ol>
<?
}
?>
<?php if ('open' == $post->comment_status) : ?>
<h2><span class="post_comment">Post a Comment</span></h2>
<form class="dark" method="post" action="<?php echo get_option('siteurl'); ?>/wp-comments-post.php" id="comment_form">
	<?php if ( $user_ID ) : ?>
		<p>Logged in as <a href="<?php echo get_option('siteurl'); ?>/wp-admin/profile.php"><?php echo $user_identity; ?></a>. <a href="<?php echo get_option('siteurl'); ?>/wp-login.php?action=logout" title="Log out of this account">Logout &raquo;</a></p>
	<?php else : ?>
	<div class="form_description">
		<p>Name and email are required (website is optional). Basic HTML is enabled.</p>
		<p>Your email address is not revealed to anyone.</p>
	</div>
	<p><label class="form_label" for="name">Name</label>
	<input type="text" value="<?php echo $comment_author; ?>" name="author" id="name" class="form_text" tabindex="1" />
	</p>
	<p><label class="form_label" for="email">Email</label>
	<input type="text" value="<?php echo $comment_author_email; ?>" name="email" id="email" class="form_text" tabindex="2" />
	</p>
	<p><label class="form_label" for="website">Website</label>
	<input type="text" value="<?php echo $comment_author_url; ?>" name="url" id="website" class="form_text" tabindex="3" />
	</p>
	<? endif; ?>
	<p><label class="form_label" for="comment">Comment</label>
	<textarea cols="20" rows="5" name="comment" id="comment" class="form_textarea" tabindex="4"></textarea>
	</p>
	<input type="hidden" name="comment_post_ID" value="<?php echo $id; ?>" />
	<p><input type="submit" name="submit" value="Submit" tabindex="5" /></p>
	<?php do_action('comment_form', $post->ID); ?>
<div class="float_clearer"><hr /></div>
</form>

<?php endif; // if you delete this the sky will fall on your head ?>
</div>
</div><!--#subcontent -->
<?php endif; // If registration required and not logged in ?>
