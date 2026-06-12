<?php

// ============================================================
// Odjava korisnika - brise sesiju i vraca na login stranicu
// ============================================================

require_once __DIR__ . '/klase/Korisnik.php';

$korisnik = new Korisnik();
$korisnik->odjavi();

header('Location: ../index.php?uspeh=2');
exit;
