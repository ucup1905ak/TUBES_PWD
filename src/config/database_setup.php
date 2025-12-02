<?php
class Database_setup{
		private $query = ["CREATE TABLE IF NOT EXISTS User (
		id_user INT AUTO_INCREMENT PRIMARY KEY,
		nama_lengkap VARCHAR(100) NOT NULL,
		email VARCHAR(100) NOT NULL UNIQUE,
		no_telp VARCHAR(15),
		alamat TEXT,
		password VARCHAR(255) NOT NULL,
		foto_profil MEDIUMBLOB,
		role ENUM('user', 'admin') DEFAULT 'user'
	)","CREATE TABLE IF NOT EXISTS Pet (
		id_pet INT AUTO_INCREMENT PRIMARY KEY,
		id_user INT,
		nama_pet VARCHAR(100) NOT NULL,
		jenis_pet VARCHAR(50),
		ras VARCHAR(50),
		umur INT,
		jenis_kelamin VARCHAR(10),
		warna VARCHAR(50),
		alergi TEXT,
		catatan_medis TEXT,
		foto_pet VARCHAR(255),
		FOREIGN KEY (id_user) REFERENCES User(id_user) ON DELETE CASCADE
	)","CREATE TABLE IF NOT EXISTS Paket_Kamar (
		id_paket INT AUTO_INCREMENT PRIMARY KEY,
		nama_paket VARCHAR(100) NOT NULL,
		deskripsi TEXT,
		harga_per_hari DECIMAL(10,2),
		fasilitas TEXT
	)","CREATE TABLE IF NOT EXISTS Layanan (
		id_layanan INT AUTO_INCREMENT PRIMARY KEY,
		nama_layanan VARCHAR(100) NOT NULL,
		deskripsi TEXT,
		harga DECIMAL(10,2)
	)","CREATE TABLE IF NOT EXISTS Penitipan (
		id_penitipan INT AUTO_INCREMENT PRIMARY KEY,
		id_user INT,
		id_pet INT,
		tgl_checkin DATE,
		tgl_checkout DATE,
		id_paket INT,
		status_penitipan VARCHAR(50),
		FOREIGN KEY (id_user) REFERENCES User(id_user) ON DELETE CASCADE,
		FOREIGN KEY (id_pet) REFERENCES Pet(id_pet) ON DELETE CASCADE,
		FOREIGN KEY (id_paket) REFERENCES Paket_Kamar(id_paket) ON DELETE SET NULL
	)","CREATE TABLE IF NOT EXISTS Penitipan_Layanan (
		id_penitipan INT,
		id_layanan INT,
		qty INT,
		PRIMARY KEY (id_penitipan, id_layanan),
		FOREIGN KEY (id_penitipan) REFERENCES Penitipan(id_penitipan) ON DELETE CASCADE,
		FOREIGN KEY (id_layanan) REFERENCES Layanan(id_layanan) ON DELETE CASCADE
	)",
	"CREATE TABLE IF NOT EXISTS User_Session (
		id INT AUTO_INCREMENT PRIMARY KEY,
		id_user INT NOT NULL,
		session_token VARCHAR(255) UNIQUE NOT NULL,
		ip_address VARCHAR(45),
		expires_at TIMESTAMP NOT NULL,
		created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
		FOREIGN KEY (id_user) REFERENCES User(id_user) ON DELETE CASCADE,
		INDEX idx_session_token (session_token),
		INDEX idx_expires (expires_at)
	)",
];


	private $DB_CONN;
	public function initializeTables(){
		foreach($this->query as $q){
			if ($this->DB_CONN->query($q) === FALSE) {
				error_log("Error executing query: " . $q . " - " . $this->DB_CONN->error);
				// Output to browser console
			}
			
		}
	}

	public function __construct($DB_CONN){
		$this->DB_CONN = $DB_CONN;
	}
}