/**
 * Created by lerny on 25/11/15.
 */
prestamo = {
    url: Urls.prestamo,
    dialogSearch: "",
    init: function () {
        $('#formPrestamo').validate({
            rules: {
                inputCliente: {
                    required: true
                },
                inputMonto: {
                    required: true
                },
                inputInteres: {
                    required: true
                },
                inputFechaInicio: {
                    required: true
                }
            },
            submitHandler: function (form) {
                prestamo.sentPrestamo();
            },
            messages: {
                inputCliente: {
                    required: "Debe seleccionar un cliente."
                },
                inputMonto: {
                    required: "El monto es obligatorio."
                },
                inputInteres: {
                    required: "El interes es obligatorio."
                },
                inputFechaInicio: {
                    required: "Debe seleccionar la fecha de inicio."
                }
            }
        });
        $("#buttonPrestamo").on("click", prestamo.submit);
        $("#buttonSearchCliente").on("click", prestamo.search);
        $("#buttonPrestamoCancel").on("click", prestamo.cancel);
        $("#prestamoSearchToogle").on("click", function () {
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
        prestamo.eventsListado();
        $("#inputMonto").on("keypress", function(event) {
            var e = new events(event, $(this).val());
            if(!e.validateReal()) {
                event.preventDefault();
                event.stopPropagation();
            }
        });
        $("#inputMonto").on("keyup", prestamo.calcular);
        $("#inputInteres").on("keyup", prestamo.calcular);
        $("#inputInteres").on("keypress", function(event) {
            var e = new events(event, $(this).val());
            if(!e.validateNumber()) {
                event.preventDefault();
                event.stopPropagation();
            }
        });
        $("#inputTipoIntervalo").on("change", prestamo.calcular);
        $("#inputIntervalo").on("keyup", prestamo.calcular);
        $("#inputIntervalo").on("keypress", function(event) {
            var e = new events(event, $(this).val());
            if(!e.validateNumber()) {
                event.preventDefault();
                event.stopPropagation();
            }
        });
        $("#inputFechaInicio").on("change", prestamo.calcular);
    },
    search: function () {
        prestamo.dialogSearch = mensajes.search("Seleccion de cliente", {
            loading: loading.html,
            fields: [
                { text: "Nombres", value: "nombres" },
                { text: "Apellidos", value: "apellidos" },
                { text: "Teléfono", value: "telefono" },
                { text: "Dirección", value: "direccion" },
                { text: "Barrio", value: "barrio" },
                { text: "Ciudad", value: "ciudad" }
            ],
            "callback": "prestamo.selected"
        }, function(){
            cliente.listadoSearch();
        });
    },
    selected: function (objeto) {
        $("#inputClienteNombres").val(objeto.nombres + ", " + objeto.apellidos);
        $("#inputCliente").val(objeto.id);
        prestamo.dialogSearch.modal("hide");
    },
    calcular: function (event) {
        var tipo_intervalo = $("#inputTipoIntervalo").val();
        var fecha_inicio = $("#inputFechaInicio").val();
        var intervalo = $("#inputIntervalo").val();
        var monto = $("#inputMonto").val();
        var interes = $("#inputInteres").val();
        var id = $("#inputId").val();
        if(tipo_intervalo != "" && fecha_inicio != "" && intervalo != "") {
            var interval;
            switch (tipo_intervalo) {
                case "diario": interval = "days"; break;
                case "semanal": interval = "weeks"; break;
                case "mensual": interval = "months"; break;
                case "anual": interval = "years"; break;
            }
            $("#inputFechaFin").val(moment(fecha_inicio, "YYYY/MM/DD").add(intervalo, interval).format("YYYY/MM/DD"));
        }
        if(monto != "" && interes != "") {
            monto = parseFloat(monto);
            interes = parseFloat(interes);
            var mInteres = monto * interes/100;
            $("#inputMontoInteres").val(mInteres.toFixed(2));
            if(id == "") {
                $("#inputSaldo").val((monto + mInteres).toFixed(2));
            }
        } else {
            $("#inputMontoInteres").val("");
            if(id == "") {
                $("#inputSaldo").val("");
            }
        }
    },
    submit: function () {
        $('#formPrestamo').submit();
    },
    sentPrestamo: function () {
        loading.load("#containerLoading");
        $.ajax({
            url: prestamo.url + "&a=save",
            data: {
                id: $("#inputId").val(),
                cliente: $("#inputCliente").val(),
                monto: $("#inputMonto").val(),
                interes: $("#inputInteres").val(),
                tipo_intervalo: $("#inputTipoIntervalo").val(),
                intervalo: $("#inputIntervalo").val(),
                fecha_inicio: $("#inputFechaInicio").val(),
                fecha_fin: $("#inputFechaFin").val(),
                monto_interes: $("#inputMontoInteres").val(),
                saldo: $("#inputSaldo").val(),
                observaciones: $("#inputObservaciones").val(),
                estado: $("#inputEstado").val()
            },
            type: "post",
            success: function (response) {
                var r = eval("("+response+")");
                loading.unload("#containerLoading");
                if(!r.error) {
                    window.location = prestamo.url;
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
                    url: prestamo.url + "&a=delete",
                    data: {
                        id: id
                    },
                    type: "post",
                    success: function (response) {
                        var r = eval("("+response+")");
                        m.modal('hide');
                        loading.unload("#containerLoading");
                        if(!r.error) {
                            prestamo.listado();
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
        var tipo = [];
        $('#inputTipoIntervalosearch :selected').each(function(i, selected){
            tipo[i] = $(selected).val();
        });
        var estado = [];
        $('#inputEstadosearch :selected').each(function(i, selected){
            estado[i] = $(selected).val();
        });
        var usuarios = [];
        $('#inputUsuarioSearch :selected').each(function(i, selected){
            usuarios[i] = $(selected).val();
        });
        loading.load("#containerLoading");
        $.ajax({
            url: (url == undefined)?prestamo.url + "&a=listado":url,
            data: {
                cliente: $("#inputClienteSearch").val(),
                tipo_intervalo: tipo,
                estado: estado,
                usuario: usuarios,
                fecha_inicio_start: $('#inputFechaInicioStartSearch').val(),
                fecha_inicio_end: $('#inputFechaInicioEndSearch').val(),
                fecha_fin_start: $('#inputFechaFinStartSearch').val(),
                fecha_fin_end: $('#inputFechaFinEndSearch').val()
            },
            type: "get",
            success: function (response) {
                $("#containerListadoPrestamo").html(response);
                loading.unload("#containerLoading");
                prestamo.eventsListado();
            }
        });
    },
    listadoSearch: function (url) {
        loading.load("#containerLoadingSearch");
        $.ajax({
            url: (url == undefined)?prestamo.url + "&a=listadoSearch":url,
            data: {
                idcliente: $("#inputSearchIdCliente").val()
            },
            type: "get",
            success: function (response) {
                $("#containerListadoSearch").html(response);
                loading.unload("#containerLoadingSearch");
                prestamo.eventsListado();
            }
        });
    },
    launchEdit: function (id) {
        if(id != undefined && id != "") {
            window.location = prestamo.url + "&a=edit&id=" + id;
        } else {
            mensajes.alert("Error", "Debe seleccionar un registro valido.", 3);
        }
    },
    launchPay: function (id) {
        if(id != undefined && id != "") {
            window.location = Urls.pago + "&a=add&id_prestamo=" + id;
        } else {
            mensajes.alert("Error", "Debe seleccionar un registro valido.", 3);
        }
    },
    cancel: function () {
        window.history.back(-1);
    },
    eventsListado: function () {
        $("#containerListadoSearch .selected-option").each(function() {
            $(this).on("click", function () {
                eval("("+$("#containerListadoSearch").attr("data-callback")+"("+$(this).attr("data-object")+"))");
            });
        });
        $("#paginatioPrestamos").find("a").each(function () {
            $(this).on("click", function () {
                prestamo.listado($(this).attr("href"));
                return false;
            });
        });
    }
};