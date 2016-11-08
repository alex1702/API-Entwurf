-- phpMyAdmin SQL Dump
-- version 4.5.1
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Erstellungszeit: 08. Nov 2016 um 21:30
-- Server-Version: 10.1.13-MariaDB
-- PHP-Version: 7.0.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Datenbank: `mediathekview`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `download`
--

DROP TABLE IF EXISTS `download`;
CREATE TABLE `download` (
  `id` int(11) NOT NULL,
  `sendung` int(11) NOT NULL,
  `url` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Daten für Tabelle `download`
--

INSERT INTO `download` (`id`, `sendung`, `url`) VALUES
(1, 1, 'http://mediathekview.de/testsendung-sd.mp4'),
(2, 1, 'http://mediathekview.de/testsendung-hq.mp4'),
(3, 1, 'http://mediathekview.de/testsendung-hd.mp4');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `sendungen`
--

DROP TABLE IF EXISTS `sendungen`;
CREATE TABLE `sendungen` (
  `id` int(11) NOT NULL,
  `title` varchar(70) NOT NULL,
  `date` datetime NOT NULL,
  `length` time NOT NULL,
  `sender` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `sender`
--

DROP TABLE IF EXISTS `sender`;
CREATE TABLE `sender` (
  `id` int(11) NOT NULL,
  `name` varchar(35) NOT NULL,
  `abbr` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Daten für Tabelle `sender`
--

INSERT INTO `sender` (`id`, `name`, `abbr`) VALUES
(1, 'ZDFneo', 'zdfneo'),
(2, 'arte', 'arte');

--
-- Daten für Tabelle `sendungen`
--

INSERT INTO `sendungen` (`id`, `title`, `date`, `length`, `sender`) VALUES
(1, 'Testsendung', '2016-11-08 17:30:00', '00:45:00', 1);

--
-- Indizes der exportierten Tabellen
--

--
-- Indizes für die Tabelle `download`
--
ALTER TABLE `download`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sendung` (`sendung`);

--
-- Indizes für die Tabelle `sender`
--
ALTER TABLE `sender`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `sendungen`
--
ALTER TABLE `sendungen`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sender` (`sender`);

--
-- AUTO_INCREMENT für exportierte Tabellen
--

--
-- AUTO_INCREMENT für Tabelle `download`
--
ALTER TABLE `download`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
--
-- AUTO_INCREMENT für Tabelle `sender`
--
ALTER TABLE `sender`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
--
-- AUTO_INCREMENT für Tabelle `sendungen`
--
ALTER TABLE `sendungen`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
--
-- Constraints der exportierten Tabellen
--

--
-- Constraints der Tabelle `download`
--
ALTER TABLE `download`
  ADD CONSTRAINT `sendung_zu_download` FOREIGN KEY (`sendung`) REFERENCES `sendungen` (`id`);

--
-- Constraints der Tabelle `sendungen`
--
ALTER TABLE `sendungen`
  ADD CONSTRAINT `sender_zu_sendung` FOREIGN KEY (`sender`) REFERENCES `sender` (`id`);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
