/**
 * Created by lerny on 25/11/15.
 */
zona = {
    url: "index.php?m=Configuracion&c=Zona",
    init: function () {
        $('#formZona').validate({
            rules: {
                inputZona: {
                    required: true
                }
            },
            submitHandler: function (form) {
                zona.sentZona();
            },
            messages: {
                inputZona: {
                    required: "Los nombres son obligatorios."
                }
            }
        });
        $("#buttonZona").on("click", zona.submit);
        $("#buttonZonaCancel").on("click", zona.cancel);
        $("#zonaSearchToogle").on("click", function () {
            var status = $(this).data("status");
            if(status == undefined) {
                status = false;
            }
            $(this).parent().find("div[class*='panel-body']").css({
                display: status?"none":"block"
            });
            $(this).parent().find("div[class*='panel-footer']").css({
                display: status?"none":"block"
            });
            $(this).data("status", !status);
        });
        zona.eventsListado();
    },
    submit: function () {
        $('#formZona').submit();
    },
    sentZona: function () {
        loading.load("#containerLoading");
        $.ajax({
            url: zona.url + "&a=save",
            data: {
                id: $("#inputId").val(),
                zona: $("#inputZona").val(),
                estado: $("#inputEstado").val()
            },
            type: "post",
            success: function (response) {
                var r = eval("("+response+")");
                loading.unload("#containerLoading");
                if(!r.error) {
                    window.location = zona.url;
                } else {
                    mensajes.alert("Error", r.msg, 3);
                }

            }
        });
    },
    eliminar: function (id) {
        if(id != undefined && id != "") {
            var m = mensajes.confirmation("Error", "Desea realmente eliminar el registro?", function() {
                loading.load("#containerLoading");
                $.ajax({
                    url: zona.url + "&a=delete",
                    data: {
                        id: id
                    },
                    type: "post",
                    success: function (response) {
                        var r = eval("("+response+")");
                        m.modal('hide');
                        loading.unload("#containerLoading");
                        if(!r.error) {
                            zona.listado();
                        } else {
                            mensajes.alert("Error", r.msg, 3);
                        }
                    }
                });
            });
        } else {
            mensajes.alert("Error", "Debe seleccionar un registro valido.", 3);
        }
    },
    listado: function (url) {
        var estado = [];
        $('#inputEstadosearch :selected').each(function(i, selected){
            estado[i] = $(selected).val();
        });
        loading.load("#containerLoading");
        $.ajax({
            url: (url == undefined)?zona.url + "&a=listado":url,
            data: {
                zona: $("#inputZonaSearch").val(),
                estado: estado
            },
            type: "get",
            success: function (response) {
                $("#containerListadoZona").html(response);
                loading.unload("#containerLoading");
                zona.eventsListado();
            }
        });
    },
    listadoSearch: function (url) {
        loading.load("#containerLoadingSearch");
        var campo = $("#inputCampos").val();
        var valor = $("#inputSearch").val();
        var data = {};
        data[campo] = valor;
        $.ajax({
            url: (url == undefined)?zona.url + "&a=listadoSearch":url,
            data: data,
            type: "get",
            success: function (response) {
                $("#containerListadoSearch").html(response);
                loading.unload("#containerLoadingSearch");
                zona.eventsListadoSearch();
            }
        });
    },
    launchEdit: function (id) {
        if(id != undefined && id != "") {
            window.location = zona.url + "&a=edit&id=" + id;
        } else {
            mensajes.alert("Error", "Debe seleccionar un registro valido.", 3);
        }
    },
    cancel: function () {
        window.history.back(-1);
    },
    eventsListado: function () {
        $("#paginationZonas").find("a").each(function () {
            $(this).on("click", function () {
                zona.listado($(this).attr("href"));
                return false;
            });
        });
    },
    eventsListadoSearch: function () {
        $("#containerListadoSearch .selected-option").each(function() {
            $(this).on("click", function () {
                eval("("+$("#containerListadoSearch").attr("data-callback")+"("+$(this).attr("data-object")+"))");
            });
        });
        $("#paginationZonas").find("a").each(function () {
            $(this).on("click", function () {
                zona.listadoSearch($(this).attr("href"));
                return false;
            });
        });
    }
};