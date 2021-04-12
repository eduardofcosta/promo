/*
SQLyog Community v13.1.6 (64 bit)
MySQL - 10.4.14-MariaDB : Database - promo
*********************************************************************
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
CREATE DATABASE /*!32312 IF NOT EXISTS*/`promo` /*!40100 DEFAULT CHARACTER SET latin1 */;

USE `promo`;

/*Table structure for table `tb_aeroporto` */

DROP TABLE IF EXISTS `tb_aeroporto`;

CREATE TABLE `tb_aeroporto` (
  `iata` varchar(3) NOT NULL,
  `lat` varchar(10) NOT NULL,
  `log` varchar(10) NOT NULL,
  `estado` varchar(2) NOT NULL,
  `cidade` varchar(20) NOT NULL,
  PRIMARY KEY (`iata`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

/*Table structure for table `tb_voo` */

DROP TABLE IF EXISTS `tb_voo`;

CREATE TABLE `tb_voo` (
  `voo_id` int(9) NOT NULL AUTO_INCREMENT,
  `origem` varchar(3) NOT NULL,
  `destino` varchar(3) NOT NULL,
  `distancia` float(10,2) NOT NULL,
  `menor_valor` decimal(10,2) NOT NULL,
  `aeronave` varchar(20) NOT NULL,
  `modelo` varchar(20) NOT NULL,
  `data_voo` date DEFAULT NULL,
  `url` varchar(200) NOT NULL,
  PRIMARY KEY (`voo_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;

/*Table structure for table `tb_voo_detalhe` */

DROP TABLE IF EXISTS `tb_voo_detalhe`;

CREATE TABLE `tb_voo_detalhe` (
  `voo_detalhe_id` int(9) NOT NULL AUTO_INCREMENT,
  `voo_id` int(9) DEFAULT NULL,
  `aeronave` varchar(20) DEFAULT NULL,
  `modelo` varchar(20) DEFAULT NULL,
  `saida` datetime DEFAULT NULL,
  `chegada` datetime DEFAULT NULL,
  `valor` decimal(10,2) DEFAULT NULL,
  `velocidade` varchar(6) DEFAULT NULL,
  `tempoVoo` varchar(6) DEFAULT NULL,
  `custoTarifa` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`voo_detalhe_id`),
  KEY `voo_detalhe_fk` (`voo_id`),
  CONSTRAINT `voo_detalhe_fk` FOREIGN KEY (`voo_id`) REFERENCES `tb_voo` (`voo_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
