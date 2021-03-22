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

		public static function object_id( $object_id, $type )
		{
			$default_language = apply_filters( 'wpml_default_language', null );
			
			// if array
			if( is_array( $object_id ) ){
				$translated_object_ids = array();
				foreach ( $object_id as $id ) {
					$translated_object_ids[] = apply_filters( 'wpml_object_id', $id, $type, true, $default_language );
				}
				return $translated_object_ids;
			}
			// if string
			elseif( is_string( $object_id ) ) {
				// check if we have a comma separated ID string
				$is_comma_separated = strpos( $object_id,"," );
		
				if( $is_comma_separated !== FALSE ) {
					// explode the comma to create an array of IDs
					$object_id     = explode( ',', $object_id );
		
					$translated_object_ids = array();
					foreach ( $object_id as $id ) {
						$translated_object_ids[] = apply_filters ( 'wpml_object_id', $id, $type, true, $default_language );
					}
		
					// make sure the output is a comma separated string (the same way it came in!)
					return implode ( ',', $translated_object_ids );
				}
				// if we don't find a comma in the string then this is a single ID
				else {
					return apply_filters( 'wpml_object_id', intval( $object_id ), $type, true, $default_language );
				}
			}
			// if int
			else {
				return apply_filters( 'wpml_object_id', $object_id, $type, true, $default_language );
			}
		}

	}
	
}