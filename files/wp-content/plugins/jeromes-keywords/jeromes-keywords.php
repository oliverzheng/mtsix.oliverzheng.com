<?php
/*
Plugin Name: Jerome's Keywords
Plugin URI: http://vapourtrails.ca/wp-keywords
Version: 2.0-beta3
Description: Allows keywords to be associated with each post, which can be used as meta keywords and for creating a local tag system.
Author: Jerome Lavigne
Author URI: http://vapourtrails.ca
*/

/*	Copyright 2005  Jerome Lavigne  (email : darkcanuck@vapourtrails.ca)
	
	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 2 of the License, or
	(at your option) any later version.
	
	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.
	
	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

/* Credits:
	Special thanks also to Dave Metzener, Mark Eckenrode, Dan Taarin, N. Godbout,
    "theanomaly", "oso", Wayne @ AcmeTech, Will Luke, Gyu-In Lee, Denis de Bernardy,
    Horst Gutmann, "Chip" Camden, Christian Davén, Johannes Jarolim, Mike Koepke
    and the many others who have provided feedback, spotted bugs, and suggested
    improvements.
*/

/* ChangeLog:

9-Sep-2006:  Version 2.0-beta3
    - major overhaul of entire plugin
    - added tag management features from LightPress
    - added default options which are saved to the db
    - moved old functions (template tags) to legacy.php
    - many minor improvements, including random sorting of tag cloud & automatic meta keyword generation

*/

/* uncomment these lines to see how many warnings WP can throw */
//error_reporting(E_ALL);
//ini_set('display_errors', 1);

class JeromesKeywords {

    /* plugin options */
    var $option = array(
        'version'        => '2.0',          // keywords options version
        'keywords_table' => 'jkeywords',    // table where keywords/tags are stored
        'query_varname'  => 'tag',          // HTTP var name used for tag searches
        'template'       => 'keywords.php', // template file to use for displaying tag queries

        'meta_always_include' => '',        // meta keywords to always include
        'meta_includecats' => 'default',    // default' => include cats in meta keywords only for home page
                                            // all' => includes cats on every page, none' => never included
        
        'meta_autoheader'    => '1',        // automatically output meta keywords in header
        'search_strict'      => '1',        // returns only exact tag matches if true
        'use_feed_cats'      => '1',        // insert tags into feeds as categories
    
        /* post tag options */
        'post_linkformat'    => '',         // post tag format (initialized to $link_localsearch)
        'post_tagseparator'  => ', ',       // tag separator character(s)
        'post_includecats'   => '0',        // include categories in post's tag list
        'post_notagstext'    => 'none',     // text to display if no tags found
        
        /* tag cloud options */
        'cloud_linkformat'   => '',         // post tag format (initialized to $link_tagcloud)
        'cloud_tagseparator' => ' ',        // tag separator character(s)
        'cloud_includecats'  => '0',        // include categories in tag cloud
        'cloud_sortorder'    => 'natural',  // tag sorting: natural, countup/asc, countdown/desc, alpha
        'cloud_displaymax'   => '0',        // maximum # of tags to display (all if set to zero)
        'cloud_displaymin'   => '0',        // minimum tag count to include in tag cloud
        'cloud_scalemax'     => '0',        // maximum value for count scaling (no scaling if zero)
        'cloud_scalemin'     => '0'         // minimum value for count scaling
        );
    
    /* standard tag link formats */
    var $link_localsearch = '<a href="%fulltaglink%" title="Search site for %tagname%" rel="tag">%tagname%</a>';
    var $link_technorati = '<a href="http://technorati.com/tag/%taglink%/" title="Technorati tag page for %tagname%" rel="tag">%tagname%</a>';
    var $link_tagcloud = '<li class="cosmos keyword%count%"><a href="%fulltaglink%">%tagname%</a></li>';
    
    /* set during class setup */
    var $keyword = '';          // keyword search value
    var $rewriteon = false;     // defaults to using no rewrite rules
    var $base_url  = '';        // base URL for local tags (depending on permalink style)
    var $admin = null;          // reference to admin class
    
    
    /* private members */
    var $_table = '';           // full name of tags table
    var $_postids = '';         // stores comma-separated list of post IDs in current view
    var $_posttags = null;      // post tag data cache
    var $_alltags = null;       // all published tag data cache
    var $_allcats = null;       // all published categories cache
    var $_allcombined = null;   // sorted compilation of tags & cats
    var $_initdone = false;
    var $_flushrules = false;
    
    
    /* initialization and basic setup methods */

    function JeromesKeywords() {
        $this->loadOptions();
        
        /* set custom table name */
        global $table_prefix;
        $this->_table = $table_prefix . $this->option['keywords_table'];
        
        /* setup filter/action triggers */
        add_action('init', array(&$this, 'initRewrite'));           // can't use WP rewrite flags until "init" hook
        add_filter('the_posts', array(&$this, 'getPostIds'), 90);   // get post IDs once WP query is done
        add_filter('query_vars', array(&$this, 'addQueryVar'));     // used for keyword searches
        add_action('parse_query', array(&$this, 'parseQuery'));     // used for keyword searches
        if (is_admin())
            add_action('admin_menu', array(&$this, 'setAdminHooks'));    // administration interface
        if ($this->option['use_feed_cats'])       // insert tags into feeds as categories
            add_filter('the_category_rss', array(&$this, 'createFeedCategories'), 5, 2);
        if ($this->option['meta_autoheader'])     // automagic meta keywords in header
            add_action('wp_head', array(&$this, 'outputHeader'));
    }
    
    function initRewrite() {
        global $wp_rewrite;
        /* detect permalink type & construct base URL for local links */
        $this->base_url = get_settings('home') . '/';
        if (isset($wp_rewrite) && $wp_rewrite->using_permalinks()) {
            $this->rewriteon = true;                    // using rewrite rules
            $this->base_url .= $wp_rewrite->root;		// set to "index.php/" if using that style
            $this->base_url .= $this->option['query_varname'] . '/';
        } else {
            $this->base_url .= '?' . $this->option['query_varname'] . '=';
        }
        
        /* generate rewrite rules for tag queries */
        if ($this->rewriteon)
            add_filter('search_rewrite_rules', array(&$this, 'createRewriteRules'));
        
        /* flush rules if requested */
        $this->_initdone = true;
        if ($this->_flushrules) 
            $wp_rewrite->flush_rules();
    }

    function createRewriteRules($rewrite) {
        global $wp_rewrite;
        /* add rewrite tokens */
        $qvar =& $this->option['query_varname'];
        $token = '%' . $qvar . '%';
        $wp_rewrite->add_rewrite_tag($token, '(.+)', $qvar . '=');
        
        $keywords_structure = $wp_rewrite->root . $qvar . "/$token";
        $keywords_rewrite = $wp_rewrite->generate_rewrite_rules($keywords_structure);

        return ( $rewrite + $keywords_rewrite );
    }

    function setAdminHooks() {
        /* load administration class */
        include (dirname(__FILE__) . DIRECTORY_SEPARATOR . 'LightPressTagging.php');
        $this->admin =& new LightPressTagging($this->_table);
        
        /* add tag management menu */
        add_management_page('Manage Jerome\'s Keywords', 'Keywords/Tags', 'manage_categories', 'jeromes-keywords/managetags.php');
        add_options_page('Jerome\'s Keywords Options', 'Jerome\'s Keywords', 'manage_options', 'jeromes-keywords/options-jkeywords.php');
    }

    function loadOptions() {
        /* check if options exist */
        if (substr(get_option('jkeywords_version'),1,-1) < 2.0) {
            $this->option['post_linkformat'] = $this->link_localsearch;
            $this->option['cloud_linkformat'] = $this->link_tagcloud;
            $this->saveOptions();
        } else {
            /* read option values from the database, stripping our spacer characters */
            foreach($this->option as $optname => $optval) {
                $newval = substr(get_option('jkeywords_' . $optname), 1, -1);
                if ($newval != '')
                    $this->option[$optname] = $newval;
            }
        }
    }
    
    function setOption($optname, $optval) {
        /* update option value */
        $this->option[$optname] = $optval;
    }

    function saveOptions() {
        /* flush WP rewrite rules if query var has changed */
        global $wp_rewrite;
        $qvar = 'query_varname';
        if ($this->option[$qvar] != substr(get_option("jkeywords_$qvar"),1,-1)) {
            if ($this->_initdone)
                $wp_rewrite->flush_rules();
            else
                $this->_flushrules = true;
        }
        
        /* write current option values to the database */
        /* add a dummy char to beginning & end because WP won't save spaces! */
        foreach($this->option as $optname => $optval)
            update_option('jkeywords_' . $optname, '*' . $optval . '*');
    }


    /* tag fetch & caching */
    
    function getPostIds($posts) {
        /* extract list of post IDs from the posts array */
        if (!is_null($posts) && is_array($posts)) {
            foreach($posts as $p) {
                $this->_postids .= (!empty($this->_postids) ? ',' : '') . $p->ID;   //create comma-separated list
            }
        }
        return $posts;  //send'em back to WP
    }
    
    function getPostTags($postid=null, $force=false) {
        /*
         * caches all tags matching current post selection
         */
        if (is_null($this->_posttags) || $force) {
            if (empty($this->_postids)) {
                $this->_posttags = array(); // no posts, thus no tags
                return $this->_posttags;
            }
            
            global $wpdb;
            $q = "SELECT DISTINCT post_id, tag_name AS name
                    FROM {$this->_table} tags
                    WHERE post_id IN ({$this->_postids})
                    ORDER BY post_id, name";
            $tag_results = $wpdb->get_results($q);
            
            $post_tags = array();
            if (!is_null($tag_results) && is_array($tag_results)) {
                foreach ($tag_results as $tag) {
                    $post_id = $tag->post_id;
                    if (!isset($post_tags[$post_id]))
                            $post_tags[$post_id] = array();
                    $post_tags[$post_id][] = $tag->name;
                }
            }
            $this->_posttags =& $post_tags;
        }
        if (is_null($postid))
            return $this->_posttags;
        elseif (isset($this->_posttags[$postid]))
            return $this->_posttags[$postid];
        else
            return array();
    }

    function getAllTags($force=false) {
        /*
         * gets all published site tags
         */
        if (is_null($this->_alltags) || $force) {
            global $wpdb;
            $q = "SELECT tag.tag_name AS name, COUNT(tag.post_id) AS numposts
                FROM {$this->_table} tag
                INNER JOIN {$wpdb->posts} p ON tag.post_id=p.id
                WHERE (p.post_status='publish' OR p.post_status='static')
                  AND p.post_date_gmt<='" . gmdate("Y-m-d H:i:s", time()) . "'
                GROUP BY tag.tag_name
                ORDER BY numposts DESC ";
            $tag_results = $wpdb->get_results($q);
            
            $alltags = array();
            if (!is_null($tag_results) && is_array($tag_results)) {
                foreach ($tag_results as $tag) {
                    $alltags[$tag->name] = array('name' => $tag->name,
                                                 'count'=> $tag->numposts,
                                                 'link' => $this->getTagPermalink($tag->name)
                                                );
                }
            }
            $this->_alltags =& $alltags;
        }
        return $this->_alltags;
    }

    function getAllCats($force=false) {
        /*
         * gets all published site categories (same format as getAllTags)
         */
        if (is_null($this->_allcats) || $force) {
            global $wpdb;
            $q = "SELECT p2c.category_id AS cat_id, COUNT(p2c.rel_id) AS numposts,
                    UNIX_TIMESTAMP(max(p.post_date_gmt)) + '" . get_option('gmt_offset') . "' AS last_post_date,
                    UNIX_TIMESTAMP(max(p.post_date_gmt)) AS last_post_date_gmt
                FROM {$wpdb->post2cat} p2c
                INNER JOIN {$wpdb->posts} p ON p2c.post_id=p.id
                WHERE (p.post_status='publish' OR p.post_status='static')
                  AND p.post_date_gmt<='" . gmdate("Y-m-d H:i:s", time()) . "'
                GROUP BY p2c.category_id
                ORDER BY numposts DESC ";
            $results = $wpdb->get_results($q);
            
            $allcats = array();
            if (!is_null($results) && is_array($results)) {
                foreach ($results as $cat) {
                    $catname = get_catname($cat->cat_id);
                    $allcats[$catname] = array('name' => get_catname($cat->cat_id),
                                               'count'=> $cat->numposts,
                                               'link' => get_category_link((int)$cat->cat_id)
                                              );
                }
            }
            $this->_allcats =& $allcats;
        }
        return $this->_allcats;
    }

    function getAllCombined($force=false) {
        /*
         * combines all published tags & categories
         */
        if (is_null($this->_allcombined) || $force) {
            $combined = array_merge($this->getAllTags(), $this->getAllCats());
            uasort($combined, array(&$this, 'sortCombined'));
            $this->_allcombined =& $combined;
        }
        return $this->_allcombined;
    }

    function sortCombined($a, $b) {
        /* sort by descending count, ties broken by nat case ascending name */
        if ($a['count'] == $b['count']) 
            return strnatcasecmp($a['name'], $b['name']);
        else
            return ( ($a['count'] > $b['count']) ? -1 : 1 );
    }
    
    
    /* meta keywords methods */
    
    function getMetaKeywords($before='', $after='', $separator=',', $include_cats=null) {
        /* add pre-defined keywords */
        $pagekeys = explode(',', $this->option['meta_always_include']);
        
        /* get tags for all posts in current view */
        foreach($this->getPostTags() as $post_tags)
            $pagekeys = array_merge($pagekeys, $post_tags);
        
        /* add categories if necessary */
        if (is_null($include_cats))
            $include_cats = $this->option['meta_includecats'];
        if ($include_cats) {
            global $category_cache;
            if ( ($include_cats == 'all') || (($include_cats == 'default') && is_home()) ) {
                /* include all site categories */
                foreach($this->getAllCats() as $category)
                    $pagekeys[] = $category['name'];
            } elseif (isset($category_cache) && ($include_cats != 'none')) {
                /* include only categories from posts in current view */
                foreach($category_cache as $post_category) {
                    foreach($post_category as $category)
                        $pagekeys[] = $category->cat_name;
                }
            }
        }
        $pagekeys = array_unique($pagekeys);    // remove duplicates
        
        /* setup meta keywords for page header */
        $keywordlist = '';
        foreach($pagekeys as $keyword) {
            $keywordlist .= (($keywordlist != '') ? $separator : '') .
                                $before . $keyword . $after;
        }
        return htmlspecialchars($keywordlist);
    }
    
    function outputMetaKeywords($before='', $after='', $separator=',', $include_cats=null) {
        /* output meta keyword list */
        echo $this->getMetaKeywords($before, $after, $separator, $include_cats);
    }
    
    function outputHeader() {
        /* automagically output meta keywords tag */
        echo "\t" . '<meta name="keywords" content="' . $this->getMetaKeywords() . '" />';
    }
    
    /* post keywords methods */
    
    function formatPostTags($include_cats=null, $linkformat=null) {
        /* check parameters vs. class options */
        $include_cats = (is_null($include_cats)) ? $this->option['post_includecats'] : $include_cats;
        $linkformat   = (is_null($linkformat))   ? $this->option['post_linkformat']  : $linkformat;
        
        /* create array of tags & full links */
        $taglinks = array();
        if ($include_cats) {
            $categories = get_the_category();
            foreach($categories as $cat)
                $taglinks[ $cat->cat_name ] = get_category_link((int)$cat->cat_ID);
        }
        global $id;
        $post_tags = $this->getPostTags($id);
        foreach($post_tags as $tag) {
            $taglinks [ $tag ] = $this->getTagPermalink($tag);
        }
        
        /* substitute values into link format */
        $output = '';
        foreach($taglinks as $tag => $url) {
            $output .= (($output != '') ? $this->option['post_tagseparator'] : '') . 
                        $this->formatLink($tag, $url, $linkformat);
        }
        return($output);
    }
    
    function outputPostTags($include_cats=null, $linkformat=null) {
        /* output formatted tag list or text indicating no tags */
        $tags = $this->formatPostTags($include_cats, $linkformat);
        if (empty($tags))
            echo $this->option['post_notagstext'];
        else
            echo $tags;
    }
    
    
    /* tag cloud methods */
    
    function formatAllTags($include_cats=null, $linkformat=null, $sort_order=null,
                           $display_max=null, $display_min=null,
                           $scale_max=null, $scale_min=null) {
        /* check parameters vs. class options */
        $include_cats = (is_null($include_cats)) ? $this->option['cloud_includecats']: $include_cats;
        $linkformat   = (is_null($linkformat))   ? $this->option['cloud_linkformat'] : $linkformat;
        $sort_order   = (is_null($sort_order))   ? $this->option['cloud_sortorder']  : $sort_order;
        $display_max  = (is_null($display_max))  ? $this->option['cloud_displaymax'] : $display_max;
        $display_min  = (is_null($display_min))  ? $this->option['cloud_displaymin'] : $display_min;
        $scale_max    = (is_null($scale_max))    ? $this->option['cloud_scalemax']   : $scale_max;
        $scale_min    = (is_null($scale_min))    ? $this->option['cloud_scalemin']   : $scale_min;
        
        /* creatarray of tags & full links */
        if ($include_cats)
            $alltags = $this->getAllCombined();
        else
            $alltags = $this->getAllTags();
        
        /* limit results */
        $limit = (int) $display_max;
        if (($limit > 0) && (count($alltags) > $limit))       
            $alltags = array_slice($alltags, 0, $limit, true);  // already in descending order
        
        /* re-sort results */
        switch(strtolower($sort_order)) {
            case 'alpha':
                ksort($alltags);
                break;
            case 'countup':
            case 'asc':
                $alltags = array_reverse($alltags, true);       // reverse array order to be ascending
                break;
            case 'countdown':
            case 'desc':
                // already in descending order
                break;
            case 'random':
                srand((float)microtime() * 1000000);
                shuffle($alltags);                              // WARNING: keys not kept!
                break;
            default:    // case for 'natural'
                uksort($alltags, 'strnatcasecmp');
                break;
        }
        
        /* scaling */
        $do_scale = ($scale_max != 0);
        if ($do_scale) {
            $minval = $maxval = $alltags[ key($alltags) ]['count'];
            foreach($alltags as $tag) {
                $minval = min($tag['count'], $minval);
                $maxval = max($tag['count'], $maxval, $display_min);
            }
            $minval = max($minval, $display_min);
            $minout = max($scale_min, 0);
            $maxout = max($scale_max, $minout);
            $scale = ($maxval > $minval) ? (($maxout - $minout) / ($maxval - $minval)) : 0;
        }
        
        /* scale counts & format links */
        $output = '';
        foreach($alltags as $tag) {
            if ($tag['count'] >= $display_min) {
                $output .= (($output != '') ? $this->option['cloud_tagseparator'] : '') . 
                            $this->formatLink($tag['name'], $tag['link'], $linkformat, 
                                              ( ($do_scale) ?
                                                (int) (($tag['count'] - $minval) * $scale + $minout) 
                                                : $tag['count'] )
                            );
            }
        }
        return($output);
    }


    /* link formatting */

    function formatLink($name, $full_link, $format, $count=0) {
        /* substitute values into link format */
        $newlink = $format;
        $newlink = str_replace(array('%tagname%', '%keyword%'), $name, $newlink);
        $newlink = str_replace(array('%taglink%', '%keylink%'), $this->localLink($name), $newlink);
        $newlink = str_replace('%flickr%', $this->flickrLink($name), $newlink);
        $newlink = str_replace('%delicious%', $this->deliciousLink($name), $newlink);
        $newlink = str_replace(array('%fulltaglink%', '%fullkeylink%'), $full_link, $newlink);
		$newlink = str_replace('%count%', $count, $newlink);
		$newlink = str_replace('%em%', str_repeat('<em>', $count), $newlink);
		$newlink = str_replace('%/em%', str_repeat('</em>', $count), $newlink);
		return $newlink;
    }
    
    function localLink($keyword) {
        return str_replace('%2F', '/', urlencode($keyword));
    }
    
    function getTagPermalink($tag) {
        return ($this->base_url . 
                (($this->rewriteon) ? $this->localLink($tag) : urlencode($tag)));
    }
    
    function flickrLink($keyword) {
        return urlencode(preg_replace('/[^a-zA-Z0-9]/', '', strtolower($keyword)));
    }
    
    function deliciousLink($keyword) {
        $del = preg_replace('/\s/', '', $keyword);
        if (strstr($del, '+'))
            $del = '"' . $del . '"';
        return str_replace('%2F', '/', rawurlencode($del));
    }
    
    
    /* keyword search results */
    
    function is_keyword() {
        if (!is_null($this->keyword) && ($this->keyword != ''))
            return true;
        else
            return false;
    }

    function addQueryVar($wpvar_array) {
        $wpvar_array[] = $this->option['query_varname'];
        return($wpvar_array);
    }
    
    function parseQuery() {
        /* set the search keyword if it's available */
        /* WP2.0's new rewrite rules mean we need to grab it from the query vars */
        $this->keyword = stripslashes(get_query_var($this->option['query_varname']));
        if (get_magic_quotes_gpc())
            $this->keyword = stripslashes($this->keyword);  // why so many freakin' slashes?

        /* if this is a keyword query, then reset other is_x flags and add query filters */
        if ($this->keyword != '') {
            global $wp_query;
            $wp_query->is_single = false;
            $wp_query->is_page = false;
            $wp_query->is_archive = false;
            $wp_query->is_search = false;
            $wp_query->is_home = false;
            add_filter('posts_where', array(&$this, 'postsWhere'));
            add_filter('posts_join',  array(&$this, 'postsJoin'));
            add_action('template_redirect', array(&$this, 'includeTemplate'));
        }
    }
    
    function postsWhere($where) {
        /* update where clause to search on keywords table */
        if ($this->option['search_strict'])
            $where .= " AND jkeywords.tag_name='" . addslashes($this->keyword) . "' ";
        else
            $where .= " AND jkeywords.tag_name LIKE '%" . addslashes($this->keyword) . "%' ";
        // include pages in search (from jeromes-search.php)
        return str_replace(' AND (post_status = "publish"', ' AND ((post_status = \'static\' OR post_status = \'publish\')', $where);
    }
    
    function postsJoin($join) {
        /* update join clause to include keywords table */
        global $wpdb;
        $join .= " LEFT JOIN {$this->_table} AS jkeywords ON ({$wpdb->posts}.ID = jkeywords.post_id) ";
        return ($join);
    }

    function includeTemplate() {
        /* switch template when doing a keyword search */
        if ($this->is_keyword()) {
            $template = '';
            
            if ( file_exists(TEMPLATEPATH . "/" . $this->option['template']) )
                $template = TEMPLATEPATH . "/" . $this->option['template'];
            else if ( file_exists(TEMPLATEPATH . "/tags.php") )
                $template = TEMPLATEPATH . "/tags.php";
            else
                $template = get_category_template();
            
            if ($template) {
                load_template($template);
                exit;
            }
        }
        return;
    }
    
    
    /* Tagging feeds */

    function createFeedCategories($list, $type) {
        /* insert post's tags as categories */
        global $id;
        $post_tags = $this->getPostTags($id);
        
        foreach($post_tags as $tag) {
            if ($type == "rdf")
                $list .= "<dc:subject>$tag</dc:subject>";
            else
                $list .= "<category>$tag</category>";
        }
        return $list;   //send'em back to WP
    }
}
$JKeywords =& new JeromesKeywords();


/* 2.0 "template tags" */

/* jkeywords_post_tags
 *
 * Outputs the list of tags related to the current post.
 * Use this function "in the loop", e.g.
 *      <?php jkeywords_post_tags(); ? >
 */
function jkeywords_post_tags($include_cats=null, $linkformat=null) {
	global $JKeywords;
    $JKeywords->outputPostTags($include_cats, $linkformat);
}

/* jkeywords_meta_keywords
 *
 * Outputs the list of meta keywords for the current view
 * Use this within your site's header, e.g.
 *      <meta name="keywords" content="<?php jkeywords_meta_keywords(); ? >" />
 */
function jkeywords_meta_keywords($before='', $after='', $separator=',', $include_cats=null) {
	global $JKeywords;
    $JKeywords->outputMetaKeywords($before, $after, $separator, $include_cats);
}

/* jkeywords_is_keyword
 *
 * Use this to check if the current view will be returning keyword search results.
 * Returns TRUE if a keyword search was requested, FALSE otherwise.
 */
function jkeywords_is_keyword() {
	global $JKeywords;
    return $JKeywords->is_keyword();
}

/* jkeywords_search_keyword
 *
 * Outputs the keyword used in a keyword search.  Useful in your tags.php page:
 *      <h2>All results for "<?php jkeywords_search_keyword(); ? >"</h2>
 */
function jkeywords_search_keyword() {
    global $JKeywords;
    echo $JKeywords->keyword;
}

/* jkeywords_tag_cloud
 *
 * Outputs a tag cloud for your entire weblog, e.g.
 *      <h2>All tags on my site</h2>
 *      <?php jkeywords_tag_cloud(); ? >
 */
function jkeywords_tag_cloud($include_cats=null, $linkformat=null, $sort_order=null,
                             $display_max=null, $display_min=null,
                             $scale_max=null, $scale_min=null) {
	global $JKeywords;
	echo $JKeywords->formatAllTags($include_cats, $linkformat, $sort_order, $display_max,
                                   $display_min, $scale_max, $scale_min);
}

/* jkeywords_top_tags
 *
 * Similar to a tag cloud, but sorted with the most popular tags listed first.
 */
function jkeywords_top_tags($display_max=null,
                             $linkformat='<li><a href="%fulltaglink%">%keyword%</a></li>',
                             $sort_order='desc', $display_min=null,
                             $include_cats=null, $scale_max=null, $scale_min=null) {
	global $JKeywords;
	echo $JKeywords->formatAllTags($include_cats, $linkformat, $sort_order, $display_max,
                                   $display_min, $scale_max, $scale_min);
}

/* to provide compatibility with 1.x "template tags" */
include (dirname(__FILE__) . DIRECTORY_SEPARATOR . 'legacy.php');
?>