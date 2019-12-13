var multiOptionsInput = {};

jQuery(document).ready(function(){
	
	jQuery('[data-id="multi-options-input"]').chosen();

	var removed = false;
	// Method to add tags pressing enter
	jQuery('body').on('keyup', 'select[data-id=multi-options-input]+div.chzn-container.chzn-container-multi input', function(event) {
		var selector = "#" + jQuery(this).closest('.chzn-container').siblings('[data-id="multi-options-input"]').attr('id');
		// solucao para garantir que o drop de opcoes seja removido apenas uma vez
		if(!removed){
			jQuery('[data-id=multi-options-input]').siblings('.chzn-container').find('.chzn-drop').remove();
			removed = true;
		}

		// A tag é maior que os caracteres mínimos necessários e enter pressionado
		if (this.value && this.value.length >= 3 && (event.which === 13 || event.which === 188)) {
			// Search an highlighted result
			var highlighted = jQuery(selector+'_chzn').find('li.active-result.highlighted').first();

			// Add the highlighted option
			if (event.which === 13 && highlighted.text() !== '')
			{
				// Extra check. If we have added a custom tag with this text remove it
				var customOptionValue = highlighted.text();
				jQuery(selector+' option').filter(function () { return jQuery(this).val() == customOptionValue; }).remove();

				// Select the highlighted result
				var tagOption = jQuery(selector+' option').filter(function () { return jQuery(this).html() == highlighted.text(); });
				tagOption.attr('selected', 'selected');
			}
			// Add the custom tag option
			else
			{
				var customTag = this.value;

				// Extra check. Search if the custom tag already exists (typed faster than AJAX ready)
				var tagOption = jQuery(selector+' option').filter(function () { return jQuery(this).html() == customTag; });// jshint ignore:line
				if (tagOption.text() !== '')
				{
					tagOption.attr('selected', 'selected');
				}
				else
				{
					var option = jQuery('<option>');
					option.text(this.value).val(this.value);
					option.attr('selected','selected');

					// Append the option an repopulate the chosen field
					jQuery(selector).append(option);
				}
			}

			this.value = '';
			jQuery(selector).trigger('liszt:updated');
			event.preventDefault();

		}
	});


});
