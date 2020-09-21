create table blog (id int auto_increment, title varchar(191), content text, category int, created_at timestamp, publish_status int default 1, index(id));

CREATE TABLE `blog` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(191) DEFAULT NULL,
  `content` text,
  `category` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `publish_status` int(11) DEFAULT '1',
  KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 |
