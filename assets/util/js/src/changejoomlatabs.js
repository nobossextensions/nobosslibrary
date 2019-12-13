// verifica se o objeto util não existe para criá-lo
if(!util){
    var util = {};
}

/**
 * Funcao que altera a aba default da pagina
 * 
 * @param  string	 hrefAba    Href do link da aba que deve ser a default	
 *
 * @return void
 */
util.changeDefaultTab = function (hrefAba) {
    
    // Realiza trigger para aba informacao
    jQuery( ".form-horizontal .nav-tabs li a[href='#"+hrefAba+"']").trigger('click');
    // Remove o evento de clique que nao eh mais necessario
    jQuery( ".form-horizontal").off('click', ".nav-tabs li a[href='#general']");
    jQuery("a[href='#"+hrefAba+"']").closest('li').addClass('active');
    
};

util.moveAssignTab = function(){
    // pega as abas do modulo
    var tabs = jQuery('#myTabTabs');
    // remove e guarda o html da aba 'Atribuir Menu' 
    var assignTab = tabs.find('a[href="#assignment"]').parent('li').remove();
    // pega a aba 'Permissoes de modulo'
    var permissionTab = tabs.find('a[href="#permissions"]').parent('li');
    // insere a aba de atribuicao antes da aba de permissoes
    permissionTab.before(assignTab);
};

util.movePermissionTab = function(){
    // pega as abas do modulo
    var tabs = jQuery('#myTabTabs');
    // remove e guarda o html da aba 'Atribuir Menu' 
    var assignTab = tabs.find('a[href="#assignment"]').parent('li').remove();
    // pega a aba 'Permissoes de modulo'
    var permissionTab = tabs.find('a[href="#permissions"]').parent('li');
    // insere a aba de atribuicao antes da aba de permissoes
    permissionTab.before(assignTab);
};

// funcao para trocar a aba de 'Modulo' para 'Publicacao'
util.changeModuleTab = function(){
    // remove a aba 'Atribuir modulo'
    jQuery('a[href="#assignment"]').parent('li').hide();
    // troca o nome da aba 'Modulo' para 'Publicacao'
    jQuery('a[href="#general"]').html(Joomla.JText._('NOBOSS_EXTENSIONS_TAB_PUBLICATION_LABEL'));
    // esconde campo de exibir titulo do joomla
    jQuery('fieldset#jform_showtitle').closest('.control-group').hide();
    // Forca para que o titulo do modulo seja marcado como 'nao'
    jQuery('#jform_showtitle1').attr('checked', true);
    // esconde campo 'nota'
    jQuery('#jform_note').closest('.control-group').hide();
    // pega a div de campos da aba 'Publicacao'
    var publicationDiv = jQuery('div#general');
    // esconde o primeiro span9 (era onde exibia o nome do modulo)
    publicationDiv.find('.span9').first().hide();
    // tira a margem lateral dos campos 'details'
    publicationDiv.find('.span3').first().css('margin-left', '0px').removeClass('span3');
    // adiciona span4 na div da aba publicacao
    publicationDiv.find('.row-fluid').first().addClass('span5');
    // remove a classe form-vertical para tirar o estilo que ela aplica
    publicationDiv.find('.form-vertical').removeClass('form-vertical');
    // pega o conteudo da aba 'Atribuir menu'
    var assignmentDiv = jQuery('div#assignment').detach();
    // tira as classes colocadas pelo joomla na div de atribuir menu e adiciona span8 ára limitar a largura
    assignmentDiv.removeClass('tab-pane active').addClass('span7');
    // tira o field 'posicao de modulo' da primeira coluna
    var positionField = jQuery('#jform_position').closest('.control-group').detach();
    // coloca esse field antes do 'Atribuir modulo'
    assignmentDiv.prepend(positionField);
    // adiciona esse contuedo na aba 'Publicacao'
    publicationDiv.append(assignmentDiv);
    // remove a aba 'Publicacao'
    var publicationTab = jQuery('a[href="#general"]').parent('li').detach();
    // e coloca ela antes da aba 'Licenca'
    jQuery('a[href="#attrib-style-js"]').parent('li').before(publicationTab);
    // remove os campos de ordenacao e acesso
    var orderingField = publicationDiv.find('#parent_jform_ordering').first().closest('.control-group').detach();
    var accessField = publicationDiv.find('#jform_access').first().closest('.control-group').detach();
    // coloca eles abaixo do campo de idioma
    jQuery('#jform_language').closest('.control-group').after(orderingField).after(accessField);
};


