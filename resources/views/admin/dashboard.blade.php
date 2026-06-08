<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin Dashboard</title>

    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
    <style>
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
        }
        .admin-session {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .admin-name {
            font-size: 14px;
            color: #334155;
            font-weight: 600;
        }
        .logout-btn {
            border: none;
            background: #ef4444;
            color: #fff;
            border-radius: 8px;
            padding: 8px 12px;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
        }
        .logout-btn:hover {
            background: #dc2626;
        }
        .log-action { font-weight: 600; text-transform: uppercase; font-size: 0.85em; padding: 4px 8px; border-radius: 4px; background: #e2e8f0; color: #475569; display: inline-block;}
        .log-action.login { background: #dcfce7; color: #166534; }
        .log-action.logout { background: #fee2e2; color: #991b1b; }
        .log-action.register { background: #dbeafe; color: #1e40af; }

        /* GRID CCTV & TERMINAL SECTION (CLEAN THEME) */
        .monitoring-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 20px;
            margin-bottom: 24px;
        }

        .terminal-card {
            background: #fff;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
            border: 1px solid #e2e8f0;
            overflow: hidden;
            height: 400px;
            display: flex;
            flex-direction: column;
        }
        .terminal-header {
            color: #475569;
            font-size: 14px;
            font-weight: 700;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 12px;
            margin-bottom: 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .terminal-header .live-badge {
            font-size: 11px;
            background: #dcfce7;
            color: #166534;
            padding: 2px 8px;
            border-radius: 12px;
            animation: pulse 2s infinite;
        }
        .terminal-body {
            flex: 1;
            overflow-y: auto;
            font-family: inherit;
            font-size: 13px;
            padding-right: 8px;
        }
        /* Custom scrollbar for terminal */
        .terminal-body::-webkit-scrollbar { width: 6px; }
        .terminal-body::-webkit-scrollbar-track { background: #f1f5f9; border-radius: 4px; }
        .terminal-body::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
        
        .term-line {
            display: flex;
            margin-bottom: 10px;
            line-height: 1.6;
            gap: 12px;
            padding-bottom: 10px;
            border-bottom: 1px dashed #f1f5f9;
        }
        .term-line:last-child {
            border-bottom: none;
        }
        .term-time { color: #94a3b8; min-width: 140px; font-variant-numeric: tabular-nums; }
        .term-user { color: #0f172a; font-weight: 700; min-width: 100px; }
        .term-action { min-width: 160px; }
        .term-desc { color: #475569; flex: 1; }

        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.5; }
            100% { opacity: 1; }
        }
    </style>
</head>
<body>
@php
    $admin = auth()->user();
    $adminName = $admin?->username ?? $admin?->name ?? 'Admin';
@endphp

<div class="layout">
    <!-- SIDEBAR -->
    <aside class="sidebar">
        <h2 class="logo">Neuroom Admin</h2>

        <nav class="nav">
            <a href="{{ route('admin.dashboard') }}"
               class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                Dashboard
            </a>

            <a href="{{ route('admin.users') }}"
               class="{{ request()->routeIs('admin.users') ? 'active' : '' }}">
                Users
            </a>
        </nav>
    </aside>

    <!-- MAIN -->
    <main class="main">
        <!-- HEADER -->
        <div class="header">
            <h1>Dashboard</h1>
            <div class="admin-session">
                <span class="admin-name">
                    Login sebagai: <strong>{{ $adminName }}</strong>
                </span>
                <button type="button" class="logout-btn" id="logout-button">Logout</button>
            </div>
        </div>

        <!-- STAT CARDS -->
        <section class="stats">
            <div class="card">
                <h4>Total Users</h4>
                <h2 id="total-users">-</h2>
            </div>

            <div class="card">
                <h4>Total Sessions</h4>
                <h2 id="total-sessions">-</h2>
            </div>

            <div class="card">
                <h4>Active Today</h4>
                <h2 id="active-today">-</h2>
            </div>
        </section>

        <!-- CCTV & TERMINAL GRID -->
        <section class="monitoring-grid">
            <!-- TERMINAL LOGS -->
            <div class="terminal-card">
                <div class="terminal-header">
                    <span>Aktivitas Sistem Terbaru</span>
                    <span class="live-badge">● LIVE AUTO-SYNC</span>
                </div>
                <div class="terminal-body" id="terminal-body">
                    <div class="term-line">
                        <span class="term-desc" style="color: #64748b;">Menunggu aktivitas log terbaru...</span>
                    </div>
                </div>
            </div>

        </section>

        <!-- TABLE (Optional: if you still want the clean table) -->
        <section class="table-card">
            <h3>Recent Activity</h3>

            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>User</th>
                        <th>Aksi</th>
                        <th>Keterangan</th>
                        <th>Waktu</th>
                    </tr>
                </thead>
                <tbody id="latest-sessions-body">
                    <tr>
                        <td colspan="5">Memuat data...</td>
                    </tr>
                </tbody>
            </table>
        </section>

    </main>
</div>

<!-- SCRIPT -->
<script src="{{ asset('js/stateful-api.js') }}" defer></script>
<script src="{{ asset('js/admin-dashboard.js') }}" defer></script>

</body>
</html>
