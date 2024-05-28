<?php

if( ! class_exists('acf_field_upload_file') ) :

class acf_field_upload_file extends acf_field {
	
	
	/*
	*  __construct
	*
	*  This function will setup the field type data
	*
	*  @type	function
	*  @date	5/03/2014
	*  @since	5.0.0
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function initialize() {
		
		// vars
		$this->name = 'upload_file';
		$this->label = __("Upload File",FEA_NS);
		$this->public = false;
		$this->defaults = array(
			'return_format'	=> 'array',
			'preview_size'	=> 'thumbnail',
			'library'		=> 'all',
			'min_width'		=> 0,
			'min_height'	=> 0,
			'min_size'		=> 0,
			'max_width'		=> 0,
			'max_height'	=> 0,
			'max_size'		=> 0,
			'mime_types'	=> '',
            'button_text'   => __( 'Add Image', FEA_NS ),
		);

		//actions
		add_action('wp_ajax_acf/fields/upload_file/add_attachment',				array($this, 'ajax_add_attachment'));
		add_action('wp_ajax_nopriv_acf/fields/upload_file/add_attachment',		array($this, 'ajax_add_attachment'));

		// filters
		add_filter('get_media_item_args',				array($this, 'get_media_item_args'));
		add_filter('wp_prepare_attachment_for_js',		array($this, 'wp_prepare_attachment_for_js'), 10, 3);

		add_filter( 'acf/prepare_field/type=image',  [ $this, 'prepare_image_or_file_field'], 5 );
		add_filter( 'acf/prepare_field/type=file',  [ $this, 'prepare_image_or_file_field'], 5 );

		$single_file = array( 'file', 'image', 'upload_file', 'featured_image', 'main_image', 'site_logo' );
		foreach( $single_file as $type ){
			add_filter( 'acf/update_value/type=' .$type, [ $this, 'update_attachment_value'], 8, 3 );
			add_action( 'acf/render_field_settings/type=' .$type,  [ $this, 'upload_button_text_setting'] );	
		}
	
	}

	function ajax_add_attachment() {

		$args = acf_parse_args( $_POST, array(
			'field_key'		=> '',
			'nonce'			=> '',
		));
		
		// validate nonce
		if( !wp_verify_nonce($args['nonce'], 'fea_form') ) {
			wp_send_json_error( __( 'Invalid Nonce', FEA_NS ) );			
		} 
				
		// bail early if no attachments
		if( empty($_FILES['file']['name']) ) {		
			wp_send_json_error( __( 'Missing file name', FEA_NS ) );
		}

		//TO dos: validate file types, sizes, and dimensions
		//Add loading bar for each image

		if( isset( $args['field_key'] ) ){
			$field = get_field_object( $args['field_key'] );
		}else{
			wp_send_json_error( __( 'Invalid Key', FEA_NS ) );
		}
		
		// get errors
		$errors = acf_validate_attachment( $_FILES['file'], $field, 'upload' );
		
		// append error
		if( !empty($errors) ) {				
			$data = implode("\n", $errors);
			wp_send_json_error( $data );
		}		

		/* Getting file name */
		$upload = wp_upload_bits( $_FILES['file']['name'], null, file_get_contents( $_FILES['file']['tmp_name'] ) );

		$wp_filetype = wp_check_filetype( basename( $upload['file'] ), null );
	
		$wp_upload_dir = wp_upload_dir();
	
		$attachment = array(
			'guid' => $wp_upload_dir['baseurl'] . _wp_relative_upload_path( $upload['file'] ),
			'post_mime_type' => $wp_filetype['type'],
			'post_title' => preg_replace('/\.[^.]+$/', '', basename( $upload['file'] )),
			'post_content' => '',
			'post_status' => 'inherit'
		);
		
		$attach_id = wp_insert_attachment( $attachment, $upload['file'] );
		update_post_meta( $attach_id, 'hide_from_lib', 1 );

		require_once(ABSPATH . 'wp-admin/includes/image.php');
	
		$attach_data = wp_generate_attachment_metadata( $attach_id, $upload['file'] );
		wp_update_attachment_metadata( $attach_id, $attach_data );

		$return_data = array( 'id' => $attach_id, 'title' => $attachment['post_title'] );

		if( $wp_filetype['type'] == 'application/pdf' )	$return_data['src'] = wp_mime_type_icon( $wp_filetype['type'] );
		
		wp_send_json_success( $return_data );
    }

	function prepare_image_or_file_field( $field ) {
		$uploader = acf_get_setting('uploader');		

		if( $uploader == 'basic' || ! empty( $field['button_text'] ) ) {
			$field['field_type'] = $field['type'];
			$field['type'] = 'upload_file';
			$field['wrapper']['data-field_type'] = $field['field_type'];
		}
		
		if( $uploader == 'basic' ){
			if( isset( $field['wrapper']['class'] ) ){ 
				$field['wrapper']['class'] .= ' acf-uploads';
			}else{
				$field['wrapper']['class'] = 'acf-uploads';
			}
		}
		$field['wrapper']['class'] .= ' image-field';
		
		return $field;
	}
	function prepare_field( $field ){
		$uploader = acf_get_setting('uploader');

		if( empty( $field['field_type'] ) ){
			$field['wrapper']['data-field_type'] = 'image';
		}else{
			$field['wrapper']['data-field_type'] = $field['field_type'];
		}
		// enqueue
		if( $uploader == 'basic' ){
			if( isset( $field['wrapper']['class'] ) ){ 
				$field['wrapper']['class'] .= ' acf-uploads';
			}else{
				$field['wrapper']['class'] = 'acf-uploads';
			}
		}
		$field['wrapper']['class'] .= ' image-field';
		return $field;
	}
    /*
	*  input_admin_enqueue_scripts
	*
	*  description
	*
	*  @type	function
	*  @date	16/12/2015
	*  @since	5.3.2
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function input_admin_enqueue_scripts() {
		
		// localize
		acf_localize_text(array(
		   	'Select Image'	=> __('Select File', FEA_NS),
			'Edit Image'	=> __('Edit File', FEA_NS),
			'Update Image'	=> __('Update File', FEA_NS),
			'All images'	=> __('All', FEA_NS),
	   	));
	}
	
	function upload_button_text_setting( $field ) {
		acf_render_field_setting( $field, array(
			'label'			=> __('Button Text'),
			'name'			=> 'button_text',
			'type'			=> 'text',
			'placeholder'	=> __( 'Add Image', FEA_NS ),
		) );
	}
	
	/*
	*  render_field()
	*
	*  Create the HTML interface for your field
	*
	*  @param	$field - an array holding all the field's data
	*
	*  @type	action
	*  @since	3.6
	*  @date	23/01/13
	*/
	
	function render_field( $field ) {
		if( empty( $field['field_type'] ) ){
			$field['field_type'] = 'image';
		}
		if( empty( $field['preview_size'] ) ) $field['preview_size'] = 'thumbnail';


		// vars
		$uploader = acf_get_setting('uploader');
		
		// enqueue
		if( $uploader == 'wp' ) {
			acf_enqueue_uploader();
		}
		
		
		// vars
		$url = '';
		$alt = '';
		$div = array(
			'class'					=> 'acf-'.$field['field_type'].'-uploader',
			'data-preview_size'		=> $field['preview_size'],
			'data-library'			=> $field['library'],
			'data-mime_types'		=> $field['mime_types'],
			'data-uploader'			=> $uploader,
		);
		if( ! empty( $field['button_text'] ) ){
			$button_text = $field['button_text'];
		}else{
			if( $field['field_type'] == 'image' ){
				$button_text = __( 'Add Image', FEA_NS );	
			}else{
				$button_text = __( 'Add File', FEA_NS );	
			}	
		}

		// has value?
		if( $field['value'] ) {			
			// update vars
			$url = wp_get_attachment_image_src($field['value'], $field['preview_size']);
			$alt = get_post_meta($field['value'], '_wp_attachment_image_alt', true);
			
			
			// url exists
			if( $url ) $url = $url[0];
			
			
			// url exists
			if( $url ) {
				$div['class'] .= ' has-value';
			}
						
		}
		
		
		// get size of preview value
		$size = acf_get_image_size($field['preview_size']);
		
?>
<div <?php acf_esc_attr_e( $div ); ?>>
	<?php acf_hidden_input(array( 'name' => $field['name'], 'value' => $field['value'] )); ?>
	<div class="show-if-value image-wrap" <?php if( $size['width'] ): ?>style="<?php echo esc_attr('max-width: '.$size['width'].'px'); ?>"<?php endif; ?>>
		<img data-name="image" src="<?php echo esc_url($url); ?>" alt="<?php echo esc_attr($alt); ?>"/>
		<div class="frontend-admin-hidden uploads-progress"><div class="percent">0%</div><div class="bar"></div></div>
		<div class="acf-actions -hover">
			<?php 
		//	if( $uploader != 'basic' ): 
			?><a class="acf-icon -pencil dark" data-name="edit" href="#" title="<?php _e('Edit', FEA_NS); ?>"></a><?php 
		//	endif;
			?><a class="acf-icon -cancel dark" data-name="remove" href="#" title="<?php _e('Remove', FEA_NS); ?>"></a>
		</div>
	</div>
	<div class="hide-if-value">
		<?php if( $uploader == 'basic' ): ?>
			<label class="acf-basic-uploader file-drop">
                <?php 
				$input_args = array( 'name' => 'upload_file_input', 'id' => $field['id'], 'class' => 'image-preview' );
				if( $field['field_type'] == 'image' ) $input_args['accept'] = "image/*"; 
				acf_file_input( $input_args ); ?>
                <div class="file-custom">
					<?php  _e('No ' .$field['field_type']. ' selected',FEA_NS); ?>
					<div class="acf-button button">
						<?php echo $button_text; ?>
					</div>
				</div>
			</label>
		
		<?php else: ?>
			
			<p><?php  _e('No ' .$field['field_type']. ' selected',FEA_NS); ?> <a data-name="add" class="acf-button button" href="#"><?php echo $button_text; ?></a></p>
			
		<?php endif; ?>
			
	</div>
</div>
<?php
		
	}
	
	
	/*
	*  render_field_settings()
	*
	*  Create extra options for your field. This is rendered when editing a field.
	*  The value of $field['name'] can be used (like bellow) to save extra data to the $field
	*
	*  @type	action
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$field	- an array holding all the field's data
	*/
	
	function render_field_settings( $field ) {
		
		// clear numeric settings
		$clear = array(
			'min_width',
			'min_height',
			'min_size',
			'max_width',
			'max_height',
			'max_size'
		);
		
		foreach( $clear as $k ) {
			
			if( empty($field[$k]) ) {
				
				$field[$k] = '';
				
			}
			
		}
		
		
		// return_format
		acf_render_field_setting( $field, array(
			'label'			=> __('Return Value',FEA_NS),
			'instructions'	=> __('Specify the returned value on front end',FEA_NS),
			'type'			=> 'radio',
			'name'			=> 'return_format',
			'layout'		=> 'horizontal',
			'choices'		=> array(
				'array'			=> __("Image Array",FEA_NS),
				'url'			=> __("Image URL",FEA_NS),
				'id'			=> __("Image ID",FEA_NS)
			)
		));
		
		
		// preview_size
		acf_render_field_setting( $field, array(
			'label'			=> __('Preview Size',FEA_NS),
			'instructions'	=> __('Shown when entering data',FEA_NS),
			'type'			=> 'select',
			'name'			=> 'preview_size',
			'choices'		=> acf_get_image_sizes()
		));
		
		
		// library
		acf_render_field_setting( $field, array(
			'label'			=> __('Library',FEA_NS),
			'instructions'	=> __('Limit the media library choice',FEA_NS),
			'type'			=> 'radio',
			'name'			=> 'library',
			'layout'		=> 'horizontal',
			'choices' 		=> array(
				'all'			=> __('All', FEA_NS),
				'uploadedTo'	=> __('Uploaded to post', FEA_NS)
			)
		));
		
		
		// min
		acf_render_field_setting( $field, array(
			'label'			=> __('Minimum',FEA_NS),
			'instructions'	=> __('Restrict which images can be uploaded',FEA_NS),
			'type'			=> 'text',
			'name'			=> 'min_width',
			'prepend'		=> __('Width', FEA_NS),
			'append'		=> 'px',
		));
		
		acf_render_field_setting( $field, array(
			'label'			=> '',
			'type'			=> 'text',
			'name'			=> 'min_height',
			'prepend'		=> __('Height', FEA_NS),
			'append'		=> 'px',
			'_append' 		=> 'min_width'
		));
		
		acf_render_field_setting( $field, array(
			'label'			=> '',
			'type'			=> 'text',
			'name'			=> 'min_size',
			'prepend'		=> __('File size', FEA_NS),
			'append'		=> 'MB',
			'_append' 		=> 'min_width'
		));	
		
		
		// max
		acf_render_field_setting( $field, array(
			'label'			=> __('Maximum',FEA_NS),
			'instructions'	=> __('Restrict which images can be uploaded',FEA_NS),
			'type'			=> 'text',
			'name'			=> 'max_width',
			'prepend'		=> __('Width', FEA_NS),
			'append'		=> 'px',
		));
		
		acf_render_field_setting( $field, array(
			'label'			=> '',
			'type'			=> 'text',
			'name'			=> 'max_height',
			'prepend'		=> __('Height', FEA_NS),
			'append'		=> 'px',
			'_append' 		=> 'max_width'
		));
		
		acf_render_field_setting( $field, array(
			'label'			=> '',
			'type'			=> 'text',
			'name'			=> 'max_size',
			'prepend'		=> __('File size', FEA_NS),
			'append'		=> 'MB',
			'_append' 		=> 'max_width'
		));	
		
		
		// allowed type
		acf_render_field_setting( $field, array(
			'label'			=> __('Allowed file types',FEA_NS),
			'instructions'	=> __('Comma separated list. Leave blank for all types',FEA_NS),
			'type'			=> 'text',
			'name'			=> 'mime_types',
		));
		
	}
	
	
	/*
	*  format_value()
	*
	*  This filter is appied to the $value after it is loaded from the db and before it is returned to the template
	*
	*  @type	filter
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$value (mixed) the value which was loaded from the database
	*  @param	$post_id (mixed) the $post_id from which the value was loaded
	*  @param	$field (array) the field array holding all the field options
	*
	*  @return	$value (mixed) the modified value
	*/
	
	function format_value( $value, $post_id, $field ) {
		
		// bail early if no value
		if( empty($value) ) return false;
		
		
		// bail early if not numeric (error message)
		if( !is_numeric($value) ) return false;
		
		
		// convert to int
		$value = intval($value);
		
		
		// format
		if( $field['return_format'] == 'url' ) {
		
			return wp_get_attachment_url( $value );
			
		} elseif( $field['return_format'] == 'array' ) {
			
			return acf_get_attachment( $value );
			
		}
		
		
		// return
		return $value;
		
	}
	
	
	/*
	*  get_media_item_args
	*
	*  description
	*
	*  @type	function
	*  @date	27/01/13
	*  @since	3.6.0
	*
	*  @param	$vars (array)
	*  @return	$vars
	*/
	
	function get_media_item_args( $vars ) {
	
	    $vars['send'] = true;
	    return($vars);
	    
	}
		
	
	/*
	*  wp_prepare_attachment_for_js
	*
	*  this filter allows ACF to add in extra data to an attachment JS object
	*  This sneaky hook adds the missing sizes to each attachment in the 3.5 uploader. 
	*  It would be a lot easier to add all the sizes to the 'image_size_names_choose' filter but 
	*  then it will show up on the normal the_content editor
	*
	*  @type	function
	*  @since:	3.5.7
	*  @date	13/01/13
	*
	*  @param	{int}	$post_id
	*  @return	{int}	$post_id
	*/
	
	function wp_prepare_attachment_for_js( $response, $attachment, $meta ) {
		
		// only for image
		if( $response['type'] != 'image' ) {
		
			return $response;
			
		}
		
		
		// make sure sizes exist. Perhaps they dont?
		if( !isset($meta['sizes']) ) {
		
			return $response;
			
		}
		
		
		$attachment_url = $response['url'];
		$base_url = str_replace( wp_basename( $attachment_url ), '', $attachment_url );
		
		if( isset($meta['sizes']) && is_array($meta['sizes']) ) {
		
			foreach( $meta['sizes'] as $k => $v ) {
			
				if( !isset($response['sizes'][ $k ]) ) {
				
					$response['sizes'][ $k ] = array(
						'height'      => $v['height'],
						'width'       => $v['width'],
						'url'         => $base_url .  $v['file'],
						'orientation' => $v['height'] > $v['width'] ? 'portrait' : 'landscape',
					);
				}
				
			}
			
		}

		return $response;
	}

	function update_attachment_value( $value, $post_id = false, $field = false ){									
		if( is_numeric( $post_id ) ){  				
			remove_filter( 'acf/update_value/type=' . $field['type'], [ $this, 'update_attachment_value'], 8, 3 );
			$value = (int) $value;
			$post = get_post( $post_id );
			if( wp_is_post_revision( $post ) ){
				$post_id = $post->post_parent;
			}
			acf_connect_attachment_to_post( $value, $post_id );

			add_filter( 'acf/update_value/type=' . $field['type'], [ $this, 'update_attachment_value'], 8, 3 );
		}	
		delete_post_meta( $value, 'hide_from_lib' );
		return $value;
	}
		
	/*
	*  update_value()
	*
	*  This filter is appied to the $value before it is updated in the db
	*
	*  @type	filter
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$value - the value which will be saved in the database
	*  @param	$post_id - the $post_id of which the value will be saved
	*  @param	$field - the field array holding all the field options
	*
	*  @return	$value - the modified value
	*/
	
	function update_value( $value, $post_id, $field ) {
		return acf_get_field_type('file')->update_value( $value, $post_id, $field );
		
	}
	
	
	/*
	*  validate_value
	*
	*  This function will validate a basic file input
	*
	*  @type	function
	*  @date	11/02/2014
	*  @since	5.0.0
	*
	*  @param	$post_id (int)
	*  @return	$post_id (int)
	*/
	
	function validate_value( $valid, $value, $field, $input ){
		
		return acf_get_field_type('file')->validate_value( $valid, $value, $field, $input );
		
	}
	
}


// initialize
acf_register_field_type( 'acf_field_upload_file' );

endif; // class_exists check

?>