<?php
/**
 *
 * @author Dominik Bułaj <dominik@bulaj.com>
 */

namespace Webit;

class Domains
{
    const TABLE = '`shares.domains`';
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
     * Get most searched domains
     *
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getMostSearched($limit = 10, $offset = 0)
    {
        $sql = "SELECT d.domain, d.counter as count,
(SELECT last_search FROM " . Urls::TABLE . " WHERE domain_id = d.ID ORDER BY last_search DESC LIMIT 1) as time
 FROM " . self::TABLE . " d
ORDER BY d.counter DESC
LIMIT {$offset},{$limit}";

        return $this->_db->fetchAll($sql, array(intval($offset), intval($limit)));
    }

    /**
     * Get domainID
     *
     * @param $url
     * @return mixed|string
     */
    public function getDomainId($url)
    {
        $host = parse_url($url, PHP_URL_HOST);
        $host = preg_replace('/^(www\.)/', '', $host);

        $sql = "SELECT ID FROM " . self::TABLE . " WHERE domain=?";
        $id = $this->_db->fetchColumn($sql, [$host]);

        // we need to add new domain
        if (!$id) {
            $this->_db->insert(self::TABLE, ['domain' => $host, 'counter' => 0]);
            $id = $this->_db->lastInsertId();
        }

        return $id;
    }

    /**
     * Increment domain counter
     *
     * @param $url
     * @return int
     * @throws \Doctrine\DBAL\DBALException
     */
    public function countDomain($url)
    {
        $domainId = $this->getDomainId($url);
        return $this->_db->executeUpdate("UPDATE " .self::TABLE. " SET counter=counter+1 WHERE ID=?", array($domainId));
    }
}