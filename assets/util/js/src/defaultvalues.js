// verifica se o objeto util não existe para criá-lo
if(!util){
    var util = {};
}

// contorno para os campos do tipo number
util.numberField = function(){
    // evento de focusout
    jQuery('#content').on('focusout', 'input[type="number"]', function(){
        // se nao foi especificado um valor
        if(!jQuery(this).val()){
            var newVal = jQuery(this).attr('min') ? jQuery(this).attr('min') : 0;
            // seta como valor o minimo definido no xml ou 0
            jQuery(this).val(newVal);
        }
    });
};

// contorno para os campos do tipo textarea
util.textareaField = function(){
    // evento de focusout
    jQuery('#content').on('focusout', 'textarea', function(){
        // se nao foi especificado um valor
        if(!jQuery(this).val()){
            // coloca um espaço em branco como value
            jQuery(this).val(" ");
        }
    });
};

// contorno para os campos color
// util.colorField = function(){
//     // evento de focusout
//     jQuery('#content').on('focusout', '.minicolors-input[data-format="rgba"]', function(){
//         // se nao foi especificado um valor
//         if(!jQuery(this).val()){
//             // seta valor default
//             jQuery(this).minicolors('value', 'rgba(0, 0, 0, 0)');
//         }
//     });
// };

// chamada das funções de correções de campos com valores default
util.fixDefaultValues = function(){
    util.numberField();
    util.textareaField();
    // o trecho abaixo foi comentado para que a correcao dos valores do campo de cor nao ocorra mais, para que seja possivel fazer o tratamento de cor primaria do template
    // util.colorField();   
};
