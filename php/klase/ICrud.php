<?php

// ============================================================
// Interfejs koji definise sve CRUD operacije
// Svaka klasa koja radi sa bazom MORA da implementira ovo
// ============================================================

interface ICrud {
    // ucitaj sve zapise (ili jedan po ID-u ako se prosledi)
    public function citaj($id = null);

    // dodaj novi zapis u bazu
    public function dodaj(array $podaci): bool;

    // azuriraj postojeci zapis
    public function azuriraj(int $id, array $podaci): bool;

    // obrisi zapis po ID-u
    public function obrisi(int $id): bool;
}
