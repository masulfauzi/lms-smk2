<!DOCTYPE html>
<html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>@yield('kode')</title>
<link href="https://fonts.googleapis.com/css2?family=Lexend:wght@400;600;700&family=Kalam:wght@700&display=swap" rel="stylesheet">
@vite('resources/css/app.css')</head>
<body><div style="min-height:100vh;display:grid;place-items:center;padding:24px;text-align:center">
<div><div class="tulisan" style="font-size:72px;color:var(--tinta);line-height:1">@yield('kode')</div>
<h1 style="margin:10px 0 6px"><span class="sorot">@yield('judul')</span></h1>
<p style="color:var(--redup)">@yield('pesan')</p>
<a class="btn" href="{{ url('/') }}">Kembali ke beranda</a></div></div></body></html>
