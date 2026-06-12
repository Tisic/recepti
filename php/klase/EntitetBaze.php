<?php

require_once __DIR__ . '/Baza.php';
require_once __DIR__ . '/ICrud.php';

// ============================================================
// Apstraktna klasa - osnova za sve entitete (Recept, Alergen, Sastojak...)
// Implementira ICrud interfejs i pruza zajednicki kod svim naslednicima
// ============================================================

abstract class EntitetBaze implements ICrud {

    protected Baza $baza;         // konekcija ka bazi
    protected string $tabela;     // naziv tabele u bazi (npr. "Recepti")
    protected string $primarniKljuc; // naziv PK kolone (npr. "ReceptID")

    public function __construct() {
        // uzimamo singleton instancu baze - ne pravimo novu konekciju svaki put
        $this->baza = Baza::getInstanca();
    }

    // ============================================================
    // Osnovna implementacija citanja - naslednici mogu da je prepisu
    // ============================================================
    public function citaj($id = null): array {
        if ($id !== null) {
            // citamo konkretni red po ID-u
            $id = (int)$id;
            $rez = $this->baza->query("SELECT * FROM {$this->tabela} WHERE {$this->primarniKljuc} = $id");
        } else {
            // citamo sve redove iz tabele
            $rez = $this->baza->query("SELECT * FROM {$this->tabela}");
        }

        if (!$rez) return [];

        $podaci = [];
        while ($red = $rez->fetch_assoc()) {
            $podaci[] = $red;
        }
        return $podaci;
    }

    // ============================================================
    // Osnovna implementacija brisanja - radi za sve tabele
    // ============================================================
    public function obrisi(int $id): bool {
        $id   = (int)$id;
        $stmt = $this->baza->pripremi("DELETE FROM {$this->tabela} WHERE {$this->primarniKljuc} = ?");
        if (!$stmt) return false;
        $stmt->bind_param('i', $id);
        $rezultat = $stmt->execute();
        $stmt->close();
        return $rezultat;
    }

    // dodaj() i azuriraj() su apstraktne - svaki entitet ih implementira sam
    // jer ima razlicite kolone i tipove podataka
    abstract public function dodaj(array $podaci): bool;
    abstract public function azuriraj(int $id, array $podaci): bool;

    // helper - vraca broj redova u tabeli (korisno za dashboard)
    public function broji(): int {
        $rez = $this->baza->query("SELECT COUNT(*) as ukupno FROM {$this->tabela}");
        if (!$rez) return 0;
        $red = $rez->fetch_assoc();
        return (int)$red['ukupno'];
    }

    public function poslednjiID(): int {
        return $this->baza->poslednjiID();
    }
}
