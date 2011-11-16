<?php

    require_once($CFG->libdir . '/gdlib.php');

    define('THUMBNAIL_WIDTH', 120);
    define('THUMBNAIL_HEIGHT', 105);

    class lightboxgallery_edit_image {

        var $filename;

        var $image;
        var $width;
        var $height;
        var $type;

        function error($text) {
            debugging($text, DEBUG_DEVELOPER);
            return false;
        }

        function lightboxgallery_edit_image($filename) {
            $this->filename = $filename;

            if (file_exists($filename)) {
                $info = getimagesize($filename);

                $supportedtypes = array(IMAGETYPE_GIF => 'gif', IMAGETYPE_JPEG => 'jpeg', IMAGETYPE_PNG => 'png');

                $this->width = $info[0];
                $this->height = $info[1];

                if (in_array($info[2], array_keys($supportedtypes))) {
                    $this->type = $supportedtypes[$info[2]];
                } else {
                    $this->type = '';
                }

                $function = 'imagecreatefrom' . $this->type;

                if (function_exists($function)) {
                    if (! $this->image = $function($filename)) {
                        $this->error("Couldn't create image");
                    }
                }
            } else {
                $this->error("Input file doesn't exist");
            }
        }

        function create_new_image($width, $height) {
            global $CFG;

            if (function_exists('imagecreatetruecolor') && $CFG->gdversion >= 2) {
                return imagecreatetruecolor($width, $height);
            } else {
                return imagecreate($width, $height);
            }
        }

        function resize($width, $height, $offsetx = 0, $offsety = 0, $return = false) {
            $resized = $this->create_new_image($width, $height);

            $cx = $this->width / 2;
            $cy = $this->height / 2;

            $ratiow = $width / $this->width;
            $ratioh = $height / $this->height;

            if ($ratiow < $ratioh) {
                $srcw = floor($width / $ratioh);
                $srch = $this->height;
                $srcx = floor($cx - ($srcw / 2)) + $offsetx;
                $srcy = $offsety;
            } else {
                $srcw = $this->width;
                $srch = floor($height / $ratiow);
                $srcx = $offsetx;
                $srcy = floor($cy - ($srch / 2)) + $offsety;
            }

            imagecopybicubic($resized, $this->image, 0, 0, $srcx, $srcy, $width, $height, $srcw, $srch);

            return ($return ? $resized : $this->save_image($resized));
        }

        function create_thumbnail($offsetx = 0, $offsety = 0) {
            global $CFG;

            $thumbdir = dirname($this->filename) . '/_thumb/';
            $thumbfile = $thumbdir . basename($this->filename) . '.jpg';

            umask(0000);

            if (! file_exists($thumbdir) && ! @mkdir($thumbdir, $CFG->directorypermissions)) {
                return $this->error("Couldn't create thumbnail directory");
            }

            if (! @touch($thumbfile)) {
                return $this->error("Couldn't create thumbnail image");
            }

            $thumb = $this->resize(THUMBNAIL_WIDTH, THUMBNAIL_HEIGHT, $offsetx, $offsety, true);

            if ($result = $this->save_image($thumb, $thumbfile, 'jpeg')) {
                @chmod($thumbfile, 0666);
            }

            return $result;
        }

        function save_image($image, $filename = '', $type = '') {
            if (empty($filename)) {
                $filename = $this->filename;
            }

            if (empty($type)) {
                $type = $this->type;
            }

            $function = 'image' . $type;

            if (function_exists($function) && @$function($image, $filename, ($type == 'png' ? 9 : 100))) {
                imagedestroy($image);
                return true;
            } else {
                return $this->error("Couldn't write image");
            }
        }

    }

?>
