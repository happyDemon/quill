CREATE TABLE IF NOT EXISTS `quill_locations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(60) NOT NULL,
  `description` varchar(144) NOT NULL,
  `count_topics` tinyint(1) NOT NULL DEFAULT '0' ,
  `record_last_topic` tinyint(1) NOT NULL DEFAULT '0',
  `stickies` tinyint(1) NOT NULL DEFAULT '0' ,
  `count_replies` tinyint(1) NOT NULL DEFAULT '0' ,
  `count_views` tinyint(1) NOT NULL DEFAULT '0' ,
  `record_last_post` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `quill_categories` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `location_id` int(10) unsigned NOT NULL,
  `title` varchar(45) NOT NULL,
  `description` varchar(144) NOT NULL,
  `status` enum('open','closed') NOT NULL DEFAULT 'open',
  `topic_count` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `fk_location_id` (`location_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `quill_topics` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `category_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `title` varchar(45) NOT NULL,
  `content` text NOT NULL,
  `status` enum('active','archived','deleted') NOT NULL DEFAULT 'active',
  `stickied` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` varchar(30) NOT NULL,
  `last_post_user_id` int(10) unsigned NOT NULL,
  `updated_at` varchar(30) NOT NULL,
  `reply_count` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_category_id` (`category_id`),
  KEY `fk_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `quill_replies` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `topic_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `created_at` varchar(30) NOT NULL,
  `content` text NOT NULL,
  `status` ENUM('active','deleted') NOT NULL DEFAULT  'active',
  PRIMARY KEY (`id`),
  KEY `fk_topic_id` (`topic_id`),
  KEY `fk_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

ALTER TABLE `quill_topics`
  ADD FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD FOREIGN KEY (`category_id`) REFERENCES `quill_categories` (`id`) ON DELETE CASCADE;

ALTER TABLE  `quill_replies` ADD FOREIGN KEY (`topic_id`) REFERENCES `quill_topics` (`id`) ON DELETE CASCADE ;
ALTER TABLE  `quill_replies` ADD FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ;

ALTER TABLE  `quill_categories` ADD FOREIGN KEY (`location_id`) REFERENCES `quill_locations` (`id`) ON DELETE CASCADE ;