var nobossrange = {};

/** 
 * Função construtora, cahmada quando a pagina termina de carregar
*/
nobossrange.CONSTRUCTOR = function() {
    nobossrange.activateFieldsEvents();

    jQuery(document).on('subform-row-add', function (event, element) {
        nobossrange.activateFieldsEvents(element);
    });
};

nobossrange.activateFieldsEvents = function(container){
    container = container === undefined ? container === jQuery('body') : container;

    // Cria o evento que atualiza o campo de numero com o valor do range
    jQuery('.nobossrange', container).on('input', nobossrange.updateTextField);
    // Cria o evento que atualiza o range de acordo com o valor digitado
    jQuery('.nobossrange--input', container).on('input focusout', nobossrange.updateRange);
    // Dá um trigger inicial no range para atualizar os inputs
    jQuery('.nobossrange', container).trigger('input');
};

/**
 * Atualiza o campo input irmão do range de acordo com o valor selecionado no range
 * 
 * @param {Event} event Evento de input no range 
 */
nobossrange.updateTextField = function(event) {
    var value = jQuery(this).val();
    jQuery(this).siblings('.nobossrange--input').val(value);
};

/**
 * Atualiza o campo input irmão do range de acordo com o valor selecionado no range
 * 
 * @param {Event} event Evento de input no range 
 */
nobossrange.updateRange = function(event) {
    var value = jQuery(this).val();
    jQuery(this).siblings('.nobossrange').val(value);
    // Caso seja focusout, valida se o valor está no range
    if(event.type === 'focusout'){
        nobossrange.validateValue(jQuery(this));
    }
};

/**
 * Verifica se o elemento está dentro do range de min e max
 * @param {jQuery Object} el elemento input que será validado 
 */
nobossrange.validateValue = function(el) {
    var value = jQuery(el).val();
    // Caso seja maior que o min, define o valor como o min
    if (el.val() < el.attr('min')){
        el.val(el.attr('min'));
    }
    // Caso seja maior que o max, define o valor como o max
    if(el.val() > el.attr('max')){
        el.val(el.attr('max'));
    }
    // Dá um trigger para atualizar o range
    jQuery(el).trigger('input');    
};

jQuery(document).ready(function () {
    nobossrange.CONSTRUCTOR();
});
