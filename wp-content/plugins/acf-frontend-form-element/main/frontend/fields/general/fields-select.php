<?php

if ( ! class_exists( 'acf_field_fields_select' ) ) :

	class acf_field_fields_select extends acf_field {


		/*
		*  __construct
		*
		*  This function will setup the field type data
		*
		*  @type    function
		*  @date    5/03/2014
		*  @since   5.0.0
		*
		*  @param   n/a
		*  @return  n/a
		*/

		function initialize() {

			// vars
			$this->name      = 'fields_select';
			$this->label     = __( 'ACF Fields', FEA_NS );
			$this->category  = __( 'Form', FEA_NS );
			$this->defaults  = array(
				'fields_select'        => '',
			);

			// filters
            add_action( 'wp_ajax_acf/fields/fields_select/query', array( $this, 'ajax_query' ) );
			add_filter( 'acf/get_fields', array( $this, 'acf_get_fields' ), 5, 2 );
			add_filter( 'acf/prepare_field', array( $this, 'acf_prepare_field' ), 10, 1 );

		}


		/*
		*  is_enabled
		*
		*  This function will return true if acf_local functionality is enabled
		*
		*  @type    function
		*  @date    14/07/2016
		*  @since   5.4.0
		*
		*  @param   n/a
		*  @return  n/a
		*/

		function is_enabled() {

			return acf_is_filter_enabled( 'fields_select' );

		}


		/*
		*  load_field()
		*
		*  This filter is appied to the $field after it is loaded from the database
		*
		*  @type    filter
		*  @since   3.6
		*  @date    23/01/13
		*
		*  @param   $field - the field array holding all the field options
		*
		*  @return  $field - the field array holding all the field options
		*/

		function load_field( $field ) {

			// bail early if not enabled
			if ( ! $this->is_enabled() ) {
				return $field;
			}

			// load sub fields
			// - sub field name's will be modified to include prefix_name settings
			$field['sub_fields'] = $this->get_cloned_fields( $field );

			// return
			return $field;

		}


		/*
		*  acf_get_fields
		*
		*  This function will hook into the 'acf/get_fields' filter and inject/replace seamless clones fields
		*
		*  @type    function
		*  @date    17/06/2016
		*  @since   5.3.8
		*
		*  @param   $fields (array)
		*  @param   $parent (array)
		*  @return  $fields
		*/

		function acf_get_fields( $fields, $parent ) {

			// bail early if empty
			if ( empty( $fields ) ) {
				return $fields;
			}

			// bail early if not enabled
			if ( ! $this->is_enabled() ) {
				return $fields;
			}

			// vars
			$i = 0;

			// loop
			while ( $i < count( $fields ) ) {

				// vars
				$field = $fields[ $i ];

				// $i
				$i++;

				// bail early if not a clone field
				if ( $field['type'] != 'fields_select' ) {
					continue;
				}

				// bail early if not seamless
				if ( $field['display'] != 'seamless' ) {
					continue;
				}

				// bail early if sub_fields isn't set or not an array
				if ( ! isset( $field['sub_fields'] ) || ! is_array( $field['sub_fields'] ) ) {
					continue;
				}

				// replace this clone field with sub fields
				$i--;
				array_splice( $fields, $i, 1, $field['sub_fields'] );

			}

			// return
			return $fields;

		}

		/*
		*  render_field()
		*
		*  Create the HTML interface for your field
		*
		*  @param   $field - an array holding all the field's data
		*
		*  @type    action
		*  @since   3.6
		*  @date    23/01/13
		*/

		function render_field( $field ) {

			// bail early if no sub fields
			if ( empty( $field['sub_fields'] ) ) {
				return;
			}

			// load values
			foreach ( $field['sub_fields'] as &$sub_field ) {

				// add value
				if ( isset( $field['value'][ $sub_field['key'] ] ) ) {

					// this is a normal value
					$sub_field['value'] = $field['value'][ $sub_field['key'] ];

				} elseif ( isset( $sub_field['default_value'] ) ) {

					// no value, but this sub field has a default value
					$sub_field['value'] = $sub_field['default_value'];

				}

				// update prefix to allow for nested values
				$sub_field['prefix'] = $field['name'];

				// restore label
				$sub_field['label'] = $sub_field['__label'];

				// restore required
				if ( $field['required'] ) {
					$sub_field['required'] = 0;
				}
			}

			// render
			if ( $field['layout'] == 'table' ) {

				$this->render_field_table( $field );

			} else {

				$this->render_field_block( $field );

			}

		}


		/*
		*  render_field_block
		*
		*  description
		*
		*  @type    function
		*  @date    12/07/2016
		*  @since   5.4.0
		*
		*  @param   $post_id (int)
		*  @return  $post_id (int)
		*/

		function render_field_block( $field ) {

			// vars
			$label_placement = $field['layout'] == 'block' ? 'top' : 'left';

			// html
			echo '<div class="acf-clone-fields acf-fields -' . $label_placement . ' -border">';

			foreach ( $field['sub_fields'] as $sub_field ) {

				acf_render_field_wrap( $sub_field );

			}

			echo '</div>';

		}


		/*
		*  render_field_table
		*
		*  description
		*
		*  @type    function
		*  @date    12/07/2016
		*  @since   5.4.0
		*
		*  @param   $post_id (int)
		*  @return  $post_id (int)
		*/

		function render_field_table( $field ) {

			?>
<table class="acf-table">
	<thead>
		<tr>
			<?php
			foreach ( $field['sub_fields'] as $sub_field ) :

				// Prepare field (allow sub fields to be removed).
				$sub_field = acf_prepare_field( $sub_field );
				if ( ! $sub_field ) {
					continue;
				}

				// Define attrs.
				$attrs              = array();
				$attrs['class']     = 'acf-th';
				$attrs['data-name'] = $sub_field['_name'];
				$attrs['data-type'] = $sub_field['type'];
				$attrs['data-key']  = $sub_field['key'];

				if ( $sub_field['wrapper']['width'] ) {
					$attrs['data-width'] = $sub_field['wrapper']['width'];
					$attrs['style']      = 'width: ' . $sub_field['wrapper']['width'] . '%;';
				}

				?>
			<th <?php acf_esc_attr_e( $attrs ); ?>>
				<?php acf_render_field_label( $sub_field ); ?>
				<?php acf_render_field_instructions( $sub_field ); ?>
			</th>
			<?php endforeach; ?>
		</tr>
	</thead>
	<tbody>
		<tr class="acf-row">
			<?php

			foreach ( $field['sub_fields'] as $sub_field ) {

				acf_render_field_wrap( $sub_field, 'td' );

			}

			?>
		</tr>
	</tbody>
</table>
			<?php

		}


		/*
		*  render_field_settings()
		*
		*  Create extra options for your field. This is rendered when editing a field.
		*  The value of $field['name'] can be used (like bellow) to save extra data to the $field
		*
		*  @param   $field  - an array holding all the field's data
		*
		*  @type    action
		*  @since   3.6
		*  @date    23/01/13
		*/

		function render_field_settings( $field ) {

			// temp enable 'local' to allow .json fields to be displayed
			acf_enable_filter( 'local' );

			// default_value
			acf_render_field_setting(
				$field,
				array(
					'label'        => __( 'Fields or Field Groups', 'acf' ),
					'instructions' => __( 'Select one or more fields or field groups', FEA_NS ),
					'type'         => 'select',
					'name'         => 'fields_select',
					'multiple'     => 1,
					'allow_null'   => 1,
					'choices'      => $this->get_clone_setting_choices( $field['fields_select'] ),
					'ui'           => 1,
                    'ajax'         => 1,
					'ajax_action'  => 'acf/fields/fields_select/query',
					'placeholder'  => '',
				)
			);

			acf_disable_filter( 'local' );

		}


		/*
		*  get_clone_setting_choices
		*
		*  This function will return an array of choices data for Select2
		*
		*  @type    function
		*  @date    17/06/2016
		*  @since   5.3.8
		*
		*  @param   $value (mixed)
		*  @return  (array)
		*/

		function get_clone_setting_choices( $value ) {

			// vars
			$choices = array();

			// bail early if no $value
			if ( empty( $value ) ) {
				return $choices;
			}

			// force value to array
			$value = acf_get_array( $value );

			// loop
			foreach ( $value as $v ) {

				$choices[ $v ] = $this->get_clone_setting_choice( $v );

			}

			// return
			return $choices;

		}


		/*
		*  get_clone_setting_choice
		*
		*  This function will return the label for a given clone choice
		*
		*  @type    function
		*  @date    17/06/2016
		*  @since   5.3.8
		*
		*  @param   $selector (mixed)
		*  @return  (string)
		*/

		function get_clone_setting_choice( $selector = '' ) {

			// bail early no selector
			if ( ! $selector ) {
				return '';
			}

			// ajax_fields
			if ( isset( $_POST['fields'][ $selector ] ) ) {

				return $this->get_clone_setting_field_choice( $_POST['fields'][ $selector ] );

			}

			// field
			if ( acf_is_field_key( $selector ) ) {

				return $this->get_clone_setting_field_choice( acf_get_field( $selector ) );

			}

			// group
			if ( acf_is_field_group_key( $selector ) ) {

				return $this->get_clone_setting_group_choice( acf_get_field_group( $selector ) );

			}

			// return
			return $selector;

		}


		/*
		*  get_clone_setting_field_choice
		*
		*  This function will return the text for a field choice
		*
		*  @type    function
		*  @date    20/07/2016
		*  @since   5.4.0
		*
		*  @param   $field (array)
		*  @return  (string)
		*/

		function get_clone_setting_field_choice( $field ) {

			// bail early if no field
			if ( ! $field ) {
				return __( 'Unknown field', 'acf' );
			}

			// title
			$title = $field['label'] ? $field['label'] : __( '(no title)', 'acf' );

			// append type
			$title .= ' (' . $field['type'] . ')';

			// ancestors
			// - allow for AJAX to send through ancestors count
			$ancestors = isset( $field['ancestors'] ) ? $field['ancestors'] : count( acf_get_field_ancestors( $field ) );
			$title     = str_repeat( '- ', $ancestors ) . $title;

			// return
			return $title;

		}


		/*
		*  get_clone_setting_group_choice
		*
		*  This function will return the text for a group choice
		*
		*  @type    function
		*  @date    20/07/2016
		*  @since   5.4.0
		*
		*  @param   $field_group (array)
		*  @return  (string)
		*/

		function get_clone_setting_group_choice( $field_group ) {

			// bail early if no field group
			if ( ! $field_group ) {
				return __( 'Unknown field group', 'acf' );
			}

			// return
			return sprintf( __( 'All fields from %s field group', 'acf' ), $field_group['title'] );

		}


		/*
		*  ajax_query
		*
		*  description
		*
		*  @type    function
		*  @date    17/06/2016
		*  @since   5.3.8
		*
		*  @param   $post_id (int)
		*  @return  $post_id (int)
		*/

		function ajax_query() {

			// validate
			if ( ! acf_verify_ajax() ) {
				die();
			}

			// disable field to allow clone fields to appear selectable
			acf_disable_filter( 'fields_select' );

			// options
			$options = acf_parse_args(
				$_POST,
				array(
					'post_id' => 0,
					'paged'   => 0,
					's'       => '',
					'title'   => '',
					'fields'  => array(),
				)
			);

			// vars
			$results     = array();
			$s           = false;
			$i           = -1;
			$limit       = 20;
			$range_start = $limit * ( $options['paged'] - 1 );  // 0,  20, 40
			$range_end   = $range_start + ( $limit - 1 );         // 19, 39, 59

			// search
			if ( $options['s'] !== '' ) {

				// strip slashes (search may be integer)
				$s = wp_unslash( strval( $options['s'] ) );

			}

			// load groups
			$field_groups = acf_get_field_groups();
			$field_group  = false;

			// bail early if no field groups
			if ( empty( $field_groups ) ) {
				die();
			}

			// move current field group to start
			foreach ( array_keys( $field_groups ) as $j ) {

				// check ID
				if ( $field_groups[ $j ]['ID'] !== $options['post_id'] ) {
					continue;
				}

				// extract field group and move to start
				$field_group = acf_extract_var( $field_groups, $j );

				// field group found, stop looking
				break;

			}

			// if field group was not found, this is a new field group (not yet saved)
			if ( ! $field_group ) {

				$field_group = array(
					'ID'    => $options['post_id'],
					'title' => $options['title'],
					'key'   => '',
				);

			}

			// move current field group to start of list
			array_unshift( $field_groups, $field_group );

			// loop
			foreach ( $field_groups as $field_group ) {

				// vars
				$fields   = false;
				$ignore_s = false;
				$data     = array(
					'text'     => $field_group['title'],
					'children' => array(),
				);

				// get fields
				if ( $field_group['ID'] == $options['post_id'] ) {

					$fields = $options['fields'];

				} else {

					$fields = acf_get_fields( $field_group );
					$fields = acf_prepare_fields_for_import( $fields );

				}

				// bail early if no fields
				if ( ! $fields ) {
					continue;
				}

				// show all children for field group search match
				if ( $s !== false && stripos( $data['text'], $s ) !== false ) {

					$ignore_s = true;

				}

				// populate children
				$children   = array();
				$children[] = $field_group['key'];
				foreach ( $fields as $field ) {
					$children[] = $field['key']; }

				// loop
				foreach ( $children as $child ) {

					// bail ealry if no key (fake field group or corrupt field)
					if ( ! $child ) {
						continue;
					}

					// vars
					$text = false;

					// bail early if is search, and $text does not contain $s
					if ( $s !== false && ! $ignore_s ) {

						// get early
						$text = $this->get_clone_setting_choice( $child );

						// search
						if ( stripos( $text, $s ) === false ) {
							continue;
						}
					}

					// $i
					$i++;

					// bail early if $i is out of bounds
					if ( $i < $range_start || $i > $range_end ) {
						continue;
					}

					// load text
					if ( $text === false ) {
						$text = $this->get_clone_setting_choice( $child );
					}

					// append
					$data['children'][] = array(
						'id'   => $child,
						'text' => $text,
					);

				}

				// bail early if no children
				// - this group contained fields, but none shown on this page
				if ( empty( $data['children'] ) ) {
					continue;
				}

				// append
				$results[] = $data;

				// end loop if $i is out of bounds
				// - no need to look further
				if ( $i > $range_end ) {
					break;
				}
			}

			// return
			acf_send_ajax_results(
				array(
					'results' => $results,
					'limit'   => $limit,
				)
			);

		}


		/*
		*  acf_prepare_field
		*
		*  This function will restore a field's key ready for input
		*
		*  @type    function
		*  @date    6/09/2016
		*  @since   5.4.0
		*
		*  @param   $field (array)
		*  @return  $field
		*/

		function acf_prepare_field( $field ) {

			// bail ealry if not cloned
			if ( empty( $field['_clone'] ) ) {
				return $field;
			}

			// restore key
			if ( isset( $field['__key'] ) ) {
				$field['key'] = $field['__key'];
			}

			// return
			return $field;

		}


		/*
		*  validate_value
		*
		*  description
		*
		*  @type    function
		*  @date    11/02/2014
		*  @since   5.0.0
		*
		*  @param   $post_id (int)
		*  @return  $post_id (int)
		*/

		function validate_value( $valid, $value, $field, $input ) {

			// bail early if no $value
			if ( empty( $value ) ) {
				return $valid;
			}

			// bail early if no sub fields
			if ( empty( $field['sub_fields'] ) ) {
				return $valid;
			}

			// loop
			foreach ( array_keys( $field['sub_fields'] ) as $i ) {

				// get sub field
				$sub_field = $field['sub_fields'][ $i ];
				$k         = $sub_field['key'];

				// bail early if valu enot set (conditional logic?)
				if ( ! isset( $value[ $k ] ) ) {
					continue;
				}

				// validate
				acf_validate_value( $value[ $k ], $sub_field, "{$input}[{$k}]" );

			}

			// return
			return $valid;

		}

	}


	// initialize
	acf_register_field_type( 'acf_field_fields_select' );

endif; // class_exists check

?>
