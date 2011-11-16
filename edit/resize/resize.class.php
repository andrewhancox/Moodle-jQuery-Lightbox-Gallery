<?php

class edit_resize extends edit_base {

    var $strresize;
    var $strscale;
    var $resizeoptions;

    function edit_resize($gallery, $image, $tab) {
        parent::edit_base($gallery, $image, $tab, true);
        $this->strresize = get_string('edit_resize', 'lightboxgallery');
        $this->strscale = get_string('edit_resizescale', 'lightboxgallery');
        $this->resizeoptions = lightboxgallery_resize_options();    
    }

    function output() {
        $currentsize = sprintf('%s: %dx%d', get_string('currentsize', 'lightboxgallery'), $this->imageobj->width, $this->imageobj->height).'<br /><br />';

        $sizeselect = '<select name="size">';
        foreach ($this->resizeoptions as $index => $option) {
            $sizeselect .= '<option value="' . $index . '">' . $option . '</option>';
        }
        $sizeselect .= '</select>&nbsp;<input type="submit" name="button" value="' . $this->strresize . '" /><br /><br />';

        $scaleselect = '<select name="scale">'.
                       '  <option value="150">150&#37;</option>'.
                       '  <option value="125">125&#37;</option>'.
                       '  <option value="75">75&#37;</option>'.
                       '  <option value="50">50&#37;</option>'.
                       '  <option value="25">25&#37;</option>'.
                       '</select>&nbsp;<input type="submit" name="button" value="' . $this->strscale . '" />';

        return $this->enclose_in_form($currentsize . $sizeselect . $scaleselect);        
    }

    function process_form() {
        $button = required_param('button', PARAM_TEXT);

        switch ($button) {
            case $this->strresize:
                $size = required_param('size', PARAM_INT);
                list($width, $height) = explode('x', $this->resizeoptions[$size]);
            break;
            case $this->strscale:
                $scale = required_param('scale', PARAM_INT);
                $width = $this->imageobj->width * ($scale / 100);
                $height = $this->imageobj->height * ($scale / 100);
            break;
        }

        $this->imageobj->resize($width, $height);
    }

}

?>
