/**
 * Created by lerny on 26/11/15.
 */
loading = {
    html: '<span class="glyphicon glyphicon-refresh glyphicon-refresh-animate"></span> Cargando...',
    load: function (element) {
        if(element == undefined) {
            element = "#containerLoading";
        }
        $(element).html(loading.html);
    },
    unload: function (element) {
        if(element == undefined) {
            element = "#containerLoading";
        }
        $(element).html("");
    }
};