/**
 * Created by lerny on 25/11/15.
 */
metodo = {
    url: "index.php?m=Configuracion&c=Metodo",
    init: function () {
        $('#formMetodo').validate({
            rules: {
                inputMetodo: {
                    required: true
                }
            },
            submitHandler: function (form) {
                metodo.sentMetodo();
            },
            messages: {
                inputMetodo: {
                    required: "Los nombres son obligatorios."
                }
            }
        });
        $("#buttonMetodo").on("click", metodo.submit);
        $("#buttonMetodoCancel").on("click", metodo.cancel);
        $("#metodoSearchToogle").on("click", function () {
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
        metodo.eventsListado();
    },
    submit: function () {
        $('#formMetodo').submit();
    },
    sentMetodo: function () {
        loading.load("#containerLoading");
        $.ajax({
            url: metodo.url + "&a=save",
            data: {
                id: $("#inputId").val(),
                metodo: $("#inputMetodo").val(),
                estado: $("#inputEstado").val()
            },
            type: "post",
            success: function (response) {
                var r = eval("("+response+")");
                loading.unload("#containerLoading");
                if(!r.error) {
                    window.location = metodo.url;
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
                    url: metodo.url + "&a=delete",
                    data: {
                        id: id
                    },
                    type: "post",
                    success: function (response) {
                        var r = eval("("+response+")");
                        m.modal('hide');
                        loading.unload("#containerLoading");
                        if(!r.error) {
                            metodo.listado();
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
            url: (url == undefined)?metodo.url + "&a=listado":url,
            data: {
                metodo: $("#inputMetodoSearch").val(),
                estado: estado
            },
            type: "get",
            success: function (response) {
                $("#containerListadoMetodo").html(response);
                loading.unload("#containerLoading");
                metodo.eventsListado();
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
            url: (url == undefined)?metodo.url + "&a=listadoSearch":url,
            data: data,
            type: "get",
            success: function (response) {
                $("#containerListadoSearch").html(response);
                loading.unload("#containerLoadingSearch");
                metodo.eventsListadoSearch();
            }
        });
    },
    launchEdit: function (id) {
        if(id != undefined && id != "") {
            window.location = metodo.url + "&a=edit&id=" + id;
        } else {
            mensajes.alert("Error", "Debe seleccionar un registro valido.", 3);
        }
    },
    cancel: function () {
        window.history.back(-1);
    },
    eventsListado: function () {
        $("#paginationMetodos").find("a").each(function () {
            $(this).on("click", function () {
                metodo.listado($(this).attr("href"));
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
        $("#paginationMetodos").find("a").each(function () {
            $(this).on("click", function () {
                metodo.listadoSearch($(this).attr("href"));
                return false;
            });
        });
    }
};