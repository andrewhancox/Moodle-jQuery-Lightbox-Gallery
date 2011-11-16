<?php

class edit_base {

    var $imageobj;

    var $gallery;
    var $image;
    var $tab;
    var $showthumb;

    function edit_base($_gallery, $_image, $_tab, $_deletethumb = false, $_showthumb = true) {
        global $CFG;

        $this->gallery = $_gallery;
        $this->image = $_image;
        $this->tab = $_tab;
        $this->showthumb = $_showthumb;

        if ($_deletethumb && $this->processing()) {
            $thumb = $CFG->dataroot.'/'.$_gallery->course.'/'.$_gallery->folder.'/_thumb/'.$_image.'.jpg';
            @unlink($thumb);
        }

        $this->imageobj = new lightboxgallery_edit_image($CFG->dataroot.'/'.$_gallery->course.'/'.$_gallery->folder.'/'.$_image);
    }

    function processing() {
        return optional_param('process', false, PARAM_BOOL);
    }

    function enclose_in_form($text) {
        global $CFG, $USER;

        return '<form action="'.$CFG->wwwroot.'/mod/lightboxgallery/imageedit.php" method="post">'.
               '<fieldset class="invisiblefieldset">'.
               '<input type="hidden" name="sesskey" value="'.$USER->sesskey.'" />'.
               '<input type="hidden" name="id" value="'.$this->gallery->id.'" />'.
               '<input type="hidden" name="image" value="'.$this->image.'" />'.
               '<input type="hidden" name="tab" value="'.$this->tab.'" />'.
               '<input type="hidden" name="process" value="1" />'.$text.'</fieldset></form>';
    }

    function output() {

    }

    function process_form() {

    }

}

?>
