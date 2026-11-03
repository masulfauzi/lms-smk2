<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="sekolah" content="{{ setting('nama_sekolah') }}">
<title inertia>{{ setting('nama_sekolah') }}</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Lexend:wght@400;500;600;700&family=Kalam:wght@400;700&display=swap" rel="stylesheet">
@routes
@vite('resources/js/app.js')
@inertiaHead
</head>
<body>
@inertia
</body>
</html>
