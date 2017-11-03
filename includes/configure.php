<?php
  define('HTTP_SERVER', 'http://www.innovics.centre-albert-einstein.com');
  define('HTTPS_SERVER', 'http://www.innovics.centre-albert-einstein.com');
  define('ENABLE_SSL', false);
  define('HTTP_COOKIE_DOMAIN', 'www.innovics.centre-albert-einstein.com');
  define('HTTPS_COOKIE_DOMAIN', 'www.innovics.centre-albert-einstein.com');
  define('HTTP_COOKIE_PATH', '/');
  define('HTTPS_COOKIE_PATH', '/');
  define('DIR_WS_HTTP_CATALOG', '/');
  define('DIR_WS_HTTPS_CATALOG', '/');
  define('DIR_WS_IMAGES', 'images/');
  define('DIR_WS_REPORTS', 'reports/');

  define('DIR_WS_DOWNLOAD_PUBLIC', 'pub/');
  define('DIR_FS_CATALOG', '/home/bragu/public_html/innovics/');
  define('DIR_FS_ADMIN', 'admin/');
  define('DIR_FS_WORK', '/home/bragu/public_html/innovics/includes/work/');
  define('DIR_FS_DOWNLOAD', DIR_FS_CATALOG . 'download/');
  define('DIR_FS_PARAMETERS', DIR_FS_CATALOG . 'download/parameters/');
  define('DIR_FS_WATCHDOG', DIR_FS_CATALOG . 'download/watchdogfiles/');
  define('DIR_FS_ARCHIVES', DIR_FS_CATALOG . 'download/archives/');
  define('DIR_FS_ERRORS', DIR_FS_CATALOG . 'download/errors/');
  define('DIR_FS_DOWNLOAD_PUBLIC', DIR_FS_CATALOG . 'pub/');
  define('DIR_FS_BACKUP', '/home/bragu/public_html/innovics/' . DIR_FS_ADMIN . 'backups/');
  define('DIR_FS_CACHE', DIR_FS_CATALOG . 'cache/');
  define('DIR_FS_CACHE_ADMIN', DIR_FS_CACHE . DIR_FS_ADMIN);

  define('DB_SERVER', 'localhost');
  define('DB_SERVER_USERNAME', 'bragu');
  define('DB_SERVER_PASSWORD', 'vUUN-pcBWlqH');
  define('DB_DATABASE', 'bragu_demo');
  define('DB_DATABASE_CLASS', 'mysql');
  define('DB_TABLE_PREFIX', 'delta_');
  define('USE_PCONNECT', 'false');
  define('STORE_SESSIONS', 'mysql');

  define('DB_TICKET_DATABASE', 'bragu_osti234');
?>