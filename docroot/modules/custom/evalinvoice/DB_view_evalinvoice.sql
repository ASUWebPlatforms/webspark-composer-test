-- --------------------------------------------------------
-- Host:                         dbserver.live.71476dd6-db8f-422b-ad93-1da1ae95f623.drush.in
-- Server version:               10.0.23-MariaDB-log - MariaDB Server
-- Server OS:                    Linux
-- HeidiSQL Version:             12.1.0.6537
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

-- Dumping structure for view pantheon.EvalUser
DROP VIEW IF EXISTS `EvalUser`;
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE `EvalUser` (
	`Asurite` TEXT NULL COLLATE 'utf8_general_ci',
	`Project_ID` TEXT NULL COLLATE 'utf8_general_ci',
	`User_Role` VARCHAR(12) NOT NULL COLLATE 'utf8_general_ci',
	`User_Name` TEXT NULL COLLATE 'utf8_general_ci',
	`modifyDate` DATETIME NULL
) ENGINE=MyISAM;

-- Dumping structure for view pantheon.EvalUser
DROP VIEW IF EXISTS `EvalUser`;
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS `EvalUser`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `EvalUser` AS select `Eval_Billing_Information`.`Accountant_ASURITE` AS `Asurite`,`Eval_Billing_Information`.`Project_ID` AS `Project_ID`,'Accountant' AS `User_Role`,`Eval_Billing_Information`.`Accountant_Name` AS `User_Name`,`Eval_Billing_Information`.`modifyDate` AS `modifyDate` from `Eval_Billing_Information` where ((`Eval_Billing_Information`.`Accountant_ASURITE` is not null) and (`Eval_Billing_Information`.`Accountant_ASURITE` <> '')) union select `Eval_Billing_Information`.`Contact1_ASURITE` AS `Asurite`,`Eval_Billing_Information`.`Project_ID` AS `Project_ID`,'Contact1' AS `User_Role`,`Eval_Billing_Information`.`Contact1_Name` AS `User_Name`,`Eval_Billing_Information`.`modifyDate` AS `modifyDate` from `Eval_Billing_Information` where ((`Eval_Billing_Information`.`Contact1_ASURITE` is not null) and (`Eval_Billing_Information`.`Contact1_ASURITE` <> '')) union select `Eval_Billing_Information`.`Contact2_ASURITE` AS `Asurite`,`Eval_Billing_Information`.`Project_ID` AS `Project_ID`,'Contact2' AS `User_Role`,`Eval_Billing_Information`.`Contact2_Name` AS `User_Name`,`Eval_Billing_Information`.`modifyDate` AS `modifyDate` from `Eval_Billing_Information` where ((`Eval_Billing_Information`.`Contact2_ASURITE` is not null) and (`Eval_Billing_Information`.`Contact2_ASURITE` <> '')) union select `Eval_Billing_Information`.`Contact3_ASURITE` AS `Asurite`,`Eval_Billing_Information`.`Project_ID` AS `Project_ID`,'Contact3' AS `User_Role`,`Eval_Billing_Information`.`Contact3_Name` AS `User_Name`,`Eval_Billing_Information`.`modifyDate` AS `modifyDate` from `Eval_Billing_Information` where ((`Eval_Billing_Information`.`Contact3_ASURITE` is not null) and (`Eval_Billing_Information`.`Contact3_ASURITE` <> '')) union select `Eval_Billing_Information`.`Contact4_ASURITE` AS `Asurite`,`Eval_Billing_Information`.`Project_ID` AS `Project_ID`,'Contact4' AS `User_Role`,`Eval_Billing_Information`.`Contact4_Name` AS `User_Name`,`Eval_Billing_Information`.`modifyDate` AS `modifyDate` from `Eval_Billing_Information` where ((`Eval_Billing_Information`.`Contact4_ASURITE` is not null) and (`Eval_Billing_Information`.`Contact4_ASURITE` <> '')) union select `Eval_Billing_Information`.`Contact5_ASURITE` AS `Asurite`,`Eval_Billing_Information`.`Project_ID` AS `Project_ID`,'Contact5' AS `User_Role`,`Eval_Billing_Information`.`Contact5_Name` AS `User_Name`,`Eval_Billing_Information`.`modifyDate` AS `modifyDate` from `Eval_Billing_Information` where ((`Eval_Billing_Information`.`Contact5_ASURITE` is not null) and (`Eval_Billing_Information`.`Contact5_ASURITE` <> '')) union select `Eval_Billing_Information`.`Contact6_ASURITE` AS `Asurite`,`Eval_Billing_Information`.`Project_ID` AS `Project_ID`,'Contact6' AS `User_Role`,`Eval_Billing_Information`.`Contact6_Name` AS `User_Name`,`Eval_Billing_Information`.`modifyDate` AS `modifyDate` from `Eval_Billing_Information` where ((`Eval_Billing_Information`.`Contact6_ASURITE` is not null) and (`Eval_Billing_Information`.`Contact6_ASURITE` <> '')) union select `EvalUser_Manager`.`Asurite` AS `Asurite`,`EvalUser_Manager`.`Project_ID` AS `Project_ID`,'UOEEEManager' AS `UOEEEManager`,`EvalUser_Manager`.`User_Name` AS `User_Name`,NULL AS `NULL` from `EvalUser_Manager`;

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
