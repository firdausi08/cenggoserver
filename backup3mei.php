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

//mendefinisikan route app di home
$app-> get('/', function(){
	echo "Cenggo";
});
// Menampilkan hasil sensor
$app ->get('/hasilsensor', function($request, $response, $args) use($app, $db){
	$ph = array();
	$i = 0;
	foreach($db->pengukuran() as $data){
        /*$hasilsensor['semuasensor'][] = array(
            'id_sungai' => $data['id_sungai'],
            'waktu' => $data['waktu'],
            'suhuair' => $data['suhuair'],
			'suhuudara' => $data['suhuudara'],
            'dhl' => $data['dhl'],
			'tds' => $data['tds'],
			'salinitas' => $data['salinitas'],
			'ph' => $data['ph']
            );*/
		$id_sungai[$i]=$data['id_sungai'];
        $waktu[$i] = $data['waktu'];
        $suhuair[$i] = $data['suhuair'];
		$suhuudara[$i] = $data['suhuudara'];
        $dhl[$i] = $data['dhl'];
		$tds[$i] = $data['tds'];
		$salinitas = $data['salinitas'];
		$ph[$i] = $data['ph'];
		
		echo "Data ph ke ".$i." adalah ".$ph[$i]."<br/>";
		echo "Pada id ".$id_sungai[$i]." dengan waktu ".$waktu[$i]." memiliki suhu sebesar ".$suhuudara[$i]."<br/>";
		$i++;
    }
	
	
	//$ph = 8; $tds=1500; $tdl = 1600 ; $suhuair=27;
	
	/*function hitungPh($ph){
		$hasil = 0;
		if($ph<7.5){
			$hasil=($ph-7.5)/(6-7.5);
		}
		else{
			$hasil=($ph-7.5)/(9-7.5);
		}
		return $hasil;
	}
    
	$cl_baru_ph = hitungPh($ph);
	echo "Hasil ph 8 = ". $cl_baru_ph;
	echo "<br/> Hasil ph 10 = ". hitungPh(10);*/
});
// Menghitung tingkat pencemaran
$app ->get('/tingkatpencemaran', function($request, $response, $args) use($app, $db){
	//fungsi untuk mengambil nilai CLbaru
	function ph(){
		$app = Slim::getInstance();
		$app->response();
		foreach($db->pengukuran() as $data){
			if($ph<7.5){
				$clbaru[0]=($ph-7.5)/(6-7.5);
			}
			else{
				$clbaru[0]=($ph-7.5)/(9-7.5);
			}
			
			return $clbaru[0];
		}
	}
	
	function suhuair(){
		$app = Slim::getInstance();
		$app->response();
		foreach($db->pengukuran() as $data){
			$suhumax=$suhuair+3;
			$suhumin=$suhuair-3;
			$ratasuhu=($suhumax+suhumin)/2;
			if($suhuair<$ratasuhu){
				$clbaru[1]=($suhuair-$ratasuhu)/($suhumin-$ratasuhu);	
			}
			else{
				$clbaru[1]=($suhuair-$ratasuhu)/($suhumax-$ratasuhu);
			}
			return clbaru[1];
		}
	}
	
	/*function tds(){
		$app = Slim::getInstance();
		$app->response();
		foreach($db->pengukuran() as $data){
			$cl=$tds/1000;
			if($cl>1){
				$clbaru[2]= 1+5log(cl);
			}
			else{
				clbaru[2]=cl;
			}
		}
	}
	
	function tdl(){
		$app = Slim::getInstance();
		$app->response();
		foreach($db->pengukuran() as $data){
			$cl=$tdl/1000;
			if($cl>1){
				$clbaru[2]= 1+5log(cl);
			}
			else{
				clbaru[2]=cl;
			}
		}
	}
	
	*/
	
	
	foreach($db->pengukuran() as $data){
		//$i=0;
		$CLbaru = array();
		for($i=0; $i<4; $i++){
			
			
		}
    }
    //echo json_encode($tingkatpencemaran);
});

//run App
$app->run();