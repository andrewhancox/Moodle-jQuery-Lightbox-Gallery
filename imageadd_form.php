<?php

    require_once($CFG->libdir . '/formslib.php');

    class mod_lightboxgallery_imageadd_form extends moodleform {

        function definition() {

            global $COURSE;

            $mform =& $this->_form;
            $gallery = $this->_customdata;

            $handlecollisions = !get_config('lightboxgallery', 'overwritefiles');
            $this->set_upload_manager(new upload_manager('attachment', false, $handlecollisions, $COURSE));

            $mform->addElement('header', 'general', get_string('addimage', 'lightboxgallery'));

            $mform->addElement('file', 'attachment', get_string('file'));
            $mform->addRule('attachment', get_string('required'), 'required', null, 'client');
            $mform->setHelpButton('attachment', array('addimage', get_string('addimage', 'lightboxgallery'), 'lightboxgallery'));

            $mform->addElement('text', 'caption', get_string('edit_caption', 'lightboxgallery'));
            $mform->setType('caption', PARAM_NOTAGS);
            $mform->setAdvanced('caption');

            if ($this->can_resize()) {
                $resizegroup = array();
                $resizegroup[] = &$mform->createElement('select', 'resize', get_string('edit_resize', 'lightboxgallery'), lightboxgallery_resize_options());
                $resizegroup[] = &$mform->createElement('checkbox', 'resizedisabled', null, get_string('disable'));
                $mform->setType('resize', PARAM_INT);
                $mform->addGroup($resizegroup, 'resizegroup', get_string('edit_resize', 'lightboxgallery'), ' ', false);
                $mform->setDefault('resizedisabled', 1);
                $mform->disabledIf('resizegroup', 'resizedisabled', 'checked');
                $mform->setAdvanced('resizegroup');
            }

            $mform->addElement('hidden', 'id', 0);
            $mform->setDefault('id', $gallery->id);
            $mform->setType('id', PARAM_INT);

            $this->add_action_buttons(true, get_string('addimage', 'lightboxgallery'));

        }

        function can_resize() {
            global $gallery;

            return !in_array($gallery->autoresize, array(AUTO_RESIZE_UPLOAD, AUTO_RESIZE_BOTH));
        }
    }

?>
