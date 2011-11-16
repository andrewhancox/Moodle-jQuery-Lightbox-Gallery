<?php

class edit_tag extends edit_base {

    function sql_select($select = null) {
        return "gallery = {$this->gallery->id} AND image = '{$this->image}' AND metatype = 'tag'" . ($select ? ' AND ' . $select : '');
    }

    function tag_add($image, $tag) {
        $tag = trim(strip_tags($tag));
        $tag = addslashes(strtolower($tag));
        if (! (empty($tag) || record_exists_select('lightboxgallery_image_meta', $this->sql_select("description = '$tag'")))) {
            $record = new object;
            $record->gallery = $this->gallery->id;
            $record->image = $image;
            $record->metatype = 'tag';
            $record->description = $tag;
            insert_record('lightboxgallery_image_meta', $record);
        }
    }

    function tag_remove($tagid) {
        if (record_exists_select('lightboxgallery_image_meta', $this->sql_select('id = ' . $tagid))) {
            delete_records('lightboxgallery_image_meta', 'id', $tagid);
        }
    }

    function output() {
        global $CFG;

        $stradd = get_string('add');

        $manualform = '<input type="text" name="tag" /><input type="submit" value="' . $stradd . '" />';
        $manualform = $this->enclose_in_form($manualform);

        $iptcform = '';
        $deleteform = '';

        $tags = array();
        if ($tagrecords = get_records_select('lightboxgallery_image_meta', $this->sql_select(), 'description', 'id,description')) {
            $errorlevel = error_reporting(E_PARSE);
            $textlib = textlib_get_instance();
            foreach ($tagrecords as $tagrecord) {
                $tags[$tagrecord->id] = $textlib->typo3cs->utf8_decode($tagrecord->description, 'iso-8859-1');
            }
            error_reporting($errorlevel);            
        }

        $path = $CFG->dataroot . '/' . $this->gallery->course . '/' . $this->gallery->folder . '/' . $this->image;

        $size = getimagesize($path, $info);
        if (isset($info['APP13'])) {
            $iptc = iptcparse($info['APP13']);
            if (isset($iptc['2#025'])) {
                $iptcform = '<input type="hidden" name="iptc" value="1" />';
                sort($iptc['2#025']);
                foreach ($iptc['2#025'] as $tag) {
                    $tag = strtolower($tag);
                    $exists = ($tags && in_array($tag, array_values($tags)));
                    $tag = htmlentities($tag);
                  
                    $iptcform .= '<label ' . ($exists ? 'class="tag-exists"' : '') . '><input type="checkbox" name="iptctags[]" value="' . $tag . '" />' . $tag . '</label><br />';
                }
                $iptcform .= '<input type="submit" value="' . $stradd . '" />';
                $iptcform = '<span class="tag-head"> ' . get_string('tagsiptc', 'lightboxgallery') . '</span>' . $this->enclose_in_form($iptcform);
            }
        }

        $iptcform .= print_single_button($CFG->wwwroot . '/mod/lightboxgallery/edit/tag/import.php', array('id' => $this->gallery->id), 
                                                get_string('tagsimport', 'lightboxgallery'), 'post', '_self', true);

        if ($tags) {
            $deleteform = '<input type="hidden" name="delete" value="1" />';
            foreach ($tags as $tagid => $tagname) {
                $deleteform .= '<label><input type="checkbox" name="deletetags[]" value="' . $tagid . '" />' . htmlentities($tagname) . '</label><br />';
            }
            $deleteform .= '<input type="submit" value="' . get_string('remove') . '" />';
            $deleteform = '<span class="tag-head"> ' . get_string('tagscurrent', 'lightboxgallery') . '</span>' . $this->enclose_in_form($deleteform);
        }
        return $manualform . $iptcform . $deleteform;         
    }

    function process_form() {
        $tag = optional_param('tag', '', PARAM_TAG);

        if ($tag) {
            $this->tag_add($this->image, $tag);
        } else if (optional_param('iptc', 0, PARAM_INT)) {
            if ($tags = optional_param('iptctags', array(), PARAM_RAW)) {
                foreach ($tags as $tag) {
                    $this->tag_add($this->image, urldecode(clean_param($tag, PARAM_TAG)));
                }
            }
        } else if (optional_param('delete', 0, PARAM_INT)) {
            if ($deletes = optional_param('deletetags', array(), PARAM_RAW)) {
                foreach ($deletes as $delete) {
                    $this->tag_remove(clean_param($delete, PARAM_INT));
                }
            }
        }
    }

}

?>
