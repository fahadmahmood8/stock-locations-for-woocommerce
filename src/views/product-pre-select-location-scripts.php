<script>
    jQuery(function ($) {
        <?php foreach ($locations as $location) { ?>
            $('#in-location-<?php echo $location; ?>').attr('checked', true);
        <?php } ?>
    });
</script>