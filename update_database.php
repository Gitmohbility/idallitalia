<?php
require_once __DIR__ . '/config/config.inc.php';

try {
    $db = Db::getInstance();
    
    // Create the missing soft_hook table
    $sql = "CREATE TABLE IF NOT EXISTS `ps_soft_hook` (
        `id_soft_hook` int(10) unsigned NOT NULL AUTO_INCREMENT,
        `name` varchar(64) NOT NULL,
        `title` varchar(64) NOT NULL,
        `description` text,
        `position` int(10) unsigned NOT NULL DEFAULT '0',
        `live_edit` tinyint(1) NOT NULL DEFAULT '0',
        PRIMARY KEY (`id_soft_hook`),
        UNIQUE KEY `name` (`name`)
    ) ENGINE=" . _MYSQL_ENGINE_ . " DEFAULT CHARSET=utf8mb4;";
    
    if (!$db->execute($sql)) {
        throw new Exception('Failed to create soft_hook table');
    }
    
    echo "Database update completed successfully!\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
