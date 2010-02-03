<?php

/***** Functions for pre-2.0 compatibility ****/

/* use in the loop*/
function get_the_post_keytags($include_cats=null, $localsearch="tag", $linktitle=null) {
	global $JKeywords;

	// determine link mode
	$linkmode = strtolower(trim($localsearch));
	switch ($linkmode) {
		case '':
		case 'technorati':
            $format = $JKeywords->link_technorati;
            break;
		case 'search':
			$format = $JKeywords->link_localsearch;
            break;
        default:
			$format = $JKeywords->post_linkformat;
			break;
	}
    return $JKeywords->formatPostTags($include_cats, $format);
}

/* use in the loop*/
function the_post_keytags($include_cats=null, $localsearch="tag", $linktitle=false) {
	$taglist = get_the_post_keytags($include_cats, $localsearch, $linktitle);
	
	if (empty($taglist))
		echo 'none';
	else
		echo $taglist;
}

/* works outside the loop*/
function get_the_keywords($before='', $after='', $separator=',', $include_cats=null) {
	global $JKeywords;
    return $JKeywords->getMetaKeywords($before, $after, $separator, $include_cats);
}

/* works outside the loop */
function the_keywords($before='', $after='', $separator=',') {
	echo get_the_keywords($before, $after, $separator);
}

function is_keyword() {
	global $JKeywords;
    return $JKeywords->is_keyword();
}

function get_the_search_keytag() {
    global $JKeywords;
    return $JKeywords->keyword;
}

function the_search_keytag() {
	echo get_the_search_keytag();
}

/* tag cosmos/cloud - works outside of the loop */
function all_keywords($element = null, $element_cat = '', $min_scale = null, $max_scale = null, 
                      $min_include = null, $max_include = null, $sort_order = null) {
	global $JKeywords;
    
	$include_cats = !empty($element_cat);

	echo $JKeywords->formatAllTags($include_cats, $element, $sort_order, $max_include,
                                   $min_include, $max_scale, $min_scale);
}


function top_keywords($number = false, $element='<li><a href="%fulltaglink%">%keyword%</a></li>',
                      $element_cat = '', $min_include = 0) {
	global $JKeywords;
	
	$include_cats = !empty($element_cat);
	
	echo $JKeywords->formatAllTags($include_cats, $element, 'countdown', $number,
                                   $min_include, 0, 0);
}

/* Simple tag link cleanup */
function jkeywords_localLink($keyword) {
    return $JKeywords->localLink($keyword);
}



/* Below are old functions which I don't intend on supporting any further.
 * They have been updated to work with the 2.0 plugin version, but may not
 * make it into later versions.
 */

/* function to use in the loop */
function get_the_post_keywords($include_cats=true) {
	global $JKeywords;
	$keywords = '';

	if ($include_cats) {
		$categories = get_the_category();
		foreach($categories as $category) {
			if (!empty($keywords))
				$keywords .= ",";
			$keywords .= $category->cat_name;
		}
	}
    
    global $id;
	$post_keywords = $JKeywords->getPostTags($id);
	if (is_array($post_keywords)) {
		foreach($post_keywords as $post_keys) {
			if (!empty($post_keys))
				$keywords .= ",";
			$keywords .= $post_keys;
		}
	}
	return( $keywords );
}

/* use in the loop*/
function the_post_keywords($include_cats=true) {
	echo get_the_post_keywords($include_cats);
}

/* Tag cosmos function - works outside of the loop */
function get_all_keywords($include_cats = false) {
    global $JKeywords;
    
    if ($include_cats)
        $alltags = $JKeywords->getAllCombined();
    else
        $alltags = $JKeywords->getAllTags();

    /* re-arrange to be key=tagname, value=count */
    $keywordarray = array();
    foreach($alltags as $tag)
        $keywordarray[ $tag['name'] ] = $tag['count'];
    uksort($keywordarray, 'strnatcasecmp');
	
	return($keywordarray);
}

/* Top keywords function - works outside of the loop */
function get_top_keywords($number = false, $include_cats = false, $min_include = 0) {
    global $JKeywords;
    
    if ($include_cats)
        $alltags = $JKeywords->getAllCombined();
    else
        $alltags = $JKeywords->getAllTags();
	
    /* limit number of results */
    $limit = (int) $number;
    if (($limit > 0) && (count($alltags) > $limit)) {       // already in descending order
        $alltags = array_slice($alltags, 0, $limit);
    }

    /* re-arrange to be key=tagname, value=count, excluding low counts */
    $topkeys = array();
    foreach($alltags as $tag) {
        if ($tag['count'] >= $min_include)
            $topkeys[ $tag['name'] ] = $tag['count'];
    }
	return($alltags);
}


?>