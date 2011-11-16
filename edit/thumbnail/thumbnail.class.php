<?php

class edit_thumbnail extends edit_base {

    function output() {
        $result = '<input type="submit" name="index" value="' . get_string('setasindex', 'lightboxgallery')  . '" /><br /><br />' .
                   get_string('selectthumbpos', 'lightboxgallery') . '<br /><br />';

        if ($this->imageobj->width < $this->imageobj->height) {
            $result .= '<label><input type="radio" name="move" value="1" />' . get_string('dirup', 'lightboxgallery') . '</label>&nbsp;'.
                       '<label><input type="radio" name="move" value="2" />' . get_string('dirdown', 'lightboxgallery') . '</label>';
        } else {
            $result .= '<label><input type="radio" name="move" value="3" />' . get_string('dirleft', 'lightboxgallery') . '</label>&nbsp;'.
                       '<label><input type="radio" name="move" value="4" />' . get_string('dirright', 'lightboxgallery') . '</label>';
        }
        $result .= '<br /><br />' . get_string('thumbnailoffset', 'lightboxgallery') . ': <input type="text" name="offset" value="20" size="4" /><br /><br />'.
                   '<input type="submit" value="' . get_string('move') . '" />&nbsp;<input type="submit" name="reset" value="' . get_string('reset') . '" />';

        return $this->enclose_in_form($result);        
    }

    function process_form() {
        if (optional_param('index', '', PARAM_TEXT)) {
            return lightboxgallery_index_thumbnail($this->gallery->course, $this->gallery, $this->image);
        } else if (optional_param('reset', '', PARAM_TEXT)) {
            $offsetx = 0;
            $offsety = 0;
        } else {
            $move = required_param('move', PARAM_INT);
            $offset = optional_param('offset', 20, PARAM_INT);
            switch ($move) {
                case 1: $offsetx = 0; $offsety = -$offset; break;
                case 2: $offsetx = 0; $offsety = $offset; break;
                case 3: $offsetx = -$offset; $offsety = 0; break;
                case 4: $offsetx = $offset; $offsety = 0; break;
            }  
        }

        $this->imageobj->create_thumbnail($offsetx, $offsety);
    }

}

?>
