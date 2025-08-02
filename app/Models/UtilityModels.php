<?php

namespace App\Models;

use CodeIgniter\Model;
use Exception;

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
            $this->db->transStart();

            $now        = date('Y-m-d H:i:s');
            $cutoffDate = date('Y-m-d H:i:s', strtotime('-1 day', strtotime($now)));

            // Count entries to be deleted
            $query = $this->db->table('aggro_log')
                ->where('log_date <', $cutoffDate)
                ->get();

            if ($query === false) {
                throw new Exception('Failed to query old log entries');
            }

            $update = count($query->getResultArray());

            // Delete old entries
            $result = $this->db->table('aggro_log')
                ->where('log_date <', $cutoffDate)
                ->delete();

            if ($result === false) {
                throw new Exception('Failed to delete old log entries');
            }

            // Note: OPTIMIZE TABLE may not be necessary for every cleanup
            // and can be resource intensive on large tables
            $cleanup = 'OPTIMIZE TABLE aggro_log';
            $this->db->query($cleanup);

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                log_message('error', 'Transaction failed in cleanLog');

                return false;
            }

            $message = $update . ' log entries, older than 1 day deleted.';
            $this->sendLog($message);

            return true;
        } catch (Exception $e) {
            log_message('error', 'Exception in cleanLog: ' . $e->getMessage());

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
        } catch (Exception $e) {
            // Don't use log_message here to avoid infinite loop
            // Just return false silently
            return false;
        }
    }
}
