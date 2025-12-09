-- Insert default room packages
INSERT INTO Paket_Kamar (nama_paket, deskripsi, harga_per_hari, fasilitas) VALUES
('Reguler', 'Kamar standar dengan fasilitas dasar', 25000, 'Tempat tidur, mangkok makan & minum, AC'),
('Premium', 'Kamar lebih luas dengan fasilitas lengkap', 35000, 'Tempat tidur empuk, mainan, AC, CCTV 24 jam'),
('VIP', 'Kamar mewah dengan layanan eksklusif', 50000, 'Suite room, playground pribadi, TV, AC, kamera 24/7, grooming gratis');

-- Insert default services
INSERT INTO Layanan (nama_layanan, deskripsi, harga) VALUES
('Grooming', 'Mandi, sisir, potong bulu, dan styling', 40000),
('Spa Treatment', 'Perawatan spa lengkap dengan aromaterapi', 75000),
('Potong Kuku', 'Pemangkasan kuku hewan peliharaan', 15000),
('Vaksinasi', 'Layanan vaksinasi dasar', 100000),
('Medical Check', 'Pemeriksaan kesehatan menyeluruh', 150000);
