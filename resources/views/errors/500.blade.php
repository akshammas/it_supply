<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Something Went Wrong | {{ config('app.name') }}</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
</head>
<body class="d-flex align-items-center justify-content-center" style="min-height:100vh; background:#f8f9fa;">
    <div class="text-center px-3">
        <i class="bi bi-exclamation-triangle text-warning" style="font-size:4rem;"></i>
        <h1 class="display-4 fw-bold mt-3">Something Went Wrong</h1>
        <p class="text-muted fs-5">We're having a temporary issue. Please try again in a moment.</p>
        <a href="/" class="btn btn-primary btn-lg mt-2">Back to Homepage</a>
    </div>
</body>
</html>
