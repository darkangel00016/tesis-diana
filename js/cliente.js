/**
 * Created by lerny on 25/11/15.
 */
cliente = {
    url: "index.php?m=Loans&c=Cliente",
    init: function () {
        $('#formCliente').validate({
            rules: {
                inputNombres: {
                    required: true
                },
                inputApellidos: {
                    required: true
                },
                inputTelefono: {
                    required: true
                },
                inputDireccion: {
                    required: true
                },
                inputBarrio: {
                    required: true
                },
                inputCiudad: {
                    required: true
                }
            },
            submitHandler: function (form) {
                cliente.sentCliente();
            },
            messages: {
                inputNombres: {
                    required: "Los nombres son obligatorios."
                },
                inputApellidos: {
                    required: "Los apellidos son obligatorios."
                },
                inputTelefono: {
                    required: "El número de teléfono es obligatorio."
                },
                inputDireccion: {
                    required: "La dirección es obligatorio."
                },
                inputBarrio: {
                    required: "El barrio es obligatorio."
                },
                inputCiudad: {
                    required: "La ciudad es obligatoria."
                }
            }
        });
        $("#buttonCliente").on("click", cliente.submit);
        $("#buttonClienteCancel").on("click", cliente.cancel);
        $("#clienteSearchToogle").on("click", function () {
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
        cliente.eventsListado();
    },
    submit: function () {
        $('#formCliente').submit();
    },
    sentCliente: function () {
        loading.load("#containerLoading");
        $.ajax({
            url: cliente.url + "&a=save",
            data: {
                id: $("#inputId").val(),
                nombres: $("#inputNombres").val(),
                apellidos: $("#inputApellidos").val(),
                telefono: $("#inputTelefono").val(),
                direccion: $("#inputDireccion").val(),
                barrio: $("#inputBarrio").val(),
                ciudad: $("#inputCiudad").val(),
                observaciones: $("#inputObservaciones").val(),
                estado: $("#inputEstado").val(),
                zona: $("#inputZona").val()
            },
            type: "post",
            success: function (response) {
                var r = eval("("+response+")");
                loading.unload("#containerLoading");
                if(!r.error) {
                    window.location = cliente.url;
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
                    url: cliente.url + "&a=delete",
                    data: {
                        id: id
                    },
                    type: "post",
                    success: function (response) {
                        var r = eval("("+response+")");
                        m.modal('hide');
                        loading.unload("#containerLoading");
                        if(!r.error) {
                            cliente.listado();
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
        var zona = [];
        $('#inputZonaSearch :selected').each(function(i, selected){
            zona[i] = $(selected).val();
        });
        var rate = [];
        $('#inputRateSearch :selected').each(function(i, selected){
            rate[i] = $(selected).val();
        });
        loading.load("#containerLoading");
        $.ajax({
            url: (url == undefined)?cliente.url + "&a=listado":url,
            data: {
                nombres: $("#inputNombresSearch").val(),
                apellidos: $("#inputApellidosSearch").val(),
                telefono: $("#inputTelefonoSearch").val(),
                barrio: $("#inputBarrioSearch").val(),
                direccion: $("#inputDireccionSearch").val(),
                ciudad: $("#inputCiudadSearch").val(),
                observaciones: $("#inputObservacionesSearch").val(),
                estado: estado,
                zona: zona,
                rate: rate
            },
            type: "get",
            success: function (response) {
                $("#containerListadoCliente").html(response);
                loading.unload("#containerLoading");
                cliente.eventsListado();
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
            url: (url == undefined)?cliente.url + "&a=listadoSearch":url,
            data: data,
            type: "get",
            success: function (response) {
                $("#containerListadoSearch").html(response);
                loading.unload("#containerLoadingSearch");
                cliente.eventsListadoSearch();
            }
        });
    },
    launchEdit: function (id) {
        if(id != undefined && id != "") {
            window.location = cliente.url + "&a=edit&id=" + id;
        } else {
            mensajes.alert("Error", "Debe seleccionar un registro valido.", 3);
        }
    },
    cancel: function () {
        window.history.back(-1);
    },
    eventsListado: function () {
        $("#paginationClientes").find("a").each(function () {
            $(this).on("click", function () {
                cliente.listado($(this).attr("href"));
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
        $("#paginationClientes").find("a").each(function () {
            $(this).on("click", function () {
                cliente.listadoSearch($(this).attr("href"));
                return false;
            });
        });
    }
};