DROP SCHEMA IF EXISTS `messenger`;
CREATE SCHEMA IF NOT EXISTS `messenger` DEFAULT CHARACTER SET utf8;

DROP TABLE IF EXISTS `messenger`.`channels`;
CREATE TABLE IF NOT EXISTS `messenger`.`channels` (
  `channel_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `created_timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `channel_name` varchar(255) NOT NULL,
  `topic` varchar(255) NOT NULL,
  `enabled` TINYINT NOT NULL DEFAULT 1,
  PRIMARY KEY (`channel_id`),
  UNIQUE INDEX `channel_name` (`channel_name` ASC))
ENGINE = InnoDB
AUTO_INCREMENT = 1;

DROP TABLE IF EXISTS `messenger`.`users`;
CREATE TABLE IF NOT EXISTS `messenger`.`users` (
  `user_id` INT UNSIGNED NOT NULL,
  `created_timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `enabled` TINYINT NOT NULL DEFAULT 1,
  `last_online` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `nick` VARCHAR(20) NOT NULL,
  `selected_channel_id` INT UNSIGNED NOT NULL,
  PRIMARY KEY (`user_id`),
  UNIQUE INDEX `nick` (`nick` ASC),
  CONSTRAINT `fk_users_users1`
    FOREIGN KEY (`user_id`)
    REFERENCES `webdb`.`users` (`user_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_users_channels1`
    FOREIGN KEY (`selected_channel_id`)
    REFERENCES `messenger`.`channels` (`channel_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

DROP TABLE IF EXISTS `messenger`.`messages`;
CREATE TABLE IF NOT EXISTS `messenger`.`messages` (
  `message_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `created_timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_id` integer unsigned NOT NULL,
  `channel_id` integer unsigned NOT NULL,
  `message` longtext NOT NULL,
  PRIMARY KEY (`message_id`),
  INDEX `user_id` (`user_id` ASC),
  INDEX `channel_id` (`channel_id` ASC),
  CONSTRAINT `fk_messages_users1`
    FOREIGN KEY (`user_id`)
    REFERENCES `messenger`.`users` (`user_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_messages_channels1`
    FOREIGN KEY (`channel_id`)
    REFERENCES `messenger`.`channels` (`channel_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
AUTO_INCREMENT = 1;

DROP TABLE IF EXISTS `messenger`.`channel_users` ;
CREATE TABLE IF NOT EXISTS `messenger`.`channel_users` (
  `channel_id` INT UNSIGNED NOT NULL,
  `user_id` INT UNSIGNED NOT NULL,
  `last_read_message_id` INT UNSIGNED NOT NULL,
  PRIMARY KEY (`channel_id`, `user_id`),
  CONSTRAINT `fk_channel_users_channels1`
    FOREIGN KEY (`channel_id`)
    REFERENCES `messenger`.`channels` (`channel_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_channel_users_users1`
    FOREIGN KEY (`user_id`)
    REFERENCES `messenger`.`users` (`user_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_channel_users_messages1`
    FOREIGN KEY (`last_read_message_id`)
    REFERENCES `messenger`.`messages` (`message_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;
