<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
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
            src="https://i.pravatar.cc/150"
            class="profile-img"
            id="profile-image"
            alt="Foto profil"
        >

        <!-- DATA -->
        <h2 class="name" id="profile-display-name">Memuat...</h2>

        <p class="username" id="profile-username">-</p>

        <div class="info">
            <p id="profile-email">-</p>
        </div>

        <div id="profile-feedback" style="display:none; margin-bottom: 16px; padding: 12px 14px; border-radius: 12px;"></div>

        <!-- ACTION -->
        <div class="actions">
            <button class="edit-btn" onclick="openModal()">Edit Profile</button>
            <button type="button" class="logout-btn" id="logout-button">Logout</button>
        </div>

    </div>

</div>

<!-- MODAL -->
<div class="modal" id="editModal">
    <div class="modal-content">
        <h3>Edit Profile</h3>

        <form method="POST" id="profile-form" enctype="multipart/form-data">
            <input type="text" name="display_name" id="profile-form-display-name" placeholder="Display Name">
            <input type="email" name="email" id="profile-form-email" placeholder="Email">
            <input type="file" name="profile_picture" id="profile-form-picture" accept="image/jpeg,image/png,image/jpg">

            <div class="modal-actions">
                <button type="submit" class="save-btn" id="save-profile-btn">Save</button>
                <button type="button" class="cancel-btn" onclick="closeModal()">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- SCRIPT -->
<script src="{{ asset('js/stateful-api.js') }}" defer></script>
<script src="{{ asset('js/profile-page.js') }}" defer></script>
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
