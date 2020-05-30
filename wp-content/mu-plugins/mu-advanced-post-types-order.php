<?php
/*********** MOE, Advanced Post Types Order ******************************************************************/
add_action('admin_enqueue_scripts', 'callback_for_scripts_apto');
function callback_for_scripts_apto($hook) {
    wp_register_style( 'mu_apto_styles', network_site_url().'wp-content/mu-plugins/css/mu_apto.css' );
    wp_enqueue_style( 'mu_apto_styles' );
    //wp_register_script( 'mu_apto_js', network_site_url().'wp-content/mu-plugins/js/mu_apto.js', array( 'jquery' ), '1.0.0', true );
	//wp_enqueue_script( 'mu_apto_js', network_site_url().'wp-content/mu-plugins/js/mu_apto.js', array( 'jquery' ), '1.0.0', true );
}
/*********** hide sort settings for administrators. Only SuperAdmin should see them **************************/
add_filter('apto/wp-admin/reorder-interface/view-settings-capability', 'view_settings_capability');
    function  view_settings_capability($capability)
        {
           // $user_ID = get_current_user_id();
            
            $user_id = get_current_user_id();
			//if ( in_array( 'editor', (array) $user->roles ) ) {
			if ( ! is_super_admin( $user_id ) ) {
                $capability =   '_sort_capability_';
			}
            
            return $capability;   
        }
/* hide Automatic Tab in Re-Order admin (only show Manual tab, not Automatic and Manual tabs) */
add_filter('apto/admin/sort-order-tabs', 'apto_sort_order_tabs', 10, 2);
function apto_sort_order_tabs($tabs, $sort_view_ID)
    {
        //$sort_view_data = get_post($sort_view_ID);
        //$sort_id        = $sort_view_data->post_parent;
        
       // $user_id = get_current_user_id();
			//if ( ! is_super_admin( $user_id ) ) {
                unset($tabs['auto']);   
           // }
            
        return $tabs;
    }
/* hide /show the re-order interface */
add_filter('apto/admin/reorder-interface/hide', 'apto_admin_reorder_interface_hide', 10, 2);
function apto_admin_reorder_interface_hide($hide_reorder_interface, $location_menu_data)
        {
            global $userdata;
			
		$hide_reorder_interface = TRUE;
		if($location_menu_data['name']    ==  "News Articles" ) // can't specify post because Media is type post here.
                $hide_reorder_interface =   FALSE;			
		//if($location_menu_data['post_type']    ==  "faq" )
        //        $hide_reorder_interface =   FALSE;		
		//if($location_menu_data['post_type']    ==  "link" )
        //        $hide_reorder_interface =   FALSE;
                    
            return $hide_reorder_interface;	
        }
		

/** hide taxonomy selection **/
add_filter('apto/admin/sort-taxonomy-selection', 'apto_sort_taxonomy_selection', 10, 2);
function apto_sort_taxonomy_selection($display, $sort_ID)
    {
        
           // $user_id = get_current_user_id();
		   //if ( ! is_super_admin( $user_id ) ) {
               // $display    =   FALSE;
            //}
            
        return $display;
    }
		
/**
    * Check if a default sort exists for this blog    
    */
    add_action( 'plugins_loaded', 'msapto_plugins_loaded', 999);
    function msapto_plugins_loaded()
        {
            //no need to continue if plugin is not active
				//if ( is_plugin_active('advanced-post-types-order/advanced-post-types-order.php') ) {
					$default_sort   =   get_option('created_apto_default_sort');
            
        			if( empty($default_sort) || !get_post_status( $default_sort ) ) {
            			add_action('init', 'msapto_create_default_sort');
					}
				//}
        }
function msapto_create_default_sort()
    {          
        	$post_data  =   array(
                                'post_type'     =>  'apto_sort',
                                'post_title'    =>  'Information & Services News Article Sort',
                                'post_status'   =>  'publish'
                                );
        	$sort_id = wp_insert_post( $post_data );
        
        //add/change the settings
        $options    =   array(
                        '_title'                        =>  'Information & Services News Article Sort',
                        '_description'                  =>  'Sort for News Articles with no expiration date',
                        '_location'                     =>  'edit.php',
                        '_autosort'                     =>  'yes',
                        '_adminsort'                    =>  'yes',
                        '_pto_interface_sort'           =>  'no',
                        '_new_items_to_bottom'          =>  'no',
                        '_show_thumbnails'              =>  'no',
                        '_pagination'                   =>  'no',
                        '_pagination_posts_per_page'    =>  '100',
                        '_pagination_offset_posts'      =>  '5',
                        '_wpml_synchronize'             =>  'no',
                        '_capability'                   =>  'publish_pages',
                        
                        '_status'                       =>  array (
                                      'all' => 
                                      array (
                                        'label' => '<b>All</b>',
                                        'status' => 'hide',
                                      ),
                                      'publish' => 
                                      array (
                                        'label' => 'Published',
                                        'status' => 'show',
                                      ),
                                      'future' => 
                                      array (
                                        'label' => 'Scheduled',
                                        'status' => 'show',
                                      ),
                                      'draft' => 
                                      array (
                                        'label' => 'Draft',
                                        'status' => 'hide',
                                      ),
                                      'pending' => 
                                      array (
                                        'label' => 'Pending',
                                        'status' => 'hide',
                                      ),
                                      'private' => 
                                      array (
                                        'label' => 'Private',
                                        'status' => 'hide',
                                      ),
                                      'trash' => 
                                      array (
                                        'label' => 'Trash',
                                        'status' => 'hide',
                                      ),
                                      'auto-draft' => 
                                      array (
                                        'label' => 'auto-draft',
                                        'status' => 'hide',
                                      ),
                                      'inherit' => 
                                      array (
                                        'label' => 'inherit',
                                        'status' => 'hide',
                                      ),
                                      'public-archive' => 
                                      array (
                                        'label' => 'Public Archives',
                                        'status' => 'hide',
                                      ),

                                                                      ),
                        'conditionals'     =>  array(),
                        '_view_type'      =>  'multiple',
                        
                        '_rules'          =>  array (
                                                      'post_type' => 
                                                      array (
                                                        0 => 'post',
                                                      ),
                                                      'taxonomy_relation' => 'AND',
													  'meta' =>
																array (
																	1 =>
																	array (
																		'key' => '_post-expiration-enabled',
																		'value_type' => 'string',
																		'value' => '0',
																		'compare' => '=',
																		'type' => 'CHAR',
																	),
																),
                                                      'meta_relation' => 'AND',
                                                    ),
                        
                        '_settings_update_languages'    =>  array (
                                                          'en' => true,
                                                        )
                        );
                                        
        //add the meta
        foreach (  $options as  $option    =>  $value)
            {
                update_post_meta($sort_id, $option, $value);   
                
            }
        
        
        //add an option to held a sort previously created
        update_option('created_apto_default_sort', $sort_id);
        
    }
	
