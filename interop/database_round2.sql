# phpMyAdmin MySQL-Dump
# version 2.2.5
# http://phpwizard.net/phpMyAdmin/
# http://phpmyadmin.sourceforge.net/ (download page)
#
# Host: localhost
# Generation Time: Mar 30, 2002 at 02:39 PM
# Server version: 3.23.49
# PHP Version: 4.3.0-dev
# Database : `interop`
# --------------------------------------------------------

#
# Table structure for table `endpoints`
#

CREATE TABLE endpoints (
  id int(11) NOT NULL auto_increment,
  endpointName varchar(50) NOT NULL default '',
  endpointURL varchar(255) NOT NULL default '',
  wsdlURL varchar(255) NOT NULL default '',
  class varchar(20) NOT NULL default '',
  status int(11) NOT NULL default '1',
  PRIMARY KEY  (id)
) TYPE=MyISAM;
# --------------------------------------------------------

#
# Table structure for table `results`
#

CREATE TABLE results (
  id int(11) NOT NULL auto_increment,
  endpoint int(11) NOT NULL default '0',
  stamp int(11) NOT NULL default '0',
  class varchar(10) NOT NULL default '',
  type varchar(10) default NULL,
  wsdl int(11) NOT NULL default '0',
  function varchar(255) NOT NULL default '',
  result varchar(25) NOT NULL default '',
  error text,
  wire text NOT NULL,
  PRIMARY KEY  (id)
) TYPE=MyISAM;

    

