<?php

class edit_delete extends edit_base {

    function edit_delete($gallery, $image, $tab) {
        parent::edit_base($gallery, $image, $tab, true);      
    }

    function output() {
        global $page;
        $result = get_string('deletecheck', '', '/'.$this->gallery->folder.'/'.$this->image).'<br /><br />';
        $result .= '<input type="hidden" name="page" value="'.$page.'" />';
        $result .= '<input type="submit" value="'.get_string('yes').'" />';
        return $this->enclose_in_form($result);        
    }

    function process_form() {
        global $CFG, $page;
        @unlink($this->imageobj->filename);
        delete_records('lightboxgallery_image_meta', 'gallery', $this->gallery->id, 'image', $this->image);
        redirect($CFG->wwwroot.'/mod/lightboxgallery/view.php?l='.$this->gallery->id.'&amp;page='.$page.'&amp;editing=1');
    }

}

?>
