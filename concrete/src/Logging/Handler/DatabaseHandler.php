<?php
namespace Concrete\Core\Logging\Handler;

use Monolog\Handler\AbstractProcessingHandler;
use Database;
use User;

class DatabaseHandler extends AbstractProcessingHandler
{
    protected $initialized;
    private $statement;

    protected function write(array $record)
    {
        if (!$this->initialized) {
            $this->initialize();
        }

        $u = new User();
        $uID = ($u->isRegistered()) ? $u->getUserID() : 0;

        $this->statement->execute(
            array(
                'channel' => $record['channel'],
                'level' => $record['level'],
                'message' => $record['formatted'],
                'time' => $record['datetime']->format('U'),
                'uID' => $uID,
            )
        );
    }

    private function initialize()
    {
        $db = Database::get();

        $this->statement = $db->prepare(
            'INSERT INTO Logs (channel, level, message, time, uID) VALUES (:channel, :level, :message, :time, :uID)'
        );

        $this->initialized = true;
    }


    /**
     * Clears all log entries. Requires the database handler.
     */
    public static function clearAll()
    {
        $db = Database::get();
        $db->Execute('delete from Logs');
    }

    /**
     * Clears log entries by channel. Requires the database handler.
     */
    public static function clearByChannel($channel)
    {
        $db = Database::get();
        $db->delete('Logs', array('channel' => $channel));
    }


}
