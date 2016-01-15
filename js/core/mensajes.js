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
    }
};