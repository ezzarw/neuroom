<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>

    <link rel="stylesheet" href="{{ asset('css/profil.css') }}">
</head>
<body>

<div class="container">

    <div class="profile-card">

        <!-- HEADER -->
        <div class="card-header">
            <button class="back-btn" onclick="history.back()">←</button>
            <h3>Profile</h3>
        </div>

        <!-- FOTO -->
        <img 
            src="{{ auth()->user()->profile_picture ? asset('storage/' . auth()->user()->profile_picture) : 'https://i.pravatar.cc/150' }}" 
            class="profile-img"
        >

        <!-- DATA -->
        <h2 class="name">
            {{ auth()->user()->display_name ?? 'No Name' }}
        </h2>

        <p class="username">
            {{ auth()->user()->username }}
        </p>

        <div class="info">
            <p>{{ auth()->user()->email }}</p>
        </div>

        <!-- ACTION -->
        <div class="actions">
            <button class="edit-btn" onclick="openModal()">Edit Profile</button>

            <form action="/auth/logout" method="POST">
                @csrf
                <button type="submit" class="logout-btn">Logout</button>
            </form>
        </div>

    </div>

</div>

<!-- MODAL -->
<div class="modal" id="editModal">
    <div class="modal-content">
        <h3>Edit Profile</h3>

        <form method="POST" action="#">
            @csrf

            <input type="text" name="display_name" value="{{ auth()->user()->display_name }}">
            <input type="text" name="username" value="{{ auth()->user()->username }}">
            <input type="email" name="email" value="{{ auth()->user()->email }}">

            <div class="modal-actions">
                <button type="submit" class="save-btn">Save</button>
                <button type="button" class="cancel-btn" onclick="closeModal()">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
function openModal() {
    document.getElementById("editModal").style.display = "flex";
}

function closeModal() {
    document.getElementById("editModal").style.display = "none";
}
</script>

</body>
</html>