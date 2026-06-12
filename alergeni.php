<?php
require_once 'php/klase/Korisnik.php';
require_once 'php/klase/Entiteti.php';
Korisnik::zahtevajPrijavu();

$alergen  = new Alergen();
$svi      = $alergen->citaj();
$zaIzmenu = null;
if (isset($_GET['izmeni'])) {
    $lista    = $alergen->citaj((int)$_GET['izmeni']);
    $zaIzmenu = $lista[0] ?? null;
}
$uspeh = $_GET['uspeh'] ?? '';
?>
<!DOCTYPE html>
<html lang="sr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alergeni – MojiRecepti</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="bg-light">
<?php include 'php/navbar.php'; ?>
<div class="container" style="margin-top:80px;">

    <?php if ($uspeh == 1) echo '<div class="alert alert-success">✅ Alergen dodat!</div>'; ?>
    <?php if ($uspeh == 2) echo '<div class="alert alert-info">✏️ Alergen izmenjen!</div>'; ?>
    <?php if ($uspeh == 3) echo '<div class="alert alert-warning">🗑️ Alergen obrisan!</div>'; ?>

    <div class="row mb-3">
        <div class="col"><h3>⚠️ Alergeni</h3></div>
        <div class="col-auto">
            <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#modalDodaj">+ Dodaj alergen</button>
        </div>
    </div>

    <div class="row">
        <?php foreach ($svi as $a): ?>
        <div class="col-md-6 col-lg-4 mb-3">
            <div class="card border-danger shadow-sm h-100">
                <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">⚠️ <?= htmlspecialchars($a['Naziv']) ?></h6>
                    <div>
                        <a href="alergeni.php?izmeni=<?= $a['AlergenID'] ?>" class="btn btn-sm btn-light">✏️</a>
                        <a href="php/akcije.php?akcija=obrisi&tip=alergen&id=<?= $a['AlergenID'] ?>"
                           class="btn btn-sm btn-dark"
                           onclick="return confirm('Obrisati alergen: <?= htmlspecialchars($a['Naziv']) ?>?')">🗑️</a>
                    </div>
                </div>
                <div class="card-body">
                    <p class="card-text text-muted small"><?= htmlspecialchars($a['Opis'] ?? 'Bez opisa.') ?></p>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <?php if ($zaIzmenu): ?>
    <div id="editSection" class="card border-warning shadow mt-3">
        <div class="card-header bg-warning"><h5 class="mb-0">✏️ Izmena alergena</h5></div>
        <div class="card-body">
            <form action="php/akcije.php?akcija=azuriraj&tip=alergen&id=<?= $zaIzmenu['AlergenID'] ?>" method="post">
                <div class="mb-3">
                    <label class="form-label">Naziv</label>
                    <input type="text" name="naziv" class="form-control" value="<?= htmlspecialchars($zaIzmenu['Naziv']) ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Opis</label>
                    <textarea name="opis" class="form-control" rows="3"><?= htmlspecialchars($zaIzmenu['Opis']) ?></textarea>
                </div>
                <button type="submit" class="btn btn-warning">Sačuvaj</button>
                <a href="alergeni.php" class="btn btn-secondary">Otkaži</a>
            </form>
        </div>
    </div>
    <?php endif; ?>
</div>

<div class="modal fade" id="modalDodaj" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">+ Dodaj alergen</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form action="php/akcije.php?akcija=dodaj&tip=alergen" method="post">
                    <div class="mb-3">
                        <label class="form-label">Naziv *</label>
                        <input type="text" name="naziv" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Opis</label>
                        <textarea name="opis" class="form-control" rows="3" placeholder="Gde se nalazi ovaj alergen..."></textarea>
                    </div>
                    <div class="modal-footer px-0 pb-0">
                        <button type="submit" class="btn btn-danger">Dodaj</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Otkaži</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
    if (new URLSearchParams(window.location.search).has('izmeni')) {
        const editTarget = document.getElementById('editSection');
        if (editTarget) {
            editTarget.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
