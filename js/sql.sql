-- テーブルの作成
CREATE TABLE IF NOT EXISTS `gs_hook_table` (
  `id` int(12) NOT NULL AUTO_INCREMENT,
  `punch_type` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `velocity` float NOT NULL,
  `comment` text COLLATE utf8_unicode_ci,
  `indate` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- テスト用データの挿入
INSERT INTO `gs_hook_table` (`punch_type`, `velocity`, `comment`, `indate`) VALUES
('Right Hook', 85.5, '素晴らしいキレです！', NOW()),
('Right Hook', 92.0, 'KO間違いなし！', NOW());