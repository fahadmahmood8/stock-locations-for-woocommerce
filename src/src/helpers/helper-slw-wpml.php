<?php
/**
 * SLW WPML Helper Class
 * @since 1.5.0
 */

namespace SLW\SRC\Helpers;

if ( ! defined( 'WPINC' ) ) die;

if ( ! class_exists( 'SlwWpmlHelper' ) ) {

	class SlwWpmlHelper
	{

		public static function object_id( $object_id )
		{
			if( empty( $object_id ) ) return;

			return apply_filters( 'wpml_object_id', $object_id, get_post_type( $object_id ), true, apply_filters( 'wpml_default_language', null ) );
		}

	}
	
}