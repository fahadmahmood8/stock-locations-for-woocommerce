<?php if ( ! defined('WPINC') ) die; ?>
<form method="post" action="options.php">
<?php
	settings_fields( 'slw_setting_option_group' );
	do_settings_sections( 'slw-setting-admin' );
	submit_button();
?>
</form>