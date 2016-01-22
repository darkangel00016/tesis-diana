/**
 * Created by lerny on 24/11/15.
 */
login = {
    init: function () {
      $('#formLogin').validate({
          rules: {
              inputEmail: {
                  email: true,
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
              login.sentLogin();
          },
          messages: {
              inputEmail: {
                  required: "El usuario es obligatorio.",
                  email: "Debe ingrese un email valido."
              },
              inputPassword: {
                  required: "La clave es obligatorio.",
                  minlength: jQuery.validator.format("Al menos {0} son requeridos!")
              },
              inputCode: {
                  required: "La codigo es obligatorio.",
                  minlength: jQuery.validator.format("Al menos {0} son requeridos!"),
                  maxlength: jQuery.validator.format("Al menos {0} son requeridos!")
              }
          }
      });
      $("#buttonLogin").on("click", login.access);
    },
    access: function () {
        $('#formLogin').submit();
    },
    sentLogin: function () {
        loading.load("#containerLoading");
        $.ajax({
            url: "index.php?m=Login&c=Main&a=access",
            data: {
                email: $("#inputEmail").val(),
                password: $("#inputPassword").val(),
                code: $("#inputCode").val()
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