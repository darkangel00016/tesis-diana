/**
 * Created by lerny on 26/11/15.
 */
loading = {
    load: function (element) {
        if(element == undefined) {
            element = "#containerLoading";
        }
        $(element).html('<span class="glyphicon glyphicon-refresh glyphicon-refresh-animate"></span> Cargando...');
    },
    unload: function (element) {
        if(element == undefined) {
            element = "#containerLoading";
        }
        $(element).html("");
    }
};