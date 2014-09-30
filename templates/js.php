jQuery(function ($) {
    $('<?= $selector ?>').first().after('<?= addcslashes($snippet, "'") ?>');
});