<?php

// ============================================================
// Obrada registracione forme
// ============================================================

require_once __DIR__ . '/klase/Korisnik.php';

$korisnik = new Korisnik();

$ime      = trim($_POST['ime'] ?? '');
$prezime  = trim($_POST['prezime'] ?? '');
$email    = trim($_POST['email'] ?? '');
$lozinka  = trim($_POST['lozinka'] ?? '');
$potvrda  = trim($_POST['potvrda'] ?? '');

// osnovna validacija - sva polja su obavezna
if (empty($ime) || empty($prezime) || empty($email) || empty($lozinka)) {
    header('Location: ../register.php?greska=1');
    exit;
}

// lozinke moraju da se podudaraju
if ($lozinka !== $potvrda) {
    header('Location: ../register.php?greska=2');
    exit;
}

// email mora biti jedinstven
if ($korisnik->emailPostoji($email)) {
    header('Location: ../register.php?greska=3');
    exit;
}

// sve je ok - registrujemo korisnika
if ($korisnik->dodaj(['ime' => $ime, 'prezime' => $prezime, 'email' => $email, 'lozinka' => $lozinka])) {
    header('Location: ../index.php?uspeh=1');
    exit;
} else {
    header('Location: ../register.php?greska=4');
    exit;
}
