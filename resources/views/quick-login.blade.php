<!DOCTYPE html>
<html>
<head>
    <title>Pilih User</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">

<div class="container mt-5">
    <h3 class="text-center mb-4">Pilih User untuk Login</h3>

    <div class="card shadow-sm p-4">
        @foreach ($users as $u)
        <form action="{{ route('quick.login') }}" method="POST" class="mb-2">
            @csrf
            <input type="hidden" name="user_id" value="{{ $u->id }}">
            <button class="btn btn-outline-primary w-100 py-2">
                <strong>{{ $u->name }}</strong>  
                <br>
                <span class="text-muted small">{{ $u->email }}</span>
            </button>
        </form>
        @endforeach
    </div>
</div>

</body>
</html>
