CREATE TABLE `users` (
                         `id` bigint NOT NULL,
                         `username` varchar(50) NOT NULL,
                         `password` varchar(255) NOT NULL,
                         `email` varchar(100) NOT NULL,
                         `code` varchar(50) DEFAULT NULL,
                         `ragioneSociale` varchar(100) DEFAULT NULL,
                         `codiceFiscale` varchar(16) DEFAULT NULL,
                         `partitaIva` varchar(20) DEFAULT NULL,
                         `indirizzo` varchar(255) DEFAULT NULL,
                         `citta` varchar(100) DEFAULT NULL,
                         `cap` varchar(10) DEFAULT NULL,
                         `provincia` varchar(100) DEFAULT NULL,
                         `country` varchar(100) DEFAULT NULL,
                         `telefono` varchar(20) DEFAULT NULL,
                         `fax` varchar(20) DEFAULT NULL,
                         `relationKind` varchar(50) NOT NULL,
                         `livello` varchar(50) NOT NULL,
                         `tariffaOraria` double DEFAULT NULL,
                         `role` varchar(50) NOT NULL,
                         `notes` text,
                         `locked` tinyint(1) DEFAULT '0',
                         `permissions` text,
                         `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                         `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                         PRIMARY KEY (`id`),
                         UNIQUE KEY `username` (`username`),
                         KEY `email` (`email`)
) DEFAULT CHARSET=utf8mb4;

CREATE TABLE `roles` (
                         `id` varchar(36) NOT NULL,
                         `name` varchar(100) NOT NULL,
                         `description` text,
                         `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                         `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                         PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8mb4


CREATE TABLE `permissions` (
                               `role` varchar(36) NOT NULL,
                               `id` varchar(36) NOT NULL,
                               `permissions` varchar(36) NOT NULL,
                               `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                               `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                               PRIMARY KEY (`role`,`id`)
) DEFAULT CHARSET=utf8mb4;

CREATE TABLE `reset_tokens` (
                                `id` int NOT NULL AUTO_INCREMENT,
                                `username` varchar(255) NOT NULL,
                                `valid_until` timestamp NULL DEFAULT NULL,
                                PRIMARY KEY (`id`)
) DEFAULT CHARSET=latin1



CREATE TABLE `operations_log` (
                                  `id` int NOT NULL AUTO_INCREMENT,
                                  `type` varchar(50) NOT NULL,
                                  `operation` varchar(255) NOT NULL,
                                  `user_id` int NOT NULL,
                                  `data` text,
                                  `operationId` varchar(255) NOT NULL,
                                  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                                  PRIMARY KEY (`id`)
) DEFAULT CHARSET=utf8mb4







