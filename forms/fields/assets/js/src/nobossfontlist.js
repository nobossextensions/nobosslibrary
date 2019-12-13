var nobossfontlist = {};
/**
 * Função construtodas do js, primeira a ser executada ao terminar de carregar a página
 */
nobossfontlist.CONSTRUCTOR = function () {
    // forca change para atualizar showon caso precise
    jQuery('[data-id="font_list_field"]').trigger('change');

    // Evento personalizado que é chamado quando um novo container é incluido na página
    jQuery(document).on('new-container-add subform-row-add', function (event, container) {
        // Cria o trigger de change para o select de fonte
        jQuery(container).find('[data-id=font_list_field]').each(function () {
            if (jQuery(this).data('activated') !== true) {
                // Cria o evento de slide para cada campo
                nobossfontlist.addSlideEvent(this);

                var parent = jQuery(this).closest('.control-group').parent();
                var fullName = jQuery(this).data('full-name');
                
                // Cria o evento de change para o select da familia de fonte
                jQuery(this).on('change', nobossfontlist.fontSelectChange);
                // Cria o change para o select de escolha do estilo da fonte
                parent.find('[name=' + fullName + '_font_style]').on('change', function () {
                    nobossfontlist.updateHiddenFieldValue(fullName, parent);
                });
                parent.find('[name=' + fullName + '_font_external_url]').on('focusout', function () {
                    nobossfontlist.updateHiddenFieldValue(fullName, parent);
                });
                nobossfontlist.updateHiddenFieldValue(fullName, parent);
                jQuery(this).data('activated', true);
            }
        });
        jQuery(container).find('[data-id=font_list_field]').trigger('change');
    });
    // Dá um trigger no evento personalizado
    jQuery(document).trigger("new-container-add", jQuery('body'));
};
// Evento para a troca de fonte
nobossfontlist.fontSelectChange = function (event) {
    var that = this;
    var value = jQuery(that).val();

    var parent = jQuery(that).closest('.control-group').parent();
    // pega o nome verdadeiro do item
    var fullName = jQuery(that).data('full-name');
    // acessa o select dos etilos de fontes relacionado ao slect escolhido
    var fontSylesSelect = parent.find('[name=' + fullName + '_font_style]');
    // Acessa o input de url de fonte
    var externalLinkedInput = parent.find('[name=' + fullName + '_font_external_url]');
    if (value != 'external_linked' && value != 'inherit') {
        //Faz um ajax buscando o html paramontar a modal
        jQuery.ajax({
            url: "../administrator/index.php?option=com_nobossajax&library=noboss.forms.fields.nobossfontlist.nobossfontlist&method=loadFontStyles&format=raw",
            data: {
                fontName: value
            },
            type: "GET"
        }).done(function (response) {
            // Converte de json para js
            var selectOptions = JSON.parse(response);

            // Pega o valor previamente selecionado
            var selectedValue = fontSylesSelect.val();

            // Limpa as opções do select
            fontSylesSelect.empty();
            // Monta as options dentro de um foreach
            jQuery.each(selectOptions, function (key, value) {
                fontSylesSelect.append('<option value="' + value + '" ' + (selectedValue === value ? "selected" : "") + '>' + value + '</option>');
            });
            // Triggera o evento para atualizar o chosen
            fontSylesSelect.trigger('liszt:updated');
            // Chama o metodo qeu atuliza o value do campo hidden
            nobossfontlist.updateHiddenFieldValue(fullName, jQuery(that).closest('.control-group').parent());
        });

    } else {
        fontSylesSelect.val('Regular');
        // Chama o metodo que atuliza o value do campo hidden
        nobossfontlist.updateHiddenFieldValue(fullName, jQuery(that).closest('.control-group').parent());
    }
};


/**
 *  Pega o valor dos campos relacionados com o campo de fonte, cria um objeto e salva como json no campo hidden
 * 
 * @param {jQuery Object} fieldName Nome verdadeiro do campo, que estará no atributo data-full-name 
 * @param {jQuery Object} parentReference Referencia ao elemento pai das div com control-group
 */
nobossfontlist.updateHiddenFieldValue = function (fieldName, parentReference) {
    //pega os campos relacionados ao principal através do atributo data-full-name
    var selectFontList = jQuery(parentReference).find('[data-full-name=' + fieldName + '][name$=_select_font_list]').val();
    var fontExternalUrl = jQuery(parentReference).find('[data-full-name=' + fieldName + '][name$=_font_external_url]').val();
    var fontStyle = jQuery(parentReference).find('[data-full-name=' + fieldName + '][name$=_font_style]').val();

    /* jshint ignore:start */
    // Monta um objeto que sera convertido para json
    var value = {
        fontfamily: (selectFontList == null ? '' : selectFontList),
        externalLinked: (fontExternalUrl == null ? '' : fontExternalUrl),
        fontStyle: (fontStyle == null ? '' : fontStyle)
    };
    /* jshint ignore:end */

    // Seta o valor do campo hidden
    jQuery(parentReference).find('[data-full-name=' + fieldName + '][type=hidden]').val(JSON.stringify(value));
};

nobossfontlist.addSlideEvent = function (el) {
    // evento de troca de opcao na select de fonte
    jQuery(el).on('change', function () {
        // se o valor selecionado foi "herdar"
        if (jQuery(this).val() === "inherit") {
            // esconde os campos de url externa e estilo da fonte
            jQuery(this).closest('.control-group').nextAll(':lt(2)').slideUp();
            return;
        }

        // campo de url externa
        var externalLinkedInput = jQuery(this).closest('.control-group').next();
        // campo de estilo da fonte
        var fontSylesSelect = externalLinkedInput.next();

        // se foi url externa
        if (jQuery(this).val() === "external_linked") {
            // esconde o campo de estilo da fonte caso esteja sendo exibido
            if (fontSylesSelect.is(':visible')) {
                fontSylesSelect.slideUp();
            }
            // exibe o campo de url externa
            externalLinkedInput.slideDown();
        }
        // se foi selecionada uma fonte
        else {
            // esconde o campo de url externa caso esteja sendo exibido
            if (externalLinkedInput.is(':visible')) {
                externalLinkedInput.slideUp();
            }
            // exibe o campo de estilo da fonte
            fontSylesSelect.slideDown();
        }
    });
};

jQuery(document).ready(function (jQuery) {
    // Declara $ para evitar conflito
    $ = jQuery;
    // Scripts que são necessários estarem carregados na página
    var scripts = [];
    // Carrega os scripts que ainda não estão na página
    var queue = scripts.map(function (script) {
        if (script.slice(-2) === 'js' && jQuery("script[src*='" + script.slice(-30) + "']").length === 0) {
            return jQuery("head").append('<script type="text/javascript" src="' + script + '"></script>');
        } else if (script.slice(-3) === 'css' && jQuery("link[href*='" + script.slice(-30) + "']").length === 0) {
            return jQuery("head").append('<link rel="stylesheet" href="' + script + '"/>');
        }
    });

    // Todos scripts necessário estão carregados
    jQuery.when.apply(null, queue).done(function () {
        nobossfontlist.CONSTRUCTOR();
    });
});
