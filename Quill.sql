CREATE TABLE IF NOT EXISTS `quill_threads` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `location` varchar(60) NOT NULL,
  `title` varchar(45) NOT NULL,
  `description` varchar(144) NOT NULL,
  `status` enum('open','closed') NOT NULL DEFAULT 'open',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `quill_topics` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `thread_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `title` varchar(45) NOT NULL,
  `content` text NOT NULL,
  `status` enum('open','closed','deleted') NOT NULL DEFAULT 'open',
  `stickied` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` varchar(30) NOT NULL,
  `last_post_user_id` int(10) unsigned NOT NULL,
  `updated_at` varchar(30) NOT NULL,
  `reply_count` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_thread_id` (`thread_id`),
  KEY `fk_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `quill_replies` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `topic_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `created_at` varchar(30) NOT NULL,
  `content` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_topic_id` (`topic_id`),
  KEY `fk_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

ALTER TABLE `quill_topics`
  ADD CONSTRAINT `quill_topics_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `quill_topics_ibfk_2` FOREIGN KEY (`thread_id`) REFERENCES `quill_threads` (`id`) ON DELETE CASCADE;

ALTER TABLE  `quill_replies` ADD FOREIGN KEY (`topic_id`) REFERENCES `quill_topics` (`id`) ON DELETE CASCADE ;
ALTER TABLE  `quill_replies` ADD FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ;