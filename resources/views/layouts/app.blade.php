<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Load assets from manifest -->
        @php
        // Check both possible manifest locations
        $manifestPath = public_path('build/manifest.json');
        $viteManifestPath = public_path('build/.vite/manifest.json');
        $manifest = null;
        
        if (file_exists($manifestPath)) {
            $manifest = json_decode(file_get_contents($manifestPath), true);
        } elseif (file_exists($viteManifestPath)) {
            $manifest = json_decode(file_get_contents($viteManifestPath), true);
        }
        
        $cssFile = null;
        $jsFile = null;
        
        if ($manifest) {
            // Check both possible CSS entry formats
            if (isset($manifest['resources/css/app.css']['file'])) {
                $cssFile = '/build/' . $manifest['resources/css/app.css']['file'];
            } elseif (isset($manifest['resources/css/app.css'])) {
                $cssFile = '/build/' . $manifest['resources/css/app.css'];
            }
            
            // Check both possible JS entry formats
            if (isset($manifest['resources/js/app.js']['file'])) {
                $jsFile = '/build/' . $manifest['resources/js/app.js']['file'];
            } elseif (isset($manifest['resources/js/app.js'])) {
                $jsFile = '/build/' . $manifest['resources/js/app.js'];
            }
        }
        @endphp
        
        @if($cssFile)
        <link rel="stylesheet" href="{{ $cssFile }}">
        @endif
        
        @if($jsFile)
        <script src="{{ $jsFile }}" defer></script>
        @endif
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @isset($header)
                <header class="bg-white shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>
        </div>
    </body>
</html>
