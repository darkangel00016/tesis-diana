/**
 * Created by lerny on 25/11/15.
 */
config = {
    url: "index.php?m=Configuracion&c=Main",
    dialogSearch: "",
    dialogSearchPrestamo: "",
    dialogSearchCobrador: "",
    init: function () {
        $("#buttonConfig").on("click", config.submit);
        $("#buttonConfigCancel").on("click", config.cancel);
        $("#inputMonto").on("keypress", function(event) {
            var e = new events(event, $(this).val());
            if(!e.validateReal()) {
                event.preventDefault();
                event.stopPropagation();
            }
        });
    },
    submit: function () {
        config.sentConfig();
    },
    sentConfig: function () {
        loading.load("#containerLoading");
        $.ajax({
            url: config.url + "&a=save",
            data: {
                host: $("#inputHost").val(),
                usuario: $("#inputUser").val(),
                clave: $("#inputClave").val(),
                from: $("#inputFrom").val(),
                subject: $("#inputFromSubject").val(),
                formato_fecha_corta: $("#inputFormatoFechaCorta").val(),
                formato_fecha_larga: $("#inputFormatoFechaLarga").val()
            },
            type: "post",
            success: function (response) {
                var r = eval("("+response+")");
                loading.unload("#containerLoading");
                if(r.error) {
                    mensajes.alert("Error", r.msg, 3);
                }

            }
        });
    },
    cancel: function () {
        window.history.back(-1);
    }
};