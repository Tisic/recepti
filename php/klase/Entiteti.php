<?php

require_once __DIR__ . '/EntitetBaze.php';

// ============================================================
// Klasa za alergene - najjednostavnija od svih
// nasleduje EntitetBaze, samo treba da implementira dodaj i azuriraj
// ============================================================

class Alergen extends EntitetBaze {

    public function __construct() {
        parent::__construct();
        $this->tabela        = 'Alergeni';
        $this->primarniKljuc = 'AlergenID';
    }

    public function dodaj(array $podaci): bool {
        $stmt = $this->baza->pripremi("INSERT INTO Alergeni (Naziv, Opis) VALUES (?, ?)");
        if (!$stmt) return false;
        $stmt->bind_param('ss', $podaci['naziv'], $podaci['opis']);
        $rez = $stmt->execute();
        $stmt->close();
        return $rez;
    }

    public function azuriraj(int $id, array $podaci): bool {
        $stmt = $this->baza->pripremi("UPDATE Alergeni SET Naziv=?, Opis=? WHERE AlergenID=?");
        if (!$stmt) return false;
        $stmt->bind_param('ssi', $podaci['naziv'], $podaci['opis'], $id);
        $rez = $stmt->execute();
        $stmt->close();
        return $rez;
    }
}

// ============================================================
// Klasa za sastojke sa nutritivnim vrednostima
// ============================================================

class Sastojak extends EntitetBaze {

    public function __construct() {
        parent::__construct();
        $this->tabela        = 'Sastojci';
        $this->primarniKljuc = 'SastojakID';

        // Ako kolona za sliku ne postoji, dodaj je automatski.
        if (!$this->baza->imaKolonu($this->tabela, 'Slika')) {
            $this->baza->query("ALTER TABLE {$this->tabela} ADD COLUMN Slika VARCHAR(255) DEFAULT 'default.jpg'");
        }
    }

    public function dodaj(array $podaci): bool {
        $hasSlika = $this->baza->imaKolonu($this->tabela, 'Slika');
        if ($hasSlika) {
            $stmt = $this->baza->pripremi(
                "INSERT INTO Sastojci (Naziv, Kalorije, Proteini, Ugljeni_hidrati, Masti, Vlakna, Jedinica, Slika)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
            );
        } else {
            $stmt = $this->baza->pripremi(
                "INSERT INTO Sastojci (Naziv, Kalorije, Proteini, Ugljeni_hidrati, Masti, Vlakna, Jedinica)
                 VALUES (?, ?, ?, ?, ?, ?, ?)"
            );
        }
        if (!$stmt) return false;

        if ($hasSlika) {
            $slika = $podaci['slika'] ?? 'default.jpg';
            $stmt->bind_param('sdddddss',
                $podaci['naziv'],
                $podaci['kalorije'],
                $podaci['proteini'],
                $podaci['ugljeni_hidrati'],
                $podaci['masti'],
                $podaci['vlakna'],
                $podaci['jedinica'],
                $slika
            );
        } else {
            $stmt->bind_param('sddddds',
                $podaci['naziv'],
                $podaci['kalorije'],
                $podaci['proteini'],
                $podaci['ugljeni_hidrati'],
                $podaci['masti'],
                $podaci['vlakna'],
                $podaci['jedinica']
            );
        }

        $rez = $stmt->execute();
        $stmt->close();
        return $rez;
    }

    public function azuriraj(int $id, array $podaci): bool {
        $hasSlika = $this->baza->imaKolonu($this->tabela, 'Slika');
        $sql = "UPDATE Sastojci SET Naziv=?, Kalorije=?, Proteini=?, Ugljeni_hidrati=?, Masti=?, Vlakna=?, Jedinica=?";

        if ($hasSlika && isset($podaci['slika'])) {
            $sql .= ", Slika=?";
        }

        $sql .= " WHERE SastojakID=?";
        $stmt = $this->baza->pripremi($sql);
        if (!$stmt) return false;

        if ($hasSlika && isset($podaci['slika'])) {
            $stmt->bind_param('sdddddssi',
                $podaci['naziv'],
                $podaci['kalorije'],
                $podaci['proteini'],
                $podaci['ugljeni_hidrati'],
                $podaci['masti'],
                $podaci['vlakna'],
                $podaci['jedinica'],
                $podaci['slika'],
                $id
            );
        } else {
            $stmt->bind_param('sdddddsi',
                $podaci['naziv'],
                $podaci['kalorije'],
                $podaci['proteini'],
                $podaci['ugljeni_hidrati'],
                $podaci['masti'],
                $podaci['vlakna'],
                $podaci['jedinica'],
                $id
            );
        }

        $rez = $stmt->execute();
        $stmt->close();
        return $rez;
    }

    // pretraga sastojaka po imenu - za autocomplete formu
    public function pretrazi(string $termin): array {
        $t   = $this->baza->esc($termin);
        $rez = $this->baza->query("SELECT * FROM Sastojci WHERE Naziv LIKE '%$t%' ORDER BY Naziv");
        if (!$rez) return [];
        $podaci = [];
        while ($red = $rez->fetch_assoc()) $podaci[] = $red;
        return $podaci;
    }
}

// ============================================================
// Klasa za obrока (sta si jeo i kada)
// ============================================================

class Obrok extends EntitetBaze {

    public function __construct() {
        parent::__construct();
        $this->tabela        = 'Obroci';
        $this->primarniKljuc = 'ObrokID';
    }

    public function dodaj(array $podaci): bool {
        $stmt = $this->baza->pripremi(
            "INSERT INTO Obroci (KorisnikID, ReceptID, DatumObroka, VrstaObroka, BrojPorcija, Napomena)
             VALUES (?, ?, ?, ?, ?, ?)"
        );
        if (!$stmt) return false;
        $stmt->bind_param('issdss',
            $podaci['korisnik_id'],
            $podaci['recept_id'],
            $podaci['datum'],
            $podaci['vrsta'],
            $podaci['porcije'],
            $podaci['napomena']
        );
        $rez = $stmt->execute();
        $stmt->close();
        return $rez;
    }

    public function azuriraj(int $id, array $podaci): bool {
        $stmt = $this->baza->pripremi(
            "UPDATE Obroci SET ReceptID=?, DatumObroka=?, VrstaObroka=?, BrojPorcija=?, Napomena=?
             WHERE ObrokID=?"
        );
        if (!$stmt) return false;
        $stmt->bind_param('issdsi', // i=recept_id, s=datum, s=vrsta, d=porcije, s=napomena, i=id
            $podaci['recept_id'],
            $podaci['datum'],
            $podaci['vrsta'],
            $podaci['porcije'],
            $podaci['napomena'],
            $id
        );
        $rez = $stmt->execute();
        $stmt->close();
        return $rez;
    }

    // uzima obroka za određenog korisnika sa detaljima recepta
    public function citajZaKorisnika(int $korisnikId): array {
        $id  = (int)$korisnikId;
        $rez = $this->baza->query(
            "SELECT o.*, r.Naziv as NazivRecepta,
                    ROUND(((SELECT SUM((s.Kalorije/100)*rs.Kolicina)
                            FROM Recepti_Sastojci rs JOIN Sastojci s ON rs.SastojakID=s.SastojakID
                            WHERE rs.ReceptID=r.ReceptID) * o.BrojPorcija), 0) as UkupnoKalorija
             FROM Obroci o
             LEFT JOIN Recepti r ON o.ReceptID = r.ReceptID
             WHERE o.KorisnikID = $id
             ORDER BY o.DatumObroka DESC, o.VrstaObroka"
        );
        if (!$rez) return [];
        $podaci = [];
        while ($red = $rez->fetch_assoc()) $podaci[] = $red;
        return $podaci;
    }
}

// ============================================================
// Klasa za planove ishrane
// ============================================================

class PlanIshrane extends EntitetBaze {

    public function __construct() {
        parent::__construct();
        $this->tabela        = 'PlanoviIshrane';
        $this->primarniKljuc = 'PlanID';
    }

    public function dodaj(array $podaci): bool {
        $stmt = $this->baza->pripremi(
            "INSERT INTO PlanoviIshrane (KorisnikID, Naziv, Opis, DatumPocetka, DatumZavrsetka, CiljKalorija)
             VALUES (?, ?, ?, ?, ?, ?)"
        );
        if (!$stmt) return false;
        $stmt->bind_param('issssi',
            $podaci['korisnik_id'],
            $podaci['naziv'],
            $podaci['opis'],
            $podaci['datum_pocetka'],
            $podaci['datum_zavrsetka'],
            $podaci['cilj_kalorija']
        );
        $rez = $stmt->execute();
        $stmt->close();
        return $rez;
    }

    public function azuriraj(int $id, array $podaci): bool {
        $stmt = $this->baza->pripremi(
            "UPDATE PlanoviIshrane SET Naziv=?, Opis=?, DatumPocetka=?, DatumZavrsetka=?, CiljKalorija=?
             WHERE PlanID=?"
        );
        if (!$stmt) return false;
        $stmt->bind_param('ssssii', // s=naziv, s=opis, s=datum_poc, s=datum_zav, i=cilj_kcal, i=id
            $podaci['naziv'],
            $podaci['opis'],
            $podaci['datum_pocetka'],
            $podaci['datum_zavrsetka'],
            $podaci['cilj_kalorija'],
            $id
        );
        $rez = $stmt->execute();
        $stmt->close();
        return $rez;
    }

    // uzima planove za konkretnog korisnika
    public function citajZaKorisnika(int $korisnikId): array {
        $id  = (int)$korisnikId;
        $rez = $this->baza->query(
            "SELECT * FROM PlanoviIshrane WHERE KorisnikID = $id ORDER BY DatumPocetka DESC"
        );
        if (!$rez) return [];
        $podaci = [];
        while ($red = $rez->fetch_assoc()) $podaci[] = $red;
        return $podaci;
    }
}
