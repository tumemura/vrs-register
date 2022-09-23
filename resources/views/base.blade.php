<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

@yield('meta')

<!-- Styles -->
@yield('css')

<!-- Scripts -->
@yield('script')
<script type="text/javascript" src="//webfonts.xserver.jp/js/xserver.js"></script>

<!-- Style -->
@yield('style')

<style>

.standard-form {
    margin-block-end: 0em;
}

body {
   font-family: "トーキング",arial,sans-serif !important;
}

</style>

<title>@yield('title')</title>
</head>
<body>
@yield('body')
</body>
</html>