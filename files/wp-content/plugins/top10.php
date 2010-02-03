<?php
/*
Plugin Name: Top10
Plugin URI: http://weblogtoolscollection.com/archives/2005/03/14/top-10-plugin/
Description: Top 10 Posts/Views
Author: Laughing Lizard
Author URI: http://www.weblogtoolscollection.com
Port Author: Jon Dingman <http://www.bleakgeek.com/>
Examples: http://www.bleakgeek.com/archives/2005/04/08/wordpress-top10view-count-plugin/
Version: 1.5
*/

/*
## USAGE ##

This plugin uses a table in MySQL.  You may either open up phpMyAdmin and insert this SQL or you may do it via command line.  
Either way, it needs to be there to track the stats.

use <your_wpdatabase>;
create table mostAccessed
(
postnumber int not null,
cntaccess int not null,
primary key(postnumber),
unique id(postnumber)
);

## HTML INSTRUCTIONS ##

Open your index.php and find this line:
<?php } } else { // end foreach, end if any posts ?>

And place this line just above that:
no permalinks:
<?php if ($p > 0) { add_count($p);}?>

using permalinks:
<?php if($id > 0) { add_count($id);} ?>

Now place this code where you want your top10 posts to show up (outside the wp-loop):
<?php show_pop_posts(); ?>

## UPDATE ##
Want to see how many times a post has been viewed?  Follow the instructions below:

Then find this line to your index.php:
<?php comments_popup_link('Comments (0)', 'Comments (1)', 'Comments (%)'); ?>

And add this line right below it (or anywhere around it):
<?php show_post_count($post->ID, $before="(Visited ", $after=" times)"); ?>

*/

function add_count($p_number) {
	$result = mysql_query("select postnumber, cntaccess from mostAccessed where postnumber = '$p_number'");
	$test = 0;
	while ($row = mysql_fetch_array($result, MYSQL_NUM)) {
		$row[1] += 1;
		@mysql_query("update mostAccessed set cntaccess = '$row[1]' where postnumber = '$row[0]'");
		$test = 1;
		}
	if ($test == 0) {
		@mysql_query("insert into mostAccessed(postnumber, cntaccess) values('$p_number', '1')");
		}
	}

function show_pop_posts() {
        global $wpdb, $siteurl, $tableposts;
        $results = $wpdb->get_results("select postnumber, cntaccess from mostAccessed ORDER BY cntaccess DESC LIMIT 7");
        foreach ($results as $result) {
                $postnumber = $result->postnumber;
                $post = @$wpdb->get_row("SELECT ID, post_title  FROM $tableposts WHERE '$postnumber' = ID");
                $post_title = substr($post->post_title, 0, 40);
                $urlperma = get_permalink($post->ID);
                //echo "<li><a href=\"$siteurl\"index.php?p=".$post->ID."&more=1&c=1\" title=\"$text\">$post_title</a></li>";
	          /* Use the line below if you use permalinks in your blog */                
		    echo "<li><a href=\"$urlperma\">$post_title</a></li>";
        }
}  	

function show_comments_posts() {
        global $wpdb, $siteurl, $tableposts;
        $results = $wpdb->get_results("SELECT ID, post_title FROM $tableposts ORDER BY comment_count DESC LIMIT 7");
        foreach ($results as $result) {
			$post_title = substr($result->post_title, 0, 40);
			$urlperma = get_permalink($result->ID);
			echo "<li><a href=\"$urlperma\">$post_title</a></li>";
		}
} 

function show_post_count($postcountID, $before="(Visited ", $after=" times)") {
	global $wpdb, $tableposts;
	$resultscount = $wpdb->get_results("select postnumber, cntaccess from mostAccessed WHERE postnumber = $postcountID");
	if (isset($resultscount)) {
		foreach ($resultscount as $resultcount) {
			$postcount = $resultcount->cntaccess;
			echo $before.$postcount.$after;
			}
		}
	}

?>