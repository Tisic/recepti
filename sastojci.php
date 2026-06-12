<?php
require_once 'php/klase/Korisnik.php';
require_once 'php/klase/Entiteti.php';
Korisnik::zahtevajPrijavu();

$sastojak = new Sastojak();
$svi      = $sastojak->citaj();

$zaIzmenu = null;
if (isset($_GET['izmeni'])) {
    $lista    = $sastojak->citaj((int)$_GET['izmeni']);
    $zaIzmenu = $lista[0] ?? null;
}
$uspeh = $_GET['uspeh'] ?? '';
?>
<!DOCTYPE html>
<html lang="sr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sastojci – MojiRecepti</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="bg-light">
<?php include 'php/navbar.php'; ?>
<div class="container" style="margin-top:80px;">

    <?php if ($uspeh == 1) echo '<div class="alert alert-success">✅ Sastojak dodat!</div>'; ?>
    <?php if ($uspeh == 2) echo '<div class="alert alert-info">✏️ Sastojak izmenjen!</div>'; ?>
    <?php if ($uspeh == 3) echo '<div class="alert alert-warning">🗑️ Sastojak obrisan!</div>'; ?>

    <div class="row mb-3">
        <div class="col"><h3>🥦 Sastojci</h3></div>
        <div class="col-auto">
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalDodaj">+ Dodaj sastojak</button>
        </div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-bordered mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>Slika</th>
                            <th>Naziv</th>
                            <th>Kcal/100g</th>
                            <th>Proteini (g)</th>
                            <th>Ugljeni hidrati (g)</th>
                            <th>Masti (g)</th>
                            <th>Vlakna (g)</th>
                            <th>Jedinica</th>
                            <th>Akcije</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($svi as $s): ?>
                        <tr>
                            <td>
                                <img src="img/sastojci/<?= htmlspecialchars($s['Slika'] ?? 'default.jpg') ?>"
                                     onerror="this.src='img/default.jpg'"
                                     alt="<?= htmlspecialchars($s['Naziv']) ?>"
                                     style="width:40px; height:40px; object-fit:cover; border-radius:6px;">
                            </td>
                            <td><?= htmlspecialchars($s['Naziv']) ?></td>
                            <td><strong><?= $s['Kalorije'] ?></strong></td>
                            <td><?= $s['Proteini'] ?></td>
                            <td><?= $s['Ugljeni_hidrati'] ?></td>
                            <td><?= $s['Masti'] ?></td>
                            <td><?= $s['Vlakna'] ?></td>
                            <td><span class="badge bg-secondary"><?= $s['Jedinica'] ?></span></td>
                            <td>
                                <a href="sastojci.php?izmeni=<?= $s['SastojakID'] ?>" class="btn btn-sm btn-outline-warning">✏️</a>
                                <a href="php/akcije.php?akcija=obrisi&tip=sastojak&id=<?= $s['SastojakID'] ?>"
                                   class="btn btn-sm btn-outline-danger"
                                   onclick="return confirm('Obrisati <?= htmlspecialchars($s['Naziv']) ?>?')">🗑️</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <?php if ($zaIzmenu): ?>
    <div id="editSection" class="card border-warning shadow">
        <div class="card-header bg-warning"><h5 class="mb-0">✏️ Izmena: <?= htmlspecialchars($zaIzmenu['Naziv']) ?></h5></div>
        <div class="card-body">
            <form action="php/akcije.php?akcija=azuriraj&tip=sastojak&id=<?= $zaIzmenu['SastojakID'] ?>" method="post" enctype="multipart/form-data">
                <div class="row g-3">
                    <div class="col-md-6"><label class="form-label">Naziv</label>
                        <input type="text" name="naziv" class="form-control" value="<?= htmlspecialchars($zaIzmenu['Naziv']) ?>" required></div>
                    <div class="col-md-6"><label class="form-label">Slika sastojka</label>
                        <input type="file" name="slika" class="form-control" accept="image/*"></div>
                    <div class="col-md-2"><label class="form-label">Kalorije</label>
                        <input type="number" step="0.01" name="kalorije" class="form-control" value="<?= $zaIzmenu['Kalorije'] ?>"></div>
                    <div class="col-md-2"><label class="form-label">Proteini</label>
                        <input type="number" step="0.01" name="proteini" class="form-control" value="<?= $zaIzmenu['Proteini'] ?>"></div>
                    <div class="col-md-2"><label class="form-label">Ugljeni hidrati</label>
                        <input type="number" step="0.01" name="ugljeni_hidrati" class="form-control" value="<?= $zaIzmenu['Ugljeni_hidrati'] ?>"></div>
                    <div class="col-md-2"><label class="form-label">Masti</label>
                        <input type="number" step="0.01" name="masti" class="form-control" value="<?= $zaIzmenu['Masti'] ?>"></div>
                    <div class="col-md-2"><label class="form-label">Vlakna</label>
                        <input type="number" step="0.01" name="vlakna" class="form-control" value="<?= $zaIzmenu['Vlakna'] ?>"></div>
                    <div class="col-md-2"><label class="form-label">Jedinica</label>
                        <select name="jedinica" class="form-select">
                            <?php foreach (['g','ml','kom'] as $j): ?>
                            <option value="<?= $j ?>" <?= $zaIzmenu['Jedinica']===$j?'selected':'' ?>><?= $j ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="mt-3">
                    <button type="submit" class="btn btn-warning">Sačuvaj</button>
                    <a href="sastojci.php" class="btn btn-secondary">Otkaži</a>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Modal dodaj -->
<div class="modal fade" id="modalDodaj" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">+ Dodaj novi sastojak</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form action="php/akcije.php?akcija=dodaj&tip=sastojak" method="post" enctype="multipart/form-data">
                    <div class="row g-3">
                        <div class="col-md-6"><label class="form-label">Naziv *</label>
                            <input type="text" name="naziv" class="form-control" required></div>
                        <div class="col-md-6"><label class="form-label">Slika sastojka</label>
                            <input type="file" name="slika" class="form-control" accept="image/*"></div>
                        <div class="col-md-2"><label class="form-label">Kalorije (kcal)</label>
                            <input type="number" step="0.01" name="kalorije" class="form-control" value="0"></div>
                        <div class="col-md-2"><label class="form-label">Proteini (g)</label>
                            <input type="number" step="0.01" name="proteini" class="form-control" value="0"></div>
                        <div class="col-md-2"><label class="form-label">Ug. hidrati (g)</label>
                            <input type="number" step="0.01" name="ugljeni_hidrati" class="form-control" value="0"></div>
                        <div class="col-md-2"><label class="form-label">Masti (g)</label>
                            <input type="number" step="0.01" name="masti" class="form-control" value="0"></div>
                        <div class="col-md-2"><label class="form-label">Vlakna (g)</label>
                            <input type="number" step="0.01" name="vlakna" class="form-control" value="0"></div>
                        <div class="col-md-2"><label class="form-label">Jedinica</label>
                            <select name="jedinica" class="form-select">
                                <option value="g">g</option>
                                <option value="ml">ml</option>
                                <option value="kom">kom</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer px-0 pb-0 mt-3">
                        <button type="submit" class="btn btn-success">Dodaj</button>
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
