# MojiRecepti

Aplikacija za upravljanje receptima i obrocima na osnovu MySQL baze.

**Tehnologije:** PHP, MySQL, Bootstrap, OOP  
**Tema:** Aplikacija za prikupljanje recepata sa pratnjom dnevnih obroka i alergena

---

## Brza instalacija

### Korak 1: Baza podataka

U phpMyAdmin kreiraj novu bazu `recepti_app`, pa importuj `sql/recepti_app.sql`.

Ili iz terminala:

```
mysql -u root recepti_app < sql/recepti_app.sql
```

### Korak 2: Konekcija

Otvori `php/klase/Baza.php` i proveri da li su ovi parametri ispravni:

```php
$host     = 'localhost';
$korisnik = 'root';
$sifra    = '';
$baza     = 'recepti_app';
```

### Korak 3: Pokreni

Postavi folder u htdocs i otvori `http://localhost/recepti/`.

---

## Demo nalog

```
Email: admin@recepti.rs
Lozinka: password
```

---

## Kako funkcioniše

### Login i registracija

Korisnik se prvo mora registrovati ili prijaviti. Sesija se čuva u `$_SESSION`. Klasa `Korisnik` upravlja prijavljivanjem i hešovanjem lozinke.

### Recepti

Svaki korisnik može da dodaje svoje recepte. Svaki recept ima:

- Naziv, opis, vreme pripreme/kuvanja
- Listu sastojaka sa količinama
- Listu alergena koji se nalaze u receptu
- Sliku

Pri dodavanju recepta, možeš da dodam više sastojaka odjednom ili da ih dodaš kasnije iz forme za izmenu.

### Sastojci

Lista svih namirnica sa nutritivnim vrednostima (kalorije, proteini, masti, itd.). Koristi se pri kreiranju receptа.

### Alergeni

Lista znanih alergena. Receplu se mogu dodati alergeni kroz formu u edit sekciji.

### Obroci

Praćenje šta je korisnik jeo, kada i koliko porcija. Automatski se računa broj kalorija na osnovu sastojaka u receptu.

### Planovi ishrane

Planovi sa kaloriskim ciljem (npr. 2000 kcal dnevno). Obroci se dodeluju planovima.

---

## Struktura fajlova

```
recepti/
├── index.php           - Login forma
├── register.php        - Registracija forma
├── panel.php           - Početna stranica nakon prijave
├── recepti.php         - Lista, pregled, dodavanje i izmena recepti
├── sastojci.php        - Lista, dodavanje i izmena sastojaka
├── obroci.php          - Lista obroka i dodavanje
├── planovi.php         - Planovi ishrane
├── alergeni.php        - Alergeni (samo za admina)
├── css/style.css       - Stilovi
├── img/                - Slike recepata
├── php/
│   ├── login.php       - Handler za prijavu (POST)
│   ├── logout.php      - Handler za odjavu
│   ├── register.php    - Handler za registraciju (POST)
│   ├── navbar.php      - Deljeni navigation bar
│   ├── akcije.php      - Centralni handler za sve CRUD akcije
│   └── klase/
│       ├── Baza.php           - Konekcija sa bazom (Singleton)
│       ├── ICrud.php          - Interfejs sa metodama: citaj, dodaj, azuriraj, obrisi
│       ├── EntitetBaze.php    - Apstraktna klasa koja implementira ICrud
│       ├── Korisnik.php       - Upravljanje korisnicima i sesijama
│       ├── Recept.php         - CRUD za recepte
│       └── Entiteti.php       - Klase za Alergen, Sastojak, Obrok, PlanIshrane
└── sql/recepti_app.sql - Dump baze sa tabelama i demo podacima
```

---

## OOP i CRUD

### Interfejs ICrud

Definiše 4 osnovna metoda koje sve klase moraju da implementiraju:

```php
interface ICrud {
    public function citaj($id = null);        // Očitaj jednu ili sve zapise
    public function dodaj(array $podaci);     // Dodaj novi zapis
    public function azuriraj(int $id, array $podaci); // Izmeni zapis
    public function obrisi(int $id);          // Obriši zapis
}
```

### Bazna klasa EntitetBaze

Implementira ICrud i sadrži `citaj()` i `obrisi()` koje su iste za sve entitete.

Svaka konkretna klasa (Korisnik, Recept, Sastojak...) nasljeđuje EntitetBaze i implementira svoje `dodaj()` i `azuriraj()` jer imaju različite kolone i tipove podataka.

### Baza podataka

Klasa `Baza` je Singleton - postoji samo jedna instanca u čitavoj aplikaciji. To osigurava da se ne prave multiple konekcije sa bazom.

---


### Sesije

Korisnik se prijavljuje, sesija se startuje i u `$_SESSION` se čuva:

- `korisnik_id` - ID korisnika
- `prijavljen` - true/false

Svaka zaštićena stranica proverava da li je `$_SESSION['prijavljen']` true.

### Prepared statements

Sve SQL naredbe koriste prepared statements (`bind_param`) da bi se sprečili SQL injection napadi.

### Slike

Slike se slažu u `img/` folder. Ako slika ne postoji, prikazuje se `default.jpg`.

Slike se automatski smeštaju sa nazivom prema receptu/sastojku (npr. `jaja.jpg`, `pileca_supa.jpg`).

### Bootstrap

Ceo UI je napravljen sa Bootstrap 5 - kartice, tabele, forme, grid sistem.

---

## Baza podataka

| Tabela           | Sadržaj                                          |
| ---------------- | ------------------------------------------------ |
| Korisnici        | Nalozi, email, heš lozinke                       |
| Recepti          | Naziv, opis, vreme, autor, slika                 |
| Sastojci         | Namirnice sa nutritivnim vrednostima             |
| Alergeni         | Alergenske supstance                             |
| Obroci           | Koje je recepte jede korisnik, kada              |
| PlanoviIshrane   | Planovi sa ciljanim kalorijama                   |
| Recepti_Sastojci | Veza - koji sastojci su u kojem receptu i koliko |
| Recepti_Alergeni | Veza - koji alergeni su u kojem receptu          |
| Plan_Obroci      | Veza - koji obroci su u kojem planu              |
