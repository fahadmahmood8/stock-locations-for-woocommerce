<?php
    if(!function_exists('wc_os_check_plugin_active_status')){
        function wc_os_check_plugin_active_status($plugin = ''){
		
			$wc_os_all_plugins = get_plugins();
			
			
			$wc_os_active_plugins = get_site_option( 'active_sitewide_plugins' );
			
			$wc_os_active_plugins = is_array($wc_os_active_plugins)?$wc_os_active_plugins:array();
			$wc_os_network_active_plugins = is_array($wc_os_active_plugins)?apply_filters( 'active_plugins', array_keys($wc_os_active_plugins) ):array();
			$wc_os_active_plugins = apply_filters( 'active_plugins', get_option( 'active_plugins' ) );

            $wc_os_active_plugins = get_site_option( 'active_sitewide_plugins' );
            $wc_os_active_plugins = is_array($wc_os_active_plugins)?$wc_os_active_plugins:array();
            $wc_os_network_active_plugins = is_array($wc_os_active_plugins)?apply_filters( 'active_plugins', array_keys($wc_os_active_plugins) ):array();
            $wc_os_active_plugins = apply_filters( 'active_plugins', get_option( 'active_plugins' ) );

            $plugin_status = ((is_multisite() && in_array($plugin, $wc_os_network_active_plugins))
                ||
                in_array($plugin, $wc_os_active_plugins)
            );

            return $plugin_status;

        }
    }	
	
	if(!function_exists('wc_os_addons_init')){
		function wc_os_addons_init(){
			
			
			$scripts = array();
			
			$addons_list = array(
			
				'wp_sort_order' => array('install'=>admin_url('plugin-install.php?s=wp-sort-order+fahad+mahmood&tab=search&type=term'), 'settings'=>admin_url('options-general.php?page=wpso-settings'))
				
			);
			
			$filename = basename($_SERVER['SCRIPT_FILENAME']);
			switch($filename){
				case 'edit-tags.php':
					
					$wp_sort_order = wc_os_check_plugin_active_status('wp-sort-order/index.php');
					
					$taxonomy = (array_key_exists('taxonomy', $_GET)?$_GET['taxonomy']:'');
					$wpso_tags = array();
					
					if($wp_sort_order){
						$wpso_options = get_option( 'wpso_options' );
						$wpso_objects = isset( $wpso_options['objects'] ) ? $wpso_options['objects'] : array();
						$wpso_tags = isset( $wpso_options['tags'] ) ? $wpso_options['tags'] : array();
						$wpso_extras = isset( $wpso_options['extras'] ) ? $wpso_options['extras'] : array();
						
						$addon_link = '<a class="ab-promotion" href="'.$addons_list['wp_sort_order']['settings'].'" target="_blank" title="'.__('Click here enable/disable taxonomy','wpso-sort-order').'">'.__('Use Sort Order Plugin with drag & drop option to rearrange sort order','wpso-sort-order').'</a>';

					}else{
						
						$addon_link = '<a class="ab-promotion" href="'.$addons_list['wp_sort_order']['install'].'" target="_blank" title="'.__('Click here to install WP Sort Order','wpso-sort-order').'">'.__('Install Sort Order Plugin with drag & drop option for taxonomy terms','wpso-sort-order').'</a>';
						
					}
					
					if(empty($wpso_tags) || (!empty($wpso_tags) && !in_array($taxonomy, $wpso_tags))){
						
						$scripts['form#posts-filter div.tablenav.top div.actions.bulkactions'] = $addon_link;
					}
					
				break;
			}
			
?>

<?php if(!empty($scripts)){
?>
<script type="text/javascript" language="javascript">
	jQuery(document).ready(function($){
<?php					
	foreach($scripts as $selector=>$html){ ?>
		$('<?php echo $selector; ?>').append('<?php echo $html; ?>');
<?php 
	} 
?>
	});
</script>		
<?php		
	} 
?>
<style type="text/css">
	a.ab-promotion {
		display: inline-block;
		text-decoration: none;
		font-size: 12px;
		border: 1px solid #ccc;
		border-radius: 4px;
		padding: 4px 20px;
		background-color: #dfdfdf;
		color: #807070;
		cursor: pointer;
	}
</style>
<?php			
		}
	}
	
	add_action('admin_footer', 'wc_os_addons_init');