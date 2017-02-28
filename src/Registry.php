<?php

namespace RobLoach\LibretroNetplayRegistry;

use \PDO;

/**
 * Class Registry.
 */
class Registry
{
    /**
     * @var PDO
     */
    private $db;

    /**
     * @var \PDOStatement
     */
    private $insert;

    /**
     * @var \PDOStatement
     */
    private $select;

    /**
     * @var \PDOStatement
     */
    private $clearOldEntries;

    /**
     * Registry constructor.
     *
     * @param string $name
     */
    public function __construct($name = '.registry')
    {
        $this->db = new PDO("sqlite:$name.sqlite");
        $this->db->exec('CREATE TABLE IF NOT EXISTS registry (
            id INTEGER PRIMARY KEY,
            username TEXT,
            ip TEXT,
            port INTEGER,
            corename TEXT,
            coreversion TEXT,
            gamename TEXT,
            gamecrc TEXT,
            haspassword BOOLEAN,
            connectable BOOLEAN,
            created INTEGER
        )');

        $this->insert = $this->db->prepare('INSERT INTO
            registry (
                username,
                ip,
                port,
                corename,
                coreversion,
                gamename,
                gamecrc,
                haspassword,
                connectable,
                created
            )
            VALUES (
                :username,
                :ip,
                :port,
                :corename,
                :coreversion,
                :gamename,
                :gamecrc,
                :haspassword,
                :connectable,
                :created
            )
        ');
        $this->select = $this->db->prepare('SELECT * FROM registry');
        $this->clearOldEntries = $this->db->prepare('DELETE FROM registry where created <= :time');
        $this->updateQuery = $this->db->prepare('UPDATE registry SET
            username = :username,
            ip = :ip,
            port = :port,
            corename = :corename,
            coreversion = :coreversion,
            gamename = :gamename,
            gamecrc = :gamecrc,
            haspassword = :haspassword,
            connectable = :connectable,
            created = :created
            WHERE id = :id
        ');
        $this->clearOld();
    }

    /**
     * @param array $newEntry
     * @param bool  $throttle
     *
     * @return bool
     */
    public function insert($newEntry, $throttle = true)
    {
        if (!isset($newEntry['ip'])) {
            $newEntry['ip'] = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1';
        }
        if (!isset($newEntry['port'])) {
            $newEntry['port'] = 55435;
        }
        if (!isset($newEntry['created'])) {
            $newEntry['created'] = time();
        }
        $newEntry['haspassword'] = !empty($newEntry['haspassword']);

        $added = false;
        $entries = $this->selectAll();
        foreach ($entries as $index => $entry) {
            if ($throttle) {
                if ($newEntry['ip'] == $entry['ip'] && $newEntry['created'] - $entry['created'] < 10) {
                    $added = true;
                    break;
                }
            }

            // Update unique entries by username, IP and Port.
            if ($entry['username'] == $newEntry['username'] &&
                $entry['ip'] == $newEntry['ip'] &&
                $entry['port'] == $newEntry['port']) {
                $this->update(array_merge($entry, $newEntry));
                $added = true;
                break;
            }
        }

        if (!$added) {
            // Find if it's connectable.
            if (!isset($newEntry['connectable'])) {
                $newEntry['connectable'] = $this->isConnectable($newEntry['ip'], $newEntry['port']);
            }
            $this->insert->bindParam(':username', $newEntry['username'], PDO::PARAM_STR);
            $this->insert->bindParam(':ip', $newEntry['ip'], PDO::PARAM_STR);
            $this->insert->bindParam(':port', $newEntry['port'], PDO::PARAM_INT);
            $this->insert->bindParam(':corename', $newEntry['corename'], PDO::PARAM_STR);
            $this->insert->bindParam(':coreversion', $newEntry['coreversion'], PDO::PARAM_STR);
            $this->insert->bindParam(':gamename', $newEntry['gamename'], PDO::PARAM_STR);
            $this->insert->bindParam(':gamecrc', $newEntry['gamecrc'], PDO::PARAM_STR);
            $this->insert->bindParam(':haspassword', $newEntry['haspassword'], PDO::PARAM_BOOL);
            $this->insert->bindParam(':connectable', $newEntry['connectable'], PDO::PARAM_BOOL);
            $this->insert->bindParam(':created', $newEntry['created'], PDO::PARAM_INT);
            return $this->insert->execute();
        }
        return false;
    }

    /**
     * @param $entry
     *
     * @return bool
     */
    public function update($entry)
    {
        $this->updateQuery->bindParam(':id', $entry['id'], PDO::PARAM_INT);
        $this->updateQuery->bindParam(':username', $entry['username'], PDO::PARAM_STR);
        $this->updateQuery->bindParam(':ip', $entry['ip'], PDO::PARAM_STR);
        $this->updateQuery->bindParam(':port', $entry['port'], PDO::PARAM_INT);
        $this->updateQuery->bindParam(':corename', $entry['corename'], PDO::PARAM_STR);
        $this->updateQuery->bindParam(':coreversion', $entry['coreversion'], PDO::PARAM_STR);
        $this->updateQuery->bindParam(':gamename', $entry['gamename'], PDO::PARAM_STR);
        $this->updateQuery->bindParam(':gamecrc', $entry['gamecrc'], PDO::PARAM_STR);
        $this->updateQuery->bindParam(':haspassword', $entry['haspassword'], PDO::PARAM_BOOL);
        $this->insert->bindParam(':connectable', $newEntry['connectable'], PDO::PARAM_BOOL);
        $this->updateQuery->bindParam(':created', $entry['created'], PDO::PARAM_INT);
        return $this->updateQuery->execute();
    }

    /**
     * @param int $age
     */
    public function clearOld($age = 120)
    {
        $time = time() - $age;
        $this->clearOldEntries->bindParam(':time', $time, PDO::PARAM_INT);
        $this->clearOldEntries->execute();
    }

    /**
     * @return array
     */
    public function selectAll()
    {
        $this->select->execute();
        return $this->select->fetchAll();
    }

    public function isConnectable($ip, $port)
    {
        // Attempt to open the port.
        $fp = @fsockopen($ip, $port);
        if ($fp) {
            fclose($fp);
            return true;
        }
        return false;
    }
}
