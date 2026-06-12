<?php
// panel zahteva prijavljenog korisnika - ako nije, idi na login
require_once 'php/klase/Korisnik.php';
require_once 'php/klase/Recept.php';
require_once 'php/klase/Entiteti.php';
Korisnik::zahtevajPrijavu();

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
                            <td class="hover-img" data-img="<?= htmlspecialchars(getRecipeImageSrc($r['Slika'] ?? '')) ?>"><?= htmlspecialchars($r['Naziv']) ?></td>
                            <?php $kat = trim((string)($r['Kategorija'] ?? '')); ?>
                            <td><span class="badge bg-secondary"><?= ($kat === '' || $kat === '0') ? '-' : htmlspecialchars(ucfirst($kat)) ?></span></td>
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

<style>
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
        const w = 280, h = 280;
        if (x + w > window.innerWidth) x = x - w - 24;
        if (y + h > window.innerHeight) y = window.innerHeight - h - 10;
        popup.style.left = (Math.max(8, x)) + 'px';
        popup.style.top = (Math.max(8, y)) + 'px';
        popup.style.display = 'block';
    }
    function hidePopup(){
        popup.style.display = 'none';
        popup.querySelector('img').src = '';
    }

    document.querySelectorAll('[data-img]').forEach(el=>{
        el.addEventListener('mouseenter', function(e){
            const src = el.getAttribute('data-img');
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
