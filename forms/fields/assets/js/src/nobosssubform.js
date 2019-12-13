var nobosssubform = {};

nobosssubform.CONSTRUCTOR = function(){
    // Configurações globais para a modal de alerta e confimação
    jconfirm.defaults = {
        draggable: false,
        useBootstrap: false,
        columnClass: "modal-confirm-resposive",
        animateFromElement: false
    };

    // Ativa o evento de collapse de todos os itens de subform na página
    nobosssubform.activateItemCollapse();

    nobosssubform.activateCollapseButtons();

    // verifica para cada botao de reset na pagina se ele deve ser exibido ou escondido dependendo se existe item cadastrado
    jQuery('[data-id="noboss-subform-reset"][data-multiple="true"][data-show="true"]').each(function(){
        nobosssubform.validateButtonDisplay(jQuery(this));
    }); 

    // para cada subform unico na pagina, esconde os botoes de acao de subform multiplo
    jQuery('[data-id="noboss-subform-reset"][data-multiple="false"]').each(function(){
        nobosssubform.hideMultipleButtons(jQuery(this));
    });

    // Evento ativado na troca do tema
    jQuery('body').on('change', '[data-id="theme-modal-input"]', function(){
        // esconde os botoes "Limpar" do nobosssubform caso tenha sido dito no xml que ele nao deve ser exibido
        jQuery('[data-id="noboss-subform-reset"][data-show="false"]').each(function () {
            jQuery(this).hide();
        });
        // reativa os botoes de collapse desse subform
        nobosssubform.activateCollapseButtons();
        nobosssubform.toggleCollapseAll('shrink');
    });

    // evento de item do subform removido
    jQuery(document).on('subform-row-remove', function (event, row) {
        // pega o botao de reset do subform desse item que foi apagado
        var resetButton = jQuery(row).parents('.row-fluid').siblings('[data-id="noboss-subform-reset"]');
        // apaga o item do html
        jQuery(row).remove();
        // verifica se foi definido no xml para o botao ser exibido
        if(resetButton.data('show') === true){
            // valida se o campo deve continuar sendo exibido dependendo se ainda existe item cadastrado 
            nobosssubform.validateButtonDisplay(resetButton);
        }
    });

    // evento de item do subform adicionado
    jQuery(document).on('subform-row-add', function (event, row) {
        var mainSection = jQuery('section#content');
        util.fixScripts(mainSection);
        util.activateYesno(mainSection);
        // pega o botao de reset do subform desse item que foi apagado
        var resetButton = jQuery(row).parents('.row-fluid').first().find('[data-id="noboss-subform-reset"]').first();
        // verifica se foi definido no xml para o botao ser exibido    
        if(resetButton.data('show') === true){
            // exibe o botao
            resetButton.show();
        }
        
        // aproveita requisicao para pegar botoes de expandir e recolher que tambem seguem mesma regra
        var collapseButtons = resetButton.closest('.row-fluid').find('[data-id="noboss-collapse-button"]');
        collapseButtons.show();

        // se o subform nao for multiplo, esconde os botoes de acao
        if(resetButton.data('multiple') === false){
            nobosssubform.hideMultipleButtons(resetButton);
        }
        // Ativa o evento de collapse da nova linha
        nobosssubform.activateItemCollapse(row);
    });

    // evento de clique no botao de reset
    jQuery('section#content').on('click', '[data-id="noboss-subform-reset"]', nobosssubform.confirmModal);

    jQuery(window).on('change', function(){
        nobosssubform.changeSubformLabel();
    });
    
    jQuery(window).load(function(){
        nobosssubform.changeSubformLabel();
        nobosssubform.toggleCollapseAll('shrink');
    });
};

nobosssubform.changeSubformLabel= function (){
    //aqui eh verificado se o label do subform tem algum valor
    var subformLabelElement = jQuery('[data-subform-collapse-wrapper]').parent().prev();
    var subformLabelClass = "";
    if(jQuery(subformLabelElement).text().trim().length !== 0){
        //caso tenha, coloca uma classe para aplicar estilos de label
        subformLabelClass = 'subform-label--visible';
    }else{
        subformLabelClass = 'subform-label--invisible';
    }

    jQuery(subformLabelElement).addClass(subformLabelClass);
};

/**
 *  Reativa os eventos dos subforms
 *
 */
nobosssubform.activateItemCollapse = function(row) {

    //verifica se o subform passado ja esta com os eventos ativos
    if(jQuery(row).data('activated') == true){
        //se sim, apenas para a execucao
        return;
    } else {
        //caso nao tenha sido passado um subform como parametro, significa que ele nao foi criado agora
        if(row === undefined){
            //entao, apenas define os eventos para os subforms que estao na pagina
            jQuery('[data-noboss-collapse]').off('click').on('click', nobosssubform.toggletCollapse);
        } else {
            jQuery(row).find('[data-noboss-collapse]').off('click').on('click', nobosssubform.toggletCollapse);
            jQuery(row).data('activated', true);
        }
    }  
};

/**
 *  Evento de toggle que verifica se deve colapsar ou expandir os itens
 *
 */
nobosssubform.toggletCollapse = function(){
    //pega o elemento pai do collapse que é o subform
    var parentGroup =  jQuery(this).parent();
    //verifica se o subform tem a classe expandida
    if(jQuery(parentGroup).data('collapse') === 'grow'){
        //se sim, colapsa o elemento
        nobosssubform.collapseItems(parentGroup);
    }else{
        //caso esteja colapsada, expande
        nobosssubform.expandItems(parentGroup);
    }
};

/**
 *  Reativa os botoes de colapsar/expandir todos
 *
 */
nobosssubform.activateCollapseButtons = function(){
    var collapseButtons = jQuery('[data-id=noboss-collapse-button]');
    //pega cada botao
    jQuery(collapseButtons).each(function(){
        //ativa o evento de click
        jQuery(this).on('click', function(){
            var collapseToggle = jQuery(this).data('toggle');
            //informa qual elemento que possui os subforms para expandir/colapsar
            var closestSubformWrapper = jQuery(this).closest('[data-subform-collapse-wrapper]');
            //chama o metodo de alternar a exibicao com o valor do botao e o elemento wrapper
            nobosssubform.toggleCollapseAll(collapseToggle, closestSubformWrapper);
        });
    });
};

/**
 *  De acordo com o parametro expande ou colapse todos os itens
 *
 */
nobosssubform.toggleCollapseAll = function(collapseToggle, subformWrapper){
    var currentSubformWrapper = subformWrapper;

    //caso nao tenha sido passado o parametro de wrapper dos subforms, alterna TODOS os subforms
    if(subformWrapper === undefined){
        currentSubformWrapper = jQuery('[data-subform-collapse-wrapper]');
    }
    //para cada subform 
    jQuery(currentSubformWrapper).find('.subform-repeatable-group').each(function(){
        //verifica se precisa expandir ou colapsar
        if(collapseToggle == 'grow'){
            if(jQuery(this).data('collapse') === 'shrink'){
                nobosssubform.expandItems(jQuery(this));
            }
        }else if(collapseToggle == 'shrink'){
            if(jQuery(this).data('collapse') === 'grow'){
                nobosssubform.collapseItems(jQuery(this));
            }
        }
    });
};


/**
 *  Expande os items do grupo informado
 *
 */
nobosssubform.expandItems = function(parentGroup){

    //pega os elementos dentro do subform que estejam com a classe hidden, remove ela, para a execucao de qualquer animacao e anima
    jQuery(parentGroup).children('.hidden').removeClass('hidden').clearQueue().slideDown();
    //limpa o titulo do subform
    jQuery(parentGroup).find('.noboss-collapse__title').text('');
    //muda o icone que sinaliza se esta expandido ou nao
    jQuery(parentGroup).find('.noboss-collapse__icon').removeClass('rotate-icon');
    //muda o data attribute que informa se esta colapsado ou nao
    jQuery(parentGroup).data('collapse', 'grow');
    //alterna as classes de estilo para expandido
    jQuery(parentGroup).toggleClass('shrink grow');
};

/**
 *  Colapsa os items do grupo informado o subform
 *
 */
nobosssubform.collapseItems = function(parentGroup){
    //muda a exibicao apenas dos elementos nao visiveis, que nao sao o collapse e que nao sao a toolbar
    jQuery(parentGroup).children().not('[style*="display: none"], .noboss-collapse, .btn-toolbar').clearQueue().slideUp('fast', function(){
        //adiciona a classe para poder diferenciar dos elementos que estao com display none inline (aqueles com showon)
        jQuery(this).addClass('hidden');
    });
    //atualiza o titulo do subform
    nobosssubform.updateCollapseTitle(parentGroup);
    //modifica o icone do collapse sinalizando que esta fechado
    jQuery(parentGroup).find('.noboss-collapse__icon').addClass('rotate-icon');
    //muda o data attribute que guarda o status do collapse
    jQuery(parentGroup).data('collapse', 'shrink');
    //modifica a classe do subform para ter o estilo de quando esta fechado
    jQuery(parentGroup).toggleClass('shrink grow');
};

/**
 *  Atualiza o titulo do collapse
 *
 */
nobosssubform.updateCollapseTitle = function(parentGroup){
    //pega a label que deve ser exibida no titulo
    var collapseLabel = jQuery(parentGroup).closest('[data-subform-collapse-wrapper]').find('[data-collapse-label]').data('collapse-label');

    if(collapseLabel){
        //pega o grupo do subform
        var subformGroup = jQuery(parentGroup).data('group');
        //busca no subform atual o texto do elemento que possui a label para titulo
        var collapseTitle = jQuery(parentGroup).find("[for$="+subformGroup + '__'+collapseLabel+"]").text();

        // Retira astericos que ficam no label para campos obrigatorios
        collapseTitle = collapseTitle.replace('*', '');
    
        //pega o valor que deve ser concatenado ao titulo
        var collapseValueElement = jQuery(parentGroup).find("[id$="+subformGroup + '__' + collapseLabel+"]");
        var collapseValue = jQuery(collapseValueElement).val();
        //caso seja um campo editor, pega o iframe que tem o valor real do campo
        if(jQuery(parentGroup).closest("[data-subform-collapse-wrapper]").find("[data-is-editor]").data('is-editor')){
            collapseValue = jQuery(collapseValueElement).parent().find("iframe").contents().find('body').text();
        }

        if(collapseValue !== undefined){
            //pega o valor default para quando esta vazio
            if(collapseValue.trim().length === 0){
                collapseValue = jQuery('[data-collapse-default-value]').data('collapse-default-value');
            }
            //modifica o titulo do collapse adicionando titulo da label e o respectivo valor
            jQuery(parentGroup).find('.noboss-collapse__title').text(collapseTitle + ': ' + collapseValue);
        }
    }

};

/**
 *  Abre uma modal de confirmacao de reset dos itens cadastrados
 *
 */
nobosssubform.confirmModal = function(e){
    e.preventDefault();
    // botao de reset que foi clicado
    var resetButton = jQuery(this);

    // numero minino de itens
    var min = resetButton.data('min');

    // guarda se eh um subform multiplo
    var multiple = resetButton.data('multiple');

    // pega contexto onde se encontram os itens do subform relacionado ao botao de reset
    var itemsWrap = resetButton.closest('.row-fluid');

    // pega botao de adicionar item
    var addButton = itemsWrap.find('a.group-add.button:first');

    // pega os subforms existentes
    var subforms = itemsWrap.find('[data-group^="' + resetButton.data('name') + '"]');

    // exibe alerta de confirmacao
    jQuery.confirm({
        title: Joomla.JText._('LIB_NOBOSS_FIELD_NOBOSSSUBFORM_ALERT_MESSAGE_TITLE'),
        content: Joomla.JText._('LIB_NOBOSS_FIELD_NOBOSSSUBFORM_ALERT_MESSAGE_CONTENT'),
        type: 'blue',
        closeIcon: 'cancel',
        escapeKey: 'cancel',
        buttons: {
            cancel: {
                keys: ['esc'],
                text: Joomla.JText._('LIB_NOBOSS_FIELD_NOBOSSMODAL_MODAL_CONFIRM_CANCEL_BUTTON')
            },
            confirm: {
                text: Joomla.JText._('LIB_NOBOSS_FIELD_NOBOSSMODAL_MODAL_CONFIRM_CONFIRM_BUTTON'),
                btnClass: 'btn-blue',
                keys: ['enter'],
                action: function () {
                    // entra aqui se confirmar foi clicado
                    // verifica se o subform eh multiplo
                    if(multiple){
                        // itera cada item do subform
                        jQuery(subforms).each(function(index, el){
                            // remove o item clicando no botao de delete
                            jQuery(el).find('.btn-group').find('.btn-danger').first().click();
                        });
                        // verifica se ha um minimo de itens setados
                        if(min){
                            // cria novos itens de acordo com o numero minimo
                            for(var i = 0; i < min; i++){
                                addButton.click();
                            }
                        }
                        // valida se o botao deve ser exibido ou escondido
                        nobosssubform.validateButtonDisplay(resetButton);
                    }else{
                        // entra aqui se nao for multiplo
                        // primeiro adiciona um novo item
                        addButton.click();
                        
                        // atualiza lista de itens cadastrados
                        subforms = itemsWrap.find('[data-group^="' + resetButton.data("name") + '"]');
                        
                        // exclui o item que estava preenchido
                        jQuery(subforms[0]).remove();
                        
                        // esconde os botoes de acoes de subform multiplo
                        nobosssubform.hideMultipleButtons(resetButton);
                    }
                }
            }
        }
    });


};

/**
 *  Valida se o botao de limpar deve ser exibido ou escondido, dependendo se ha dados cadastrados
 *
 * @param {JQuery Object} button Botao de reset do subform
 */
nobosssubform.validateButtonDisplay = function(button){
    // pega o botao de reset
    var resetButton = button;
    // aproveita requisicao para pegar botoes de expandir e recolher que tambem seguem mesma regra
    var collapseButtons = resetButton.closest('.row-fluid').find('[data-id="noboss-collapse-button"]');
    // pega o "container" de items do subform relacionado a esse botao
    var itemsWrap = resetButton.closest('.row-fluid');
    // pega os itens do subform
    var subforms = itemsWrap.find('[data-group^="' + resetButton.data('name') + '"]');
    // se existir itens do subform, o botao deve ser exibido
    if(subforms.length != 0){
        resetButton.show();
        collapseButtons.show();
    }else{
        resetButton.hide();
        collapseButtons.hide();
    }

};

/**
 *  Esconde os botoes de acoes para subform multiplo
 *
 * @param {JQuery Object} button Botao de reset do subform
 */
nobosssubform.hideMultipleButtons = function(button){
    // pega o botao de reset
    var resetButton = button;
    // pega o "container" de items do subform relacionado a esse botao
    var itemsWrap = resetButton.siblings('.row-fluid');
    // esconde a div com os botoes
    itemsWrap.find('div.btn-toolbar').hide();
    // adiciona classe que corrige o espacamento entre o botao e o container do subform
    resetButton.siblings('.row-fluid').addClass('clear-fix');
};

jQuery(document).ready(function (jQuery) {
    // Declara $ para evitar conflito
    $ = jQuery;
    // Scripts que são necessários estarem carregados na página
    var scripts = [
        "../libraries/noboss/assets/plugins/js/min/jquery-confirm.min.js",
        "../libraries/noboss/assets/plugins/stylesheets/css/jquery-confirm.min.css",
        "../libraries/noboss/assets/util/js/min/reactivatesubformfields.min.js"
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
        nobosssubform.CONSTRUCTOR();
    });
});
