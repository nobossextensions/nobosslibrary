jQuery(document).ready(function (jQuery) {
    // Declara $ para evitar conflito
    $ = jQuery;
    // Scripts que são necessários estarem carregados na página
    var scripts = [
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
        // Chama o constructor
        nobossinstagramlogin.CONSTRUCTOR();
    });
});

nobossinstagramlogin = {};

nobossinstagramlogin.CONSTRUCTOR = function (){
    jQuery('body').on('click', '[data-id="instagram-login-btn"]', nobossinstagramlogin.openInstagramLogin);
    jQuery('body').on('click', '[data-id="instagram-logoff-btn"]', nobossinstagramlogin.logoffInstagram);
    // Evento de clique no botão de renovar a licença
    window.addEventListener("message", nobossinstagramlogin.filterIframeMessage, false);
};

/**
 * Chamado quando o usuário clica no botão de fazr logoff
 * 
 * @param {Event} e Evento de clique
 */
nobossinstagramlogin.logoffInstagram = function(e){
    // Remove o nome do usuário do lado do botão
    jQuery('[data-id="instagram-logged-user"]').html('');
    jQuery('[data-id="instagram-logged-user"]').closest('[data-id="instagram-logged"]').hide();
    // Esconde o botão de logoff e exibe o de login
    jQuery('[data-id="instagram-logoff-btn"]').hide();
    jQuery('[data-id="instagram-login-btn"]').show();
    // Limpa os dados do campo hidden
    jQuery('[data-id="instagram-input-hidden"]').val('');
    jQuery('<img data-id="instagram-logoff-img" src="http://instagram.com/accounts/logout/" width="0" height="0" style="display:none;"/>').insertAfter(jQuery('[data-id="instagram-input-hidden"]'));
};

/**
 * Abre uma modal com a listagem dos planos disponíveis para o usuário renovar ou fazer um upgrade
 */
nobossinstagramlogin.openInstagramLogin = function(e){
    // Remove a imagem de logoff caso exista
    jQuery('[data-id="instagram-logoff-img"]').remove();
    // 'index.php?option=com_nbextensoes&view=buyprocess&task=buyprocess.getCoupon&format=raw
    var redirect = Joomla.JText._('NOBOSS_EXTENSIONS_URL_SITE')+'/social-media-proxy?media=instagram';
    var iframeUrl = 'https://api.instagram.com/oauth/authorize/?client_id=9e6ba58ccf804a35a8401a5d5400622c&redirect_uri='+redirect+'&response_type=code';
    // Abre o popup centralizado
    nobossinstagramlogin.popitup(iframeUrl, 'InstagramLogin', 450, 500);
};

/**
 * Recebe os parametros e abre uma janela popup
 * 
 * @param {String} url Url que o popup vai abrir
 * @param {String} title Título do popup
 * @param {Int} w Largura do popup
 * @param {Int} h altura do popup
 */
nobossinstagramlogin.popitup = function (url, title, w, h) {
    // Fixes dual-screen position                         Most browsers      Firefox
    var dualScreenLeft = window.screenLeft != undefined ? window.screenLeft : window.screenX;
    var dualScreenTop = window.screenTop != undefined ? window.screenTop : window.screenY;

    var width = window.innerWidth ? window.innerWidth : document.documentElement.clientWidth ? document.documentElement.clientWidth : screen.width;
    var height = window.innerHeight ? window.innerHeight : document.documentElement.clientHeight ? document.documentElement.clientHeight : screen.height;

    var left = ((width / 2) - (w / 2)) + dualScreenLeft;
    var top = ((height / 2) - (h / 2)) + dualScreenTop;
    var newWindow = window.open(url, title, 'scrollbars=yes, width=' + w + ', height=' + h + ', top=' + top + ', left=' + left);

    // Puts focus on the newWindow
    if (window.focus) {
        newWindow.focus();
    }
};

nobossinstagramlogin.saveInstagramLoginInfo = function(data) {
    // Remove o nome do usuário do lado do botão
    jQuery('[data-id="instagram-logged-user"]').html('@'+data.user.username);
    jQuery('[data-id="instagram-logged-user"]').closest('[data-id="instagram-logged"]').show();
    jQuery('[data-id="instagram-logged-user"]').parent('a').attr('href', 'https://www.instagram.com/'+data.user.username);
    // Esconde o botão de logoff e exibe o de login
    jQuery('[data-id="instagram-logoff-btn"]').show();
    jQuery('[data-id="instagram-login-btn"]').hide();
    // Limpa os dados do campo hidden
   jQuery('[data-id="instagram-input-hidden"]').val(JSON.stringify(data));
};
