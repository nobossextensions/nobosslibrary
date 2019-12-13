// verifica se o objeto util não existe para criá-lo
if (!util) {
    var util = {};
}

/**
 * Adiciona o html de um loader na página
 * 
 * @param  int	 time   tempo em milissegundos que o loader irá se manter na tela, false para caso ele deva ser permanente	
 *
 * @return void
 */
util.addLoader = function(time){
    jQuery('body').append('<div class="loader loader--fullpage"></div>');
    jQuery('body').append('<div class="loader-fade"></div>');

    if(time != undefined){
        setTimeout(function(){
            util.removeLoader();
        }, time);
    }

};

util.removeLoader = function(){
    jQuery('body').find('.loader.loader--fullpage').remove();
    jQuery('body').find('.loader-fade').remove();
};