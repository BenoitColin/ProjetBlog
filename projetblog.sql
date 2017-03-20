-- phpMyAdmin SQL Dump
-- version 4.6.4
-- https://www.phpmyadmin.net/
--
-- Client :  127.0.0.1
-- Généré le :  Ven 17 Mars 2017 à 10:29
-- Version du serveur :  5.7.14
-- Version de PHP :  5.6.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données :  `projetblog`
--

-- --------------------------------------------------------

--
-- Structure de la table `auth_tokens`
--

CREATE TABLE `auth_tokens` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `value` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Contenu de la table `auth_tokens`
--

INSERT INTO `auth_tokens` (`id`, `user_id`, `value`, `created_at`) VALUES
(1, 2, 'KKpgMJZqSGdycNjyx1r327wFtKfKoHsjBobJdABJpLaldLhfwkz0cKPQRbk9yr1iLeI=', '2017-03-16 15:23:14'),
(2, 2, '0Uni5WvqDxk4xlhl/6vit8msLK101UKcWvtOv4iFzSnvbScgISA6XP+p/+M0Lqi9NQ4=', '2017-03-16 15:28:15'),
(3, 2, 'GAUiOEY7tGosYU3syJucKa5jcZWLcHXKFaZHRlaMdZKcj4oDBC1V4H8k9VbXvfuIJZs=', '2017-03-17 10:01:10');

-- --------------------------------------------------------

--
-- Structure de la table `user`
--

CREATE TABLE `user` (
  `id` int(11) NOT NULL,
  `firstname` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `lastname` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Contenu de la table `user`
--

INSERT INTO `user` (`id`, `firstname`, `lastname`, `email`, `password`) VALUES
(2, 'first', 'first', 'first@first.local', '$2y$12$voSfdk0UnjTii72XAsCHgOdwNHEsl04bwJfaWqb2WFZil80JnCl52'),
(3, 'second', 'second', 'second@second.local', '$2y$12$kYEq7zG2KzCm/CbIg.Pd1egNiXfhpb9V86xYcyuTUnxQx65EwDslS'),
(4, 'third', 'third', 'third@third.local', '$2y$12$GyBBYeUsiTfvSrp7lcEcwO5hg1Z49WWvJMfGtJFvh2QGJw5Krbo4C');

--
-- Index pour les tables exportées
--

--
-- Index pour la table `auth_tokens`
--
ALTER TABLE `auth_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `auth_tokens_value_unique` (`value`),
  ADD KEY `IDX_8AF9B66CA76ED395` (`user_id`);

--
-- Index pour la table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_email_unique` (`email`);

--
-- AUTO_INCREMENT pour les tables exportées
--

--
-- AUTO_INCREMENT pour la table `auth_tokens`
--
ALTER TABLE `auth_tokens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
--
-- AUTO_INCREMENT pour la table `user`
--
ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
--
-- Contraintes pour les tables exportées
--

--
-- Contraintes pour la table `auth_tokens`
--
ALTER TABLE `auth_tokens`
  ADD CONSTRAINT `FK_8AF9B66CA76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
