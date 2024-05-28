<?php
/**
 * SLW Admin Notice Class
 *
 * @since 1.0.0
 */

namespace SLW\SRC\Classes;

if ( !defined( 'WPINC' ) ) {
	die;
}

if( !class_exists('SlwAdminNotice') ) {

	class SlwAdminNotice
	{
		const NOTICE_FIELD = SLW_PLUGIN_SLUG . '_admin_notice_message';

		public function displayAdminNotice()
		{
			$option      = get_option(self::NOTICE_FIELD);
			$message     = isset($option['message']) ? $option['message'] : false;
			$noticeLevel = ! empty($option['notice-level']) ? $option['notice-level'] : 'notice-error';

			if ($message) {
				echo "<div class='notice {$noticeLevel} is-dismissible'><p>{$message}</p></div>";
				delete_option(self::NOTICE_FIELD);
			}
		}

		public static function displayError( $message )
		{
			self::updateOption($message, 'notice-error');
		}

		public static function displayWarning( $message )
		{
			self::updateOption($message, 'notice-warning');
		}

		public static function displayInfo( $message )
		{
			self::updateOption($message, 'notice-info');
		}

		public static function displaySuccess( $message )
		{
			self::updateOption($message, 'notice-success');
		}

		protected static function updateOption( $message, $noticeLevel ) {
			update_option(self::NOTICE_FIELD, [
				'message'       => $message,
				'notice-level'  => $noticeLevel
			]);
		}
	}

}
