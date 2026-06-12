<?php
require_once 'php/klase/Korisnik.php';
require_once 'php/klase/Recept.php';
require_once 'php/klase/Entiteti.php';
Korisnik::zahtevajPrijavu();

$obrok    = new Obrok();
$recept   = new Recept();
$korisnikId = (int)$_SESSION['korisnik_id'];
$svi      = $obrok->citajZaKorisnika($korisnikId);
$sviRec   = $recept->citajSaAutorom(); // za select listu u formi

$zaIzmenu = null;
if (isset($_GET['izmeni'])) {
    $lista    = $obrok->citaj((int)$_GET['izmeni']);
    $zaIzmenu = $lista[0] ?? null;
}
$uspeh = $_GET['uspeh'] ?? '';
?>
<!DOCTYPE html>
<html lang="sr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Obroci – MojiRecepti</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="bg-light">
<?php include 'php/navbar.php'; ?>
<div class="container" style="margin-top:80px;">

    <?php if ($uspeh == 1) echo '<div class="alert alert-success">✅ Obrok dodat!</div>'; ?>
    <?php if ($uspeh == 2) echo '<div class="alert alert-info">✏️ Obrok izmenjen!</div>'; ?>
    <?php if ($uspeh == 3) echo '<div class="alert alert-warning">🗑️ Obrok obrisan!</div>'; ?>

    <div class="row mb-3">
        <div class="col"><h3>🍴 Moji obroci</h3></div>
        <div class="col-auto">
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalDodaj">+ Dodaj obrok</button>
        </div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>Datum</th>
                            <th>Vrsta</th>
                            <th>Recept</th>
                            <th>Porcije</th>
                            <th>Kalorije</th>
                            <th>Napomena</th>
                            <th>Akcije</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($svi as $o): ?>
                        <tr>
                            <td><?= date('d.m.Y', strtotime($o['DatumObroka'])) ?></td>
                            <td><span class="badge bg-info"><?= htmlspecialchars($o['VrstaObroka']) ?></span></td>
                            <td><?= htmlspecialchars($o['NazivRecepta'] ?? '-') ?></td>
                            <td><?= $o['BrojPorcija'] ?></td>
                            <!-- ukupne kalorije računamo ovde na osnovu porcija -->
                            <td><strong><?= $o['UkupnoKalorija'] ?? '?' ?> kcal</strong></td>
                            <td class="text-muted small"><?= htmlspecialchars($o['Napomena'] ?? '') ?></td>
                            <td>
                                <a href="obroci.php?izmeni=<?= $o['ObrokID'] ?>" class="btn btn-sm btn-outline-warning">✏️</a>
                                <a href="php/akcije.php?akcija=obrisi&tip=obrok&id=<?= $o['ObrokID'] ?>"
                                   class="btn btn-sm btn-outline-danger"
                                   onclick="return confirm('Obrisati ovaj obrok?')">🗑️</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($svi)): ?>
                        <tr><td colspan="7" class="text-center text-muted py-4">Nema unetih obroka. Dodaj prvi!</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <?php if ($zaIzmenu): ?>
    <div id="editSection" class="card border-warning shadow">
        <div class="card-header bg-warning"><h5 class="mb-0">✏️ Izmena obroka</h5></div>
        <div class="card-body">
            <form action="php/akcije.php?akcija=azuriraj&tip=obrok&id=<?= $zaIzmenu['ObrokID'] ?>" method="post">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Recept</label>
                        <select name="recept_id" class="form-select">
                            <?php foreach ($sviRec as $r): ?>
                            <option value="<?= $r['ReceptID'] ?>" <?= $zaIzmenu['ReceptID']==$r['ReceptID']?'selected':'' ?>>
                                <?= htmlspecialchars($r['Naziv']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Datum</label>
                        <input type="date" name="datum" class="form-control" value="<?= $zaIzmenu['DatumObroka'] ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Vrsta</label>
                        <select name="vrsta" class="form-select">
                            <?php foreach (['doručak','ručak','večera','užina'] as $v): ?>
                            <option value="<?= $v ?>" <?= $zaIzmenu['VrstaObroka']===$v?'selected':'' ?>><?= ucfirst($v) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Porcije</label>
                        <input type="number" step="0.5" min="0.5" name="porcije" class="form-control" value="<?= $zaIzmenu['BrojPorcija'] ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Napomena</label>
                        <input type="text" name="napomena" class="form-control" value="<?= htmlspecialchars($zaIzmenu['Napomena'] ?? '') ?>">
                    </div>
                </div>
                <div class="mt-3">
                    <button type="submit" class="btn btn-warning">Sačuvaj</button>
                    <a href="obroci.php" class="btn btn-secondary">Otkaži</a>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>
</div>

<div class="modal fade" id="modalDodaj" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">+ Dodaj obrok</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form action="php/akcije.php?akcija=dodaj&tip=obrok" method="post">
                    <div class="mb-3">
                        <label class="form-label">Recept *</label>
                        <select name="recept_id" class="form-select" required>
                            <?php foreach ($sviRec as $r): ?>
                            <option value="<?= $r['ReceptID'] ?>"><?= htmlspecialchars($r['Naziv']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label">Datum</label>
                            <input type="date" name="datum" class="form-control" value="<?= date('Y-m-d') ?>">
                        </div>
                        <div class="col-6">
                            <label class="form-label">Vrsta obroka</label>
                            <select name="vrsta" class="form-select">
                                <option value="doručak">Doručak</option>
                                <option value="ručak">Ručak</option>
                                <option value="večera">Večera</option>
                                <option value="užina">Užina</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Broj porcija</label>
                            <input type="number" step="0.5" min="0.5" name="porcije" class="form-control" value="1">
                        </div>
                        <div class="col-6">
                            <label class="form-label">Napomena (opciono)</label>
                            <input type="text" name="napomena" class="form-control" placeholder="npr. bez soli">
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
