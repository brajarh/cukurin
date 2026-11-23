<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>@yield('title', 'Beranda') - Cukur.in</title>
@vite(['resources/sass/app.scss', 'resources/js/app.js'])
@stack('styles')
</head>
<body class="bg-light">
<div id="app">
@include('partials.navbar')
<main class="py-4">
<div class="container">
@include('partials.flash')
@yield('content')
</div>
</main>
<footer class="text-center text-muted small py-3">
&copy; {{ date('Y') }} Cukur.in
</footer>
</div>
@stack('scripts')
</body>
</html>
