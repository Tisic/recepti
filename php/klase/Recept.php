<?php

require_once __DIR__ . '/EntitetBaze.php';

// ============================================================
// Klasa za upravljanje receptima
// Nasleduje EntitetBaze - dobija citaj() i obrisi() besplatno
// ============================================================

class Recept extends EntitetBaze {

    public function __construct() {
        parent::__construct();
        $this->tabela        = 'Recepti';
        $this->primarniKljuc = 'ReceptID';
    }

    // ============================================================
    // Dodavanje novog recepta u bazu
    // ============================================================
    public function dodaj(array $podaci): bool {
        $stmt = $this->baza->pripremi(
            "INSERT INTO Recepti (KorisnikID, Naziv, Opis, VremePripreme, VremePecenja, BrojPorcija, Kategorija, Tezina, Slika)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        if (!$stmt) return false;

        $slika = $podaci['slika'] ?? 'default.jpg'; // ako ne postavljaju sliku, koristi defaultnu

        $stmt->bind_param('issiiisss',
            $podaci['korisnik_id'],
            $podaci['naziv'],
            $podaci['opis'],
            $podaci['vreme_pripreme'],
            $podaci['vreme_pecenja'],
            $podaci['broj_porcija'],
            $podaci['kategorija'],
            $podaci['tezina'],
            $slika
        );
        $rez = $stmt->execute();
        $stmt->close();
        return $rez;
    }

    // ============================================================
    // Azuriranje podataka recepta
    // ============================================================
    public function azuriraj(int $id, array $podaci): bool {
        $stmt = $this->baza->pripremi(
            "UPDATE Recepti SET Naziv=?, Opis=?, VremePripreme=?, VremePecenja=?, BrojPorcija=?, Kategorija=?, Tezina=?
             WHERE ReceptID=?"
        );
        if (!$stmt) return false;

        $stmt->bind_param('ssiiissi',
            $podaci['naziv'],
            $podaci['opis'],
            $podaci['vreme_pripreme'],
            $podaci['vreme_pecenja'],
            $podaci['broj_porcija'],
            $podaci['kategorija'],
            $podaci['tezina'],
            $id
        );
        $rez = $stmt->execute();
        $stmt->close();
        return $rez;
    }

    public function dodajSastojak(int $receptId, int $sastojakId, float $kolicina): bool {
        $stmt = $this->baza->pripremi(
            "INSERT INTO Recepti_Sastojci (ReceptID, SastojakID, Kolicina) VALUES (?, ?, ?)"
        );
        if (!$stmt) return false;
        $stmt->bind_param('iid', $receptId, $sastojakId, $kolicina);
        $rez = $stmt->execute();
        $stmt->close();
        return $rez;
    }

    public function obrisiSastojak(int $receptId, int $sastojakId): bool {
        $stmt = $this->baza->pripremi(
            "DELETE FROM Recepti_Sastojci WHERE ReceptID = ? AND SastojakID = ?"
        );
        if (!$stmt) return false;
        $stmt->bind_param('ii', $receptId, $sastojakId);
        $rez = $stmt->execute();
        $stmt->close();
        return $rez;
    }

    public function dodajAlergen(int $receptId, int $alergenId): bool {
        $stmt = $this->baza->pripremi(
            "INSERT IGNORE INTO Recepti_Alergeni (ReceptID, AlergenID) VALUES (?, ?)"
        );
        if (!$stmt) return false;
        $stmt->bind_param('ii', $receptId, $alergenId);
        $rez = $stmt->execute();
        $stmt->close();
        return $rez;
    }

    public function obrisiAlergen(int $receptId, int $alergenId): bool {
        $stmt = $this->baza->pripremi(
            "DELETE FROM Recepti_Alergeni WHERE ReceptID = ? AND AlergenID = ?"
        );
        if (!$stmt) return false;
        $stmt->bind_param('ii', $receptId, $alergenId);
        $rez = $stmt->execute();
        $stmt->close();
        return $rez;
    }

    // ============================================================
    // Ucitava recepte sa filtriranjem po kategoriji
    // ============================================================
    public function citajPoKategoriji(string $kategorija): array {
        $kat = $this->baza->esc($kategorija);
        $rez = $this->baza->query(
            "SELECT r.*, k.Ime, k.Prezime FROM Recepti r
             LEFT JOIN Korisnici k ON r.KorisnikID = k.KorisnikID
             WHERE r.Kategorija = '$kat'
             ORDER BY r.DatumDodavanja DESC"
        );
        if (!$rez) return [];
        $podaci = [];
        while ($red = $rez->fetch_assoc()) $podaci[] = $red;
        return $podaci;
    }

    // ============================================================
    // Ucitava sve recepte zajedno sa imenom autora (JOIN sa Korisnici)
    // ============================================================
    public function citajSaAutorom(): array {
        $rez = $this->baza->query(
            "SELECT r.*, k.Ime, k.Prezime FROM Recepti r
             LEFT JOIN Korisnici k ON r.KorisnikID = k.KorisnikID
             ORDER BY r.DatumDodavanja DESC"
        );
        if (!$rez) return [];
        $podaci = [];
        while ($red = $rez->fetch_assoc()) $podaci[] = $red;
        return $podaci;
    }

    // ============================================================
    // Racuna ukupne kalorije recepta na osnovu sastojaka
    // ============================================================
    public function izracunajKalorije(int $receptId): float {
        $id  = (int)$receptId;
        $rez = $this->baza->query(
            "SELECT SUM((s.Kalorije / 100) * rs.Kolicina) as ukupno
             FROM Recepti_Sastojci rs
             JOIN Sastojci s ON rs.SastojakID = s.SastojakID
             WHERE rs.ReceptID = $id"
        );
        if (!$rez) return 0;
        $red = $rez->fetch_assoc();
        return round((float)($red['ukupno'] ?? 0), 1);
    }

    // ============================================================
    // Vraca sve sastojke za jedan recept sa nutritivnim vrednostima
    // ============================================================
    public function getSastojke(int $receptId): array {
        $id  = (int)$receptId;
        $rez = $this->baza->query(
            "SELECT s.*, rs.Kolicina,
                    ROUND((s.Kalorije / 100) * rs.Kolicina, 1) as KalorijeUkupno,
                    ROUND((s.Proteini / 100) * rs.Kolicina, 1) as ProteiniUkupno,
                    ROUND((s.Masti / 100) * rs.Kolicina, 1) as MastiUkupno,
                    ROUND((s.Ugljeni_hidrati / 100) * rs.Kolicina, 1) as UHUkupno,
                    ROUND((s.Vlakna / 100) * rs.Kolicina, 1) as VlaknaUkupno
             FROM Recepti_Sastojci rs
             JOIN Sastojci s ON rs.SastojakID = s.SastojakID
             WHERE rs.ReceptID = $id"
        );
        if (!$rez) return [];
        $podaci = [];
        while ($red = $rez->fetch_assoc()) $podaci[] = $red;
        return $podaci;
    }

    // vraca alergene za dati recept
    public function getAlergene(int $receptId): array {
        $id  = (int)$receptId;
        $rez = $this->baza->query(
            "SELECT a.* FROM Recepti_Alergeni ra
             JOIN Alergeni a ON ra.AlergenID = a.AlergenID
             WHERE ra.ReceptID = $id"
        );
        if (!$rez) return [];
        $podaci = [];
        while ($red = $rez->fetch_assoc()) $podaci[] = $red;
        return $podaci;
    }
}
