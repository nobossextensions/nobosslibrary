// verifica se o objeto util não existe para criá-lo
if(!util){
    var util = {};
}

util.disableEditButtons = function () {
    // disabilita os botões de salvar, salvar e fechar, salvar e novo, criar cópia 
    jQuery("#toolbar-apply").find('button').prop("disabled", true).css({ 'pointer-events': 'none' });
    jQuery("[id*='-save']").find('button').prop("disabled", true).css({ 'pointer-events': 'none' });

};