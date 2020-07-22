--
-- Tabellenstruktur für Tabelle `pkuej_bs3ghsvs_article`
--

CREATE TABLE IF NOT EXISTS `#__bs3ghsvs_article` (
  `article_id` int(11) NOT NULL,
  `key` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Article fields from plg_system_bs3ghsvs';

--
-- Indizes für die Tabelle `pkuej_bs3ghsvs_article`
--
ALTER TABLE `#__bs3ghsvs_article`
  ADD UNIQUE KEY `idx_article_id_key` (`article_id`,`key`);
COMMIT;
