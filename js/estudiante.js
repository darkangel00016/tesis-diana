/**
 * Created by lerny on 25/11/15.
 */
estudiante = {
    url: "index.php?m=ManagerMail&c=Estudiante",
    files: [],
    init: function () {
        $('#formEstudiante').validate({
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
                estudiante.sentEstudiante();
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
        $("#buttonEstudiante").on("click", estudiante.submit);
        $("#buttonEstudianteCancel").on("click", estudiante.cancel);
        $("#estudianteSearchToogle").on("click", function () {
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
        estudiante.eventsListado();
    },
    submit: function () {
        $('#formEstudiante').submit();
    },
    sentEstudiante: function () {
        loading.load("#containerLoading");
        $.ajax({
            url: estudiante.url + "&a=save",
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
                    window.location = estudiante.url;
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
                    url: estudiante.url + "&a=delete",
                    data: {
                        id: id
                    },
                    type: "post",
                    success: function (response) {
                        var r = eval("("+response+")");
                        m.modal('hide');
                        loading.unload("#containerLoading");
                        if(!r.error) {
                            estudiante.listado();
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
            url: (url == undefined)?estudiante.url + "&a=listado":url,
            data: {
                correo: $("#inputCorreoSearch").val(),
                cedula: $("#inputCedulaSearch").val(),
                nombres: $("#inputNombresSearch").val(),
                apellidos: $("#inputApellidosSearch").val(),
                estado: estado
            },
            type: "get",
            success: function (response) {
                $("#containerListadoEstudiante").html(response);
                loading.unload("#containerLoading");
                estudiante.eventsListado();
            }
        });
    },
    launchEdit: function (id) {
        if(id != undefined && id != "") {
            window.location = estudiante.url + "&a=edit&id=" + id;
        } else {
            mensajes.alert("Error", "Debe seleccionar un registro valido.", 3);
        }
    },
    cancel: function () {
        window.history.back(-1);
    },
    eventsListado: function () {
        $("#paginationEstudiante").find("a").each(function () {
            $(this).on("click", function () {
                estudiante.listado($(this).attr("href"));
                return false;
            });
        });
    },
    prepareUpload: function (event)
    {
        estudiante.files = event.target.files;
        $("#buttonEstudianteCancel").prop("disabled", false);
        $("#buttonEstudianteImport").prop("disabled", false);
        $("#containerResult").html("");
    },
    cancelImport: function (cancel) {
        if(cancel == undefined) {
            cancel = true;
        }
        $("#buttonEstudianteCancel").prop("disabled", true);
        $("#buttonEstudianteImport").prop("disabled", true);
        $("#inputFile").filestyle('clear');
        $("#inputFile").filestyle('disabled', false);
        if(cancel) {
            $("#containerResult").html("");
        }
    },
    import: function () {
        if(estudiante.files.length > 0) {
            $("#inputFile").filestyle('disabled', true);
            var data = new FormData();
            $.each(estudiante.files, function(key, value)
            {
                data.append(key, value);
            });
            loading.load();
            $.ajax({
                url: estudiante.url + "&a=import",
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
                        estudiante.cancelImport(false);
                        var containerResult = $("#containerResult");
                        containerResult.append('<p class="bg-info" style="padding: 15px;">Lista de estudiantes importados.</p>');
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