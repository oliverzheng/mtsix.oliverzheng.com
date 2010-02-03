<?php
/*
Plugin Name: Official Comments
Version: 1.1
Plugin URI: http://inner.geek.nz/archives/2005/01/12/wp-plugin-official-comments/
Description: Adds functions to distinguish authorised WP user's comments.
Author: Brett Taylor
Author URI: http://inner.geek.nz/
*/

add_action('comment_post','wbft_comment_post_is_user_logged_in');

if (!function_exists('comment_user_id')){
  function comment_user_id() {
    return (is_wpuser_comment());
  }
}

function is_wpuser_comment() {
  global $comment;
  return $comment->user_id;
}

function comment_wpusername() {
  global $tableusers, $wpdb, $comment;
  if ($comment->user_id != 0) {
    $query = "SELECT user_login FROM $tableusers WHERE ID = ".$comment->user_id ;
    $authors = $wpdb->get_results($query);
    $username = $authors[0]->user_login;
    return ($username);
  } else {
    return (false);
  }
}

function wbft_comment_post_is_user_logged_in($comment_id){
  global $wpdb, $tablecomments, $approved, $user_ID;
  get_currentuserinfo();
  if ($user_ID) {
    $wpdb->query("UPDATE $tablecomments SET user_id = $user_ID WHERE comment_ID='".$comment_id."'");
    $approved = 1; // change to 0 to put WP user's comments in the comment moderation queue.
  }
}
?>
