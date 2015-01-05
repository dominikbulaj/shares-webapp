/*
   Domains table where domains usage is count.

   Queries handled by \Webit\Domains class
*/
CREATE TABLE `shares.domains`
(
  ID INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
  domain VARCHAR(255) DEFAULT '' NOT NULL
);
CREATE INDEX domain ON `shares.domains` (domain);

/*
  URLs table. Stores information about unique URL that was checked.
  If URL was checked before - it will update counter for this row.

  Queries handled by \Webit\Urls class
 */
CREATE TABLE `shares.urls`
(
  ID INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
  url VARCHAR(2048) DEFAULT '' NOT NULL,
  urlhash VARCHAR(32) DEFAULT '' NOT NULL,
  domain_id INT DEFAULT 0 NOT NULL,
  counts LONGTEXT,
  added DATETIME DEFAULT '0000-00-00 00:00:00' NOT NULL,
  added_ip VARCHAR(20) DEFAULT '' NOT NULL,
  counter INT DEFAULT 0 NOT NULL,
  last_search DATETIME NOT NULL
);
CREATE INDEX domain_id ON `shares.urls` (domain_id);
CREATE INDEX last_search_index ON `shares.urls` (last_search);
CREATE INDEX urlhash ON `shares.urls` (urlhash);