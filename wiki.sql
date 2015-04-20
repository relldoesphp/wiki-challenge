CREATE TABLE IF NOT EXISTS `conversion_rates` (
`id` int(11) NOT NULL ,
`currency` VARCHAR(255) NULL ,
`rate` VARCHAR(255) NULL ,
PRIMARY KEY (`id`) ,
UNIQUE INDEX `currency` (`currency`)
)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_bin
