<?php

require_once __DIR__ . '/EntitetBaze.php';

// ============================================================
// Klasa za upravljanje korisnicima i sesijama
// Nasleduje EntitetBaze i dodaje metode za login/logout/registraciju
// ============================================================

class Korisnik extends EntitetBaze {

    public function __construct() {
        parent::__construct();
        $this->tabela        = 'Korisnici';
        $this->primarniKljuc = 'KorisnikID';
    }

    // ============================================================
    // Registracija novog korisnika
    // ============================================================
    public function dodaj(array $podaci): bool {
        // password_hash brine o sigurnosti - nikad ne cuvas plain text lozinku
        $hash = password_hash($podaci['lozinka'], PASSWORD_DEFAULT);

        $stmt = $this->baza->pripremi(
            "INSERT INTO Korisnici (Ime, Prezime, Email, Lozinka) VALUES (?, ?, ?, ?)"
        );
        if (!$stmt) return false;

        $stmt->bind_param('ssss',
            $podaci['ime'],
            $podaci['prezime'],
            $podaci['email'],
            $hash
        );
        $rez = $stmt->execute();
        $stmt->close();
        return $rez;
    }

    // ============================================================
    // Azuriranje podataka korisnika
    // ============================================================
    public function azuriraj(int $id, array $podaci): bool {
        $stmt = $this->baza->pripremi(
            "UPDATE Korisnici SET Ime=?, Prezime=?, Email=? WHERE KorisnikID=?"
        );
        if (!$stmt) return false;

        $stmt->bind_param('sssi',
            $podaci['ime'],
            $podaci['prezime'],
            $podaci['email'],
            $id
        );
        $rez = $stmt->execute();
        $stmt->close();
        return $rez;
    }

    // ============================================================
    // Prijava korisnika - proverava email i lozinku
    // ============================================================
    public function prijavi(string $email, string $lozinka): bool {
        $stmt = $this->baza->pripremi(
            "SELECT * FROM Korisnici WHERE Email = ? LIMIT 1"
        );
        if (!$stmt) return false;

        $stmt->bind_param('s', $email);
        $stmt->execute();
        $rez  = $stmt->get_result();
        $stmt->close();

        if ($rez->num_rows === 0) return false; // email ne postoji

        $korisnik = $rez->fetch_assoc();

        // password_verify poredi unetu lozinku sa hashom iz baze
        if (!password_verify($lozinka, $korisnik['Lozinka'])) return false;

        // sve ok - pokrenemo sesiju i upisemo podatke korisnika
        if (session_status() === PHP_SESSION_NONE) session_start();
        $_SESSION['korisnik_id']  = $korisnik['KorisnikID'];
        $_SESSION['korisnik_ime'] = $korisnik['Ime'];
        $_SESSION['korisnik_email'] = $korisnik['Email'];
        $_SESSION['prijavljen']   = true;

        return true;
    }

    // ============================================================
    // Odjava korisnika - brisemo sesiju
    // ============================================================
    public function odjavi(): void {
        if (session_status() === PHP_SESSION_NONE) session_start();
        session_unset();
        session_destroy();
    }

    // ============================================================
    // Proverava da li je korisnik prijavljen - koristi se na svakoj zastecenoj stranici
    // ============================================================
    public static function jePrijavljen(): bool {
        if (session_status() === PHP_SESSION_NONE) session_start();
        return isset($_SESSION['prijavljen']) && $_SESSION['prijavljen'] === true;
    }

    // ============================================================
    // Zastita stranice - ako nije prijavljen, preusmeri na login
    // ============================================================
    public static function zahtevajPrijavu(): void {
        if (!self::jePrijavljen()) {
            header('Location: ../index.php?greska=2');
            exit;
        }
    }

    // proverava da li email vec postoji u bazi (za registraciju)
    public function emailPostoji(string $email): bool {
        $stmt = $this->baza->pripremi("SELECT KorisnikID FROM Korisnici WHERE Email = ?");
        if (!$stmt) return false;
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $rez = $stmt->get_result();
        $stmt->close();
        return $rez->num_rows > 0;
    }
}
