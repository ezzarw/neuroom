<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Profil Saya</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            background: #f3f4f6;
            color: #111827;
        }

        .wrap {
            max-width: 720px;
            margin: 32px auto;
            padding: 24px;
        }

        .card {
            background: #fff;
            border: 1px solid #d1d5db;
            padding: 20px;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 12px;
        }

        .item {
            border: 1px solid #e5e7eb;
            padding: 12px;
            background: #f9fafb;
        }

        .item strong {
            display: block;
            margin-bottom: 6px;
        }
    </style>
</head>
<body>
    <main class="wrap">
        <div class="card">
            <h1>Profil Saya</h1>

            <div class="grid">
                <div class="item">
                    <strong>ID</strong>
                    <div>{{ $auth->id }}</div>
                </div>
                <div class="item">
                    <strong>Username</strong>
                    <div>{{ $auth->username }}</div>
                </div>
                <div class="item">
                    <strong>Email</strong>
                    <div>{{ $auth->email }}</div>
                </div>
                <div class="item">
                    <strong>Role</strong>
                    <div>{{ (int) $auth->is_admin === 1 ? 'Admin' : 'User' }}</div>
                </div>
                <div class="item">
                    <strong>Display Name</strong>
                    <div>{{ $user?->display_name ?? '-' }}</div>
                </div>
                <div class="item">
                    <strong>Profile Picture</strong>
                    <div>{{ $user?->profile_picture ?? '-' }}</div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
