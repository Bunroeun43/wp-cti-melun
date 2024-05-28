<div class="acf-field-list-wrap">
	
	<ul class="acf-hl acf-thead">
		<li class="li-field-order"><?php _e( 'Order', 'acf' ); ?></li>
		<li class="li-field-label"><?php _e( 'Label', 'acf' ); ?></li>
		<li class="li-field-name"><?php _e( 'Name', 'acf' ); ?></li>
		<li class="li-field-key"><?php _e( 'Key', 'acf' ); ?></li>
		<li class="li-field-type"><?php _e( 'Type', 'acf' ); ?></li>
	</ul>
	
	<div class="acf-field-list
	<?php
	if ( ! $fields ) {
		echo ' -empty'; }
	?>
	">
		
		<div class="no-fields-message">
			<?php _e( 'No fields. Click the <strong>+ Add Field</strong> button to create your first field.', 'acf' ); ?>
		</div>
		
		<?php
		if ( $fields ) :

			foreach ( $fields as $i => $field ) :

				fea_instance()->form_builder->get_view('form-field-object',
					array(
						'field' => $field,
						'i'     => $i,
					)
				);

			endforeach;

		endif;
		?>
		
	</div>
	
	<ul class="acf-hl acf-tfoot">
		<li class="acf-fr">
			<a href="#" class="button button-primary button-large add-fields"><?php _e( '+ Add Field', 'acf' ); ?></a>
		</li>
        <!-- <li class="acf-fr">
			<a href="#" class="button button-primary button-large add-step"><?php //_e( '+ Add Step', 'acf' ); ?></a>
		</li>  -->
	</ul>
	
<?php
if ( ! $parent ) :

	// get clone
	$clone = acf_get_valid_field(
		array(
			'ID'    => 'acfcloneindex',
			'key'   => 'acfcloneindex',
			'label' => '',
			'name'  => '',
			'type'  => 'loading',
		)
	);

	?>
	<script type="text/html" id="tmpl-acf-field">
	<?php
	fea_instance()->form_builder->get_view('form-field-object',
		array(
			'field' => $clone,
			'i'     => 0,
		)
	);
	?>
	</script>
<?php endif; ?>
	
</div>
