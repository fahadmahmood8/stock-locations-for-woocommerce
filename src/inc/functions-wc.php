<?php

	function slw_feature_enabled($feature_key){
		
		global $slw_optional_features_key;
	
		$features = get_option($slw_optional_features_key, []);
	
		return (
			is_array($features)
			&& !empty($features[$feature_key])
		);
	
	}
	add_action('wp_ajax_slw_update_optional_feature', function () {
	
		
		slw_verify_admin_request();

	
		$feature_key = isset($_POST['feature_key'])
			? sanitize_key($_POST['feature_key'])
			: '';
	
		$feature_status = (
			isset($_POST['feature_status'])
			&& intval($_POST['feature_status']) === 1
		);
	
		if (!$feature_key) {
			wp_send_json_error([
				'message' => __('Invalid feature key.', 'stock-locations-for-woocommerce')
			]);
		}
		
		global $slw_optional_features_key;
	
		$features = get_option($slw_optional_features_key, []);
	
		$features = is_array($features)
			? $features
			: [];
	
		$features[$feature_key] = $feature_status;
	
		update_option($slw_optional_features_key, $features);
	
		wp_send_json_success([
			'message' => __('Feature updated successfully.', 'stock-locations-for-woocommerce')
		]);
	
	});
	add_filter('manage_edit-product_columns', function($columns){
		
		if (!slw_feature_enabled('stock_status_column')) { return $columns; }
	
		$new = [];
	
		foreach ($columns as $key => $label) {
	
			$new[$key] = $label;
	
			if ($key === 'is_in_stock') {
				$new['slw_stock_debug'] = 'Stock Status';
			}
		}
	
		return $new;
	
	}, 999);
	
	add_action('manage_product_posts_custom_column', function($column, $post_id){
		
		if (!slw_feature_enabled('stock_status_column')) { return; }
	
		if ($column !== 'slw_stock_debug') {
			return;
		}
	
		$legacy_status = get_post_meta($post_id, '_stock_status', true);
		$legacy_qty    = get_post_meta($post_id, '_stock', true);
	
		$terms = wp_get_post_terms($post_id, 'product_visibility', [
			'fields' => 'slugs'
		]);
	
		$terms = is_array($terms) ? $terms : [];
	
		$has_outofstock_visibility = in_array('outofstock', $terms, true);
	
		$legacy_color = '';
		$visibility_color = '';
	
		if (
			$legacy_status === 'outofstock'
			&& !$has_outofstock_visibility
		) {
			$legacy_color = 'color:red;font-weight:bold;';
			$visibility_color = 'color:red;font-weight:bold;';
		}
	
		echo '<small>';
	
		echo '<strong>Legacy:</strong> ';
		echo '<span style="' . esc_attr($legacy_color) . '">';
		echo esc_html($legacy_status);
		echo '</span><br>';
	
		echo '<strong>Qty:</strong> ' . esc_html($legacy_qty) . '<br>';
	
		echo '<strong>Visibility:</strong> ';
	
		echo '<span style="' . esc_attr($visibility_color) . '">';
	
		if (!empty($terms)) {
			echo implode(', ', array_map('esc_html', $terms));
		} else {
			echo '-';
		}
	
		echo '</span>';
	
		echo '</small>';
	
	}, 999, 2);
	
	
	add_action('admin_head', function(){
		
		if (!slw_feature_enabled('stock_status_column')) { return; }
	
		echo '
		<style>
			.column-slw_stock_debug{
				width:180px !important;
			}
		</style>
		';
	
	});
	add_action('wp', function () {
		
		if (!slw_feature_enabled('fix_stock_status_visibility_mismatch')) { return; }
		
		//pree('A');
	
		if (is_admin()) {
			return;
		}
		
		//pree('B');
	
		if (!is_product_category()) {
			return;
		}
		
		//pree('C');
		
		$term = get_queried_object();
		
		//pree('D');
		
		$args = array(
			'status' => 'publish',
			'limit'  => 10,
			'return' => 'ids',
			'stock_status' => 'outofstock',
			'category' => [$term->slug],
			'tax_query' => [
				[
					'taxonomy' => 'product_visibility',
					'field'    => 'name',
					'terms'    => ['outofstock'],
					'operator' => 'NOT IN',
				],
			],
		);
		
		//pree($args);
	
		$products = wc_get_products($args);
		
		//pree($products);
	
		if (empty($products)) {
			return;
		}
	
		foreach ($products as $product_id) {
	
			wp_set_post_terms(
				$product_id,
				['outofstock'],
				'product_visibility',
				true
			);
	
			clean_post_cache($product_id);
			wc_delete_product_transients($product_id);
		}
	
	}, 999);