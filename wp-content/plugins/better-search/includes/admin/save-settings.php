<?php
/**
 * Save settings.
 *
 * Functions to register, read, write and update settings.
 * Portions of this code have been inspired by Easy Digital Downloads, WordPress Settings Sandbox, etc.
 *
 * @link  https://webberzone.com
 * @since 2.2.0
 *
 * @package    Better Search
 * @subpackage Admin/Save_Settings
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}


/**
 * Sanitize the form data being submitted.
 *
 * @since 2.2.0
 * @param  array $input Input unclean array.
 * @return array Sanitized array
 */
function bsearch_settings_sanitize( $input = array() ) {

	// First, we read the options collection.
	global $bsearch_settings;

	// This should be set if a form is submitted, so let's save it in the $referrer variable.
	if ( empty( $_POST['_wp_http_referer'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
		return $input;
	}

	parse_str( sanitize_text_field( wp_unslash( $_POST['_wp_http_referer'] ) ), $referrer ); // phpcs:ignore WordPress.Security.NonceVerification.Missing

	// Get the various settings we've registered.
	$settings_types = bsearch_get_registered_settings_types();

	// Check if we need to set to defaults.
	if ( isset( $_POST['settings_reset'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing

		$_POST['settings_reset'] = null;

		bsearch_settings_reset();
		$bsearch_settings = bsearch_get_settings();

		add_settings_error( 'bsearch-notices', '', __( 'Settings have been reset to their default values. Reload this page to view the updated settings', 'better-search' ), 'error' );

		return $bsearch_settings;
	}

	// Get the tab. This is also our settings' section.
	$tab = isset( $referrer['tab'] ) ? $referrer['tab'] : 'general';

	$input = $input ? $input : array();

	/**
	 * Filter the settings for the tab. e.g. bsearch_settings_general_sanitize.
	 *
	 * @since 2.2.0
	 * @param  array $input Input unclean array
	 */
	$input = apply_filters( 'bsearch_settings_' . $tab . '_sanitize', $input );

	// Create out output array by merging the existing settings with the ones submitted.
	$output = array_merge( $bsearch_settings, $input );

	// Loop through each setting being saved and pass it through a sanitization filter.
	foreach ( $settings_types as $key => $type ) {

		/**
		 * Skip settings that are not really settings.
		 *
		 * @since 2.2.0
		 * @param  array $non_setting_types Array of types which are not settings.
		 */
		$non_setting_types = apply_filters( 'bsearch_non_setting_types', array( 'header', 'descriptive_text' ) );

		if ( in_array( $type, $non_setting_types, true ) ) {
			continue;
		}

		if ( array_key_exists( $key, $output ) ) {

			/**
			 * Field type filter.
			 *
			 * @since 2.2.0
			 * @param array $output[$key] Setting value.
			 * @param array $key Setting key.
			 */
			$output[ $key ] = apply_filters( 'bsearch_settings_sanitize_' . $type, $output[ $key ], $key );
		}

		/**
		 * Field type filter for a specific key.
		 *
		 * @since 2.2.0
		 * @param array $output[$key] Setting value.
		 * @param array $key Setting key.
		 */
		$output[ $key ] = apply_filters( 'bsearch_settings_sanitize' . $key, $output[ $key ], $key );

		// Delete any key that is not present when we submit the input array.
		if ( ! isset( $input[ $key ] ) ) {
			unset( $output[ $key ] );
		}
	}

	// Delete any settings that are no longer part of our registered settings.
	if ( array_key_exists( $key, $output ) && ! array_key_exists( $key, $settings_types ) ) {
		unset( $output[ $key ] );
	}

	add_settings_error( 'bsearch-notices', '', __( 'Settings updated.', 'better-search' ), 'updated' );

	/**
	 * Filter the settings array before it is returned.
	 *
	 * @since 2.2.0
	 * @param array $output Settings array.
	 * @param array $input Input settings array.
	 */
	return apply_filters( 'bsearch_settings_sanitize', $output, $input );

}


/**
 * Sanitize text fields
 *
 * @since 2.2.0
 *
 * @param  string $value The field value.
 * @return string Sanitized value
 */
function bsearch_sanitize_text_field( $value ) {
	return bsearch_sanitize_textarea_field( $value );
}
add_filter( 'bsearch_settings_sanitize_text', 'bsearch_sanitize_text_field' );


/**
 * Sanitize number fields
 *
 * @since 2.2.0
 *
 * @param  string $value The field value.
 * @return string Sanitized number.
 */
function bsearch_sanitize_number_field( $value ) {
	return filter_var( $value, FILTER_SANITIZE_NUMBER_INT );
}
add_filter( 'bsearch_settings_sanitize_number', 'bsearch_sanitize_number_field' );


/**
 * Sanitize CSV fields
 *
 * @since 2.2.0
 *
 * @param  string $value The field value.
 * @return string Comma separated list.
 */
function bsearch_sanitize_csv_field( $value ) {

	return implode( ',', array_map( 'trim', explode( ',', sanitize_text_field( wp_unslash( $value ) ) ) ) );
}
add_filter( 'bsearch_settings_sanitize_csv', 'bsearch_sanitize_csv_field' );


/**
 * Sanitize CSV fields which hold numbers e.g. IDs
 *
 * @since 2.2.0
 *
 * @param  string $value The field value.
 * @return string Comma separated list of numbers.
 */
function bsearch_sanitize_numbercsv_field( $value ) {

	return implode( ',', array_filter( array_map( 'absint', explode( ',', sanitize_text_field( wp_unslash( $value ) ) ) ) ) );
}
add_filter( 'bsearch_settings_sanitize_numbercsv', 'bsearch_sanitize_numbercsv_field' );


/**
 * Sanitize textarea fields
 *
 * @since 2.2.0
 *
 * @param  string $value The field value.
 * @return string Sanitized value
 */
function bsearch_sanitize_textarea_field( $value ) {

	global $allowedposttags;

	// We need more tags to allow for script and style.
	$moretags = array(
		'script' => array(
			'type'    => true,
			'src'     => true,
			'async'   => true,
			'defer'   => true,
			'charset' => true,
			'lang'    => true,
		),
		'style'  => array(
			'type'   => true,
			'media'  => true,
			'scoped' => true,
			'lang'   => true,
		),
		'link'   => array(
			'rel'      => true,
			'type'     => true,
			'href'     => true,
			'media'    => true,
			'sizes'    => true,
			'hreflang' => true,
		),
	);

	$allowedtags = array_merge( $allowedposttags, $moretags );

	/**
	 * Filter allowed tags allowed when sanitizing text and textarea fields.
	 *
	 * @since 2.2.0
	 *
	 * @param array $allowedtags Allowed tags array.
	 * @param array $value The field value.
	 */
	$allowedtags = apply_filters( 'bsearch_sanitize_allowed_tags', $allowedtags, $value );

	return wp_kses( wp_unslash( $value ), $allowedtags );

}
add_filter( 'bsearch_settings_sanitize_textarea', 'bsearch_sanitize_textarea_field' );


/**
 * Sanitize checkbox fields
 *
 * @since 2.2.0
 *
 * @param  string $value The field value.
 * @return int 0 or 1 if checkbox is false or true.
 */
function bsearch_sanitize_checkbox_field( $value ) {

	$value = ( -1 === (int) $value ) ? 0 : 1;

	return $value;
}
add_filter( 'bsearch_settings_sanitize_checkbox', 'bsearch_sanitize_checkbox_field' );


/**
 * Sanitize post_types fields
 *
 * @since 2.2.0
 *
 * @param  string $value The field value.
 * @return string Comma separated list of post types.
 */
function bsearch_sanitize_posttypes_field( $value ) {

	$post_types = is_array( $value ) ? array_map( 'sanitize_text_field', wp_unslash( $value ) ) : array( 'post', 'page' );

	return implode( ',', $post_types );
}
add_filter( 'bsearch_settings_sanitize_posttypes', 'bsearch_sanitize_posttypes_field' );


/**
 * Sanitize color fields
 *
 * @since 2.5.0
 *
 * @param  string $value The field value.
 * @return string Hexadecimal colour value.
 */
function bsearch_sanitize_color_field( $value ) {

	$color = str_replace( '#', '', $value );
	if ( strlen( $color ) === 3 ) {
		$color = $color . $color;
	}

	if ( strlen( $color ) > 6 ) {
		$color = substr( $color, 0, 6 );
	}

	if ( preg_match( '/^[a-f0-9]{6}$/i', $color ) ) {
		$color = '#' . $color;
	} else {
		$color = '#000000';
	}
	return $color;
}
add_filter( 'bsearch_settings_sanitize_color', 'bsearch_sanitize_color_field' );


/**
 * Sanitize exclude_cat_slugs to save a new entry of exclude_categories
 *
 * @since 2.2.0
 *
 * @param  array $settings Settings array.
 * @return array Sanitizied settings array.
 */
function bsearch_sanitize_exclude_cat( $settings ) {

	if ( isset( $settings['exclude_cat_slugs'] ) ) {

		$exclude_cat_slugs = array_unique( str_getcsv( $settings['exclude_cat_slugs'] ) );

		foreach ( $exclude_cat_slugs as $cat_name ) {
			$cat = get_term_by( 'name', $cat_name, 'category' );
			if ( isset( $cat->term_taxonomy_id ) ) {
				$exclude_categories[]       = $cat->term_taxonomy_id;
				$exclude_categories_slugs[] = $cat->name;
			}
		}
		$settings['exclude_categories'] = isset( $exclude_categories ) ? join( ',', $exclude_categories ) : '';
		$settings['exclude_cat_slugs']  = isset( $exclude_categories_slugs ) ? bsearch_str_putcsv( $exclude_categories_slugs ) : '';

	}

	return $settings;
}
add_filter( 'bsearch_settings_sanitize', 'bsearch_sanitize_exclude_cat' );


/**
 * Delete cache when saving settings.
 *
 * @since 2.2.0
 *
 * @param  array $settings Settings array.
 * @return array Sanitizied settings array.
 */
function bsearch_sanitize_cache( $settings ) {

	// Delete the cache.
	bsearch_cache_delete();

	return $settings;
}
add_filter( 'bsearch_settings_sanitize', 'bsearch_sanitize_cache' );
