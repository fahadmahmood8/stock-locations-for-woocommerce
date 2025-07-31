<?php
	global $wpdb;
	
	$location_id = (is_archive()?get_queried_object_id():0);
	

	$products_ids_query = "SELECT p.post_parent, pm.post_id FROM $wpdb->postmeta pm, $wpdb->posts p WHERE p.ID=pm.post_id AND p.post_type IN ('product', 'product_variation') AND pm.meta_key='_stock_at_$location_id' AND pm.meta_value>0";

	$products_ids = $wpdb->get_results($products_ids_query);

	$include_ids = array();
	if(!empty($products_ids)){
		foreach($products_ids as $products){
			$include_ids[] = ($products->post_parent>0?$products->post_parent:$products->post_id);
		}
	}
	
	
	$args = array(
		'numberposts' => -1,
		'post_type' => array('product', 'product_variation'),
		'include' => $include_ids,
		/*'meta_query' => array(
			'relation'    => 'AND',
			array(
				'key'   => '_stock_at_'.$location_id,
				'value'     => 0,
				'compare'   => '>=',
		  	)
		),*/
		'tax_query' => array(
			array(
				'taxonomy' => 'location',
				'field'    => 'id',
				'terms'    => $location_id,
			),
		),
	);
	
	$products_obj = get_posts($args);
	
	
	$items = array();
	$products = array();
	$cat_order_arr = array();
	
	if(!empty($products_obj)){
		foreach($products_obj as $product){ if(!is_object($product)){ continue; }
			
			$terms = wp_get_post_terms( $product->ID, 'product_cat' );
			
			$products[$product->ID] = $product;
			
			if(!empty($terms)){
				foreach($terms as $term){
					if(!array_key_exists($term->term_id, $items)){											
						$thumbnail_id = get_term_meta( $term->term_id, 'thumbnail_id', true ); 
						$image = wp_get_attachment_url( $thumbnail_id ); 
						$term->image = $image;
						$term->link = get_term_link($term->term_id, 'product_cat');
						$items[$term->term_id]['cat_data'] = $term;
						
						$term_order = $wpdb->get_row( "SELECT term_order FROM $wpdb->terms WHERE term_id = ".intval( $term->term_id ) );
						$items[$term->term_id]['term_order'] = $term_order->term_order;
						
					}
					
					
					
					$items[$term->term_id]['cat_items'][] = $product->ID;
					
				}
			}
		}
	}
	
	
	// Same handler function...
	
	
?>
<style type="text/css">
	div.slw-archive-wrapper{
		visibility:hidden;
	}
	div.slw-archive-wrapper,
	div.slw-archive-wrapper div.slw-archive-set,
	p.woocommerce-info{
		width:100%;
		float:left;
	}
	div.slw-archive-wrapper div.slw-archive-set{
		min-height:400px;
	}
	div.slw-archive-wrapper div.slw-archive-set:nth-child(odd){
		background-color:#F7F7F7;
	}
	div.slw-archive-wrapper div.slw-archive-set:nth-child(even){
		background-color:#E6E6E6;
	}
	div.slw-archive-set div.slw-archive-cat {
		margin: 0 0 0 0;
		padding: 54px 0 0 0;
		width: 22%;
		min-height: 400px;
		float: left;
	}
	div.slw-archive-set div.slw-archive-cat a {
		display: block;
		width: 170px;
		height: 118px;
		border-radius: 12px;
		text-align: center;
		overflow: hidden;
		margin:0 auto;
	}
	div.slw-archive-set div.slw-archive-cat a img {
		min-height: 118px;
		min-width: 170px;
		max-width: none;
		max-height: none;
	}
	div.slw-archive-set div.slw-archive-items {
		width: 78%;
		float: left;
		padding:18px 0 30px 0;
	}
	div.slw-archive-set div.slw-archive-items > ul{
		margin:0 !important;
		padding:0 !important;
		display: flex;
  		flex-wrap: wrap;
	}
	div.slw-archive-set div.slw-archive-items > ul > li {
		list-style: none;
		width: 170px;
		border-radius: 12px;
		min-height: 330px;
		background-color: #fff;
		padding: 6px 6px 20px 6px;
		margin: 0 15px 15px 0;
	}
	
	div.slw-archive-set div.slw-archive-items ul li a.product-title {
		color: #503F38;
		display: block;
		cursor: pointer;
		font-size: 14px;
		line-height: 16px;
		text-align: center;
		margin: 0 0 12px;
		min-height: 32px;
		text-decoration:none;
	}
	div.slw-archive-set div.slw-archive-items ul li a.product-img {
		display: block;
		margin: 0 auto 12px auto;
		width: 158px;
		height: 160px;
		overflow: hidden;
		border-radius: 12px;
	}
	div.slw-archive-set div.slw-archive-items ul li a.product-img img{
		max-width:100%;
		height:auto;
	} 
	div.slw-archive-set div.slw-archive-items ul li div.slw-item-qty-wrapper,
	div.slw-archive-set div.slw-archive-items ul li div.slw-variations-wrapper{
		float:left;
		width:100%;
		margin:0 0 10px;
	}
	div.slw-archive-set div.slw-archive-items ul li div.slw-variations-wrapper div.slw-variations {
		margin: 0 auto;
		width: auto;
		display: table;
	}
	div.slw-archive-set div.slw-archive-items ul li div.slw-variations-wrapper div.slw-variations *{
		cursor:pointer;
	}
	div.slw-archive-set div.slw-archive-items ul li div.slw-variations-wrapper div.slw-variations label span{
		font-size:12px;
	}
	div.slw-archive-set div.slw-archive-items ul li div.slw-item-qty {
		float: none;
		margin: 0 auto;
		clear: both;
		display: table;
	}
	div.slw-archive-set div.slw-archive-items ul li div.slw-item-qty a{
		float:left;
		cursor:pointer;		
		
	}
	div.slw-archive-set div.slw-archive-items ul li div.slw-item-qty a i,
	div.slw-archive-set div.slw-archive-items ul li div.slw-item-qty a svg{
		color:#000;
		font-size:26px;
	}
	div.slw-archive-set div.slw-archive-items ul li div.slw-item-qty a.decrease{
		margin:0 10px 0 0;
	}
	div.slw-archive-set div.slw-archive-items ul li div.slw-item-qty a.increase{
		margin:0 0 0 10px;
	}
	div.slw-archive-set div.slw-archive-items ul li div.slw-item-qty input[type="text"] {
		border-radius: 10px;
		font-size: 14px;
		font-weight: bold;
		width: 26px;
		height: 30px;
		float: left;
		text-align: center;
		color:#000;
		padding:0;
	}
	div.slw-archive-set div.slw-archive-items ul li a.slw-add-item {
		background-color: #FF000E;
		color: #fff;
		text-transform: uppercase;
		font-size: 16px;
		width: 78px;
		padding: 4px 0;
		text-align: center;
		cursor: pointer;
		display: table;
		margin: 0 auto;
		border-radius: 24px;
		text-decoration:none;
	}
	p.woocommerce-info{
		display:none;
	}
	@media only screen and (max-device-width: 480px) {
		div.slw-archive-wrapper div.slw-archive-set{
			height:auto;
		}
		div.slw-archive-set div.slw-archive-cat{
			width:100%;
			height:auto;
		}
		div.slw-archive-set div.slw-archive-cat a{
			width:100%;			
		}
		div.slw-archive-set div.slw-archive-items{
			width:100%;
		}
		div.slw-archive-set div.slw-archive-items > ul > li {
			width: 90%;
			float: none;
			margin: 0 auto 20px auto;
		}
	}

</style>
<script type="text/javascript" language="javascript">
	jQuery(document).ready(function($){
		$('body').on('click', 'div.slw-item-qty a', function(){
			var increase = $(this).hasClass('increase');
			var qty = $(this).parent().find('input');
			var qty_val = $.trim(qty.val());
			qty_val = (qty_val>=0?qty_val:0);
			if(increase){
				qty_val++;				
			}else{
				qty_val--;
			}
			qty_val = (qty_val>=0?qty_val:0);
			qty.val(qty_val);
		});
		
		$('body').on('click', 'div.slw-archive-items a.slw-add-item', function(){
			var wrapper = $(this).parent();
			var qty = wrapper.find('input[name="qty"]').val();
			
			
			var data = {
				'action': 'slw_archive_add_to_cart',
				'quantity': qty,
				'product_id': wrapper.data('product'),
				'variation_id': wrapper.data('variation'),
				'location_id': <?php echo $location_id; ?>,
				'slw_nonce_field': slw_frontend.nonce
			};
			if(qty>0 && wrapper.data('variation')>0){
				$.blockUI({ message: false });
				$.post(slw_frontend.ajaxurl, data, function(response) {

					//$.unblockUI();
					setTimeout(function(){
						document.location.reload();
					}, 1000);
				});
			}
		});
		$('body').on('click', 'div.slw-variations input[type="radio"]', function(){ //for variable products
			var wrapper = $(this).parents().closest('li');			
			wrapper.data('variation', $(this).val());
		});
		//$('div.slw-variations input').prop('checked', false);
		
		setTimeout(function(){
			var listedTerms = $('div.slw-archive-wrapper').find('div.slw-archive-set').sort(function (a, b) {
								
				var contentA =parseInt( $(a).attr('data-order'));
				var contentB =parseInt( $(b).attr('data-order'));
				return (contentA < contentB) ? -1 : (contentA > contentB) ? 1 : 0;	
									
			});
			$('div.slw-archive-wrapper').find('div.slw-archive-set').remove();
			$('div.slw-archive-wrapper').append(listedTerms).css('visibility', 'visible');
			
			if($('div.slw-variations input[type="hidden"]').length>0){ //for simple products
				$.each($('div.slw-variations input[type="hidden"]'), function(i, v){
					var wrapper = $(this).parents().closest('li');			
					wrapper.data('variation', $(this).val());
				});
			}
			if($('div.slw-variations input[type="radio"]').length>0){
				$.each($('div.slw-variations input[type="radio"][checked="checked"]'), function(i, v){
					$(this).click();
				});
			}
		}, 500);
		
	});
</script>
<?php do_action('slw_archive_before_wrapper', $location_id); ?>
<div <?php slw_archive_wrapper_attributes(); ?>>

<?php

	if(!empty($items)){
		foreach($items as $cat_id=>$cat_obj){
			
			if(in_array($cat_obj['cat_data']->slug, array('uncategorized'))){ continue; }
			
?>		
	<div class="slw-archive-set" data-order="<?php echo $cat_obj['term_order']; ?>">
    	<?php do_action('slw_archive_inside_wrapper_start', $cat_obj, $cat_id, $location_id); ?>
        <div class="slw-archive-cat">
			<a href="<?php echo $cat_obj['cat_data']->link; ?>" title="<?php echo $cat_obj['cat_data']->name; ?>"><img src="<?php echo $cat_obj['cat_data']->image; ?>" alt="<?php echo $cat_obj['cat_data']->name; ?>" /></a>
        </div>
        <div class="slw-archive-items">
        	<ul>
<?php
			if(!empty($cat_obj['cat_items'])){
				foreach($cat_obj['cat_items'] as $product_id){
					
					$product_obj = $products[$product_id];
					
					if(!is_object($product_obj)){ continue; }
					
					$product_image = apply_filters( 'slw_archive_product_image', '', $product_id );
				
?>					            
            	<li id="product-item-<?php echo $product_id; ?>" data-product="<?php echo $product_id; ?>" data-variation="0">
                	<a class="product-img" href="<?php echo $link = get_permalink($product_id); ?>" title="<?php echo $product_obj->post_title; ?>"><img src="<?php echo $product_image; ?>" alt="<?php echo $product_obj->post_title; ?>" /></a>
                    <a class="product-title" href="<?php echo $link; ?>" title="<?php echo $product_obj->post_title; ?>"><?php echo $product_obj->post_title; ?></a>
                    <?php do_action('slw_archive_items_below_title', $product_id, $cat_id, $location_id); ?>
                    <?php if(function_exists('slw_archive_qty_box')){ echo slw_archive_qty_box ($product_id); } ?>
                    <?php do_action('slw_archive_items_below_qty', $product_id, $cat_id, $location_id); ?>
                    <a class="slw-add-item"><?php _e('Add', 'stock-locations-for-woocommerce'); ?></a>
                </li>
<?php
				}
			}
?>                
            </ul>
        </div>
        <?php do_action('slw_archive_inside_wrapper_end', $cat_obj, $cat_id, $location_id); ?>
	</div>        
<?php
	
		}
   
	}

?>    
    
</div>
<?php do_action('slw_archive_after_wrapper', $location_id); ?>