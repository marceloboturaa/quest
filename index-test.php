<?php
echo 'PHP OK';
echo "\n";
echo 'Version: ' . PHP_VERSION;
echo "\n";
echo 'SAPI: ' . php_sapi_name();
echo "\n";
echo 'PDO: ' . (class_exists('PDO') ? 'yes' : 'no');
echo "\n";
echo 'PDO MySQL: ' . (extension_loaded('pdo_mysql') ? 'yes' : 'no');
