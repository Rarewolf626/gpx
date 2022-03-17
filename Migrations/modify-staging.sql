/*
Date: 3/3/2022
Author: Jeff Shaikh

 Database modifications required on the staging server to sync the structure of the production server.

 */


CREATE TABLE wp_temp_cart (
                              id int NOT NULL AUTO_INCREMENT,
                              item varchar(25) NOT NULL,
                              user_id int NOT NULL,
                              data text NOT NULL,
                              PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;




CREATE TABLE `closure_credits_import` (
                                          `id` int NOT NULL AUTO_INCREMENT,
                                          `AccoutID` int DEFAULT NULL,
                                          `AccountName` varchar(50) DEFAULT NULL,
                                          `Couponcode` varchar(5) DEFAULT NULL,
                                          `LeviesPd` varchar(10) DEFAULT NULL,
                                          `CheckIn` varchar(10) DEFAULT NULL,
                                          `UnitSize` varchar(8) DEFAULT NULL,
                                          `BkgNo` varchar(13) DEFAULT NULL,
                                          `DepositID` int DEFAULT NULL,
                                          `CRBal` int DEFAULT NULL,
                                          `Deposit_Resort` int DEFAULT NULL,
                                          `Week_ID` varchar(8) DEFAULT NULL,
                                          `imported` int NOT NULL DEFAULT '0',
                                          `new_id` int NOT NULL,
                                          PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4;



CREATE TABLE `credit_dup_checked` (
                                      `id` int NOT NULL AUTO_INCREMENT,
                                      `checked` int NOT NULL,
                                      `txid` int DEFAULT NULL,
                                      PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;



CREATE TABLE `credit_dup_delete` (
                                     `id` int NOT NULL AUTO_INCREMENT,
                                     `owner_id` int NOT NULL,
                                     `deposit_year` int NOT NULL,
                                     `check_in_date` date NOT NULL,
                                     `credit_amount` int NOT NULL,
                                     `resort_name` varchar(150) NOT NULL,
                                     `unit_type` varchar(150) NOT NULL,
                                     PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;


CREATE TABLE `deposit_rework` (
                                  `id` int NOT NULL AUTO_INCREMENT,
                                  `weekId` int NOT NULL,
                                  `userID` int NOT NULL,
                                  `imported` int NOT NULL DEFAULT '0',
                                  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

CREATE TABLE `final_import_exceptions` (
                                           `id` int NOT NULL AUTO_INCREMENT,
                                           `type` varchar(255) NOT NULL,
                                           `data` text NOT NULL,
                                           `validated` int NOT NULL DEFAULT '0',
                                           PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;


CREATE TABLE `final_owner_import` (
                                      `id` int NOT NULL AUTO_INCREMENT,
                                      `sf` varchar(18) DEFAULT NULL,
                                      `dae` int DEFAULT NULL,
                                      `imported` int NOT NULL DEFAULT '0',
                                      PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `gpx_ownerships` (
                                  `AccountID` int NOT NULL DEFAULT '0',
                                  `OwnershipNo` int NOT NULL DEFAULT '0',
                                  `ResortID` varchar(8) CHARACTER SET latin1 NOT NULL DEFAULT '',
                                  `UnitType` int NOT NULL DEFAULT '0',
                                  `AnnivDate` datetime DEFAULT NULL,
                                  `FixedWeekType` tinyint DEFAULT NULL,
                                  `ForSale` char(1) CHARACTER SET latin1 DEFAULT NULL,
                                  `LastDepositID` int DEFAULT NULL,
                                  `PrevDepositID` int DEFAULT NULL,
                                  `ModifyUser` varchar(5) CHARACTER SET latin1 DEFAULT NULL,
                                  `ModifyDate` date DEFAULT NULL,
                                  `FixedWeekNo` int DEFAULT NULL,
                                  `OwnerShipType` int DEFAULT NULL,
                                  `ResortMemberNo` varchar(50) CHARACTER SET latin1 DEFAULT NULL,
                                  `ResortUnitRef` varchar(30) CHARACTER SET latin1 DEFAULT NULL,
                                  `UnitSleeps` varchar(10) CHARACTER SET latin1 DEFAULT NULL,
                                  `Comments` varchar(1024) CHARACTER SET latin1 DEFAULT NULL,
                                  `ExpiryDate` datetime DEFAULT NULL,
                                  `YearsDuration` int DEFAULT NULL,
                                  `PointsValue` int DEFAULT NULL,
                                  `ResortShareID` varchar(30) CHARACTER SET latin1 DEFAULT NULL,
                                  `Season` int DEFAULT NULL,
                                  `CheckInDay` varchar(3) CHARACTER SET latin1 DEFAULT '',
                                  `MaxAdults` int DEFAULT NULL,
                                  `MaxOccupancy` int DEFAULT NULL,
                                  `AccountType` int NOT NULL DEFAULT '0',
                                  `AccountTypeDesc` varchar(50) CHARACTER SET latin1 NOT NULL DEFAULT '',
                                  `ResortName` varchar(100) CHARACTER SET latin1 NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;



CREATE TABLE `import_credit_future_stay` (
                                             `ai_id` int NOT NULL AUTO_INCREMENT,
                                             `ID` int DEFAULT NULL,
                                             `Member_Name` int DEFAULT NULL,
                                             `credit_amount` int DEFAULT NULL,
                                             `Credit_expiration date` varchar(12) DEFAULT NULL,
                                             `resort_name` varchar(61) CHARACTER SET utf8mb4 COLLATE utf8_unicode_520_ci DEFAULT NULL,
                                             `Deposit_year` int DEFAULT NULL,
                                             `unit_type` varchar(8) CHARACTER SET utf8mb4 COLLATE utf8_unicode_520_ci DEFAULT NULL,
                                             `check_in_date` varchar(10) DEFAULT NULL,
                                             `credit_used` int DEFAULT NULL,
                                             `status` varchar(8) DEFAULT NULL,
                                             `extended` varchar(4) DEFAULT NULL,
                                             `sold check in date` varchar(10) DEFAULT NULL,
                                             `week_id` int DEFAULT NULL,
                                             `imported` int DEFAULT NULL,
                                             `new_id` int DEFAULT NULL,
                                             `missing_resort_id` int DEFAULT NULL,
                                             `sfError` text NOT NULL,
                                             PRIMARY KEY (`ai_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4;


CREATE TABLE `import_exceptions` (
                                     `id` int NOT NULL AUTO_INCREMENT,
                                     `type` varchar(255) NOT NULL,
                                     `data` text NOT NULL,
                                     `validated` int NOT NULL DEFAULT '0',
                                     PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


CREATE TABLE `import_owner_credits` (
                                        `ID` int DEFAULT NULL,
                                        `Member_Name` int DEFAULT NULL,
                                        `credit_amount` int DEFAULT NULL,
                                        `Credit_expiratio_date` varchar(10) DEFAULT NULL,
                                        `resort_name` varchar(50) DEFAULT NULL,
                                        `Deposit_year` int DEFAULT NULL,
                                        `unit_type` varchar(8) DEFAULT NULL,
                                        `check_in_date` varchar(10) DEFAULT NULL,
                                        `credit_used` int DEFAULT NULL,
                                        `status` varchar(8) DEFAULT NULL,
                                        `imported` int NOT NULL DEFAULT '0',
                                        `sfError` text NOT NULL,
                                        UNIQUE KEY `ID` (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;



CREATE TABLE `import_owner_no_vest` (
                                        `id` int NOT NULL AUTO_INCREMENT,
                                        `owner` varchar(55) NOT NULL,
                                        `imported` int NOT NULL DEFAULT '0',
                                        PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


CREATE TABLE `import_partner_credits` (
                                          `id` int NOT NULL AUTO_INCREMENT,
                                          `record_id` int DEFAULT NULL,
                                          `active_specific_date` varchar(15) DEFAULT NULL,
                                          `check_in_date` varchar(15) DEFAULT NULL,
                                          `check_out_date` varchar(15) DEFAULT NULL,
                                          `resort` int DEFAULT NULL,
                                          `source_num` int DEFAULT NULL,
                                          `source_partner_id` varchar(15) DEFAULT NULL,
                                          `Given_to_Partner_id` int DEFAULT NULL,
                                          `sourced_by_partner_on` varchar(15) DEFAULT NULL,
                                          `resort_confirmation_number` varchar(255) DEFAULT NULL,
                                          `Active` int DEFAULT NULL,
                                          `Activity` varchar(100) DEFAULT NULL,
                                          `availability` int DEFAULT NULL,
                                          `Type` varchar(100) DEFAULT NULL,
                                          `active_rental_push_date` varchar(90) DEFAULT NULL,
                                          `Unit_Type` varchar(40) DEFAULT NULL,
                                          `imported` int NOT NULL DEFAULT '0',
                                          PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `import_rooms` (
                                `id` int NOT NULL AUTO_INCREMENT,
                                `record_id` varchar(90) DEFAULT NULL,
                                `note` varchar(20) DEFAULT NULL,
                                `ResortName` varchar(61) DEFAULT NULL,
                                `Unit_Type` varchar(8) DEFAULT NULL,
                                `StartDate` varchar(11) DEFAULT NULL,
                                `Price` decimal(6,2) DEFAULT NULL,
                                `source_partner_id` varchar(8) DEFAULT NULL,
                                `active_rental_push_date` varchar(11) DEFAULT NULL,
                                `active` varchar(5) DEFAULT NULL,
                                `availability` varchar(3) DEFAULT NULL,
                                `type` varchar(15) DEFAULT NULL,
                                `imported` int NOT NULL DEFAULT '0',
                                PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;



CREATE TABLE `owner_import` (
                                `id` int NOT NULL AUTO_INCREMENT,
                                `imported` int NOT NULL DEFAULT '0',
                                `last_date` date DEFAULT NULL,
                                `last_offset` int NOT NULL,
                                PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


CREATE TABLE `owner_monetary_credits` (
                                          `id` int NOT NULL AUTO_INCREMENT,
                                          `AccountID` varchar(8) DEFAULT NULL,
                                          `Account_Name` varchar(50) DEFAULT NULL,
                                          `Business_Date` varchar(10) DEFAULT NULL,
                                          `Amount` decimal(6,2) DEFAULT NULL,
                                          `imported` int DEFAULT '0',
                                          `F` varchar(10) DEFAULT NULL,
                                          `G` varchar(10) DEFAULT NULL,
                                          `H` varchar(10) DEFAULT NULL,
                                          `I` varchar(10) DEFAULT NULL,
                                          `J` varchar(10) DEFAULT NULL,
                                          `K` varchar(10) DEFAULT NULL,
                                          `L` varchar(10) DEFAULT NULL,
                                          `M` varchar(10) DEFAULT NULL,
                                          `N` varchar(10) DEFAULT NULL,
                                          `O` varchar(10) DEFAULT NULL,
                                          `P` varchar(10) DEFAULT NULL,
                                          `Q` varchar(10) DEFAULT NULL,
                                          `R` varchar(10) DEFAULT NULL,
                                          `S` varchar(10) DEFAULT NULL,
                                          `T` varchar(10) DEFAULT NULL,
                                          `U` varchar(10) DEFAULT NULL,
                                          `V` varchar(10) DEFAULT NULL,
                                          `W` varchar(10) DEFAULT NULL,
                                          `X` varchar(10) DEFAULT NULL,
                                          `Y` varchar(10) DEFAULT NULL,
                                          `Z` varchar(10) DEFAULT NULL,
                                          PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4;



CREATE TABLE `owner_rework` (
                                `id` int NOT NULL AUTO_INCREMENT,
                                `last_offset` int NOT NULL,
                                PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



CREATE TABLE `owner_rework_owners` (
                                       `id` int NOT NULL AUTO_INCREMENT,
                                       `old_owner_id` bigint NOT NULL,
                                       `new_owner_id` int NOT NULL,
                                       `imported` int NOT NULL DEFAULT '0',
                                       PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;



CREATE TABLE `owner_rework_r` (
                                  `id` int NOT NULL AUTO_INCREMENT,
                                  `last_offset` int NOT NULL,
                                  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



CREATE TABLE `reimport_exceptions` (
                                       `id` int NOT NULL AUTO_INCREMENT,
                                       `type` varchar(255) NOT NULL,
                                       `data` text NOT NULL,
                                       `validated` int NOT NULL DEFAULT '0',
                                       PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;



CREATE TABLE `resort_import` (
                                 `id` int NOT NULL AUTO_INCREMENT,
                                 `resort` int NOT NULL,
                                 `resortResult` text NOT NULL,
                                 `alertResult` text NOT NULL,
                                 PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



CREATE TABLE `temp_import_owner` (
                                     `id` int NOT NULL AUTO_INCREMENT,
                                     `accountid` int NOT NULL,
                                     `imported` int NOT NULL DEFAULT '0',
                                     PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



CREATE TABLE `transactions_import` (
                                       `id` int NOT NULL AUTO_INCREMENT,
                                       `weekId` int DEFAULT NULL,
                                       `MemberNumber` int DEFAULT NULL,
                                       `GuestName` varchar(255) DEFAULT NULL,
                                       `Resort_Name` varchar(255) DEFAULT NULL,
                                       `Unit_Type` varchar(60) DEFAULT NULL,
                                       `Check_In_Date` varchar(10) DEFAULT NULL,
                                       `Paid` decimal(6,2) DEFAULT NULL,
                                       `Adults` int DEFAULT NULL,
                                       `Children` int DEFAULT NULL,
                                       `CPO` varchar(30) DEFAULT NULL,
                                       `WeekTransactionType` varchar(80) DEFAULT NULL,
                                       `Week_type` varchar(90) DEFAULT NULL,
                                       `Active` int DEFAULT '0',
                                       `Rental_Opening_Date` varchar(100) DEFAULT NULL,
                                       `transaction_date` varchar(200) NOT NULL,
                                       `actWeekPrice` decimal(6,2) DEFAULT NULL,
                                       `actcpoFee` decimal(4,2) DEFAULT NULL,
                                       `actextensionFee` decimal(5,2) DEFAULT NULL,
                                       `actguestFee` decimal(5,2) DEFAULT NULL,
                                       `actupgradeFee` decimal(5,2) DEFAULT NULL,
                                       `acttax` decimal(5,2) DEFAULT NULL,
                                       `actlatedeposit` decimal(4,2) DEFAULT NULL,
                                       `imported` int DEFAULT '0',
                                       PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4;



CREATE TABLE `transactions_import_owner` (
                                             `id` int DEFAULT NULL,
                                             `weekId` int DEFAULT NULL,
                                             `MemberNumber` int DEFAULT NULL,
                                             `GuestName` varchar(35) DEFAULT NULL,
                                             `Resort_Name` varchar(31) DEFAULT NULL,
                                             `Unit_Type` varchar(4) DEFAULT NULL,
                                             `Check_In_Date` varchar(10) DEFAULT NULL,
                                             `Paid` decimal(6,2) DEFAULT NULL,
                                             `Adults` int DEFAULT NULL,
                                             `Children` int DEFAULT NULL,
                                             `CPO` varchar(3) DEFAULT NULL,
                                             `WeekTransactionType` varchar(8) DEFAULT NULL,
                                             `Week_type` varchar(9) DEFAULT NULL,
                                             `Active` int DEFAULT NULL,
                                             `Rental_Opening_Date` varchar(10) DEFAULT NULL,
                                             `transaction_date` varchar(10) DEFAULT NULL,
                                             `actWeekPrice` decimal(5,2) DEFAULT NULL,
                                             `actcpoFee` int DEFAULT NULL,
                                             `actextensionFee` int DEFAULT NULL,
                                             `actguestFee` int DEFAULT NULL,
                                             `actupgradeFee` int DEFAULT NULL,
                                             `acttax` decimal(5,2) DEFAULT NULL,
                                             `actlatedeposit` int DEFAULT NULL,
                                             `imported` int NOT NULL DEFAULT '0',
                                             `otherid` int NOT NULL AUTO_INCREMENT,
                                             PRIMARY KEY (`otherid`),
                                             KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;






CREATE TABLE `transactions_import_two` (
                                           `id` int NOT NULL AUTO_INCREMENT,
                                           `weekId` int DEFAULT NULL,
                                           `MemberNumber` int DEFAULT NULL,
                                           `MemberName` varchar(10) DEFAULT NULL,
                                           `GuestName` varchar(100) DEFAULT NULL,
                                           `Resort_Name` varchar(61) DEFAULT NULL,
                                           `Unit_Type` varchar(8) DEFAULT NULL,
                                           `Check_In_Date` varchar(10) DEFAULT NULL,
                                           `Paid` decimal(6,2) DEFAULT NULL,
                                           `Adults` int DEFAULT NULL,
                                           `Children` int DEFAULT NULL,
                                          `CPO` varchar(3) DEFAULT NULL,
                                           `WeekTransactionType` varchar(8) DEFAULT NULL,
                                           `Week_type` varchar(9) DEFAULT NULL,
                                           `Active` varchar(5) DEFAULT NULL,
                                           `Rental_Opening_Date` varchar(10) DEFAULT NULL,
                                           `transaction_date` varchar(10) DEFAULT NULL,
                                           `actWeekPrice` decimal(6,2) DEFAULT NULL,
                                           `actcpoFee` int DEFAULT NULL,
                                           `actextensionFee` int DEFAULT NULL,
                                           `actguestFee` int DEFAULT NULL,
                                           `actupgradeFee` int DEFAULT NULL,
                                           `acttax` decimal(5,2) DEFAULT NULL,
                                           `actlatedeposit` int DEFAULT NULL,
                                           `deposit used` int DEFAULT NULL,
                                           `imported` int NOT NULL DEFAULT '0',
                                           PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;



CREATE TABLE `vest_rework_users` (
                                     `id` int NOT NULL AUTO_INCREMENT,
                                     `old_id` bigint NOT NULL,
                                     `new_id` varchar(20) NOT NULL,
                                     PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


CREATE TABLE `wp_missing_resorts` (
                                      `id` int NOT NULL AUTO_INCREMENT,
                                      `resort` varchar(255) NOT NULL,
                                      PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;





CREATE TABLE `wp_owner_spi_error` (
                                      `id` int NOT NULL AUTO_INCREMENT,
                                      `owner_id` int NOT NULL,
                                      `data` text,
                                      `updated_at` datetime DEFAULT NULL,
                                      PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



CREATE TABLE `wp_refresh_removed` (
                                      `id` int NOT NULL AUTO_INCREMENT,
                                      `removed` text NOT NULL,
                                      `type` varchar(255) NOT NULL DEFAULT 'rental',
                                      `datetime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                                      PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



CREATE TABLE `wp_refresh_to_remove` (
                                        `id` int NOT NULL AUTO_INCREMENT,
                                        `session` varchar(255) NOT NULL,
                                        `weeks_added` text NOT NULL,
                                        `weeks_all` text NOT NULL,
                                        PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



ALTER TABLE `wp_gpxCREmails`
    ADD COLUMN `sfData` TEXT NOT NULL AFTER `datetime`,
CHANGE COLUMN `id` `id` INT NOT NULL AUTO_INCREMENT ,
ADD PRIMARY KEY (`id`);



ALTER TABLE `wp_augmented_price`
    CHANGE COLUMN `id` `id` MEDIUMINT NOT NULL AUTO_INCREMENT ,
    ADD PRIMARY KEY (`id`);



ALTER TABLE `wp_credit_modification`
    CHANGE COLUMN `id` `id` INT NOT NULL AUTO_INCREMENT,
    ADD PRIMARY KEY (`id`);

ALTER TABLE `wp_daeCountry`
    CHANGE COLUMN `id` `id` MEDIUMINT NOT NULL AUTO_INCREMENT ;


UPDATE `wp_daeMembers`
SET
    JoinedDate = '2022-02-02',
    ModifyDate = '2022-02-02',
    ClosedDate = '2022-02-02';


ALTER TABLE `wp_daeMembers`
    CHANGE COLUMN `id` `id` MEDIUMINT NOT NULL AUTO_INCREMENT ,
    CHANGE COLUMN `JoinedDate` `JoinedDate` DATETIME NULL ,
    CHANGE COLUMN `ModifyDate` `ModifyDate` DATETIME NULL ,
    CHANGE COLUMN `ClosedDate` `ClosedDate` DATETIME NULL ,
    ADD PRIMARY KEY (`id`);

UPDATE `wp_daeMembers`
SET
    JoinedDate = null,
    ModifyDate = null,
    ClosedDate = null;




ALTER TABLE `wp_daeRefresh`
    CHANGE COLUMN `datetime` `datetime` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
CHANGE COLUMN `id` `id` int NOT NULL AUTO_INCREMENT ,
ADD PRIMARY KEY (`id`);


ALTER TABLE `wp_gpr_smartbar_hide`
    CHANGE COLUMN `id` `id` MEDIUMINT NOT NULL AUTO_INCREMENT ,
    CHANGE COLUMN `time` `time` DATETIME NULL ,
    CHANGE COLUMN `user_ip` `user_ip` VARCHAR(55) NULL ,
    ADD PRIMARY KEY (`id`);


ALTER TABLE `wp_gpxAutoCoupon`
    CHANGE COLUMN `id` `id` int NOT NULL AUTO_INCREMENT ,
    ADD PRIMARY KEY (`id`);


ALTER TABLE `wp_gpxCategory`
    CHANGE COLUMN `id` `id` MEDIUMINT NOT NULL AUTO_INCREMENT ;


ALTER TABLE `gpx_staging`.`wp_gpxDepostOnExchange`
    CHANGE COLUMN `id` `id` int NOT NULL AUTO_INCREMENT ,
    CHANGE COLUMN `data` `data` TEXT NULL ,
    ADD PRIMARY KEY (`id`);








ALTER TABLE `wp_gpxMemberSearch`
    CHANGE COLUMN `id` `id` INT NOT NULL AUTO_INCREMENT ,
    CHANGE COLUMN `userID` `userID` INT NULL ,
    CHANGE COLUMN `sessionID` `sessionID` VARCHAR(255) NULL ,
    CHANGE COLUMN `cartID` `cartID` VARCHAR(255) NULL ,
    CHANGE COLUMN `data` `data` LONGTEXT NULL ,
    ADD PRIMARY KEY (`id`);



ALTER TABLE `wp_gpxOwnerImport`
    CHANGE COLUMN `id` `id` INT NOT NULL AUTO_INCREMENT ,
    ADD PRIMARY KEY (`id`);



ALTER TABLE `wp_gpxTaxAudit`
    ADD COLUMN `cancelled` TEXT NULL AFTER `baseAmount`,
CHANGE COLUMN `id` `id` INT NOT NULL AUTO_INCREMENT ,
ADD PRIMARY KEY (`id`);



ALTER TABLE `wp_gpxTaxes`
    CHANGE COLUMN `ID` `ID` INT NOT NULL AUTO_INCREMENT ,
    ADD PRIMARY KEY (`id`);




CREATE TABLE `wp_gpx_import_account_credit` (
                                                `id` int NOT NULL AUTO_INCREMENT,
                                                `account` int NOT NULL,
                                                `business_date` varchar(20) NOT NULL,
                                                `amount` varchar(10) NOT NULL,
                                                `is_added` int DEFAULT '0',
                                                PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


ALTER TABLE `wp_owner`
    CHANGE COLUMN `id` `id` INT NOT NULL AUTO_INCREMENT ,
    ADD PRIMARY KEY (`id`);




ALTER TABLE `wp_owner_interval`
    CHANGE COLUMN `ownerID` `ownerID` INT NOT NULL ;


ALTER TABLE `wp_ownership`
    CHANGE COLUMN `id` `id` INT NOT NULL AUTO_INCREMENT ,
    ADD PRIMARY KEY (`id`);


ALTER TABLE `wp_partner_debit_balance`
    CHANGE COLUMN `id` `id` INT NOT NULL AUTO_INCREMENT ,
    ADD PRIMARY KEY (`id`);




ALTER TABLE `wp_room`
    CHANGE COLUMN `booked_status` `booked_status` VARCHAR(255) NULL ;
