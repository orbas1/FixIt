<script>
    $(document).ready(function() {
        @if (Session::has('message'))
            toastr.options = {
                "closeButton": false,
                "progressBar": true,
            }
            toastr.success("{{ session('message') }}");
        @endif

        @if (Session::has('error'))
            toastr.options = {
                "closeButton": false,
                "progressBar": true,
            }
            toastr.error("{{ session('error') }}");
        @endif

        @if (Session::has('info'))
            toastr.options = {
                "closeButton": false,
                "progressBar": true,
            }
            toastr.info("{{ session('info') }}");
        @endif

        @if (Session::has('warning'))
            toastr.options = {
                "closeButton": false,
                "progressBar": true,
            }
            toastr.warning("{{ session('warning') }}");
        @endif
    });
</script>
