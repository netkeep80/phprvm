/*
Navicat MySQL Data Transfer

Source Server         : local
Source Server Version : 50610
Source Host           : localhost:3306
Source Database       : wordpress

Target Server Type    : MYSQL
Target Server Version : 50610
File Encoding         : 65001

Date: 2013-11-15 08:18:59
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for `rmodel`
-- ----------------------------
DROP TABLE IF EXISTS `rmodel`;
CREATE TABLE `rmodel` (
  `Ent` int(11) NOT NULL COMMENT 'идентификатор сущности',
  `Sub` int(11) NOT NULL DEFAULT '0' COMMENT 'идентификатор субъекта',
  `Rel` int(11) NOT NULL DEFAULT '0' COMMENT 'идентификатор отношения',
  `Obj` int(11) NOT NULL DEFAULT '0' COMMENT 'идентификатор объекта',
  `Val` text COMMENT 'кэш значения сущности',
  `EntName` text COMMENT 'Кэш имени сущности',
  `EntDesc` text,
  PRIMARY KEY (`Ent`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of rmodel
-- ----------------------------
INSERT INTO `rmodel` VALUES ('0', '0', '0', '0', '$Val = $Ent->View( $Ent )->Val;', 'Ent', 'Возвращает сущность - контекст исполнения');
INSERT INTO `rmodel` VALUES ('1', '0', '0', '1', '$Val .= $Ent->View( $Sub )->Val;', 'Sub', null);
INSERT INTO `rmodel` VALUES ('2', '0', '0', '2', '$Val .= $Ent->View( $Rel )->Val;', 'Rel', null);
INSERT INTO `rmodel` VALUES ('3', '0', '0', '3', '$Val .= $Ent->View( $Obj )->Val;', 'Obj', null);
INSERT INTO `rmodel` VALUES ('4', '0', '0', '4', '$Val .= $Ent->Val;', 'Val', null);
INSERT INTO `rmodel` VALUES ('5', '0', '0', '5', '$Val = \'echo \"\';\r\n$Val .= $Obj->EntName;\r\n$Val .= \'\";\';', 'EntName', null);
INSERT INTO `rmodel` VALUES ('6', '0', '0', '6', '$Val = \'echo \"\';\r\n$Val .= $Obj->EntDesc;\r\n$Val .= \'\";\';', 'EntDesc', 'Возвращает описание сущности');
INSERT INTO `rmodel` VALUES ('100', '0', '0', '0', '$Val .= $Ent->View( $Sub )->Val;\r\n$Val .= \'; \';\r\n$Val .= $Ent->View( $Obj )->Val;', '; ', null);
INSERT INTO `rmodel` VALUES ('101', '1', '100', '3', null, '$Sub; $Obj', null);
INSERT INTO `rmodel` VALUES ('200', '200', '200', '200', '$tmp =  $this->View( $this )->Val;\r\n$Val = \'?><h2><?\' . $tmp . \'?></h2><?\';', 'h2', null);
INSERT INTO `rmodel` VALUES ('201', '201', '201', '201', '$tmp =  $this->View( $this )->Val;\r\n$Val = \'?><span style=\"color:#00ff00\"><?\' . $tmp .\'?></span><?\';\r\n', 'green', 'Надпись зелёного цвета');
INSERT INTO `rmodel` VALUES ('1000', '1005', '100', '1002', null, 'title1;a;b', null);
INSERT INTO `rmodel` VALUES ('1001', '1001', '5', '1001', null, 'a', null);
INSERT INTO `rmodel` VALUES ('1002', '1002', '5', '1002', null, 'b', null);
INSERT INTO `rmodel` VALUES ('1003', '1003', '5', '1003', null, 'c', null);
INSERT INTO `rmodel` VALUES ('1004', '1000', '100', '1003', null, 'title1; a; b; c', null);
INSERT INTO `rmodel` VALUES ('1005', '2000', '100', '1001', null, 'title1; a', null);
INSERT INTO `rmodel` VALUES ('2000', '2000', '4001', '1004', null, 'title1', null);
INSERT INTO `rmodel` VALUES ('3000', '3000', '3000', '3000', '?><div class=\"rmodel\" style=\"margin:10px;\">\r\n <h2>New entity creation</h2>\r\n <form id=\"myForm\" name=\"myForm\" onsubmit=\"return false;\">\r\n  <fieldset>\r\n   <b>Id-------></b><input type=\"text\" name=\"EntId\" id=\"EntId\" value=\"\" placeholder=\"\" /><br /><br />\r\n   <b>Sub------></b><input type=\"text\" name=\"SubId\" id=\"SubId\" value=\"\" placeholder=\"\" /><br /><br />\r\n   <b>Rel------></b><input type=\"text\" name=\"RelId\" id=\"RelId\" value=\"\" placeholder=\"\" /><br /><br />\r\n   <b>Obj------></b><input type=\"text\" name=\"ObjId\" id=\"ObjId\" value=\"\" placeholder=\"\" /><br /><br />\r\n   <b>Value-></b><input type=\"text\" name=\"EntValue\" id=\"EntValue\" value=\"\" placeholder=\"\" /><br /><br />\r\n   <b>Name--></b><input type=\"text\" name=\"EntName\" id=\"EntName\" value=\"\" placeholder=\"\" /><br /><br />\r\n   <button id=\"AddEnt\">Add Entity</button>\r\n  </fieldset>\r\n </form>\r\n <script type=\"text/javascript\" src=\"/js/jquery.min.js?1017-01\"></script>\r\n <script type=\"text/javascript\" src=\"/js/example.ui.js?1017-01\" ></script>\r\n</div>', 'New entity creation', null);
INSERT INTO `rmodel` VALUES ('4000', '200', '101', '201', '', 'h2; green', null);
INSERT INTO `rmodel` VALUES ('4001', '2000', '100', '4000', null, 'title1; h2; green', null);
INSERT INTO `rmodel` VALUES ('4002', '1001', '100', '1002', null, 'a; b', null);
INSERT INTO `rmodel` VALUES ('4003', '4003', '200', '1001', null, 'h2(a)', null);
INSERT INTO `rmodel` VALUES ('4004', '4004', '4000', '3000', null, null, null);
