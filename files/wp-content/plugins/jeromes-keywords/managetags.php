<?php

//error_reporting(E_ALL);
//ini_set('display_errors', 1);

require_once('admin.php');

$title = 'Tags';
$this_file = 'jeromes-keywords/managetags.php';
$parent_file = 'edit.php';


/* handle form actions */
$lp_tags =& $JKeywords->admin;

switch ((isset($_POST['tag_action'])) ? $_POST['tag_action'] : '') {
    case 'renametag':
        $oldtag = stripslashes( (isset($_POST['renametag_old'])) ? $_POST['renametag_old'] : '');
        $newtag = stripslashes( (isset($_POST['renametag_new'])) ? $_POST['renametag_new'] : '');
        $pagemsg = $lp_tags->updateTags($oldtag, $newtag);
        break;
    case 'deletetag':
        $todelete = stripslashes( (isset($_POST['deletetag_name'])) ? $_POST['deletetag_name'] : '');
        $pagemsg = $lp_tags->deleteTags($todelete);
        break;
    case 'addtag':
        $matchtag = stripslashes( (isset($_POST['addtag_match'])) ? $_POST['addtag_match'] : '');
        $newtag   = stripslashes( (isset($_POST['addtag_new'])) ? $_POST['addtag_new'] : '');
        $pagemsg = $lp_tags->updateTags($matchtag, $newtag, false);
        break;
    default:
        // no action
        $pagemsg = (isset($_REQUEST['tag_message'])) ? stripslashes($_REQUEST['tag_message']) : '';
        break;
}
if (!empty($pagemsg))
    $pagemsg = '<div class="updated"><p>' . $pagemsg . '</p></div>';


/* URL for form actions */
$actionurl = $_SERVER['REQUEST_URI'];

/* tag sort order */
$tag_listing = '<p>';
$order_array = array('desc' => 'Most&nbsp;Popular', 'asc' => 'Least&nbsp;Used', 'natural' => 'Alphabetical');
$sortorder = strtolower((isset($_REQUEST['tag_sortorder'])) ? $_REQUEST['tag_sortorder'] : 'desc');
$sortbaseurl = preg_replace('/&?tag_sortorder=[^&]*/', '', $actionurl, 1);
foreach($order_array as $sort => $caption)
    $tag_listing .= ($sort == $sortorder) ? " <strong>$caption</strong> <br />" : 
                    " <a href=\"{$sortbaseurl}&tag_sortorder=$sort\">$caption</a> <br/>";
$tag_listing .= '</p>';

/* create tag listing */
$all_tags = $lp_tags->getAllTags($sortorder);
foreach($all_tags as $tag => $count)
    $tag_listing .= "<li>
        <span style=\"cursor: pointer;\" 
            onclick=\"javascript:updateTagFields(this.innerHTML);\">$tag</span>&nbsp;
        <a href=\"" . get_option('lp_opt_url') . '?tag=' . str_replace('%2F', '/', urlencode($tag)) . 
        "\" title=\"View all posts tagged with $tag\">($count)</a></li>\n";

?>


<div class="wrap">
  <?php echo $pagemsg; ?>

  <h2>Manage Keywords/Tags</h2>

  <table>
  <tr><td style="vertical-align: top;">
  
    <fieldset class="options" id="taglist"><legend>Existing Tags</legend>
      <ul style="list-style: none; margin: 0; padding: 0;"
        <?php echo $tag_listing; ?>
      </ul>
    </fieldset>
  
  </td><td style="vertical-align: top;">

    <fieldset class="options"><legend>Rename Tag</legend>
      <p>Enter the tag to rename and its new value.  You can use this feature to merge tags too.
         Click "Rename" and all posts which use this tag will be updated.</p>
      <p>You can specify multiple tags to rename by separating them with commas.</p>
      <form action="<?php echo $actionurl; ?>" method="post">
        <input type="hidden" name="tag_action" value="renametag" />
        <table>
        <tr><th>Tag(s) to Rename:</th><td> <input type="text" id="renametag_old" name="renametag_old" value="" size="40" /> </td></tr>
        <tr><th>New Tag Name(s):</th><td> <input type="text" id="renametag_new" name="renametag_new" value="" size="40" /> </td></tr>
        <tr><th></th><td> <input type="submit" name="Rename" value="Rename" /> </td></tr>
        </table>
      </form>
    </fieldset>

    <fieldset class="options"><legend>Delete Tag</legend>
      <p>Enter the name of the tag to delete.  This tag will be removed from all posts.</p>
      <p>You can specify multiple tags to delete by separating them with commas.</p>
      <form action="<?php echo $actionurl; ?>" method="post">
        <input type="hidden" name="tag_action" value="deletetag" />
        <table>
        <tr><th>Tag(s) to Delete:</th><td> <input type="text" id="deletetag_name" name="deletetag_name" value="" size="40" /> </td></tr>
        <tr><th></th><td> <input type="submit" name="Delete" value="Delete" /> </td></tr>
        </table>
      </form>
    </fieldset>
    
    <fieldset class="options"><legend>Add Tag</legend>
      <p>This feature lets you add one or more new tags to all posts which match any of the tags given.</p>
      <p>You can specify multiple tags to add by separating them with commas.  If you want the tag(s)
         to be added to all posts, then don't specify any tags to match.</p>
      <form action="<?php echo $actionurl; ?>" method="post">
        <input type="hidden" name="tag_action" value="addtag" />
        <table>
        <tr><th>Tag(s) to Match:</th><td> <input type="text" id="addtag_match" name="addtag_match" value="" size="40" /> </td></tr>
        <tr><th>Tag(s) to Add:</th><td>   <input type="text" id="addtag_new" name="addtag_new" value="" size="40" /> </td></tr>
        <tr><th></th><td> <input type="submit" name="Add" value="Add" /> </td></tr>
        </table>
      </form>
    </fieldset>
    
  </td></tr>
  </table>

  <script type="text/javascript">
    if(document.all && !document.getElementById) {
        document.getElementById = function(id) { return document.all[id]; }
    }
    function addTag(tag, input_element) {
        if (input_element.value.length > 0 && !input_element.value.match(/,\s*$/))
            input_element.value += ", ";
        var re = new RegExp(tag + ",");
        if (!input_element.value.match(re))
            input_element.value += tag + ", ";
    }
    function updateTagFields(tag) {
        addTag(tag, document.getElementById("renametag_old"));
        addTag(tag, document.getElementById("deletetag_name"));
        addTag(tag, document.getElementById("addtag_match"));
    }

  </script>
 </div>

<div class="wrap">
  <h2>Import Tags</h2>
</div>
