/**
 * Created by lerny on 25/11/15.
 */
porcentaje = {
    url: "index.php?m=Configuracion&c=Porcentaje",
    init: function () {
        $('#formPorcentaje').validate({
            rules: {
                inputPorcentaje: {
                    required: true
                }
            },
            submitHandler: function (form) {
                porcentaje.sentPorcentaje();
            },
            messages: {
                inputPorcentaje: {
                    required: "Los nombres son obligatorios."
                }
            }
        });
        $("#buttonPorcentaje").on("click", porcentaje.submit);
        $("#buttonPorcentajeCancel").on("click", porcentaje.cancel);
        $("#porcentajeSearchToogle").on("click", function () {
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
        porcentaje.eventsListado();
        $("#inputPorcentaje").on("keypress", function(event) {
            var e = new events(event, $(this).val());
            if(!e.validateNumber()) {
                event.preventDefault();
                event.stopPropagation();
            }
        });
    },
    submit: function () {
        $('#formPorcentaje').submit();
    },
    sentPorcentaje: function () {
        loading.load("#containerLoading");
        $.ajax({
            url: porcentaje.url + "&a=save",
            data: {
                id: $("#inputId").val(),
                porcentaje: $("#inputPorcentaje").val(),
                estado: $("#inputEstado").val()
            },
            type: "post",
            success: function (response) {
                var r = eval("("+response+")");
                loading.unload("#containerLoading");
                if(!r.error) {
                    window.location = porcentaje.url;
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
                    url: porcentaje.url + "&a=delete",
                    data: {
                        id: id
                    },
                    type: "post",
                    success: function (response) {
                        var r = eval("("+response+")");
                        m.modal('hide');
                        loading.unload("#containerLoading");
                        if(!r.error) {
                            porcentaje.listado();
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
            url: (url == undefined)?porcentaje.url + "&a=listado":url,
            data: {
                porcentaje: $("#inputPorcentajeSearch").val(),
                estado: estado
            },
            type: "get",
            success: function (response) {
                $("#containerListadoPorcentaje").html(response);
                loading.unload("#containerLoading");
                porcentaje.eventsListado();
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
            url: (url == undefined)?porcentaje.url + "&a=listadoSearch":url,
            data: data,
            type: "get",
            success: function (response) {
                $("#containerListadoSearch").html(response);
                loading.unload("#containerLoadingSearch");
                porcentaje.eventsListadoSearch();
            }
        });
    },
    launchEdit: function (id) {
        if(id != undefined && id != "") {
            window.location = porcentaje.url + "&a=edit&id=" + id;
        } else {
            mensajes.alert("Error", "Debe seleccionar un registro valido.", 3);
        }
    },
    cancel: function () {
        window.history.back(-1);
    },
    eventsListado: function () {
        $("#paginationPorcentajes").find("a").each(function () {
            $(this).on("click", function () {
                porcentaje.listado($(this).attr("href"));
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
        $("#paginationPorcentajes").find("a").each(function () {
            $(this).on("click", function () {
                porcentaje.listadoSearch($(this).attr("href"));
                return false;
            });
        });
    }
};