<?php 
require 'vendor/autoload.php';
require 'libs/NotORM.php'; 
//membuat dan mengkonfigurasi slim app
$app = new \Slim\app;

// konfigurasi database
$dbhost = 'localhost';
$dbuser = 'root';
$dbpass = '';
$dbname = 'cenggoo';
$dbmethod = 'mysql:dbname=';

$dsn = $dbmethod.$dbname;
$pdo = new PDO($dsn, $dbuser, $dbpass);
$db  = new NotORM($pdo);

// Mendapatkan semua data produk 
$app ->get('hasilsensor', function() use($app, $db){
    foreach($db->pengukuran() as $data){
        $produk['semuasensor'][] = array(
            'id_sungai' => $data['id_sungai'],
            'tgl' => $data['tgl'],
            'suhuair' => $data['suhuair'],
            'dhl' => $data['dhl']
            );
    }
    echo json_encode($hasilsensor);
});

//run App
$app->run();