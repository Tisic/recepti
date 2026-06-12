<?php

// ============================================================
// Klasa za konekciju sa bazom podataka
// Koristimo singleton pattern - samo jedna konekcija postoji
// ============================================================

class Baza {
    private static ?Baza $instanca = null; // jedina instanca klase
    private mysqli $konekcija;             // mysqli objekat za vezu

    // privatni konstruktor - ne moze da se pozove spolja (to je poenta singletona)
    private function __construct() {
        $host     = 'localhost';
        $korisnik = 'root';       // izmeni po potrebi
        $sifra    = '';           // izmeni po potrebi
        $baza     = 'recepti_app';

        $this->konekcija = new mysqli($host, $korisnik, $sifra, $baza);
        $this->konekcija->set_charset('utf8mb4');

        // ako konekcija nije uspela - prijavi gresku i zaustavi sve
        if ($this->konekcija->connect_error) {
            die('<div class="alert alert-danger m-3">Greška pri konekciji sa bazom: ' . $this->konekcija->connect_error . '</div>');
        }
    }

    // ova metoda vraca jedinu instancu klase Baza
    public static function getInstanca(): Baza {
        if (self::$instanca === null) {
            self::$instanca = new Baza();
        }
        return self::$instanca;
    }

    // vraca mysqli konekciju da ostale klase mogu da je koriste
    public function getKonekcija(): mysqli {
        return $this->konekcija;
    }

    // helper - izvrsava query i vraca rezultat, ili false ako je greska
    public function query(string $sql): mysqli_result|bool {
        return $this->konekcija->query($sql);
    }

    // helper - priprema prepared statement (zastitа od SQL injection-a)
    public function pripremi(string $sql): mysqli_stmt|false {
        return $this->konekcija->prepare($sql);
    }

    // vraca ID poslednjeg insertovanog reda - korisno posle dodavanja
    public function poslednjiID(): int {
        return $this->konekcija->insert_id;
    }

    // escape-uje string pre unosa u bazu - dodatna zastita
    public function esc(string $vrednost): string {
        return $this->konekcija->real_escape_string($vrednost);
    }
    // helper - proverava da li kolona postoji u tabeli
    public function imaKolonu(string $tabela, string $kolona): bool {
        $stmt = $this->konekcija->prepare(
            "SELECT 1 FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = ? AND column_name = ? LIMIT 1"
        );
        if (!$stmt) {
            return false;
        }
        $stmt->bind_param('ss', $tabela, $kolona);
        $stmt->execute();
        $stmt->store_result();
        $exists = $stmt->num_rows > 0;
        $stmt->close();
        return $exists;
    }}
