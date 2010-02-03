<?php
/*******************************************************************************
 * LightPress Tag Management - http://lightpress.org
 *
 * Copyright 2004-2005 Ludovico Magnocavallo, Jerome Lavigne.
 * Released under the GPL http://www.gnu.org/copyleft/gpl.html
 *
 * developers:
 *              Ludovico Magnocavallo <ludo@asiatica.org>
 *              Jerome Lavigne <jerome@vapourtrails.ca>
 ******************************************************************************/

class LightPressTagging {

    var $version = '1.2.1';
    
    var $tablename = '';
    var $max_suggest = 10;
    
    var $_tablestruct = " (
        post_id bigint(20) unsigned NOT NULL,
        tag_name varchar(255) NOT NULL default '',
        PRIMARY KEY  (post_id, tag_name),
        KEY tag_name (tag_name)
        )";

    function LightPressTagging($tablename) {
        /* set table name */
        $this->tablename = $tablename;
        
        /* add WP filters & actions for saving/editing tags */
        add_filter('simple_edit_form',  array(&$this, 'showTagEntry'));
        add_filter('edit_form_advanced',array(&$this, 'showTagEntry'));
        add_filter('edit_page_form',    array(&$this, 'showTagEntry'));
        add_action('edit_post',         array(&$this, 'savePostTags'));
        add_action('publish_post',      array(&$this, 'savePostTags'));
        add_action('save_post',         array(&$this, 'savePostTags'));
        
        /* check if we need to upgrade */
        if (get_option('lp_opt_version_tags') < $this->version)
            $this->install();
    }

    function getAllTags($sort='desc') {
        /* get all tags from the db */
        global $wpdb;
        $all_tags = array();
        
        switch($sort) {
            case 'asc':
                $orderby = 'tag_count';
                break;
            case 'desc':
                $orderby = 'tag_count DESC';
                break;
            default:
                $orderby = 'tag_name';
                break;
        }
        
        $tags = $wpdb->get_results("SELECT tag_name, COUNT(post_id) AS tag_count
                                    FROM {$this->tablename}
                                    GROUP BY tag_name ORDER BY $orderby ");
        if (is_array($tags)) {
            foreach($tags as $t)
                $all_tags[$t->tag_name] = $t->tag_count;
            
            switch($sort) {
                case 'natural':
                    uksort($all_tags, 'strnatcasecmp');
                    break;
                default:
                    //do nothing
                    break;
            }
        }
        
        return $all_tags;
    }

    function getPostTags($id) {
        /* get all tags for the specified post from the db */
        global $wpdb;
        $post_tags = array();
        
        $tags = $wpdb->get_results("SELECT tag_name FROM {$this->tablename} WHERE post_id='$id'");
       
        if (is_array($tags)) {
            foreach($tags as $t)
                $post_tags[] = $t->tag_name;
        }
        return $post_tags;
    }
    
    function showTagEntry() {
        /* display tag entry & suggested tag fields */
        global $post;
        
        /* get this post's tags */
        $tags = $this->getPostTags($post->ID);
        $post_tags = implode(', ', $tags);
        
        /* suggest tags based on existing tags & post content */
        $top_tags = $this->getAllTags('desc');
        $suggested = array();
        foreach($top_tags as $tag => $count) {
            if (!in_array($tag, $tags) && stristr($post->post_content, $tag)) {
                $suggested[] = $tag;
                if (count($suggested) >= $this->max_suggest)
                    break;
            }
        }
        if (count($suggested) < $this->max_suggest) {
            foreach($top_tags as $tag => $count) {
                if (!in_array($tag, $tags) && !in_array($tag, $suggested)) {
                    $suggested[] = $tag;
                    if (count($suggested) >= $this->max_suggest)
                        break;
                }
            }
        }
        if (count($suggested) > 0) {
            $suggested_tags = '<span class="lp_tag" onclick="javascript:addTag(this.innerHTML);">'
                . implode('</span> <span class="lp_tag" onclick="javascript:addTag(this.innerHTML);">', $suggested)
                . '</span>';
        } else
            $suggested_tags = '';
        // TODO: add word boundaries to the regexp, or use a global array to store
        //       already added tags, too tired now and too many years since I last
        //       used JS --ludo
        echo '<style type="text/css">
        #lp_tag_entry {
            font-size: 80%;
            margin-left: 1%;
            width: 97%;
            height: 2.5em;
        }
        #lp_taglist {
            margin: 3px 0 3px 1%;
        }
        span.lp_tag {
            font-size: 80%;
            display: block;
            float: left;
            background: #efefef;
            padding: 1px;
            margin: 1px;
            border: solid 1px;
            border-color: #cccccc #999999 #999999 #cccccc;
            color: #333333;
            cursor: pointer;
        }
        </style>
        <script type="text/javascript">
        if(document.all && !document.getElementById) {
            document.getElementById = function(id) { return document.all[id]; }
        }
        function addTag(tag) {
            var lp_tag_entry = document.getElementById("lp_tag_entry");
            if (lp_tag_entry.value.length > 0 && !lp_tag_entry.value.match(/,\s*$/))
                lp_tag_entry.value += ", ";
            var re = new RegExp(tag + ",");
            if (!lp_tag_entry.value.match(re))
                lp_tag_entry.value += tag + ", ";
        }
        </script>';

        /* display tag entry fields */
        echo "<div id=\"lptagstuff\" class=\"dbx-group\" >
            <fieldset class=\"dbx-box\" id=\"posttags\">
                <h3 class=\"dbx-handle\">Tags</h3>
                <div class=\"dbx-content\">
                    <textarea rows=\"2\" cols=\"40\" name=\"tag_list\" tabindex=\"4\" id=\"lp_tag_entry\">$post_tags</textarea>
                    <br />
                    <div id=\"lp_taglist\">Suggested Tags $suggested_tags</div>
                </div>
            </fieldset>
        </div>
            ";
    }

    function saveTag($id, $tag) {
        global $wpdb;
        $wpdb->query("INSERT INTO {$this->tablename} VALUES ('$id', '$tag')");
    }

    function savePostTags($id) {
        /* save new list of post tags to database */
        global $wpdb;
        
        /* clear old values first */
        $wpdb->query("DELETE FROM {$this->tablename} WHERE post_id='$id'");
        
        /* clean up tag list & save */
        $tag_list = (get_magic_quotes_gpc()) ? 
                    $_REQUEST['tag_list'] : addslashes($_REQUEST['tag_list']);
        $post_tags = array_unique(explode(',', $tag_list));
        if (is_array($post_tags)) {
            foreach($post_tags as $tag) {
                $tag = trim($tag);
                if (!empty($tag))
                    $this->saveTag($id, $tag);
            }
        }
    }
    
    function deleteTags($todelete) {
        /* deletes list of tags from the database */
        global $wpdb;
        
        /* split list of tags */
        $old_tags = array_unique(explode(',', $todelete));
        $old_list = '';
        foreach($old_tags as $key=>$tag) {
            if (!empty($old_list))
                $old_list .= ',';
            $old_list .= "'" . addslashes(trim($tag)) . "'";
        }
        
        /* delete old tags */
        if ($wpdb->query("DELETE FROM {$this->tablename} WHERE tag_name IN ($old_list)") > 0)
            return "Deleted the following tag(s): $todelete";
        else
            return "Could not find tag(s) in database: $todelete";
    }

    function updateTags($old, $new, $rename=true) {
        /* resaves list of old tags to new value(s) */
        global $wpdb;
        
        /* split lists of old & new tags */
        $old_tags = array_unique(explode(',', $old));
        $old_list = '';
        foreach($old_tags as $tag) {
            if (!empty($old_list))
                $old_list .= ',';
            $old_list .= "'" . addslashes(trim($tag)) . "'";
        }
        if (trim(str_replace(',', '', stripslashes($new))) == '')
            return ('No new tag specified!'); 
        $new_tags = array_unique(explode(',', $new));
        
        /* get list of posts matching old tags */
        $posts = $wpdb->get_results("SELECT post_id FROM {$this->tablename}
                                    WHERE tag_name IN ($old_list) GROUP BY post_id");
        if (is_array($posts) && (count($posts) > 0)) {
            if ($rename) {
                /* delete old tags */
                $wpdb->query("DELETE FROM {$this->tablename} WHERE tag_name IN ($old_list)");
            }
            
            /* save new tags */
            foreach ($posts as $p) {
                foreach($new_tags as $tag) {
                    $tag = addslashes(trim($tag));
                    /* check if tag already exists for post before saving */
                    if ($wpdb->query("SELECT post_id, tag_name FROM {$this->tablename}
                                      WHERE tag_name='$tag' AND post_id='{$p->post_id}'") <= 0)
                        $this->saveTag($p->post_id, $tag);
                }
            }
            if ($rename)
                return "Renamed tag(s) &laquo;$old&raquo; to &laquo;$new&raquo;";
            else
                return "Added tag(s) &laquo;$new&raquo; to posts tagged with &laquo;$old&raquo;";
        } else {
            return "No posts found matching tag(s): $old";
        }
    }
    
    function install() {
        /* creates the LightPress tags table & imports other tag formats */
        global $wpdb;
        
        /* create tags table if it doesn't exist */
        $table =& $this->tablename;
        $found = false;
        foreach ($wpdb->get_results("SHOW TABLES;", ARRAY_N) as $row) {
            if ($row[0] == $table) {
                $found = true;
                break;
            }
        }
        if (!$found) {
            $res = $wpdb->get_results("CREATE TABLE $table " . $this->_tablestruct);
        }
        
        /* import Jerome's Keywords tags */
        $qry = "SELECT post_id, meta_id, meta_key, meta_value
                FROM  {$wpdb->postmeta} meta
                WHERE meta.meta_key = 'keywords'";
        $metakeys = $wpdb->get_results($qry);
        if (is_array($metakeys)) {
            foreach($metakeys as $post_meta) {
                if ($post_meta->meta_value != '') {
                    $post_keys = explode(',', $post_meta->meta_value);
                    foreach($post_keys as $keyword) {
                        $keyword = addslashes(trim($keyword));
                        if ($keyword != '')
                            $this->saveTag($post_meta->post_id, $keyword);
                    }
                }
                //delete_post_meta($post_meta->post_id, 'keywords');
            }
        }
        
        update_option('lp_opt_version_tags', $this->version);
    }
}

?>