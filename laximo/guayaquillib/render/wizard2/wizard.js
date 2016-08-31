$(function(){
    linkHandler();
})

//извлечение параметров из урла
function getUrlVars(url) {
    if (!url) {
        var url = window.location.href; 
    }
    var vars = {};      
    var parts = url.replace(/[?&]+([^=&]+)=([^&]*)/gi, function(m,key,value) {  
        vars[key] = value;       
    });               
    return vars;      
}

function openWizard(ssd) {

    var url_params = getUrlVars();              
    url = "wizard2.php?c=" + url_params.c + "&ssd=" + ssd;
    $("#wizard-wrap").load(url + " #wizard-wrap > *");
    linkHandler();
    history.replaceState(3, "", url);

}

function linkHandler() {
    $("#wizard-wrap").on("click", ".remove_param", function(e){
        e.preventDefault();
        var url = $(this).attr("href");
        $("#wizard-wrap").load(url + " #wizard-wrap > *");
        history.replaceState(3, "", url);
    })
}


