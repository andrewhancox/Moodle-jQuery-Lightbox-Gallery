<?php
defined('MOODLE_INTERNAL') || die();

// French translation by Éric Bugnet
$string['acceptablefiletypebriefing'] = 'Si vous souhaitez déposer plusieurs fichiers en une fois, vous pouvez déposer un fichier zip contenant les images, et les images valides seront ajoutées à la galerie.';
    $string['addcomment'] = 'Ajouter un commentaire';
    $string['addimage'] = 'Ajouter des images';
$string['addimage_help'] = 'Parcourir pour ajouter une image de votre disuque dur à cette galerie.

Vous pouvez également sélectionner un fichier zip contenant plusieurs images qui seront extraites dans le répertoire d\'images.';
    $string['autoresize'] = 'Redimensionnement automatique';
$string['autoresize_help'] = 'Vous pouvez contrôler et autoriser le redimensionnement des images. Les méthodes suivantes sont valables lors de la configuration de la galerie :

* Écran : les images qui sont plus grandes que l\'écran de l\'utilisateur seront réduites pour s\'afficher totalement sur l\'écran..
* Déposer : les images seront redimensionnées aux dimensions spécifiées quand elles sont déposées par l\'option  \'Ajouter des images\'.

Il y a aussi un module de redimensionnement inclus dans l\'éditeur d\'image, à partir duquel vous pouvez redimenssionner les images manuellement.';
    $string['allowcomments'] = 'Autoriser les commentaires';
    $string['allowrss'] = 'Autoriser les flux RSS';
$string['allpluginsdisabled'] = 'Désolé, tous les modules d\'édition sont désactivés.';
    $string['backtogallery'] = 'Retour à la galerie';
$string['captionfull'] = 'Afficher le texte complet de la légende ?';
$string['captionpos'] = 'Position de la légende';
    $string['commentadded'] = 'Votre commentaire a été posté dans la galerie';
    $string['commentcount'] = '{$a} commentaires';
    $string['commentdelete'] = 'Confirmer la suppression du commentaire ?';
    $string['configdisabledplugins'] = 'Désactiver les plugins';
    $string['configdisabledpluginsdesc'] = 'Sélectionner les plugins d\'édition d\'image que vous voulez supprimer.';
    $string['configenablerssfeeds'] = 'Activer les flux RSS';
    $string['configenablerssfeedsdesc'] = 'Autoriser la génération des flux RSS à partir des galeries.';
    $string['configimagelifetime'] = 'Durée de vie de l\'image';
    $string['configimagelifetimedesc'] = 'Durée de vie (en secondes) durant laquelle les images restent dans le cache du navigateur.';
    $string['configoverwritefiles'] = 'Écraser les fichiers';
    $string['configoverwritefilesdesc'] = 'Écraser les images quand de nouvelles images sont déposées avec le même nom.';
    $string['configstrictfilenames'] = 'Utiliser des noms de fichiers stricts';
    $string['configstrictfilenamesdesc'] = 'Obliger l\'éditeur à nettoyer les noms de fichiers en accord avec les usages de Moodle.';
    $string['currentsize'] = 'Taille actuelle';
    $string['dimensions'] = 'Dimensions';
    $string['dirup'] = 'Haut';
    $string['dirdown'] = 'Bas';
    $string['dirleft'] = 'Gauche';
    $string['dirright'] = 'Droite';
    $string['displayinggallery'] = 'Galerie : {$a}';
    $string['editimage'] = 'Modifier l\'image';
    $string['edit_caption'] = 'Titre';
    $string['edit_crop'] = 'Rogner';
    $string['edit_delete'] = 'Effacer';
    $string['edit_flip'] = 'Miroir';
    $string['edit_resize'] = 'Redimensionner';
    $string['edit_resizescale'] = 'Échelle';
    $string['edit_rotate'] = 'Tourner';
    $string['edit_tag'] = 'Tag';
    $string['edit_thumbnail'] = 'Vignette';
    $string['errornofile'] = 'Le fichier suivant n\'a pas été trouvé : {$a}';
    $string['errornoimages'] = 'Aucune image n\'a été trouvée dans cette galerie.';
    $string['errornosearchresults'] = 'Votre requette n\' a retournée aucune image.';
    $string['erroruploadimage'] = 'Le ficher que vous envoyez doit être une image.';
    $string['extendedinfo'] = 'Afficher des informations étendues';
    $string['imageadd'] = 'Ajouter des images';
    $string['imagecount'] = 'Total';
    $string['imagecounta'] = '{$a} images';
    $string['imagedirectory'] = 'Répertoire contenant les images';
$string['imagedirectory_help'] = 'Sélectionnez le répertoire qui contient les images à intégrer à la galerie. Lorsque vous utilisez l\'option \'Ajouter des images\' de la galerie, les images téléversées y seront déposées.';
    $string['imagedownload'] = 'Télécharger';
    $string['imageresized'] = 'Image redimensionnée : {$a}';
$string['images'] = 'Images';
    $string['imagesperpage'] = 'Nombre d\'images par page';
$string['imagesperrow'] = 'Nombre d\'images par ligne';
    $string['imageuploaded'] = 'Image déposée : {$a}';
$string['invalidlightboxgalleryid'] = 'ID de Galerie incorrecte';
$string['lightboxgallery'] = 'Galerie d\'images';
    $string['lightboxgallery:addcomment'] = 'Ajouter un commentaire à la galerie';
$string['lightboxgallery:addinstance'] = 'Ajouter une nouvelle galerie';
    $string['lightboxgallery:addimage'] = 'Ajouter une image à la galerie';
    $string['lightboxgallery:edit'] = 'Modifier la galerie';
$string['lightboxgallery:submit'] = 'Soumettre une galerie';
    $string['lightboxgallery:viewcomments'] = 'Voir les commentaires de la galerie';
    $string['makepublic'] = 'Rendre publique';
$string['metadata'] = 'Métadonnées';
    $string['modulename'] = 'Galerie d\'images';
$string['modulename_help'] = 'Le module Galerie d\'images permet aux participants de voir une galerie d\'images.

Ce module vous permet de créer une galerie d\'images fonctionnelle au sein de votre espace de cours.

En tant qu\'enseignant, bien sûr, vous pouvez créer, modifier et supprimer des galeries. Des vignettes seront alors générées, et seront utilisées pour l\'affichage des miniatures de la galerie.
Cliquer sur une des vignettes met cette image au centre, et vous permet de faire défiler la galerie à votre guise. L\'utilisation des scripts de la galerie crée des effets de transition lors du chargement et du visonnement des images de la galerie.

Si l\'option est activée, les utilisateurs peuvent laisser des commentaires dans votre galerie.';
    $string['modulenameplural'] = 'Galeries';
    $string['modulenameshort'] = 'Galerie';
    $string['modulenameadd'] = 'Ajouter une galerie d\'images';
    $string['newgallerycomments'] = 'Nouveaux commentaires';
$string['norssfeedavailable'] = 'Flux RSS non valide';
$string['position_bottom'] = 'Bas';
$string['position_top'] = 'Haut';
$string['pluginadministration'] = 'Administration de la galerie';
$string['pluginname'] = 'Galerie d\'images';
    $string['resizeto'] = 'Redimensionner à';
    $string['rsssubscribe'] = 'Flux RSS de la galerie';
    $string['saveimage'] = 'Enregistrer {$a}';
    $string['screen'] = 'Écran';
    $string['selectflipmode'] = 'Choisir le mode de miroir';
    $string['selectrotation'] = 'Choisir l\'angle de rotation';
    $string['selectthumbpos'] = 'Décalage de la vignette (à partir du centre)';
    $string['setasindex'] = 'Placer en image d\'entête';
    $string['showall'] = 'Montrer tout';
    $string['tagscurrent'] = 'Tags actuels';
    $string['tagsdisabled'] = 'Le système de tag est désactivé';
    $string['tagsimport'] = 'Importer des tags';
    $string['tagsimportconfirm'] = 'Êtes vous sur de vouloir importer les tags de toutes les images de cette galerie ?';
    $string['tagsimportfinish'] = 'Import terminé {$a->tags} tags à partir de {$a->images} images';
    $string['tagsiptc'] = 'Tags IPTC';
    $string['tagspopular'] = 'Tags populaires';
    $string['tagsrelated'] = 'Tags liés';
    $string['thumbnailoffset'] = 'Décalage';
    $string['zipextracted'] = 'Fichier Zip extrait : {$a}';
    $string['zipnonewfiles'] = 'Aucune nouvelle image n\'a été trouvée, vérifiez que les images sont dans la base de l\'archive.';
?>
