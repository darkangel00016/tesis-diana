/**
 * Created by lerny on 25/11/15.
 */
supervisor = {
    url: "index.php?m=ManagerMail&c=Supervisor",
    files: [],
    init: function () {
        $('#formSupervisor').validate({
            rules: {
                inputNombres: {
                    required: true
                },
                inputApellidos: {
                    required: true
                },
                inputCedula: {
                    required: true
                },
                inputCorreo: {
                    email: true,
                    required: true
                }
            },
            submitHandler: function (form) {
                supervisor.sentSupervisor();
            },
            messages: {
                inputNombres: {
                    required: "El nombre es obligatorio."
                },
                inputApellidos: {
                    required: "El apellido es obligatorio."
                },
                inputCedula: {
                    required: "La cedula es obligatoria."
                },
                inputCorreo: {
                    required: "El correo es obligatorio.",
                    email: "Debe ingrese un email valido."
                }
            }
        });
        $("#buttonSupervisor").on("click", supervisor.submit);
        $("#buttonSupervisorCancel").on("click", supervisor.cancel);
        $("#supervisorSearchToogle").on("click", function () {
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
        supervisor.eventsListado();
    },
    submit: function () {
        $('#formSupervisor').submit();
    },
    sentSupervisor: function () {
        loading.load("#containerLoading");
        $.ajax({
            url: supervisor.url + "&a=save",
            data: {
                id: $("#inputId").val(),
                correo: $("#inputCorreo").val(),
                nombres: $("#inputNombres").val(),
                apellidos: $("#inputApellidos").val(),
                cedula: $("#inputCedula").val(),
                estado: $("#inputEstado").val()
            },
            type: "post",
            success: function (response) {
                var r = eval("("+response+")");
                loading.unload("#containerLoading");
                if(!r.error) {
                    window.location = supervisor.url;
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
                    url: supervisor.url + "&a=delete",
                    data: {
                        id: id
                    },
                    type: "post",
                    success: function (response) {
                        var r = eval("("+response+")");
                        m.modal('hide');
                        loading.unload("#containerLoading");
                        if(!r.error) {
                            supervisor.listado();
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
            url: (url == undefined)?supervisor.url + "&a=listado":url,
            data: {
                correo: $("#inputCorreoSearch").val(),
                cedula: $("#inputCedulaSearch").val(),
                nombres: $("#inputNombresSearch").val(),
                apellidos: $("#inputApellidosSearch").val(),
                estado: estado
            },
            type: "get",
            success: function (response) {
                $("#containerListadoSupervisor").html(response);
                loading.unload("#containerLoading");
                supervisor.eventsListado();
            }
        });
    },
    launchEdit: function (id) {
        if(id != undefined && id != "") {
            window.location = supervisor.url + "&a=edit&id=" + id;
        } else {
            mensajes.alert("Error", "Debe seleccionar un registro valido.", 3);
        }
    },
    cancel: function () {
        window.history.back(-1);
    },
    eventsListado: function () {
        $("#paginationSupervisor").find("a").each(function () {
            $(this).on("click", function () {
                supervisor.listado($(this).attr("href"));
                return false;
            });
        });
    },
    prepareUpload: function (event)
    {
        supervisor.files = event.target.files;
        $("#buttonSupervisorCancel").prop("disabled", false);
        $("#buttonSupervisorImport").prop("disabled", false);
        $("#containerResult").html("");
    },
    cancelImport: function (cancel) {
        if(cancel == undefined) {
            cancel = true;
        }
        $("#buttonSupervisorCancel").prop("disabled", true);
        $("#buttonSupervisorImport").prop("disabled", true);
        $("#inputFile").filestyle('clear');
        $("#inputFile").filestyle('disabled', false);
        if(cancel) {
            $("#containerResult").html("");
        }
    },
    import: function () {
        if(supervisor.files.length > 0) {
            $("#inputFile").filestyle('disabled', true);
            var data = new FormData();
            $.each(supervisor.files, function(key, value)
            {
                data.append(key, value);
            });
            loading.load();
            $.ajax({
                url: supervisor.url + "&a=import",
                type: 'POST',
                data: data,
                cache: false,
                dataType: 'json',
                processData: false, // Don't process the files
                contentType: false, // Set content type to false as jQuery will tell the server its a query string request
                success: function(data, textStatus, jqXHR)
                {
                    if(!data.error)
                    {
                        mensajes.alert("Exito", data.msg, 2);
                        supervisor.cancelImport(false);
                        var containerResult = $("#containerResult");
                        containerResult.append('<p class="bg-info" style="padding: 15px;">Lista de supervisores importados.</p>');
                        containerResult.append('<table class="table table-bordered table-hover"><thead></thead><tbody></tbody></table>');
                        containerResult.append('<button type="button" class="btn btn-success" onclick="$(this).parent().html(\'\')">Limpiar listado</button>');
                        var thead = '<tr>' +
                        '    <th>Nombres</th>' +
                        '    <th>Apellidos</th>' +
                        '    <th>Correo</th>' +
                        '    <th>Cedula</th>' +
                        '    <th>Mensaje</th>' +
                        '</tr>';
                        containerResult.find("thead").append(thead);
                        for(i = 1; i < data.data.length; i++) {
                            var tbody = '<tr>' +
                            '    <td width="20%">' + data.data[i].value[2] + '</td>' +
                            '    <td width="20%">' + data.data[i].value[1] + '</td>' +
                            '    <td width="10%">' + data.data[i].value[10] + '</td>' +
                            '    <td width="10%">' + data.data[i].value[0] + '</td>' +
                            '    <td width="10%">' + data.data[i].msg + '</td>' +
                            '</tr>';
                            containerResult.find("tbody").append(tbody);
                        }
                    }
                    else
                    {
                        mensajes.alert("Error", data.msg, 3);
                    }
                    loading.unload();
                },
                error: function(jqXHR, textStatus, errorThrown)
                {
                    mensajes.alert("Error", textStatus, 3);
                    loading.unload();
                }
            });
        } else {
            mensajes.alert("Error", "Debe seleccionar un archivo.", 3);
        }
    }
};