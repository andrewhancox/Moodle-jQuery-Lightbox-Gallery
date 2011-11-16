<?php

require_once($CFG->libdir.'/gdlib.php');

class edit_crop extends edit_base {

    function edit_crop($gallery, $image, $tab) {
        parent::edit_base($gallery, $image, $tab, true, false);      
    }

    function output() {

        require_js(array('js/jquery.Jcrop.min.js'));

        $result = '<script type="text/javascript" charset="utf-8">
                        function saveCoords( coords ) {
                            $("#x1").attr("value", coords.x);
                            $("#y1").attr("value", coords.y);
                            $("#x2").attr("value", coords.x2);
                            $("#y2").attr("value", coords.y2);
                            $("#cropInfo").html("'.get_string('from').':" + coords.x + "x" + coords.y + ", '.get_string('size').': " + coords.w + "x" + coords.h);;
                        }
                        
                    $(document).ready(function(){
                        $("#cropImage").Jcrop({
                                onSelect: saveCoords
                        });
                    });
                    </script>';
        $result .= '<input type="hidden" name="x1" id="x1" value="0" />
                    <input type="hidden" name="y1" id="y1" value="0" />
                    <input type="hidden" name="x2" id="x2" value="0" />
                    <input type="hidden" name="y2" id="y2" value="0" />
                    <table>
                      <tr>
                        <td>'.lightboxgallery_make_img_tag(lightboxgallery_get_image_url($this->gallery->id, $this->image), 'cropImage').'</td>
                      </tr>
                      <tr>
                        <td><span id="cropInfo">&nbsp;</span></td>
                      </tr>
                      <tr>
                        <td><input type="submit" value="'.get_string('savechanges').'" /></td>
                      </tr>
                    </table>';
                                                
        return $this->enclose_in_form($result);        
    }

    function process_form() {
        $x1 = required_param('x1', PARAM_INT);
        $y1 = required_param('y1', PARAM_INT);
        $x2 = required_param('x2', PARAM_INT);
        $y2 = required_param('y2', PARAM_INT);

        $width = $x2 - $x1;
        $height = $y2 - $y1;

        if ($width > 0 && $height > 0) {
            $cropped = $this->imageobj->create_new_image($width, $height);
            imagecopybicubic($cropped, $this->imageobj->image, 0, 0, $x1, $y1, $width, $height, $width, $height);
            $this->imageobj->save_image($cropped);
        }
    }

}


?>