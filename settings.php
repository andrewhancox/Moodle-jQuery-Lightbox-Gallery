<?php 

    require_once($CFG->dirroot . '/mod/lightboxgallery/lib.php');

    /* Disabled Plugins */

    $options = lightboxgallery_edit_types(true);

    $disableplugins = new admin_setting_configmulticheckbox('disabledplugins', get_string('configdisabledplugins', 'lightboxgallery'), get_string('configdisabledpluginsdesc', 'lightboxgallery'), array(), $options);
    $disableplugins->plugin = 'lightboxgallery';

    $settings->add($disableplugins);

    /* Enable RSS Feeds */

    $description = get_string('configenablerssfeedsdesc', 'lightboxgallery');

    if (empty($CFG->enablerssfeeds)) {
        $description .= ' (' . get_string('configenablerssfeedsdisabled2', 'admin') . ')';
    }

    $enablerss = new admin_setting_configcheckbox('enablerssfeeds', get_string('configenablerssfeeds', 'lightboxgallery'), $description, 0);
    $enablerss->plugin = 'lightboxgallery';

    $settings->add($enablerss);

    /* Strict filenames */

    $strictfilenames = new admin_setting_configcheckbox('strictfilenames', get_string('configstrictfilenames', 'lightboxgallery'), get_string('configstrictfilenamesdesc', 'lightboxgallery'), 0);
    $strictfilenames->plugin = 'lightboxgallery';

    $settings->add($strictfilenames);

    /* Overwrite existing files */

    $overwritefiles = new admin_setting_configcheckbox('overwritefiles', get_string('configoverwritefiles', 'lightboxgallery'), get_string('configoverwritefilesdesc', 'lightboxgallery'), 1);
    $overwritefiles->plugin = 'lightboxgallery';

    $settings->add($overwritefiles);

    /* Thumbnail cache lifetime */

    $imagelifetime = new admin_setting_configtext('imagelifetime', get_string('configimagelifetime', 'lightboxgallery'), get_string('configimagelifetimedesc', 'lightboxgallery'), '86400', PARAM_INT, 6);
    $imagelifetime->plugin = 'lightboxgallery';

    $settings->add($imagelifetime);

?>
