-- CADANGAN DATABASE UAP VILLA 
-- Dibuat otomatis: 2026-06-05 01:36:25

-- Data Tabel customer 
INSERT INTO customer VALUES ('1', 'Kirana Aditya Moza', '08123456789');
INSERT INTO customer VALUES ('2', 'Khaila Noverisya Nurdi', '08987654321');
INSERT INTO customer VALUES ('3', 'Naura Azura grahyta', '088673561074');

-- Data Tabel users 
INSERT INTO users VALUES ('1', 'admin', 'admin123', 'admin', NULL);
INSERT INTO users VALUES ('2', 'kirana', 'kirana123', 'customer', '1');
INSERT INTO users VALUES ('3', 'khaila', 'khaila123', 'customer', '2');
INSERT INTO users VALUES ('4', 'naura', 'naura123', 'customer', '3');

-- Data Tabel vila 
INSERT INTO vila VALUES ('V01', 'Villa Kabut Pinus', 'Jl. Raya Cisarua No. 88, Puncak, Bogor, Jawa Barat', 'Puncak', '750000.00', 'Disewa');
INSERT INTO vila VALUES ('V02', 'Villa Arunika Hills', 'Jl. Bukit Pelangi No. 15, Megamendung, Puncak, Jawa Barat', 'Puncak', '750000.00', 'Tersedia');
INSERT INTO vila VALUES ('V03', 'Villa Panorama Puncak', 'Jl. Gunung Mas No. 27, Cisarua, Puncak, Jawa Barat', 'Puncak', '650000.00', 'Tersedia');
INSERT INTO vila VALUES ('V04', 'Villa Skyview Retreat', 'Jl. Taman Safari No. 42, Cibeureum, Puncak, Jawa Barat', 'Puncak', '800000.00', 'Tersedia');
INSERT INTO vila VALUES ('V05', 'Villa Pine Valley', 'Jl. Hutan Pinus No. 10, Megamendung, Puncak, Jawa Barat', 'Puncak', '700000.00', 'Tersedia');
INSERT INTO vila VALUES ('V06', 'Villa Ombak Biru', 'Jl. Pantai Selatan No. 12, Anyer, Banten', 'Pantai', '1200000.00', 'Tersedia');
INSERT INTO vila VALUES ('V07', 'Villa Sunset Cove', 'Jl. Karang Bolong No. 25, Anyer, Banten', 'Pantai', '1100000.00', 'Tersedia');
INSERT INTO vila VALUES ('V08', 'Villa Pasir Mutiara', 'Jl. Pantai Indah No. 7, Lampung Selatan, Lampung', 'Pantai', '950000.00', 'Tersedia');
INSERT INTO vila VALUES ('V09', 'Villa Sea Breeze', 'Jl. Teluk Harapan No. 18, Kalianda, Lampung', 'Pantai', '1000000.00', 'Tersedia');
INSERT INTO vila VALUES ('V10', 'Villa Samudra Indah', 'Jl. Pesisir Bahari No. 33, Pesawaran, Lampung', 'Pantai', '900000.00', 'Tersedia');
INSERT INTO vila VALUES ('V11', 'Villa Puncak Indah', 'Jl Puncak Indah No 12, Bogot, Jawa Barat', 'Puncak', '100000.00', 'Tersedia');
INSERT INTO vila VALUES ('V12', 'Villa Idaman', 'Jl Puncak Idaman No 15, Denpasar, Bali', 'Pantai', '500000.00', 'Tersedia');

-- Data Tabel booking 
INSERT INTO booking VALUES ('6', '1', 'V02', '2026-06-06', '1', '750000.00', '0.00', '750000.00', 'Pending');
INSERT INTO booking VALUES ('7', '1', 'V02', '2026-06-03', '1', '750000.00', '0.00', '750000.00', 'Pending');
INSERT INTO booking VALUES ('8', '1', 'V02', '2026-06-20', '5', '3750000.00', '375000.00', '3375000.00', 'Pending');
INSERT INTO booking VALUES ('9', '2', 'V01', '2026-06-13', '1', '750000.00', '0.00', '750000.00', 'Paid');

-- Data Tabel log_pembatalan 

