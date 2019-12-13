var nobossmodal = {};

// Cria uma variável global para armazenar os css e js que já foram requisitados na modal
nobossmodal.loadedJsAndCss = [];


/**
 * Função construtodas do js, primeira a ser executada ao terminar de carregar a página
 */
nobossmodal.CONSTRUCTOR = function() {
    // Configurações globais para a modal de aleta e confimação
    jconfirm.defaults = {
        draggable: false,
        useBootstrap: true,
        animateFromElement: false,
        columnClass: 'modal-confirm-responsive'
    };
    
    // variaveis vindas do PHP
    nobossmodal.infos = Joomla.getOptions("nobossmodal");

    // remove listeners de focusout e change para impedir eventos do html5fallback
    jQuery('#module-form, #form-group').off('focusout change');
    
    //*****************************
    // Listeners de eventos
    //*****************************

    /**
     * Evento de clique no botão que abre a modal
     */	 
    jQuery('body').on('click', '[data-id=noboss-modal]',nobossmodal.loadModal);

    /**
     * Intercepta o evento nos botões do topo (salvar, salvar e fechar, fechar) e gera values default para as modais vazias
     */
    jQuery('#module-form, #form-group').on('submit', nobossmodal.interceptFormSubmit);

    // Cria o evento que detecta cliques nos botões da modal
    jQuery('body').on("click", "[data-id=modal] [data-id=button-confirm]", nobossmodal.confirmModal);
    jQuery('body').on("click", "[data-id=modal] [data-id=button-cancel]", nobossmodal.cancelModal);
    jQuery('body').on("click", "[data-id=modal] [data-id=button-reset]", nobossmodal.resetModal);
    jQuery('body').on("click", "[data-id=modal] [data-id=modal-nav-tabs] [data-id=modal-tab]", nobossmodal.tabNavigation);
};

/**
 * Interpta os eventos de submit do fomulário 
 * principal e realiza ações e depois refaz o submit
 * 
 * @param {Event} event 
 */
nobossmodal.interceptFormSubmit = function(event){
   

};

/**
 * 	Função acionada no evento de clique em um botão de carregar a modal. Valida se a modal já foi carregada, somente abrindo a mesma em tal caso
 * 
 * @param {Event} event Evento de clique 
 */
nobossmodal.loadModal = function (event) {
    event.preventDefault();
    var that = jQuery(this);

    if(jQuery(that).attr("disabled")){
        return false;
    }

    // Pega o elemento modal
    var modalElem = jQuery(that).siblings('[data-id=modal]');

    //Verifica se a modal já foi carregada para não carregar novamente
    if (modalElem.length === 0) {
        //adiciona loader do lado do botao clicado
        nobossmodal.addLoader(jQuery(that));

        //Pega os dados em formato json do campo hidden
        var jsonData = jQuery(that).siblings("[data-id=noboss-modal-input-hidden]").val();
        //Pega o nome da modal que já foi setado pelo php
        var modalName = jQuery(that).siblings("[data-id=noboss-modal-input-hidden]").data('modal-name');
        // Pega o caminho para o xml do form que foi setado em um data attribute
        var xmlPath = jQuery(that).siblings('[data-id=noboss-modal-input-hidden]').data('formsource');

        // Faz o ajax e insere a modal na página
        nobossmodal.ajaxAndInsertModal(that, jsonData, modalName, xmlPath).done(function(response){
            nobossmodal.openModal(jQuery(that).siblings('[data-id=modal]'));
        });
    } else {
        nobossmodal.openModal(modalElem);
    }
};

/**
 * 	Função para adicionar o loader
 * 
 * @param {jQuery Object} el elemento que o loader deve ficar próximo
 */
nobossmodal.addLoader = function(el){
    //desabilita os botoes de abrir modal
    nobossmodal.disableButtons(true);
    var loaderEl = jQuery('<div class="loader loader--side"></div>');
    //adiciona o elemento ao lado do botao
    jQuery(el).after(loaderEl);
};

/**
 * 	Função para remover o loader
 * 
 */
nobossmodal.removeLoader = function(){
    jQuery(".controls").children(".loader.loader--side").remove();
    nobossmodal.disableButtons(false);
};

/**
 * 	Função usada para desabilitar os botões de abrir modal 
 *  @param {Boolean} option false desabilita, true desabilita
 */
nobossmodal.disableButtons = function(option){
    jQuery("[data-id=noboss-modal]:visible").attr('disabled', option);
};

/**
 * 
 * @param {jQuery Object} that input hidden qeu servirá de referencia para inserir a modal 
 * @param {String} jsonData dados json de value dos campos da modal já existentes
 * @param {String} modalName alias(nome) da modal para busca no php
 * @param {String} xmlPath caminho do arquivo xml que representa os campos da modal
 */
nobossmodal.ajaxAndInsertModal = function (that, jsonData, modalName, xmlPath){
    // Faz um ajax buscando o html paramontar a modal
    
    return jQuery.ajax({
        url: "../administrator/index.php?option=com_nobossajax&library=noboss.forms.fields.nobossmodal.nobossmodal&method=loadModal&format=raw",
        data: {
            data: jsonData,
            modalName: modalName,
            xmlPath: xmlPath
        },
        type: "POST"
    }).done(function (response) {
        //Insere a modal na página

        //remove o loader
        nobossmodal.removeLoader();
        var modalElem = jQuery(response).insertAfter(that);

        // após inserir o html, carrega o js e css
        nobossmodal.verifyAndLoadJsCss(modalElem);
    }).fail(function () {
        jQuery.alert({
            title: translationConstants.nobossmodal.LIB_NOBOSS_FIELD_NOBOSSMODAL_MODAL_CONNECTION_ERROR_TITLE,
            content: translationConstants.nobossmodal.LIB_NOBOSS_FIELD_NOBOSSMODAL_MODAL_CONNECTION_ERROR_CONTENT,
            buttons: {
                ok: function () {
                    nobossmodal.removeLoader();
                }
            }
        });
        
    });
};

/**
 * Verifica a variavel global que mantém uma lista dos arquivos para serem carregados e verifica na página
 * 
 * @param {jQuery Object} modalElem Objeto jqeury do elemento modal
 */
nobossmodal.verifyAndLoadJsCss = function(modalElem){
    var scripts = nobossmodal.loadedJsAndCss;

    // Carrega os scripts que ainda não estão na página
    var scriptsToLoad = scripts.map(function (script) {
        if (script.indexOf('html5fallback') === -1 &&(script.slice(-2) === 'js' && jQuery("script[src*='" + script.slice(-30) + "']").length === 0)||(script.slice(-3) === 'css' && jQuery("link[href*='" + script.slice(-30) + "']").length === 0)) {
            return script;
        }
    });
    
    var minicolorsItems;
    // Tratamento especial para o minicolors
    if (scriptsToLoad.join('|').indexOf('minicolors.min.js') > 0) {
        minicolorsItems = modalElem.find('.minicolors');
        minicolorsItems.removeClass('minicolors');
    }
    
    // Carrega os scripts que ainda não estão na página
    var queue = scriptsToLoad.map(function (script) {
        if (script){
            if (script.slice(-2) === 'js' && jQuery("script[src*='" + script.slice(-30) + "']").length === 0) {
                return jQuery("head").append('<script type="text/javascript" src="' + script + '"></script>');
            } else if (script.slice(-3) === 'css' && jQuery("link[href*='" + script.slice(-30) + "']").length === 0) {
                return jQuery("head").append('<link rel="stylesheet" href="' + script + '"/>');
            }
        }
    });
    
    // Cria uma stringona para validar a existencia de algum arquivo mais facilmente
    scriptsToLoad = scriptsToLoad.join('|');
    
    // Todos scripts necessário estão carregados
    jQuery.when.apply(null, queue).done(function () {
        //Ativa o chosen para selects
        jQuery(modalElem).find('form select').chosen();
    
        //ativa um chosen especial para os select do tipo multiple
        jQuery(modalElem).find('form select[multiple=multiple]').chosen().change(function () {
            jQuery(this).chosen("destroy");
            jQuery(this).chosen("liszt:updated");
        });

        // Readiciona a classe minicolors
        if (minicolorsItems){
            minicolorsItems.addClass('minicolors');
        }

        // Se for versao maior que 3.8.0 ativa o minicolors manualmente
        if(nobossmodal.infos.higherJVersion){
            modalElem.find('.minicolors').each(function() {
                jQuery(this).minicolors({
                    control: jQuery(this).attr('data-control') || 'hue',
                    format: jQuery(this).attr('data-validate') === 'color' ? 'hex' : (jQuery(this).attr('data-format') === 'rgba' ? 'rgb' : jQuery(this).attr('data-format')) || 'hex',
                    keywords: jQuery(this).attr('data-keywords') || '',
                    opacity: jQuery(this).attr('data-format') === 'rgba' ? true : false || false,
                    position: jQuery(this).attr('data-position') || 'default',
                    theme: 'bootstrap'
                });
            });
        }

        // Verifica se foi carregado o script do caledário1
        if (scriptsToLoad.indexOf('calendar') > 0){
            // Gera um evento falseo que o DOM terminou de carregar
            var DOMContentLoaded_event = document.createEvent("Event");
            DOMContentLoaded_event.initEvent("DOMContentLoaded", true, true);
            window.document.dispatchEvent(DOMContentLoaded_event);
        }

        // Ativa a função que arruma os campos que quebram pos causa do js
        nobossmodal.fixScripts(modalElem);
    });
};



/**
 * Recebe uma referência da modal e ativa os js necessário para o funcionamento de alguns campos
 * 
 * @param {jQuery Object} modalElem Objeto jQuery do elemento modal 
 */
nobossmodal.fixScripts = function (modalElem) {
    
    // fix media field
    modalElem.find('a[onclick*="jInsertFieldValue"]').each(function () {
        var jQueryel = jQuery(this),
            inputId = jQueryel.siblings('input[type="text"]').attr('id'),
            jQueryselect = jQueryel.prev(),
            oldHref = jQueryselect.attr('href');
        // update the clear button
        jQueryel.attr('onclick', "jInsertFieldValue('', '" + inputId + "');return false;");
        // update select button
        jQueryselect.attr('href', oldHref.replace(/&fieldid=(.+)&/, '&fieldid=' + inputId + '&'));
    });

    // bootstrap based Media field
    if (jQuery.fn.fieldMedia) {
        modalElem.find('.field-media-wrapper').fieldMedia();
    }

    // bootstrap tooltips
    if (jQuery.fn.popover) {
        modalElem.find('.hasPopover').popover({ trigger: 'hover focus' });
    }

    // bootstrap based User field
    if (jQuery.fn.fieldUser) {
        modalElem.find('.field-user-wrapper').fieldUser();
    }

    // Caso tenha o calendário na página
    if (window.Calendar){
        elements = modalElem.find(".field-calendar");
        // Percorre os calendários da modalElem e ativa
        elements.each(function(){
            JoomlaCalendar.init(this);
        });
    }

    nobossmodal.activateYesno(modalElem);

    modalElem.find('.fieldset').each(function(){
        jQuery(document).trigger("subform-row-add", this);
        jQuery(document).trigger("new-container-add", this);
        // verifica se a versao eh menor que 3.7.3 para fazer tratamento especial aos campos color
        if(nobossmodal.infos.lowerJVersion){
            jQuery(this).find('.minicolors').each(function() {
                // guarda o valor setado
                var value = jQuery(this).val().replace('#','');
                // monta o campo de minicolor
                jQuery(this).minicolors({
                    control: jQuery(this).attr('data-control') || 'hue',
                    format: jQuery(this).attr('data-validate') === 'color' ? 'hex' : (jQuery(this).attr('data-format') === 'rgba' ? 'rgb' : jQuery(this).attr('data-format')) || 'hex',
                    keywords: jQuery(this).attr('data-keywords') || '',
                    opacity: jQuery(this).attr('data-format') === 'rgba' ? true : false || false,
                    position: jQuery(this).attr('data-position') || 'default',
                    theme: 'bootstrap'
                });
                // seta o valor que estava setado previamente
                jQuery(this).minicolors('value', value);
            });
        }
    });
};


/**
 * Recebe um referência da modal e abrea a mesma adicionando os eventos
 * 
 * @param {jQuery Object} modalElem objeto jquery representando a modal
 */
nobossmodal.openModal = function(modalElem){
    // percorre todas as abas
    modalElem.find('[name=nb-modal-form]').find('[data-tab-id]').each(function(){
        // pega a vativel correspondenta a aba atual
        var thisTab = jQuery(this).closest('.nb-modal-content').find('[data-id=modal-nav-tabs]').find('[href=#' + jQuery(this).data('tab-id') + ']');
        // valida as abas que tem o nbshoon para saber se devem ser escondidas
        if(jQuery(this).data('nbshowon')){
            // Separa os itens pelo ; e o primeiro item é o contexto e o segundo os campos:valores
            var context = jQuery(this).data('nbshowon').split(';');
            // Separa o campo dos valores
            var fieldInfo = context[context.length-1].split(':');
            // Separa os valores por vírgula
            var values = fieldInfo[fieldInfo.length - 1].split(',');
            // Variavel para identificar a poeração a ser feita
            var equals = true;
            if (fieldInfo[0].slice(-1) === '!'){
                fieldInfo[0] = fieldInfo[0].slice(0, -1);
                equals = false;
            }

            // verifica se deve buscar campos no contexto externo
            if(context[0] == 'outside'){
                // Pega um input filho do controls que tem o nome do campo de subform
                nameContext = jQuery(this).closest('.control-group').closest('.controls').children('input[type=hidden][name^=jform]').attr('name');
                // Dá replace pelo nome do campo
                fieldName = nameContext.replace(/\[[^\[\]]+\](?=[^\[\]]*$)/, '['+fieldInfo[0]+']');
            
                // Caso o campo a ser observado esteja dentro da própria modal
            } else if (context[0] == 'inner') {
                // Pega o nome puro da modal
                modalRawName = modalElem.data('modal-name');
                fieldName = modalRawName + '\\[' + fieldInfo[0] +'\\]';
                var field = jQuery('[name=' + fieldName + ']');

                // Verifica se esse campo já tem um evento vinculado a ele atravéz de um atributo
                if (!field.data('has-nbshowon-event-'+jQuery(this).data('tab-id'))){
                    // Cria um evento que observa a mudança no campo
                    jQuery(field).on('change', function (event) {
                        // Manda a operação, valor atual, array de valores e a aba para ver se esconde ou não
                        nobossmodal.validateAndChangeTab(equals, jQuery(this).val(), values, thisTab);
                    });

                    // pega o value do campo
                    var innerFieldValue = field.val();
                    // Tratamento especial para o caso do radio
                    if (field.attr('type') == 'radio') {
                        innerFieldValue = field.filter(':checked').val();
                    }
                    nobossmodal.validateAndChangeTab(equals, innerFieldValue, values, thisTab);

                    // cria um atributo para avisar que essa campo já tem um evento nbshowon vinculado a ele
                    field.each(function(){
                        field.data('has-nbshowon-event-'+jQuery(this).data('tab-id'), true);
                    });
                }
                // equivalente a um 'continue' pulando para p´roxima iteração
                return true;
            
            // Caso o campo a ser olhado esteja no mesmo contexto que a modal
            } else {
                // Pega o nome puro da modal
                modalRawName = modalElem.data('modal-name');
                // procura o campo hidden da modal e substitui o ultimo bloco pelo nome do campo
                fieldName = modalElem.siblings('[type=hidden]').attr('name').replace(modalRawName, fieldInfo[0]);
            }
            // Formata o texto do campo para o jquery reconhecer
            var formattedName = fieldName.replace(/\[/g, '\\\[').replace(/\]/g, '\\\]');

            // pega o value do campo
            var value = jQuery("[name='" + formattedName + "']").val();
            if (jQuery("[name='" + formattedName + "']").attr('type') == 'radio') {
                value = jQuery("[name='" + formattedName + "']:checked").val();
            }
            // Manda a operação, valor atual, array de valores e a aba para ver se esconde ou não
            nobossmodal.validateAndChangeTab(equals, value, values, thisTab);

            // percorre as abas da modal
            jQuery(this).closest('.nb-modal-content').find('[data-id=modal-nav-tabs]').find('li a').each(function(){
                // verifica se a aba está sendo exibida
                if(jQuery(this).css('display') !== 'none'){
                    // se estiver, trigera um clique
                    jQuery(this).trigger('click');
                    // Sai da iteração
                    return false;
                }
            });
        }
    });

    // Põe a classe necessária para deixar o fundo sem eventos
    nobossmodal.toogleBodyBackgroundFade(true);	
    //Pega a div mais externa da modal e retira a classe hidden
    modalElem.removeClass('hidden');
};

/**
 * função de validação usada pelo nbshowon, recebe os parametros,
 * valida e esconde ou exibe uma aba detro de uma modal de acordo com um campo
 * 
 * @param {Boolean} equals true faz operação de igual e false faz operação de diferente
 * @param {mixed} value Valor atual do campo
 * @param {Array} values Array com os valores válidos, usados para comparar
 * @param {jQuery Object} tab A aba que será escondida/exibida quando a comparação tiver sucesso
 * 
 */
nobossmodal.validateAndChangeTab = function(equals, value, values, tab) {
    // Valida se o valor do campo é o valor que deve ser para escoder
    if ((equals && jQuery.inArray(value, values) > -1) || (!equals && jQuery.inArray(value, values) == -1)) {
        // Exibe a aba
        tab.show();
    } else {
        // Esconde a aba
        tab.hide();
    }
};

// Evento para navegação entre as abas da modal
/**
 * Ativado no clique de troca de abas na modal, altera a aba selecionada
 * e troca o conteúdo da modal para o consteúdo da aba escolhida
 * 
 * @param {Event} event 
 */
nobossmodal.tabNavigation = function(event) {
    event.preventDefault();

    var modalElem = jQuery(this).closest('[data-id="modal"]');
    var tabToOpen = jQuery(this).attr('href').replace('#', '');

    // Adiciona a classe active à própria aba
    jQuery(this).siblings('[data-id=modal-tab]').removeClass('active');

    modalElem.find('[data-id=nb-modal-form] [data-tab-id]').removeClass('active');
    modalElem.find('[data-id=nb-modal-form]').find('[data-tab-id='+tabToOpen+']').addClass('active');
};

/**
 *  REcebe a modal e ativa os eventos e ativa os botões Radio yesno
 * 
 * @param {JQuery Object} modalElem Elemento da modal noboss
 */
nobossmodal.activateYesno = function(modalElem){
    // Turn radios into btn-group
    modalElem.find('.radio.btn-group label').addClass('btn');

    // Prevent clicks on disabled fields
    modalElem.find('fieldset.btn-group').each(function () {
        if (jQuery(this).prop('disabled')) {
            jQuery(this).css('pointer-events', 'none').off('click');
            jQuery(this).find('.btn').addClass('disabled');
        }
    });

    // Add btn-* styling to checked fields according to their values
    modalElem.find('.btn-group label:not(.active)').click(function () {
        var label = jQuery(this);
        var input = jQuery('#' + label.attr('for'));

        if (!input.prop('checked')) {
            label.closest('.btn-group').find('label').removeClass('active btn-success btn-danger btn-primary');
            if (input.val() === '') {
                label.addClass('active btn-primary');
            } else if (input.val() === '0') {
                label.addClass('active btn-danger');
            } else {
                label.addClass('active btn-success');
            }
            input.prop('checked', true);
            input.trigger('change');
        }
    });

    // Similar
    modalElem.find('.btn-group input[checked="checked"]').each(function () {
        var input = jQuery(this);
        if (input.val() === '') {
            input.parent().find('label[for="' + input.attr('id') + '"]').addClass('active btn-primary');
        } else if (input.val() === '0') {
            input.parent().find('label[for="' + input.attr('id') + '"]').addClass('active btn-danger');
        } else {
            input.parent().find('label[for="' + input.attr('id') + '"]').addClass('active btn-success');
        }
    });

};

/**
 * Função de clique para confirmar a modal, deixando os dados salvos
 * 
 * @param {Event} event 
 */
nobossmodal.confirmModal = function(event){
    event.preventDefault();

    var modalElem = jQuery(this).closest('[data-id="modal"]');

    // Gera o json com os valores
    var jsonValue = nobossmodal.generateJsonValue(modalElem);
    // Atualiza o valor no campo hidden
    jQuery(modalElem).siblings('[data-id="noboss-modal-input-hidden"]').val(jsonValue);

    // esconde a modal
    jQuery(modalElem).addClass('hidden');
    // Remove o fundo preto
    nobossmodal.toogleBodyBackgroundFade(false);
};

/**
 * Função de clique cancelar as edições feitas na modal. A função não altera o input hidden e deleta o elemento modal
 * 
 * @param {Event} event 
 */
nobossmodal.cancelModal = function(event){
    event.preventDefault();
    // Pega o elemento da modal
    var modalElem = jQuery(this).closest('[data-id="modal"]');

    var newValue = nobossmodal.generateJsonValue(modalElem);
    var oldValue = jQuery(modalElem).siblings("[data-id=noboss-modal-input-hidden]").val();

    // Verifica se algo foi alterado
    if (newValue !== oldValue){
        // Cria uma modal para o usuário confirmar a ação de troca de tema
        jQuery.confirm({
            title: translationConstants.nobossmodal.LIB_NOBOSS_FIELD_NOBOSSMODAL_MODAL_CANCEL_LABEL,
            content: translationConstants.nobossmodal.LIB_NOBOSS_FIELD_NOBOSSMODAL_MODAL_CANCEL_DESC,
            type: 'blue',
            closeIcon: 'cancel',
            escapeKey: 'cancel',
            buttons: {
                cancel: {
                    keys: ['esc'],
                    text: translationConstants.nobossmodal.LIB_NOBOSS_FIELD_NOBOSSMODAL_MODAL_CONFIRM_CANCEL_BUTTON
                },
                confirm: {
                    text: translationConstants.nobossmodal.LIB_NOBOSS_FIELD_NOBOSSMODAL_MODAL_CONFIRM_CONFIRM_BUTTON,
                    btnClass: 'btn-blue',
                    keys: ['enter'],
                    action: function () {
                        // Remove a tag script
                        jQuery(modalElem).siblings('script').remove();
                        // esconde a modal
                        jQuery(modalElem).remove();
                        // Remove o fundo preto
                        nobossmodal.toogleBodyBackgroundFade(false);
                        
                    }
                }
                
            }
        });
    }else{
        // Remove a tag script
        jQuery(modalElem).siblings('script').remove();
        // esconde a modal
        jQuery(modalElem).remove();
        // Remove o fundo preto
        nobossmodal.toogleBodyBackgroundFade(false);
        
    }
};

/**
 * Função de clique para resetar os valores da modal. A função reseta o input hidden e deleta o elemento modal
 * 
 * @param {Event} event 
 */
nobossmodal.resetModal = function(event){
    event.preventDefault();
    // Pega o elemento da modal
    var modalElem = jQuery(this).closest('[data-id="modal"]');

    // Cria uma modal para o usuário confirmar a ação de reset da modal
    jQuery.confirm({
        title: translationConstants.nobossmodal.LIB_NOBOSS_FIELD_NOBOSSMODAL_MODAL_RESET_LABEL,
        content: translationConstants.nobossmodal.LIB_NOBOSS_FIELD_NOBOSSMODAL_MODAL_RESET_DESC,
        type: 'blue',
        closeIcon: 'cancel',
        escapeKey: 'cancel',
        buttons: {
            cancel: {
                keys: ['esc'],
                text: translationConstants.nobossmodal.LIB_NOBOSS_FIELD_NOBOSSMODAL_MODAL_CONFIRM_CANCEL_BUTTON
            },
            confirm: {
                text: translationConstants.nobossmodal.LIB_NOBOSS_FIELD_NOBOSSMODAL_MODAL_CONFIRM_CONFIRM_BUTTON,
                btnClass: 'btn-blue',
                keys: ['enter'],
                action: function () {
                    // Mantém o valor do campo hidden para recolo
                    var oldValue = jQuery(modalElem).siblings("[data-id=noboss-modal-input-hidden]").val();

                    // Seta o valor do input hidden como o json criado
                    jQuery(modalElem).siblings("[data-id=noboss-modal-input-hidden]").val('');
                    // Remove a tag script
                    jQuery(modalElem).siblings('script').remove();
                    
                    //Pega o nome da modal que já foi setado pelo php
                    var modalName = jQuery(modalElem).siblings("[data-id=noboss-modal-input-hidden]").data('modal-name');
                    // Pega o caminho para o xml do form que foi setado em um data attribute
                    var xmlPath = jQuery(modalElem).siblings('[data-id=noboss-modal-input-hidden]').data('formsource');

                    jQuery(modalElem).find('.nb-modal-content').append('<div class="loader-fade"></div>');
                    jQuery(modalElem).find('.nb-modal-content').append('<div class="loader loader--fullpage"></div>');
                    // Faz o ajax e insere a modal na página
                    nobossmodal.ajaxAndInsertModal(jQuery(modalElem), '', modalName, xmlPath).done(function (response) {
                        // Salva a instancia da nova modal resetada
                        var newModal = jQuery(modalElem).siblings('[data-id="modal"]');
                        
                        // Repoe o valor do campo hidden
                        jQuery(newModal).siblings('[data-id=noboss-modal-input-hidden]').val(oldValue);
                        
                        setTimeout(function(){
                            // Deleta a modal antiga
                            jQuery(modalElem).remove();
                            // Abre a nova modal com os valores default
                            nobossmodal.openModal(newModal);
                        }, 500);
                    });
                }
            }

        }
    });
};

/**
 * Reecebe o objeto modal como parametro e gera
 * so json que é salvo no value do input hidden
 * 
 * @param {JQuery Object} modalElem 
 */
nobossmodal.generateJsonValue = function(modalElem) {
    var form = jQuery(modalElem).find("form");
    var modalName = modalElem.data('modal-name');

    var formData = form.serializeArray();
    //Organiza o array como um ojeto
    var data = {};
    jQuery(formData).each(function (index, obj) {

        var regex = new RegExp(modalName + "\\[(.*?)\\]");
        obj.name = obj.name.split(regex).join('');

        //Verifica se a string contem []
        if (/.*\[.*\].*/.test(obj.name)) {
            //Sefor um array, pegar o nome antes de aparecer um "["
            var arrayName = obj.name.split("[")[0];
            //Pega o valor entre '[]'
            var arrayNameKey = obj.name.split(/\[(.*?)\]/)[1];

            //Ele precisa trabalhar de maneiras diferentes para quando os cmapos 
            //tem ou não chave, pois não dá pra usar array porque as 
            //chaves de numero grande travam o navegador

            //Verifica se o objeto já existe, caso não, cria um
            if (data[arrayName] === undefined) {
                //Verifica se o objeto já tem uma key ou se é vazia. Ex.: []
                if (arrayNameKey) {
                    //Cria um objeto
                    data[arrayName] = {};
                } else {
                    //Cria um array
                    data[arrayName] = [];
                }
            }

            //Se a chave não for vazia, vai trabalhar como objeto
            if (arrayNameKey) {
                //Verifica se o objeto ja existe, caso sim, quer dizer que é um array
                if (data[arrayName][arrayNameKey] !== undefined) {
                    //Varifica se é um array, porque caso não seja, cria um e põe no lugar
                    if (Array.isArray(data[arrayName][arrayNameKey])) {
                        //Caso já seja um array, só insere
                        data[arrayName][arrayNameKey].push(obj.value);
                    } else {
                        // Caso não seja um array ainda, cria um e insere os valores dentro dele
                        var temp = data[arrayName][arrayNameKey];
                        data[arrayName][arrayNameKey] = [];
                        data[arrayName][arrayNameKey].push(temp);
                        data[arrayName][arrayNameKey].push(obj.value);
                    }
                    //Caso não exista, ele só insere
                } else {
                    //Insere o valor no objeto. tendo o name como chave
                    data[arrayName][arrayNameKey] = obj.value;
                }
                //Caso seja vazia, trabalha como array
            } else {
                //insere o valor no array
                data[arrayName].push(obj.value);
            }
        } else {
            //Caso não seja parte de um array, simplemente insere como objeto
            data[obj.name] = obj.value;
        }
    });

    // Retorna um json com os campos e valores
    return JSON.stringify(data);
};

nobossmodal.toogleBodyBackgroundFade = function(state){
    if (state) {
        jQuery('body').addClass('modal--is-open');
    } else {
        jQuery('body').removeClass('modal--is-open');
        jQuery(document).trigger('modalClosed');
    }
};


jQuery(document).ready(function (jQuery) {
    // Declara $ para evitar conflito
    $ = jQuery;
    // Scripts que são necessários estarem carregados na página
    var scripts = [
        "../libraries/noboss/assets/plugins/js/min/jquery-confirm.min.js",
        "../libraries/noboss/assets/plugins/stylesheets/css/jquery-confirm.min.css",
    ];
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
        nobossmodal.CONSTRUCTOR();
    });
});
