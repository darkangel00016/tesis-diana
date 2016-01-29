/**
 * Created by lerny on 25/11/15.
 */
boletin = {
    url: "index.php?m=ManagerMail&c=Boletin",
    files: [],
    init: function () {
        $('#formBoletin').validate({
            rules: {
                inputSubject: {
                    required: true
                },
                inputFrom: {
                    required: true
                },
                inputContenido: {
                    required: true
                }
            },
            submitHandler: function (form) {
                boletin.sentBoletin();
            },
            messages: {
                inputSubject: {
                    required: "El asunto obligatorio."
                },
                inputFrom: {
                    required: "El origen es obligatorio."
                },
                inputContenido: {
                    required: "El contenido es obligatorio."
                }
            }
        });
        $("#buttonBoletin").on("click", boletin.submit);
        $("#buttonBoletinCancel").on("click", boletin.cancel);
        $("#boletinSearchToogle").on("click", function () {
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
        jQuery("#inputTipo").on("change", function(e) {
            boletin.changeFechaEnvio(this);
        });
        boletin.eventsListado();
    },
    submit: function () {
        $('#formBoletin').submit();
    },
    sentBoletin: function () {
        loading.load("#containerLoading");
        $.ajax({
            url: boletin.url + "&a=save",
            data: {
                id: $("#inputId").val(),
                tipo: $("#inputTipo").val(),
                fecha_envio: $("#inputFechaEnvio").val(),
                subject: $("#inputSubject").val(),
                from: $("#inputFrom").val(),
                contenido: CKEDITOR.instances.inputContenido.getData(),
                estado: $("#inputEstado").val()
            },
            type: "post",
            success: function (response) {
                var r = eval("("+response+")");
                loading.unload("#containerLoading");
                if(!r.error) {
                    window.location = boletin.url;
                } else {
                    mensajes.alert("Error", r.msg, 3);
                }

            }
        });
    },
    eliminar: function (id) {
        if(id != undefined && id != "") {
            var m = mensajes.confirmation("Confirmar", "Desea realmente eliminar el registro?", function() {
                loading.load("#containerLoading");
                $.ajax({
                    url: boletin.url + "&a=delete",
                    data: {
                        id: id
                    },
                    type: "post",
                    success: function (response) {
                        var r = eval("("+response+")");
                        m.modal('hide');
                        loading.unload("#containerLoading");
                        if(!r.error) {
                            boletin.listado();
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
        var tipo = [];
        $('#inputTiposearch :selected').each(function(i, selected){
            tipo[i] = $(selected).val();
        });
        var from = [];
        $('#inputFromSearch :selected').each(function(i, selected){
            from[i] = $(selected).val();
        });
        loading.load("#containerLoading");
        $.ajax({
            url: (url == undefined)?boletin.url + "&a=listado":url,
            data: {
                estado: estado,
                tipo: tipo,
                from: from
            },
            type: "get",
            success: function (response) {
                $("#containerListadoBoletin").html(response);
                loading.unload("#containerLoading");
                boletin.eventsListado();
            }
        });
    },
    launchEdit: function (id) {
        if(id != undefined && id != "") {
            window.location = boletin.url + "&a=edit&id=" + id;
        } else {
            mensajes.alert("Error", "Debe seleccionar un registro valido.", 3);
        }
    },
    cancel: function () {
        window.history.back(-1);
    },
    eventsListado: function () {
        $("#paginationBoletin").find("a").each(function () {
            $(this).on("click", function () {
                boletin.listado($(this).attr("href"));
                return false;
            });
        });
    },
    changeFechaEnvio: function (obj) {
        if(jQuery(obj).val() == "p") {
            jQuery("#containerFechaEnvio").removeClass("hide");
        } else {
            jQuery("#containerFechaEnvio").addClass("hide");
            jQuery(obj).val("");
        }
    }
};