<?php

    require_once('lib.php');

    function lightboxgallery_rss_feed($gallery) {
        global $CFG;

        $result = "";

        $images = lightboxgallery_directory_images($CFG->dataroot . '/' . $gallery->course . '/' . $gallery->folder);

        $captions = array();
        if ($cobjs = get_records_select('lightboxgallery_image_meta',  "metatype = 'caption' AND gallery = $gallery->id")) {
            foreach ($cobjs as $cobj) {
                $captions[$cobj->image] = $cobj->description;
            }
        }

        if (!empty($images)) {
            $webroot = lightboxgallery_get_image_url($gallery->id);
            $dataroot = $CFG->dataroot . '/' . $gallery->course . '/' . $gallery->folder;

            $result .= "<?xml version=\"1.0\" encoding=\"UTF-8\" standalone=\"yes\"?>\n";
            $result .= "<rss version=\"2.0\" xmlns:media=\"http://search.yahoo.com/mrss\" xmlns:atom=\"http://www.w3.org/2005/Atom\">";

            $result .= rss_start_tag('channel', 1, true);

            $result .= rss_full_tag('title', 2, false, strip_tags(format_string($gallery->name, true)));
            $result .= rss_full_tag('link', 2, false, $CFG->wwwroot . '/mod/lightboxgallery/view.php?l=' . $gallery->id);
            $result .= rss_full_tag('description', 2, false, format_string($gallery->description, true));

            $result .= rss_start_tag('image', 2, true);
            $result .= rss_full_tag('url', 3, false, $CFG->pixpath . '/i/rsssitelogo.gif');
            $result .= rss_full_tag('title', 3, false, 'moodle');
            $result .= rss_full_tag('link', 3, false, $CFG->wwwroot);
            $result .= rss_full_tag('width', 3, false, '140');
            $result .= rss_full_tag('height', 3, false, '35');
            $result .= rss_end_tag('image', 2, true);

            $counter = 1;

            foreach ($images as $image) {
                $description = (isset($captions[$image]) ? $captions[$image] : $image);

                $result .= rss_start_tag('item', 2, true);
                $result .= rss_full_tag('title', 3, false, strip_tags($image));
                $result .= rss_full_tag('link', 3, false, $webroot . '/' . $image);
                $result .= rss_full_tag('guid', 3, false, 'img' . $counter);

                $result .= rss_full_tag('media:description', 3, false, $description);
                $result .= rss_full_tag('media:thumbnail', 3, false, '', array('url' => lightboxgallery_get_image_url($gallery->id, $image, true)));
                $result .= rss_full_tag('media:content', 3, false, '', array('url' => $webroot . '/' . $image, 'type' => mime_content_type($dataroot . '/' . $image)));

                $result .= rss_end_tag('item', 2, true);

                $counter++;
            }

            $result .= rss_standard_footer();
        }

        return $result;
    }

    function lightboxgallery_rss_feeds() {
        global $CFG;

        $status = true;

        if (! $CFG->enablerssfeeds) {
            debugging('DISABLED (admin variables)');
        } else if (! get_config('lightboxgallery', 'enablerssfeeds')) {
            debugging('DISABLED (module configuration)');
        } else {
            if ($galleries = get_records('lightboxgallery')) {
                foreach ($galleries as $gallery) {
                    if ($gallery->rss && $status) {

                        $filename = rss_file_name('lightboxgallery', $gallery);

                        if (file_exists($filename)) {
                            if ($lastmodified = filemtime($filename)) {
                                if ($lastmodified > time() - HOURSECS) {
                                    continue;
                                }
                            }
                        }

                        if (!instance_is_visible('lightboxgallery', $gallery)) {
                            if (file_exists($filename)) {
                                @unlink($filename);
                            }
                            continue;
                        }

                        mtrace('Updating RSS feed for ' . format_string($gallery->name, true) . ', ID: ' . $gallery->id);

                        $result = lightboxgallery_rss_feed($gallery);

                        if (! empty($result)) {
                            $status = rss_save_file('lightboxgallery', $gallery, $result);
                        }

                        if (debugging()) {
                            if (empty($result)) {
                                echo('ID: ' . $gallery->id . '-> (empty) ');
                            } else {
                                if (! empty($status)) {
                                    echo('ID: ' . $gallery->id . '-> OK ');
                                } else {
                                    echo('ID: ' . $gallery->id . '-> FAIL ');
                                }
                            }
                        }

                    }
                }
            }
        }

        return $status;
    }

?>
