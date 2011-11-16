<?php

require_once($CFG->libdir . '/filelib.php');
require_once('imageclass.php');

define('THUMB_WIDTH', 120);
define('THUMB_HEIGHT', 105);
define('MAX_IMAGE_LABEL', 13);
define('MAX_COMMENT_PREVIEW', 20);

define('AUTO_RESIZE_SCREEN', 1);
define('AUTO_RESIZE_UPLOAD', 2);
define('AUTO_RESIZE_BOTH', 3);

function lightboxgallery_add_instance($gallery) {
    global $CFG;

    if (! lightboxgallery_rss_enabled()) {
        $gallery->rss = 0;
    }
   
    $gallery->timemodified = time();

    return insert_record('lightboxgallery', $gallery);
}


function lightboxgallery_update_instance($gallery) {
    global $CFG;

    $gallery->id = $gallery->instance;

    if (! lightboxgallery_rss_enabled()) {
        $gallery->rss = 0;
    }

    if (isset($gallery->autoresizedisabled)) {
        $gallery->autoresize = 0;
        $gallery->resize = 0;
    }

    $gallery->timemodified = time();

    return update_record('lightboxgallery', $gallery);
}


function lightboxgallery_delete_instance($id) {
    if ($gallery = get_record('lightboxgallery', 'id', $id)) {
        $result = true;

        $result = $result && delete_records('lightboxgallery', 'id', $gallery->id);
        $result = $result && delete_records('lightboxgallery_comments', 'gallery', $gallery->id);
        $result = $result && delete_records('lightboxgallery_image_meta', 'gallery', $gallery->id);

    } else {
        $result = false;
    }

    return $result;
}

function lightboxgallery_user_outline($course, $user, $mod, $resource) {
    if ($logs = get_records_select('log', "userid='$user->id' AND module='lightboxgallery' AND action='view' AND info='$resource->id'", 'time ASC')) {
        $numviews = count($logs);
        $lastlog = array_pop($logs);

        $result = new object;
        $result->info = get_string('numviews', '', $numviews);
        $result->time = $lastlog->time;
        return $result;
    } else {
        return null;
    }
}

function lightboxgallery_user_complete($course, $user, $mod, $resource) {
    global $CFG;

    if ($logs = get_records_select('log', "userid='$user->id' AND module='lightboxgallery' AND action='view' AND info='$resource->id'", 'time ASC')) {
        $numviews = count($logs);
        $lastlog = array_pop($logs);

        $strnumviews = get_string('numviews', '', $numviews);
        $strmostrecently = get_string('mostrecently');

        echo("$strnumviews - $strmostrecently " . userdate($lastlog->time));

        $sql = "SELECT c.*
                  FROM {$CFG->prefix}lightboxgallery_comments c
                       JOIN {$CFG->prefix}lightboxgallery l ON l.id = c.gallery
                       JOIN {$CFG->prefix}user            u ON u.id = c.userid
                 WHERE l.id = {$mod->instance} AND u.id = {$user->id}
              ORDER BY c.timemodified ASC";

        if ($comments = get_records_sql($sql)) {
            $cm = get_coursemodule_from_id('lightboxgallery', $mod->id);
            $context = get_context_instance(CONTEXT_MODULE, $cm->id);
            foreach ($comments as $comment) {
                lightboxgallery_print_comment($comment, $context);
            }
        }
    } else {
        print_string('neverseen', 'resource');
    }
}

function lightboxgallery_get_recent_mod_activity(&$activities, &$index, $timestart, $courseid, $cmid, $userid=0, $groupid=0) {
    global $CFG, $COURSE;

    if ($COURSE->id == $courseid) {
        $course = $COURSE;
    } else {
        $course = get_record('course', 'id', $courseid);
    }

    $modinfo =& get_fast_modinfo($course);

    $cm = $modinfo->cms[$cmid];

    $sql = "SELECT c.*, l.name, u.firstname, u.lastname, u.picture
              FROM {$CFG->prefix}lightboxgallery_comments c
                   JOIN {$CFG->prefix}lightboxgallery l ON l.id = c.gallery
                   JOIN {$CFG->prefix}user            u ON u.id = c.userid
             WHERE c.timemodified > $timestart AND l.id = {$cm->instance}
                   " . ($userid ? "AND u.id = $userid" : '') . "
          ORDER BY c.timemodified ASC";

    if ($comments = get_records_sql($sql)) {
        foreach ($comments as $comment) {
            $display = lightboxgallery_resize_text(trim(strip_tags($comment->comment)), MAX_COMMENT_PREVIEW);

            $activity = new object();

            $activity->type         = 'lightboxgallery';
            $activity->cmid         = $cm->id;
            $activity->name         = format_string($cm->name, true);
            $activity->sectionnum   = $cm->sectionnum;
            $activity->timestamp    = $comment->timemodified;

            $activity->content = new object();
            $activity->content->id      = $comment->id;
            $activity->content->comment = $display;

            $activity->user = new object();
            $activity->user->id        = $comment->userid;
            $activity->user->firstname = $comment->firstname;
            $activity->user->lastname  = $comment->lastname;
            $activity->user->picture   = $comment->picture;

            $activities[$index++] = $activity;

        }
    }
    return true;
}

function lightboxgallery_print_recent_mod_activity($activity, $courseid, $detail, $modnames, $viewfullnames) {
    global $CFG;

    echo('<table border="0" cellpadding="3" cellspacing="0">');

    echo('<tr><td class="userpicture" valign="top">' . print_user_picture($activity->user, $courseid, $activity->user->picture, 0, true) . '</td><td>');

    echo('<div class="title">');

    if ($detail) {
        echo('<img src="' . $CFG->modpixpath . '/' . $activity->type. '/icon.gif" class="icon" alt="' . s($activity->name) . '" />');
    }

    echo('<a href="' . $CFG->wwwroot . '/mod/lightboxgallery/view.php?id=' . $activity->cmid . '#c' . $activity->content->id . '">' . $activity->content->comment . '</a>');

    echo('</div>');

    $fullname = fullname($activity->user, $viewfullnames);
    echo('<div class="user">' .
         ' <a href="' . $CFG->wwwroot . '/user/view.php?id=' . $activity->user->id . '&amp;course=' . $courseid . '"> ' . $fullname . '</a> - ' . userdate($activity->timestamp) .
         '</div>');

    echo('</td></tr></table>');

    return true;
}

function lightboxgallery_print_recent_activity($course, $viewfullnames, $timestart) {
    global $CFG;

    $sql = "SELECT c.*, l.name, u.firstname, u.lastname
              FROM {$CFG->prefix}lightboxgallery_comments c
                   JOIN {$CFG->prefix}lightboxgallery l ON l.id = c.gallery
                   JOIN {$CFG->prefix}user            u ON u.id = c.userid
             WHERE c.timemodified > $timestart AND l.course = {$course->id}
          ORDER BY c.timemodified ASC";

    if ($comments = get_records_sql($sql)) {
        print_headline(get_string('newgallerycomments', 'lightboxgallery').':', 3);

        echo('<ul class="unlist">');

        $strftimerecent = get_string('strftimerecent');

        foreach ($comments as $comment) {
            $display = lightboxgallery_resize_text(trim(strip_tags($comment->comment)), MAX_COMMENT_PREVIEW);

            echo('<li>' .
                 ' <div class="head">' .
                 '  <div class="date">' . userdate($comment->timemodified, $strftimerecent) . '</div>' .
                 '  <div class="name">' . fullname($comment, $viewfullnames) . ' - ' . format_string($comment->name) . '</div>' .
                 ' </div>'.
                 ' <div class="info">'.
                 '  "<a href="' . $CFG->wwwroot . '/mod/lightboxgallery/view.php?l=' . $comment->gallery . '#c' . $comment->id . '">' . $display . '</a>"' .
                 ' </div>'.
                 '</li>');
        }
        echo('</ul>');
    }

    return true;
}

function lightboxgallery_get_participants($galleryid) {
    global $CFG;

    return get_records_sql("SELECT DISTINCT u.id, u.id
                              FROM {$CFG->prefix}user u,
                                   {$CFG->prefix}lightboxgallery_comments c
                             WHERE c.gallery = $galleryid AND u.id = c.userid");
}

function lightboxgallery_get_view_actions() {
    return array('view', 'view all', 'search');
}

function lightboxgallery_get_post_actions() {
    return array('comment', 'addimage', 'editimage');
}

function lightboxgallery_get_types() {
    $types = array();

    $type = new object;
    $type->modclass = MOD_CLASS_RESOURCE;
    $type->type = 'lightboxgallery';
    $type->typestr = get_string('modulenameadd', 'lightboxgallery');
    $types[] = $type;

    return $types;
}

// Custom lightboxgallery methods

function lightboxgallery_config_defaults() {
    $defaults = array(
        'disabledplugins' => '',
        'enablerssfeeds' => 0,
        'strictfilenames' => 0,
        'overwritefiles' => 1,
        'imagelifetime' => 86400
    );

    $localcfg = get_config('lightboxgallery');

    foreach ($defaults as $name => $value) {
        if (! isset($localcfg->$name)) {
            set_config($name, $value, 'lightboxgallery');
        }
    }
}

function lightboxgallery_get_file_extension($filename) {
    return strtolower(substr(strrchr($filename, '.'), 1));
}

function lightboxgallery_allowed_filetypes() {
    return array('jpg', 'jpeg', 'gif', 'png');
}

function lightboxgallery_allowed_filetype($element) {
    $extension = lightboxgallery_get_file_extension($element);
    return in_array($extension, lightboxgallery_allowed_filetypes());
}

function lightboxgallery_directory_images($directory) {
    $files = get_directory_list($directory, '', false, false, true);
    return array_filter($files, 'lightboxgallery_allowed_filetype');
}

function lightboxgallery_get_image_url($galleryid, $image = false, $thumb = false) {
    global $CFG;

    $script = $CFG->wwwroot . '/mod/lightboxgallery/pic.php';
    $path = $galleryid . ($image ? '/' . rawurlencode($image) : '');

    if ($CFG->slasharguments) {
        $url = $script . '/' . $path . ($thumb ? '?thumb=1' : '');
    } else {
        $url = $script . '?file=/' . $path . ($thumb ? '&amp;thumb=1' : '');
    }

    return $url;
}

function lightboxgallery_make_img_tag($path, $imageid = '',$cacheBust = true) {
    $cacheBust;
    if (!$cacheBust){
        $cacheBust = '';
    } elseif(!strpos ( $path , '?')){
        $cacheBust = '?rnd='.rand(0,1000);
    } else {
        $cacheBust = '&rnd='.rand(0,1000);
    }
        
    return '<img src="' . $path.$cacheBust . '" alt="" ' . (! empty($imageid) ? 'id="' . $imageid . '"' : '' )  . ' />';
}

function lightboxgallery_image_thumbnail($courseid, $gallery, $file, $forcenew = false) {
    global $CFG;

    $fallback = '['.$file.']';

    $oldpath = $CFG->dataroot.'/'.$courseid.'/'.$gallery->folder.'/'.$file;
    $newpath = $CFG->dataroot.'/'.$courseid.'/'.$gallery->folder.'/_thumb/'.$file.'.jpg';

    if ($forcenew || !file_exists($newpath)) {
        $thumb = new lightboxgallery_edit_image($oldpath);
        if (! $thumb->create_thumbnail()) {
            return $fallback;
        }
    }

    return lightboxgallery_make_img_tag(lightboxgallery_get_image_url($gallery->id, $file, true),'',false);
}

function lightboxgallery_index_thumbnail($courseid, $gallery, $file = '') {
    global $CFG;

    $gallerypath = $CFG->dataroot . '/' . $courseid . '/' . $gallery->folder;

    $indexpath = $gallerypath . '/_thumb/index.png';

    $webpath = $courseid . '/' . $gallery->folder . '/_thumb/index.png';

    if (! file_exists($indexpath) || ! empty($file)) {

        if (empty($file)) {
            if (! $images = lightboxgallery_directory_images($gallerypath)) {
                return;
            }
            $file = $images[0];           
        }

        $thumbpath = $gallerypath . '/_thumb/' . $file . '.jpg';

        if (! file_exists($thumbpath)) {
            $thumbparent = new lightboxgallery_edit_image($gallerypath . '/' . $file);
            if (! $thumbparent->create_thumbnail()) {
                return;
            }
        }

        $base = new lightboxgallery_edit_image('index.png');
        $thumb = new lightboxgallery_edit_image($thumbpath);

        $transparent = imagecolorat($base->image, 0, 0);

        $shrunk = imagerotate($thumb->resize(48, 48, 0, 0, true), 351, $transparent, 0);

        imagecolortransparent($base->image, $transparent);

        imagecopy($base->image, $shrunk, 2, 3, 0, 0, imagesx($shrunk), imagesy($shrunk));

        if (! $base->save_image($base->image, $indexpath)) {
            return;
        }

    }

    return lightboxgallery_make_img_tag($CFG->wwwroot . '/file.php' . ($CFG->slasharguments ? '/' : '?file=/') . $webpath);
}

function lightboxgallery_resize_text($text, $length) {
    $textlib = textlib_get_instance();
    return ($textlib->strlen($text) > $length ? $textlib->substr($text, 0, $length) . '...' : $text);
}

function lightboxgallery_resize_label($label) {
    return lightboxgallery_resize_text($label, MAX_IMAGE_LABEL);
}

function lightboxgallery_print_comment($comment, $context) {
    global $CFG, $COURSE;

    $user = get_record('user', 'id', $comment->userid);

    echo('<table cellspacing="0" width="50%" class="boxaligncenter datacomment forumpost">');

    echo('<tr class="header"><td class="picture left">' . print_user_picture($user, $COURSE->id, $user->picture, 0, true) . '</td>');

    echo('<td class="topic starter" align="left"><a name="c' . $comment->id . '"></a><div class="author">');
    echo('<a href="'.$CFG->wwwroot.'/user/view.php?id='.$user->id.'&amp;course='.$COURSE->id.'">' . fullname($user, has_capability('moodle/site:viewfullnames', $context)) . '</a> - '.userdate($comment->timemodified));
    echo('</div></td></tr>');

    echo('<tr><td class="left side">');
    if ($groups = user_group($COURSE->id, $user->id)) {
        print_group_picture($groups, $COURSE->id, false, false, true);
    } else {
        echo('&nbsp;');
    }

    echo('</td><td class="content" align="left">');

    echo(format_text($comment->comment, FORMAT_MOODLE));

    echo('<div class="commands">');
    if (has_capability('mod/lightboxgallery:edit', $context)) {
        echo('<a href="'.$CFG->wwwroot.'/mod/lightboxgallery/comment.php?id='.$comment->gallery.'&amp;delete='.$comment->id.'">'.get_string('delete').'</a>');
    }
    echo('</div>');

    echo('</td></tr></table>');
}

function lightboxgallery_image_modified($file) {
    $timestamp = 0;
    if (function_exists('exif_read_data') && $exif = @exif_read_data($file, 0, true)) {
        if (isset($exif['IFD0']['DateTime'])) {
            $date = preg_split('/[:]|[ ]/', $exif['IFD0']['DateTime']);
            $timestamp = mktime($date[3], $date[4], $date[5], $date[1], $date[2], $date[0]);
        }
    }
    if (! $timestamp > 0) {
        $timestamp = filemtime($file);
    }
    return date('d/m/y H:i', $timestamp);
}

function lightboxgallery_image_info($file) {
    $result = new object;
    $result->filesize  = display_size(filesize($file));
    $result->modified  = lightboxgallery_image_modified($file);
    $result->imagesize = getimagesize($file);
    return $result;
}

function lightboxgallery_set_image_caption($galleryid, $image, $caption) {
    if ($oldcaption = get_record('lightboxgallery_image_meta', 'metatype', 'caption', 'gallery', $galleryid, 'image', $image)) {
        $oldcaption->description = $caption;
        return update_record('lightboxgallery_image_meta', $oldcaption);
    } else if (trim($caption) != '') {
        $newcaption = new object;
        $newcaption->gallery = $galleryid;
        $newcaption->image = $image;
        $newcaption->metatype = 'caption';
        $newcaption->description = $caption;
        return insert_record('lightboxgallery_image_meta', $newcaption);
    }
}

function lightboxgallery_edit_types($showall = false) {
    global $CFG;

    $result = array();

    $disabledplugins = explode(',', get_config('lightboxgallery', 'disabledplugins'));

    $edittypes = get_list_of_plugins('mod/lightboxgallery/edit');

    foreach ($edittypes as $edittype) {
        if ($showall || !in_array($edittype, $disabledplugins)) {
            $result[$edittype] = get_string('edit_' . $edittype, 'lightboxgallery');
        }
    }

    return $result;
}

function lightboxgallery_print_tags($heading, $tags, $courseid, $galleryid) {
    global $CFG;

    print_simple_box_start('center');

    echo('<form action="search.php" style="float: right; margin-left: 4px;">' .
         ' <fieldset class="invisiblefieldset">' . 
         '  <input type="hidden" name="id" value="' . $courseid . '" />' .
         '  <input type="hidden" name="l" value="' . $galleryid . '" />' .
         '  <input type="text" name="search" size="8" />' .
         '  <input type="submit" value="' . get_string('search') . '" />' .
         ' </fieldset>' .
         '</form>');

    echo($heading . ': ');
    $tagarray = array();
    foreach ($tags as $tag) {
        $tagarray[] = '<a class="taglink" href="' . $CFG->wwwroot . '/mod/lightboxgallery/search.php?id=' . $courseid . '&amp;l=' . $galleryid . '&amp;search=' . urlencode(stripslashes($tag->description)) . '">' . s($tag->description) . '</a>';
    }
    echo(implode(', ', $tagarray));

    print_simple_box_end();
}

function lightboxgallery_resize_options() {
    return array(1 => '1280x1024', 2 => '1024x768', 3 => '800x600', 4 => '640x480');
}

function lightboxgallery_rss_enabled() {
    global $CFG;

    return ($CFG->enablerssfeeds && get_config('lightboxgallery', 'enablerssfeeds'));
}

function lightboxgallery_print_js_config($autoresize) {
    global $CFG;

    $resizetoscreen = (int)in_array($autoresize, array(AUTO_RESIZE_SCREEN, AUTO_RESIZE_BOTH));

    $jsconf = array(
        'resizetoscreen' => $resizetoscreen,
        'download' => get_string('imagedownload', 'lightboxgallery'),
        'forcedownload' => $CFG->slasharguments ? '?' : '&'
    );

    $jsconfvalues = array();

    foreach ($jsconf as $key => $value) {
        $jsconfvalues[] = "$key: '$value'";
    }

    echo('<script type="text/javascript">
           //<![CDATA[
             lightboxgallery_config = {' . implode(', ', $jsconfvalues) . '};
           //]]>
         </script>');
}

?>
