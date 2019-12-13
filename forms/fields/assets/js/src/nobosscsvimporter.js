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
        nobossCsvImporter.CONSTRUCTOR();
    });

});

var nobossCsvImporter = {};

nobossCsvImporter.CONSTRUCTOR = function(){
    // Usuario clicou no botao fake de upload de arquivp: realiza trigger de click no botao de upload real
    $('.csvimportercols-btn-upload').on('click', function(event){
        event.preventDefault();
        $('[data-csvimporter="file"]').trigger('click');
    });

    // Usuario selecionou um arquivo para upload
    $('[data-csvimporter="file"]').bind('change', nobossCsvImporter.fileUpload);

    // Executa funcao que observa campo 'loadmode' permitindo migrar dados do modelo de edicao pelo field 'nobosssubform' para 'importercsv' e vice-versa
    nobossCsvImporter.dataMigrationInLoadMode();
};

/**
 * Funcao para realizar upload do arquivo e preparar os dados para salvar em input hidden
 */
nobossCsvImporter.fileUpload = function(evt){
    nobossCsvImporter.error = false;
    var msgError = '';
    
    // Nenhum arquivo selecionado
    if ((typeof evt.target.value == undefined) || (evt.target.value === '')){
        return;
    }
    // Extensao do arquivo nao eh valida (permitido 'csv' ou 'txt')
    else if (!(/^([a-zA-Z0-9\s_\\.\-:])+(.csv|.txt)$/.test(evt.target.value.toLowerCase()))) {
        msgError = Joomla.JText._("LIB_NOBOSS_FIELD_NOBOSSITEMSLOADMODE_OPT_UNAVAILABLE")+ evt.target.value;
        nobossCsvImporter.error = true;
    }
    // Inicia leitura do arquivo
    else{
        // Obtem arquivo submetido
        var file = evt.target.files[0];
        // Declara objeto para leitura do arquivo
        var reader = new FileReader();
        // Le o arquivo
        reader.readAsText(file, 'ISO-8859-1');

        // Arquivo lido com sucesso
        reader.onload = function(event) { 
            // Adiciona loader na pagina
            jQuery('body').append('<div class="loader loader--fullpage"></div>');
            jQuery('body').append('<div class="loader-fade"></div>');

            // Obtem as colunas que devem conter no arquivo (definido no xml)
            var cols = jQuery(evt.target).data('csvimportercols');
            // Array com todas as linhas do arquivo
            var allTextLines = event.target.result.split(/\r\n|\n/);
            // Array com as colunas da primeira linha (cabecalho)
            //var headers = allTextLines[0].split(',');
            // Array com os dados finais
            var data = [];

            // Limpa conteudos que possam estar na tabela
            document.getElementById('csvimporter-result').textContent = '';

            // Obtem elemento table para exibicao do resultado
            var tableImport = document.getElementById('csvimporter-result');

            // Cria elemento <tr> na table que ira exibir o cabecalho
            var tableImportRow = tableImport.appendChild(document.createElement('thead')).appendChild(document.createElement('tr'));

            // Percorre as colunas definidas no xml somente para exibir cabecalho
            for (var k = 0; k < cols.length; k++) {
                // Cria elemento <td> com label da coluna
                tableImportRow.appendChild(document.createElement('th')).appendChild(document.createTextNode(cols[k].label));
            }

            // Percorre todas as linhas do arquivo
            for (var i = 1; i < allTextLines.length; i++) {
                // Linha com conteudo definido
                if ((allTextLines[i] !== undefined) && (allTextLines[i] != '')){
                    // Obtem os dados da linha corrente
                    // TODO: permitir definir no xml qual sera o caracter de separacao (; ou ,)
                    var lineData = allTextLines[i].split(';');
                    
                    var line = {};
                    
                    // Cria elemento <tr> na table que exibe resultado
                    tableImportRow = tableImport.appendChild(document.createElement('tr'));
                    
                    // Percorre as colunas definidas no xml
                    for (var j = 0; j < cols.length; j++) {
                        // Coluna definida para linha corrente
                        if ((lineData[j] !== undefined) && (lineData[j] != '')){
                            // Armazena no objeto o nome do campo como indice e o valor da coluna como valor
                            line[cols[j].alias] = lineData[j].trim().toString();

                            // Troca ocorrencias de duas aspas duplas por uma apenas
                            line[cols[j].alias] = line[cols[j].alias].replace(/""/g, '"');
                            // Remove aspas duplas no inicio da string (caso tenha)
                            line[cols[j].alias] = line[cols[j].alias].replace( /^"/g, "");
                            // Remove aspas duplas no final da string (caso tenha)
                            line[cols[j].alias] = line[cols[j].alias].replace( /"$/g, "");
                            // Troca ocorrencias de aspas duplas por aspas simples
                            line[cols[j].alias] = line[cols[j].alias].replace(/"/g, "'");

                            // Coluna possui opcoes validas definidas para a coluna
                            if((cols[j].validvalues != undefined) && (cols[j].validvalues != '')){
                                // Gera array com opcoes validas
                                validOptions = cols[j].validvalues.split('|');
                                // Executa funcao que faz a validacao
                                var returnValidOptions = nobossCsvImporter.columnValidOptions(cols[j], i, line[cols[j].alias], validOptions);
                                // Valor nao eh valido: seta erro e para execucao 
                                if ((returnValidOptions.success == undefined) || (returnValidOptions.success == 0)){
                                    nobossCsvImporter.error = true;
                                    msgError = returnValidOptions.message;
                                    break;
                                }
                            }

                            // Coluna possui funcao JS a executar
                            if ((cols[j].jsfunction != undefined) && (cols[j].jsfunction != '')){
                                // Verifica se a coluna possui funcao a executar e que esteja definida
                                if (typeof window[cols[j].jsfunction] === 'function') {
                                    // Executa funcao de tratamento passando objeto com dados da coluna e o valor a tratar
                                    var returnTreatment = window[cols[j].jsfunction](cols[j], line[cols[j].alias], i);
                                    // Retorno nao eh um objeto
                                    if (typeof returnTreatment != 'object'){
                                        // Apenas registra erro no console log e deixa seguir execucao
                                        console.log(Joomla.JText._("LIB_NOBOSS_FIELD_NOBOSSCSVIMPORTER_BADLY_FORMATTED_FUNCTION").replace('%s1', cols[j].label).replace('%s2', cols[j].jsfunction));
                                    }
                                    // Tratamento ocorreu com sucesso e retorno do valor foi definido
                                    else if ((returnTreatment.success != undefined) && (returnTreatment.success == 1) && (returnTreatment.value != undefined)){
                                        line[cols[j].alias] = returnTreatment.value;
                                    }
                                    // Ocorreu erro e mensagem esta definida: seta erro e para execucao
                                    else if (returnTreatment.message != undefined){
                                        nobossCsvImporter.error = true;
                                        msgError = returnTreatment.message;
                                        break;
                                    }
                                }
                            }

                            // Cria elemento <td> com conteudo da coluna
                            tableImportRow.appendChild(document.createElement('td')).appendChild(document.createTextNode(line[cols[j].alias]));

                            // Encoda valor a ser salvo para nao ter problemas com conteudo html
                            line[cols[j].alias] = encodeURI(line[cols[j].alias]);

                        }else{
                            // Cria elemento <td> com conteudo vazio
                            tableImportRow.appendChild(document.createElement('td')).appendChild(document.createTextNode(''));
                        }
                    }

                    // Definido erro no laco for interno, sai deste laco for tb
                    if (nobossCsvImporter.error == true){
                        break;
                    }

                    // Adiciona dados da linha corrente no array de dados finais
                    data[i-1] = line;
                }
            }

            // Remove loader
            setTimeout(function(){ 
                jQuery('body').find('.loader.loader--fullpage').remove();
                jQuery('body').find('.loader-fade').remove();
            }, 1000);

            // Definido algum erro
            if (nobossCsvImporter.error == true){
                // Exibe erro e modal
                jQuery.alert({
                    title: Joomla.JText._("LIB_NOBOSS_FIELD_NOBOSSCSVIMPORTER_UNABLE_UPLOAD_DATA_TITLE"),
                    content: msgError,
                    type: 'red',
                    buttons: {
                        ok: {
                            keys: ['enter']
                        }
                    }
                });
            }
            // Ocorreu tudo bem
            else{
                // Salva os dados convertidos para json no input hidden (no mesmo formato que os dados do subform sao salvos)
                jQuery('[data-csvimporter="jsonvalue"]').val(JSON.stringify(data));

                // Exibe div de resultados
                jQuery('#csvimporter-result').parent().show();
            }
        };

        // Ocorreu erro ao ler o arquivo
        reader.onerror = function() {
            alert(Joomla.JText._("LIB_NOBOSS_FIELD_NOBOSSCSVIMPORTER_ERROR_READING_FILE") + file.fileName);
        };

        // Limpa valor do campo file
        jQuery(evt.target).val('');
    }
};

/**
 * Funcao para realizar validacoes e tratamentos de valores importados do arquivo
 * 
 * @param  object   colData         Dados da coluna
 * @param  int      line            Numero da linha que esta sendo executado
 * @param  string   value           Valor a tratar
 * @param  array    validOptions    Valores possiveis que podem estar definidos para a coluna
 * 
 * @return object   Retorno com os seguintes atributos ('success' com valor '0' ou '1'; 'message' para mensagem no caso de erro)
 */
nobossCsvImporter.columnValidOptions = function(colData, line, value, validOptions){
    // Definias opcoes possiveis de valores do campo
    if (validOptions.length > 0){
        if (validOptions.indexOf(value) > -1) {
            return {success: 1};
        } else {
            return {success: 0, message: Joomla.JText._("LIB_NOBOSS_FIELD_NOBOSSCSVIMPORTER_INCORRECTLY_FORMATTED_DATA").replace('%s1', value).replace('%s2', colData.label).replace('%s3', line)+validOptions.join("', '")+"'"};
        } 
    }
};

/**
 * Funcao para uso com o campo 'loadmode' permitindo migrar dados do modelo de edicao pelo field 'nobosssubform' para 'importercsv' e vice-versa
 */
nobossCsvImporter.dataMigrationInLoadMode = function(){
    // evento de troca do modo de carregamento
    jQuery('#itemsloadmode').on('change', function(){
        // pega a opcao escolhida
        var option = jQuery(this).find('option[value='+jQuery(this).val()+']').val();
        var data = null;
       
        // TODO: quando a extensao tiver mais de um subform (Ex: qnd tem varios modelos e por isso subforms escondidos na pagina), eh necessario especificar melhor qual sera o subform a pegar abaixo
        var subform = jQuery('.row-fluid[data-subform-collapse-wrapper]');

        // Subform nao existe na pagina: sai da funcao
        if (subform.length < 1){
            return false;
        }

        alert('sasasa');

        // Exibe loader e mensagem de que os dados estao sendo migrados
        jQuery('body').append('<div class="loader loader--fullpage"></div>');
        jQuery('body').append('<div class="loader-fade"></div>');
        jQuery('body').append('<div class="loader-message">'+Joomla.JText._("LIB_NOBOSS_FIELD_NOBOSSCSVIMPORTER_DATA_BEING_MIGRATED")+'</div>');

        // Selecionada opcao de cadastro manual: migrar dados do csv para subform
        if (option == 'manual'){
            // Remove itens do subform ja existentes
            subform.find('.subform-repeatable-group').remove();
            
            // Obtem dos dados do csv
            data = JSON.parse(jQuery('[data-csvimporter="jsonvalue"]').val());

            // Algum valor esta definido
            if (data.length > 0){
                // Percorre cada item a ser adicionado
                jQuery.each(data, function (i, itemValues) {
                    var arrayItems = [];

                    // Converte objeto com valores para array decodificando valor
                    jQuery.each( itemValues, function( key, value ) {
                        arrayItems[key] = decodeURI(value);
                    });

                    itemValues = null;

                    // Clica no botao de adicionar novo item de subform
                    subform.find('.btn-toolbar .btn-group a.group-add').click();
                    
                    // Obtem item adicionado
                    var newItem = subform.find('[data-new="true"]').last();

                    // OBS: Homologado com campos do tipo 'text', 'textarea', 'editor tinymce', 'radio', 'hidden', 'select', 'color' e 'date'

                    // Percorre os campos do tipo input com excecao do campo 'radio' do item adicionado
                    newItem.find('input').not('[type="radio"]').each(function () {
                        if (jQuery(this).attr('name') != undefined){
                            nameField = jQuery(this).attr('name').match(/jform(\[([a-z0-9_-]*)\])*/)[2];
                            // Campo de minicolor: coloca valor de forma diferente para realizar trigger do campo
                            if (jQuery(this).is(".minicolors")){
                                jQuery(this).minicolors('value', arrayItems[nameField]);
                            }
                            // Input normal
                            else{
                                // Salva valor no campo
                                jQuery(this).val(arrayItems[nameField]);
                            }
                        } 
                    });

                    // Percorre os campos do tipo select do item adicionado
                    newItem.find('select').each(function () {
                        nameField = jQuery(this).attr('name').match(/jform(\[([a-z0-9_-]*)\])*/)[2];
                        // Salva valor no campo
                        jQuery(this).val(arrayItems[nameField]);
                        // Realiza trigger no campo
                        jQuery(this).trigger('liszt:updated');

                        // OBS: nao esta sendo executado o trigger pq ele buga o showsen deixando campos escondidos que deveriam estar visiveis
                        //jQuery(this).trigger('change');
                    });

                    // Percorre os campos do tipo radio do item adicionado
                    newItem.find('fieldset.radio').each(function () {
                        var inputRadioTarget = jQuery(this).find('label[class*="active"]').first().attr('for');
                        var inputRadioSelected = jQuery(this).find('input#' + inputRadioTarget);
                        nameField = inputRadioSelected.attr('name').match(/jform(\[([a-z0-9_-]*)\])*/)[2];
                        // Obtem opcao do radio que possui o value                       
                        var tempRadio = newItem.find('input[name$="\\['+nameField+'\\]"][value="' + arrayItems[nameField] + '"]');
                        // Trigger de clique no radio equivalente ao value
                        tempRadio.siblings('label[for="' + tempRadio.attr('id') + '"]').click();
                    });

                    // Percorre os campos do tipo textarea do item adicionado
                    newItem.find('.controls > textarea').each(function () {
                        if (jQuery(this).attr('name') != undefined){
                            nameField = jQuery(this).attr('name').match(/jform(\[([a-z0-9_-]*)\])*/)[2];
                            // Salva valor no campo
                            jQuery(this).val(arrayItems[nameField]);
                        }
                    });

                    // Percorre os campos do tipo editor tinymce do item adicionado
                    newItem.find('.js-editor-tinymce textarea').each(function () {
                        nameField = jQuery(this).attr('name').match(/jform(\[([a-z0-9_-]*)\])*/)[2];
                        // Salva valor no textarea no iframe do editor
                        jQuery(this).val((arrayItems[nameField])).parent().find("iframe").contents().find('body').html(arrayItems[nameField]);
                    });
                });

                // Fecha collapse que por padrao vem aberto da criacao
                nobosssubform.toggleCollapseAll('shrink', subform);

                // Limpa os dados no input do csv
                jQuery('[data-csvimporter="jsonvalue"]').val();
                // Limpa dados exibidos na table de resultados do csv
                document.getElementById('csvimporter-result').textContent = '';
            }

        }
        // Selecionada opcao de cadastro csv: migrar dados do subform para csv
        else if(option == 'csv'){
            // Obtem items do subform 
            var subformItems = subform.find('.subform-repeatable-group[data-base-name]');

            // Exite mais de um item cadastrado OU existe apenas um item (normalmente um eh obrigatorio existir, sem poder ser removido), mas os campos obrigatorios estao preenchidos 
            if((subformItems.length > 1) || ((subformItems.length == 1) && (document.formvalidator.isValid(document.adminForm)))){
                // Array que ira armazenar os itens a serem salvos no input
                var arrayItems = [];

                // Percorre os itens
                subformItems.each(function () {
                    var arrayFields = [];
                    var nameField = '';

                    // OBS: funciona com campos do tipo 'text', 'textarea', 'editor tinymce', 'radio', 'hidden' e 'select'

                    // Percorre os campos do tipo input com excecao do campo 'radio'
                    jQuery(this).find('input').not('[type="radio"]').each(function () {
                        if (jQuery(this).attr('name') != undefined){
                            nameField = jQuery(this).attr('name').match(/jform(\[([a-z0-9_-]*)\])*/)[2];
                            arrayFields[nameField] = jQuery(this).val();
                        }
                    });

                    // Percorre os campos do tipo select
                    jQuery(this).find('select').each(function () {
                        if (jQuery(this).attr('name') != undefined){
                            nameField = jQuery(this).attr('name').match(/jform(\[([a-z0-9_-]*)\])*/)[2];
                            arrayFields[nameField] = jQuery(this).val();
                        }
                    });

                    // Percorre os campos do tipo radio
                    jQuery(this).find('fieldset.radio').each(function () {
                        var inputRadioTarget = jQuery(this).find('label[class*="active"]').first().attr('for');
                        var inputRadioSelected = jQuery(this).find('input#' + inputRadioTarget);
                        nameField = inputRadioSelected.attr('name').match(/jform(\[([a-z0-9_-]*)\])*/)[2];
                        arrayFields[nameField] = inputRadioSelected.val();
                    });

                    // Percorre os campos do tipo textarea
                    jQuery(this).find('.controls > textarea').each(function () {
                        if (jQuery(this).attr('name') != undefined){
                            nameField = jQuery(this).attr('name').match(/jform(\[([a-z0-9_-]*)\])*/)[2];
                            arrayFields[nameField] = jQuery(this).html();
                        }
                    });

                    // Percorre os campos do tipo editor tinymce
                    jQuery(this).find('.js-editor-tinymce textarea').each(function () {
                        nameField = jQuery(this).attr('name').match(/jform(\[([a-z0-9_-]*)\])*/)[2];
                        arrayFields[nameField] = jQuery(this).parent().find("iframe").contents().find('body').html();
                    });

                    // Salva no array de items o objeto do item corrente (converte o array para objeto dos fields)
                    arrayItems.push(Object.assign({}, arrayFields));
                    // Mata editor para evitar dar erro de js qnd remover ele da pagina (se existir)
                    tinymce.get(jQuery(this).find('.js-editor-tinymce textarea').attr('id')).remove();
                    // Remove item de subform da pagina
                    jQuery(this).remove();
                });

                // Salva os dados no input do csv em formato json
                jQuery('[data-csvimporter="jsonvalue"]').val(JSON.stringify(arrayItems));

                // Remove itens do subform ja existentes para nao ficar lixo
                subform.find('.subform-repeatable-group').remove();

                // Limpa conteudo da tabela, caso tenha
                document.getElementById('csvimporter-result').textContent = '';
            }
        }

        // remove o loader e mensagem de dados sendo migrados
        setTimeout(function () {
            jQuery('body').find('.loader.loader--fullpage').remove();
            jQuery('body').find('.loader-fade').remove();
            jQuery('body').find('.loader-message').remove();
            
            // Salva registro para recarregar a pagina e carregar os dados corretamente 
            // OBS: isso foi feito pq no subform nao podemos dar trigger change nos selects pq buga o showsen e no CSV teria que desenvolver exibicao dos dados em tabela apos migrar
            Joomla.submitbutton('module.apply');
        }, 4000);
    });
};

/**
 * Funcao para realizar validacoes e tratamentos de valores importados do arquivo
 * 
 * @param  object   colData     Dados da coluna (colData.label, colData.name, colData.jsfunction, colData.validvalues)
 * @param  string   value       Valor a tratar
 * @param  int      line        Numero da linha que esta sendo executado
 * 
 * @return object   Retorno com os seguintes atributos ('success' com valor '0' ou '1'; 'message' para mensagem no caso de erro; 'value' para novo valor tratado)
 */
// TODO: modelo de funcao para uso no tratamento de dados (deve ser colocada no JS da extensao que ira utilizar esse campo)
/*function columnValueTreatment(colData, value, line){
    // Executa acao conforme alias da coluna
    switch (colData.alias) {
        case 'show_same_column':
            // TODO: realiza aqui o tratamento e retorna sucesso (neste exemplo apenas retorna 'novo valor')
            return {success: 1, value: 'novo valor'};
        case 'col2':
            // TODO: realiza aqui o tratamento e retorna erro neste exemplo
            return {success: 0, message: "Ocorreu um erro no tratamento da coluna '"+colData.label+"' na linha '"+line+"' para o valor '"+value+"'"};
    }
}*/
