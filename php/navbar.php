<?php
// ============================================================
// Deljeni navbar - include-ujemo ga na svakoj stranici da ne pisemo isti kod
// ============================================================
if (session_status() === PHP_SESSION_NONE) session_start();
$imePrezime = ($_SESSION['korisnik_ime'] ?? 'Korisnik');
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
    <div class="container-fluid">
        <a class="navbar-brand fw-bold" href="panel.php">
            🍽️ MojiRecepti
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarMain">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link" href="panel.php">🏠 Početna</a></li>
                <li class="nav-item"><a class="nav-link" href="recepti.php">📖 Recepti</a></li>
                <li class="nav-item"><a class="nav-link" href="sastojci.php">🥦 Sastojci</a></li>
                <li class="nav-item"><a class="nav-link" href="obroci.php">🍴 Obroci</a></li>
                <li class="nav-item"><a class="nav-link" href="planovi.php">📅 Planovi ishrane</a></li>
                <li class="nav-item"><a class="nav-link" href="alergeni.php">⚠️ Alergeni</a></li>
            </ul>
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <!-- pokazuje ime prijavljenog korisnika -->
                    <span class="nav-link text-warning">👤 <?= htmlspecialchars($imePrezime) ?></span>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-danger" href="php/logout.php">🚪 Odjava</a>
                </li>
            </ul>
        </div>
    </div>
</nav>
