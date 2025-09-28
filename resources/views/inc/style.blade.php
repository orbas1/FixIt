@use('app\Helpers\Helpers')
@php
    $settings = Helpers::getSettings();
    $primaryColor = @$settings['appearance']['primary_color'] ?? '#5465ff';
    $sidebarColor = @$settings['appearance']['sidebar_color'] ?? '#000000';
    $fontFamily = @$settings['appearance']['font_family'] ?? 'Poppins';
@endphp

<style>
    :root {
        --primary-color: {{ $primaryColor }};
        --font-family: {{ $fontFamily }};
        --bg-sidebar: {{ $sidebarColor }};
    }
</style>
