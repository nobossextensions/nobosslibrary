jQuery(document).ready(function (jQuery) {
    // Declara $ para evitar conflito
    $ = jQuery;

    nobosseditor.CONSTRUCTOR();
});

var nobosseditor = {};

nobosseditor.CONSTRUCTOR = function () {
    jQuery(window).on('load', function(){
        nobosseditor.addCssFileEditor();
    });
};

/**
 * Adiciona arquivo CSS dentro do iframe do editor
 */
nobosseditor.addCssFileEditor = function () {
    // Necessario aguardar o iframe ser gerado via JS na pagina
    setTimeout(function(){
        // Adicionar arquivo CSS dentro do editor
        jQuery('.js-editor-tinymce iframe').contents().find('head').append('<link rel="stylesheet" href="'+baseNameUrl+'libraries/noboss/forms/fields/assets/stylesheets/css/nobosseditorcontentiframe.min.css"/>');
     }, 300);
};
