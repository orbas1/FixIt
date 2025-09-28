<script>
    (function($) {
        "use strict";

        $(document).ready(function() {
            $('#withdrawRequestForm').submit(function(event) {
                event.preventDefault();
                var formData = $(this).serialize();

                $.ajax({
                    type: 'POST',
                    url: $(this).attr('action'),
                    data: formData,
                    success: function(response) {
                        if (response.errors) {
                            handleValidationErrors(response.errors);
                        } else {
                            $('#withdrawModal').modal('hide');
                            location.reload();
                        }
                    },
                    error: function(xhr) {
                        var errors = xhr.responseJSON.errors;
                        handleValidationErrors(errors);
                    }
                });
            });

            function handleValidationErrors(errors) {
                $('#form-errors').empty().removeClass('d-none');
                $.each(errors, function(key, value) {
                    $('#form-errors').append('<p>' + value + '</p>');
                });
            }
        });

    })(jQuery);
</script>

