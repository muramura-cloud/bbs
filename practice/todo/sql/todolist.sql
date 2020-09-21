CREATE TABLE `todolist` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL,
  `content` text NOT NULL,
  `flg` int(11) NOT NULL COMMENT '1の時は実行中、0の時は終了',
  KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8
