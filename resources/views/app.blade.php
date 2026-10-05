<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csp-nonce" content="{{ \Illuminate\Support\Facades\Vite::cspNonce() }}">
  @viteReactRefresh
  @vite(['resources/css/app.css', 'resources/js/app.tsx'])
  <x-inertia::head />
</head>
<body>
  <x-inertia::app />
</body>
</html>