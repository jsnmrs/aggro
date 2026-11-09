<?php

namespace App\Models;

use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\Model;

/**
 * @file
 * All utility functions.
 */

/**
 * All models for utilities.
 */
class UtilityModels extends Model
{
    /**
     * Clean logs.
     */
    public function cleanLog()
    {
        try {
            $now        = date('Y-m-d H:i:s');
            $cutoffDate = date('Y-m-d H:i:s', strtotime('-1 day', strtotime($now)));

            // Delete old entries
            $result = $this->db->table('aggro_log')
                ->where('log_date <', $cutoffDate)
                ->delete();

            if ($result === false) {
                throw new DatabaseException('Failed to delete old log entries');
            }

            // The delete reports how many entries it removed, so they
            // are never loaded just to be counted
            $update = $this->db->affectedRows();

            $message = $update . ' log entries, older than 1 day deleted.';
            $this->sendLog($message);

            return true;
        } catch (DatabaseException $e) {
            log_message('error', 'Database error in cleanLog: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Get log entries from aggro_log.
     *
     * @return array
     *               Recent log entries.
     */
    public function getLog()
    {
        $query = $this->db->table('aggro_log')
            ->orderBy('log_date', 'DESC')
            ->limit(100)
            ->get();

        if ($query === false) {
            return [];
        }

        return $query->getResult();
    }

    /**
     * Send message to aggro_log table. Typically non-error messages.
     *
     * @param string $message
     *                        Message to insert into aggro_log table.
     *
     * @return bool
     *              Message inserted into aggro_log table.
     */
    public function sendLog($message)
    {
        try {
            $data = [
                'log_date'    => date('Y-m-d H:i:s'),
                'log_message' => $message,
            ];
            $result = $this->db->table('aggro_log')->insert($data);

            return ! ($result === false);
            // Don't use log_message here to avoid infinite loop
            // Just return false silently
        } catch (DatabaseException $e) {
            // Don't use log_message here to avoid infinite loop
            // Just return false silently
            return false;
        }
    }
}
