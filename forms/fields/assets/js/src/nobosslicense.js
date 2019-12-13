jQuery(document).ready(function (jQuery) {
    // Declara $ para evitar conflito
    $ = jQuery;
    // Scripts que são necessários estarem carregados na página
    var scripts = [
        "../libraries/noboss/assets/util/js/min/cookies.min.js",
        "../libraries/noboss/assets/util/js/min/loaders.min.js",
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
        // Adiciona um método para pegar parametros da url
        jQuery.urlParam = function(name){
            var results = new RegExp('[\?&]' + name + '=([^&#]*)').exec(window.location.href);
            return results[1] || 0;
        };
        // Chama o constructor
        nobosslicense.CONSTRUCTOR();
    });
});

var nobosslicense = {};

nobosslicense.CONSTRUCTOR = function(){
    nobosslicense.licenseInfo = Joomla.getOptions('nobosslicense');
    
    // Configurações globais para a modal de aleta e confimação
    jconfirm.defaults = {
        draggable: false,
        useBootstrap: true,
        animateFromElement: false,
        columnClass: 'modal-confirm-responsive'
    };

    // Verifica se não teve erro de token não encontrado na base local
    if (nobosslicense.licenseInfo !== undefined && nobosslicense.licenseInfo.data === 'TOKEN_OR_PLAN_NOT_FOUND') {
        // Esconde a aba de licença e abre uma modal de erro
        nobosslicense.alertErrorModal(Joomla.JText._('LIB_NOBOSS_FIELD_NOBOSSLICENSE_MODAL_INVALID_TOKEN_TITLE'), Joomla.JText._('LIB_NOBOSS_FIELD_NOBOSSLICENSE_MODAL_INVALID_TOKEN_DESC'));
        
    // Verifica se conseguiu conexão com o servidor
    } else if (nobosslicense.licenseInfo === undefined || nobosslicense.licenseInfo.data === 'CONNECTION_ERROR') {
        // Esconde a aba de licença e abre uma modal de erro
        nobosslicense.alertErrorModal(Joomla.JText._('LIB_NOBOSS_FIELD_NOBOSSLICENSE_MODAL_INITIAL_CONNECTION_ERROR_TITLE'), Joomla.JText._('LIB_NOBOSS_FIELD_NOBOSSLICENSE_MODAL_INITIAL_CONNECTION_ERROR_DESC'));

    // Verifica se não teve erro de token não encontrado na base do Extensions
    } else if (nobosslicense.licenseInfo !== undefined && nobosslicense.licenseInfo.data === 'INVALID_TOKEN') {
        // Esconde a aba de licença e abre uma modal de erro
        nobosslicense.alertErrorModal(Joomla.JText._('LIB_NOBOSS_FIELD_NOBOSSLICENSE_MODAL_INVALID_TOKEN_TITLE'), Joomla.JText._('LIB_NOBOSS_FIELD_NOBOSSLICENSE_MODAL_INVALID_TOKEN_DESC'));
        
    // Verifica se a licença não está desativada
    } else if (jQuery('#license_state').val() == '0') {
        // Abre uma modal de erro
        nobosslicense.alertErrorModal(Joomla.JText._('LIB_NOBOSS_FIELD_NOBOSSLICENSE_MODAL_UNPUBLISHED_LICENSE_TITLE'), Joomla.JText._('LIB_NOBOSS_FIELD_NOBOSSLICENSE_MODAL_UNPUBLISHED_LICENSE_DESC'), false);

    // Caso foi possível conectar com o servidor, ativa os eventos
    } else {
        // Caso exista algum erro relacionado a licença, adiciona um alerta no topo
        if (nobosslicense.licenseInfo.flags.license_has_errors && !nobosslicense.licenseInfo.flags.has_parent_license) {
            var urlOption = jQuery.urlParam('option');
            var msgText = '';
            var linkText = '';
            var linkHref = '#';

            // Caso seja um módulo
            if (urlOption === 'com_modules') {
                msgText = Joomla.JText._('LIB_NOBOSS_FIELD_NOBOSSLICENSE_ALERT_HAS_ERRORS_MODULE');
                linkText = Joomla.JText._('LIB_NOBOSS_FIELD_NOBOSSLICENSE_ALERT_HAS_ERRORS_MODULE_LINK');
                jQuery('body').on('click', '[data-id="open-license-tab"]',function (e) {
                    e.preventDefault();
                    jQuery('[href="#attrib-license"]').trigger('click');
                });
            // Caso seja um componente noboss
            } else if (/^com_noboss/.test(urlOption)){
                msgText = Joomla.JText._('LIB_NOBOSS_FIELD_NOBOSSLICENSE_ALERT_HAS_ERRORS_COMPONENT');
                linkText = Joomla.JText._('LIB_NOBOSS_FIELD_NOBOSSLICENSE_ALERT_HAS_ERRORS_COMPONENT_LINK');
                // Monta o caminho para as configurações globais do componente
                linkHref = window.location.origin + window.location.pathname + '?option=com_config&view=component&component='+urlOption;
            // Caso seja as configurações globais
            } else if (urlOption === 'com_config') {
                msgText = Joomla.JText._('LIB_NOBOSS_FIELD_NOBOSSLICENSE_ALERT_HAS_ERRORS_GLOBAL_COMPONENT');
                linkText = Joomla.JText._('LIB_NOBOSS_FIELD_NOBOSSLICENSE_ALERT_HAS_ERRORS_GLOBAL_COMPONENT_LINK');
                jQuery('body').on('click', '[data-id="open-license-tab"]',function (e) {
                    e.preventDefault();
                    jQuery('[href="#license"]').trigger('click');
                });
            }      

            // Mensagem que estará no topo
            jQuery('#system-message-container').before(
                '<div data-id="license-alert-message" class="feedback-notice feedback-notice--error" data-has-error style="margin-top:-5px;">'+
                    '<span class="feedback-notice__icon fa fa-ban"></span>'+
                    '<div class="feedback-notice__content">'+
                        '<p class="feedback-notice__message" data-id="license-alert-message-text"> '+
                            msgText+
                            ' <a href="'+linkHref+'" target="_blank" data-id="open-license-tab"> '+linkText+'</a>'+
                        '</p>'+
                    '</div>'+
                '</div>'
            );
        }

        // Evento de clique no botão de atualizar url autorizada
        jQuery('[data-id=license-alert-message]').on('click', '[data-id="btn-change-url"]', nobosslicense.changeAuthorizedUrl);
        // Evento de clique no botão para realizar upgrade
        jQuery('[data-id="plan-upgrade"]').on('click', '[data-id="btn-plan-upgrade"]', nobosslicense.upgradeLicensePlan);
        // Evento de clique no botão para comprar um upgrade
        jQuery(document).on('click', '[data-id="btn-plan-upgrade-buy"]', nobosslicense.openPlanstableModal);
        // Evento de clique no botão de renovar a licença
        jQuery('[data-id=license-alert-message]').on('click', '[data-id="btn-renew-license"]', nobosslicense.openPlanstableModal);
        // Evento de clique no botão de renovar a licença
        window.addEventListener("message", nobosslicense.filterIframeMessage, false);

        // Caso a flag de abrir modal de licença esteja habilitada valida se a url está autorizada ou existe um pgrade pronto para ser feito
        if (nobosslicense.licenseInfo.flags.modal_display_notice_license){
            // Abre a modal de aviso
            nobosslicense.displayModalNoticeLicense();
        }

        // Plano do usuario esta em versao gratuita: exibe mensagem no topo com link e cupom de desconto para adquirir um plano pago
        if((nobosslicense.licenseInfo.data.is_free != undefined) && (nobosslicense.licenseInfo.data.is_free == 1)){
            // TODO: migrar no futuro a exibicao desta e outras mensagens para funcao 'getLicenseInfo' do arquivo 'components\com_nbextensoes\controllers\externallicenses.raw.php'
            jQuery('#system-message-container').before(
                '<div data-id="license-alert-message" class="feedback-notice style="margin-top:-5px;">'+
                    '<span class="feedback-notice__icon fa fa-money"></span>'+
                    '<div class="feedback-notice__content">'+
                        '<p class="feedback-notice__message" data-id="license-alert-message-text">'+
                            Joomla.JText._('LIB_NOBOSS_FIELD_NOBOSSLICENSE_NOTICE_GET_PAID_VERSION')+
                        '</p>'+
                    '</div>'+
                '</div>'
            );
        }

        // Percorre a lista com as mensagens personalizadas e exibe cada uma em um alert para o usuário
        if (nobosslicense.licenseInfo.flags.modal_display_messages && nobosslicense.licenseInfo.data.messages !== false){
            // Percorre as mensagens vindas do servidor
            jQuery.each(nobosslicense.licenseInfo.data.messages, function(key, value){
                // Para cada uma delas, abre uma modal de alerta
                jQuery.alert({
                    title: value.title,
                    content: value.message
                });
            });
        }
    }

};
/**
 * 
 * @param {String} title  Titulo da modal de erro
 * @param {String} content Conteúdo da modal de erro
 * @param {Boolen} hideTab Boolen para saber se deve ou não esconder a aba de licença 
 */
nobosslicense.alertErrorModal = function(title, content, hideTab){
    // Vefifica se deve esconder a aba de licença
    if(hideTab == undefined || hideTab){
        // Esconde a aba de licença caso não tenha conseguido se conectar com o servidor
        jQuery(document).ready(function () {
            var licenseTab = jQuery('.nav.nav-tabs').find('[href="#attrib-license"]');
            // Caso exista aba de licença
            if (licenseTab.length != 0) {
                // Esconde a aba de licença
                licenseTab.parent().addClass('hidden');
            }
        });
    }

    // Exibe um alerta avisando que teve erro de conexão e não foi possivel obter o plano da extensão
    jQuery.alert({
        title: title,
        content: content,
        type: 'red',
        buttons: {
            ok: {
                keys: ['enter']
            }
        }
    });
};

/**
 *  Exibe uma modal informando que a url atual é inválida ou que existe um upgrade disponível
 */
nobosslicense.displayModalNoticeLicense = function(){
    var expiringOrExpired = {};
    var urlOrUpgrade = {};

    // Caso a licença tenha expirado
    if (nobosslicense.licenseInfo.data.inside_support_updates_expiration == 0 && !util.getCookie('expired_license_modal') && !nobosslicense.licenseInfo.flags.has_parent_license) {
        expiringOrExpired = {
            title: Joomla.JText._('LIB_NOBOSS_FIELD_NOBOSSLICENSE_MODAL_EXPIRED_LICENSE_TITLE'),
            content: Joomla.JText._('LIB_NOBOSS_FIELD_NOBOSSLICENSE_MODAL_EXPIRED_LICENSE_DESC'),
            type: 'red',
            closeIcon: 'close',
            buttons: {
                close:{
                    keys: ['esc'],
                    text: Joomla.JText._('LIB_NOBOSS_FIELD_NOBOSSLICENSE_MODAL_EXPIRED_LICENSE_CLOSE')
                },
                renew:{
                    text: Joomla.JText._('LIB_NOBOSS_FIELD_NOBOSSLICENSE_MODAL_EXPIRED_LICENSE_RENEW'),
                    keys: ['enter'],
                    btnClass: 'btn-blue',
                    action: function(){
                        nobosslicense.openPlanstableModal();
                    }
                }
            }
        };
        // Adiciona um cookie
        util.setCookie('expired_license_modal', true, 1);
    // Caso falte sete dias ou menos para a licença expirar
    } else if ( nobosslicense.licenseInfo.data.updates_near_to_expire && !util.getCookie('expiring_license_modal') && !nobosslicense.licenseInfo.flags.has_parent_license){
        expiringOrExpired = {
            title: Joomla.JText._('LIB_NOBOSS_FIELD_NOBOSSLICENSE_MODAL_EXPIRING_LICENSE_TITLE'),
            content: Joomla.JText._('LIB_NOBOSS_FIELD_NOBOSSLICENSE_MODAL_EXPIRING_LICENSE_DESC'),
            type: 'blue',
            closeIcon: 'close',
            buttons: {
                close:{
                    keys: ['esc'],
                    text: Joomla.JText._('LIB_NOBOSS_FIELD_NOBOSSLICENSE_MODAL_EXPIRING_LICENSE_CLOSE')
                },
                renew:{
                    text: Joomla.JText._('LIB_NOBOSS_FIELD_NOBOSSLICENSE_MODAL_EXPIRING_LICENSE_RENEW'),
                    keys: ['enter'],
                    btnClass: 'btn-blue',
                    action: function(){
                        nobosslicense.openPlanstableModal();
                    }
                }
            }
        };
        // Adiciona um cookie
        util.setCookie('expiring_license_modal', true, parseInt(nobosslicense.licenseInfo.data.days_to_expire_support_updates) - 1);
    }
    
    if (!nobosslicense.licenseInfo.data.isAuthorizedUrl && !util.getCookie('unauthorized_url')){
        urlOrUpgrade = {
            title: Joomla.JText._('LIB_NOBOSS_FIELD_NOBOSSLICENSE_MODAL_UNAUTHORIZED_URL_TITLE'),
            content: Joomla.JText._('LIB_NOBOSS_FIELD_NOBOSSLICENSE_MODAL_UNAUTHORIZED_URL_DESC'),
            type: 'red',
            closeIcon: 'keepLicenseUrl',
            buttons: {
                keepLicenseUrl:{
                    keys: ['esc'],
                    text: Joomla.JText._('LIB_NOBOSS_FIELD_NOBOSSLICENSE_MODAL_UNAUTHORIZED_URL_BUTTON_KEEP_URL')
                },
                updateLicenseUrl:{
                    text: Joomla.JText._('LIB_NOBOSS_FIELD_NOBOSSLICENSE_MODAL_UNAUTHORIZED_URL_BUTTON_UPDATE_URL'),
                    keys: ['enter'],
                    btnClass: 'btn-blue',
                    action: function(){
                        nobosslicense.changeAuthorizedUrlAction();
                    }
                }
            }
        };
        // Adiciona um cookie
        util.setCookie('unauthorized_url', true, 1);
    } else if (nobosslicense.licenseInfo.data.has_upgrade == 1) {
        urlOrUpgrade = {
            title: Joomla.JText._('LIB_NOBOSS_FIELD_NOBOSSLICENSE_MODAL_UPGRADE_AVAILABLE_DOWNLOAD_TITLE'),
            content: Joomla.JText._('LIB_NOBOSS_FIELD_NOBOSSLICENSE_MODAL_UPGRADE_AVAILABLE_DOWNLOAD_DESC'),
            type: 'blue',
            closeIcon: 'upgradeLater',
            buttons: {
                upgradeLater: {
                    keys: ['esc'],
                    text: Joomla.JText._('LIB_NOBOSS_FIELD_NOBOSSLICENSE_MODAL_UPGRADE_AVAILABLE_DOWNLOAD_LATER_BUTTON')
                },
                upgradeNow: {
                    text: Joomla.JText._('LIB_NOBOSS_FIELD_NOBOSSLICENSE_MODAL_UPGRADE_AVAILABLE_DOWNLOAD_NOW_BUTTON'),
                    btnClass: 'btn-blue',
                    action: function(){
                        nobosslicense.upgradeLicensePlanAction();
                    }
                }
            }
        };
    }
    
    var obj1 = {
        title: 'Error',
        type: 'blue'
    };

    if(!jQuery.isEmptyObject(expiringOrExpired)){
        jQuery.confirm(jQuery.extend(obj1, expiringOrExpired));
        // Abre uma modal quando a licença expirou ou falta menos de uma semana para expirar
    }
    if(!jQuery.isEmptyObject(urlOrUpgrade)){
        // Abre uma modal de aviso sobre url não autorizada ou caso haja alguma atualização
        jQuery.confirm(jQuery.extend(obj1, urlOrUpgrade));
    }
};

/**
 * Evento que muda a url autorizada fazendo uma requisição para o servidor
 * 
 * @param Event event Evento de clique no botão de atualiza url autorizada
 */
nobosslicense.changeAuthorizedUrl = function(event){
    event.preventDefault();
    // Chama a função que faz a troca de url
    nobosslicense.changeAuthorizedUrlAction();
};

/** 
 * Atualiza a url autorizada para uso da extensão. A nova url será a que o usuário está atualmente
*/
nobosslicense.changeAuthorizedUrlAction = function () {
    // Cria uma modal para o usuário confirmar a ação de mudar a url autorizada
    jQuery.confirm({
        title: Joomla.JText._('LIB_NOBOSS_FIELD_NOBOSSLICENSE_MODAL_CHANGE_LICENSE_URL_TITLE'),
        content: Joomla.JText._('LIB_NOBOSS_FIELD_NOBOSSLICENSE_MODAL_CHANGE_LICENSE_URL_DESC'),
        type: 'blue',
        closeIcon: 'cancel',
        escapeKey: 'cancel',
        buttons: {
            cancel: {
                keys: ['esc'],
                text: Joomla.JText._('LIB_NOBOSS_FIELD_NOBOSSLICENSE_MODAL_CHANGE_LICENSE_URL_BUTTON_CANCEL')
            },
            confirm: {
                text: Joomla.JText._('LIB_NOBOSS_FIELD_NOBOSSLICENSE_MODAL_CHANGE_LICENSE_URL_BUTTON_CONFIRM'),
                btnClass: 'btn-blue',
                keys: ['enter'],
                action: function () {
                    jQuery.ajax({
                        url: Joomla.JText._('NOBOSS_EXTENSIONS_URL_SITE') + '/index.php?option=com_nbextensoes&task=externallicenses.changeAuthorizedUrl&format=raw',
                        type: 'POST',
                        timeout: 30000,
                        data: {
                            token: nobosslicense.licenseInfo.data.token,
                            newUrl: nobosslicense.licenseInfo.data.siteUrl
                        },
                        beforeSend: function () {
                            util.addLoader();
                        }
                    }).done(function (response) {
                        if (response == 'true') { //jshint ignore:line
                            var alertDiv = jQuery('[data-id="license-alert-message"]').remove();
                            jQuery.alert({
                                title: Joomla.JText._('LIB_NOBOSS_FIELD_NOBOSSLICENSE_MODAL_CHANGE_LICENSE_URL_UPDATE_SUCESS_TITLE'),
                                content: Joomla.JText._('LIB_NOBOSS_FIELD_NOBOSSLICENSE_MODAL_CHANGE_LICENSE_URL_UPDATE_SUCESS_DESC'),
                                type:"blue"
                            });
                        } else {
                            jQuery.alert({
                                title: Joomla.JText._('LIB_NOBOSS_FIELD_NOBOSSLICENSE_MODAL_CHANGE_LICENSE_URL_UPDATE_ERROR_TITLE'),
                                content: Joomla.JText._('LIB_NOBOSS_FIELD_NOBOSSLICENSE_MODAL_CHANGE_LICENSE_URL_UPDATE_ERROR_DESC'),
                                type: "red"
                            });
                        }

                        setTimeout(function () {
                            util.removeLoader();
                        }, 400);
                    });

                }
            }
        }
    });
};

/**
 * Recebe o evento de clique no botão de upgrade e chama a função para iniciar o processo
 * 
 * @param Event event Evento de clique 
 */
nobosslicense.upgradeLicensePlan = function(event){
    if (nobosslicense.licenseInfo.data.has_upgrade != 1){
        return;
    }
    event.preventDefault();
    // Chama a função que faz a atualização de plano
    nobosslicense.upgradeLicensePlanAction();
};

/**
 * Abre uma modal com a listagem dos planos disponíveis para o usuário renovar ou fazer um upgrade
 */
nobosslicense.openPlanstableModal = function(e){
    var processType = 'renew';
    // Caso seja um evento
    if (e != undefined) {
        e.preventDefault();
        if(jQuery(this).data('id') === 'btn-plan-upgrade-buy'){
            processType = 'upgrade';
        }
    }

    var token = nobosslicense.licenseInfo.data.token;
    var plan = nobosslicense.licenseInfo.data.id_local_plan;
    var iframeUrl = Joomla.JText._('NOBOSS_EXTENSIONS_URL_SITE') + '/buy-process/planstable?token='+token+'&processtype='+processType;

    // armazena posição atual da tela 
    $currentScrollPos = jQuery(window).scrollTop();
    // atualiza o top do body para a posição armazenada para que quando for adicionado a classe que muda o position para fixed ele se mantenha posicionado onde o usuário estava
    jQuery('body').css('top', -$currentScrollPos);

    //cria os elementos
    var modalElement = jQuery('<div>').addClass('buy-process-modal').hide().fadeIn('normal');
    var modalShadowElement = jQuery('<div>').addClass('modal-shadow').hide().fadeIn('normal');
    var modalCloseButton = jQuery('<span>').addClass('btn-close fa fa-times');
    var iframeElement = jQuery('<iframe>').attr('src', iframeUrl).attr('scrolling', 'yes').addClass('buy-process-iframe');
    
    //adiciona a sombra
    modalShadowElement.appendTo('body');
    //monta a modal
    modalCloseButton.appendTo(modalElement);
    iframeElement.appendTo(modalElement);
    //mostra a modal
    modalElement.appendTo('body');
    // adiciona classe que coloca o estilo de fixed no body
    jQuery('body').toggleClass("inactive");
    
    //depois de criado, adiciona o evento de fechar no botao
    nobosslicense.addCloseListener();
};

nobosslicense.closePlanstableModal = function(){
    var modalElement = jQuery('.buy-process-modal');
    var modalShadowElement = jQuery('.modal-shadow');

    modalShadowElement.fadeOut(300, function(){
        jQuery(this).remove();
    });
    modalElement.fadeOut(250, function(){
        jQuery(this).remove();
    });

    //exclui cookie
    document.cookie = "modalPlanId=; expires=" + new Date() + "; path= /";
    //adiciona classe que evita o scroll da area externa
    jQuery('body').toggleClass("inactive");
    // tira o valor de top que havia sido colocado no momento da abertura da modal 
    jQuery('body').css('top', '');
};
nobosslicense.addCloseListener = function(){
    jQuery(".btn-close").on('click', function(e){
        nobosslicense.closePlanstableModal();
    });
};


/**
 * Faz uma requisição para 
 */
nobosslicense.upgradeLicensePlanAction = function(){
    // abre uma modal de confirmação para a realização do upgrade
    jQuery.confirm({
        title: Joomla.JText._('LIB_NOBOSS_FIELD_NOBOSSLICENSE_MODAL_UPGRADE_CONFIRM_ACTION_TITLE'),
        content: Joomla.JText._('LIB_NOBOSS_FIELD_NOBOSSLICENSE_MODAL_UPGRADE_CONFIRM_ACTION_DESC'),
        type: 'blue',
        closeIcon: 'cancel',
        escapeKey: 'cancel',
        buttons: {
            cancel: {
                keys: ['esc'],
                text: Joomla.JText._('LIB_NOBOSS_FIELD_NOBOSSLICENSE_MODAL_UPGRADE_CONFIRM_ACTION_BUTTON_CANCEL')
            },
            confirm: {
                text: Joomla.JText._('LIB_NOBOSS_FIELD_NOBOSSLICENSE_MODAL_UPGRADE_CONFIRM_ACTION_BUTTON_CONFIRM'),
                btnClass: 'btn-blue',
                keys: ['enter'],
                action: function () {
                    // Faz o ajax para o upgrade
                    jQuery.ajax({
                        url: "../index.php?option=com_nobossajax&library=noboss.forms.fields.nobosslicense.nobosslicense&method=upgradeLicensePlan&format=raw",
                        type: 'POST',
                        timeout: 30000,
                        data: {
                            token: nobosslicense.licenseInfo.data.token,
                            plan: nobosslicense.licenseInfo.data.id_local_plan
                        },
                        beforeSend: function () {
                            //NBTODO: Adicionar o esquema de ir exibindo várias frases enquanto carrega (se conseguir ,exibir o status do download tb)
                            util.addLoader();
                        }
                    }).done(function (response) {
                        // Decoda o json
                        response = JSON.parse(response);
                        // Exibe uma mensagem de alerta para o usuário
                        jQuery.alert({
                            title: Joomla.JText._('LIB_NOBOSS_FIELD_NOBOSSLICENSE_UPGRADE_PLAN_TITLE'),
                            content: response.message,
                            type: response.success == 1 ? 'blue' : 'red',
                            buttons: {
                                ok: {
                                    keys: ['enter'],
                                    action: function(){
                                        setTimeout(function () {
                                            location.reload();
                                        }, 500);
                                    }
                                }
                            }
                        });

                    }).fail(function () {
                        // Exibe um alerta avisando que teve erro de conexão
                        jQuery.alert({
                            title: Joomla.JText._('LIB_NOBOSS_FIELD_NOBOSSLICENSE_MODAL_INITIAL_CONNECTION_ERROR_TITLE'),
                            content: Joomla.JText._('LIB_NOBOSS_FIELD_NOBOSSLICENSE_MODAL_INITIAL_CONNECTION_ERROR_DESC'),
                            type: 'red',
                            buttons: {
                                ok: {
                                    keys: ['enter']
                                }
                            }
                        });
                    }).always(function () {
                        // Remove o loader
                        setTimeout(function () {
                            util.removeLoader();
                        }, 400);
                    });
                }
            }
        }
    });
};

/** 
 * Função que recebe a mensagem do iframe e executa metodos de acordo com ela
 */
nobosslicense.filterIframeMessage = function(e){
    var data = e.data;

    if(e.data !== undefined){
        if(e.data.msgType == 'refresh'){
            nobosslicense.warnAndRefreshPage(data);
        } else if (e.data.msgType == 'installNewExtensions') {
            nobosslicense.installNewExtensions(data);
        }
    }

};

/** 
 * Exibe um aviso e atualiza a página
*/
nobosslicense.warnAndRefreshPage = function(data) {
    jQuery.alert({
        title: Joomla.JText._('LIB_NOBOSS_FIELD_NOBOSSLICENSE_MODAL_PAGE_REFRESH_ALERT_TITLE'),
        content: Joomla.JText._('LIB_NOBOSS_FIELD_NOBOSSLICENSE_MODAL_PAGE_REFRESH_ALERT_DESC'),
        buttons: {
            ok: {
                keys: ['enter'],
                action: function(){
                    setTimeout(function () {
                        location.reload();
                    }, 500);
                }
            }
        }
    });
};

/**
 * Acionado quando o plano é trocado e existem novas extensões no novo plano para serem instaladao
 */
nobosslicense.installNewExtensions = function(data){

    if(typeof(data) == 'string' || data.new_extensions == undefined || data.new_extensions.length == 0){
        return;
    }

    // abre uma modal de confirmação para a realização das instalações
    jQuery.confirm({
        title: Joomla.JText._('LIB_NOBOSS_FIELD_NOBOSSLICENSE_MODAL_INSTALL_NEW_CONFIRM_ACTION_TITLE'),
        content: Joomla.JText._('LIB_NOBOSS_FIELD_NOBOSSLICENSE_MODAL_INSTALL_NEW_CONFIRM_ACTION_DESC'),
        type: 'blue',
        buttons: {
            confirm: {
                text: Joomla.JText._('LIB_NOBOSS_FIELD_NOBOSSLICENSE_MODAL_INSTALL_NEW_CONFIRM_ACTION_BUTTON_CONFIRM'),
                btnClass: 'btn-blue',
                keys: ['enter'],
                action: function () {
                    jQuery(".btn-close").trigger('click');
                    // Faz o ajax para o upgrade
                    jQuery.ajax({
                        url: "../index.php?option=com_nobossajax&library=noboss.forms.fields.nobosslicense.nobosslicense&method=installNewExtension&format=raw",
                        type: 'POST',
                        timeout: 30000,
                        data: {
                            newExtUrl: data.new_extensions
                        },
                        beforeSend: function () {
                            util.addLoader();
                        }
                    }).done(function (response) {
                        // Decoda o json
                        response = JSON.parse(response);
                        // Exibe uma mensagem de alerta para o usuário
                        jQuery.alert({
                            title: Joomla.JText._('LIB_NOBOSS_FIELD_NOBOSSLICENSE_MODAL_INSTALL_NEW_CONFIRM_ACTION_TITLE'),
                            content: response.message,
                            type: response.success == 1 ? 'blue' : 'red',
                            buttons: {
                                ok: {
                                    keys: ['enter'],
                                    action: function(){
                                        setTimeout(function () {
                                            location.reload();
                                        }, 500);
                                    }
                                }
                            }
                        });

                    }).fail(function () {
                        // Exibe um alerta avisando que teve erro de conexão
                        jQuery.alert({
                            title: Joomla.JText._('LIB_NOBOSS_FIELD_NOBOSSLICENSE_MODAL_INITIAL_CONNECTION_ERROR_TITLE'),
                            content: Joomla.JText._('LIB_NOBOSS_FIELD_NOBOSSLICENSE_MODAL_INITIAL_CONNECTION_ERROR_DESC'),
                            type: 'red',
                            buttons: {
                                ok: {
                                    keys: ['enter']
                                }
                            }
                        });
                    }).always(function () {
                        // Remove o loader
                        setTimeout(function () {
                            util.removeLoader();
                        }, 400);
                    });

                }
            }
        }
    });
};
