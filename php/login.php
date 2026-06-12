<?php

// ============================================================
// Obrada login forme - prima POST podatke i pokusava prijavu
// ============================================================

require_once __DIR__ . '/klase/Korisnik.php';

$korisnik = new Korisnik();

$email   = trim($_POST['email'] ?? '');
$lozinka = trim($_POST['lozinka'] ?? '');

if (empty($email) || empty($lozinka)) {
    // ako nisu popunjena oba polja - nazad na login sa greškom
    header('Location: ../index.php?greska=1');
    exit;
}

if ($korisnik->prijavi($email, $lozinka)) {
    // uspesna prijava - idi na panel
    header('Location: ../panel.php');
    exit;
} else {
    // pogresni podaci - nazad na login
    header('Location: ../index.php?greska=1');
    exit;
}
