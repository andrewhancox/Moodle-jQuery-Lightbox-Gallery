<?php

    require_once('../../config.php');
    require_once($CFG->libdir . '/filelib.php');
    require_once('lib.php');

    $id = required_param('id', PARAM_INT);
    $l = optional_param('l' , 0, PARAM_INT);
    $search = optional_param('search', '', PARAM_RAW);

    $search = trim(strip_tags($search));

    if (! $course = get_record('course', 'id', $id)) {
        error('Course is misconfigured');
    }

    if ($l && ! $gallery = get_record('lightboxgallery', 'id', $l)) {
        error('Course module is incorrect');
    }

    if (isset($gallery) && $gallery->ispublic) {
        course_setup($course->id);
        $userid = 0;
    } else {
        require_login($course->id);
        $userid = $USER->id;
    }

    add_to_log($course->id, 'lightboxgallery', 'search', 'search.php?id=' . $course->id . '&l=' . $l . '&search=' . $search, $search, 0, $userid);

    require_js(array('scripts/prototype.js', 'scripts/scriptaculous.js', 'scripts/effects.js', 'scripts/lightbox.js', 'scripts/thumbglow.js'));

    $navlinks = array();
    $navlinks[] = array('name' => get_string('search'), 'link' => '', 'type' => 'misc');
    $navlinks[] = array('name' => "'" . s($search, true) . "'", 'link' => '', 'type' => 'misc');

    if (isset($gallery)) {
        if (! $cm = get_coursemodule_from_instance('lightboxgallery', $gallery->id, $course->id)) {
            error('Course Module ID was incorrect');
        }
        $heading = $course->shortname . ': ' . $gallery->name;
        $navigation = build_navigation($navlinks, $cm);
    } else {
        $strmodplural = get_string('modulenameplural', 'lightboxgallery');
        array_unshift($navlinks, array('name' => $strmodplural, 'link' => $CFG->wwwroot . '/mod/lightboxgallery/index.php?id=' . $course->id, 'type' => 'activity'));
        $heading = $course->shortname . ': ' . $strmodplural;
        $navigation = build_navigation($navlinks);
    }

    echo('<br />');

    lightboxgallery_print_js_config(1);

    if ($instances = get_all_instances_in_course('lightboxgallery', $course)) {
        $options = array(0 => get_string('all'));
        foreach ($instances as $instance) {
            $options[$instance->id] = $instance->name;
        }

        echo('<form action="search.php"><div>');

        $table = new object;
        $table->width = '*';
        $table->align = array('left', 'left', 'left', 'left');
        $table->data[] = array(get_string('modulenameshort', 'lightboxgallery'), choose_from_menu($options, 'l', $l, '', '', '', true),
                               '<input type="text" name="search" size="10" value="' . s($search, true) . '" />' .
                               '<input type="hidden" name="id" value="' . $course->id . '" />',
                               '<input type="submit" value="' . get_string('search') . '" />') ;
        print_table($table);

        echo('</div></form>');
    }

    $galleryselect = (isset($gallery) ? 'AND l.id = ' . $gallery->id : '');

    $like = sql_ilike();

    $sql = "SELECT m.image, m.metatype, m.description, l.id AS lid
            FROM {$CFG->prefix}lightboxgallery l, {$CFG->prefix}lightboxgallery_image_meta m
            WHERE m.gallery = l.id
            AND l.course = $course->id
            AND m.description $like '%$search%'
            $galleryselect
            ORDER BY l.id, m.image";

    $textlib = textlib_get_instance();

    if (($textlib->strlen($search) > 2) && ($images = get_records_sql($sql))) {
        $imagesdisplay = array();
        $currentgallery = 0;
        foreach ($images as $image) {
            if ($currentgallery != $image->lid) {
                if ($currentgallery > 0) {
                    print_simple_box_end();
                }
                $gallery = get_record('lightboxgallery', 'id', $image->lid);

                $dataroot = $CFG->dataroot . '/' . $gallery->course . '/' . $gallery->folder;
                $webroot = lightboxgallery_get_image_url($gallery->id);

                $currentgallery = $gallery->id;

                print_heading('<a href="' . $CFG->wwwroot . '/mod/lightboxgallery/view.php?l=' . $gallery->id . '">' . $gallery->name . '</a>');
                print_simple_box_start('center');
            }
            $imagelabel = lightboxgallery_resize_label($image->image);
            echo('<div class="thumb">
                    <div class="image"><a class="overlay" href="' . $webroot . '/' . $image->image . '" rel="lightbox[search-result]" title="' . ($image->metatype == 'caption' ? s($image->description) : $image->image) . '">'.lightboxgallery_image_thumbnail($gallery->course, $gallery, $image->image).'</a></div>
                    '.$imagelabel.'
                  </div>');
            $imagesdisplay[] = "'{$image->lid}{$image->image}'";
        }
        print_simple_box_end();

        if (count($imagesdisplay) > 0) {
            $sql = 'SELECT description
                    FROM ' . $CFG->prefix . 'lightboxgallery_image_meta
                    WHERE CONCAT(gallery, image) IN (' . implode(',', $imagesdisplay) . ')
                    AND description != \'' . $search . '\'
                    AND metatype = \'tag\'
                    GROUP BY description
                    ORDER BY COUNT(description) DESC, description ASC';
            if ($tags = get_records_sql($sql, 0, 10)) {
                lightboxgallery_print_tags(get_string('tagsrelated', 'lightboxgallery'), $tags, $course->id, $l);
            }
        }
    } else {
        echo('<br />');
        print_simple_box(get_string('errornosearchresults', 'lightboxgallery'), 'center');
    }

    print_footer($course);

?>

