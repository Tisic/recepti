<?php
// panel zahteva prijavljenog korisnika - ako nije, idi na login
require_once 'php/klase/Korisnik.php';
require_once 'php/klase/Recept.php';
require_once 'php/klase/Entiteti.php';
Korisnik::zahtevajPrijavu();

// uzimamo statistike za dashboard kartice
$recept   = new Recept();
$alergen  = new Alergen();
$sastojak = new Sastojak();
$obrok    = new Obrok();
$plan     = new PlanIshrane();

$brRec  = $recept->broji();
$brAler = $alergen->broji();
$brSas  = $sastojak->broji();
$brObr  = $obrok->broji();
$brPlan = $plan->broji();

// poslednjih 5 recepta za brzi pregled
$poslednji = $recept->citajSaAutorom();
$poslednji = array_slice($poslednji, 0, 5);
?>
<!DOCTYPE html>
<html lang="sr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel – MojiRecepti</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="bg-light">

<?php include 'php/navbar.php'; ?>

<div class="container" style="margin-top:80px;">

    <div class="row mb-4">
        <div class="col">
            <h3>Dobrodošao, <?= htmlspecialchars($_SESSION['korisnik_ime']) ?>! 👋</h3>
            <p class="text-muted">Ovo je tvoj pregled ishrane i recepata.</p>
        </div>
    </div>

    <!-- statisticke kartice -->
    <div class="row g-3 mb-5">
        <div class="col-6 col-md-4 col-lg-2">
            <div class="card text-white bg-primary text-center shadow-sm">
                <div class="card-body">
                    <h2 class="card-title mb-0"><?= $brRec ?></h2>
                    <small>📖 Recepti</small>
                </div>
                <div class="card-footer p-1"><a href="recepti.php" class="small">Pogledaj</a></div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-lg-2">
            <div class="card text-white bg-success text-center shadow-sm">
                <div class="card-body">
                    <h2 class="card-title mb-0"><?= $brSas ?></h2>
                    <small>🥦 Sastojci</small>
                </div>
                <div class="card-footer p-1"><a href="sastojci.php" class="small">Pogledaj</a></div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-lg-2">
            <div class="card text-white bg-warning text-center shadow-sm">
                <div class="card-body">
                    <h2 class="card-title mb-0"><?= $brObr ?></h2>
                    <small>🍴 Obroci</small>
                </div>
                <div class="card-footer p-1"><a href="obroci.php" class="small">Pogledaj</a></div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-lg-2">
            <div class="card text-white bg-info text-center shadow-sm">
                <div class="card-body">
                    <h2 class="card-title mb-0"><?= $brPlan ?></h2>
                    <small>📅 Planovi</small>
                </div>
                <div class="card-footer p-1"><a href="planovi.php" class="small">Pogledaj</a></div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-lg-2">
            <div class="card text-white bg-danger text-center shadow-sm">
                <div class="card-body">
                    <h2 class="card-title mb-0"><?= $brAler ?></h2>
                    <small>⚠️ Alergeni</small>
                </div>
                <div class="card-footer p-1"><a href="alergeni.php" class="small">Pogledaj</a></div>
            </div>
        </div>
    </div>

    <!-- poslednjih 5 recepta u tabeli -->
    <div class="card shadow-sm">
        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">📖 Poslednji dodati recepti</h5>
            <a href="recepti.php" class="btn btn-sm btn-outline-light">Svi recepti</a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Naziv</th>
                            <th>Kategorija</th>
                            <th>Autor</th>
                            <th>Porcije</th>
                            <th>Datum</th>
                            <th>Akcije</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($poslednji as $r): ?>
                        <tr>
                            <td><?= htmlspecialchars($r['Naziv']) ?></td>
                            <td><span class="badge bg-secondary"><?= htmlspecialchars($r['Kategorija']) ?></span></td>
                            <td><?= htmlspecialchars($r['Ime'] . ' ' . $r['Prezime']) ?></td>
                            <td><?= $r['BrojPorcija'] ?></td>
                            <td><?= date('d.m.Y', strtotime($r['DatumDodavanja'])) ?></td>
                            <td>
                                <a href="recepti.php?id=<?= $r['ReceptID'] ?>" class="btn btn-sm btn-outline-primary">Detalji</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
