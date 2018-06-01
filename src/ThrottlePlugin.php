<?php

namespace Sinevia\Plugins;

class ThrottlePlugin {

    /**
     * @var \Sinevia\SqlDb
     */
    protected static $db = null;

    /**
     * @var string
     */
    protected static $table = null;

    public static function configure($options) {
        $pdo = isset($options['pdo']) ? $options['pdo'] : null;
        self::$table = isset($options['table']) ? $options['table'] : 'snv_plugin_throttle';
        if ($pdo == null) {
            throw new \RuntimeException('Required option "pdo" is missing');
        }
        self::$db = new \Sinevia\SqlDb();
        self::$db->setPdo($pdo);
        if (self::$db->table(self::$table)->exists() == false) {
            self::install();
        }
    }
    
    public static function clear($resource, $who) {
        self::$db->table(self::$table)
                ->where('Resource', '=', $resource)
                ->where('Who', '=', $who)
                ->delete();
    }

    public static function install() {
        if (self::$db->table(self::$table)->exists() == true) {
            return true;
        }
        self::$db->table(self::$table)
                ->column('Id', 'INTEGER')
                ->column('Status', 'STRING')
                ->column('Resource', 'STRING')
                ->column('Who', 'STRING')
                ->column('AttemptedAt', 'DATETIME')
                ->create();
    }

    public static function logAttempt($resource, $who) {
        self::$db->table(self::$table)->insert(array(
            'Id' => time(),
            'Status' => 'Active',
            'Resource' => $resource,
            'Who' => $who,
            'AttemptedAt' => date('Y-m-d H:i:s'),
        ));
    }

    public static function mayAttempt($resource, $who, $maxAttempts = 5, $lockOutSeconds = 600) {
        $lastAttempt = self::$db->table(self::$table)
                ->where('Status', '=', 'Active')
                ->where('Resource', '=', $resource)
                ->where('Who', '=', $who)
                ->orderBy('AttemptedAt', 'DESC')
                ->selectOne();
        
        if ($lastAttempt != null) {
            $lastAttemptAt = strtotime($lastAttempt['AttemptedAt']);
            if (time() - $lastAttemptAt > $lockOutSeconds) {
                self::$db->table(self::$table)
                        ->where('Status', '=', 'Active')
                        ->where('Resource', '=', $resource)
                        ->where('Who', '=', $who)
                        ->update(['Status' => 'Deleted']);
            }
        }
        
        $attempts = self::$db->table(self::$table)
                ->where('Status', '=', 'Active')
                ->where('Resource', '=', $resource)
                ->where('Who', '=', $who)
                ->select();
        $attemptsCount = count($attempts);
        
        if ($attemptsCount < $maxAttempts) {
            return true;
        }
        
        return false;
    }

    public static function getCurrentUserIp() {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return $ip;
    }

}
