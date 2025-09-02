-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 23 Agu 2025 pada 15.25
-- Versi server: 10.4.6-MariaDB
-- Versi PHP: 8.3.8

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `satubudaya_kuningan`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `participants`
--

CREATE TABLE `participants` (
  `id` int(11) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `asal_sekolah` varchar(100) NOT NULL,
  `pilihan_lomba` enum('Tari Kreasi Kelompok','Pop Sunda','Menggambar','Desain Poster') NOT NULL,
  `no_wa` varchar(20) NOT NULL,
  `bukti_pendaftaran` varchar(255) NOT NULL,
  `tanggal_daftar` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data untuk tabel `participants`
--

INSERT INTO `participants` (`id`, `nama_lengkap`, `asal_sekolah`, `pilihan_lomba`, `no_wa`, `bukti_pendaftaran`, `tanggal_daftar`) VALUES
(1, 'Itsna Kamilatusyah Riyah', 'SMAN 1 Ciniru', 'Pop Sunda', '081327414126', 'bukti_68a67a547feb48.28141638.jpg', '2025-08-21 01:45:56'),
(2, 'Ayunda Zeskia Putri', 'SMAN 1 Ciniru', 'Tari Kreasi Kelompok', '081327414126', 'bukti_68a67a930412c7.66955768.png', '2025-08-21 01:46:59'),
(3, 'Ario Zulkaesi Nubli', 'SMK Pertiwi Kuningan', 'Tari Kreasi Kelompok', '081327414126', 'bukti_68a67d65bfa8b3.05729644.jpg', '2025-08-21 01:59:01');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `participants`
--
ALTER TABLE `participants`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `participants`
--
ALTER TABLE `participants`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
