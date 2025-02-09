<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Bootstrap demo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
</head>
<body class="bg-light text-dark">
  <div class="container pb-4 pt-2">
    <!-- Header -->
    <header class="row align-items-center pb-3">

      <div class="col-md-4 text-center">
        <h1 class="h3">{{ config('app.name', 'Laravel') }}</h1>
      </div>
      <div class="col-md-8 text-center text-md-end">
        @if (Route::has('login'))
          @auth
            <a href="{{ url('/dashboard') }}" class="btn btn-primary">Dashboard</a>
          @else
            <a href="{{ route('login') }}" class="btn btn-outline-primary me-2">Log in</a>
            @if (Route::has('register'))
              <a href="{{ route('register') }}" class="btn btn-success">Register</a>
            @endif
          @endauth
        @endif
      </div>
    </header>
  </div>

  <!-- Bootstrap JS (CDN orqali, kerak bo'lsa) -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
