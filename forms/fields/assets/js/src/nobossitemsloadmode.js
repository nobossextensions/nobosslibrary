jQuery(document).ready(function (jQuery) {
    // Declara $ para evitar conflito
    $ = jQuery;
    // Scripts que são necessários estarem carregados na página
    var scripts = ["../libraries/noboss/assets/plugins/js/min/jquery-confirm.min.js", "../libraries/noboss/assets/plugins/stylesheets/css/jquery-confirm.min.css"];
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
        nobossitemsloadmode.CONSTRUCTOR();
    });
});

var nobossitemsloadmode = {};

nobossitemsloadmode.CONSTRUCTOR = function () {
    // Configurações globais para a modal de aleta e confimação
	jconfirm.defaults = {
		draggable: false,
		useBootstrap: true,
		animateFromElement: false,
		columnClass: 'modal-confirm-responsive'
    };

    // Pega o primeiro form da pagina, qeu é o form principal
    var form = jQuery('body').find('form')[0];
    
    if(jQuery('#itemsloadmode').find('option').length > 1){
        // Classifica os options da modal de acordo com o plano do usuário
        nobossitemsloadmode.classifyPlans();
    }
    // Classifica os options da modal de acordo com o tema escolhido
    nobossitemsloadmode.classifyOptionsByThemes();

    // Evento ativado na troca do tema
    jQuery('body').on('change', '[data-id="theme-modal-input"]', nobossitemsloadmode.classifyOptionsByThemes);

    // pega a opcao escolhida ao carregar a pagina
    var selectedOpt = jQuery('#itemsloadmode').find('option[value='+jQuery('#itemsloadmode').val()+']');
    // e valida se esta disponivel
    nobossitemsloadmode.checkOptionAvailability(selectedOpt);

    // evento de troca do modo de carregamento
    jQuery('#itemsloadmode').on('change', function(){
        // pega a opcao escolhida
        var option = jQuery(this).find('option[value='+jQuery(this).val()+']');
        // valida se esta disponivel
        nobossitemsloadmode.checkOptionAvailability(option);
    });

    if (jQuery('#itemsloadmode').find('option').length <= 1){
        jQuery('#itemsloadmode').closest('.control-group').hide();
        jQuery("#itemsloadmode").find('option').first().attr('selected', true);
    }

};

/**
 * Valida se a opcao selecionada esta disponivel no plano do usuario
 *
 * @param {jQuery Object} option Opcao selecionada
 */
nobossitemsloadmode.checkOptionAvailability = function(option){
    // se o data-available tiver um valor falso, eh pq nao esta disponivel
    if (option.data('available') === false){
        // desabilita os botoes de save do modulo
        nobossitemsloadmode.disableSave(true);
        // exibe o alert de opcao nao disponivel
        jQuery('#itemsloadmode_alert_msg').slideDown();
    } else {
        // entra aqui caso esteja habilitada
        // ativa os botoes de save
        nobossitemsloadmode.disableSave(false);
        // esconde o alert de opcao nao disponivel
        jQuery('#itemsloadmode_alert_msg').slideUp();
    }
};

/**
 * Percorre os options do select de modo de carregamento e valida se o modo existe para o tema atual
 */
nobossitemsloadmode.classifyOptionsByThemes = function(event){
    // pega o tema escolhido
    var themeValue = jQuery("[data-id=theme-modal-input]").val();
    // se tiver vazio eh pq nao foi escolhido um tema ainda
    if (!themeValue) {
        // entao sai da funcao
        return;
    }

    //Percorre cada option validando o mesmo
    jQuery('#itemsloadmode').find('option').each(function (index, element) {
        var available;
        // Caso o item tenha tema 'dev' ou 'custom' ou tenha o value vazio, considera disponível
        if (jQuery(this).data('themes') === 'custom' || jQuery(this).val().length === 0) {
            available = true;
        } else {
            // Pega os temas no attr da option
            var themesOptions = jQuery(this).data('themes').toString().split(',');
            available = jQuery.inArray(themeValue, themesOptions) !== -1;
        }

        // Caso não esteja disponível, desabilita o item
        if (!available) {
            jQuery(this).attr('disabled', true);
            jQuery(this).html(jQuery(this).html() + Joomla.JText._("LIB_NOBOSS_FIELD_NOBOSSITEMSLOADMODE_OPT_UNAVAILABLE"));
        } else {
            jQuery(this).attr('disabled', false);
        }
        // jQuery(this).attr('selected', '');
    });

    // verifica se a opcao selecionada nao esta disponivel no tema    
    if(jQuery('#itemsloadmode').find('option[selected="selected"]').first().attr('disabled') === "disabled"){
        var selected = false;
        // Verifica os itens bloqueados e seleciona o primeiro liberado
        jQuery('#itemsloadmode').find('option').map(function(){
            if((jQuery(this).attr('disabled') === undefined || jQuery(this).attr('disabled').length === 0) && !selected){
                jQuery(this).attr('selected', true);
                selected = true;
            }
        });
        // entra aqui se nenhuma opcao estiver disponivel
        if(!selected){
            var noOptsNote = Joomla.JText._("LIB_NOBOSS_FIELD_NOBOSSITEMSLOADMODE_NO_OPTIONS_ALERT").replace("%s", Joomla.JText._("NOBOSS_EXTENSIONS_URL_SITE_CONTACT"));
            // esconde o select e exibe o alert de nenhum modo de carregamento disponivel
            jQuery('#itemsloadmode').closest('.control-group').hide().after(noOptsNote);
            
        }
    }
    

    // atualiza o chosen
    jQuery('#itemsloadmode').trigger('liszt:updated');
    jQuery('#itemsloadmode').trigger('change');

    // apos ter gerado os dados, esconde o campo de modo de carregamento caso tenha apenas uma option
    if (jQuery('#itemsloadmode').find('option').length <= 1) {
        jQuery('#itemsloadmode').closest('.control-group').hide();
        jQuery("#itemsloadmode").find('option').first().attr('selected', true);
    }

};

/**
 * Percorre as options do campo de modo de carregamento e classifica como PRO ou não
 */
nobossitemsloadmode.classifyPlans = function () {
    // pega o tema escolhido
    var themeValue = jQuery("[data-id=theme-modal-input]").val();

    
        
    // Percorre o select dos temas, validando cada item para saber se está disponível no plano atual
    jQuery('#itemsloadmode').find('option').each(function () {
        
        var available;
        // Caso o item tenha plano 'dev' ou 'custom' ou tenha o value vazio, considera disponível
        if (jQuery(this).data('plan') === 'custom' || jQuery(this).val().length === 0) {
            available = true;
        } else {
            // Pega os planos no attr da option
            var plansOfOption = jQuery(this).data('plan').toString().split(',');
            available = jQuery.inArray(jQuery('#extension_plan').val(), plansOfOption) !== -1;
        }
        
        // Salva a informação se o tema está ou não disponível para o plano atual da extensão
        jQuery(this).data('available', available);
        // Caso não esteja disponível, concatena o '- PRO' no final do texto
        if (!available) {
                    
            jQuery(this).html(jQuery(this).html() + " - PRO"); 
            
            // var subformSearch = jQuery(this).val() === 'manual' ? '[id*="'+themeValue+'-lbl"]' : '[id*="'+jQuery(this).val()+'"][id$="-lbl"]';
            // procura pelo subform 'principal' de itens de acordo com o loadmode escolhido
            var subformSearch = '[id*="' + jQuery(this).data('subform').replace('##model##', themeValue) + '-lbl"]';
            // Remove o subform dos itens que são PRO
            jQuery(this).closest('.control-group').parent().find(subformSearch).closest('.control-group').remove();

            // remove os campos com showon que aponta para o modo de carregamento escolhido
            jQuery('div.control-group[data-showon*="\\"values\\":[\\"'+ jQuery(this).val() +'\\"]"]').each(function(){
                jQuery(this).remove();
            });
            
        }
    });
};

/**
 * Desabilita os botoes de save e adiciona um evento de click que abre uma modal de alerta
 *
 * @param disable Flag para saber se os botoes devem ser desativos ou ativados novamente
 */
nobossitemsloadmode.disableSave = function(disable){
    // pega os botoes de save do joomla
    var btns = jQuery('#toolbar').find("[id*='-save'], [id='toolbar-apply']").find('button');
    // se a flag for verdadeira
    if(disable){
        // percorre cada botao
        btns.each(function() {
            // remove o onclick inline
            jQuery(this).attr("onclick", null);
            // remove evento de click adicionado anteriormente para impedir que a modal abra mais de uma vez
            jQuery(this).off('click');
            // adiciona evento de click que abre a modal de alerta
            jQuery(this).on("click", nobossitemsloadmode.showAlertModal);
        });
    }else{
        // se nao for para desabilitar
        // percorre cada botao
        btns.each(function(){
            // tira o evento de click que abre a modal
            jQuery(this).off('click');
            // pega o id da div pai, que informa qual a acao do botao
            var parentId = jQuery(this).parent('div').attr('id');
            // adiciona onclick com o respectivo evento
            if (parentId == "toolbar-apply") {
              jQuery(this).attr("onclick", "Joomla.submitbutton('module.apply');");
            } else if (parentId == "toolbar-save") {
              jQuery(this).attr("onclick", "Joomla.submitbutton('module.save');");
            } else if (parentId == "toolbar-save-new") {
              jQuery(this).attr("onclick", "Joomla.submitbutton('module.save2new');");
            } else if (parentId == "toolbar-save-copy") {
              jQuery(this).attr("onclick", "Joomla.submitbutton('module.save2copy');");
            }
        });
    }

};

/**
 * Exibe uma modal de alerta informando que o modo de carregamento escolhido nao esta incluso no plano
 *
 */
nobossitemsloadmode.showAlertModal = function(){
    jQuery.alert({
        title: Joomla.JText._("LIB_NOBOSS_FIELD_NOBOSSITEMSLOADMODE_NOT_INCLUDED_ALERT_TITLE"),
        content: Joomla.JText._("LIB_NOBOSS_FIELD_NOBOSSITEMSLOADMODE_NOT_INCLUDED_LABEL").replace("%s", Joomla.JText._("NOBOSS_EXTENSIONS_URL_SITE_CONTACT")),
        type: 'red',
        closeIcon: 'cancel',
        escapeKey: 'cancel',
        buttons: {
            ok:{
                action: function(){
                    // forca um click para levar o usuario a aba de itens
                    jQuery('a[href="#attrib-items"]').click();
                }
            }
        }
    });
};
