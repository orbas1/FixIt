// Password hide show
$(document).ready(function () {
    $('.toggle-password').on('click', function () {
        var input = $(this).closest('.form-group').find('input');
        if (input.attr('type') === 'password') {
            input.attr('type', 'text');
            $(this).removeClass('eye');
            $(this).addClass('eye-slash');
        } else {
            input.attr('type', 'password');
            $(this).removeClass('eye-slash');
            $(this).addClass('eye');
        }
    });
});