<?php
require_once 'php/klase/Korisnik.php';
require_once 'php/klase/Recept.php';
require_once 'php/klase/Entiteti.php';
Korisnik::zahtevajPrijavu();

$recept = new Recept();
$sastojakModel = new Sastojak();
$alergenModel = new Alergen();
$sviSastojci = $sastojakModel->citaj();
$sviAlergeni = $alergenModel->citaj();

// ako je tražen detalj jednog recepta
$detalj = null;

function getRecipeImageSrc(string $filename): string {
    $filename = trim($filename);
    if ($filename === '') {
        return 'img/default.jpg';
    }

    $imgDir = __DIR__ . '/img/hrana/';
    $candidate = $imgDir . $filename;
    if (file_exists($candidate)) {
        return 'img/hrana/' . $filename;
    }

    $rootDir = __DIR__ . '/img/';
    $rootCandidate = $rootDir . $filename;
    if (file_exists($rootCandidate)) {
        return 'img/' . $filename;
    }

    $base = pathinfo($filename, PATHINFO_FILENAME);
    foreach (['png', 'jpg', 'jpeg', 'webp', 'gif'] as $ext) {
        $try = $imgDir . $base . '.' . $ext;
        if (file_exists($try)) {
            return 'img/hrana/' . $base . '.' . $ext;
        }
        $rootTry = $rootDir . $base . '.' . $ext;
        if (file_exists($rootTry)) {
            return 'img/' . $base . '.' . $ext;
        }
    }

    $files = is_dir($imgDir) ? scandir($imgDir) : [];
    foreach ($files as $file) {
        if (!is_file($imgDir . $file)) {
            continue;
        }
        $fileBase = pathinfo($file, PATHINFO_FILENAME);
        if ($fileBase === $base || strpos($fileBase, $base) !== false || strpos($base, $fileBase) !== false) {
            return 'img/hrana/' . $file;
        }
    }

    return 'img/default.jpg';
}

if (isset($_GET['id'])) {
    $lista  = $recept->citaj((int)$_GET['id']);
    $detalj = $lista[0] ?? null;
    if ($detalj) {
        $detalj['sastojci'] = $recept->getSastojke((int)$_GET['id']);
        $detalj['alergeni'] = $recept->getAlergene((int)$_GET['id']);
        $detalj['kalorije'] = $recept->izracunajKalorije((int)$_GET['id']);
        $detalj['SlikaSrc'] = getRecipeImageSrc($detalj['Slika'] ?? '');
    }
}

// ako je filtriranje po kategoriji
$kategorija = $_GET['kat'] ?? '';
$sviRecepti = $kategorija
    ? $recept->citajPoKategoriji($kategorija)
    : $recept->citajSaAutorom();

// recept za izmenu (popunjava formu)
$zaIzmenu = null;
if (isset($_GET['izmeni'])) {
    $lista    = $recept->citaj((int)$_GET['izmeni']);
    $zaIzmenu = $lista[0] ?? null;
    if ($zaIzmenu) {
        $zaIzmenu['sastojci'] = $recept->getSastojke((int)$_GET['izmeni']);
        $zaIzmenu['alergeni'] = $recept->getAlergene((int)$_GET['izmeni']);
    }
}

$uspeh = $_GET['uspeh'] ?? '';
?>
<!DOCTYPE html>
<html lang="sr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recepti – MojiRecepti</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="bg-light">
<?php include 'php/navbar.php'; ?>

<div class="container" style="margin-top:80px;">

    <?php if ($uspeh == 1) echo '<div class="alert alert-success alert-dismissible fade show">✅ Recept dodat!</div>'; ?>
    <?php if ($uspeh == 2) echo '<div class="alert alert-info alert-dismissible fade show">✏️ Recept izmenjen!</div>'; ?>
    <?php if ($uspeh == 3) echo '<div class="alert alert-warning alert-dismissible fade show">🗑️ Recept obrisan!</div>'; ?>
    <?php if (isset($_GET['greska']) && $_GET['greska'] == 1) echo '<div class="alert alert-danger">⚠️ Morate izabrati kategoriju (Doručak / Ručak / Večera / Užina).</div>'; ?>

    <?php if ($detalj): ?>
    <!-- ========== PRIKAZ DETALJA JEDNOG RECEPTA ========== -->
    <div class="mb-3">
        <a href="recepti.php" class="btn btn-outline-secondary">← Nazad na listu</a>
    </div>
    <div class="card shadow">
        <div class="card-header bg-dark text-white">
            <h3 class="mb-0"><?= htmlspecialchars($detalj['Naziv']) ?></h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-8">
                    <p class="lead"><?= nl2br(htmlspecialchars($detalj['Opis'])) ?></p>
                    <div class="row text-center mb-3">
                        <!-- info bedževi -->
                        <div class="col"><span class="badge bg-primary fs-6">⏱️ <?= $detalj['VremePripreme'] ?>min pripreme</span></div>
                        <div class="col"><span class="badge bg-secondary fs-6">🔥 <?= $detalj['VremePecenja'] ?>min kuvanja</span></div>
                        <div class="col"><span class="badge bg-success fs-6">🍽️ <?= $detalj['BrojPorcija'] ?> porcija</span></div>
                        <div class="col"><span class="badge bg-warning text-dark fs-6">🌟 <?= ucfirst($detalj['Tezina']) ?></span></div>
                    </div>

                    <!-- ukupne kalorije recepta -->
                    <div class="alert alert-info">
                        <strong>🔥 Ukupno kalorija u receptu: <?= $detalj['kalorije'] ?> kcal</strong>
                        (<?= $detalj['BrojPorcija'] > 0 ? round($detalj['kalorije'] / $detalj['BrojPorcija']) : 0 ?> kcal po porciji)
                    </div>

                    <?php if (!empty($detalj['alergeni'])): ?>
                    <div class="mb-3">
                        <strong>⚠️ Alergeni:</strong>
                        <?php foreach ($detalj['alergeni'] as $a): ?>
                            <span class="badge bg-danger me-1"><?= htmlspecialchars($a['Naziv']) ?></span>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="col-md-4 text-center">
                    <!-- placeholder za sliku - dodaj slike u img/ folder -->
                    <img src="<?= htmlspecialchars($detalj['SlikaSrc'] ?? 'img/default.jpg') ?>"
                         class="img-fluid rounded shadow" alt="<?= htmlspecialchars($detalj['Naziv']) ?>"
                         style="max-height:250px; object-fit:cover;">
                </div>
            <!-- tabela sastojaka sa nutritivnim vrednostima -->
            <?php if (!empty($detalj['sastojci'])): ?>
            <h5 class="mt-4">🥦 Sastojci i nutritivne vrednosti</h5>
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-sm">
                    <thead class="table-dark">
                        <tr>
                            <th>Sastojak</th>
                            <th>Količina</th>
                            <th>Kalorije</th>
                            <th>Proteini (g)</th>
                            <th>Ugljeni hidrati (g)</th>
                            <th>Masti (g)</th>
                            <th>Vlakna (g)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($detalj['sastojci'] as $s): ?>
                        <tr>
                            <td class="hover-img" data-img="<?= htmlspecialchars('img/sastojci/' . ($s['Slika'] ?? 'default.jpg')) ?>"><?= htmlspecialchars($s['Naziv']) ?></td>
                            <td><?= $s['Kolicina'] ?><?= $s['Jedinica'] ?></td>
                            <td><strong><?= $s['KalorijeUkupno'] ?></strong></td>
                            <td><?= $s['ProteiniUkupno'] ?></td>
                            <td><?= $s['UHUkupno'] ?></td>
                            <td><?= $s['MastiUkupno'] ?></td>
                            <td><?= $s['VlaknaUkupno'] ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <?php else: ?>
    <!-- ========== LISTA RECEPTA + FORME ========== -->

    <div class="row mb-3 align-items-center">
        <div class="col">
            <h3>📖 Recepti</h3>
        </div>
        <div class="col-auto">
            <!-- dugme otvara modal za dodavanje -->
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalDodaj">
                + Dodaj recept
            </button>
        </div>
    </div>

    <!-- filter po kategoriji -->
    <div class="mb-3">
        <a href="recepti.php" class="btn btn-sm <?= !$kategorija ? 'btn-dark' : 'btn-outline-dark' ?>">Svi</a>
        <?php foreach (['doručak','ručak','večera','užina'] as $kat): ?>
        <a href="recepti.php?kat=<?= urlencode($kat) ?>"
           class="btn btn-sm <?= $kategorija === $kat ? 'btn-dark' : 'btn-outline-dark' ?>"><?= ucfirst($kat) ?></a>
        <?php endforeach; ?>
    </div>

    <!-- tabela recepta -->
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Naziv</th>
                            <th>Kategorija</th>
                            <th>Težina</th>
                            <th>Porcije</th>
                            <th>Autor</th>
                            <th>Akcije</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($sviRecepti as $r): ?>
                        <tr>
                            <td><?= $r['ReceptID'] ?></td>
                            <td class="hover-img" data-img="<?= htmlspecialchars(getRecipeImageSrc($r['Slika'] ?? '')) ?>"><?= htmlspecialchars($r['Naziv']) ?></td>
                            <td>
                                  <?php $kat = trim((string)($r['Kategorija'] ?? '')); ?>
                                  <span class="badge bg-secondary"><?php echo ($kat === '' || $kat === '0') ? '-' : htmlspecialchars(ucfirst($kat)); ?></span>
                            </td>
                            <td><?= $r['Tezina'] ?></td>
                            <td><?= $r['BrojPorcija'] ?></td>
                            <td><?= htmlspecialchars($r['Ime'] . ' ' . $r['Prezime']) ?></td>
                            <td>
                                <!-- detalji, izmena, brisanje -->
                                <a href="recepti.php?id=<?= $r['ReceptID'] ?>" class="btn btn-sm btn-outline-primary">👁️</a>
                                <a href="recepti.php?izmeni=<?= $r['ReceptID'] ?>" class="btn btn-sm btn-outline-warning">✏️</a>
                                <a href="php/akcije.php?akcija=obrisi&tip=recept&id=<?= $r['ReceptID'] ?>"
                                   class="btn btn-sm btn-outline-danger"
                                   onclick="return confirm('Obrisati recept: <?= htmlspecialchars($r['Naziv']) ?>?')">🗑️</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <?php if ($zaIzmenu): ?>
    <!-- ========== FORMA ZA IZMENU (ispod tabele) ========== -->
    <div id="editSection" class="card mt-4 shadow border-warning">
        <div class="card-header bg-warning">
            <h5 class="mb-0">✏️ Izmena recepta: <?= htmlspecialchars($zaIzmenu['Naziv']) ?></h5>
        </div>
        <div class="card-body">
            <form action="php/akcije.php?akcija=azuriraj&tip=recept&id=<?= $zaIzmenu['ReceptID'] ?>" method="post" enctype="multipart/form-data">
                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label">Naziv</label>
                        <input type="text" name="naziv" class="form-control" value="<?= htmlspecialchars($zaIzmenu['Naziv']) ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Kategorija</label>
                        <select name="kategorija" class="form-select" required>
                            <option value="">Izaberi kategoriju</option>
                            <?php foreach (['doručak','ručak','večera','užina'] as $k): ?>
                            <option value="<?= $k ?>" <?= $zaIzmenu['Kategorija'] === $k ? 'selected' : '' ?>><?= ucfirst($k) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Opis</label>
                        <textarea name="opis" class="form-control" rows="3"><?= htmlspecialchars($zaIzmenu['Opis']) ?></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Trenutna slika</label>
                        <div class="border rounded p-2 text-center bg-white">
                            <img src="<?= htmlspecialchars(getRecipeImageSrc($zaIzmenu['Slika'] ?? '')) ?>" alt="Slika recepta" class="img-fluid" style="max-height:150px;">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Zameni sliku</label>
                        <input type="file" name="slika" class="form-control" accept="image/*">
                        <div class="form-text">Ostavi prazno ako ne želiš da menjaš postojeću sliku.</div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Vreme pripreme (min)</label>
                        <input type="number" name="vreme_pripreme" class="form-control" value="<?= $zaIzmenu['VremePripreme'] ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Vreme kuvanja (min)</label>
                        <input type="number" name="vreme_pecenja" class="form-control" value="<?= $zaIzmenu['VremePecenja'] ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Broj porcija</label>
                        <input type="number" name="broj_porcija" class="form-control" value="<?= $zaIzmenu['BrojPorcija'] ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Težina</label>
                        <select name="tezina" class="form-select">
                            <?php foreach (['lako','srednje','teško'] as $t): ?>
                            <option value="<?= $t ?>" <?= $zaIzmenu['Tezina'] === $t ? 'selected' : '' ?>><?= ucfirst($t) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="mt-3">
                    <button type="submit" class="btn btn-warning">Sačuvaj izmene</button>
                    <a href="recepti.php" class="btn btn-secondary">Otkaži</a>
                </div>
            </form>

                <hr class="my-4">

                <div class="row">
                    <div class="col-md-6">
                        <div class="card mb-3">
                            <div class="card-header bg-secondary text-white">
                                <strong>🥦 Uredi sastojke recepta</strong>
                            </div>
                            <div class="card-body">
                                <form action="php/akcije.php?akcija=dodaj&tip=recept-sastojak" method="post" class="row g-3 align-items-end">
                                    <input type="hidden" name="recept_id" value="<?= $zaIzmenu['ReceptID'] ?>">
                                    <div class="col-md-7">
                                        <label class="form-label">Dodaj sastojak</label>
                                        <select name="sastojak_id" class="form-select">
                                            <option value="">Izaberi sastojak</option>
                                            <?php foreach ($sviSastojci as $s): ?>
                                            <option value="<?= $s['SastojakID'] ?>"><?= htmlspecialchars($s['Naziv']) ?> (<?= $s['Jedinica'] ?>)</option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Količina</label>
                                        <input type="number" step="0.01" min="0" name="kolicina" class="form-control" placeholder="Količina">
                                    </div>
                                    <div class="col-md-2 d-grid">
                                        <button type="submit" class="btn btn-primary" style="white-space: nowrap;">Dodaj</button>
                                    </div>
                                </form>

                                <div class="table-responsive mt-4">
                                    <table class="table table-sm table-borderless mb-0">
                                        <thead>
                                            <tr>
                                                <th>Sastojak</th>
                                                <th>Količina</th>
                                                <th>Jedinica</th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($zaIzmenu['sastojci'] as $s): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($s['Naziv']) ?></td>
                                                <td><?= $s['Kolicina'] ?></td>
                                                <td><?= htmlspecialchars($s['Jedinica']) ?></td>
                                                <td>
                                                    <a href="php/akcije.php?akcija=obrisi&tip=recept-sastojak&recept_id=<?= $zaIzmenu['ReceptID'] ?>&sastojak_id=<?= $s['SastojakID'] ?>"
                                                       class="btn btn-sm btn-outline-danger"
                                                       onclick="return confirm('Ukloniti <?= htmlspecialchars($s['Naziv']) ?> iz recepta?')">Ukloni</a>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card mb-3">
                            <div class="card-header bg-danger text-white">
                                <strong>⚠️ Uredi alergene recepta</strong>
                            </div>
                            <div class="card-body">
                                <form action="php/akcije.php?akcija=dodaj&tip=recept-alergen" method="post" class="row g-3 align-items-end">
                                    <input type="hidden" name="recept_id" value="<?= $zaIzmenu['ReceptID'] ?>">
                                    <div class="col-md-8">
                                        <label class="form-label">Dodaj alergen</label>
                                        <select name="alergen_id" class="form-select">
                                            <option value="">Izaberi alergen</option>
                                            <?php foreach ($sviAlergeni as $a): ?>
                                            <option value="<?= $a['AlergenID'] ?>"><?= htmlspecialchars($a['Naziv']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <button type="submit" class="btn btn-danger w-100">Dodaj</button>
                                    </div>
                                </form>

                                <div class="mt-4">
                                    <?php if (empty($zaIzmenu['alergeni'])): ?>
                                        <p class="text-muted mb-0">Nema alergena u ovom receptu.</p>
                                    <?php else: ?>
                                        <?php foreach ($zaIzmenu['alergeni'] as $a): ?>
                                            <div class="d-flex align-items-center mb-2">
                                                <span class="badge bg-danger me-2"><?= htmlspecialchars($a['Naziv']) ?></span>
                                                <a href="php/akcije.php?akcija=obrisi&tip=recept-alergen&recept_id=<?= $zaIzmenu['ReceptID'] ?>&alergen_id=<?= $a['AlergenID'] ?>"
                                                   class="btn btn-sm btn-outline-dark">Ukloni</a>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>
</div>

<!-- MODAL za dodavanje novog recepta -->
<div class="modal fade" id="modalDodaj" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">+ Dodaj novi recept</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form action="php/akcije.php?akcija=dodaj&tip=recept" method="post" enctype="multipart/form-data">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label">Naziv recepta *</label>
                            <input type="text" name="naziv" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Kategorija</label>
                            <select name="kategorija" class="form-select" required>
                                <option value="">Izaberi kategoriju</option>
                                <option value="doručak">Doručak</option>
                                <option value="ručak">Ručak</option>
                                <option value="večera">Večera</option>
                                <option value="užina">Užina</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Opis i uputstvo za pripremu</label>
                            <textarea name="opis" class="form-control" rows="4" placeholder="Kako se pravi..."></textarea>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Vreme pripreme (min)</label>
                            <input type="number" name="vreme_pripreme" class="form-control" value="15" min="0">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Vreme kuvanja (min)</label>
                            <input type="number" name="vreme_pecenja" class="form-control" value="30" min="0">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Broj porcija</label>
                            <input type="number" name="broj_porcija" class="form-control" value="4" min="1">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Težina</label>
                            <select name="tezina" class="form-select">
                                <option value="lako">Lako</option>
                                <option value="srednje">Srednje</option>
                                <option value="teško">Teško</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Sastojci</label>
                            <div class="row g-2 align-items-end mb-2">
                                <div class="col-md-6">
                                    <select id="dodaj-sastojak" class="form-select">
                                        <option value="">Izaberi sastojak</option>
                                        <?php foreach ($sviSastojci as $s): ?>
                                        <option value="<?= $s['SastojakID'] ?>" data-jedinica="<?= htmlspecialchars($s['Jedinica']) ?>"><?= htmlspecialchars($s['Naziv']) ?> (<?= $s['Jedinica'] ?>)</option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <input id="dodaj-kolicinu" type="number" step="0.01" min="0" class="form-control" placeholder="Količina">
                                </div>
                                <div class="col-md-2 d-grid">
                                    <button type="button" class="btn btn-primary" onclick="dodajSastojak()">Dodaj</button>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered" id="lista-sastojaka">
                                    <thead>
                                        <tr>
                                            <th>Sastojak</th>
                                            <th>Količina</th>
                                            <th>Jedinica</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr class="text-muted"><td colspan="4">Još nema dodatih sastojaka.</td></tr>
                                    </tbody>
                                </table>
                            </div>
                            <input type="hidden" name="sastojak_ids" id="sastojak-ids">
                            <input type="hidden" name="kolicine" id="sastojak-kolicine">
                            <div class="form-text">Dodaj sve sastojke pre nego što pošalješ formu.</div>
                        </div>
                        <div class="col-12">
                            <label class="form-label d-block">Alergeni</label>
                            <div class="row g-2">
                                <?php if (!empty($sviAlergeni)): ?>
                                    <?php foreach ($sviAlergeni as $a): ?>
                                    <div class="col-6 col-md-4">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox"
                                                   name="alergeni[]"
                                                   value="<?= $a['AlergenID'] ?>"
                                                   id="alergen_<?= $a['AlergenID'] ?>">
                                            <label class="form-check-label"
                                                   for="alergen_<?= $a['AlergenID'] ?>"><?= htmlspecialchars($a['Naziv']) ?></label>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="col-12">
                                        <p class="form-text text-muted mb-0">Trenutno nema unetih alergena. Dodajte ih u <a href="alergeni.php">Alergeni</a>.</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="form-text">Označi alergene koji važe za recept.</div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Slika recepta</label>
                            <input type="file" name="slika" class="form-control" accept="image/*">
                            <div class="form-text">Ako otpremiš sliku, biće sačuvana u img/hrana/ folderu pod imenom baziranim na nazivu recepta.</div>
                        </div>
                    </div>
                    <div class="modal-footer px-0 pb-0 mt-3">
                        <button type="submit" class="btn btn-success">Dodaj recept</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Otkaži</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function dodajSastojak() {
        const select = document.getElementById('dodaj-sastojak');
        const kolicinaInput = document.getElementById('dodaj-kolicinu');
        const tabela = document.querySelector('#lista-sastojaka tbody');
        const hiddenIds = document.getElementById('sastojak-ids');
        const hiddenKolicine = document.getElementById('sastojak-kolicine');

        const sastojakId = select.value;
        const sastojakNaziv = select.options[select.selectedIndex]?.text || '';
        const jedinica = select.options[select.selectedIndex]?.dataset?.jedinica || '';
        const kolicina = parseFloat(kolicinaInput.value);

        if (!sastojakId || !kolicina || kolicina <= 0) {
            alert('Izaberi sastojak i unesi količinu veću od 0.');
            return;
        }

        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${sastojakNaziv}</td>
            <td>${kolicina.toFixed(2)}</td>
            <td>${jedinica}</td>
            <td><button type="button" class="btn btn-sm btn-outline-danger" onclick="ukloniSastojak(this)">Ukloni</button></td>
        `;
        row.dataset.sastojakId = sastojakId;
        row.dataset.kolicina = kolicina;
        tabela.querySelectorAll('tr.text-muted').forEach(el => el.remove());
        tabela.appendChild(row);

        azurirajSkrivenaPolja();
        select.value = '';
        kolicinaInput.value = '';
    }

    function ukloniSastojak(button) {
        const row = button.closest('tr');
        row.remove();
        azurirajSkrivenaPolja();
        const tabela = document.querySelector('#lista-sastojaka tbody');
        if (!tabela.querySelector('tr')) {
            const empty = document.createElement('tr');
            empty.className = 'text-muted';
            empty.innerHTML = '<td colspan="4">Još nema dodatih sastojaka.</td>';
            tabela.appendChild(empty);
        }
    }

    function azurirajSkrivenaPolja() {
        const redovi = document.querySelectorAll('#lista-sastojaka tbody tr');
        const ids = [];
        const kolicine = [];
        redovi.forEach(row => {
            if (row.dataset.sastojakId) {
                ids.push(row.dataset.sastojakId);
                kolicine.push(row.dataset.kolicina);
            }
        });
        document.getElementById('sastojak-ids').value = ids.join(',');
        document.getElementById('sastojak-kolicine').value = kolicine.join(',');
    }

</script>
<script>
    if (new URLSearchParams(window.location.search).has('izmeni')) {
        const editTarget = document.getElementById('editSection');
        if (editTarget) {
            editTarget.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }
</script>
<style>
/* Hover image popup */
.hover-popup{position:fixed;pointer-events:none;z-index:2100;border-radius:8px;overflow:hidden;box-shadow:0 6px 18px rgba(0,0,0,.25);display:none;background:#fff}
.hover-popup img{display:block;max-width:260px;max-height:260px;object-fit:cover}
</style>
<script>
document.addEventListener('DOMContentLoaded', function(){
    let hoverTimer = null;
    let lastX = 0, lastY = 0;
    const popup = document.createElement('div');
    popup.className = 'hover-popup';
    popup.innerHTML = '<img src="" alt="">';
    document.body.appendChild(popup);

    function showPopup(src, x, y){
        const img = popup.querySelector('img');
        img.src = src;
        // adjust so popup doesn't go off-screen
        const w = 280, h = 280;
        if (x + w > window.innerWidth) x = x - w - 24;
        if (y + h > window.innerHeight) y = window.innerHeight - h - 10;
        popup.style.left = (Math.max(8, x)) + 'px';
        popup.style.top = (Math.max(8, y)) + 'px';
        popup.style.display = 'block';
    }
    function hidePopup(){
        popup.style.display = 'none';
        const img = popup.querySelector('img'); img.src = '';
    }

    document.querySelectorAll('[data-img]').forEach(el=>{
        el.addEventListener('mouseenter', function(e){
            const src = el.getAttribute('data-img');
            // prefer current mouse coords if available
            if (e && e.clientX) { lastX = e.clientX; lastY = e.clientY; }
            hoverTimer = setTimeout(()=>{
                let x = lastX || (el.getBoundingClientRect().right + 8);
                let y = lastY || el.getBoundingClientRect().top;
                showPopup(src, x + 12, y + 12);
            }, 1000);
        });
        el.addEventListener('mousemove', function(e){
            if (e && e.clientX) { lastX = e.clientX; lastY = e.clientY; }
            if (popup.style.display === 'block'){
                let x = e.clientX + 12;
                let y = e.clientY + 12;
                if (x + popup.offsetWidth > window.innerWidth) x = e.clientX - popup.offsetWidth - 12;
                if (y + popup.offsetHeight > window.innerHeight) y = window.innerHeight - popup.offsetHeight - 10;
                popup.style.left = x + 'px';
                popup.style.top = y + 'px';
            }
        });
        el.addEventListener('mouseleave', function(){
            if (hoverTimer){ clearTimeout(hoverTimer); hoverTimer = null; }
            hidePopup();
        });
    });
});
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
