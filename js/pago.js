/**
 * Created by lerny on 25/11/15.
 */
pago = {
    url: "index.php?m=Loans&c=Pago",
    dialogSearch: "",
    dialogSearchPrestamo: "",
    dialogSearchCobrador: "",
    init: function () {
        $('#formPago').validate({
            rules: {
                inputClienteNombres: {
                    required: true
                },
                inputPrestamo: {
                    required: true
                },
                inputMonto: {
                    required: true
                },
                inputCobradorNombres: {
                    required: true
                },
                inputInteres: {
                    required: true
                },
                inputFecha: {
                    required: true
                }
            },
            submitHandler: function (form) {
                var id = $("#inputId").val();
                if(id == "") {
                    if ($(".cuota:checked").length > 0) {
                        pago.sentPago();
                    } else {
                        mensajes.alert("Error", "Debe seleccionar al menos una cuota a pagar.");
                    }
                } else {
                    pago.sentPago();
                }
            },
            messages: {
                inputClienteNombres: {
                    required: "Debe seleccionar un cliente."
                },
                inputPrestamo: {
                    required: "Debe seleccionar un prestamo."
                },
                inputCobradorNombres: {
                    required: "Debe seleccionar un cobrador."
                },
                inputInteres: {
                    required: "El interes es obligatorio."
                },
                inputFecha: {
                    required: "Debe seleccionar la fecha."
                },
                inputMonto: {
                    required: "Debe seleccionar un prestamo y las cuotas."
                }
            },
            errorPlacement: function (error, element) {
                var parent = element.parent();
                if(parent.attr("class").match(/input-group/)) {
                    error.appendTo($(parent).parent());
                } else {
                    error.appendTo(parent);
                }
            }
        });
        $("#buttonPago").on("click", pago.submit);
        $("#buttonSearchCliente").on("click", pago.search);
        $("#buttonSearchPrestamo").on("click", pago.searchPrestamo);
        $("#buttonSearchCobrador").on("click", pago.searchCobrador);
        $("#buttonPagoCancel").on("click", pago.cancel);
        $("#pagoSearchToogle").on("click", function () {
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
        pago.eventsListado();
        $("#inputMonto").on("keypress", function(event) {
            var e = new events(event, $(this).val());
            if(!e.validateReal()) {
                event.preventDefault();
                event.stopPropagation();
            }
        });
    },
    search: function () {
        pago.dialogSearch = mensajes.search("Seleccion de cliente", {
            loading: loading.html,
            fields: [
                { text: "Nombres", value: "nombres" },
                { text: "Apellidos", value: "apellidos" },
                { text: "Teléfono", value: "telefono" },
                { text: "Dirección", value: "direccion" },
                { text: "Barrio", value: "barrio" },
                { text: "Ciudad", value: "ciudad" }
            ],
            "callback": "pago.selected"
        }, function(){
            cliente.listadoSearch();
        });
    },
    searchPrestamo: function () {
        var idCliente = $("#inputCliente").val();
        if(idCliente != "") {
            pago.dialogSearchPrestamo = mensajes.search("Selección de prestamo", {
                loading: loading.html,
                fields: [],
                hidden: [
                    {'id': 'inputSearchIdCliente', 'value': idCliente}
                ],
                "callback": "pago.selectedPrestamo"
            }, function () {
                prestamo.listadoSearch();
            });
        } else {
            mensajes.alert("Error", "Debe seleccionar el cliente.")
        }
    },
    searchCobrador: function () {
        pago.dialogSearchCobrador = mensajes.search("Selección de cobrador", {
            loading: loading.html,
            fields: [],
            hidden: [],
            "callback": "pago.selectedCobrador"
        }, function () {
            user.listadoSearch();
        });
    },
    selectedPrestamo: function (objeto) {
        $("#inputPrestamo").val(objeto.id);
        pago.dialogSearchPrestamo.modal("hide");
        pago.requestCuotas();
    },
    selected: function (objeto) {
        $("#inputClienteNombres").val(objeto.nombres + ", " + objeto.apellidos);
        $("#inputCliente").val(objeto.id);
        $("#inputPrestamo").val("");
        $("#containerCuotasPagos").hide();
        pago.dialogSearch.modal("hide");
    },
    selectedCobrador: function (objeto) {
        $("#inputCobradorNombres").val(objeto.nombre);
        $("#inputCobrador").val(objeto.id);
        pago.dialogSearchCobrador.modal("hide");
    },
    changeCuota: function (o) {
        var selected = $(o).prop("checked");
        if(selected) {
            $(o).parent().parent().next().find("input[class*='cuota']").prop("disabled", false);
            $(o).parent().parent().next().removeClass("active");
        } else {
            $(o).parent().parent().nextAll().find("input[class*='cuota']").prop("disabled", true);
            $(o).parent().parent().nextAll().find("input[class*='cuota']").prop("checked", false);
            $(o).parent().parent().nextAll().addClass("active");
        }
        var montos = 0;
        $('.cuota:checked').each(function(i){
            var monto = parseFloat($(this).attr("data-monto"));
            var interes = parseFloat($(this).attr("data-interes"));
            montos += monto + interes;
        });
        $("#inputMonto").val(montos);
    },
    requestCuotas: function () {
        var id = $("#inputPrestamo").val();
        if(id != "") {
            $("#containerCuotasPagos").show();
            $("#containerCuotasPagos .body").html(loading.html);
            $.ajax({
                url: prestamo.url + "&a=cuotas&is_ajax=true",
                data: {
                    "id": id
                },
                type: 'get',
                dataType: 'json',
                success: function (response) {
                    if(response.error) {
                        $("#containerCuotasPagos .body").html(response.msg);
                    } else if (response.cuotas.length == 0) {
                        $("#containerCuotasPagos .body").html("No tiene cuotas pendientes.");
                    } else {
                        var html = '<div style="max-height: 200px; overflow: auto;"><table class="table table-responsive">';
                        html += '<tr>';
                        html += '<th></th>';
                        html += '<th>Fecha</th>';
                        html += '<th>Monto</th>';
                        html += '<th>Interes</th>';
                        html += '</tr>';
                        for(var i = 0; i < response.cuotas.length; i++) {
                            var selected = (i == 0)?"":"disabled";
                            var tr = (i == 0)?"":"active";
                            html += '<tr class="'+tr+'">';
                            html += '<th><input type="checkbox" value="' + response.cuotas[i].id + '" '+selected+' data-monto="'+response.cuotas[i].monto+'" data-interes="'+response.cuotas[i].interes+'" class="cuota" onclick="pago.changeCuota(this)" /></th>';
                            html += '<th>' + response.cuotas[i].fecha + '</th>';
                            html += '<th>' + response.cuotas[i].monto + '</th>';
                            html += '<th>' + response.cuotas[i].interes + '</th>';
                            html += '</tr>';
                        }
                        html += '</table></div>';
                        $("#containerCuotasPagos .body").html(html);
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    $("#containerCuotasPagos .body").html(textStatus);
                }
            });
        } else {
            mensajes.alert("Error", "Debe seleccionar el prestamo.")
        }
    },
    submit: function () {
        $('#formPago').submit();
    },
    sentPago: function () {
        loading.load("#containerLoading");
        var items = [];
        var montos = [];
        $('.cuota:checked').each(function(i){
            var monto = parseFloat($(this).attr("data-monto"));
            var interes = parseFloat($(this).attr("data-interes"));
            items[i] = $(this).val();
            montos[i] = monto + interes;
        });
        $.ajax({
            url: pago.url + "&a=save",
            data: {
                id: $("#inputId").val(),
                cliente: $("#inputCliente").val(),
                cobrador: $("#inputCobrador").val(),
                prestamo: $("#inputPrestamo").val(),
                metodo: $("#inputMetodo").val(),
                monto: $("#inputMonto").val(),
                origen: 'cuota',
                fecha: $("#inputFecha").val(),
                observaciones: $("#inputObservaciones").val(),
                estado: $("#inputEstado").val(),
                items: items,
                montos: montos
            },
            type: "post",
            success: function (response) {
                var r = eval("("+response+")");
                loading.unload("#containerLoading");
                if(!r.error) {
                    window.location = pago.url;
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
                    url: pago.url + "&a=delete",
                    data: {
                        id: id
                    },
                    type: "post",
                    success: function (response) {
                        var r = eval("("+response+")");
                        m.modal('hide');
                        loading.unload("#containerLoading");
                        if(!r.error) {
                            pago.listado();
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
    rechazar: function (id) {
        if(id != undefined && id != "") {
            var m = mensajes.confirmation("Error", "Desea realmente rechazar el pago?", function() {
                loading.load("#containerLoading");
                $.ajax({
                    url: pago.url + "&a=rechazar",
                    data: {
                        id: id
                    },
                    type: "post",
                    success: function (response) {
                        var r = eval("("+response+")");
                        m.modal('hide');
                        loading.unload("#containerLoading");
                        if(!r.error) {
                            pago.listado();
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
    aprobar: function (id) {
        if(id != undefined && id != "") {
            var m = mensajes.confirmation("Error", "Desea realmente aprobar el pago?", function() {
                loading.load("#containerLoading");
                $.ajax({
                    url: pago.url + "&a=aprobar",
                    data: {
                        id: id
                    },
                    type: "post",
                    success: function (response) {
                        var r = eval("("+response+")");
                        m.modal('hide');
                        loading.unload("#containerLoading");
                        if(!r.error) {
                            pago.listado();
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
    reversar: function (id) {
        if(id != undefined && id != "") {
            var m = mensajes.confirmation("Error", "Desea realmente reversar el pago?", function() {
                loading.load("#containerLoading");
                $.ajax({
                    url: pago.url + "&a=reversar",
                    data: {
                        id: id
                    },
                    type: "post",
                    success: function (response) {
                        var r = eval("("+response+")");
                        m.modal('hide');
                        loading.unload("#containerLoading");
                        if(!r.error) {
                            pago.listado();
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
        var usuarios = [];
        $('#inputCobradorSearch :selected').each(function(i, selected){
            usuarios[i] = $(selected).val();
        });
        loading.load("#containerLoading");
        $.ajax({
            url: (url == undefined)?pago.url + "&a=listado":url,
            data: {
                cliente: $("#inputClienteSearch").val(),
                cobrador: usuarios,
                fecha_start: $('#inputFechaStartSearch').val(),
                fecha_end: $('#inputFechaEndSearch').val(),
                estado: estado
            },
            type: "get",
            success: function (response) {
                $("#containerListadoPago").html(response);
                loading.unload("#containerLoading");
                pago.eventsListado();
            }
        });
    },
    launchEdit: function (id) {
        if(id != undefined && id != "") {
            window.location = pago.url + "&a=edit&id=" + id;
        } else {
            mensajes.alert("Error", "Debe seleccionar un registro valido.", 3);
        }
    },
    cancel: function () {
        window.history.back(-1);
    },
    eventsListado: function () {
        $("#paginatioPagos").find("a").each(function () {
            $(this).on("click", function () {
                pago.listado($(this).attr("href"));
                return false;
            });
        });
    }
};