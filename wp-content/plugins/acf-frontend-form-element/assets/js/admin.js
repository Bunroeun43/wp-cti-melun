(function($) {
	
		/**
	 * Insert text in input at cursor position
	 *
	 * Reference: http://stackoverflow.com/questions/1064089/inserting-a-text-where-cursor-is-using-javascript-jquery
	 *
	 */
		function insert_at_caret(input, text) {
		var txtarea = input;
		if (!txtarea) { return; }
		
		var scrollPos = txtarea.scrollTop;
		var strPos = 0;
		var br = ((txtarea.selectionStart || txtarea.selectionStart == '0') ?
			"ff" : (document.selection ? "ie" : false ) );
		if (br == "ie") {
			txtarea.focus();
			var range = document.selection.createRange();
			range.moveStart ('character', -txtarea.value.length);
			strPos = range.text.length;
		} else if (br == "ff") {
			strPos = txtarea.selectionStart;
		}
		
		var front = (txtarea.value).substring(0, strPos);
		var back = (txtarea.value).substring(strPos, txtarea.value.length);
		txtarea.value = front + text + back;
		strPos = strPos + text.length;
		if (br == "ie") {
			txtarea.focus();
			var ieRange = document.selection.createRange();
			ieRange.moveStart ('character', -txtarea.value.length);
			ieRange.moveStart ('character', strPos);
			ieRange.moveEnd ('character', 0);
			ieRange.select();
		} else if (br == "ff") {
			txtarea.selectionStart = strPos;
			txtarea.selectionEnd = strPos;
			txtarea.focus();
		}
		
		txtarea.scrollTop = scrollPos;
	}


	var dynamicValues = $('<div class="dynamic-values"></div>');
	$.each(acffdv, function (i, group) {
		var sub_div = $('<div class="group-options"><span class="group-name">'+group['label']+'</span></div>');
		$(sub_div).appendTo(dynamicValues);
		var sub_select = $('<select class="dynamic-value-select"><option value="" selected><span class="field-name">-- Select One --</span></option></select>');
		$.each(group['options'], function (j, l) {				
			var sub_option = $('<option class="field-option '+j+'-option" value="['+j+']"><span class="field-name">'+l+'</span></option>');
			
			$(sub_option).appendTo(sub_select);
		});
		$(sub_select).appendTo(sub_div);
    });
	
	
	$(document).ready(function() {

		$(document).on('click','.post-type-admin_form .page-title-action',function(e){
			e.preventDefault();
			$('.frontend-admin-edit-button.render-form').trigger('click');
		});

		$('body').on('change','#acff-post-admin_form_types',function(e){
			var title = $(this).parents('form').find('#acff-post-frontend_admin_title');

			if( title.val() == '' ){
				title.val($(this).find('option[value='+$(this).val()+']').text());
			}
		});

		$('.select2').select2({
			closeOnSelect: false
		});

		$(document).find('.acf-field[data-form-tab]:not([data-form-tab=fields])').addClass('frontend-admin-hidden');

		var currentDynamicField = ''; 
		
		// Close dropdowns when clicking anywhere
		$(document).on( 'click', function(e) {
			if( e.target.id != currentDynamicField && $(e.target).parents('.acf-field').id != currentDynamicField ){
				$('.dynamic-values').remove();
			}
		}); 
		
		$(document).on( 'change', '.dynamic-values select', function(e) {
			
			e.stopPropagation();
			
			var $option = $(this);
			
			var value = $option.val();
			
			var $editor = $option.parents('.acf-field').first().find('.wp-editor-area');
			
			// Check if we should insert into WYSIWYG field or a regular field
			if ( $editor.length > 0 ) {
				
				// WYSIWYG field
				var editor = tinymce.editors[ $editor.attr('id') ];
				editor.editorCommands.execCommand( 'mceInsertContent', false, value );
				$('.dynamic-values').remove();
				$dvOpened = false;
				
			} else {
				
				// Regular field
				var $input = $option.parents('.dynamic-values').siblings('input[type=text]');
				insert_at_caret( $input.get(0), value );
				
			}

			$option.removeProp('selected').closest('select').val('');

			
		});
		
		// Toggle dropdown
		$(document).on( 'input click', '.acf-field[data-dynamic_values] input', function(e) {			
			e.stopPropagation();
			
			var $this = $( this );

				$('.dynamic-values').remove();
				dynamicValues.find('.all_fields-option').addClass('acf-hidden');
				$this.after(dynamicValues);
			
		});

		var $dvOpened;
		$(document).on( 'click', '.acf-field[data-dynamic_values] .dynamic-value-options', function(e) {			
			e.stopPropagation();
			
			var $this = $( this );

			$('.dynamic-values').remove();
			if( $dvOpened != true ){
				$dvOpened = true;
				dynamicValues.find('.all_fields-option').removeClass('acf-hidden');
				$this.after(dynamicValues);
			}else{
				$dvOpened = false;
			}
			
		});

		$(document).on( 'change', '.field-type', function(e) {	
			var $tbody = $(this).parents('.acf-field-settings');
			
			var fieldLabel = $tbody.find('input.field-label');
			if(fieldLabel.val() == ''){
				fieldLabel.val($(this).find('option[value="'+$(this).val()+'"]').text()).trigger('blur');
			}
			var fieldName = $tbody.find('input.field-name');
			if(fieldName.val() == ''){
				fieldName.val($(this).val());
			}
		});
		
		$(document).on( 'change', '.acf-field-admin-form-tabs input[type=radio]', function(e) {	
			$(document).find('.acf-field[data-form-tab]').addClass('frontend-admin-hidden');
			$(document).find('.acf-field[data-form-tab='+$(this).val()+']').removeClass('frontend-admin-hidden');
		} );

	});
	$(document).on('click', '.add-fields', function(e){
		$list = $('#acf-field-group-fields').find('.acf-field-list');
		addField(false,true);
		renderFields( $list );	
	});
	
	$(document).on('click', 'button.bulk-add-fields', function(e){
		
		e.preventDefault();
		var selected = $('#bulk_add_fields').select2('data')
		$('#bulk_add_fields').val('').trigger('change');
		$list = $('#acf-field-group-fields').find('.acf-field-list');
		var defer = $.Deferred().resolve();
		$.each(selected, function(index, field){
			defer = defer.then(function() {
				return addField(field,false);
			});
			 
		});
		defer.then(function() {
			renderFields( $list );		
		});
	});


	function addField(field, open){
		// vars
		var html = $('#tmpl-acf-field').html();
		var $el = $(html);
		var prevId = $el.data('id');
		var newKey = acf.uniqid('field_');
		
		// duplicate
		var $newField = acf.duplicate({
			target: $el,
			search: prevId,
			replace: newKey,
			append: function( $el, $el2 ){ 
				$list.append( $el2 );
			}
		});
		$newField.find('.li-field-type').text(field.text);
		
		// get instance
		var newField = acf.getFieldObject( $newField );
		
		var newType = 'text';
		if( field ){
			newType = field.id;
			newField.prop('label', field.text);
			newField.prop('name', field.id);
		}
		// props
		newField.changeType(newType);
		$newField.find('.field-type').val(newType);
		newField.prop('key', newKey);
		newField.prop('ID', 0);
		
		// attr
		$newField.attr('data-key', newKey);
		$newField.attr('data-id', newKey);

		if(open==true){
			newField.open();
		}
		// update parent prop
		newField.updateParent();
		
		// action
		acf.doAction('add_field_object', newField);
		acf.doAction('append_field_object', newField);
	};

	function renderFields( $list ){
			
		// vars
		var fields = acf.getFieldObjects({
			list: $list
		});
		
		// no fields
		if( !fields.length ) {
			$list.addClass('-empty');
			return;
		}
		
		// has fields
		$list.removeClass('-empty');
		
		// prop
		fields.map(function( field, i ){
			field.prop('menu_order', i);
		});
	}

		
	$(document).on('click', '.copy-shortcode', function(e){
		var copyText = "["+$(this).data('prefix')+" form=\"" + $(this).data('form') + "\"]";

		/* Copy the text */
		navigator.clipboard.writeText(copyText);  
		
		var normalText = $(this).html();

		$(this).addClass('copied-text').html(normalText.replace('Copy code','Code copied'));
		setTimeout(function(){
			$('body').find('.copied-text').removeClass('copied-text').html(normalText.replace('Code copied','Copy code'));
		}, 1000);
	});
})(jQuery);

