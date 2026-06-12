<?php
require_once 'php/klase/Korisnik.php';
require_once 'php/klase/Entiteti.php';
Korisnik::zahtevajPrijavu();

$plan       = new PlanIshrane();
$korisnikId = (int)$_SESSION['korisnik_id'];
$svi        = $plan->citajZaKorisnika($korisnikId);

$zaIzmenu = null;
if (isset($_GET['izmeni'])) {
    $lista    = $plan->citaj((int)$_GET['izmeni']);
    $zaIzmenu = $lista[0] ?? null;
}
$uspeh = $_GET['uspeh'] ?? '';
?>
<!DOCTYPE html>
<html lang="sr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Planovi ishrane – MojiRecepti</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="bg-light">
<?php include 'php/navbar.php'; ?>
<div class="container" style="margin-top:80px;">

    <?php if ($uspeh == 1) echo '<div class="alert alert-success">✅ Plan ishrane dodat!</div>'; ?>
    <?php if ($uspeh == 2) echo '<div class="alert alert-info">✏️ Plan izmenjen!</div>'; ?>
    <?php if ($uspeh == 3) echo '<div class="alert alert-warning">🗑️ Plan obrisan!</div>'; ?>

    <div class="row mb-3">
        <div class="col"><h3>📅 Planovi ishrane</h3></div>
        <div class="col-auto">
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalDodaj">+ Novi plan</button>
        </div>
    </div>

    <div class="row g-3">
        <?php if (empty($svi)): ?>
            <div class="col-12 text-center text-muted py-5">
                <h5>Nemaš nijedan plan ishrane.</h5>
                <p>Kreiraj prvi plan i prati svoju ishranu!</p>
            </div>
        <?php endif; ?>

        <?php foreach ($svi as $p): ?>
        <div class="col-md-6 col-lg-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0">📅 <?= htmlspecialchars($p['Naziv']) ?></h6>
                </div>
                <div class="card-body">
                    <p class="text-muted small"><?= htmlspecialchars($p['Opis'] ?? '') ?></p>
                    <ul class="list-unstyled small">
                        <li>📆 Od: <strong><?= date('d.m.Y', strtotime($p['DatumPocetka'])) ?></strong></li>
                        <li>📆 Do: <strong><?= date('d.m.Y', strtotime($p['DatumZavrsetka'])) ?></strong></li>
                        <li>🔥 Cilj: <strong><?= number_format($p['CiljKalorija']) ?> kcal/dan</strong></li>
                    </ul>
                </div>
                <div class="card-footer d-flex justify-content-between">
                    <a href="planovi.php?izmeni=<?= $p['PlanID'] ?>" class="btn btn-sm btn-outline-warning">✏️ Izmeni</a>
                    <a href="php/akcije.php?akcija=obrisi&tip=plan&id=<?= $p['PlanID'] ?>"
                       class="btn btn-sm btn-outline-danger"
                       onclick="return confirm('Obrisati plan: <?= htmlspecialchars($p['Naziv']) ?>?')">🗑️ Obriši</a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <?php if ($zaIzmenu): ?>
    <div id="editSection" class="card border-warning shadow mt-4">
        <div class="card-header bg-warning"><h5 class="mb-0">✏️ Izmena plana</h5></div>
        <div class="card-body">
            <form action="php/akcije.php?akcija=azuriraj&tip=plan&id=<?= $zaIzmenu['PlanID'] ?>" method="post">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Naziv plana</label>
                        <input type="text" name="naziv" class="form-control" value="<?= htmlspecialchars($zaIzmenu['Naziv']) ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Cilj kalorija (kcal/dan)</label>
                        <input type="number" name="cilj_kalorija" class="form-control" value="<?= $zaIzmenu['CiljKalorija'] ?>">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Opis</label>
                        <textarea name="opis" class="form-control" rows="3"><?= htmlspecialchars($zaIzmenu['Opis'] ?? '') ?></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Datum početka</label>
                        <input type="date" name="datum_pocetka" class="form-control" value="<?= $zaIzmenu['DatumPocetka'] ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Datum završetka</label>
                        <input type="date" name="datum_zavrsetka" class="form-control" value="<?= $zaIzmenu['DatumZavrsetka'] ?>">
                    </div>
                </div>
                <div class="mt-3">
                    <button type="submit" class="btn btn-warning">Sačuvaj</button>
                    <a href="planovi.php" class="btn btn-secondary">Otkaži</a>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>
</div>

<div class="modal fade" id="modalDodaj" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">+ Novi plan ishrane</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form action="php/akcije.php?akcija=dodaj&tip=plan" method="post">
                    <div class="mb-3">
                        <label class="form-label">Naziv plana *</label>
                        <input type="text" name="naziv" class="form-control" placeholder="npr. Dijeta jul 2025" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Opis</label>
                        <textarea name="opis" class="form-control" rows="3" placeholder="Cilj i opis plana..."></textarea>
                    </div>
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label">Datum početka</label>
                            <input type="date" name="datum_pocetka" class="form-control" value="<?= date('Y-m-d') ?>">
                        </div>
                        <div class="col-6">
                            <label class="form-label">Datum završetka</label>
                            <input type="date" name="datum_zavrsetka" class="form-control" value="<?= date('Y-m-d', strtotime('+30 days')) ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Dnevni cilj kalorija (kcal)</label>
                            <input type="number" name="cilj_kalorija" class="form-control" value="2000" min="500" max="6000">
                        </div>
                    </div>
                    <div class="modal-footer px-0 pb-0 mt-3">
                        <button type="submit" class="btn btn-primary">Kreiraj plan</button>
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
