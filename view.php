<?php

    require_once('../../config.php');
    require_once($CFG->libdir . '/filelib.php');
    require_once($CFG->libdir . '/rsslib.php');
    require_once('lib.php');

    $id = optional_param('id', 0, PARAM_INT);
    $l = optional_param('l' , 0, PARAM_INT);
    $page = optional_param('page', 0, PARAM_INT);
    $search = optional_param('search', '', PARAM_TEXT);
    $editing = optional_param('editing', 0, PARAM_BOOL);

    if ($id) {
        if (! $cm = get_coursemodule_from_id('lightboxgallery', $id)) {
            error('Course module ID was incorrect');
        }    
        if (! $course = get_record('course', 'id', $cm->course)) {
            error('Course is misconfigured');
        }    
        if (! $gallery = get_record('lightboxgallery', 'id', $cm->instance)) {
            error('Course module is incorrect');
        }
    } else {
        if (! $gallery = get_record('lightboxgallery', 'id', $l)) {
            error('Course module is incorrect');
        }
        if (! $course = get_record('course', 'id', $gallery->course)) {
            error('Course is misconfigured');
        }
        if (! $cm = get_coursemodule_from_instance('lightboxgallery', $gallery->id, $course->id)) {
            error('Course module ID was incorrect');
        }
    }

    if ($gallery->ispublic) {
        course_setup($course->id);
        $userid = (isloggedin() ? $USER->id : 0);
    } else {
        require_login($course->id);
        $userid = $USER->id;
    }

    $context = get_context_instance(CONTEXT_MODULE, $cm->id);

    if ($editing) {
        require_capability('mod/lightboxgallery:edit', $context);
    }

    if (!$cm->visible and !has_capability('moodle/course:viewhiddenactivities', $context)) {
        notice(get_string("activityiscurrentlyhidden"),$CFG->wwwroot . '/course/view.php?id=' . $course->id);
    }    

    lightboxgallery_config_defaults();

    add_to_log($course->id, 'lightboxgallery', 'view', 'view.php?id=' . $cm->id . '&page=' . $page, $gallery->id, $cm->id, $userid);

    require_js(array('js/slimbox2.js'));

    $navigation = build_navigation('', $cm);

    $update = update_module_button($cm->id, $course->id, get_string('modulenameshort', 'lightboxgallery'));

    if (has_capability('mod/lightboxgallery:edit', $context)) {
        $options = array('id' => $cm->id, 'page' => $page, 'editing' => ($editing ? '0' : '1'));
        $update = print_single_button($CFG->wwwroot.'/mod/lightboxgallery/view.php', $options, get_string('turnediting' . ($editing ? 'off' : 'on')), 'get', '', true) . $update;
    }

    $allowrssfeed = (lightboxgallery_rss_enabled() && $gallery->rss);

    if ($allowrssfeed) {
        $rsspath = rss_get_url($course->id, $userid, 'lightboxgallery', $gallery->id);
        $meta .= "\n" . '<link rel="alternate" href="' . $rsspath . '" type="application/rss+xml" title="' . format_string($gallery->name) . '" id="gallery" />';
    }

    print_header($course->shortname . ': ' . $gallery->name, $course->fullname, $navigation, '', '', true, $update, navmenu($course, $cm));

    $heading = get_string('displayinggallery', 'lightboxgallery', $gallery->name);

    if ($allowrssfeed) {
        $heading .= ' ' . rss_get_link($course->id, $userid, 'lightboxgallery', $gallery->id, get_string('rsssubscribe', 'lightboxgallery'));
    }

    print_heading($heading);

    lightboxgallery_print_js_config($gallery->autoresize);

    $fobj = new object;
    $fobj->para = false;

    if ($gallery->description && !$editing) {
        print_simple_box(format_text($gallery->description, FORMAT_MOODLE, $fobj), 'center');
    }

    print_simple_box_start('center');

    $dataroot = $CFG->dataroot . '/' . $course->id . '/' . $gallery->folder;
    $webroot = lightboxgallery_get_image_url($gallery->id);

    $allimages = lightboxgallery_directory_images($dataroot);
    $images = ($gallery->perpage == 0 ? $allimages : array_slice($allimages, $page * $gallery->perpage, $gallery->perpage));

    $captions = array();
    if ($cobjs = get_records_select('lightboxgallery_image_meta',  "metatype = 'caption' AND gallery = $gallery->id")) {
        foreach ($cobjs as $cobj) {
            $captions[$cobj->image] = s($cobj->description);
        }
    }

    if (count($images) > 0) {
        $edittypes = ($editing ? lightboxgallery_edit_types() : null);
        foreach ($images as $image) {
            $imageextra = '';
            $imageurl = $webroot.'/'.$image;
            $imagelocal = $dataroot.'/'.$image;
            $imagelabel = lightboxgallery_resize_label($image);
            if ($edittypes) {
                $imageextra = '<form action="'.$CFG->wwwroot.'/mod/lightboxgallery/imageedit.php" method="get">'.
                              '<fieldset class="invisiblefieldset">'.
                              '<input type="hidden" name="id" value="'.$gallery->id.'" />'.
                              '<input type="hidden" name="image" value="'.$image.'" />'.
                              '<input type="hidden" name="page" value="'.$page.'" />'.
                              '<select name="tab" class="lightbox-edit-select" onchange="submit();">'.
                              '<option>' . get_string('choose') . '...</option>';
                foreach ($edittypes as $editoption => $editdisplay) {
                    $imageextra .= '<option value="'.$editoption.'">'.$editdisplay.'</option>';
                }
                $imageextra .= '</select></fieldset></form>';
            } else if ($gallery->extinfo) {
                $iobj = lightboxgallery_image_info($imagelocal);
                $imageextra = sprintf('<br />%s<br />%s, %dx%d', $iobj->modified, $iobj->filesize, $iobj->imagesize[0], $iobj->imagesize[1]);
            }
            $imagetitle = (isset($captions[$image]) ? $captions[$image] : $image);
            echo('<div class="thumb">
                    <div class="image"><a class="overlay" href="'.$imageurl.'?rnd='.rand(0,1000) .'" rel="lightbox[gallery-' . $gallery->id . ']" title="'.$imagetitle.'">'.lightboxgallery_image_thumbnail($course->id, $gallery, $image).'</a></div>
                    '.$imagelabel.$imageextra.'
                  </div>');
        }
    } else {
        print_string('errornoimages', 'lightboxgallery');
    }

    print_simple_box_end();

    if ($gallery->perpage) {
        print_paging_bar(count($allimages), $page, $gallery->perpage, $CFG->wwwroot.'/mod/lightboxgallery/view.php?id='.$cm->id.'&amp;' . ($editing ? 'editing=1&amp;' : ''));
    }

    $showtags = !in_array('tag', explode(',', get_config('lightboxgallery', 'disabledplugins')));

    if (!$editing && $showtags) {
        $sql = 'SELECT description
                FROM ' . $CFG->prefix . 'lightboxgallery_image_meta
                WHERE gallery = ' . $gallery->id . '
                AND metatype = \'tag\'
                GROUP BY description
                ORDER BY COUNT(description) DESC, description ASC';
        if ($tags = get_records_sql($sql, 0, 10)) {
            lightboxgallery_print_tags(get_string('tagspopular', 'lightboxgallery'), $tags, $course->id, $gallery->id);
        }
    }

    $options = array();

    if ($gallery->folder && has_capability('mod/lightboxgallery:addimage', $context)) {
        $options[] = '<a href="' . $CFG->wwwroot . '/mod/lightboxgallery/imageadd.php?id=' . $gallery->id . '">' . get_string('addimage', 'lightboxgallery') . '</a>';
    }

    if ($gallery->comments && has_capability('mod/lightboxgallery:addcomment', $context)) {
        $options[] = '<a href="' . $CFG->wwwroot . '/mod/lightboxgallery/comment.php?id=' . $gallery->id . '">' . get_string('addcomment', 'lightboxgallery') . '</a>';
    }

    if (count($options) > 0) {
        echo('<div style="text-align:center; font-size: 0.8em;">' . implode(' | ', $options) . '</div>');
    }

    if (!$editing && $gallery->comments && has_capability('mod/lightboxgallery:viewcomments', $context)) {
        if ($comments = get_records('lightboxgallery_comments', 'gallery', $gallery->id, 'timemodified ASC')) {
            foreach ($comments as $comment) {
                lightboxgallery_print_comment($comment, $context);
            }
        }
    }

    print_footer($course);

?>

