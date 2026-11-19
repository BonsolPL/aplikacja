-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Lis 18, 2026 at 04:42 PM
-- Wersja serwera: 10.4.32-MariaDB
-- Wersja PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `vulcan`
--

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `historia_zmian`
--

CREATE TABLE `historia_zmian` (
  `id` int(11) NOT NULL,
  `operation` varchar(100) NOT NULL,
  `details` text DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `user_email` varchar(150) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `historia_zmian`
--

INSERT INTO `historia_zmian` (`id`, `operation`, `details`, `user_id`, `user_email`, `created_at`) VALUES
(1, 'Dodanie zmiany', '2026-11-18 | Poniedziałek | 08:00–08:45 | 1A | Zastępstwo', 1, 'test@gmail.com', '2026-11-18 15:31:08'),
(2, 'Usunięcie zmiany', '2026-11-18 | 1A | 08:00–08:45', 1, 'test@gmail.com', '2026-11-18 15:34:45');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `plan_edits`
--

CREATE TABLE `plan_edits` (
  `id` int(11) NOT NULL,
  `day_name` varchar(30) NOT NULL,
  `lesson_time` varchar(20) NOT NULL,
  `class_name` varchar(10) NOT NULL,
  `subject` varchar(150) NOT NULL,
  `teacher` varchar(150) NOT NULL,
  `user_email` varchar(150) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `registration_code` varchar(100) NOT NULL,
  `class` tinyint(4) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `email`, `registration_code`, `class`, `password`, `created_at`) VALUES
(1, 'test@gmail.com', 'ABC123', 3, 'test12345', '2026-11-10 20:58:58'),
(2, 'testowymail@gmail.com', 'asdaw', 3, 'test12345', '2026-11-10 21:21:49');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `zastepstwa`
--

CREATE TABLE `zastepstwa` (
  `id` int(11) NOT NULL,
  `lesson_date` date NOT NULL,
  `day_name` varchar(30) NOT NULL,
  `lesson_time` varchar(20) NOT NULL,
  `class_name` varchar(10) NOT NULL,
  `type` varchar(20) NOT NULL,
  `subject` varchar(150) DEFAULT '',
  `teacher` varchar(150) DEFAULT '',
  `reason` text DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `user_email` varchar(150) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indeksy dla zrzutów tabel
--

--
-- Indeksy dla tabeli `historia_zmian`
--
ALTER TABLE `historia_zmian`
  ADD PRIMARY KEY (`id`);

--
-- Indeksy dla tabeli `plan_edits`
--
ALTER TABLE `plan_edits`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_lesson` (`day_name`,`lesson_time`,`class_name`);

--
-- Indeksy dla tabeli `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indeksy dla tabeli `zastepstwa`
--
ALTER TABLE `zastepstwa`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `historia_zmian`
--
ALTER TABLE `historia_zmian`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `plan_edits`
--
ALTER TABLE `plan_edits`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `zastepstwa`
--
ALTER TABLE `zastepstwa`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
