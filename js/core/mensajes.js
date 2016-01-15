/**
 * Created by lerny on 25/11/15.
 */
mensajes = {
    confirmation: function (title, mensaje, callback) {
        $('#modalConfirmation .modal-title').html(title);
        $('#modalConfirmation .modal-body').html("<p>" + mensaje + "</p>");
        var modal = $('#modalConfirmation').modal({
            show: true
        });
        $('#modalConfirmation').on('shown.bs.modal', function (e) {
            $(this).find("button[class*='btn-primary']").on('click', function(e) {
                callback($(this), e);
            });
        });
        $("#modalConfirmation").on('hidden.bs.modal', function () {
            $('#modalConfirmation').off('shown.bs.modal');
            $('#modalConfirmation').off('hidden.bs.modal');
            $(this).data('bs.modal', null);
            $(this).find("button[class*='btn-primary']").off('click');
        });
        return modal;
    },
    alert: function (title, mensaje, type) {
        var clase = "modal-info";
        if (type == 1) {
            clase = 'modal-warning';
        } else if (type == 2) {
            clase = 'modal-success';
        } else if (type == 3) {
            clase = 'modal-danger';
        }
        $('#modalAlert .modal').addClass(clase);
        $('#modalAlert .modal-title').html(title);
        $('#modalAlert .modal-body').html("<p>" + mensaje + "</p>");
        var modal = $('#modalAlert').modal({
            show: true
        });
        $("#modalAlert").on('hidden.bs.modal', function () {
            $(this).data('bs.modal', null);
        });
        return modal;
    },
    search: function (title, options, callback) {
        $('#modalSearch .modal-title').html(title);
        $('#containerLoadingSearch').html(options.loading);
        $('#containerListadoSearch').attr("data-callback", options.callback);
        $("#inputCampos option").remove();
        if(options.fields.length > 0) {
            for (var i = 0; i < options.fields.length; i++) {
                $("#inputCampos").append("<option value='" + options.fields[i].value + "'>" + options.fields[i].text + "</option>");
            }
        } else {
            $("#containerFormSearch").hide();
        }
        $("#containerFormSearch .field").remove();
        if(options.hidden != undefined && options.hidden.length > 0) {
            for (var i = 0; i < options.hidden.length; i++) {
                $("#containerFormSearch").append("<input type='hidden' id='"+options.hidden[i].id+"' value='"+options.hidden[i].value+"' class='field' />");
            }
        }
        var modal = $('#modalSearch').modal({
            show: true
        });
        $('#modalSearch').on('shown.bs.modal', function (e) {
            $("#inputCampos").select2();
            callback();
        });
        $("#modalSearch").on('hidden.bs.modal', function () {
            $('#modalSearch').off('shown.bs.modal');
            $('#modalSearch').off('hidden.bs.modal');
            $(this).data('bs.modal', null);
        });
        return modal;
    }
};