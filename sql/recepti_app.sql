-- ============================================================
-- Baza podataka: Aplikacija za upravljanje receptima i obrocima
-- Kreiraj bazu pa onda importuj ovaj fajl
-- ============================================================

CREATE DATABASE IF NOT EXISTS recepti_app CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE recepti_app;

-- ============================================================
-- Tabela korisnika (za login i registraciju)
-- ============================================================
CREATE TABLE IF NOT EXISTS Korisnici (
    KorisnikID INT AUTO_INCREMENT PRIMARY KEY,
    Ime VARCHAR(100) NOT NULL,
    Prezime VARCHAR(100) NOT NULL,
    Email VARCHAR(150) NOT NULL UNIQUE,
    Lozinka VARCHAR(255) NOT NULL,          -- čuvaćemo hash, ne plain text
    DatumRegistracije DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- Alergeni (jaje, gluten, laktoza itd.)
-- ============================================================
CREATE TABLE IF NOT EXISTS Alergeni (
    AlergenID INT AUTO_INCREMENT PRIMARY KEY,
    Naziv VARCHAR(100) NOT NULL,
    Opis TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- Sastojci sa nutritivnim vrednostima (na 100g)
-- ============================================================
CREATE TABLE IF NOT EXISTS Sastojci (
    SastojakID INT AUTO_INCREMENT PRIMARY KEY,
    Naziv VARCHAR(150) NOT NULL,
    Kalorije DECIMAL(8,2) DEFAULT 0,        -- kcal na 100g
    Proteini DECIMAL(8,2) DEFAULT 0,        -- g na 100g
    Ugljeni_hidrati DECIMAL(8,2) DEFAULT 0, -- g na 100g
    Masti DECIMAL(8,2) DEFAULT 0,           -- g na 100g
    Vlakna DECIMAL(8,2) DEFAULT 0,          -- g na 100g
    Jedinica VARCHAR(20) DEFAULT 'g',       -- g, ml, kom...
    Slika VARCHAR(255) DEFAULT 'default.jpg'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- Recepti
-- ============================================================
CREATE TABLE IF NOT EXISTS Recepti (
    ReceptID INT AUTO_INCREMENT PRIMARY KEY,
    KorisnikID INT,                          -- ko je dodao recept
    Naziv VARCHAR(200) NOT NULL,
    Opis TEXT,
    VremePripreme INT DEFAULT 0,              -- vreme pripreme u minutima
    VremePecenja INT DEFAULT 0,             -- vreme kuvanja/pecenja u minutima
    BrojPorcija INT DEFAULT 1,
    Kategorija VARCHAR(100),                -- doručak, ručak, večera, užina
    Tezina VARCHAR(50),                     -- lako, srednje, teško
    Slika VARCHAR(255) DEFAULT 'default.jpg',
    DatumDodavanja DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (KorisnikID) REFERENCES Korisnici(KorisnikID) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- Veza recept <-> sastojak (količina sastojka u receptu)
-- ============================================================
CREATE TABLE IF NOT EXISTS Recepti_Sastojci (
    ID INT AUTO_INCREMENT PRIMARY KEY,
    ReceptID INT NOT NULL,
    SastojakID INT NOT NULL,
    Kolicina DECIMAL(8,2) NOT NULL,         -- koliko grama/ml/kom
    FOREIGN KEY (ReceptID) REFERENCES Recepti(ReceptID) ON DELETE CASCADE,
    FOREIGN KEY (SastojakID) REFERENCES Sastojci(SastojakID) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- Veza recept <-> alergen
-- ============================================================
CREATE TABLE IF NOT EXISTS Recepti_Alergeni (
    ID INT AUTO_INCREMENT PRIMARY KEY,
    ReceptID INT NOT NULL,
    AlergenID INT NOT NULL,
    FOREIGN KEY (ReceptID) REFERENCES Recepti(ReceptID) ON DELETE CASCADE,
    FOREIGN KEY (AlergenID) REFERENCES Alergeni(AlergenID) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- Obroci (konkretni obroci za određen dan)
-- ============================================================
CREATE TABLE IF NOT EXISTS Obroci (
    ObrokID INT AUTO_INCREMENT PRIMARY KEY,
    KorisnikID INT,
    ReceptID INT,
    DatumObroka DATE NOT NULL,
    VrstaObroka VARCHAR(50),                -- doručak, ručak, večera, užina
    BrojPorcija DECIMAL(4,1) DEFAULT 1,
    Napomena TEXT,
    FOREIGN KEY (KorisnikID) REFERENCES Korisnici(KorisnikID) ON DELETE CASCADE,
    FOREIGN KEY (ReceptID) REFERENCES Recepti(ReceptID) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- Planovi ishrane (npr. "Dijeta za maj", "Bulk faza")
-- ============================================================
CREATE TABLE IF NOT EXISTS PlanoviIshrane (
    PlanID INT AUTO_INCREMENT PRIMARY KEY,
    KorisnikID INT,
    Naziv VARCHAR(200) NOT NULL,
    Opis TEXT,
    DatumPocetka DATE,
    DatumZavrsetka DATE,
    CiljKalorija INT DEFAULT 2000,          -- dnevni cilj kcal
    FOREIGN KEY (KorisnikID) REFERENCES Korisnici(KorisnikID) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- Veza plan ishrane <-> obrok
-- ============================================================
CREATE TABLE IF NOT EXISTS Plan_Obroci (
    ID INT AUTO_INCREMENT PRIMARY KEY,
    PlanID INT NOT NULL,
    ObrokID INT NOT NULL,
    FOREIGN KEY (PlanID) REFERENCES PlanoviIshrane(PlanID) ON DELETE CASCADE,
    FOREIGN KEY (ObrokID) REFERENCES Obroci(ObrokID) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================================
-- PODACI - Alergeni
-- ============================================================
INSERT INTO Alergeni (Naziv, Opis) VALUES
('Gluten', 'Prisutan u pšenici, ječmu, raži i ovsu'),
('Laktoza', 'Mlečni šećer prisutan u mleku i mlečnim proizvodima'),
('Jaja', 'Alergija na belance ili žumance jajeta'),
('Kikiriki', 'Jedan od najčešćih i najopasnijih alergena'),
('Orasi i lešnici', 'Razne vrste orašastih plodova'),
('Riba', 'Alergija na sve vrste riba'),
('Školjke', 'Škampi, jastog, kamenice i sl.'),
('Soja', 'Prisutna u mnogim prerađenim prehrambenim proizvodima'),
('Sezam', 'Prisutan u humusu, tahini pasti i nekim hlebovima'),
('Gorušica/Senf', 'Prisutna u začinima i sosovima');

-- ============================================================
-- PODACI - Sastojci (nutritivne vrednosti na 100g)
-- ============================================================
INSERT INTO Sastojci (Naziv, Kalorije, Proteini, Ugljeni_hidrati, Masti, Vlakna, Jedinica) VALUES
-- žitarice i testenine
('Pšenično brašno tip 400', 364, 10.3, 76.3, 1.0, 2.7, 'g'),
('Integralno brašno', 340, 13.2, 72.0, 2.5, 10.7, 'g'),
('Beli pirinač', 365, 7.1, 79.3, 0.7, 1.3, 'g'),
('Integralni pirinač', 370, 7.9, 77.2, 2.9, 3.5, 'g'),
('Pasta (suva)', 357, 12.5, 71.0, 1.5, 2.7, 'g'),
('Zob (ovsene pahuljice)', 389, 16.9, 66.3, 6.9, 10.6, 'g'),
('Hleb beli', 265, 9.0, 49.0, 3.2, 2.7, 'g'),
('Hleb integralni', 247, 12.5, 41.4, 3.4, 6.0, 'g'),

-- meso i riba
('Pileća prsa (sirova)', 110, 23.1, 0.0, 1.2, 0.0, 'g'),
('Pileće bedro (sirovo)', 177, 18.3, 0.0, 11.4, 0.0, 'g'),
('Juneći but (sirovi)', 158, 20.6, 0.0, 8.4, 0.0, 'g'),
('Svinjski kare (sirovi)', 242, 16.9, 0.0, 19.2, 0.0, 'g'),
('Losos (sirovi)', 208, 20.4, 0.0, 13.4, 0.0, 'g'),
('Tunjevina u vodi (konzerva)', 116, 25.5, 0.0, 1.0, 0.0, 'g'),
('Sardine u ulju', 208, 24.6, 0.0, 11.5, 0.0, 'g'),

-- mlečni proizvodi
('Mleko 2.8% masti', 50, 3.4, 4.8, 2.0, 0.0, 'ml'),
('Jogurt 2%', 56, 3.5, 5.1, 2.0, 0.0, 'g'),
('Grčki jogurt', 59, 10.3, 3.6, 0.4, 0.0, 'g'),
('Sir gauda', 356, 25.0, 2.2, 27.4, 0.0, 'g'),
('Sir beli (feta)', 264, 14.2, 4.1, 21.3, 0.0, 'g'),
('Kačkavalj', 387, 24.9, 1.3, 31.8, 0.0, 'g'),
('Svež sir (urda)', 105, 11.1, 3.4, 4.9, 0.0, 'g'),
('Pavlaka 20%', 195, 2.7, 3.4, 19.3, 0.0, 'g'),

-- jaja i mahunarke
('Jaje (celo)', 155, 13.0, 1.1, 11.0, 0.0, 'kom'),
('Pasulj (suv)', 337, 22.5, 61.3, 1.2, 15.2, 'g'),
('Sočivo (suvo)', 353, 25.8, 60.1, 1.1, 10.7, 'g'),
('Leblebija', 364, 19.3, 60.7, 6.0, 17.4, 'g'),

-- povrće
('Paradajz', 18, 0.9, 3.9, 0.2, 1.2, 'g'),
('Krastavac', 15, 0.6, 3.6, 0.1, 0.5, 'g'),
('Paprika (crvena)', 31, 1.0, 6.0, 0.3, 2.1, 'g'),
('Šargarepa', 41, 0.9, 9.6, 0.2, 2.8, 'g'),
('Krompir', 77, 2.0, 17.5, 0.1, 2.2, 'g'),
('Beli luk', 149, 6.4, 33.1, 0.5, 2.1, 'g'),
('Luk crni', 40, 1.1, 9.3, 0.1, 1.7, 'g'),
('Brokoli', 34, 2.8, 6.6, 0.4, 2.6, 'g'),
('Spanać', 23, 2.9, 3.6, 0.4, 2.2, 'g'),
('Tikvice', 17, 1.2, 3.1, 0.3, 1.0, 'g'),
('Pečurke', 22, 3.1, 3.3, 0.3, 1.0, 'g'),
('Kelj', 50, 3.3, 10.0, 0.7, 2.0, 'g'),
('Patlidžan', 25, 1.0, 5.9, 0.2, 3.0, 'g'),

-- voće
('Jabuka', 52, 0.3, 13.8, 0.2, 2.4, 'g'),
('Banana', 89, 1.1, 22.8, 0.3, 2.6, 'g'),
('Jagoda', 32, 0.7, 7.7, 0.3, 2.0, 'g'),
('Borovnica', 57, 0.7, 14.5, 0.3, 2.4, 'g'),
('Narandža', 47, 0.9, 11.8, 0.1, 2.4, 'g'),
('Limun', 29, 1.1, 9.3, 0.3, 2.8, 'g'),
('Avokado', 160, 2.0, 8.5, 14.7, 6.7, 'g'),

-- orasi i semenke
('Orasi', 654, 15.2, 13.7, 65.2, 6.7, 'g'),
('Badem', 579, 21.2, 21.6, 49.9, 12.5, 'g'),
('Suncokretove semenke', 584, 20.8, 20.0, 51.5, 8.6, 'g'),
('Susam', 573, 17.7, 23.4, 49.7, 11.8, 'g'),

-- ulja i masti
('Maslinovo ulje', 884, 0.0, 0.0, 100.0, 0.0, 'ml'),
('Suncokretovo ulje', 884, 0.0, 0.0, 100.0, 0.0, 'ml'),
('Puter', 717, 0.9, 0.1, 81.1, 0.0, 'g'),

-- ostalo
('Šećer beli', 387, 0.0, 100.0, 0.0, 0.0, 'g'),
('Med', 304, 0.3, 82.4, 0.0, 0.2, 'g'),
('So', 0, 0.0, 0.0, 0.0, 0.0, 'g'),
('Biber', 251, 10.4, 63.7, 3.3, 26.5, 'g'),
('Paradajz pire', 38, 1.7, 8.7, 0.4, 1.8, 'g'),
('Ketchup', 101, 1.5, 25.0, 0.1, 0.3, 'g'),
('Soja sos', 53, 5.6, 4.9, 0.6, 0.8, 'ml');

-- ============================================================
-- PODACI - Demo korisnik (lozinka je "admin123")
-- ============================================================
INSERT INTO Korisnici (Ime, Prezime, Email, Lozinka) VALUES
('Admin', 'Korisnik', 'admin@recepti.rs', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('Marko', 'Marković', 'marko@test.rs', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('Ana', 'Anić', 'ana@test.rs', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- ============================================================
-- PODACI - Recepti
-- ============================================================
INSERT INTO Recepti (KorisnikID, Naziv, Opis, VremePripreme, VremePecenja, BrojPorcija, Kategorija, Tezina, Slika) VALUES
(1, 'Pileća supa', 'Klasična domaća pileća supa sa povrćem i rezancima. Savršena za hladne dane i kad nisi dobro.', 20, 60, 6, 'ručak', 'lako', 'pileca_supa.png'),
(1, 'Špageti bolognese', 'Italijanski klasik - sočni mleveni sos sa paradajzom i tjesteninom. Uvek pun pogodak.', 15, 45, 4, 'ručak', 'srednje', 'spageti_bolognese.png'),
(1, 'Grčka salata', 'Sveža salata sa paradajzom, krastavcem, feta sirom i maslinama. Gotova za 10 minuta.', 10, 0, 2, 'večera', 'lako', 'grcka_salata.png'),
(2, 'Ovsena kaša sa voćem', 'Zdrav doručak pun vlakana. Sa bananama, jagodama i medom.', 5, 10, 1, 'doručak', 'lako', 'ovsena_kasa_sa_vocem.png'),
(2, 'Losos sa povrćem iz rerne', 'Zdrav obrok bogat omega-3 masnim kiselinama i vitaminima.', 15, 25, 2, 'večera', 'srednje', 'losos_sa_povrcem_iz_rerne.png'),
(3, 'Pasulj čorba', 'Naše klasično jelo. Gusta, hranjiva čorba koja te zagreje iznutra.', 20, 90, 6, 'ručak', 'srednje', 'pasulj_corba.png'),
(3, 'Palacinke', 'Tanke palacinke sa pekmezom ili nutellom. I deca ih obožavaju.', 10, 20, 4, 'doručak', 'lako', 'palacinke.png'),
(1, 'Cezar salata sa piletinom', 'Popularna salata sa hrskavim krutonima, parmezanom i Cezar dresingom.', 20, 15, 2, 'večera', 'srednje', 'cezar_salata_sa_piletinom.png'),
(2, 'Musaka', 'Tradicionalna musaka sa mlevenim mesom, patlidžanom i bešamel sosom.', 30, 60, 6, 'ručak', 'teško', 'musaka.png'),
(3, 'Smoothie od bobičastog voćća', 'Brzi zdravi napitak za doručak ili užinu. Pun antioksidanata.', 5, 0, 1, 'doručak', 'lako', 'smoothie_od_bobicastog_voca.png');

-- ============================================================
-- PODACI - Recepti_Sastojci (veza recept-sastojak sa količinama)
-- ============================================================
-- Pileća supa (ReceptID=1)
INSERT INTO Recepti_Sastojci (ReceptID, SastojakID, Kolicina) VALUES
(1, 10, 500),  -- pileće bedro 500g
(1, 32, 200),  -- šargarepa 200g
(1, 35, 100),  -- krompir 100g
(1, 34, 50),   -- crni luk 50g
(1, 5, 200),   -- pasta (rezanci) 200g
(1, 52, 5);    -- so 5g

-- Španeti bolognese (ReceptID=2)
INSERT INTO Recepti_Sastojci (ReceptID, SastojakID, Kolicina) VALUES
(2, 5, 400),   -- pasta 400g
(2, 11, 500),  -- juneći but 500g
(2, 28, 300),  -- paradajz 300g
(2, 55, 200),  -- paradajz pire 200g
(2, 34, 80),   -- crni luk 80g
(2, 33, 20),   -- beli luk 20g
(2, 49, 30);   -- maslinovo ulje 30ml

-- Grčka salata (ReceptID=3)
INSERT INTO Recepti_Sastojci (ReceptID, SastojakID, Kolicina) VALUES
(3, 28, 300),  -- paradajz 300g
(3, 29, 200),  -- krastavac 200g
(3, 20, 150),  -- feta sir 150g
(3, 30, 100),  -- paprika 100g
(3, 34, 50),   -- crni luk 50g
(3, 49, 20);   -- maslinovo ulje 20ml

-- Ovsena kaša (ReceptID=4)
INSERT INTO Recepti_Sastojci (ReceptID, SastojakID, Kolicina) VALUES
(4, 6, 80),    -- zob 80g
(4, 16, 200),  -- mleko 200ml
(4, 43, 100),  -- jagoda 100g
(4, 42, 50),   -- banana 50g
(4, 54, 15);   -- med 15g

-- Losos sa povrćem (ReceptID=5)
INSERT INTO Recepti_Sastojci (ReceptID, SastojakID, Kolicina) VALUES
(5, 13, 400),  -- losos 400g
(5, 36, 200),  -- brokoli 200g
(5, 35, 300),  -- krompir 300g
(5, 49, 30),   -- maslinovo ulje 30ml
(5, 33, 10),   -- beli luk 10g
(5, 52, 5);    -- so 5g

-- Pasulj čorba (ReceptID=6)
INSERT INTO Recepti_Sastojci (ReceptID, SastojakID, Kolicina) VALUES
(6, 25, 400),  -- pasulj 400g
(6, 12, 200),  -- svinjski kare 200g
(6, 34, 100),  -- crni luk 100g
(6, 32, 100),  -- šargarepa 100g
(6, 30, 100),  -- paprika 100g
(6, 52, 8),    -- so 8g
(6, 53, 2);    -- biber 2g

-- Palačinke (ReceptID=7)
INSERT INTO Recepti_Sastojci (ReceptID, SastojakID, Kolicina) VALUES
(7, 1, 200),   -- brašno 200g
(7, 24, 3),    -- jaja 3 kom
(7, 16, 400),  -- mleko 400ml
(7, 56, 30),   -- šećer 30g
(7, 51, 20);   -- puter 20g

-- Cezar salata (ReceptID=8)
INSERT INTO Recepti_Sastojci (ReceptID, SastojakID, Kolicina) VALUES
(8, 9, 300),   -- pileća prsa 300g
(8, 17, 150),  -- jogurt (kao baza dressinga) 150g
(8, 19, 50),   -- gauda (parmezan nije u listi, koristimo gauda) 50g
(8, 7, 100),   -- hleb za krutone 100g
(8, 33, 15),   -- beli luk 15g
(8, 49, 20);   -- maslinovo ulje 20ml

-- Musaka (ReceptID=9)
INSERT INTO Recepti_Sastojci (ReceptID, SastojakID, Kolicina) VALUES
(9, 11, 600),  -- juneći but 600g
(9, 40, 500),  -- patlidžan 500g
(9, 35, 400),  -- krompir 400g
(9, 28, 300),  -- paradajz 300g
(9, 34, 100),  -- crni luk 100g
(9, 16, 300),  -- mleko (za bešamel) 300ml
(9, 1, 50),    -- brašno (za bešamel) 50g
(9, 51, 50);   -- puter (za bešamel) 50g

-- Smoothie (ReceptID=10)
INSERT INTO Recepti_Sastojci (ReceptID, SastojakID, Kolicina) VALUES
(10, 43, 100), -- jagoda 100g
(10, 44, 50),  -- borovnica 50g
(10, 42, 100), -- banana 100g
(10, 17, 150), -- grčki jogurt 150g
(10, 54, 10);  -- med 10g

-- ============================================================
-- PODACI - Alergeni po receptu
-- ============================================================
INSERT INTO Recepti_Alergeni (ReceptID, AlergenID) VALUES
(1, 1),  -- supa: gluten (rezanci)
(2, 1),  -- bolognese: gluten
(3, 2),  -- grčka salata: laktoza (feta)
(4, 2),  -- ovsena kaša: laktoza
(4, 1),  -- ovsena kaša: gluten (ovas)
(5, 6),  -- losos: riba
(7, 1),  -- palačinke: gluten
(7, 2),  -- palačinke: laktoza
(7, 3),  -- palačinke: jaja
(8, 2),  -- cezar: laktoza
(8, 1),  -- cezar: gluten (krutoni)
(9, 2),  -- musaka: laktoza
(9, 3),  -- musaka: jaja
(9, 1);  -- musaka: gluten

-- ============================================================
-- PODACI - Planovi ishrane
-- ============================================================
INSERT INTO PlanoviIshrane (KorisnikID, Naziv, Opis, DatumPocetka, DatumZavrsetka, CiljKalorija) VALUES
(1, 'Kalorijski deficit - jun 2025', 'Plan za mršavljenje. Ciljamo -500 kcal od održavanja.', '2025-06-01', '2025-06-30', 1800),
(2, 'Masa - bulk faza', 'Povećanje mišićne mase. Kalorijski suficit uz trening.', '2025-06-01', '2025-07-31', 3000),
(3, 'Uravnotežena ishrana', 'Zdrav i uravnotežen plan bez ekstremnih ograničenja.', '2025-06-01', '2025-08-31', 2200);

-- ============================================================
-- PODACI - Obroci
-- ============================================================
INSERT INTO Obroci (KorisnikID, ReceptID, DatumObroka, VrstaObroka, BrojPorcija, Napomena) VALUES
(1, 4, '2025-06-10', 'doručak', 1, 'Sa dodatnom bananinom'),
(1, 3, '2025-06-10', 'ručak', 1, NULL),
(1, 5, '2025-06-10', 'večera', 1, NULL),
(2, 4, '2025-06-10', 'doručak', 2, 'Dupla porcija za trening dan'),
(2, 2, '2025-06-10', 'ručak', 1.5, NULL),
(2, 9, '2025-06-10', 'večera', 1, NULL),
(3, 10, '2025-06-10', 'doručak', 1, NULL),
(3, 6, '2025-06-10', 'ručak', 1, 'Bez mesa'),
(1, 7, '2025-06-11', 'doručak', 1, NULL),
(1, 1, '2025-06-11', 'ručak', 2, 'Ostaci od juče');

-- ============================================================
-- PODACI - Plan_Obroci (veza plan <-> obrok)
-- ============================================================
INSERT INTO Plan_Obroci (PlanID, ObrokID) VALUES
(1, 1), (1, 2), (1, 3),
(2, 4), (2, 5), (2, 6),
(3, 7), (3, 8),
(1, 9), (1, 10);
