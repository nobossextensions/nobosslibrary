// verifica se o objeto util não existe para criá-lo
if (!util) {
    var util = {};
}

/**
 * Cria o 
 * 
 * @param  string	 hrefAba    Href do link da aba que deve ser a default	
 *
 * @return void
 */
util.subformRowAddListener = function () {
    var activated = true;
    if(activated) {
        // Cria o evento que é ativado quando uma nova linha é adicionada no subform
        jQuery(document).on('subform-row-add', function (event, row) {
            // console.log(row);
            //Ativa o chosen para selects
            jQuery(row).find('select').chosen({ disable_search_threshold: 10 });
            // bootstrap tooltips
            if (jQuery.fn.popover) {
                $(row).find('.hasPopover').popover({ trigger: 'hover focus' });
            }
            // Remove da pagina a tag '<scripts>' usada por esse plugin para recriar eventos JS de modais
            jQuery(document).contents().find("[data-target*='functions-modal-subform']").remove();
            // Ativa os campos radio
            util.activateYesno($(row));
            // Ativa os campos clockpicker
            util.activateClockpicker($(row));
            // Ativa os campos tipo module para edicao por modal
            util.activateModalModule($(row));
            // Ativa os campos tipo nobossarticles para edicao por modal
            util.activateModalArticle($(row));
            // Ativa os campos tipo nobossmenus para edicao por modal
            util.activateModalMenu($(row));
        });
        activated = false;
    }    
};

// Ativa campos radio
util.activateYesno = function (container) {
    // Turn radios into btn-group
    container.find('.radio.btn-group label').addClass('btn');

    // Prevent clicks on disabled fields
    container.find('fieldset.btn-group').each(function () {
        if (jQuery(this).prop('disabled')) {
            jQuery(this).css('pointer-events', 'none').off('click');
            jQuery(this).find('.btn').addClass('disabled');
        }
    });

    // Add btn-* styling to checked fields according to their values
    container.find('.btn-group label:not(.active)').click(function () {
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
};
    
// Ativa campos clockpicker
util.activateClockpicker = function (container) {
    // Percorre os campos do tipo clickpicker
    container.find('[data-id-clockpicker]').each(function () {
        jQuery(this).clockpicker();
    });
};

// Ativa campos do tipo module para edicao por modal
util.activateModalModule = function (container) {
    // Percorre todos campos de selecao de modulo com botao que abre modal de edicao
    container.find('a.editModule').each(function () {
        // Obtem id real da modal para que possa ser feito replace nos lugares que estao errados ao gerar o item do sobform
        var moduleEditModalId = jQuery(this).parent().find('select').attr('id');

        var moduleEditModalName = 'modal_' + moduleEditModalId;
        // Atualiza href
        jQuery(this).attr('href', '#'+moduleEditModalName);
        // Atualiza ID da div que tem a classe 'modal' e junto o onClick do button filho que tem a classe 'btn-primary'
        jQuery(this).closest('[data-base-name]').find("[data-button-module-close]").parent().parent().attr('id', moduleEditModalName).find('.modal-footer button.btn-primary').attr('onClick', "jQuery('#"+moduleEditModalName+" iframe').contents().find('#applyBtn').click()");
        
        // Recria evento para quando clicar no botao de edicao da modal (originalmente isso ja eh feito dentro do campo do 'nobossmodules', mas precisa ser refeito aqui para subform)
        jQuery(function($) {
            $("#"+moduleEditModalName).on("shown.bs.modal", function() {
                var moduleEditModalIdModule = jQuery('#'+moduleEditModalId).val();
                var moduleEditModalUrl = baseNameUrl+"administrator/index.php?option=com_modules&view=module&task=module.edit&layout=modal&tmpl=component&id=" + moduleEditModalIdModule;
                
                // Caso ja tenha iframe na modal, remove para inserir novamente
                jQuery(this).find('.modal-body').find('iframe').remove();

                // Adicionar iframe dentro da modal que eh aberta ja com a url correta
                jQuery(this).find('.modal-body').prepend('<iframe class="iframe jviewport-height70" src="'+moduleEditModalUrl+'" name="Editar módulo" height="400px" width="800px"></iframe>');
            });
        });
    });
};

/**
 * Funcao com acoes genericas dos campos que permitem criar / editar / selecionar itens (artigos, menus)
 *
 * @param	String		type                Tipo de modal
 * @param	Object		fieldObj            Objeto this do field que estara sendo tratado
 * @param	String		modalFieldId        Id do field da modal para manipulacao dos elementos html
 * @param	String		urlModalEdit        url da modal de edicao de item
 * @param	String		urlModalNew         url da modal de criacao de item
 * @param	String		urlModalSelect      url da modal de selecao de item
 *
 */
util.genericActionsModal = function (type, fieldObj, modalFieldId, urlModalEdit, urlModalNew, urlModalSelect) {
    switch (type) {
        case 'article':
            typeHtml = 'Article';
            break;
        case 'menu':
                typeHtml = 'Item';
            break;
        /*
         * TODO: tentar criar modal do tipo module (criamos um campo que permite fazer algumas acoes, mas não tem tantos recursos)
         * No caso do modulo, nao temos um campo do joomla para estender e teremos que criar os eventos
         */
        // case 'module':
        //     xxxx
        //     break;
        default:
            return;
    }

    // Atualiza atributos id e for do label
    jQuery(fieldObj).closest('.control-group').find('label').attr('id', modalFieldId+'_id-lbl').attr('for', modalFieldId+'_id');

    // Atualiza atributo id do input text
    jQuery(fieldObj).closest('.controls').find('input[type="text"]').attr('id', modalFieldId+'_name');

    // Ajusta id do campo input hidden principal (por algum motivo falta ter '_id' no final do id do campo)
    jQuery(fieldObj).closest('.controls').find('#'+modalFieldId).attr('id', modalFieldId+'_id');

    // Atualiza atributo id e data-target do button de selecionar ou alterar artigo
    var btnSelect = jQuery(fieldObj).attr('id', modalFieldId+'_name').attr('id', modalFieldId+'_select').attr('data-target', '#ModalSelect'+typeHtml+'_'+modalFieldId+'');
    // Atualiza atributo id e data-target do button de criar novo artigo
    var btnNewModal = jQuery(fieldObj).closest('.controls').find("[data-target*='ModalNew"+typeHtml+"']").attr('id', modalFieldId+'_new').attr('data-target', '#ModalNew'+typeHtml+'_'+modalFieldId+'');
    // Atualiza atributo id e data-target do button de editar artigo
    var btnEditModal = jQuery(fieldObj).closest('.controls').find("[data-target*='ModalEdit"+typeHtml+"']").attr('id', modalFieldId+'_edit').attr("data-target", "#ModalEdit"+typeHtml+"_"+modalFieldId);
    // Atualiza atributo id e onClick do button de limpar artigo
    var btnClearModal = jQuery(fieldObj).closest('.controls').find("[id*='_clear']").attr('id', modalFieldId+'_clear').attr("onClick", "window.processModalParent('"+modalFieldId+"'); return false;");

    // Atualiza id de div com id comecando por ModalSelectXXXX
    jQuery(fieldObj).closest('.controls').find("[id*='ModalSelect"+typeHtml+"']").attr('id', 'ModalSelect'+typeHtml+'_' + modalFieldId);
    // Atualiza id de div com id comecando por ModalNewXXXX
    jQuery(fieldObj).closest('.controls').find("[id*='ModalNew"+typeHtml+"']").attr('id', 'ModalNew'+typeHtml+'_' + modalFieldId);
    // Atualiza id de div com id comecando por ModalEditXXXX
    jQuery(fieldObj).closest('.controls').find("[id*='ModalEdit"+typeHtml+"']").attr('id', 'ModalEdit'+typeHtml+'_' + modalFieldId);

    // Atualiza atributo onClick do button de fechar da modal
    jQuery(fieldObj).closest('.controls').find('.modal-footer').find("[onClick*='cancel']").attr("onClick", "window.processModalEdit(this, '"+modalFieldId+"', 'add', '"+typeHtml.toLowerCase()+"', 'cancel', 'item-form'); return false;");
    // Atualiza atributo onClick do button de 'salvar e fechar' da modal
    jQuery(fieldObj).closest('.controls').find('.modal-footer').find("[onClick*='save']").attr("onClick", "window.processModalEdit(this, '"+modalFieldId+"', 'add', '"+typeHtml.toLowerCase()+"', 'save', 'item-form'); return false;");
    // Atualiza atributo onClick do button de salvar da modal
    jQuery(fieldObj).closest('.controls').find('.modal-footer').find("[onClick*='apply']").attr("onClick", "window.processModalEdit(this, '"+modalFieldId+"', 'add', '"+typeHtml.toLowerCase()+"', 'apply', 'item-form'); return false;");

    // Define nome da funcao que sera criada logo abaixo dinamicamente
    var nameFunctionModal = "jSelect"+typeHtml+"_"+modalFieldId;

    // Se o tipo for menu, refaz o nome da funcao que precisa de tratamento especifico
    if (type == 'menu'){
        nameFunctionModal = "jSelectMenu_"+modalFieldId;
    }

    // Criar funcao com nome do campo necessarias para o funcionamento da modal
    jQuery(document).contents().find('head').append("<script data-id='functions-modal-subform'> function "+nameFunctionModal+"(id, title, catid, object, url, language) { window.processModalSelect('"+typeHtml+"', '"+modalFieldId+"', id, title, catid, object, url, language); } </script>");

    // Recria evento para modal de selecao do item
    util.adjustModalIframe("ModalSelect"+typeHtml+"_"+modalFieldId, modalFieldId, urlModalSelect);

    // Recria evento para modal de criar novo item
    util.adjustModalIframe("ModalNew"+typeHtml+"_"+modalFieldId, modalFieldId, urlModalNew);

    // Recria evento para modal de editar um item
    util.adjustModalIframe("ModalEdit"+typeHtml+"_"+modalFieldId, modalFieldId, urlModalEdit);
    
    // Scripts que precisam estar com toda pagina carregada ja
    jQuery(document).ready(function($) {
        // Adiciona setTimeout para uso do filtro do calendar que recria os itens do subform e precisa de milisegundos para isso
        setTimeout(function(){
            // Item possui valor (edicao de registro) e botao de editar esta escondido
            if ((jQuery('#'+modalFieldId+'_id').val() != '') && (jQuery(btnEditModal).hasClass('hidden'))){
                // Esconde botao de selecionar item
                jQuery(btnSelect).addClass('hidden');
                // Esconde botao de criar item
                jQuery(btnNewModal).addClass('hidden');
                // Exibe botao editar
                jQuery(btnEditModal).removeClass('hidden');
                // Exibe botao de limpar
                jQuery(btnClearModal).removeClass('hidden');
                // Altera o texto do input que descreve qual artigo esta selecionado (como nao temos o nome, exibimos apenas o ID)
                jQuery('#'+modalFieldId+'_name').val(typeHtml+' ID ' + jQuery('#'+modalFieldId+'_id').val());
            }
        }, 500);
    });
};

/**
 * Funcao que observa quando modal eh aberta para adicionar iframe dentro dela
 *
 * @param	String		idModal         Id da modal
 * @param	String		modalFieldId    Id do field da modal
 * @param	String		modalSrc        url do iframe da modal
 *
 */
util.adjustModalIframe = function (idModal, modalFieldId, modalSrc) {
    jQuery(document).ready(function($) {
        // Usuario clicou para abrir a modal
        $("#"+idModal).on("shown.bs.modal", function() {
            var srcIframe = modalSrc;

            // Modal do tipo edicao: obtem id para concatenar no final da url (eh preciso pegar dinamicamente aqui)
            if (idModal.match(/ModalEdit/)){
                srcIframe = modalSrc + jQuery('#'+modalFieldId+'_id').val();
            }

            // Caso ja tenha iframe na modal, remove para inserir novamente
            jQuery(this).find('.modal-body').find('iframe').remove();

            // Adicionar iframe dentro da modal que eh aberta ja com a url correta
            jQuery(this).find('.modal-body').prepend('<iframe class="iframe jviewport-height70" src="'+srcIframe+'" height="400px" width="800px"></iframe>');
        });
    });
};

// Ativa campos do tipo nobossarticles (modal para criacao, selecao e edicao de artigos)
util.activateModalArticle = function (container) {
    // Percorre todos campos (utilizado um dos botoes como identificador)
    container.find("[data-target*='ModalSelectArticle']").each(function () {
        // Obtem id real da modal para que possa ser feito replace nos lugares que estao errados ao gerar o item do sobform
        var modalFieldId = jQuery(this).parent().parent().find("input[type='hidden']").attr('id');

        // url para iframe da modal de edicao de item (obs: o valor do id eh concatenado no final da url na hora que a modal eh aberta)
        var urlModalEdit = baseNameUrl+"administrator/index.php?option=com_content&view=article&layout=modal&tmpl=component&57590de4f8b37151f8815f5bdff139b4=1&task=article.edit&id=";
        // url para iframe da modal de criacao de item
        var urlModalNew = baseNameUrl+"administrator/index.php?option=com_content&view=article&layout=modal&tmpl=component&57590de4f8b37151f8815f5bdff139b4=1&task=article.add";
        // url para iframe da modal de selecao de item
        var urlModalSelect = baseNameUrl+"administrator/index.php?option=com_content&view=articles&layout=modal&tmpl=component&57590de4f8b37151f8815f5bdff139b4=1&function=jSelectArticle_" + modalFieldId;

        // Executa funcao com acoes genericas para modais
        util.genericActionsModal('article', this, modalFieldId, urlModalEdit, urlModalNew, urlModalSelect);
    });
};

// Ativa campos do tipo nobossmenus (modal para criacao, selecao e edicao de menus)
util.activateModalMenu = function (container) {
    // Percorre todos campos (utilizado um dos botoes como identificador)
    container.find("[data-target*='ModalSelectItem']").each(function () {
        // Obtem id real da modal para que possa ser feito replace nos lugares que estao errados ao gerar o item do sobform
        var modalFieldId = jQuery(this).parent().parent().find("input[type='hidden']").attr('id');

        // url para iframe da modal de edicao de item (obs: o valor do id eh concatenado no final da url na hora que a modal eh aberta)
        var urlModalEdit = baseNameUrl+"administrator/index.php?option=com_menus&view=item&layout=modal&client_id=0&tmpl=component&0936722486b34d6072bcc00621a37810=1&task=item.edit&id=";
        // url para iframe da modal de criacao de item
        var urlModalNew = baseNameUrl+"administrator/index.php?option=com_menus&view=item&layout=modal&client_id=0&tmpl=component&0936722486b34d6072bcc00621a37810=1&task=item.add";
        // url para iframe da modal de selecao de item
        var urlModalSelect = baseNameUrl+"administrator/index.php?option=com_menus&view=items&layout=modal&client_id=0&tmpl=component&0936722486b34d6072bcc00621a37810=1&function=jSelectMenu_" + modalFieldId;

        // Executa funcao com acoes genericas para modais
        util.genericActionsModal('menu', this, modalFieldId, urlModalEdit, urlModalNew, urlModalSelect);
    });
};


/**
 * Recebe uma referência de um container e ativa os js necessário para o funcionamento de alguns campos
 * 
 * @param {jQuery Object} container Objeto jQuery do elemento
 */
util.fixScripts = function (container) {
    
    // fix media field
    container.find('a[onclick*="jInsertFieldValue"]').each(function () {
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
        container.find('.field-media-wrapper').fieldMedia();
    }

    // bootstrap tooltips
    if (jQuery.fn.popover) {
        container.find('.hasPopover').popover({ trigger: 'hover focus' });
    }

    // bootstrap based User field
    if (jQuery.fn.fieldUser) {
        container.find('.field-user-wrapper').fieldUser();
    }

    // Caso tenha o calendário na página
    if (window.Calendar) {
        elements = container.find(".field-calendar");
        // Percorre os calendários da container e ativa
        elements.each(function () {
            JoomlaCalendar.init(this);
        });
    }

    // Caso tenha editor na pagina
    if(window.nobosseditor){
        // Ativa css custom dentro do editor (funcao do JS editor do noboss library)
        nobosseditor.addCssFileEditor();

        // Percorre os botoes 'Trocar editor' quando for tinymce para refazer atributo 'onclick' colocando o ID correto
        container.find('.nb-editor .toggle-editor').each(function () {
            jQuery(this).find("a").attr("onClick", "tinyMCE.execCommand('mceToggleEditor', false, '"+jQuery(this).parent().find('textarea').attr('id')+"'); return false");
        });
    }

    if(typeof nobossdisplaymodelist !== 'undefined'){
        nobossdisplaymodelist.CONSTRUCTOR();
    }

};
