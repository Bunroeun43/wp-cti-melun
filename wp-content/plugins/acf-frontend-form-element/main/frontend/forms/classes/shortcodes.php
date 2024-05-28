<?php
namespace Frontend_WP\Classes;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if( ! class_exists( 'Dynamic_Values' ) ) :

class Dynamic_Values{

	
/* 	function implode_recur($separator, $arrayvar) {
		$return = "";
		foreach ($arrayvar as $av)
		if (is_array ($av)) 
			$return .= $this->implode_recur($separator, $av); // Recursive array 
		else                   
			$return .= $separator.$av;
	
		return $return . '<br>';
	} */

	function get_user_field( $field, $user = null, $context = 'display' ) {
		if( is_object( $user ) ) $user = $user->ID;
		if( is_array( $user ) ) $user = $user['ID'];

		$user_data = get_userdata( $user );
	
		if ( ! $user_data ) {
			return '';
		}
	
		if ( ! isset( $user_data->$field ) ) {
			return '';
		}
	
		return sanitize_user_field( $field, $user_data->$field, $user, $context );
	}


	function get_dynamic_values( $text, $form = array() ) {
		// If no record search for a global form record
		if( empty( $form['record'] ) && isset( $GLOBALS['admin_form'] ) ) $form = $GLOBALS['admin_form'];

		// If no global record, look for a record stored in the cookie
		if( empty( $form['record'] ) ) $form = fea_instance()->form_display->get_record( $form );

		// If no record found, return the text as is
		if( empty( $form['record'] ) ) return $text;

		// Find all merge tags
		if ( preg_match_all( "/\[(.+?)?\]/", $text, $matches ) ) {
			foreach ( $matches[1] as $i=>$tag ){
				$value = '';
				
				if( isset( $form['record'] ) && 'all_fields' == $tag ) $value = $this->get_all_fields_values( $tag, $form );

				if( ! $value ) $value = $this->get_field_value( $tag, $form );
				if( ! $value ) $value = $this->get_sub_field_value( $tag, $form );
				if( ! $value ) $value = $this->get_post_value( $tag, $form );
				if( ! $value ) $value = $this->get_user_value( $tag, $form );
				
				if( $value !== false ) $text = str_replace( $matches[0][$i], $value, $text );
			}
		
		}
		
		return $text;
	}

	function get_all_fields_values( $tag, $form ) {
		$record = $form['record']['fields'];
		$return = '<table class="acf-display-values">';
		
		$return_type = false;
		if( isset( $tag[2] ) ) $return_type = $tag[2];

		foreach ( $record as $group => $fields ) {
			if( ! is_array( $fields ) ) continue;	
			foreach( $fields as $field ){

				if ( 'clone' == $field['type'] ) {
					
					foreach ( $field['sub_fields'] as $sub_field ) {					
						$return .= sprintf( '<tr><th>%s</th></tr>', $sub_field['label'] );
						$return .= sprintf( '<tr><td>%s</td></tr>', $this->display_field( $sub_field, $field['value'][ $sub_field['name'] ], $return_type ) );					
					}
					
				} else {
					$return .= sprintf( '<tr><th>%s</th></tr>', $field['label'] );
					$return .= sprintf( '<tr><td>%s</td></tr>', 
					$this->display_field( $field, false, $return_type ) );
				}
			}
		}
		
		$return .= '</table>';
	
		return $return;
	  }
	
	  function get_user_value( $tag, $form ){
		if ( ! preg_match_all( '/user:(.*)/', $tag, $matches ) ) {
			return false;
		}
		$value = '';
		
		if( isset( $form['record']['user'] ) ){
			$user_id = $form['record']['user'];
		}else{
			$user_id = get_current_user_id();
		}
		$edit_user = get_user_by( 'ID', $user_id );

		if( empty( $edit_user->user_login ) ) return $value;

		$field_name = $matches[1][0];
		switch( $field_name ){
			case 'id':
				$value = $user_id;
			break;
			case 'username':
				$value = $edit_user->user_login;
			break;
			case 'email':
				$value = $edit_user->user_email;
			break;
			case 'first_name':
				$value = $edit_user->first_name;
			break;
			case 'last_name':
				$value = $edit_user->last_name;
			break;
			case 'display_name':
				$value = $edit_user->display_name;
			break;
			case 'role':
				$role = $edit_user->roles[0];
				global $wp_roles;
				$value = $wp_roles->roles[ $role ]['name'];
			break;
			case 'bio':
				$value = $edit_user->description;
			break;
		}
		return $value;
	}
	function get_post_value( $tag, $form ){
		if ( ! preg_match_all( '/post:(.*)/', $tag, $matches ) ) {
			return false;
		}

		$value = '';
		if( isset( $form['record']['post'] ) ){
			$post_id = $form['record']['post'];
			$edit_post = get_post( $post_id );
		}else{
			global $post;
			if( empty( $post->ID ) ) return $value;
			
			$post_id = $post->ID;
			$edit_post = $post;
		}

		if( ! is_wp_error( $edit_post ) ){
			$field_name = $matches[1][0];

			$tag_parts = explode( ':', $field_name );
			
			if( isset( $tag_parts[1] ) ){
				$field_name = $tag_parts[0];
				$display_mode = $tag_parts[1];
			}

			switch( $field_name ){
				case 'id':
					return $post_id;
				break;
				case 'post_title':
				case 'title':
					return $edit_post->post_title;
				break;
				case 'slug':
						$value = $edit_post->post_name;
				break;
				case 'post_content':
				case 'content':
				case 'desc':
						$value = $edit_post->post_content;
				break;
				case 'post_excerpt':
				case 'excerpt':
				case 'short_desc':
						$value = $edit_post->post_excerpt;
				break;
				case 'featured_image':
				case 'main_image':
					$post_thumb_id = get_post_thumbnail_id( $post_id );
					$post_thumb_url = wp_get_attachment_url( $post_thumb_id );
					$max_width = '500px';
					if( isset( $display_mode ) ){
						if( $display_mode == 'image_link' ){
							$value = $post_thumb_id;
						}elseif( $display_mode == 'image_id' ){
							$value = $post_thumb_url;
						}else{
							$max_width = $display_mode;
							if( is_numeric( $max_width ) ) $max_width .= 'px';							
						}
					}
					if( ! $value ){
						$value = '<div style="max-width:' .$max_width. '"><a href="' .$post_thumb_url. '"><img style=" width: 100%;height: auto" src="' . $post_thumb_url . '"/></a></div>';
					}
				break;
				case 'post_url':
				case 'url':
						$value = get_permalink( $post_id );
				break;
				case 'author':
					$author = $edit_post->post_author;
					if( $author ){
						$author = get_user_by( 'id', $author );
						if( isset( $author->first_name ) ){
							$value = sprintf( '%s %s', $author->first_name, $author->last_name );			
						}
					}

				break;
			}
			return $value;
		}
		return '';
	}
	
	
	  function get_sub_field_value( $tag, $form ) {	
		if ( ! preg_match_all( '/acf:(.*)/', $tag, $matches ) ) {
			return false;
		}
		$record = call_user_func_array('array_merge', $form['record']['fields'] );

		$field_name = $matches[1][0];
		if( isset( $record[$field_name] ) ){
			$field = $record[$field_name];
		}else{
			$field = get_field_object( $field_name, $form['record']['post_id'] );	
		}
		if ( ! $field ) return '';
	
		// The previous regex will greedily match everything inside brackets.
		// For example "field:f1[s1][s2][s3] will yield "s1][s2][s3".
		// By splitting this we get the selector: array( "s1", "s2", "s3" )
		$selector = explode( '][', $matches[2][0] );
	
		// Find the nested subfield and its value
		$sub_field = $this->sub_field( $field, $selector );
		$sub_field_value = $this->sub_field_value( $field, $selector );
	
		if ( $sub_field ) {
			$return_type = false;
			if( isset( $tag[2] ) ) $return_type = $tag[2];
			
			return $this->display_field( $sub_field, $sub_field_value, $return_type );
		}
	
		return '';
	  }
	
	  function get_field_value( $tag, $form ) {	
		if ( ! preg_match_all( '/acf:(.*)/', $tag, $matches ) ) {
			return false;
		}
		$record = call_user_func_array('array_merge', $form['record']['fields'] );

		$field_name = $matches[1][0];
		
		if( isset( $record[$field_name] ) ){
			$field = $record[$field_name];
		}else{
			$field = get_field_object( $field_name, $form['record']['post_id'] );	
		}
		if ( ! $field ) return '';

		$return_type = false;
		if( isset( $tag[2] ) ) $return_type = $tag[2];
	
		return $this->display_field( $field, false, $return_type );
	  }

	  function sub_field( $field, $selector ) {
		  
		while ( ! empty( $selector ) && $field && isset( $field['sub_fields'] ) ) {
			$search = array_shift( $selector );
			$field = acf_search_fields( $search, $field['sub_fields'] );
		}

		return $field;
	}

   function sub_field_value( $field, $selector ) {
		$value = $field['value'];

		while ( ! empty( $selector ) ) {
			$search = array_shift( $selector );
			if ( isset( $value[ $search ] ) ) {
				$value = $value[ $search ];
			} else {
				return false;
			}
		}

		return $value;
	}
	
	  function display_field( $field, $value = false, $return_type = false ) {
		if ( ! $value ) {
			$value = $field['value'];
		}
		if ( ! $value ) return '';
	
		$return = '';

		switch( $field['type'] ){
			case 'repeater':
				if( is_array( $value ) ){
					$return .= '<table class="acf-display-values acf-display-values-repeater">';
			
					// Column headings
					$return .= '<thead><tr>';
					
					foreach ( $field['sub_fields'] as $sub_field ) {
						$return .= sprintf( '<th>%s</th>', $sub_field['label'] );
					}
					
					$return .= '</tr></thead>';
					
					
					// Rows
					$return .= '<tbody>';
					
					if ( is_array( $value ) ) {
						foreach ( $value as $row_values ) {
							$return .= '<tr>';
							
							foreach ( $field['sub_fields'] as $sub_field ) {
								$row_value = false;
								if( isset( $row_values[ $sub_field['name'] ] ) ){
									$row_value = $row_values[ $sub_field['name'] ];
								}
								if( isset( $row_values[ $sub_field['key'] ] ) ){
									$row_value = $row_values[ $sub_field['key'] ];
								}
								
								$return .= sprintf( '<td>%s</td>', $this->display_field( $sub_field, $row_value, $return_type ) );
								
							}
							
							$return .= '</tr>';
						}
					}
					
					$return .= '</tbody>';
					
					
					$return .= '</table>';
				}
			break;
			case 'clone':
			case 'group':
				$return .= sprintf( '<table class="acf-display-values acf-display-values-%s">', $field['type'] );
		
				foreach ( $field['sub_fields'] as $sub_field ) {
					if ( isset( $value[ $sub_field['name'] ] ) ) {
						$return .= sprintf( '<tr><th>%s</th></tr>', $sub_field['label'] );
						$return .= sprintf( '<tr><td>%s</td></tr>', $this->display_field( $sub_field, $value[ $sub_field['name'] ], $return_type ) );
					}
				}
				
				$return .= '</table>';
			break;
			case 'true_false':
				$true_text = isset( $field['ui_on_text'] ) && ! empty( $field['ui_on_text'] ) ? $field['ui_on_text'] : __( 'Yes', 'advanced-forms' );
				$false_text = isset( $field['ui_off_text'] ) && ! empty( $field['ui_off_text'] ) ? $field['ui_off_text'] : __( 'No', 'advanced-forms' );
				
				$return .= $value ? $true_text : $false_text;
			break;
			case 'image':
			case 'featured_image':
			case 'main_image':
			case 'site_logo':
				$value = acf_get_attachment( $value );
				$max_width = '';
				if( $return_type ){
					if( $return_type == 'image_link' ){
						$return .= $value['url'];
					}elseif( $return_type == 'image_id' ){
						$return .= $value['ID'];
					}else{
						$max_width = 'style="max-width:' .$return_type. '"';
					}
				}
				$return .= sprintf( '<img '. $max_width .'src="%s" alt="%s" />', esc_attr( $value['sizes']['medium'] ), esc_attr( $value['alt'] ) );
			break;
			case 'gallery':
			case 'product_images':
				foreach ( $value as $image ) {
					$return .= sprintf( '<img src="%s" alt="%s" />', esc_attr( $image['sizes']['medium'] ), esc_attr( $image['alt']));
				}
			break;
			case 'file':
				$return .= sprintf( '<a href="%s">%s</a>', $value['url'], htmlspecialchars( $value['title'] ) );
			break;
			case 'wysiwyg':
			case 'textarea':
			case 'post_excerpt':
			case 'post_content':
			case 'user_bio':
				$return .= wp_kses_post( stripslashes( $value ) );
			break;
			case 'taxonomy':
				if( $value ){
					$returns = array();
					foreach( $value as $single_value ){
						if( $field['return_format'] == 'id' ) $single_value = get_term( $single_value );
						$returns[] = $single_value->name;
					}
					$return .= join( ', ', $returns );
				}
			break;
			case 'relationship':
			case 'product_grouped':
			case 'product_upsells':
			case 'product_cross_sells':
			case 'post_object':
				if( is_array( $value ) && $value ){
					$returns = array();
					foreach( $value as $single_value ){
						if( $field['return_format'] == 'id' ) $single_value = get_post( $single_value );
						$returns[] = $single_value->post_title ? $single_value->post_title : '(no-name)';
					}
					$return .= join( ', ', $returns );
				}elseif( $value ){
					if( $field['return_format'] == 'id' ) $value = get_post( $value );
					$return .= $value->post_title ? $value->post_title : '(no-name)';
				}
			break;
			case 'user':
			case 'post_author':
				if( is_array( $value ) && $value ){
					$returns = array();
					if( $field['return_format'] == 'array' ){
						foreach( $value as $single_value ){
							$returns[] = sprintf( '%s %s', $value['user_firstname'], $value['user_lastname'] );
						}
					}else{
						foreach( $value as $single_value ){
							if( $field['return_format'] == 'id' ) $single_value = get_userdata( $single_value );
							$returns[] = sprintf( '%s %s', $value->first_name, $value->last_name );
						}
					}
					$return .= join( ', ', $returns );
				}elseif( $value ){
					if( $field['return_format'] == 'array' ){
						$return .= sprintf( '%s %s', $value['user_firstname'], $value['user_lastname'] );
					}else{
						if( $field['return_format'] == 'id' ) $value = get_userdata( $value );
						$return .= sprintf( '%s %s', $value->first_name, $value->last_name );
					}
				}
			break;
			default:
				$return .= $this->display_default_value( $value ); 
		}
		
		// Allow third-parties to alter rendered field
		$return = apply_filters( FEA_PREFIX.'/display_value', $return, $field, $value );
		$return = apply_filters( FEA_PREFIX.'/display_value/name=' . $field['name'], $return, $field, $value );
		$return = apply_filters( FEA_PREFIX.'/display_value/key=' . $field['key'], $return, $field, $value );
		
		return $return;
	}
	
	
	function display_default_value( $value ) {
		$return = '';
		 
		if ( $value instanceof WP_Post ) {
			
			$return = $value->post_title ? $value->post_title : '(no-name)';
			
		} elseif ( $value instanceof WP_User ) {
			
			$return = sprintf( '%s %s', $value->first_name, $value->last_name );
		
		} elseif ( is_array( $value ) && isset( $value['user_email'] ) ) {
			
			$return = sprintf( '%s %s', $value['user_firstname'], $value['user_lastname'] );
			
		} elseif ( $value instanceof WP_Term ) {
			
			$return = $value->name;
			
		} elseif ( is_array( $value ) ) {
			
			$returns = array();
			
			foreach ( $value as $single_value ) {
				
				$returns[] = $this->display_default_value( $single_value );
				
			}
			
			$return = join( ', ', $returns );
			
		} else {
			
			$return = (string)$value;
			
		}
	
		// Sanitize output to protect against XSS
		return htmlspecialchars( $return );
	}
	

}

fea_instance()->dynamic_values = new Dynamic_Values();

endif;	