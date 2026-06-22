
INSERT INTO `functionalities` (`functionality`, `id`, `name`, `description`, `value`, `created_at`, `updated_at`) VALUES
                                                                                                          ('impiego', 'altro', 'Altro', 'Altro', 'altro', '2025-08-18 10:50:58', '2025-08-18 10:50:58'),
                                                                                                          ('livello', 'altro', 'Altro', 'Altro', 'altro', '2025-08-18 10:50:58', '2025-08-18 10:50:58');

INSERT INTO `roles` (`id`, `name`, `description`, `created_at`, `updated_at`) VALUES
                                                                                  ('Admin', 'Admin', 'Amministratore con accesso a tutte le funzionalità tranne la gestione degli utenti', '2025-07-21 09:33:15', '2025-07-21 09:33:15'),
                                                                                  ('Ospite', 'Ospite', 'Ospite', '2025-08-07 11:20:46', '2025-08-07 11:20:46'),
                                                                                  ('SuperAdmin', 'Super Admin', 'Accesso completo e illimitato a tutte le funzionalità', '2025-07-21 09:33:15', '2025-07-21 09:33:15');


INSERT INTO `permissions` (`role`, `id`, `permissions`, `created_at`, `updated_at`) VALUES
                                                ('Admin', 'users', 'RU', '2025-07-21 09:33:15', '2025-07-21 09:33:15'),
                                                ('SuperAdmin', 'files', 'CRUD', '2025-07-21 09:33:15', '2025-07-21 09:33:15'),
                                                ('SuperAdmin', 'functionalities', 'CRUD', '2025-07-21 09:33:15', '2025-07-21 09:33:15'),
                                                ('SuperAdmin', 'maintenance', 'CRUD', '2025-07-21 09:33:15', '2025-07-21 09:33:15'),
                                                ('SuperAdmin', 'permissions', 'CRUD', '2025-07-21 09:33:15', '2025-07-21 09:33:15'),
                                                ('SuperAdmin', 'roles', 'CRUD', '2025-07-21 09:33:15', '2025-07-21 09:33:15'),
                                                ('SuperAdmin', 'simulate', 'CRUD', '2025-07-21 09:33:15', '2025-07-21 09:33:15'),
                                                ('SuperAdmin', 'users', 'CRUD', '2025-07-21 09:33:15', '2025-07-21 09:33:15'),
                                                ('SuperAdmin', 'packages', 'CRUD', '2025-07-21 09:33:15', '2025-07-21 09:33:15'),
                                                ('Admin', 'packages', 'CRUD', '2025-07-21 09:33:15', '2025-07-21 09:33:15'),
                                                ('Ospite', 'packages', 'R', '2025-08-07 11:20:46', '2025-08-07 11:20:46');

CREATE TABLE `accesses`
(
    `id`            int(11) NOT NULL AUTO_INCREMENT,
    `username`      varchar(255) NOT NULL,
    `attempt_time`  timestamp    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `is_blocked`    tinyint(1) DEFAULT '0',
    `blocked_until` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY             `idx_username` (`username`),
    KEY             `idx_attempt_time` (`attempt_time`)
) ENGINE=InnoDB AUTO_INCREMENT=246 DEFAULT CHARSET=utf8mb4;
CREATE TABLE `counters`
(
    `table_name` varchar(50) NOT NULL,
    `counter`    int(11) NOT NULL DEFAULT '0',
    PRIMARY KEY (`table_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `files`
(
    `id`          int(11) NOT NULL AUTO_INCREMENT,
    `name`        varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Original filename',
    `description` text COLLATE utf8mb4_unicode_ci         NOT NULL COMMENT 'File description',
    `path`        varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Logical path/directory',
    `mime_type`   varchar(100) COLLATE utf8mb4_unicode_ci          DEFAULT NULL COMMENT 'MIME type of the file',
    `created_at`  timestamp                               NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`  timestamp                               NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY           `idx_path` (`path`),
    KEY           `idx_created_at` (`created_at`),
    KEY           `idx_name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='File storage metadata table';
CREATE TABLE `functionalities`
(
    `functionality` varchar(36)  NOT NULL,
    `id`            varchar(255) NOT NULL,
    `name`          varchar(36)  NOT NULL,
    `description`   text,
    `value`         varchar(64)  NOT NULL,
    `created_at`    timestamp    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`    timestamp    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`functionality`, `id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `operations_log`
(
    `id`          int(11) NOT NULL AUTO_INCREMENT,
    `type`        varchar(50)  NOT NULL,
    `operation`   varchar(255) NOT NULL,
    `user_id`     int(11) NOT NULL,
    `data`        text,
    `created_at`  timestamp    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `operationId` varchar(255) NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11527 DEFAULT CHARSET=utf8mb4;
CREATE TABLE `password_resets`
(
    `id`         bigint(20) NOT NULL AUTO_INCREMENT,
    `user_id`    bigint(20) NOT NULL,
    `token`      varchar(255) NOT NULL,
    `expires_at` datetime     NOT NULL,
    `used`       tinyint(1) DEFAULT '0',
    `created_at` timestamp    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY          `user_id` (`user_id`),
    CONSTRAINT `password_resets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=34 DEFAULT CHARSET=utf8mb4;
CREATE TABLE `permissions`
(
    `role`        varchar(36) NOT NULL,
    `id`          varchar(36) NOT NULL,
    `permissions` varchar(36) NOT NULL,
    `created_at`  timestamp   NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`  timestamp   NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`role`, `id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `reset_tokens`
(
    `id`          int(11) NOT NULL AUTO_INCREMENT,
    `username`    varchar(255) NOT NULL,
    `valid_until` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `roles`
(
    `id`          varchar(36)  NOT NULL,
    `name`        varchar(100) NOT NULL,
    `description` text,
    `created_at`  timestamp    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`  timestamp    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE `users`
(
    `id`                bigint(20) NOT NULL,
    `username`          varchar(50)  NOT NULL,
    `password`          varchar(255) NOT NULL,
    `email`             varchar(100) NOT NULL,
    `apiKey`            varchar(64)           DEFAULT NULL,
    `code`              varchar(50)           DEFAULT NULL,
    `ragioneSociale`    varchar(100)          DEFAULT NULL,
    `codiceFiscale`     varchar(16)           DEFAULT NULL,
    `partitaIva`        varchar(20)           DEFAULT NULL,
    `indirizzo`         varchar(255)          DEFAULT NULL,
    `citta`             varchar(100)          DEFAULT NULL,
    `provincia`         varchar(100)          DEFAULT NULL,
    `cap`               varchar(10)           DEFAULT NULL,
    `telefono`          varchar(20)           DEFAULT NULL,
    `fax`               varchar(20)           DEFAULT NULL,
    `relationKind`      varchar(50)  NOT NULL,
    `relationKindOther` varchar(50)           DEFAULT NULL,
    `livello`           varchar(50)  NOT NULL,
    `tariffaOraria` double DEFAULT NULL,
    `role`              varchar(50)  NOT NULL,
    `notes`             text,
    `locked`            tinyint(1) DEFAULT '0',
    `created_at`        timestamp    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`        timestamp    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `country`           varchar(100)          DEFAULT NULL,
    `permissions`       text,
    PRIMARY KEY (`id`),
    UNIQUE KEY `username` (`username`),
    UNIQUE KEY `apiKey` (`apiKey`),
    KEY                 `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
-- Nuget package metadata store (mysql backend of lib\nuget\NugetPackages).
-- Columns mirror the lib\nuget\models\NugetPackage properties, plus the split
-- version columns added by NugetPackageConverter::extraAssoc. Array/object
-- fields (Author, Owners, References, Dependencies) are stored as JSON text.
CREATE TABLE `packages`
(
    `Id`                       varchar(255) NOT NULL,
    `Version`                  varchar(128) NOT NULL,
    `UserId`                   varchar(64)            DEFAULT NULL,
    `IsSymbols`                tinyint(1)             DEFAULT '0',
    `Author`                   longtext,
    `Copyright`                varchar(255)           DEFAULT NULL,
    `Created`                  varchar(40)            DEFAULT NULL,
    `Dependencies`             longtext,
    `Description`              text,
    `DownloadCount`            int(11)                DEFAULT '0',
    `IconUrl`                  varchar(1024)          DEFAULT NULL,
    `IsLatestVersion`          tinyint(1)             DEFAULT '0',
    `Listed`                   tinyint(1)             DEFAULT '1',
    `IsAbsoluteLatestVersion`  tinyint(1)             DEFAULT '0',
    `IsPreRelease`             tinyint(1)             DEFAULT '0',
    `LastUpdated`              varchar(40)            DEFAULT NULL,
    `PackageHash`              varchar(255)           DEFAULT NULL,
    `PackageHashAlgorithm`     varchar(50)            DEFAULT 'sha256',
    `PackageSize`              bigint(20)             DEFAULT '0',
    `ProjectUrl`               varchar(1024)          DEFAULT NULL,
    `ReleaseNotes`             text,
    `RequireLicenseAcceptance` tinyint(1)             DEFAULT '0',
    `Summary`                  text,
    `Title`                    varchar(512)           DEFAULT NULL,
    `VersionDownloadCount`     int(11)                DEFAULT '0',
    `Tags`                     varchar(1024)          DEFAULT NULL,
    `LicenseUrl`               varchar(1024)          DEFAULT NULL,
    `LicenseNames`             varchar(512)           DEFAULT NULL,
    `LicenseReportUrl`         varchar(1024)          DEFAULT NULL,
    `TargetFramework`          varchar(512)           DEFAULT NULL,
    `Owners`                   longtext,
    `References`               longtext,
    `Version0`                 int(11)                DEFAULT '0',
    `Version1`                 int(11)                DEFAULT '0',
    `Version2`                 int(11)                DEFAULT '0',
    `Version3`                 int(11)                DEFAULT '0',
    `VersionBeta`              varchar(128)           DEFAULT NULL,
    PRIMARY KEY (`Id`, `Version`),
    KEY `idx_userid` (`UserId`),
    KEY `idx_title` (`Title`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;