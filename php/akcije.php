<?php

// ============================================================
// Centralni handler za sve CRUD akcije
// Poziva se sa ?akcija=xxx&tip=xxx
// ============================================================

require_once __DIR__ . '/klase/Korisnik.php';
require_once __DIR__ . '/klase/Recept.php';
require_once __DIR__ . '/klase/Entiteti.php';

// svaka akcija zahteva prijavljenog korisnika
Korisnik::zahtevajPrijavu();

$akcija = $_GET['akcija'] ?? '';
$tip    = $_GET['tip']    ?? '';

// pomocna funkcija - uzima korisnika iz sesije
function getKorisnikId(): int {
    if (session_status() === PHP_SESSION_NONE) session_start();
    return (int)($_SESSION['korisnik_id'] ?? 0);
}

function sanitizeFilename(string $naziv): string {
    $naziv = trim(mb_strtolower($naziv, 'UTF-8'));
    $naziv = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $naziv) ?: $naziv;
    $naziv = preg_replace('/[^a-z0-9]+/', '_', $naziv);
    $naziv = trim($naziv, '_');
    if ($naziv === '') {
        $naziv = 'file';
    }
    return $naziv;
}

function getUploadedImageName(string $naziv, string $subfolder, string $field = 'slika'): ?string {
    $subfolder = trim($subfolder, '/');
    $imgDir = __DIR__ . '/../img/' . $subfolder . '/';
    if (!is_dir($imgDir)) {
        mkdir($imgDir, 0755, true);
    }

    $file = $_FILES[$field] ?? null;
    if (!$file || $file['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name'])) {
        return null;
    }

    $info = @getimagesize($file['tmp_name']);
    $mime = $info['mime'] ?? '';
    $extensions = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/gif'  => 'gif',
        'image/webp' => 'webp',
    ];

    if (!isset($extensions[$mime])) {
        return null;
    }

    $baseName = sanitizeFilename($naziv);
    $extension = $extensions[$mime];
    $fileName = $baseName . '.' . $extension;
    $target = $imgDir . $fileName;
    $counter = 1;

    while (file_exists($target)) {
        $fileName = $baseName . '_' . $counter . '.' . $extension;
        $target = $imgDir . $fileName;
        $counter++;
    }

    if (move_uploaded_file($file['tmp_name'], $target)) {
        return $fileName;
    }

    return null;
}

// ============================================================
// Routing - na osnovu akcije i tipa pozivamo odgovarajucu metodu
// ============================================================

switch ($tip) {

    // --------------------------------------------------------
    // RECEPTI
    // --------------------------------------------------------
    case 'recept':
        $recept = new Recept();

        if ($akcija === 'dodaj') {
            $naziv = trim($_POST['naziv'] ?? '');
            $slika = getUploadedImageName($naziv, 'hrana') ?? 'default.jpg';
            $podaci = [
                'korisnik_id'    => getKorisnikId(),
                'naziv'          => $naziv,
                'opis'           => trim($_POST['opis'] ?? ''),
                'vreme_pripreme' => (int)($_POST['vreme_pripreme'] ?? 0),
                'vreme_pecenja'  => (int)($_POST['vreme_pecenja'] ?? 0),
                'broj_porcija'   => (int)($_POST['broj_porcija'] ?? 1),
                'kategorija'     => $_POST['kategorija'] ?? '',
                'tezina'         => $_POST['tezina'] ?? 'lako',
                'slika'          => $slika,
            ];
            if ($recept->dodaj($podaci)) {
                $receptId = $recept->poslednjiID();
                foreach ($_POST['alergeni'] ?? [] as $alergenId) {
                    $alergenId = (int)$alergenId;
                    if ($alergenId > 0) {
                        $recept->dodajAlergen($receptId, $alergenId);
                    }
                }

                if (!empty($_POST['sastojak_ids']) && !empty($_POST['kolicine'])) {
                    $ids = explode(',', $_POST['sastojak_ids']);
                    $kolicine = explode(',', $_POST['kolicine']);
                    foreach ($ids as $index => $sastojakId) {
                        $sastojakId = (int)$sastojakId;
                        $kolicina = isset($kolicine[$index]) ? (float)$kolicine[$index] : 0;
                        if ($sastojakId > 0 && $kolicina > 0) {
                            $recept->dodajSastojak($receptId, $sastojakId, $kolicina);
                        }
                    }
                } else {
                    $sastojakId = (int)($_POST['sastojak_id'] ?? 0);
                    $kolicina   = (float)($_POST['kolicina'] ?? 0);
                    if ($sastojakId > 0 && $kolicina > 0) {
                        $recept->dodajSastojak($receptId, $sastojakId, $kolicina);
                    }
                }
            }
            header('Location: ../recepti.php?uspeh=1');

        } elseif ($akcija === 'azuriraj') {
            $id = (int)($_GET['id'] ?? 0);
            $slika = getUploadedImageName(trim($_POST['naziv'] ?? ''), 'hrana');
            $podaci = [
                'naziv'          => trim($_POST['naziv'] ?? ''),
                'opis'           => trim($_POST['opis'] ?? ''),
                'vreme_pripreme' => (int)($_POST['vreme_pripreme'] ?? 0),
                'vreme_pecenja'  => (int)($_POST['vreme_pecenja'] ?? 0),
                'broj_porcija'   => (int)($_POST['broj_porcija'] ?? 1),
                'kategorija'     => $_POST['kategorija'] ?? '',
                'tezina'         => $_POST['tezina'] ?? 'lako',
            ];
            if ($slika !== null) {
                $podaci['slika'] = $slika;
            }
            $recept->azuriraj($id, $podaci);
            header('Location: ../recepti.php?uspeh=2');

        } elseif ($akcija === 'obrisi') {
            $id = (int)($_GET['id'] ?? 0);
            $recept->obrisi($id);
            header('Location: ../recepti.php?uspeh=3');
        }
        break;

    case 'recept-sastojak':
        $recept = new Recept();

        if ($akcija === 'dodaj') {
            $receptId   = (int)($_POST['recept_id'] ?? 0);
            $sastojakId = (int)($_POST['sastojak_id'] ?? 0);
            $kolicina   = (float)($_POST['kolicina'] ?? 0);
            if ($receptId > 0 && $sastojakId > 0 && $kolicina > 0) {
                $recept->dodajSastojak($receptId, $sastojakId, $kolicina);
            }
            header('Location: ../recepti.php?id=' . $receptId);

        } elseif ($akcija === 'obrisi') {
            $receptId   = (int)($_GET['recept_id'] ?? 0);
            $sastojakId = (int)($_GET['sastojak_id'] ?? 0);
            if ($receptId > 0 && $sastojakId > 0) {
                $recept->obrisiSastojak($receptId, $sastojakId);
            }
            header('Location: ../recepti.php?id=' . $receptId);
        }
        break;

    case 'recept-alergen':
        $recept = new Recept();

        if ($akcija === 'dodaj') {
            $receptId  = (int)($_POST['recept_id'] ?? 0);
            $alergenId = (int)($_POST['alergen_id'] ?? 0);
            if ($receptId > 0 && $alergenId > 0) {
                $recept->dodajAlergen($receptId, $alergenId);
            }
            header('Location: ../recepti.php?id=' . $receptId);

        } elseif ($akcija === 'obrisi') {
            $receptId  = (int)($_GET['recept_id'] ?? 0);
            $alergenId = (int)($_GET['alergen_id'] ?? 0);
            if ($receptId > 0 && $alergenId > 0) {
                $recept->obrisiAlergen($receptId, $alergenId);
            }
            header('Location: ../recepti.php?id=' . $receptId);
        }
        break;

    // --------------------------------------------------------
    // ALERGENI
    // --------------------------------------------------------
    case 'alergen':
        $alergen = new Alergen();

        if ($akcija === 'dodaj') {
            $alergen->dodaj([
                'naziv' => trim($_POST['naziv'] ?? ''),
                'opis'  => trim($_POST['opis'] ?? ''),
            ]);
            header('Location: ../alergeni.php?uspeh=1');

        } elseif ($akcija === 'azuriraj') {
            $id = (int)($_GET['id'] ?? 0);
            $alergen->azuriraj($id, [
                'naziv' => trim($_POST['naziv'] ?? ''),
                'opis'  => trim($_POST['opis'] ?? ''),
            ]);
            header('Location: ../alergeni.php?uspeh=2');

        } elseif ($akcija === 'obrisi') {
            $id = (int)($_GET['id'] ?? 0);
            $alergen->obrisi($id);
            header('Location: ../alergeni.php?uspeh=3');
        }
        break;

    // --------------------------------------------------------
    // SASTOJCI
    // --------------------------------------------------------
    case 'sastojak':
        $sastojak = new Sastojak();

        if ($akcija === 'dodaj') {
            $naziv = trim($_POST['naziv'] ?? '');
            $sastojak->dodaj([
                'naziv'           => $naziv,
                'kalorije'        => (float)($_POST['kalorije'] ?? 0),
                'proteini'        => (float)($_POST['proteini'] ?? 0),
                'ugljeni_hidrati' => (float)($_POST['ugljeni_hidrati'] ?? 0),
                'masti'           => (float)($_POST['masti'] ?? 0),
                'vlakna'          => (float)($_POST['vlakna'] ?? 0),
                'jedinica'        => $_POST['jedinica'] ?? 'g',
                'slika'           => getUploadedImageName($naziv, 'sastojci') ?? 'default.jpg',
            ]);
            header('Location: ../sastojci.php?uspeh=1');

        } elseif ($akcija === 'azuriraj') {
            $id = (int)($_GET['id'] ?? 0);
            $slika = getUploadedImageName(trim($_POST['naziv'] ?? ''), 'sastojci');
            $data = [
                'naziv'           => trim($_POST['naziv'] ?? ''),
                'kalorije'        => (float)($_POST['kalorije'] ?? 0),
                'proteini'        => (float)($_POST['proteini'] ?? 0),
                'ugljeni_hidrati' => (float)($_POST['ugljeni_hidrati'] ?? 0),
                'masti'           => (float)($_POST['masti'] ?? 0),
                'vlakna'          => (float)($_POST['vlakna'] ?? 0),
                'jedinica'        => $_POST['jedinica'] ?? 'g',
            ];
            if ($slika !== null) {
                $data['slika'] = $slika;
            }
            $sastojak->azuriraj($id, $data);
            header('Location: ../sastojci.php?uspeh=2');

        } elseif ($akcija === 'obrisi') {
            $id = (int)($_GET['id'] ?? 0);
            $sastojak->obrisi($id);
            header('Location: ../sastojci.php?uspeh=3');
        }
        break;

    // --------------------------------------------------------
    // OBROCI
    // --------------------------------------------------------
    case 'obrok':
        $obrok = new Obrok();

        if ($akcija === 'dodaj') {
            $obrok->dodaj([
                'korisnik_id' => getKorisnikId(),
                'recept_id'   => (int)($_POST['recept_id'] ?? 0),
                'datum'       => $_POST['datum'] ?? date('Y-m-d'),
                'vrsta'       => $_POST['vrsta'] ?? 'ručak',
                'porcije'     => (float)($_POST['porcije'] ?? 1),
                'napomena'    => trim($_POST['napomena'] ?? ''),
            ]);
            header('Location: ../obroci.php?uspeh=1');

        } elseif ($akcija === 'azuriraj') {
            $id = (int)($_GET['id'] ?? 0);
            $obrok->azuriraj($id, [
                'recept_id' => (int)($_POST['recept_id'] ?? 0),
                'datum'     => $_POST['datum'] ?? date('Y-m-d'),
                'vrsta'     => $_POST['vrsta'] ?? 'ručak',
                'porcije'   => (float)($_POST['porcije'] ?? 1),
                'napomena'  => trim($_POST['napomena'] ?? ''),
            ]);
            header('Location: ../obroci.php?uspeh=2');

        } elseif ($akcija === 'obrisi') {
            $id = (int)($_GET['id'] ?? 0);
            $obrok->obrisi($id);
            header('Location: ../obroci.php?uspeh=3');
        }
        break;

    // --------------------------------------------------------
    // PLANOVI ISHRANE
    // --------------------------------------------------------
    case 'plan':
        $plan = new PlanIshrane();

        if ($akcija === 'dodaj') {
            $plan->dodaj([
                'korisnik_id'     => getKorisnikId(),
                'naziv'           => trim($_POST['naziv'] ?? ''),
                'opis'            => trim($_POST['opis'] ?? ''),
                'datum_pocetka'   => $_POST['datum_pocetka'] ?? date('Y-m-d'),
                'datum_zavrsetka' => $_POST['datum_zavrsetka'] ?? date('Y-m-d'),
                'cilj_kalorija'   => (int)($_POST['cilj_kalorija'] ?? 2000),
            ]);
            header('Location: ../planovi.php?uspeh=1');

        } elseif ($akcija === 'azuriraj') {
            $id = (int)($_GET['id'] ?? 0);
            $plan->azuriraj($id, [
                'naziv'           => trim($_POST['naziv'] ?? ''),
                'opis'            => trim($_POST['opis'] ?? ''),
                'datum_pocetka'   => $_POST['datum_pocetka'] ?? date('Y-m-d'),
                'datum_zavrsetka' => $_POST['datum_zavrsetka'] ?? date('Y-m-d'),
                'cilj_kalorija'   => (int)($_POST['cilj_kalorija'] ?? 2000),
            ]);
            header('Location: ../planovi.php?uspeh=2');

        } elseif ($akcija === 'obrisi') {
            $id = (int)($_GET['id'] ?? 0);
            $plan->obrisi($id);
            header('Location: ../planovi.php?uspeh=3');
        }
        break;

    default:
        // nepoznat tip - idi na panel
        header('Location: ../panel.php');
}
exit;
