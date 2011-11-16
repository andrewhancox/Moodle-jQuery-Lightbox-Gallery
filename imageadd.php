<?php

    require_once('../../config.php');
    require_once('lib.php');
    require_once('imageadd_form.php');

    $id = required_param('id', PARAM_INT);

    if (! $gallery = get_record('lightboxgallery', 'id', $id)) {
        error('Course module is incorrect');
    }
    if (! $course = get_record('course', 'id', $gallery->course)) {
        error('Course is misconfigured');
    }
    if (! $cm = get_coursemodule_from_instance('lightboxgallery', $gallery->id, $course->id)) {
        error('Course Module ID was incorrect');
    }

    function lightboxgallery_resize_upload($filename, $dimensions) {
        list($width, $height) = explode('x', $dimensions);
        $image = new lightboxgallery_edit_image($filename);
        return $image->resize($width, $height);
    }

    require_login($course->id);

    $context = get_context_instance(CONTEXT_MODULE, $cm->id);
    require_capability('mod/lightboxgallery:addimage', $context);

    $galleryurl = $CFG->wwwroot . '/mod/lightboxgallery/view.php?id=' . $cm->id;

    $straddimage = get_string('addimage', 'lightboxgallery');

    $navigation = build_navigation($straddimage, $cm);

    print_header($course->shortname . ': ' . $gallery->name . ': ' . $straddimage, $course->fullname, $navigation, '', '', true, '&nbsp;', navmenu($course, $cm));

    $mform = new mod_lightboxgallery_imageadd_form(null, $gallery);

    if ($mform->is_cancelled()) {
        redirect($galleryurl);
    } else if (($formdata = $mform->get_data()) && confirm_sesskey()) {
        require_once($CFG->dirroot . '/lib/uploadlib.php');

        $handlecollisions = !get_config('lightboxgallery', 'overwritefiles');
        $um = new upload_manager('attachment', false, $handlecollisions, $course);

        $uploaddir = $course->id . '/' . $gallery->folder;

        if ($um->process_file_uploads($uploaddir)) {
            $folder = $CFG->dataroot . '/' . $uploaddir;
            $filename = $um->get_new_filename();

            $messages = array();

            if (lightboxgallery_get_file_extension($filename) == 'zip') {
                $thumb = '<img src="' . $CFG->pixpath . '/f/zip.gif" class="icon" alt="zip" />';

                $before = lightboxgallery_directory_images($folder);

                if (unzip_file($folder .  '/' . $filename, $folder, false)) {
                    $messages[] = get_string('zipextracted', 'lightboxgallery', $filename);

                    $after = lightboxgallery_directory_images($folder);

                    if ($newfiles = array_diff($after, $before)) {

                        $resizeoption = 0;

                        if (in_array($gallery->autoresize, array(AUTO_RESIZE_UPLOAD, AUTO_RESIZE_BOTH))) {
                            $resizeoption = $gallery->resize;
                        } else if (isset($formdata->resize))  {
                            $resizeoption = $formdata->resize;
                        }

                        foreach ($newfiles as $newfile) {
                            if ($resizeoption > 0) {
                                $resizeoptions = lightboxgallery_resize_options();
                                if (lightboxgallery_resize_upload($folder . '/' . $newfile, $resizeoptions[$resizeoption])) {
                                    $messages[] = get_string('imageresized', 'lightboxgallery', $newfile);
                                } else {
                                    $messages[] = $newfile;
                                }
                            } else {
                                $messages[] = $newfile;
                            }                        
                        }
                    } else {
                        $messages[] = get_string('zipnonewfiles', 'lightboxgallery');
                    }

                } else {
                    $messages[] = get_string('errorunzippingfiles', 'error');
                }

            } else if (lightboxgallery_allowed_filetype($filename)) {

                $uploadedimage = new lightboxgallery_edit_image($folder . '/' . $filename);

                $thumb = lightboxgallery_image_thumbnail($course->id, $gallery, $filename, true) . '<br />' . $filename;
                $messages[] = get_string('imageuploaded', 'lightboxgallery', $filename);

                if (isset($formdata->caption) && trim($formdata->caption) != '') {
                    lightboxgallery_set_image_caption($gallery->id, $filename, $formdata->caption);
                    $messages[] = get_string('edit_caption', 'lightboxgallery') . ': ' . s($formdata->caption, true);
                }

                $resizeoption = 0;

                if (in_array($gallery->autoresize, array(AUTO_RESIZE_UPLOAD, AUTO_RESIZE_BOTH))) {
                    $resizeoption = $gallery->resize;
                } else if (isset($formdata->resize))  {
                    $resizeoption = $formdata->resize;
                }

                if ($resizeoption > 0) {
                    $resizeoptions = lightboxgallery_resize_options();
                    list($width, $height) = explode('x', $resizeoptions[$resizeoption]);
                    if ($uploadedimage->resize($width, $height)) {
                        $messages[] = get_string('imageresized', 'lightboxgallery', $resizeoptions[$resizeoption]);
                    }
                }

                if (has_capability('mod/lightboxgallery:edit', $context)) {
                    $messages[] = '<a href="' . $CFG->wwwroot . '/mod/lightboxgallery/imageedit.php?id=' . $gallery->id . '&amp;image=' .  $filename . '">' . get_string('editimage', 'lightboxgallery') . '</a>';
                }

            } else {
                unlink($CFG->dataroot . '/' . $uploaddir . '/' . $filename);
                error(get_string('erroruploadimage', 'lightboxgallery') . ' (' . implode(', ', lightboxgallery_allowed_filetypes()) . ')', $CFG->wwwroot . '/mod/lightboxgallery/imageadd.php?id=' . $gallery->id);
            }

            $table = new object;
            $table->width = '*';
            $table->align = array('center', 'left');

            $table->data[] = array($thumb, '<ul id="messages"><li>' . implode('</li><li>', $messages) . '</li></ul>');

            echo('<br />');

            print_table($table);

            echo('<br />');

            add_to_log($course->id, 'lightboxgallery', 'addimage', 'view.php?id='.$cm->id, $filename, $cm->id, $USER->id);;

        }
    }

    echo('<div style="margin-left: auto; margin-right: auto; font-size: 0.8em; width: 635px;">');
    $mform->display();
    echo('</div>');

    print_footer($course);

?>
