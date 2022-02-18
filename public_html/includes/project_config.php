<?




date_default_timezone_set('Europe/Moscow'); // GMT+3
ini_set('upload_max_filesize', '20M');
ini_set('display_errors', 'on');



// version for css/js files - force refresh cache
define('Version', '1023-01');

define('DEBUG', 0);


$app_dir = dirname(dirname(__FILE__));
$app_dir = preg_replace('/\\\/i', '/', $app_dir);
define('DOC_ROOT',$app_dir);
unset($app_dir);



define('SITE_NAME', 'RMDesigner');
define('SITE_TITLE', 'Дизайнер Модели Отношений');


define('TempDir', DOC_ROOT . '/temp');
define('LogsDir', DOC_ROOT . '/logs');


define('SITE_DOMAIN', 'somesite.com');
define('SECURE_PREFIX', 'http://');
define('PUBLIC_PREFIX', 'http://');
define('SUBDOMAIN', 'www');
	

define('EMAIL_FROM', 'no-reply@' . SITE_DOMAIN);
define('EMAIL_FROM_NAME', 'no-reply@' . SITE_DOMAIN); 

define('CUSTOM_EMAIL_DEBUG', DEBUG);
define('CUSTOM_EMAIL_MAILER', 'smtp');
define('EMAIL_SMTP_PORT', 25);
define('CUSTOM_EMAIL_SMTPAUTH', false);
define('CUSTOM_EMAIL_SMTP_USER', '');
define('CUSTOM_EMAIL_SMTP_PASSWORD', '');
define('CUSTOM_EMAIL_SMTP_SERVER', "127.0.0.1");



define('ErrorHandlerEmailFrom', 'no-reply@' . SITE_DOMAIN);
define('ErrorHandlerEmailFromName', SITE_NAME);
define('ErrorHandlerEmailTo', 'some.email@test.com'); // put here your email address
define('ErrorHandlerEmailCc', null);
define('ErrorHandlerEmailBcc', null);


define("SQL_HOST","localhost");
define("SQL_USER","netkeep80_rm");
define("SQL_PASS","netkeep80_rm");
define("SQL_DB","netkeep80_rm");

