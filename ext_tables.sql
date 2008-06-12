#
# Table structure for table "tx_mhbranchenbuch_firmen"
#
CREATE TABLE tx_mhbranchenbuch_firmen (
  uid int(11) auto_increment,
  pid int(11) default '0',
  tstamp int(11) default '0',
  crdate int(11) default '0',
  cruser_id int(11) default '0',
  sorting int(10) default '0',
  deleted tinyint(4) default '0',
  hidden tinyint(4) default '0',
  starttime int(11) default '0',
  endtime int(11) default '0',
  kategorie blob,
  firma tinytext,
  forename tinytext,
  lastname tinytext,
  typ int(11) default '0',
  adresse text,
  telefon tinytext,
  fax tinytext,
  link tinytext,
  email tinytext,
  custom1 tinytext,
  custom2 tinytext,
  custom3 tinytext,
  bild blob,
  keywords text,
  handy tinytext,
  video tinytext,
  detail text,
  bundesland blob,
  landkreis blob,
  ort blob,
  hit_count int(11) default '0',
  job tinyint(1) default '0',
  map_lng tinytext NOT NULL,
  map_lat tinytext NOT NULL,
  PRIMARY KEY (uid),
  KEY parent (pid)
);


#
# Table structure for table "tx_mhbranchenbuch_kategorien"
#
CREATE TABLE tx_mhbranchenbuch_kategorien (
  uid int(11) auto_increment,
  pid int(11) default '0',
  tstamp int(11) default '0',
  crdate int(11) default '0',
  cruser_id int(11) default '0',
  root_uid int(11) default '0',
  sorting int(10) default '0',
  deleted tinyint(4) default '0',
  hidden tinyint(4) default '0',
  fe_group int(11) default '0',
  name tinytext,
  image blob,
  description text,
  PRIMARY KEY (uid),
  KEY parent (pid)
);


#
# Table structure for table "tx_mhbranchenbuch_bundesland"
#
CREATE TABLE tx_mhbranchenbuch_bundesland (
  uid int(11) auto_increment,
  pid int(11) default '0',
  tstamp int(11) default '0',
  crdate int(11) default '0',
  cruser_id int(11) default '0',
  t3ver_oid int(11) default '0',
  t3ver_id int(11) default '0',
  t3ver_wsid int(11) default '0',
  t3ver_label varchar(30) default '',
  t3ver_state tinyint(4) default '0',
  t3ver_stage tinyint(4) default '0',
  t3ver_count int(11) default '0',
  t3ver_tstamp int(11) default '0',
  t3_origuid int(11) default '0',
  sys_language_uid int(11) default '0',
  l18n_parent int(11) default '0',
  l18n_diffsource mediumblob,
  deleted tinyint(4) default '0',
  hidden tinyint(4) default '0',
  name tinytext,
  map_lng tinytext NOT NULL,
  map_lat tinytext NOT NULL,
  PRIMARY KEY (uid),
  KEY parent (pid),
  KEY t3ver_oid (t3ver_oid,t3ver_wsid)
);


#
# Table structure for table "tx_mhbranchenbuch_landkreis"
#
CREATE TABLE tx_mhbranchenbuch_landkreis (
  uid int(11) auto_increment,
  pid int(11) default '0',
  tstamp int(11) default '0',
  crdate int(11) default '0',
  cruser_id int(11) default '0',
  deleted tinyint(4) default '0',
  hidden tinyint(4) default '0',
  bundesland blob,
  name tinytext,
  detail text,
  map_lng tinytext NOT NULL,
  map_lat tinytext NOT NULL,
  PRIMARY KEY (uid),
  KEY parent (pid)
);


#
# Table structure for table "tx_mhbranchenbuch_ort"
#
CREATE TABLE tx_mhbranchenbuch_ort (
  uid int(11) auto_increment,
  pid int(11) default '0',
  tstamp int(11) default '0',
  crdate int(11) default '0',
  cruser_id int(11) default '0',
  deleted tinyint(4) default '0',
  hidden tinyint(4) default '0',
  landkreis blob,
  name tinytext,
  zip int(11) default '0',
  detail text,
  map_lng tinytext NOT NULL,
  map_lat tinytext NOT NULL,
  PRIMARY KEY (uid),
  KEY parent (pid)
);


#
# Table structure for table "tx_mhbranchenbuch_ip"
#
CREATE TABLE tx_mhbranchenbuch_ip (
  uid int(11) auto_increment,
  pid int(11) default '0',
  tstamp int(11) default '0',
  crdate int(11) default '0',
  cruser_id int(11) default '0',
  deleted tinyint(4) default '0',
  hidden tinyint(4) default '0',
  fid int(11) default '0',
  ip tinytext,
  logdate date default '0000-00-00',
  PRIMARY KEY (uid),
  KEY parent (pid)
);
