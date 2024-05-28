<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if( ! class_exists('acf_frontend_file_field') ) :

	class acf_frontend_file_field {
		function file_folders_setting( $field ) {
			acf_render_field_setting( $field, array(
				'label'			=> __('Happy Files Folder',FEA_NS),
				'instructions'	=> __('Limit the media library choice to specific Happy Files Categories',FEA_NS),
				'type'			=> 'radio',
				'name'			=> 'happy_files_folder',
				'layout'		=> 'horizontal',
				'default_value' => 'all',
				'choices' 		=> acf_frontend_get_image_folders(),
			));	
		}

		function happy_files_folder( $query ) {
			if( empty( $query['_acfuploader'] ) ) {
				return $query;
			}
			
			// load field
			$field = acf_get_field( $query['_acfuploader'] );
			if( !$field ) {
				return $query;
			}
			
			if( !isset( $field['happy_files_folder'] ) || $field['happy_files_folder'] == 'all' ){
				return $query;
			}

			
			if( isset( $query['tax_query'] ) ){
				$tax_query = $query['tax_query'];
			}else{
				$tax_query = [];
			}
			
			$tax_query[] = array(
				'taxonomy' => 'happyfiles_category',
				'field' => 'name',
				'terms' => $field['happy_files_folder'],
			);
			$query['tax_query'] = $tax_query;
			
			return $query;
		}
		function hidden_uploads_grid( $query ) {
			if( ! empty( $query['_acfuploader'] ) ){
				return $query;
			} 			
			if( ! empty( $query['post__in'] ) && count( $query['post__in'] ) == 1 ) {
				return $query;
			}
			$query['meta_query'] = [
				[
					'key'     => 'hide_from_lib',
					'compare' => 'NOT EXISTS',
				]
			];
			
			return $query;
		}
		function hidden_uploads_list( $query ) {	
			if ( 'attachment' !== $query->get( 'post_type' ) || wp_doing_ajax() ){
				return;
			}

			// Modify the query.
			$query->set( 'meta_query', [
				[
					'key'     => 'hide_from_lib',
					'compare' => 'NOT EXISTS',
				]
			] );
		
			return;
		}
		function __construct() {
			if( defined( 'HAPPYFILES_VERSION' ) ){
				$file_fields = array( 'image', 'file', 'gallery', 'featured_image', 'main_image', 'product_images' );
				foreach( $file_fields as $type ){
					add_action( 'acf/render_field_settings/type=' .$type,  [ $this, 'file_folders_setting'] );
				}				
				add_filter( 'ajax_query_attachments_args', [ $this, 'happy_files_folder'] );
			}
			//add_filter( 'ajax_query_attachments_args', [ $this, 'hidden_uploads_grid'] );
			//add_action( 'pre_get_posts', [ $this, 'hidden_uploads_list' ] );

		}
		
	}

	new acf_frontend_file_field();

endif;

