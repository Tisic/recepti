<!DOCTYPE html>
<html lang="sr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prijava – MojiRecepti</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="bg-login d-flex align-items-center min-vh-100">

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-5 col-lg-4">

            <!-- login kartica -->
            <div class="card shadow-lg border-0 rounded-4">
                <div class="card-header bg-dark text-white text-center rounded-top-4 py-4">
                    <h2 class="mb-0">🍽️ MojiRecepti</h2>
                    <p class="mb-0 text-secondary small">Upravljaj receptima i ishranom</p>
                </div>
                <div class="card-body p-4">
                    <h5 class="card-title mb-4">Prijava</h5>

                    <?php
                    // prikazujemo poruke uspeha i greske na osnovu URL parametara
                    $greska = $_GET['greska'] ?? '';
                    $uspeh  = $_GET['uspeh']  ?? '';
                    if ($uspeh == 1) echo '<div class="alert alert-success">✅ Nalog uspešno kreiran! Možeš se prijaviti.</div>';
                    if ($uspeh == 2) echo '<div class="alert alert-info">👋 Uspešno si se odjavio.</div>';
                    if ($greska == 1) echo '<div class="alert alert-danger">❌ Pogrešan email ili lozinka.</div>';
                    if ($greska == 2) echo '<div class="alert alert-warning">⚠️ Morate biti prijavljeni da biste pristupili ovoj stranici.</div>';
                    ?>

                    <!-- login forma - šalje na php/login.php -->
                    <form action="php/login.php" method="post">
                        <div class="mb-3">
                            <label class="form-label">Email adresa</label>
                            <input type="email" name="email" class="form-control" placeholder="vas@email.rs" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Lozinka</label>
                            <input type="password" name="lozinka" class="form-control" placeholder="Vaša lozinka" required>
                        </div>
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-dark btn-lg">Prijavi se</button>
                            <a href="register.php" class="btn btn-outline-secondary">Nemaš nalog? Registruj se</a>
                        </div>
                    </form>
                </div>
                <div class="card-footer text-center text-muted small py-3">
                    Demo nalog: <strong>admin@recepti.rs</strong> / <strong>password</strong>
                </div>
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
