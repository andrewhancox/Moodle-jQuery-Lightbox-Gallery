<?php

    require_once('../../config.php');
    require_once('lib.php');
    require_once('comment_form.php');

    $id      = required_param('id', PARAM_INT);
    $delete  = optional_param('delete', 0, PARAM_INT);
    $confirm = optional_param('confirm', 0, PARAM_INT);

    if (! $gallery = get_record('lightboxgallery', 'id', $id)) {
        error('Course module is incorrect');
    }
    if (! $course = get_record('course', 'id', $gallery->course)) {
        error('Course is misconfigured');
    }
    if (! $cm = get_coursemodule_from_instance('lightboxgallery', $gallery->id, $course->id)) {
        error('Course Module ID was incorrect');
    }

    if ($delete && ! $comment = get_record('lightboxgallery_comments', 'gallery', $gallery->id, 'id', $delete)) {
        error('Invalid comment ID');
    }

    require_login($course->id);

    $context = get_context_instance(CONTEXT_MODULE, $cm->id);

    $galleryurl = $CFG->wwwroot.'/mod/lightboxgallery/view.php?id='.$cm->id;

    if ($delete && has_capability('mod/lightboxgallery:edit', $context)) {
        if ($confirm && confirm_sesskey()) {
            delete_records('lightboxgallery_comments', 'id', $comment->id);
            redirect($galleryurl);
        } else {
            print_header();
            lightboxgallery_print_comment($comment, $context);
            echo('<br />');
            notice_yesno(get_string('commentdelete', 'lightboxgallery'),
                         $CFG->wwwroot . '/mod/lightboxgallery/comment.php', $CFG->wwwroot . '/mod/lightboxgallery/view.php',
                         array('id' => $gallery->id, 'delete' => $comment->id, 'sesskey' => sesskey(), 'confirm' => 1), array('id' => $cm->id),
                         'post', 'get');
            print_footer();
            die();
        }
    }

    require_capability('mod/lightboxgallery:addcomment', $context);

    if (! $gallery->comments) {
        error('Comments disabled', $galleryurl);
    }

    $mform = new mod_lightboxgallery_comment_form(null, $gallery);

    if ($mform->is_cancelled()) {
        redirect($galleryurl);
    } else if ($formadata = $mform->get_data()) {
        $newcomment = new object;
        $newcomment->gallery = $gallery->id;
        $newcomment->userid = $USER->id;
        $newcomment->comment = $formadata->comment;
        $newcomment->timemodified = time();
        if (insert_record('lightboxgallery_comments', $newcomment)) {
            add_to_log($course->id, 'lightboxgallery', 'comment', 'view.php?id='.$cm->id, $gallery->id, $cm->id, $USER->id);
            redirect($galleryurl, get_string('commentadded', 'lightboxgallery'));
        } else {
            error('Comment creation failed');
        }
    }

    $navigation = build_navigation(get_string('addcomment', 'lightboxgallery'), $cm);

    print_header($course->shortname . ': ' . $gallery->name, $course->fullname, $navigation, '', '', true, '&nbsp', navmenu($course, $cm));
   
    $mform->display();

    print_footer($course);

?>
