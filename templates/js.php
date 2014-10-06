jQuery(function ($) {
    $('<?= $selector ?>').first().<?= $location ?>('<?= addcslashes($snippet, "'") ?>');
});