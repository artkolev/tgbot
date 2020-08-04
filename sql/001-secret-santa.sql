CREATE TABLE `secret_santa_events` (
  `sse_id` INT NOT NULL AUTO_INCREMENT,
  `sse_chat_id` BIGINT(20) NOT NULL,
  `sse_active` INT(1) NOT NULL DEFAULT 0,
  `sse_last_date` DATETIME NULL,
  PRIMARY KEY (`sse_id`));

CREATE TABLE `secret_santa_event_members` (
  `ssem_id` INT NOT NULL AUTO_INCREMENT,
  `ssem_sse_id` INT NOT NULL,
  `ssem_user_id` BIGINT(20) NOT NULL,
  `ssem_active` INT(1) NOT NULL DEFAULT 0,
  `ssem_coordinate` TEXT NULL,
  PRIMARY KEY (`ssem_id`));
