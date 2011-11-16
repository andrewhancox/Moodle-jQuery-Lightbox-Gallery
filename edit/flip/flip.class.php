<?php

define('FLIP_VERTICAL', 1);
define('FLIP_HORIZONTAL', 2);

class edit_flip extends edit_base {

    function edit_flip($gallery, $image, $tab) {
        parent::edit_base($gallery, $image, $tab, true);      
    }

    function output() {
        $result = get_string('selectflipmode', 'lightboxgallery').'<br /><br />'.
                  '<label><input type="radio" name="mode" value="'.FLIP_VERTICAL.'" />Vertical</label><br />'.
                  '<label><input type="radio" name="mode" value="'.FLIP_HORIZONTAL.'" />Horizontal</label>'.
                  '<br /><br /><input type="submit" value="'.get_string('edit_flip', 'lightboxgallery').'" />';

        return $this->enclose_in_form($result);        
    }

    function process_form() {
        global $CFG;

        $mode = required_param('mode', PARAM_INT);
        $w = $this->imageobj->width;
        $h = $this->imageobj->height;
        $truecolor = (function_exists('imagecreatetruecolor') and $CFG->gdversion >= 2);

        $flipped = $this->imageobj->create_new_image($w, $h);
        if ($mode & FLIP_VERTICAL) {
            if ($truecolor) {
                for ($x = 0; $x < $w; $x++) {
                    for ($y = 0; $y < $h; $y++) {
                        imagecopy($flipped, $this->imageobj->image, $w - $x - 1, $y, $x, $y, 1, 1);
                    }
                }
            } else {
                for ($y = 0; $y < $h; $y++) {
                    imagecopy($flipped, $this->imageobj->image, 0, $y, 0, $h - $y - 1, $w, 1);
                }
            }
        }
        if ($mode & FLIP_HORIZONTAL) {
            if ($truecolor) {
                for ($x = 0; $x < $w; $x++) {
                    for ($y = 0; $y < $h; $y++) {
                        imagecopy($flipped, $this->imageobj->image, $x, $h - $y - 1, $x, $y, 1, 1);
                    }
                }
            } else {
                for ($x = 0; $x < $w; $x++) {
                    imagecopy($flipped, $this->imageobj->image, $x, 0, $w - $x - 1, 0, 1, $h);
                }
            }
        }

        $this->imageobj->save_image($flipped);
    }

}

?>
