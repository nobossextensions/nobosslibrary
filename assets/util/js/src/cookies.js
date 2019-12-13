// verifica se o objeto util não existe para criá-lo
if(!util){
    var util = {};
}

util.setCookie = function (cname, cvalue, exdays) {
    var expires = '';
    if (exdays != undefined) {
        var d = new Date();
        d.setTime(d.getTime() + (exdays*24*60*60*1000));
        expires = ';expires='+ d.toUTCString();
    }
    document.cookie = cname + '=' + cvalue + expires + ';path=/';
};

util.getCookie = function (cname) {
    var name = cname + '=';
    var decodedCookie = decodeURIComponent(document.cookie);
    var ca = decodedCookie.split(';');
    for(var i = 0; i <ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) == ' ') {
            c = c.substring(1);
        }
        if (c.indexOf(name) == 0) {
            return c.substring(name.length, c.length);
        }
    }
    return '';
};
