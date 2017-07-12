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

// $config['db']['host'] = $dbhost;
// $config['db']['user'] = $dbuser;
// $config['db']['pass'] = $dbpass;
// $config['db']['dbname'] = $dbname;

// $app = new \Slim\app(['settings'=>$config]);
$dsn = $dbmethod.$dbname;
$pdo = new PDO($dsn, $dbuser, $dbpass);
$db  = new NotORM($pdo);

//mendefinisikan route app di home
$app-> get('/', function(){
	echo "Cenggo";
});
// Menampilkan hasil sensor

$app ->get('/hasilsensor', function($request, $response, $args) use($app, $db){
	
	function hitungPh($ph_local){
		$hasil = 0;
		if($ph_local<7.5){
			$hasil=($ph_local-7.5)/(6-7.5);
		}
		else{
			$hasil=($ph_local-7.5)/(9-7.5);
		}
		return $hasil;
	}

	function hitungsuhuair($suhu_local,$suhuudara_local){
		//$app = Slim::getInstance();
		//$app->response();
		$hasil = 0;
		$suhumax = 0;
		$suhumin = 0;
		//foreach($db->pengukuran() as $data){
			$suhumax=$suhuudara_local+3;
			$suhumin=$suhuudara_local-3;
			$ratasuhu=($suhumax+$suhumin)/2;
			if($suhu_local<$ratasuhu){
				$hasil=($suhu_local-$ratasuhu)/($suhumin-$ratasuhu);	
			}
			else{
				$hasil=($suhu_local-$ratasuhu)/($suhumax-$ratasuhu);
			}
			return $hasil; //}
	}

	function hitungtds($tds_local){
		$hasil = 0;
		$cl = 0;

		$cl = $tds_local/1000;
		if($cl>1){
			$hasil= 1 + 5 * log10($cl);
		}
		else{
			$hasil=$cl;
		}
		return $hasil;
	}

	function hitungdhl($dhl_local){
		$hasil = 0;
		$cl = 0;

		$cl = $dhl_local/1800;
		if($cl>1){
			$hasil= 1 + 5 * log10($cl);
		}
		else{
			$hasil=$cl;
		}
		return $hasil;
	}

	function nilaiRata($ph_local,$suhu_local,$suhuudara_local,$tds_local,$dhl_local){
		$rata=0;
		$rata=(hitungPh($ph_local)+hitungsuhuair($suhu_local,$suhuudara_local)+hitungtds($tds_local)+hitungdhl($dhl_local))/4;
		//$rata=($a+$b+$c+$d)/4;
		return $rata;
	}


	function nilaiMaks($ph_local,$suhu_local,$suhuudara_local,$tds_local,$dhl_local){
		$maks=0;
		$a = hitungPh($ph_local) ;
		$b = hitungsuhuair($suhu_local,$suhuudara_local);
		$c = hitungtds($tds_local);
		$d = hitungdhl($dhl_local);
		$maks=max($a,$b,$c,$d);
		return $maks;
	}

	function nilaiPI($ph_local,$suhu_local,$suhuudara_local,$tds_local,$dhl_local){
		$PI=0;
		$clmaks = nilaiMaks($ph_local,$suhu_local,$suhuudara_local,$tds_local,$dhl_local);
		$clrata = nilaiRata($ph_local,$suhu_local,$suhuudara_local,$tds_local,$dhl_local);

		$PI= sqrt(((pow($clmaks,2))+(pow($clrata,2)))/2);
		return $PI;
	}

	function kategoriPI($ph_local,$suhu_local,$suhuudara_local,$tds_local,$dhl_local){
		$PI=0;
		//$hasil = '';
		$PI = nilaiPI($ph_local,$suhu_local,$suhuudara_local,$tds_local,$dhl_local);

		if(0<=$PI && $PI<=1.0)
			$hasil = "Memenuhi baku mutu (kondisi baik)";
		elseif(1.0<$PI && $PI<= 5.0){
			$hasil = "Cemar Ringan";}
		elseif(5.0<$PI && $PI<= 10){
			$hasil = "Cemar Sedang";}
		elseif($PI > 10){
			$hasil = "Cemar Berat";}

		return $hasil;
			
	}



	$ph = array();
	$suhuair = array();
	$suhuudara = array();
	$tds = array();
	$dhl = array();

	$i = 0;
	foreach($db->pengukuran() as $data){
		$id_pengukuran[$i]=$data['id_pengukuran'];
        $waktu[$i] = $data['waktu'];
        $suhuair[$i] = $data['suhuair'];
		$suhuudara[$i] = $data['suhuudara'];
        $dhl[$i] = $data['dhl'];
		$tds[$i] = $data['tds'];
		$salinitas = $data['salinitas'];
		$ph[$i] = $data['ph'];
		
		// echo "Data ph ke ".$i." adalah ".$ph[$i]."<br/>";
		// echo "Pada id ".$id_sungai[$i]." dengan waktu ".$waktu[$i]." memiliki suhu sebesar ".$suhuudara[$i]."<br/>";
		echo "Hasil dari parameter ph adalah ".hitungPh($ph[$i])."<br/>"." Dan hasil dari parameter suhu adalah ".hitungsuhuair($suhuair[$i],$suhuudara[$i])."<br/>"."hasil tds : ".hitungtds($tds[$i])."<br/>"."hasil dhl : ".hitungdhl($dhl[$i])."<br/>";
		echo "----------------------------"."<br/>";
		echo"nilai Rata : ".nilaiRata($ph[$i],$suhuair[$i],$suhuudara[$i],$tds[$i],$dhl[$i])."<br/>";
		echo "----------------------------"."<br/>";
		echo"nilai Maks : ".nilaiMaks($ph[$i],$suhuair[$i],$suhuudara[$i],$tds[$i],$dhl[$i])."<br/>";
		echo "----------------------------------------"."<br/>";
		echo"Indeks Pencemaran : ".nilaiPI($ph[$i],$suhuair[$i],$suhuudara[$i],$tds[$i],$dhl[$i])."<br/>";
		echo"Kategori Pencemaran : ".kategoriPI($ph[$i],$suhuair[$i],$suhuudara[$i],$tds[$i],$dhl[$i])."<br/>";
		echo "------------------------------------------------------------"."<br/>";
		//echo "Hasil dari parameter ph adalah ".hitungPh($ph[$i])."<br/>"." Dan hasil dari parameter suhu adalah "."<br/>";
		$i++;
    }
	//$ph = 8; $tds=1500; $tdl = 1600 ; $suhuair=27;
    
	// $cl_baru_ph = hitungPh($ph);
	// echo "Hasil ph 8 = ". $cl_baru_ph;
	// echo "<br/> Hasil ph 10 = ". hitungPh(10);
});



$app->post('/produk', function($request, $response, $args) use($app, $db){
    $produk = $request->getParams();
    $result = $db->user->insert($produk);
    echo json_encode(array(
        "status" => (bool)$result,
    ));

});

//login user
	$app-> post('/login', function($request, $response, $args) use ($app, $db){
		if ($post = $request->getParams()) {
			$query = $db->user()->where('Username', $post['username'])
							->where('password', md5($post['password']));
			if($data = $query->fetch()){
				echo json_encode(array('login' => 'true'));
			} else {
				echo json_encode(array('login' => 'false'));
			}
		} else {
			echo json_encode(array('login' => 'failed'));
		}
	});

	$app-> post('/register', function($request, $response, $args) use ($app, $db){
		if ($post = $request->getParams()) {
			$arr = array('username' => $post['username'],
					'nama_lengkap' => $post['nama_lengkap'],
					//'jenis_kelamin' => $post['jenis_kelamin'],
					'password' => md5($post['password'])
				);

			$query = $db->user()->insert($arr);
			if ($query) {
				echo json_encode(array('register' => 'true'));	
			} else {
				echo json_encode(array('register' => 'false'));
			}
		} else {
			echo json_encode(array('register' => 'failed'));
		}
	});

	$app-> post('/getpercobaan', function($request, $response, $args) use ($app, $db){
	// CREATE VIEW lihat_data_percobaan
	// 	AS
	// 	SELECT id_pengukuran, username, p.id_user, waktu, title, deskripsi, id_sungai, p.id_percobaan, suhuair, suhuudara, dhl, tds, salinitas, ph, latitude
	// 			FROM `user` u
	// 		 			JOIN percobaan p ON u.id_user=p.id_user
	// 		 		    JOIN pengukuran ukur ON ukur.id_percobaan = p.id_percobaan
		 //$sql = "SELECT * FROM lihat_data_percobaan
		 	//		WHERE id_user=";
		if ($post = $request->getParams()) {
			$query = $db->lihat_data_percobaan2()->where('id_user', $post['id_user']);
			foreach ($query as $lihat_data_percobaan2 ) {

				echo "ID = ".$lihat_data_percobaan2['id_pengukuran']."<br/>";
				echo "TITLE = ".$lihat_data_percobaan2['title']."<br/>";
				echo "ID SUNGAI = ".$lihat_data_percobaan2['id_sungai']."<br/>";
				echo "WAKTU = ".$lihat_data_percobaan2['waktu']."<br/>";
				echo "SUHU AIR = ".$lihat_data_percobaan2['suhuair']."<br/>";
				echo "SUHU UDARA = ".$lihat_data_percobaan2['suhuudara']."<br/>";
				echo "DHL = ".$lihat_data_percobaan2['dhl']."<br/>";
				echo "TDS = ".$lihat_data_percobaan2['tds']."<br/>";
				echo "SALINITAS = ".$lihat_data_percobaan2['salinitas']."<br/>";
				echo "PH = ".$lihat_data_percobaan2['ph']."<br/>";
				echo "LATITUDE = ".$lihat_data_percobaan2['latitude']."<br/>";


				// echo "Deskripsi = ".$pengukuran->percobaan['deskripsi']."<br>";
				// echo "Id percobaan = ".$percobaan->pengukuran['id_pengukuran']."<br>";
				// echo "Waktu percobaan = ".$percobaan->pengukuran['waktu']."<br>";
				// echo "$percobaan = $value <br>";
				// $pengukuran_data = array();
				// foreach ($percobaan->pengukuran() as $pengukuran => $value2) {
				// 	$pengukuran_data[] = $pengukuran->
				// }
			}

			// $stmt = $db->query($sql.$post['id_user']);
			// $post = $stmt->fetchAll(PDO::FETCH_OBJ);
			// echo json_encode($post);

			// $query = $db->percobaan()->where('id_user', $post['id_user']);
			// if($datapercobaan = $query->fetch()){
			// 	$response = array();
			// 	$percobaan = array();
			// 	$pengukuran = array();
			// 	foreach ($datapercobaan as $data) {
			// 		$query = $db->pengukuran()->where('id_percobaan', $data['id_percobaan']);
			// 		//echo $data['id_percobaan'];
			// 		//$percobaan[] = $query->fetch();
			// 		$percobaan[] = $data['id_percobaan'];
						
			// 	}
			// 	echo json_encode(array('status' => 'true', 'code' => 200,'percobaan'=>$datapercobaan));
			// } else {
			// 	echo json_encode(array('login' => 'false'));
			// }
		} else {
			echo json_encode(array('login' => 'failed'));
		}
	});

	$app -> get('/sungai', function($request, $response, $args) use($app, $db){
		$query = $db -> sungai();
		// $id_sungai = array();
		// $nama_sungai = array();
		foreach($query as $data){
		$dataAll['data'][] = array(
			'id_sungai'=>$data['id_sungai'],
			'nama_sungai'=>$data['nama_sungai']
			
		);
    }
    echo json_encode($dataAll);
	});

//run App
$app->run();