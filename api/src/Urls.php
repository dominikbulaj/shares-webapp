<?php
/**
 *
 * @author Dominik Bułaj <dominik@bulaj.com>
 */

namespace Webit;

class Urls
{
    const TABLE = '`shares.urls`';

    /**
     * @var \Doctrine\DBAL\Connection
     */
    protected $_db;

    /**
     * @param \Doctrine\DBAL\Connection $db
     */
    public function __construct(\Doctrine\DBAL\Connection $db)
    {
        $this->_db = $db;
    }

    /**
     * Get shares for URL if queried in last x minutes.
     * Works as kind of cache - disallowing to often query social network services
     *
     * @param $url
     * @param int $interval
     * @return bool|mixed
     */
    public function getShares($url, $interval = 5)
    {
        $urlHash = $this->_getUrlHash($url);

        $sql = "SELECT counts FROM ".self::TABLE." WHERE urlhash=? AND last_search > NOW() - INTERVAL ? MINUTE";
        $counts = $this->_db->fetchColumn($sql, [$urlHash, intval($interval)]);

        if ($counts) {
            return json_decode($counts);
        }

        return false;
    }

    /**
     * Get last searched URLs
     *
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getLastSearched($limit = 10, $offset = 0)
    {
        $sql = "SELECT url, DATE_FORMAT(last_search, '%Y%m%dT%H%i%s') as time, counter as count
FROM " . self::TABLE . "
ORDER BY last_search DESC
LIMIT {$offset},{$limit}";

        return $this->_db->fetchAll($sql, array(intval($offset), intval($limit)));
    }

    /**
     * @param $url
     * @param $counts
     * @return string
     */
    public function addUrl($url, $counts)
    {
        $this->_db->insert(self::TABLE, [
            'url'         => $url,
            'urlhash'     => $this->_getUrlHash($url),
            'counts'      => json_encode($counts),
            'added'       => new \DateTime(),
            'added_ip'    => $_SERVER['REMOTE_ADDR'],
            'counter'     => 0,
            'last_search' => new \DateTime(),
        ], [
            \PDO::PARAM_STR,
            \PDO::PARAM_STR,
            \PDO::PARAM_STR,
            'datetime',
            \PDO::PARAM_STR,
            \PDO::PARAM_INT,
            'datetime',
        ]);
        $id = $this->_db->lastInsertId();

        return $id;
    }

    /**
     * Helper method to generate URL hash
     *
     * @param string $url
     * @return string
     */
    protected function _getUrlHash($url)
    {
        return md5(strtolower($url));
    }

    /**
     * Increment url counter
     *
     * @param $url
     * @return int
     * @throws \Doctrine\DBAL\DBALException
     */
    public function countUrl($url)
    {
        $urlHash = $this->_getUrlHash($url);

        return $this->_db->executeUpdate("UPDATE " .self::TABLE. " SET counter=counter+1, last_search=NOW() WHERE urlhash=?", array($urlHash));
    }

}