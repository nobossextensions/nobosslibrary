jQuery(document).ready(function($) {
	binaryFile.CONSTRUCTOR();
});


// Variável que armazena funções.
var binaryFile = {};


binaryFile.CONSTRUCTOR = function() {
	// Pega todos os contextos de upload de arquivos.
	var contextsFileUpload = jQuery(".file-upload-context");

	// Evento quando ocorre a troca de arquivo no campo de upload.
	contextsFileUpload.on('change', 'input[data-id="upload-binary"]', function(event) {
		// Faz a troca do SRC da imagem e do value do campo hidden.
		binaryFile.alterFile(jQuery(this));
	});

	// Evento quando link de exibição de arquivo perde o foco.
	contextsFileUpload.on('focusout', '[data-id="seeFile"]', function(event) {
		var element = jQuery(this);
		// Pega contexto do elemento.
		var elementContext = element.closest('.file-upload-context');
		// Pega tooltip da imagem.
		var tooltipImage = elementContext.find('.tooltip-image');
		tooltipImage.addClass("hidden");
	});

	// Evento quando ocorre click no link de remover arquivo.
	contextsFileUpload.on('click', "[data-id='deleteFile']", function(event) {
		event.preventDefault();

		// Pega o link no contexto.
		var linkDeleteFile = jQuery(this);

		// Pega contexto do link.
		var linkContext = linkDeleteFile.closest('.file-upload-context');

		// Apagar valor do campo de imagem binária.
		linkContext.find("[data-id='upload-binary-img']").val("");
		// Esconde o campo de imagem do arquivo.
		linkContext.find("[data-id='upload-binary-img']").addClass("hidden");
		// Limpa arquivo do input.
		linkContext.find("[data-id='upload-binary']").val("");
		// Limpa input hidden com valor do arquivo.
		linkContext.find("[data-id='upload-binary-hidden']").val("");

		// Esconde span com links para visualizar e remover arquivo.
		linkContext.find("[data-id='file-options']").addClass("hidden");
	});

	// Evento quando ocorre clique no link para visualizar o arquivo.
	jQuery(".file-upload-context").on('click', 'a[data-id=seeFile]', binaryFile.imgBtnActivate);

	// Trigger customizado do campo, para informar quando a requisição foi finalizada.
	jQuery.event.trigger({
		type: "binnaryFileRequisitionEnds"
	});
};


/**
	 * Método que altera um arquivo.
	 * @param input[type='file'] input Campo que contém o arquivo.
	 * @return void
	 */
binaryFile.alterFile = function(input){

	// Se o campo não tem arquivo selecionado.
	if(input.val() === ""){
		// Retorna sem realizar nenhuma ação.
		return;
	}
	// Pega o nome do campo.
	var fieldName = input.attr("name");

	// Pega contexto da input.
	var inputContext = input.closest('.file-upload-context');
	// Limpa mensagem para o campo.
	inputContext.siblings("div[data-field='error_"+ fieldName + "']").remove();

	// Desabilita o input de upload de arquivo.
	input.attr("disabled", true);

	// Pega span com links para visualizar e remover arquivo.
	var fileOptions = inputContext.find("[data-id='file-options']");

	// Esconde opções de arquivo.
	fileOptions.addClass("hidden");

	// Pega campo de upload de arquivo.
	var uploadFile = jQuery("[data-id='upload-binary']");

	// Pega o arquivo do input.
    var archive = input[0].files[0];
	// Cria um FormData, necessário para encapsular e enviar arquivos via ajax.
	var data = new FormData();
	// Adiciona a foto ao formulário.
	data.append("formFile", archive);
	// Pega o contexto do campo.
	data.append("dataParamsField", uploadFile.attr("data-params-field"));
	// Pega URL da requisição.
    var url = input.attr('data-url');
	// Variável que armazena a propriedade "src" de uma tag <img>.
	var imgSrc;
	// Pega tooltip com pré-visualização para imagens.
	var imgThumb = inputContext.find("[data-id='upload-binary-img']");
	// Adiciona o hidden no tooltip de imagem
	imgThumb.addClass('hidden');
	// Realiza requisição ajax;
	jQuery.ajax({
		url: baseNameUrl + url,
		data: data,
		cache: false,
		contentType: false,
		processData: false,
		type: 'POST',
		success : function(dataReturn) {
			// Realiza decode dos dados de retorno.
			dataReturn = jQuery.parseJSON(dataReturn);

			inputContext.trigger("binnaryFileRequisitionEnds");

			// Verifica se existem erros no retorno.
			if(dataReturn.hasOwnProperty("error")){
				// Verifica se ainda não existe a mensagem de erro para o campo.
				if(jQuery("div[data-field='error_"+ fieldName + "']").length === 0) {
					jQuery("<div data-field='error_"+ fieldName + "' class='alert alert-danger'>" + dataReturn.error + "</div>").insertAfter(inputContext);
				}
			}else{
				// Pega input com string do arquivo.
				var inputStringFile = input.siblings('input[data-id=upload-binary-hidden]');

				// Verifica se o o arquivo é uma imagem.
				if(binaryFile.verifyType(dataReturn.mimeTypeFile, "image")){
					// Configura src da tag <img>.
					imgThumb.attr('src', dataReturn.imageSrcDisplay);
					imgThumb.removeClass('hidden');

				}else{
					// Adiciona o hidden no tooltip de imagem
					imgThumb.addClass('hidden');
				}

				// Dados do campo.
				var fieldValue = {
					stringFile : dataReturn.stringFile,
					mimeTypeFile :  dataReturn.mimeTypeFile
				};

				// Encapsula em um JSON dados do campo.
				var jsonFieldValue = JSON.stringify(fieldValue);

				// Configura valor do campo passando JSON.
				inputStringFile.val(jsonFieldValue);

				// Configura mime do arquivo.
				inputStringFile.attr("mime", dataReturn.mimeTypeFile);
				// Habilita campos de visualização e remoção do arquivo.
				fileOptions.removeClass("hidden");
			}
			// Habilita da input de arquivo novamente.
			input.removeAttr("disabled", true);
		}
	});
};

/**
 * Método que procura se existe um tipo no mimetype do arquivo
 * @param string mimeTypeFile Mimetype do arquivo.
 * @param string typeSearch Tipo a ser verificado.
 */
binaryFile.verifyType = function (mimeTypeFile, typeSearch){

	// Flag que verifica se o tipo existe na string.
	var hasType = mimeTypeFile.search(typeSearch);
	if(hasType != -1){
		return true;
	}else{
		return false;
	}
};

/**
	 * Ativa o botão para esconder a imagem.
	 * @param event event Evento disparado.
	 * @return void
	 */
binaryFile.imgBtnDesactivate = function(event){
	// Para eventos posteriores a este.
	event.stopPropagation();

	// Adiciona o hidden na imagem  que foi feito o upload.
	binaryFile.nowThis.siblings('.tooltip-image').addClass('hidden');

	binaryFile.nowThis = {};

	jQuery("html").off('click', 'body', binaryFile.imgBtnDesactivate);
};

/**
	 * Ativa o botão para exibir a imagem.
	 * @param event event Evento disparado.
	 * @return void
	 */
binaryFile.imgBtnActivate = function(event){

	// Para o comportomento normal do evento.
	event.preventDefault();
	// Para o eventos posteriores a este.
	event.stopPropagation();

	// Pega link clicado.
    var linkSeeFile = jQuery(this);
	// Pega contexto do link.
	var linkContext = linkSeeFile.closest(".file-upload-context");
	// Pega tooltip da imagem.
    var tooltipImage = linkContext.find('.tooltip-image');
    console.log(tooltipImage);
	// Pega input com dados do arquivo.
	var inputStringFile = linkContext.find('input[data-id=upload-binary-hidden]');

	// Pega o valor do input com dados do arquivo (formato JSON).
	var inputValue = inputStringFile.val();

	// Realiza decode do JSON do valor do campo.
	var dataFile = jQuery.parseJSON(inputValue);

	// Pega string do arquivo.
	var stringFile = dataFile.stringFile;
	// Pega mime type do arquivo.
	var mimeTypeFile = dataFile.mimeTypeFile;

	// Verifica se link é para um arquivo que não é uma imagem.
	if(!binaryFile.verifyType(mimeTypeFile, "image")){
		binaryFile.displayFile(mimeTypeFile, stringFile);
	}else{
		// Verifica se o "tooltip" da imagem tem a classe hidden.
		if(tooltipImage.hasClass('hidden')){
			// Remove a classe hidden no tooltip da imagem.
			tooltipImage.removeClass('hidden');
		}else{
			// Adiciona a classe hidden no tooltip da imagem.
			tooltipImage.addClass('hidden');
		}
	}

	jQuery("html").off('click', 'body', binaryFile.imgBtnDesactivate);
	jQuery("html").on('click', 'body', binaryFile.imgBtnDesactivate);
	binaryFile.nowThis = linkSeeFile;
};

/**
 * Método que mostra conteúdo de um arquivo genérico.
 * @param string mimeTypeFile O Mime type do arquivo.
 * @param string stringFileEncodedBase64 String do arquivo códificada em base 64.
 * @return void
 */
binaryFile.displayFile = function(mimeTypeFile, stringFileEncodedBase64){

	// Flag que verifica se o navegador é o Internet Explorer.
	var isInternetExplore = binaryFile.detectIE();

	// Se não é o Internet Explorer.
	if(!isInternetExplore){
		window.open("data:"+ mimeTypeFile + ";base64," +  stringFileEncodedBase64);
	}else{
		// Cria o final da URL.
		var url = "index.php?option=com_nobossajax&library=noboss.file.binaryfromfile&method=viewFile";
		// Monta URL completa.
		var fullUrl = baseNameUrl + url;

		// Copia o input de upload do arquivo.
		var inputFileUploaded = jQuery("[data-id='upload-binary-hidden']").clone();
		// Altera o nome do input copiado.
		inputFileUploaded.attr("name", "fileUploadedData");
		// Cria um formulário para submissão do input do arquivo.
		var formToViewFile = jQuery("<form name='form_to_view_file' style='margin: 0; padding: 0;' target='_blank' method='POST' enctype='multipart/form-data' action='"+fullUrl+"'></form>");
		// Insere o formulário na página após o input do arquivo.
		formToViewFile.insertBefore(inputFileUploaded);
		// Insere o input do arquivo dentro do formulário.
		formToViewFile.append(inputFileUploaded);
		// Realiza submit do formulário.
		formToViewFile.submit();
		// Remove o formulário.
		formToViewFile.remove();
	}
};

/**
 * Detecta se o navegador é i Internet Explorer
 * @return string Retorna a versão do IE or false se não é o IE.
 */
binaryFile.detectIE = function() {
	// Pega a agente do navegador.
	var userAgent = window.navigator.userAgent;

	// Internet Explorer 10 ou anterior.
	var msie = userAgent.indexOf('MSIE ');
	if (msie > 0) {
		return parseInt(userAgent.substring(msie + 5, userAgent.indexOf('.', msie)), 10);
	}

	// Internet Explorer 11.
	var trident = userAgent.indexOf('Trident/');
	if (trident > 0) {
		var rv = userAgent.indexOf('rv:');
		return parseInt(userAgent.substring(rv + 3, userAgent.indexOf('.', rv)), 10);
	}

	// Internet Explorer 12 e posteriores.
	var edge = userAgent.indexOf('Edge/');
	if (edge > 0) {
		return parseInt(userAgent.substring(edge + 5, userAgent.indexOf('.', edge)), 10);
	}

	// Não é internet Explorer
	return false;
};
