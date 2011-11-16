<?php

    require_once('../../config.php');
    require_once('lib.php');
    require_once('edit/base.class.php');

    $id = required_param('id', PARAM_INT);
    $image = required_param('image', PARAM_PATH);
    $tab = optional_param('tab', '', PARAM_TEXT);
    $page = optional_param('page', 0, PARAM_INT);

    if (! $gallery = get_record('lightboxgallery', 'id', $id)) {
        error('Course module is incorrect');
    }
    if (! $course = get_record('course', 'id', $gallery->course)) {
        error('Course is misconfigured');
    }
    if (! $cm = get_coursemodule_from_instance('lightboxgallery', $gallery->id, $course->id)) {
        error('Course Module ID was incorrect');
    }

    if (get_config('lightboxgallery', 'strictfilenames')) {
        $image = clean_param($image, PARAM_CLEANFILE);
    }

    require_login($course->id);

    $context = get_context_instance(CONTEXT_MODULE, $cm->id);
    require_capability('mod/lightboxgallery:edit', $context);

    $edittypes = lightboxgallery_edit_types();

    $tabs = array();
    foreach ($edittypes as $type => $name) {
        $tabs[] = new tabObject($type, $CFG->wwwroot.'/mod/lightboxgallery/imageedit.php?id='.$gallery->id.'&amp;image='.$image.'&amp;page='.$page.'&amp;tab='.$type, $name);
    }

    if (!in_array($tab, array_keys($edittypes))) {
        $types = array_keys($edittypes);
        $tab = $types[0];
    }

    $galleryurl = $CFG->wwwroot.'/mod/lightboxgallery/view.php?id='.$cm->id.'&amp;page='.$page.'&amp;editing=1';

    $navlinks = array();
    $navlinks[] = array('name' => get_string('editimage', 'lightboxgallery'), 'link' => '', 'type' => 'misc');
    $navlinks[] = array('name' => get_string('edit_' . $tab, 'lightboxgallery'), 'link' => '', 'type' => 'misc');

    $navigation = build_navigation($navlinks, $cm);

    $button = print_single_button($CFG->wwwroot.'/mod/lightboxgallery/view.php', array('id' => $cm->id, 'page' => $page, 'editing' => '1'), get_string('backtogallery', 'lightboxgallery'), 'get', '', true);

    print_header($course->shortname.': '.$gallery->name.': '.$image, $course->fullname, $navigation, '', '', true, $button, navmenu($course, $cm));

    echo('<br />');

    print_tabs(array($tabs), $tab);

    require($CFG->dirroot.'/mod/lightboxgallery/edit/'.$tab.'/'.$tab.'.class.php');
    $editclass = 'edit_'.$tab;
    $editinstance = new $editclass($gallery, $image, $tab);

    if (! file_exists($editinstance->imageobj->filename)) {
        error(get_string('errornofile', 'lightboxgallery', $image), $galleryurl);
    }

    if ($editinstance->processing() && confirm_sesskey()) {
        add_to_log($course->id, 'lightboxgallery', 'editimage', 'view.php?id='.$cm->id, $tab.' '.$image, $cm->id, $USER->id);
        echo ('<table class="generaltable boxaligncenter" width="" cellspacing="1" cellpadding="5">');
        echo ('<tr><td><img src="'.$CFG->wwwroot.'/mod/lightboxgallery//img/loading.gif"/></td></tr>');
        echo ('</table>');
        $editinstance->process_form();
        redirect($CFG->wwwroot.'/mod/lightboxgallery/imageedit.php?id='.$gallery->id.'&amp;image='.$image.'&amp;tab='.$tab);
    }

    $table = new object;
    $table->width = '*';

    if ($editinstance->showthumb) {
        $imagelabel = lightboxgallery_resize_label($image);

        $table->align = array('center', 'center');
        $table->size = array('*', '*');
        $table->data[] = array(lightboxgallery_image_thumbnail($course->id, $gallery, $image).'<br /><span title="'.$image.'">'.$imagelabel.'</span>', $editinstance->output());
    } else {
        $table->align = array('center');
        $table->size = array('*');
        $table->data[] = array($editinstance->output());
    }

    print_table($table);

    $dataroot = $CFG->dataroot.'/'.$course->id.'/'.$gallery->folder;
    if ($dirimages = lightboxgallery_directory_images($dataroot)) {
        sort($dirimages);
        $options = array();
        foreach ($dirimages as $dirimage) {
            $options[$dirimage] = $dirimage;
        }
        $index = array_search($image, $dirimages);

        echo('<table class="boxaligncenter menubar">
                <tr>');
        if ($index > 0) {
            echo('<td>');
            print_single_button($CFG->wwwroot.'/mod/lightboxgallery/imageedit.php', array('id' => $gallery->id, 'tab' => $tab, 'page' => $page, 'image' => $dirimages[$index - 1]), '←');
            echo('</td>');
        }
        echo('<td>
                <form method="get" action="'.$CFG->wwwroot.'/mod/lightboxgallery/imageedit.php">
                  <fieldset class="invisiblefieldset">
                  <input type="hidden" name="id" value="'.$gallery->id.'" />
                  <input type="hidden" name="tab" value="'.$tab.'" />
                  <input type="hidden" name="page" value="'.$page.'" />');
        choose_from_menu($options, 'image', $image, null, 'submit()');
        echo('  </fieldset>
                </form>
              </td>');
        if ($index < count($dirimages) - 1) {
            echo('<td>');
            print_single_button($CFG->wwwroot.'/mod/lightboxgallery/imageedit.php', array('id' => $gallery->id, 'tab' => $tab, 'page' => $page, 'image' => $dirimages[$index + 1]), '→');
            echo('</td>');
        }
        echo('  </tr>
              </table>');
    }
   
    print_footer($course);

?>
