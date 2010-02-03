<?php

//error_reporting(E_ALL);
//ini_set('display_errors', 1);

require_once('admin.php');

$title = 'Jerome\'s Keywords';
$this_file = 'jeromes-keywords/options-jkeywords.php';
$parent_file = 'options-general.php';

$lp_tags =& $JKeywords->admin;
$opt =& $JKeywords->option;

/* presentation data for each configurable option */
$option_data = array(
    array('query_varname', 'Keyword/tag search base:', 40, '', 'output', $JKeywords->base_url),
    array('template',      'Search results template:', 40, '', 'notes', 
            "Create a template file with this name in your theme's directory to display custom
            results. Otherwise, search results will use 'tags.php' if it exists, or your category
            template."),
    array('search_strict', 'Exact search matches only:', 1, '1', 'notes',
            "Return only exact matches when searching tags/keywords.  Turning this off allows partial
            matches:  e.g. clicking the tag 'cat' would also return posts tagged with 'catamaran'."),
    array('use_feed_cats', 'Include tags as categories in feeds:', 1, '1', 'notes',
            'This will index your tags with <a href="http://technorati.com/tag">Technorati</a>.'),
    array('meta_autoheader', 'Automatically include in header:', 1, '1', 'notes',
            'Includes the meta keywords tag automatically in your header (most, but not all, themes support this).'),
    array('meta_always_include', 'Always add these keywords:', 80, '', 'none', ''),
    array('meta_includecats', 'Include categories as keywords:', 2, 'default/all/none', 'notes', 
            "'Default' = includes all categories on the homepage, and only categories for posts in the
            current view for all other pages.<br/>'All' = includes all categories in every view.<br/>
            'None' = never includes categories in the meta keywords."),
    array('post_linkformat', 'Post tag link format:', 80, '', 'output', 
            $JKeywords->formatLink('sample-tag', $JKeywords->getTagPermalink('sample-tag'),
            $opt['post_linkformat'], 1) ),
    array('post_tagseparator', 'Post tag separator(s):', 40, '', 'none', ' '),
    array('post_includecats', 'Include categories in tag list:', 1, '1', 'none', ' '),
    array('post_notagstext', 'Text to display if no tags found:', 80, '', 'none', ' '),
    array('cloud_linkformat', 'Cloud tag link format:', 80, '', 'output', 
            $JKeywords->formatLink('sample-tag', $JKeywords->getTagPermalink('sample-tag'),
            $opt['cloud_linkformat'], 1) ),
    array('cloud_tagseparator', 'Cloud tag separator(s):', 40, '', 'none', ' '),
    array('cloud_includecats', 'Include categories in tag cloud:', 1, '1', 'none', ' '),
    array('cloud_sortorder', 'Tag cloud sort order:', 2, 'natural/countup/countdown/asc/desc/random', 'notes', 
            "'Natural' = natural case sorting (i.e. treats capital & non-capital the same). <br/>
            'Alpha' = strict alphabetic order (capitals first).
            'Countup/Asc' = ascending order by tag usage, 'Countdown/Desc' = descending order <br/>
            'Random' = randomized every time the page is loaded."),
    array('cloud_displaymax', 'Maximum number of tags to display:', 40, '', 'notes', 
            "Set to zero (0) to show all tags."),
    array('cloud_displaymin', 'Minimum tag count required:', 40, '', 'notes', 
            "Tags must be used at least this many times to show up in the cloud."),
    array('cloud_scalemax', 'Tag count scaling maximum:', 40, '', 'notes', 
            "Set to zero (0) to disable tag scaling."),
    array('cloud_scalemin', 'Tag count scaling minimum:', 40, '', 'notes', 
            "Use with the maximum scale to limit the range between your most popular and least popular tags.")
    );


/* handle form actions */
switch ((isset($_POST['action'])) ? $_POST['action'] : '') {
    case 'update':
        foreach($opt as $key => $value) {
            switch ($key) {
                case 'version':
                case 'keywords_table':
                    // don't update these
                    break;
                default:
                    $newval = (isset($_POST[$key])) ? stripslashes($_POST[$key]) : '0';
                    if ($newval != $value)
                        $JKeywords->setOption($key, $newval);
                    break;
            }
        }
        $JKeywords->saveOptions();
        $pagemsg = "Jerome's Keywords options updated!";
        break;
    default:
        // no action
        $pagemsg = (isset($_REQUEST['page_message'])) ? stripslashes($_REQUEST['tag_message']) : '';
        break;
}
if (!empty($pagemsg))
    $pagemsg = '<div class="updated"><p>' . $pagemsg . '</p></div>';


/* URL for form actions */
$actionurl = $_SERVER['REQUEST_URI'];


/* create tables */
$table_content = array('', '', '', '');
$table_rows = array(4, 7, 11, 18);
$rows_done = 0;

foreach($option_data as $option) {
    /* determine input type */
    switch($option[2]) {
        case 1:     // checkbox
            $input_type = '<input type="checkbox" id="' . $option[0] . '" name="' . $option[0] .
                          '" value="' . htmlspecialchars($option[3]) . '" ' . 
                          ( ($opt[ $option[0] ]) ? 'checked="checked"' : '') . ' />';
            break;
        case 2:     // select/dropdown
            $selopts = explode('/', $option[3]);
            $seldata = '';
            foreach($selopts as $sel) {
                $seldata .= '<option value="' . $sel . '" ' . 
                            (($opt[ $option[0] ] == $sel) ? 'selected="selected"' : '') . 
                            ' >' . ucfirst($sel) . '</option>';
            }
            $input_type = '<select id="' . $option[0] . '" name="' . $option[0] . '">' . 
                          $seldata . '</select>';
            break;
        default;    // text input
            $input_type = '<input type="text" ' . (($option[2]>50) ? ' style="width: 95%" ' : '') .
                          'id="' . $option[0] . '" name="' . $option[0] .
                          '" value="' . htmlspecialchars($opt[ $option[0] ]) . '" size="' . $option[2] .'" />';
            break;
    }
    /* create extra details */
    switch($option[4]) {
        case 'output':
            $extra = '<br />' . __('Output:') . ' <strong>' . htmlspecialchars($option[5]) . '</strong>';
            break;
        case 'none':
            $extra = '';
            break;
        default:
            $extra = '<br />' . __($option[5]);
            break;
    }
    $opt_row  = '
      <tr style="vertical-align: top;">
        <th scope="row">' . __($option[1]) . '</th>
        <td>' . $input_type . '
          ' . $extra . '</td>
      </tr>';
    
    if ($rows_done < $table_rows[0])
        $table_content[0] .= $opt_row;
    elseif ($rows_done < $table_rows[1])
        $table_content[1] .= $opt_row;
    elseif ($rows_done < $table_rows[2])
        $table_content[2] .= $opt_row;
    else
        $table_content[3] .= $opt_row;
    $rows_done++;
}
?>


<div class="wrap">
  <?php echo $pagemsg; ?>

  <h2><?php _e("Jerome's Keywords"); ?></h2>

  <form action="<?php echo $actionurl; ?>" method="post">
  
    <fieldset class="options"> 
    <table class="optiontable"><?php echo $table_content[0]; ?>
    </table>
    </fieldset>
    
    <fieldset class="options"> 
      <legend><?php _e('Meta Keyword Options') ?></legend> 
    <table class="optiontable"><?php echo $table_content[1]; ?>
    </table>
    </fieldset>
    
    <fieldset class="options"> 
      <legend><?php _e('Post Tags Display') ?></legend> 
    <table class="optiontable"><?php echo $table_content[2]; ?>
    </table>
    </fieldset>
    
    <fieldset class="options"> 
      <legend><?php _e('Tag Cloud Display') ?></legend> 
    <table class="optiontable"><?php echo $table_content[3]; ?>
    </table>
    </fieldset>
    
    <p class="submit">
      <input type="submit" name="Submit" value="<?php _e('Update Options') ?> &raquo;" />
      <input type="hidden" name="action" value="update" /> 
    </p>
    
  </form>
  
  <h3><?php _e('Link Formats'); ?></h3>
  <?php _e('<p>
    You can customize the look of your tags and how the links work by changing the
    default link formats.  These can be made up of standard (X)HTML, and you can use the following
    special format strings which will be replaced when your tags are displayed.
    <ul>
        <li><code>%tagname%</code> &ndash; Replaced by the tag (or keyword).</li>
        <li><code>%taglink%</code> &ndash; Encoded version of the tag which is safe to use in links.</li>
        <li><code>%fulltaglink%</code> &ndash; Full permalink to the tag on this site.</li>
        <li><code>%flickr%</code> &ndash; Encoded version of the tag which conforms to Flickr link standards.</li>
        <li><code>%delicious%</code> &ndash; Encoded version of the tag which conforms to del.icio.us link standards.</li>
        <li><code>%count%</code> &ndash; Replaced by the actual (or scaled) number of times the tag has been used.
            Only used when generating tag clouds or top tag lists.</li>
        <li><code>%em%</code> and %/em% &ndash; Similar to %count% but creates nested &lt;em&gt; tags,
            similar to how Technorati\'s tag cloud is built.</li>
    </ul>
  </p>
  <p>  
    <code>%keyword%</code>, <code>%keylink%</code> and <code>%fullkeylink%</code>
    are equivalent to using the first three listed.
  </p>'); ?>
</div>

