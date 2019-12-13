var nobossMultipleInputs = {};

nobossMultipleInputs.CONSTRUCTOR = function(){
    // Ativa os eventos para os campos adicionados na página
    nobossMultipleInputs.activateFieldsEvents();

    jQuery(document).on('subform-row-add', function(event, element){
        nobossMultipleInputs.activateFieldsEvents(element);
    });
};

nobossMultipleInputs.activateFieldsEvents = function(container){
    container = container === undefined ? container === jQuery('body') : container;

    // Percorre os campos que devem ter a máscara ativada e ativa uma por uma
    jQuery.each(jQuery('[data-id=nobossmultipleinputs--active-mask]', container), function (indexInArray, valueOfElement) {
        // verifica se o campo já foi ativado
        if(!jQuery(this).data('activated')) {
            // Cria um evento para o campo que é ativado quando o usuário clica ou digita algo
            jQuery(this).on("click keyup", function () {
                // Pega qual deve ser a máscara em um custom attr do campo
                var mask = jQuery(this).data("mask");
                if (mask != undefined){
                    var value = jQuery(this).val();
                    // Faz os tratamentos no valor do campo para adicionar a máscara
                    value = value.replace(/\D/g, '');
                    value = value.replace(/^0*/, "");
                    value = value === '' ? '0' : value;
                    // Adiciona a máscara no fim do campo e move o cursor para o final
                    var output = value + mask;
                    var cursorPosition = output.length - mask.length;
                    // Atualiza o valor do campo com o novo valor gerado
                    jQuery(this).val(output);
                    // Atualiza a posição do cursor
                    jQuery(this)[0].selectionStart = jQuery(this)[0].selectionEnd = cursorPosition;
                }
            });
            jQuery(this).data('activated', true);
        }
    });
    jQuery('[data-id=nobossmultipleinputs--active-mask]input[type!=checkbox]', container).trigger('click');
};

jQuery(document).ready(function (jQuery) {
    // Declara $ para evitar conflito
    $ = jQuery;
    // Scripts que são necessários estarem carregados na página
    var scripts = [];
    // Carrega os scripts que ainda não estão na página
    var queue = scripts.map(function (script) {
        if (jQuery("script[src$='" + script.slice(-35) + "']").length === 0) {
            return jQuery("head").append('<script type="text/javascript" src="' + script + '"></script>');
        }
    });
    // Todos scripts necessário estão carregados
    jQuery.when.apply(null, queue).done(function () {
        nobossMultipleInputs.CONSTRUCTOR();
    });
});
