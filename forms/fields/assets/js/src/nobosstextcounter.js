var nobosstextcounter = {};
/**
 * Função construtodas do js, primeira a ser executada ao terminar de carregar a página
 */
nobosstextcounter.CONSTRUCTOR = function () {
    //instancia os elementos 
    var counterElementWrapper = jQuery(".nobosstextcounter-wrapper");
    //percorre a lista de counters
    jQuery(counterElementWrapper).each(function(){
        //pega o valor do atributo que define o tipo de exibição dos caracteres
        var showCharacters = jQuery(this).data("showcharacters");
        var autoResizeText = jQuery(this).data("autoresizetext");

        //adiciona o listener
        listener(this, showCharacters, autoResizeText);
    });
};
/**
 *  Adiciona o listener do contador
 * 
 * @param  counterElementWrapper Elemento que engloba o contador 
 * @param  showcharacters        Valor do data-attribute que poder ser typed ou remaining
 */
var listener = function(counterElementWrapper, showCharacters, autoResizeText){
    //instancia os elementos referentes ao counter
    var counterElement = jQuery(counterElementWrapper).find(".nobosstextcounter");
    var textAreaElement = jQuery(counterElementWrapper).siblings("textarea");
    var textAreaLimit = jQuery(counterElementWrapper).data("limit");    
    
    //adiciona o listener que reage ao digitar no textarea
    jQuery(textAreaElement).on("input", function(e){
        //corta os caracteres que estao sobrando
        if(autoResizeText){
            jQuery(this).val(jQuery(this).val().substring(0, textAreaLimit));
        } 
        //ao digitar verifica quantos caracteres foram inseridos
        var textAreaTypedLength = jQuery(this).val().length;
     
        
        //calcula quantos caracteres ainda podem ser digitados
        var textAreaRemaining = textAreaLimit - textAreaTypedLength;

        
        //verifica se deve exibir o valor de caracteres remanescentes ou digitados
        if(showCharacters == "remaining"){
            displayedValue = textAreaRemaining;
        }else if(showCharacters == "typed"){
            displayedValue = textAreaTypedLength;
            //caso o valor da variável não seja válido para a execução 
        }else{
            return;
        }
        
        if(textAreaRemaining < 0){
            if(!autoResizeText){
                displayedValue = translationConstants.textcounter.LIB_NOBOSS_FIELD_NOBOSSTEXTCOUNTER_CHARACTERS_LIMIT_REACHED;
            }
        }
       
        counterElement.text(displayedValue);  
        
    });
    (function(){
        jQuery(textAreaElement).trigger("input"); 
    })();   
};


jQuery(document).ready(function (jQuery) {
    // Declara $ para evitar conflito
    $ = jQuery;
    // Scripts que são necessários estarem carregados na página
    var scripts = [];
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
        nobosstextcounter.CONSTRUCTOR();
    });

});
