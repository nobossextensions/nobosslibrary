jQuery(document).ready(function (jQuery) {
    // Declara $ para evitar conflito
    $ = jQuery;
    // Scripts que são necessários estarem carregados na página
    var scripts = [
        "../libraries/noboss/assets/util/js/min/reactivatesubformfields.min.js",
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
        nobosstheme.CONSTRUCTOR();
    });
});

var nobosstheme = {};

nobosstheme.CONSTRUCTOR = function () {
    // variaveis vindas do PHP
    nobosstheme.themeInfos = Joomla.getOptions("nobosstheme");
    
    nobosstheme.token = jQuery('#license_token').val();

    // Cria o evento para carregar a modal
    jQuery('body').on('click', '[data-id="noboss-theme-button"]', nobosstheme.loadThemeModal);
    // Clique no botão de "Cancelar" ou no "X" da modal
    jQuery('body').on('click', '[data-id="theme-modal"] [data-id="theme-modal-button-cancel"]', nobosstheme.cancelThemeModal);
    // Clique no botão de confirmar
    jQuery('body').on('click', '[data-id="theme-modal"] [data-id="theme-modal-button-confirm"]', nobosstheme.confirmThemeModal);
    // Clique em algum dos exemplos de temas
    jQuery('body').on('click', '[data-id="theme-modal"] [data-id="sample-option"]', nobosstheme.selectSample);

    // verifica se existe um tema selecionado
    if (jQuery('[data-id="noboss-theme-selected"]').val()) {
        // pega o tema
        theme = jQuery('[data-id="selected-theme"]').find('[data-id="selected-theme-img"]').data('theme');
        // Troca o texto que fica ao lado do botão para o nome do modelo escolhido
        jQuery('[data-id="noboss-theme-selected"]').val(jQuery('[data-id=theme-option][data-value=' + theme + ']').find('p').html());
    }

    // verifica se o input jform_module nao existe na pagina
    if (jQuery('#jform_module').val() === undefined) {
        // se nao existir, pega o valor que foi definido via php
        nobosstheme.extensionName = nobosstheme.themeInfos.extName;
        // seta a flag de componente como true
        nobosstheme.isComponent = true;
    } else {
        // se existir, pega o valor do campo removendo o prefixo
        nobosstheme.extensionName = jQuery('#jform_module').val().replace('mod_noboss', '');
        // seta a flag de componente como false
        nobosstheme.isComponent = false;
    }

    // Configurações globais para a modal de alerta e confimação
    jconfirm.defaults = {
        draggable: false,
        useBootstrap: false,
        columnClass: "modal-confirm-resposive",
        animateFromElement: false
    };

    // Pega o primeiro form da pagina, que é o form principal
    var form = jQuery('body').find('form')[0];
    // Evento de interceptacao do evento de "save" do Joomla
    jQuery(form).on('submit', function (event) {
        // Pega o campo hidden comdo tema
        var hiddenThemeInput = jQuery('[data-id="theme-modal-input"]');
        // atualiza o valor do input hidden de tema
        hiddenThemeInput.val(JSON.stringify(hiddenThemeInput.data('value')));
    });

    jQuery('body').find('[data-id="theme-modal"] [data-target]').on('click', function () {
        // coloca ou tira a classe para mudar o icone
        jQuery(this).toggleClass('grow');
        // pega o alvo atual que deve ser modificado
        var target = jQuery(this).data('target');
        // procura no corpo da modal e faz efeito de esconder/mostrar
        jQuery(this).parent().find(target).slideToggle();
    });

    // Cuida o resize para quando for mobile, abrir todas as listas de exemplo colapsadas
    jQuery(window).resize(function () {
        if (jQuery(window).width() < 660) {
            jQuery('[data-id="theme-modal"]').find('[data-id="theme-option"]').children('.theme-name').each(function () {
                if (!jQuery(this).hasClass('grow')) {
                    jQuery(this).trigger('click');
                }
            });
        }
    });
};

/**
 * Evento do carregamento inicial dos exemplos de temas da modal
 *
 * @param {Event} event Evento
 */
nobosstheme.loadThemeModal = function (event) {
    event.preventDefault();
    var that = jQuery(this);

    var themeModal = jQuery(this).closest('.controls').find('[data-id="theme-modal"]');
    // Variavel que define se dados de exemplo devem ser carregados.
    var loadSampleData = jQuery(themeModal).parent().find('[data-id="theme-modal-input"]').attr('data-load-sample-data');

    // Caso os exemplos não tenham sido carregados ainda
    if ((jQuery(themeModal).data('loaded') !== true) && (loadSampleData == 1)) {
        jQuery.ajax({
            url: Joomla.JText._('NOBOSS_EXTENSIONS_URL_SITE') + '/index.php?option=com_nbextensoes&task=externalthemes.getThemesSamples&format=raw',
            method: "POST",
            timeout: 8000,
            data: {
                token: nobosstheme.token,
                extension: nobosstheme.extensionName,
                language: jQuery(that).closest('.controls').find('[data-id="theme-modal-input"]').data('language')
            }
        }).done(function (response) {
            var samples;
            // Tenta decodar a resposta, que espera-se que seja um json
            try {
                samples = JSON.parse(response);
            } catch (e) {
                // Caso tenha ocorrido erro no decode, exibe o alerta
                jQuery(themeModal).find('[data-id="notification"]').removeClass('hidden').removeClass('alert-info').addClass('alert-warning').html(Joomla.JText._('LIB_NOBOSS_FIELD_NOBOSSTHEME_MODAL_NBSERVER_CONNECTION_ERROR'));
            }

            // Verifica se o json foi decodado
            if (samples !== undefined) {
                var themeList = jQuery(themeModal).find('[data-id="theme-list"]');
                // Percorre todos os temas
                jQuery.each(samples, function (themeName, themeSamples) {
                    // Pega a lista com todas as ptios de tema
                    var themeOption = jQuery(themeList).find('[data-id="theme-option"][data-value="' + themeName + '"]');
                    // Perorre todos os exemplos do tema atual
                    jQuery.each(themeSamples, function (sampleId, sampleImg) {
                        var options = jQuery(themeOption).find('[data-id="sample-list"]').find('[data-id="sample-option"]');
                        // Verifica se a amostra já existe na página
                        var existentSample = jQuery(themeOption).find('[data-id="sample-list"]').find('[data-id="sample-option"][data-value="' + sampleId + '"]');
                        if (existentSample.length !== 0) {
                            // Caso a imagem vinda do servidor esteja vazia
                            if (sampleImg.length !== 0) {
                                existentSample.find('img').attr('src', sampleImg);
                            }
                            // Caso não exista
                        } else {
                            // Clona o primeiro item
                            var clonedOption = jQuery(themeOption).find('[data-id="sample-list"]').find('[data-id="sample-option"]').first().clone();
                            // Atualiza os valores
                            clonedOption.attr('data-value', sampleId);
                            clonedOption.removeClass('selected');
                            // Caso a imagem vinda do servidor esteja vazia
                            if (sampleImg.length !== 0) {
                                clonedOption.find('img').attr('src', sampleImg);
                            }
                            // Adiciona após a ultima options
                            jQuery(options).last().after(clonedOption);
                        }
                    });
                });
            }
        }).fail(function (e) {
            // Adiciona um erro no topo da modal de falha na comunicação com o servidor
            jQuery(themeModal).find('[data-id="notification"]').removeClass('hidden').removeClass('alert-info').addClass('alert-warning').html(Joomla.JText._('LIB_NOBOSS_FIELD_NOBOSSTHEME_MODAL_NBSERVER_CONNECTION_ERROR'));
        }).always(function () {
            // Independente de resposta, abre a modal
            // Percorre os exemplos para bloquear os que o user nao tem acesso
            nobosstheme.classifyPlansAndSupport(themeModal);
            nobosstheme.freezeListGif(themeModal);
            nobosstheme.openThememodal(themeModal);
            themeModal.data('loaded', true);
        });
    } else {
        // Percorre os exemplos para bloquear os que o user nao tem acesso
        nobosstheme.classifyPlansAndSupport(themeModal);
        nobosstheme.freezeListGif(themeModal);
        nobosstheme.openThememodal(themeModal);
    }
};

nobosstheme.openThememodal = function (themeModal) {
    // Abre a modal
    themeModal.removeClass('hidden');
    jQuery('body').addClass('modal--is-open');
};

nobosstheme.closeThemeModal = function (themeModal) {
    // Fecha a modal
    themeModal.addClass('hidden');
    jQuery('body').removeClass('modal--is-open');
};


// Evento ativado quando é clicado em algum dos modelos disponíveis
nobosstheme.selectSample = function (event) {
    var that = jQuery(this);
    var themeModal = jQuery(that).closest('[data-id="theme-modal"]');

    // Remove a classe selected do anterior e adiciona no clicado
    jQuery(that).closest('[data-id="theme-list"]').find('[data-id="sample-option"]').removeClass('selected');
    jQuery(that).addClass('selected');

    // Troca a imagem principal e o valor do item selecionado
    var mainSelectedThemeImg = jQuery(that).closest('.nb-modal-body').find('[data-id="selected-theme"]').find('[data-id="selected-theme-img"]');
    mainSelectedThemeImg.data('theme', that.closest('[data-id="theme-option"]').data('value'));
    mainSelectedThemeImg.data('sample', that.data('value'));
    mainSelectedThemeImg.attr('src', that.find('img').attr('src'));

    // Verifica se o item escolhido está bloqueado
    if (jQuery(that).data('blocked') === true) {
        // Adiciona o attr disabled e remove os pointer events
        jQuery(themeModal).find('.nb-modal-footer').find('[data-id="theme-modal-button-confirm"]').css('pointer-events', 'none').attr('disabled', true);
        // Caso tenha ocorrido erro no decode, exibe o alerta
        jQuery(themeModal).find('[data-id="notification"]').html(Joomla.JText._('LIB_NOBOSS_FIELD_NOBOSSTHEME_MODAL_UNAVAILABLE_SAMPLE')).removeClass('hidden').removeClass('alert-warning').addClass('alert-info');
    } else {
        // Esconde
        jQuery(themeModal).find('[data-id="notification"]').addClass('hidden');
        // Desbloqueia o botão de confirmar a modal
        jQuery(themeModal).find('.nb-modal-footer').find('[data-id="theme-modal-button-confirm"]').css('pointer-events', 'auto').attr('disabled', false);
        // Caso esteja no mobile, triggera um clique no botão de concluido
        if (jQuery(window).width() < 660) {
            jQuery(themeModal).find('[data-id="theme-modal-button-confirm"]').trigger('click');
        }
    }
};

/**
 * Recebe a modal de temas e congela todos os gif da listagem
 *
 * @param {jQuery Element} themeModal
 */
nobosstheme.freezeListGif = function (themeModal) {
    // Filtra por todas as imagens que são gifs

    if (themeModal.data('loaded') !== true) {
        // Percorre as imagens dos temas
        jQuery(themeModal).find('[data-id="theme-list"]').find('[data-id="sample-option"] .sample-img').each(function () {
            // compia o valor do src para o data-src
            // Verifica se a imgem é um gif
            if (/^(?!data:).*\.gif/i.test(jQuery(this).attr('src'))) {
                // Pega o elemento img
                var img = jQuery(this)[0];
                // Cria a função de congelamento
                var freeze = function () {
                    // Cria o canvas e define sua altura e largura
                    var canvas = document.createElement('canvas');
                    var height = canvas.height = img.height;
                    var width = canvas.width = img.width;
                    // Desenha o canvas com base na imagem
                    canvas.getContext('2d').drawImage(img, 0, 0, width, height);
                    // Percorre os atributos da imagem replicando no canvas
                    for (i = 0; i < img.attributes.length; i++) {
                        attr = img.attributes[i];
                        if (attr.name !== '"') { // test for invalid attributes
                            canvas.setAttribute(attr.name, attr.value);
                        }
                    }
                    // Adiciona o canvas na página
                    canvas.style.position = 'absolute';
                    img.parentNode.insertBefore(canvas, img);
                    img.style.opacity = 0;
                };

                // Ao terminar de carregar a imagem, congela
                if (img.complete) {
                    freeze();
                } else {
                    img.addEventListener('load', freeze, true);
                }
            }
        });
    }
};


/**
 * Cria um json com o exemplo escolhido na modal e insere no data-value do campo hidden,
 * e troca o value do mesmo campo pelo modelo escolhido, dando um trigger nele
 *
 * @param {Event} event Evento
 */
nobosstheme.confirmThemeModal = function (event) {
    event.preventDefault(); 
    
    var themeModal = jQuery(this).closest('[data-id="theme-modal"]');

    // Constante default de mensagem para confirmacao da troca de tema
    var content = Joomla.JText._('LIB_NOBOSS_FIELD_NOBOSSTHEME_MODAL_RESET_VALUES_DESC');

    // Constante de mensagem definida na propria extensao: sobreescreve o valor de content acima
    if (Joomla.JText._('FIELD_NOBOSSTHEME_MODAL_RESET_VALUES_DESC') && (Joomla.JText._('FIELD_NOBOSSTHEME_MODAL_RESET_VALUES_DESC') != '')){
        content = Joomla.JText._('FIELD_NOBOSSTHEME_MODAL_RESET_VALUES_DESC');
    }
    
    jQuery.confirm({
        title: Joomla.JText._('LIB_NOBOSS_FIELD_NOBOSSTHEME_MODAL_RESET_VALUES_LABEL'),
        content: content,
        type: 'blue',
        closeIcon: 'cancel',
        escapeKey: 'cancel',
        buttons: {
            cancel: {
                keys: ['esc'],
                text: Joomla.JText._('LIB_NOBOSS_FIELD_NOBOSSTHEME_MODAL_CANCEL_BUTTON'),
                action: function () {
                    // Fecha a modal
                    nobosstheme.closeThemeModal(themeModal);
                }
            },
            confirm: {
                text: Joomla.JText._('LIB_NOBOSS_FIELD_NOBOSSTHEME_MODAL_CONFIRM_BUTTON'),
                btnClass: 'btn-blue',
                keys: ['enter'],
                action: function () {
                    var imageElement = themeModal.find('[data-id="selected-theme"]').find('[data-id="selected-theme-img"]');
                    // Monta o value que será salvo
                    var value = {
                        theme: imageElement.data('theme'),
                        sample: {
                            id: imageElement.data('sample'),
                            img: imageElement.attr('src')
                        }
                    };
                    // Atualiza os valores do campo hidden e simula um change para ativar o showon
                    jQuery(themeModal).parent().find('[data-id="theme-modal-input"]').attr('data-value', JSON.stringify(value)).val(value.theme);
                    // jQuery(themeModal).parent().find('[data-id="theme-modal-input"]').attr('data-value', JSON.stringify(value)).val(value.theme).trigger('change');
                    // Troca o texto que fica ao lado do botão para o nome do modelo escolhido
                    jQuery(themeModal).parent().find('[data-id="noboss-theme-selected"]').val(jQuery(themeModal).find('[data-id=theme-option][data-value=' + value.theme + ']').find('p').html());

                    // Caso o token esteja vazio, avisa o usuário e nem busca pelos exemplos
                    if((typeof nobosstheme.token != 'undefined') && (nobosstheme.token.length === 0)){
                        // Abre uma modal avisando que a licença é inválida ou esta expirada
                        nobosstheme.invalidOrExpiratedLicense(Joomla.JText._('LIB_NOBOSS_FIELD_NOBOSSTHEME_GENERATE_EXAMPLES_ERROR_TOKEN_NOT_VALID'));
                    } 
                    // Definido que os dados de exemplo devem ser setados
                    else if(jQuery(themeModal).parent().find('[data-id="theme-modal-input"]').attr('data-load-sample-data') == 1){
                        // verifica se a extensao eh um componente
                        if (nobosstheme.isComponent) {
                            nobosstheme.generateComponentDefaultExamples(themeModal);
                        } else {
                            nobosstheme.generateModuleDefaultExamples(themeModal);
                        }
                    }
                    // Fecha a modal
                    nobosstheme.closeThemeModal(themeModal);
                }
            }
        }
    });


};

/**
 * Seleciona o exemplo que estava selecionado antes de abrir a modal e volta para ele
 *
 * @param {Event} event
 */
nobosstheme.cancelThemeModal = function (event) {
    event.preventDefault();
    var themeModal = jQuery(this).closest('[data-id="theme-modal"]');
    var modalInputAttrSample = jQuery(themeModal).parent().find('[data-id="theme-modal-input"]').attr('data-value');
    // Caso o valor não esteja vazio
    if (modalInputAttrSample.length !== 0) {
        var value = JSON.parse(modalInputAttrSample);
        // Busca no value o item anteriormente clicado e triggera um click nele
        themeModal.find('[data-id="theme-option"][data-value="' + value.theme + '"]').find('[data-id="sample-option"][data-value="' + value.sample.id + '"]').trigger('click');
    }
    // Fecha a modal
    nobosstheme.closeThemeModal(themeModal);
};

/**
 * Percorre as options do campo de tema e classifica como PRO ou não
 */
nobosstheme.classifyPlansAndSupport = function (themeModal) {
    // Percorre o select dos temas, validando cada item para saber se está disponível no plano atual
    jQuery(themeModal).find('[data-id="theme-list"] [data-id="theme-option"]').each(function () {
        var avaliable;
        // Caso o item tenha plano 'dev' ou 'custom' ou tenha o value vazio, considera disponível
        if (jQuery(this).data('plan') === 'custom') {
            avaliable = true;
        } else {
            // Pega os planos no attr da option
            var plansOfOption = jQuery(this).data('plan').toString().split(',');
            avaliable = jQuery.inArray(jQuery('#extension_plan').val(), plansOfOption) !== -1;
        }
        // Salva a informação se o tema está ou não disponível para o plano atual da extensão
        jQuery(this).data('avaliable', avaliable);
        // Caso não esteja disponível, adiciona o estilo do cadeado
        if (!avaliable) {
            jQuery(this).find('[data-id="sample-option"]').addClass('blocked').data('blocked', true);
        } else {
            // Verifica se o usuário está no periodo do suporte
            if (jQuery('#license_update_support_period').val() !== '1' || jQuery('#license_state').val() !== '1') {
                // Altera os exemplos que não são o default, bloqueando eles
                jQuery(this).find('[data-id="sample-option"]').map(function (index, el) {
                    // verifica se é um modelo default e adiciona o bloqueado
                    if (jQuery(el).data('value').indexOf('default') === -1) {
                        jQuery(el).addClass('blocked').data('blocked', true);
                    }
                });
            }
        }
    });
};

/**
 * Cria uma modal de confirmação para criação de itens default,
 * fazendo a requisição e inserindo os mesmos em caso o usuário escolha sim
 *
 * @param {jQuery Object} themeModal
 */
nobosstheme.generateModuleDefaultExamples = function (themeModal) {
    var modalInputHidden = JSON.parse(jQuery(themeModal).parent().find('[data-id="theme-modal-input"]').attr('data-value'));
    var sampleId = modalInputHidden.sample.id;
    var model = modalInputHidden.theme;
    var selectedTheme = jQuery(themeModal).parent().find('[data-id="theme-modal-input"]').val();
    
    var loadModeSubform = jQuery("#itemsloadmode").find('option[value="'+jQuery("#itemsloadmode").val()+'"]').first().data('subform');
    if(loadModeSubform !== undefined){
        // troca a chave ##model## pelo tema escolhido
        loadModeSubform = loadModeSubform.replace('##model##', model);
    } else {
        loadModeSubform = [];
    }

    // subforms adicionais especificados no xml
    var subforms = JSON.parse(nobosstheme.themeInfos.subforms);
    // modais adicionais especificadas no xml
    var modals = JSON.parse(nobosstheme.themeInfos.modals);
    // fields adicionais especificados no xml
    var fields = JSON.parse(nobosstheme.themeInfos.fields);

    // array que armazenara os control-groups dos subforms que serão atualizados
    var controlGroups = [];

    // array que armazena os names de cada subform
    var subformNames = [];

    // verifica se foi definido algum subform adicional
    if (subforms !== undefined) {
        // percorre cada um
        jQuery(subforms).each(function (i, v) {
            // troca a chave de model pelo nome do tema escolhido
            v = v.replace('##model##', model);
            // verifica se esse subform existe
            if (jQuery('input[name="jform[params][' + v + ']"]').parents('.control-group').length != 0) {
                // se existir, adiciona ao array de subforms que sera enviado na requisicao
                subformNames.push(v);
                // adiciona ao array de control groups
                controlGroups.push(jQuery('input[name="jform[params][' + v + ']"]').parents('.control-group'));
            }
        });
    }
    
    // array que guardara as modais adicionais que devem ser geradas
    var addModals = [];

    // verifica se foi definido modais adicionais no xml
    if (modals !== undefined) {
        // percorre cada uma
        jQuery(modals).each(function (i, v) {
            // troca a chave de model pelo nome do tema escolhido
            v = v.replace('##model##', model);
            // verifica se existe essa modal
            if (jQuery('input[data-id="noboss-modal-input-hidden"][data-modal-name="' + v + '"]').length != 0) {
                // se existir, adiciona ao array de modais
                addModals.push(v);
            }
        });
    }

    var fieldsNames = [];

    var fieldsElements = [];

    // verifica se foi definido algum subform adicional
    if (fields !== undefined) {
        // percorre cada um
        jQuery(fields).each(function (i, v) {
            // verifica se esse subform existe
            if (jQuery('[name^="jform[params][' + v + ']"]').parents('.control-group').length != 0) {
                // se existir, adiciona ao array de subforms que sera enviado na requisicao
                fieldsNames.push(v);
                // adiciona ao array de control groups
                fieldsElements.push(jQuery('[name^="jform[params][' + v + ']"]').parents('.control-group'));
            }
        });
    }

    // verifica se foi definido modais adicionais no xml
    if (modals !== undefined) {
        // percorre cada uma
        jQuery(modals).each(function (i, v) {
            // troca a chave de model pelo nome do tema escolhido
            v = v.replace('##model##', model);
            // verifica se existe essa modal
            if (jQuery('input[data-id="noboss-modal-input-hidden"][data-modal-name="' + v + '"]').length != 0) {
                // se existir, adiciona ao array de modais
                addModals.push(v);
            }
        });
    }

    // Faz o ajax buscando pelos itens de exemplo
    jQuery.ajax({
        url: "../administrator/index.php?option=com_nobossajax&library=noboss.forms.fields.nobosstheme.nobosstheme&method=loadModuleSample&format=raw",
        data: {
            extensionName: nobosstheme.extensionName,
            itemsFormName: subformNames,
            addModals: addModals,
            fieldsNames: fieldsNames,
            lang: nobosstheme.themeInfos.langCode,
            loadModeSubform: loadModeSubform,
            sampleId: sampleId,
            model: model,
            token: nobosstheme.token
        },
        timeout: 20000,
        type: "POST",
        beforeSend: function () {
            var loader1 = jQuery('body').append('<div class="loader loader--fullpage"></div>');
            var loader2 = jQuery('body').append('<div class="loader-fade"></div>');
        }
    }).done(function (response) {
        var jsonResponse = false;
        try {
            jsonResponse = JSON.parse(response);
        } catch (e) {
            console.log('generateModuleDefaultExamples');
            console.log(response);
            jQuery.alert({
                title: Joomla.JText._('LIB_NOBOSS_FIELD_NOBOSSTHEME_GENERATE_EXAMPLES_ERROR_TITLE'),
                content: Joomla.JText._('LIB_NOBOSS_FIELD_NOBOSSTHEME_GENERATE_EXAMPLES_ERROR_JSON_PARSE_LOCAL')
            });
            // Em caso de erro no json não continua a execução
            return;
        }

        // Verifica se teve sucesso
        if(jsonResponse.success === 1){
            // Caso o token seja invalido, não inclua o plano ou o periodo de suporte tenha acabado
            if(jsonResponse.data === 'INVALID_TOKEN'){
                nobosstheme.invalidOrExpiratedLicense(jsonResponse.message);
            } else {
                // percorre cada modal
                jQuery.each(jsonResponse.data.modals, function(prop, val){
                    // localiza essa modal no html
                    var modal = jQuery(themeModal).closest("#myTabContent").find('[data-modal-name="' + prop + '"]');
                    // verifica se foi encontrada
                    if (modal.length !== 0) {
                        // atualiza seu valor
                        modal.val(val).siblings('[data-id="modal"]').remove();
                    }
                });

                // Verifica se existe o subform principal
                if(loadModeSubform !== undefined && loadModeSubform.length > 0) {
                    // seta a opcao correta no campo de load mode
                    jQuery("#itemsloadmode").val(jsonResponse.data.itemsloadmode);
                    // atualiza o chosen
                    jQuery('#itemsloadmode').trigger('liszt:updated');
                    jQuery('#itemsloadmode').trigger('change');

                    // chave onde se encontra o input hidden do subform "principal" vindo do servidor
                    var hiddenInputKey = Object.keys(jsonResponse.data.items)[Object.keys(jsonResponse.data.items).length - 1];

                    // pega o hidden input do subform
                    var hiddenInput = jQuery(jsonResponse.data.items[hiddenInputKey]).find('input[type="hidden"][name^="jform[params]"]')[0];
                    // pega o control group na pagina do subform que tem o mesmo input hidden vindo do servidor
                    var mainControlGroup = jQuery('body').find('input[type="hidden"][name="' + hiddenInput.name + '"]').closest('.control-group');

                    // adiciona esse control group ao array de control groups de subforms que serao substituidos
                    controlGroups.push(mainControlGroup);
                }

                var i = 0;
                jQuery.each(jsonResponse.data.fields, function(prop, val){
                    var field = jQuery(val);
                    // pega o id que sera substituido do item
                    var oldId = field.find('[name^="jform[params][' + prop + ']"]').attr('id');
                    // se tem fieldset pega o id dele
                    if(field.find('fieldset').length){
                        oldId = field.find('fieldset').attr('id');
                    }
                    // pega o id do field atual da pagina
                    var fieldId = jQuery(fieldsElements[i]).find('[name^="jform[params][' + prop + ']"]').attr('id');
                    // se tem fieldset pega o id dele
                    if(jQuery(fieldsElements[i]).find('fieldset').length){
                        fieldId = jQuery(fieldsElements[i]).find('fieldset').attr('id');
                    }
                    
                    /* deixa os atributos como o elemento antigo
                       isso eh preciso porque na renderizacao do campo os atributos 
                        ficam com um underline a mais nos nomes */
                    nobosstheme.replaceAttr(field.find('[name^='+oldId+']'), 'name', oldId, fieldId);
                    nobosstheme.replaceAttr(field.find('[id^='+oldId+']'), 'id', oldId, fieldId);
                    nobosstheme.replaceAttr(field.find('[for^='+oldId+']'), 'for', oldId, fieldId);
                    nobosstheme.replaceAttr(field.find('[href*='+oldId+']'), 'href', oldId, fieldId);

                    var newField = jQuery(fieldsElements[i]).replaceWith(field);

                    // reativa campos
                    setTimeout(function() {
                        jQuery('select').chosen();
                        jQuery(document).trigger("new-container-add", newField);
                    }, 400);

                    // jQuery(document).trigger('subform-row-add', jQuery(this));

                    //  // fix media field
                    // newField.find('a[onclick*="jInsertFieldValue"]').each(function () {
                    //     var jQueryel = jQuery(this),
                    //         inputId = jQueryel.siblings('input[type="text"]').attr('id'),
                    //         jQueryselect = jQueryel.prev(),
                    //         oldHref = jQueryselect.attr('href');
                    //     // update the clear button
                    //     jQueryel.attr('onclick', "jInsertFieldValue('', '" + inputId + "');return false;");
                    //     // update select button
                    //     jQueryselect.attr('href', oldHref.replace(/&fieldid=(.+)&/, '&fieldid=' + inputId + '&'));
                    // });

                    // // bootstrap based Media field
                    // if (jQuery.fn.fieldMedia) {
                    //     newField.find('.field-media-wrapper').fieldMedia();
                    // }

                    // // bootstrap tooltips
                    // if (jQuery.fn.popover) {
                    //     newField.find('.hasPopover').popover({ trigger: 'hover focus' });
                    // }

                    // // bootstrap based User field
                    // if (jQuery.fn.fieldUser) {
                    //     newField.find('.field-user-wrapper').fieldUser();
                    // }

                    // // Caso tenha o calendário na página
                    // if (window.Calendar){
                    //     elements = newField.find(".field-calendar");
                    //     // Percorre os calendários da modalElem e ativa
                    //     elements.each(function(){
                    //         JoomlaCalendar.init(this);
                    //     });
                    // }

                    i++;
                });

                setTimeout(function() {
                    util.fixScripts(jQuery(document));
                    util.activateYesno(jQuery(document));

                    // reativa modais
                    if(window.SqueezeBox && window.SqueezeBox.assign){
                        jQuery('a.modal').each(function(i, el){
                            SqueezeBox.assign(jQuery(el).get(), {parse: 'rel'});
                        });
                    }
                }, 400);

                // contador a ser utilizado no loop
                i = 0;
                //console.log(jsonResponse.data);
                // percorre cada subform vindo do servidor
                jQuery.each(jsonResponse.data.items, function (prop, val) {
                    // guarda o html
                    var subform = jQuery(val);

                    // verifica se o subform tem showon olhando pra um tema diferente do escolhido
                    if(nobosstheme.checkShowonDependencies(subform, model)){
                        // se tiver, esconde esse subform
                        jQuery(controlGroups[i]).css('display', 'none');
                        // incrementa contador
                        i++;
                        // pula pra proxima iteracao
                        return;
                    }

                    var oldId = "jform_params__";
                    var fieldId = "jform_params_";

                    nobosstheme.replaceAttr(subform.find('[name^='+oldId+']'), 'name', oldId, fieldId);
                    nobosstheme.replaceAttr(subform.find('[id^='+oldId+']'), 'id', oldId, fieldId);
                    nobosstheme.replaceAttr(subform.find('[for^='+oldId+']'), 'for', oldId, fieldId);
                    nobosstheme.replaceAttr(subform.find('[href*='+oldId+']'), 'href', oldId, fieldId);


                    // atualiza o html do subform da iteracao atual
                    jQuery(controlGroups[i]).replaceWith(subform);

                    // Reativa o evento para multiplos subforms
                    var subformRepeatable = subform.find('div.subform-repeatable');
                    try {
                        subformRepeatable.subformRepeatable();
                    } catch (e) {
                        // Cairá aqui caso o subform não seja do tipo repeatable
                    }

                    // Percorre cada item do subform dando trigger como se fosse uma nova linha
                    jQuery(subformRepeatable).find('.subform-repeatable-group').each(function () {
                        jQuery(this).data('new', true);
                        jQuery(this).find('select').chosen();
                        util.fixScripts(jQuery(this).parent('div.subform-repeatable'));
                        util.activateYesno(jQuery(this).parent('div.subform-repeatable'));
                        jQuery(document).trigger('subform-row-add', jQuery(this));
                        // verifica se a versao eh menor que 3.7.3 para fazer tratamento especial aos campos color
                        if(nobosstheme.themeInfos.lowerJVersion){
                            jQuery(this).find('.minicolors').each(function() {
                                $(this).minicolors({
                                    control: $(this).attr('data-control') || 'hue',
                                    format: $(this).attr('data-validate') === 'color' ? 'hex' : ($(this).attr('data-format') === 'rgba' ? 'rgb' : $(this).attr('data-format')) || 'hex',
                                    keywords: $(this).attr('data-keywords') || '',
                                    opacity: $(this).attr('data-format') === 'rgba' ? true : false || false,
                                    position: $(this).attr('data-position') || 'default',
                                    theme: 'bootstrap'
                                });
                            });
                        }
                    });
                    // Remove a classe dos campos minicolors para evitar que seja ativado duas vezes
                    var minicolors = jQuery(subformRepeatable).find('.minicolors').removeClass('minicolors');
                    
                    // Readiciona a classe minicolors
                    minicolors.addClass('minicolors');

                    // incrementa o contador
                    i++;
                });
            }
        } else {
            jQuery.alert({
                title: Joomla.JText._('LIB_NOBOSS_FIELD_NOBOSSTHEME_GENERATE_EXAMPLES_ERROR_TITLE'),
                content: jsonResponse.message
            });
        }
    }).fail(function () {
        jQuery.alert({
            title: Joomla.JText._('LIB_NOBOSS_FIELD_NOBOSSTHEME_GENERATE_EXAMPLES_ERROR_TITLE'),
            content: Joomla.JText._('LIB_NOBOSS_FIELD_NOBOSSTHEME_GENERATE_EXAMPLES_ERROR_UNREACHABLE')
        });
    }).always(function () {
        jQuery(themeModal).parent().find('[data-id="theme-modal-input"]').trigger('change');

        jQuery(document).trigger('subform-row-add', jQuery('section#content'));

        setTimeout(function () {
            jQuery('body').find('.loader.loader--fullpage').remove();
            jQuery('body').find('.loader-fade').remove();
            // esconde o campo de loadmode se tiver apenas uma opcao
            if (jQuery('#itemsloadmode').find('option').length <= 1){
                jQuery('#itemsloadmode').closest('.control-group').hide();
                jQuery("#itemsloadmode").find('option').first().attr('selected', true);
            }
        }, 400);
    });
};

/**
 * Substitui um atributo dos elementos
 *
 * @param {jQuery Object Array} elements
 * @param {string} attribute
 * @param {string} oldValue
 * @param {string} newValue
 */
nobosstheme.replaceAttr = function(elements, attribute, oldValue, newValue){
    jQuery(elements).each(function(i, el){
        var oldAttr = jQuery(el).attr(attribute);
        var newAttr = oldAttr.replace(oldValue, newValue);

        jQuery(el).attr(attribute, newAttr);
    });
};

/**
 * Cria uma modal de confirmação para criação de itens default,
 * fazendo a requisição e inserindo os mesmos em caso o usuário escolha sim
 *
 * @param {jQuery Object} themeModal
 */
nobosstheme.generateComponentDefaultExamples = function (themeModal) {
    var modalInputHidden = JSON.parse(jQuery(themeModal).parent().find('[data-id="theme-modal-input"]').attr('data-value'));
    var sampleId = modalInputHidden.sample.id;
    var model = modalInputHidden.theme;
    var selectedTheme = jQuery(themeModal).parent().find('[data-id="theme-modal-input"]').val();
    // Faz o ajax buscando pelos itens de exemplo
    jQuery.ajax({
        url: "../administrator/index.php?option=com_nobossajax&library=noboss.forms.fields.nobosstheme.nobosstheme&method=loadComponentSample&format=raw",
        data: {
            extensionName: nobosstheme.extensionName,
            sampleId: sampleId,
            model: model,
            token: nobosstheme.token
        },
        type: "POST",
        beforeSend: function () {
            var loader1 = jQuery('body').append('<div class="loader loader--fullpage"></div>');
            var loader2 = jQuery('body').append('<div class="loader-fade"></div>');
        }
    }).done(function (response) {
        
        var jsonResponse = false;
        try {
            jsonResponse = JSON.parse(response);
        } catch (e) {
            console.log('generateComponentDefaultExamples');
            console.log(response);
            jQuery.alert({
                title: Joomla.JText._('LIB_NOBOSS_FIELD_NOBOSSTHEME_GENERATE_EXAMPLES_ERROR_TITLE'),
                content: Joomla.JText._('LIB_NOBOSS_FIELD_NOBOSSTHEME_GENERATE_EXAMPLES_ERROR_JSON_PARSE_LOCAL')
            });
            // Em caso de erro no json não continua a execução
            return;
        }
        
        // Verifica se deu tudo certo
        if (jsonResponse.success === 1) {
                        
            if(jsonResponse.data === 'INVALID_TOKEN'){
                nobosstheme.invalidOrExpiratedLicense(jsonResponse.message);
            } else {
                
                // percorre cada modal da resposta
                jQuery.each(jsonResponse.data.modalsJson, function (prop, val) {
                    // busca elementos com o mesmo nome da modal atual e atualiza o value
                    jQuery('[data-modal-name="' + prop + '"]').val(val).siblings('[data-id="modal"]').remove();
                });
                // percorre cada field da resposta
                jQuery.each(jsonResponse.data.fields, function (prop, val) {
                    // busca elementos com o mesmo nome do field atual e altera o control group
                    jQuery('[name="jform\\[' + prop + '\\]"]').closest('.control-group').replaceWith(val);
                });
                var mainSection = jQuery('section#content');
                util.fixScripts(mainSection);
                util.activateYesno(mainSection);
                mainSection.find('select').chosen();
                jQuery(document).trigger('subform-row-add', mainSection);
                // verifica se a versao eh menor que 3.7.3 para fazer tratamento especial aos campos color
                if(nobosstheme.themeInfos.lowerJVersion){
                    mainSection.find('.minicolors').each(function() {
                        $(this).minicolors({
                            control: $(this).attr('data-control') || 'hue',
                            format: $(this).attr('data-validate') === 'color' ? 'hex' : ($(this).attr('data-format') === 'rgba' ? 'rgb' : $(this).attr('data-format')) || 'hex',
                            keywords: $(this).attr('data-keywords') || '',
                            opacity: $(this).attr('data-format') === 'rgba' ? true : false || false,
                            position: $(this).attr('data-position') || 'default',
                            theme: 'bootstrap'
                        });
                    });
                }
                 // Remove a classe dos campos minicolors para evitar que seja ativado duas vezes
                var minicolors = mainSection.find('.minicolors').removeClass('minicolors');
                
                // Reativa o evento de showon da aba itens apos adicionar um novo subform
                jQuery(document).trigger('subform-row-add', mainSection);
                // Readiciona a classe minicolors
                minicolors.addClass('minicolors');
            }
        } else {
            jQuery.alert({
                title: Joomla.JText._('LIB_NOBOSS_FIELD_NOBOSSTHEME_GENERATE_EXAMPLES_ERROR_TITLE'),
                content: jsonResponse.message
            });
        }
    }).fail(function () {
        jQuery.alert({
            title: Joomla.JText._('LIB_NOBOSS_FIELD_NOBOSSTHEME_GENERATE_EXAMPLES_ERROR_TITLE'),
            content: Joomla.JText._('LIB_NOBOSS_FIELD_NOBOSSTHEME_GENERATE_EXAMPLES_ERROR_UNREACHABLE')
        });
    }).always(function () {
        jQuery(themeModal).parent().find('[data-id="theme-modal-input"]').trigger('change');
        setTimeout(function () {
            jQuery('body').find('.loader.loader--fullpage').remove();
            jQuery('body').find('.loader-fade').remove();
        }, 400);
    });
};

/**
 * Método para verificar se um subform tem showon observando um tema diferente do tema escolhido
 *
 * @param {jQuery Object} subform Subform a ser verificado
 * @param {string} model Tema escolhido a ser comparado com o showon do subform
 */
nobosstheme.checkShowonDependencies = function(subform, model){
    // flag que indica que o subform deve ser escondido iniciada como false
    var hide = false;
    // verifica se o subform possui showon
    if(subform.data('showon')){
        // percorre cada showon do subform
        jQuery(subform.data('showon')).each(function(i, v){
            // verifica se este showon esta observando o campo de tema e se o modelo escolhido nao esta entre os valores esperados
            if(v.field === "jform[params][theme]" && v.sign === "=" && jQuery.inArray(model, v.values) === -1){
                // indica que o subform deve ser escondido
                hide = true;
                // sai da iteracao
                return false;
            }
        });
    }
    // retorna se deve ou nao ser escondido
    return hide;
};

nobosstheme.invalidOrExpiratedLicense = function(content){
    jQuery.alert({
        title: Joomla.JText._('LIB_NOBOSS_FIELD_NOBOSSTHEME_GENERATE_EXAMPLES_ERROR_TITLE'),
        content: content,
        type: 'red',
        buttons: {
            ok: {
                keys: ['enter']
            }
        }
    });
};
