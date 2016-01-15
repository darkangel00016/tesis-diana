/**
 * Created by lerny on 25/11/15.
 */
user = {
    init: function () {
        $('#formUser').validate({
            rules: {
                inputUsuario: {
                    email: true,
                    required: true
                },
                inputName: {
                    required: true
                },
                inputPassword: {
                    minlength: 6,
                    required: true
                },
                inputPasswordConfirmation: {
                    minlength: 6,
                    required: true,
                    equalTo: "#inputPassword"
                }
            },
            submitHandler: function (form) {
                user.sentUser();
            },
            messages: {
                inputUsuario: {
                    required: "El usuario es obligatorio.",
                    email: "Debe ingrese un email valido."
                },
                inputName: {
                    required: "El nombre es obligatorio."
                },
                inputPassword: {
                    required: "La clave es obligatorio.",
                    minlength: jQuery.validator.format("Al menos {0} son requeridos!")
                },
                inputPasswordConfirmation: {
                    required: "La confirmacion clave es obligatorio.",
                    minlength: jQuery.validator.format("Al menos {0} son requeridos!"),
                    equalTo: "Las contrasenas deben coincidir."
                }
            }
        });
        $("#buttonUser").on("click", user.submit);
        $("#buttonUserCancel").on("click", user.cancel);
        $("#userSearchToogle").on("click", function () {
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
        user.eventsListado();
    },
    submit: function () {
        var id = $("#inputId").val();
        if(id != "") {
            var inputPassword = $("#inputPassword").val();
            var inputPasswordConfirmation = $("#inputPasswordConfirmation").val();
            if (inputPassword != "" || inputPasswordConfirmation != "") {
                $("#inputPassword").rules("add", {
                    minlength: 6,
                    required: true
                });
                $("#inputPasswordConfirmation").rules("add", {
                    minlength: 6,
                    required: true,
                    equalTo: "#inputPassword"
                });
            } else {
                $("#inputPassword").rules("remove");
                $("#inputPasswordConfirmation").rules("remove");
            }
        }
        $('#formUser').submit();
    },
    sentUser: function () {
        loading.load("#containerLoading");
        $.ajax({
            url: "index.php?m=User&a=save",
            data: {
                id: $("#inputId").val(),
                email: $("#inputUsuario").val(),
                nombre: $("#inputNombre").val(),
                password: $("#inputPassword").val(),
                tipo: $("#inputTipo").val(),
                estado: $("#inputEstado").val()
            },
            type: "post",
            success: function (response) {
                var r = eval("("+response+")");
                loading.unload("#containerLoading");
                if(!r.error) {
                    window.location = "index.php?m=User";
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
                    url: "index.php?m=User&a=delete",
                    data: {
                        id: id
                    },
                    type: "post",
                    success: function (response) {
                        var r = eval("("+response+")");
                        m.modal('hide');
                        loading.unload("#containerLoading");
                        if(!r.error) {
                            user.listado();
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
        $('#inputTiposearch :selected').each(function(i, selected){
            tipo[i] = $(selected).val();
        });
        var estado = [];
        $('#inputEstadosearch :selected').each(function(i, selected){
            estado[i] = $(selected).val();
        });
        loading.load("#containerLoading");
        $.ajax({
            url: (url == undefined)?"index.php?m=User&a=listado":url,
            data: {
                usuario: $("#inputUsuarioSearch").val(),
                nombre: $("#inputNombreSearch").val(),
                tipo: tipo,
                estado: estado
            },
            type: "get",
            success: function (response) {
                $("#containerListadoUser").html(response);
                loading.unload("#containerLoading");
                user.eventsListado();
            }
        });
    },
    launchEdit: function (id) {
        if(id != undefined && id != "") {
            window.location = "index.php?m=User&a=edit&id=" + id;
        } else {
            mensajes.alert("Error", "Debe seleccionar un registro valido.", 3);
        }
    },
    cancel: function () {
        window.history.back(-1);
    },
    eventsListado: function () {
        $("#paginatioUsers").find("a").each(function () {
            $(this).on("click", function () {
                user.listado($(this).attr("href"));
                return false;
            });
        });
    }
};