<!DOCTYPE html>
<html lang="sr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registracija – MojiRecepti</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="bg-login d-flex align-items-center min-vh-100">

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">

            <div class="card shadow-lg border-0 rounded-4">
                <div class="card-header bg-dark text-white text-center rounded-top-4 py-4">
                    <h2 class="mb-0">🍽️ MojiRecepti</h2>
                    <p class="mb-0 text-secondary small">Kreiraj nalog besplatno</p>
                </div>
                <div class="card-body p-4">
                    <h5 class="card-title mb-4">Registracija</h5>

                    <?php
                    // greske pri registraciji
                    $greska = $_GET['greska'] ?? '';
                    if ($greska == 1) echo '<div class="alert alert-danger">❌ Sva polja su obavezna.</div>';
                    if ($greska == 2) echo '<div class="alert alert-danger">❌ Lozinke se ne podudaraju.</div>';
                    if ($greska == 3) echo '<div class="alert alert-danger">❌ Taj email već postoji u sistemu.</div>';
                    if ($greska == 4) echo '<div class="alert alert-danger">❌ Greška pri registraciji, pokušaj ponovo.</div>';
                    ?>

                    <!-- registraciona forma -->
                    <form action="php/register.php" method="post">
                        <div class="row">
                            <div class="col-6 mb-3">
                                <label class="form-label">Ime</label>
                                <input type="text" name="ime" class="form-control" placeholder="Marko" required>
                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label">Prezime</label>
                                <input type="text" name="prezime" class="form-control" placeholder="Marković" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email adresa</label>
                            <input type="email" name="email" class="form-control" placeholder="vas@email.rs" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Lozinka</label>
                            <input type="password" name="lozinka" class="form-control" placeholder="Minimalno 6 karaktera" required>
                        </div>
                        <div class="mb-4">
                            <label class="form-label">Potvrda lozinke</label>
                            <input type="password" name="potvrda" class="form-control" placeholder="Ponovi lozinku" required>
                        </div>
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-success btn-lg">Registruj se</button>
                            <a href="index.php" class="btn btn-outline-secondary">← Nazad na prijavu</a>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
