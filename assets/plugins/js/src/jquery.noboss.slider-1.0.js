// Uses CommonJS, AMD or browser globals to create a jQuery plugin.
(function (factory) {
    if (typeof define === 'function' && define.amd) {
        // AMD. Register as an anonymous module.
        define(['jquery'], factory);
    } else if (typeof module === 'object' && module.exports) {
        // Node/CommonJS
        module.exports = function( root, jQuery ) {
            if ( jQuery === undefined ) {
                if ( typeof window !== 'undefined' ) {
                    jQuery = require('jquery');
                }
                else {
                    jQuery = require('jquery')(root);
                }
            }
            factory(jQuery);
            return jQuery;
        };
    } else {
        // Browser globals
        factory(jQuery);
    }
}(function ($) {

	//Função slider é chamada pela a em um elemento que terá um container, div ou ul e terá itens, div ou li
	$.fn.slider = function(exibirQuantos, reordenar) {

		var tamanhoResponsivoSmall = 768; 
		var tamanhoResponsivoMedium= 992;
		//Armazena "this" na variavel "that" para não perder referência dentro de outras funções
		var that = $(this);
		//Armazena a variavel list, que é a lista com os itens
		var list = that.children("[data-list]");
		//Classe a ser controlada
		var classe;

		if($(window).width() >= tamanhoResponsivoMedium) {
			classe = "active";
		} else if ($(window).width() >= tamanhoResponsivoSmall) {
			classe = "active-t";
		} else {
			classe = "active-m";
		}


		//Setas que estão dentro do elemento que foi chamado o slider
		var setaEsquerda = that.find("[data-slide=prev]");
		var setaDireita = that.find("[data-slide=next]");

		//Primeiro elemento com a classe active (visivel)
		var primeiroIDvisivel = parseInt(list.children("."+classe).first().attr("data-id"));
		if(isNaN(primeiroIDvisivel)) {
			primeiroIDvisivel = parseInt(list.children("[data-id]:visible:first").attr("data-id"));
		}
		//Ultimo elemento com a classe active (visivel)
		var ultimoIDvisivel = parseInt(list.children("."+classe).last().attr("data-id"));
		//Primeiro elemento do carrosel
		var primeiroID = list.children("[data-id]").first().attr("data-id");
		//Ultimo elemento do carrosel
		var ultimoID = list.children("[data-id]").last().attr("data-id");

		//Controle da próxima exibição.
		var proximoID;
		setaDireita.on("click", function(e){
			//As setas em nossos casos são A's, portanto deve-se prevenir o evento default
			e.preventDefault();

			if (classe == "active-t") {
				deleteID = primeiroIDvisivel;
				
				if(ultimoIDvisivel == ultimoID) {
					if (primeiroIDvisivel == primeiroID) {
						proximoID = primeiroIDvisivel + 1;
						deleteID = ultimoIDvisivel;
						if(list.hasClass("items--reverse")){
							list.removeClass("items--reverse");
						}
					}else{
						proximoID = 1;
						list.addClass("items--reverse");
					}
				} else {
					proximoID = ultimoIDvisivel + 1;
				}
				
				list.children("[data-id='"+deleteID+"']").removeClass(classe);

				list.children("[data-id='"+proximoID+"']").addClass(classe);

				primeiroIDvisivel = parseInt(list.children("."+classe).first().attr("data-id"));

				ultimoIDvisivel = parseInt(list.children("."+classe).last().attr("data-id"));
			}else if(classe == "active-m"){
				if(ultimoIDvisivel == ultimoID) {
					proximoID = 1;
				} else {
					proximoID = ultimoIDvisivel + 1;
				}
				//Remove a classe do primeiro visível
				list.children("[data-id='"+primeiroIDvisivel+"']").removeClass(classe);

				//Insere a classe no próximo elemento
				list.children("[data-id='"+proximoID+"']").addClass(classe);

				primeiroIDvisivel = parseInt(list.children("."+classe).first().attr("data-id"));
				if(isNaN(primeiroIDvisivel)) {
					primeiroIDvisivel = parseInt(list.children("[data-id]:visible:first").attr("data-id"));
				}
				ultimoIDvisivel = parseInt(list.children("."+classe).last().attr("data-id"));
			}else{
				deleteID = primeiroIDvisivel;
				if(isNaN(proximoID)){
					proximoID = ultimoIDvisivel + 1;
				}

				//reseta ordenação dos itens
				list.removeClass("items--reverse");
				list.children().removeClass("product-box--order");

				//caso esteja na posicao original
				if(ultimoIDvisivel == ultimoID){
					if(primeiroIDvisivel == primeiroID){
						//verifica se o proximo elemento eh o que vem logo apos o elemento com menor id
						if(proximoID == primeiroIDvisivel + 1){
							//caso sim, admite que o que deve ser excluido é o anterior ao ultimo
							deleteID = ultimoID - 1;
							//ajusta a ordem
							list.addClass("items--reverse");
							list.children("[data-id="+ultimoID+"]").addClass("product-box--order");
							list.children("[data-id="+primeiroID+"]").addClass("product-box--order");
						//caso o ultimo elemento e o primeiro estejam visiveis mas nao na ordem
						}else{
							//exclui o ultimo
							deleteID = ultimoID;
							//e volta para a ordem original
							proximoID = primeiroIDvisivel + 2;
						}
					//caso esteja soh o ultimo visivel
					}else{
						//admite que o proximo soh pode ser o primeiro
						proximoID = primeiroID;
						//exclui o atual primeiro
						deleteID = primeiroIDvisivel;
						//reordena a lista
						list.children("[data-id="+ (ultimoID - 1) +"]").addClass("product-box--order");
						list.addClass("items--reverse");
					}
				//caso nao esteja na ordem, soh segue a logica de excluir o 1o e pegar o proximo
				}else{
					deleteID = primeiroIDvisivel;
					proximoID = ultimoIDvisivel + 1;
				}
				//esconde o elemento a ser deletado
				list.children("[data-id='"+deleteID+"']").removeClass(classe);
				//mostra o elemento que seria proximo
				list.children("[data-id='"+proximoID+"']").addClass(classe);

				//atualiza o valor do primeiro
				primeiroIDvisivel = parseInt(list.children("."+classe).first().attr("data-id"));

				//atualiza o valor do ultimo				
				ultimoIDvisivel = parseInt(list.children("."+classe).last().attr("data-id"));

				//move para o proximo elemento
				proximoID++;

			}
		});

		setaEsquerda.on("click", function(e){
			//As setas em nosso caso são A's, portanto deve-se prevenir o evento default
			e.preventDefault();

			if (classe == "active-t") {
				deleteID = ultimoIDvisivel;

				if(primeiroIDvisivel == primeiroID) {
					if (ultimoIDvisivel == ultimoID) {
						proximoID = ultimoIDvisivel - 1;
						deleteID = primeiroIDvisivel;
						if(list.hasClass("items--reverse")){
							list.removeClass("items--reverse");
						}
					}else{
						proximoID = ultimoID;
						list.addClass("items--reverse");
					}
				} else {
					proximoID = primeiroIDvisivel - 1;
				}

				list.children("[data-id='"+deleteID+"']").removeClass(classe);

				list.children("[data-id='"+proximoID+"']").addClass(classe);

				primeiroIDvisivel = parseInt(list.children("."+classe).first().attr("data-id"));

				ultimoIDvisivel = parseInt(list.children("."+classe).last().attr("data-id"));
			}else if(classe == "active-m"){
				if(primeiroIDvisivel == 1) {
					proximoID = ultimoID;
				} else {
					proximoID = primeiroIDvisivel - 1;
				}
				//Remove a classe do ultimo visível
				list.children("[data-id='"+ultimoIDvisivel+"']").removeClass(classe);
				//Insere a classe no próximo elemento e o move dentro da UL para ser o primeiro elemento no contexto do DOM
				list.children("[data-id='"+proximoID+"']").addClass(classe);

				//Seta novamente os valores dos primeiros e ultimos ID's visíveis
				primeiroIDvisivel = parseInt(list.children("."+classe).first().attr("data-id"));
				if(isNaN(primeiroIDvisivel)) {
					primeiroIDvisivel = parseInt(list.children("[data-id]:visible:first").attr("data-id"));
				}
				ultimoIDvisivel = parseInt(list.children("."+classe).last().attr("data-id"));
			}else{

				deleteID = primeiroIDvisivel;
				//seta o id do elemento que deve aparecer 
				if(isNaN(proximoID)){
					proximoID = ultimoID;
				}

				//reseta ordenação dos itens
				list.removeClass("items--reverse");
				list.children().removeClass("product-box--order");

				//caso esteja na ordem original
				if(primeiroIDvisivel == primeiroID){
					if(ultimoIDvisivel == ultimoID){
						//se o proximo id for o antepenultimo
						if(proximoID == ultimoIDvisivel - 1){
							//exclui o atual 3o
							deleteID = primeiroIDvisivel + 1;
							//muda a ordem do proximo elemento que sera o primeiro
							list.children("[data-id="+proximoID+"]").addClass("product-box--order");
							//e muda a ordem da lista
							list.addClass("items--reverse");
						//caso o proximo nao seja o antepenultimo
						}else{
							//exclui o atual primeiro elemento, que é o com menor id
							deleteID = primeiroIDvisivel;
							//e entende que o primeiro elemento eh a unica possibilidade, mudando a ordem
							list.children("[data-id="+primeiroID+"]").addClass("product-box--order");
						}
					//caso o item de maior id nao seja o ultimo	
					}else{
						//exclui o ultimo item atual
						deleteID = ultimoIDvisivel;
						//entende que o proximo soh pode ser o ultimo elemento (pelo primeiro visivel ser o primeiro de fato)
						proximoID = ultimoID;
						//ajusta a ordem dos elementos 
						list.addClass("items--reverse");
						list.children("[data-id="+ultimoID+"]").addClass("product-box--order");
						list.children("[data-id="+primeiroID+"]").addClass("product-box--order");
					}
				//caso nao seja o primeiro que esta na primeira posicao
				}else{
					//exclui o ultimo visivel
					deleteID = ultimoIDvisivel;
					//e admite que o proximo sera o anterior do atual 1o
					proximoID = primeiroIDvisivel - 1;
				}

				//esconde o elemento a ser deletado
				list.children("[data-id='"+deleteID+"']").removeClass(classe);
				//mostra o elemento que seria proximo
				list.children("[data-id='"+proximoID+"']").addClass(classe);
				//atualiza o valor do primeiro
				primeiroIDvisivel = parseInt(list.children("."+classe).first().attr("data-id"));
				//atualiza o valor do ultimo
				ultimoIDvisivel = parseInt(list.children("."+classe).last().attr("data-id"));
				//move para o proximo elemento
				proximoID--;

			}
		});


		var contadorItens = 0;
		var limitItems = 0;
		$(window).resize(function() {
			primeiroIDvisivel = parseInt(list.children("."+classe).first().attr("data-id"));
		
			if($(window).width() >= tamanhoResponsivoSmall && $(window).width() <= tamanhoResponsivoMedium) {
				limitItems = 2;
				list.children().removeClass(classe);
				classe = "active-t";
				if(isNaN(primeiroIDvisivel)) {
					primeiroIDvisivel = parseInt(list.children("[data-id]:first").attr("data-id"));
				}
				while(contadorItens < 2) {
					list.children("[data-id]:eq("+contadorItens+")").addClass(classe);
					contadorItens++;
				}
				contadorItens = 0;
			} else if($(window).width() <= tamanhoResponsivoSmall) {
				limitItems = 1;
				list.children().removeClass(classe);
				classe = "active-m";
				if(isNaN(primeiroIDvisivel)) {
					primeiroIDvisivel = parseInt(list.children("[data-id]:first").attr("data-id"));
				}
				list.children("[data-id='"+primeiroIDvisivel+"']").addClass(classe);
			}else if($(window).width() >= tamanhoResponsivoMedium) {
				limitItems = 3;
				list.children().removeClass(classe);
				classe = "active";
				if(isNaN(primeiroIDvisivel)) {
					primeiroIDvisivel = parseInt(list.children("[data-id]:first").attr("data-id"));
				}
				if($(list).children("highlights-item")){
					limitItems = 4;
				}
				while(contadorItens < limitItems) {
					$(list).children("[data-id]:eq("+contadorItens+")").addClass(classe);
					contadorItens++;
				}

				contadorItens = 0;
			}
			ultimoIDvisivel = parseInt(list.children("."+classe).last().attr("data-id"));

			//verifica se deve exibir as setas
			if(list.children().length <= limitItems){
				jQuery(".arrows-wrapper .arrow").addClass("hidden");
			}else{
				jQuery(".arrows-wrapper .arrow").removeClass("hidden");
			}
		});


		(function(){
			jQuery(window).trigger('resize');
		})();		


	};

}));
