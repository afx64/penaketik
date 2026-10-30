{{-- Runs before the stylesheet so a dark page never flashes light. --}}
<script>
    (function () {
        var mode = @json(\App\Support\Settings::themeMode());
        var media = window.matchMedia('(prefers-color-scheme: dark)');

        function apply() {
            document.documentElement.setAttribute(
                'data-theme',
                mode === 'system' ? (media.matches ? 'dark' : 'light') : mode
            );
        }

        apply();

        if (mode === 'system' && media.addEventListener) {
            media.addEventListener('change', apply);
        }
    })();
</script>
