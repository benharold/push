<?php namespace Lease317\APNS;

date_default_timezone_set('America/Indiana/Indianapolis');
error_reporting(-1);

require 'vendor/autoload.php';

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Dotenv\Dotenv;
use Illuminate\Database\Capsule\Manager as Capsule;

$dotenv = new Dotenv(__DIR__);
$dotenv->load();

$log = new Logger('APNS');
$log->pushHandler(new StreamHandler('log', Logger::DEBUG));

$env     = getenv('APNS_ENV');
$env     = constant("\\ApnsPHP_Abstract::$env");
$cert    = getenv('APNS_CERT');
$root_ca = getenv('APNS_ROOT_CA');

$db_host = getenv('DB_HOST');
$db_name = getenv('DB_NAME');
$db_user = getenv('DB_USER');
$db_pass = getenv('DB_PASS');

$capsule = new Capsule();
$capsule->addConnection([
    'host'      => $db_host,
    'driver'    => 'mysql',
    'database'  => $db_name,
    'username'  => $db_user,
    'password'  => $db_pass,
    'charset'   => 'utf8',
    'collation' => 'utf8_unicode_ci',
]);
$capsule->bootEloquent();

$server = new \ApnsPHP_Push_Server($env, $cert);
$server->setRootCertificationAuthority($root_ca);
$server->setProcesses(1);
$server->start();

$log->info('APNS server started');
while ($server->run()) {

    $errors = $server->getErrors();
    if ( ! empty($errors)) {
        foreach ($errors as $e) {
            $log->error($e);
        }
    }

    $notifications = PushNotification::where('sent', '=', 0)->get();
    foreach ($notifications as $notification) {
        $push = new \ApnsPHP_Message($notification->device_token);
        //$push->setBadge(0);
        $push->setText($notification->text);
        $push->setSound();
        $push->setCustomIdentifier($notification->id);
        //$push->setCustomProperty('key', 'value');
        $server->add($push);
        $notification->sent = 1;
        $notification->save();
    }

    sleep(10);
}
$log->info('APNS server stopped');
