<?php

class edit_rotate extends edit_base {

    function edit_rotate($gallery, $image, $tab) {
        parent::edit_base($gallery, $image, $tab, true);      
    }

    function output() {
        $result = get_string('selectrotation', 'lightboxgallery').'<br /><br />'.
                  '<label><input type="radio" name="angle" value="90" />-90&#176;</label>'.
                  '<label><input type="radio" name="angle" value="180" />180&#176;</label>'.
                  '<label><input type="radio" name="angle" value="270" />90&#176;</label>'.
                  '<br /><br /><input type="submit" value="'.get_string('edit_rotate', 'lightboxgallery').'" />';

        return $this->enclose_in_form($result);        
    }

    function process_form() {
        $angle = required_param('angle', PARAM_INT);

        $rotated = imagerotate($this->imageobj->image, $angle, 0);
        $this->imageobj->save_image($rotated);

    }

}

?>
