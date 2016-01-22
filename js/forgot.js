/**
 * Created by lerny on 24/11/15.
 */
forgot = {
    init: function () {
      $('#formForgot').validate({
          rules: {
              inputEmail: {
                  email: true,
                  required: true
              },
              inputCode: {
                  minlength: 6,
                  required: true
              }
          },
          submitHandler: function (form) {
              forgot.sentForgot();
          },
          messages: {
              inputEmail: {
                  required: "El usuario es obligatorio.",
                  email: "Debe ingrese un email valido."
              },
              inputCode: {
                  required: "La código es obligatorio.",
                  minlength: jQuery.validator.format("Al menos {0} son requeridos!")
              }
          }
      });
      $("#buttonForgot").on("click", forgot.access);
    },
    initCode: function () {
      $('#formForgot').validate({
          rules: {
              inputEmail: {
                  email: true,
                  required: true
              },
              inputValidationCode: {
                  required: true
              },
              inputPassword: {
                  minlength: 6,
                  required: true
              },
              inputCode: {
                  minlength: 6,
                  maxlength: 6,
                  required: true
              }
          },
          submitHandler: function (form) {
              forgot.sentCode();
          },
          messages: {
              inputEmail: {
                  required: "El usuario es obligatorio.",
                  email: "Debe ingrese un email valido."
              },
              inputValidationCode: {
                  required: "El código de validación es obligatorio."
              },
              inputPassword: {
                  required: "La clave es obligatorio.",
                  minlength: jQuery.validator.format("Al menos {0} son requeridos!")
              },
              inputCode: {
                  required: "La codigo es obligatorio.",
                  minlength: jQuery.validator.format("Al menos {0} son requeridos!"),
                  maxlength: jQuery.validator.format("Lo maximo son {0} requeridos!")
              }
          }
      });
      $("#buttonForgot").on("click", forgot.access);
    },
    access: function () {
        $('#formForgot').submit();
    },
    sentForgot: function () {
        loading.load("#containerLoading");
        $.ajax({
            url: "index.php?m=Login&c=Main&a=generate",
            data: {
                email: $("#inputEmail").val(),
                code: $("#inputCode").val()
            },
            type: "post",
            success: function (response) {
                var r = eval("("+response+")");
                loading.unload("#containerLoading");
                if(!r.error) {
                    window.location.href = "index.php?m=Login&c=Main&a=code";
                } else {
                    mensajes.alert("Error", r.msg, 3);
                }

            }
        });
    },
    sentCode: function () {
        loading.load("#containerLoading");
        $.ajax({
            url: "index.php?m=Login&c=Main&a=check_code",
            data: {
                email: $("#inputEmail").val(),
                code: $("#inputValidationCode").val(),
                check_code: $("#inputCode").val(),
                password: $("#inputPassword").val()
            },
            type: "post",
            success: function (response) {
                var r = eval("("+response+")");
                loading.unload("#containerLoading");
                if(!r.error) {
                    window.location.href = "index.php";
                } else {
                    mensajes.alert("Error", r.msg, 3);
                }
            }
        });
    }
};