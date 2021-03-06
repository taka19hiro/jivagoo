<?php
//このスクリプトはおばけスキャン用のテストスクリプト
//まずアプリからpostされていない場合の処理をします
//接続元のIPは抜いておく
$ip = @$_SERVER['REMOTE_ADDR'];
//送られてくるだろうアプリ識別子
$appcode = "202010161444jivagoo";
//このスクリプトのパス
$script_php = $_SERVER['SCRIPT_NAME'];
//接続端末の情報を得ておく
$user_agent = $_ENV['HTTP_USER_AGENT'];
//Mysql info//////////////////////////////
//DB name
$db_name="GhostScanAR_db";
//table name
$tb_name="player_tb";
$tb_ghost="ghost_tb";
//host name
$host = 'mysql:host=localhost';
//sql user name
$user = 'takayama';
//sql password
$pass = 'Masahiro4612*';
//exists
$exists=false;
////////////////////////////////////////

//イベントの発生件数
$events = rand(2,6);

//Language init
if(isset($_POST['local'])){
	$local = (bool)$_POST['local'];
}
//interval time: 3hours 10800
$interval = 0;

//TrustPoint init
$TrustPoint = FALSE;

//POSTされていない場合はその案内
if ($_SERVER['REQUEST_METHOD']!='POST'){
    //ローカルからの接続の場合
    $iparray = explode(".", $ip);
    if($iparray[0] == '192' && $iparray[1] == '168' && $iparray[2] == '128'){
		//セッションの開始
		session_start();
		//session init
		if(!isset($_SESSION['cpu'])){
			$_SESSION['cpu'] = '0';
			$_SESSION['memory'] = '0';
			$_SESSION['benchi'] = '0';
		}
        //GETでremoveが送信されたら処理する
        if(isset($_GET['remove'])) {
            session_destroy();//セッションをクリア
            header("Location:./");//そのままだと再びGET送信されちゃうのでカレントに一旦戻す
        }
        //30秒でリフレッシュさせてるのでリフレッシュまでのカウントダウンをする
        echo '<html><head><meta http-equiv="Refresh" content="30"><title>GhostScan Server</title></head>
        <center><body><h1>Server load status</h1><p>'.
        date("Y-m-d H:i:s").' (<span id="timer">30</span>)</p><hr width="800">';
        //カウントダウン用のJAVAスクリプト
        echo '
			<script>
			window.onload=function(){
			    timer();
			}
			function timer(){
			    setTimeout(function () {
			        var time = document.getElementById("timer").innerText;
			        time--;
			        countdown(time);
			    }, 1000);
			}
			function countdown(time){
			    document.getElementById("timer").innerText = time;
			    timer();
			}
			</script>';

        //CPU使用率を得ておく
        $load = sys_getloadavg();
        
        //メモリの使用率を算定する
        $free = Shell_exec('free');
        $free = (string)trim($free);
        $free_arr = explode("\n", $free);
        $mem = explode(" ", $free_arr[1]);
        $mem = array_filter($mem);
        $mem = array_merge($mem);
        $memory_usage = $mem[2]/$mem[1]*100;
        //処理速度の簡易ベンチマーク：一億まで$iをカウントアップさせてその時間を計測
        $time_start = microtime(true);
        for($i=0;$i<100000000;$i++){};
        $time = microtime(true) - $time_start;
        
        echo '<table border="0" width="800" bgcolor="#999999" cellpadding="6" cellspacing="1">
            <tr><td width="50%" bgcolor="#ffddee"><p>CPU Usage</p></td><td width="50%" bgcolor="#ffffff"><p>'.$load[0].'％ ('.$_SESSION['cpu'].')</p></td></tr>
            <tr><td width="50%" bgcolor="#ddddff"><p>Memory Usage</p></td><td width="50%" bgcolor="#ffffff"><p>'.round($memory_usage,2).'％ ('.$_SESSION['memory'].')</p></td></tr>
            <tr><td width="50%" bgcolor="#ffddee"><p>ServerIP</p></td><td width="50%" bgcolor="#ffffff"><p>'.$_SERVER['SERVER_ADDR'].'</p></td></tr>
            <tr><td width="50%" bgcolor="#ddddff"><p>DocumentRoot</p></td><td width="50%" bgcolor="#ffffff"><p>'.$_SERVER['DOCUMENT_ROOT'].'</p></td></tr>
            <tr><td width="50%" bgcolor="#ffddee"><p>UserAgent</p></td><td width="50%" bgcolor="#ffffff"><p>'.$_SERVER['HTTP_USER_AGENT'].'</p></td></tr>
            <tr><td width="50%" bgcolor="#ddddff"><p>ClientIP</p></td><td width="50%" bgcolor="#ffffff"><p>'.$_SERVER['REMOTE_ADDR'].'</p></td></tr>
            <tr><td width="50%" bgcolor="#ffddee"><p>Benchmark of time to 100million counts</p></td><td width="50%" bgcolor="#ffffff"><p>'.round($time,2).' sec. ('.$_SESSION['benchi'].')</p></td></tr>
            </table>';
        //OldWSPRIの資料を置いた
        echo '<br><a href="./old_wspri">昔のWSPRI</a>
        <form action="'.$script_php .'" method="GET">
        <button type="submit" name="remove">SESSION削除</button></form>
        </center></body></html>';
        //セッションを保存（上書き）
		$_SESSION['cpu'] = $load[0].'％';
		$_SESSION['memory'] = round($memory_usage,2).'％';
		$_SESSION['benchi'] = round($time,2).' sec.';
    }else{//アプリからのアクセスではない場合でLANからのアクセスではない場合はおばけスキャンの情報を表示
        echo '
        <html><head><title>GhostScan Server</title>
        <meta name="viewport" content="width=device-width,initial-scale=1.0">
        </head>
        <body><h1>GhostScan GameServer</h1>
        <p>This game server is for GhostScan only.</p>
        <p style="color:red;">Only available from GhostScan, an Android game app.</p>
        <a href="https://play.google.com/store/apps/details?id=jp.dip.wspri.jivagoo" target="_blank">
        <img src="https://lh3.googleusercontent.com/gD5aQb8FWaQ73bwCEtgc_k8Ry-2bkM6WAaqtQmSYlKjyEGHgP0aOuT4ZzZYAX0-gyv0=s180-rw">
        <p>GhostScan Download Google Play</P></a>
        ';
        print "<p>".$ip."</p>";//アクセス元のIPを表示
        echo '
        </body></html>
        ';
        //POSTされていないでアクセスしてきたらIPを記録するか検討
    }
}else{//POSTされている
	$acount=$_POST['acount'];
	$password=$_POST['password'];
    //AndroidからappcodeがPOSTされているなら正式なログイン
    if($_POST['appcode']==$appcode ){//&& stripos($user_agent,'Android') !== false){
        //初めてのログインならばアカウントを登録してDBを生成
        if($acount && $password){
			//POST Dataを受け取り、stringの配列からintに要素をキャストしてint配列に変換しておく
            if(isset($_POST['ghost'])){
				$Ghostss  =explode("|",$_POST['ghost']);
				$Ghost=[];
				foreach($Ghostss as $int_g){ $Ghost[] = (int)$int_g; }
				$masters =explode("|",$_POST['master']);
				$master=[];
				foreach($masters as $int_g){ $master[] = (int)$int_g; }
				$party1s =explode("|",$_POST['party1']);
				$party1=[];
				foreach($party1s as $int_g){ $party1[] = (int)$int_g; }
				$party2s =explode("|",$_POST['party2']);
				$party2=[];
				foreach($party2s as $int_g){ $party2[] = (int)$int_g; }
				$party3s =explode("|",$_POST['party3']);
				$party3=[];
				foreach($party3s as $int_g){ $party3[] = (int)$int_g; }
				$party4s =explode("|",$_POST['party4']);
				$party4=[];
				foreach($party4s as $int_g){ $party4[] = (int)$int_g; }
				$itemss  =explode("|",$_POST['items']);
				$items=[];
				foreach($itemss as $int_g){ $items[] = (int)$int_g; }
				$weaponss=explode("|",$_POST['weapons']);
				$weapons=[];
				foreach($weaponss as $int_g){ $weapons[] = (int)$int_g; }
				$glovess =explode("|",$_POST['gloves']);
				$gloves=[];
				foreach($glovess as $int_g){ $gloves[] = (int)$int_g; }
				$armoreds=explode("|",$_POST['armored']);
				$armored=[];
				foreach($armoreds as $int_g){ $armored[] = (int)$int_g; }
				$shosess =explode("|",$_POST['shoses']);
				$shoses=[];
				foreach($shosess as $int_g){ $shoses[] = (int)$int_g; }
            }            
            
            try{
                //Sql connect
                $db = new PDO($host,$user,$pass);
                $sql = 'use '.$db_name;//DBを選択
                    if($db->query($sql)){
						//POSTされたアカウントとパスワードでテーブルをGET
						$sql = "SELECT * FROM ".$tb_name." where acount=\"".$acount."\" and password=\"".$password."\"";
						//ghostのテーブルもGET
                        $enemy = "SELECT * FROM ".$tb_ghost;
						$rows=$db->query($sql);
						$rows = $rows->fetchAll();//$rowsのテーブルをデータ化しておく
						$enemy=$db->query($enemy);
						$result = $enemy->fetchAll();//$enemyのテーブルをデータ化しておく
						//テーブルのGETに成功していたら
                        if(!empty($rows[0])){
							$row=$rows[0];//$rows[0]がGETしたテーブルのROWになっている
							$rowmaster=unserialize($row['master']);
							///////////////////////////////////////////
                            if(isset($_POST['end_code'])){
								if($master[8]>0){//0より大きい場合TP減算
									$master[8]--;
									if($master[8]>10){
										$TrustPoint=TRUE;//TPが10より小さいとマスターの好きなようにする
									}
								}
								if(time()>($row['a_time']+$interval)){//でもSET時間以内に何度も旅には出ない
									//end_codeが送られてきた場合はステータスをUPDATEしてserverでの冒険を始める
									$sql = 'UPDATE '.$tb_name.' set a_time=:a_time,ghost=:ghost,item=:items,weapon=:weapons,grove=:gloves,armored=:armored,shoes=:shoses,master=:master,party1=:party1,party2=:party2,party3=:party3,party4=:party4 where id=:id';
									$sql = $db->prepare($sql);
									$param = array(':a_time'=>time(),':ghost'=>serialize($Ghost),':items'=>serialize($items),':weapons'=>serialize($weapons),':gloves'=>serialize($gloves),':armored'=>serialize($armored),':shoses'=>serialize($shoses),':master'=>serialize($master),':party1'=>serialize($party1),':party2'=>serialize($party2),':party3'=>serialize($party3),':party4'=>serialize($party4),':id'=>$row['id']);
									$sql->execute($param);
									//冒険の関数をCall
									battle($Ghost,$result,$master,$party1,$party2,$party3,$party4,$events);
								}else{
									//SET時間内のアクセスの場合は旅に出ずにa_timeを除くステータスのみ更新する
									if($local){//TRUE is English
										$preparea=array("START_EVENT!",
													"The members of the party are preparing for the trip.",
													"Please try starting again after a while.");
									}else{//FALSE is Japanese
										$preparea=array("START_EVENT!",
													"パーティのメンバーは旅に必要なパラメータの回復に至っていません。",
													"最初の出発から3時間以上の経過が必要です。");
									}
									$sql = 'UPDATE '.$tb_name.' set ghost=:ghost,item=:items,weapon=:weapons,grove=:gloves,armored=:armored,shoes=:shoses,master=:master,party1=:party1,party2=:party2,party3=:party3,party4=:party4,trip=:trip where id=:id';
									$sql = $db->prepare($sql);
									$param = array(':ghost'=>serialize($Ghost),':items'=>serialize($items),':weapons'=>serialize($weapons),':gloves'=>serialize($gloves),':armored'=>serialize($armored),':shoses'=>serialize($shoses),':master'=>serialize($master),':party1'=>serialize($party1),':party2'=>serialize($party2),':party3'=>serialize($party3),':party4'=>serialize($party4),':trip'=>serialize($preparea),':id'=>$row['id']);
									$sql->execute($param);
								}
                            }else{
                                //endでない場合はserverのデータをappへ送る
                                switch($_POST['getdata']){
                                    //if getdata is ghost
                                    case 'ghost':
										$rowghost = unserialize($row['ghost']);//Sqlのシリアライズを戻す
										//配列はintなのでjsonで送れるstring配列に変換する
										$rowghosts=[];
										foreach($rowghost as $rows){
											$rowghosts[] = (string)$rows;
										}
                            	        $keyghost = array_keys($rowghost);//配列のキーを取り出しておく
                        	            //取り出したキーの分だけ文字列でキーをjson配列用に作り直しておく
                                        for($i=0;$i<count($keyghost);$i++){
                                            $keysghost[$i] = '"'.$i.'"';
                                        }
                                        //配列をjson用に連想配列に作り直しておく
                                        $rowsghost = array_combine($keysghost,$rowghosts);
                                                
                                        //jsonとして出力
                                        header('Content-type: application/json');
                                        echo json_encode($rowsghost);//jsonをclientに出力
                                    break;
                                    //if getdata is master
                                    case 'master':
										$rowmaster= unserialize($row['master']);
										//配列はintなのでjsonで送れるstring配列に変換する
										$rowmasters=[];
										foreach($rowmaster as $rows){
											$rowmasters[] = (string)$rows;
										}
                                        $keymaster = array_keys($rowmaster);//配列のキーを取り出しておく
                            	        //取り出したキーの分だけ文字列でキーをjson配列用に作り直しておく
                                        for($i=0;$i<count($keymaster);$i++){
                                            $keysmaster[$i] = '"'.$i.'"';
                                        }
                                        //配列をjson用に連想配列に作り直しておく
                                        $rowsmaster = array_combine($keysmaster,$rowmasters);
                                                                                                    //jsonとして出力
                                        header('Content-type: application/json');
                                        echo json_encode($rowsmaster);//jsonをclientに出力
                                    break;
                                    //if getdata is party1
                                    case 'party1':
										$rowparty1= unserialize($row['party1']);
										//配列はintなのでjsonで送れるstring配列に変換する
										$rowparty1s=[];
										foreach($rowparty1 as $rows){
											$rowparty1s[] = (string)$rows;
										}
                                        $keyparty1 = array_keys($rowparty1);//配列のキーを取り出しておく
                                        //取り出したキーの分だけ文字列でキーをjson配列用に作り直しておく
                                        for($i=0;$i<count($keyparty1);$i++){
                                            $keysparty1[$i] = '"'.$i.'"';
                                        }
                                        //配列をjson用に連想配列に作り直しておく
                                        $rowsparty1 = array_combine($keysparty1,$rowparty1s);
                                                    
                                        //jsonとして出力
                                        header('Content-type: application/json');
                                        echo json_encode($rowsparty1);//jsonをclientに出力
                                    break;
                                    case 'party2':
										$rowparty2= unserialize($row['party2']);
										//配列はintなのでjsonで送れるstring配列に変換する
										$rowparty2s=[];
										foreach($rowparty2 as $rows){
											$rowparty2s[] = (string)$rows;
										}
                                        $keyparty2 = array_keys($rowparty2);//配列のキーを取り出しておく
                                        //取り出したキーの分だけ文字列でキーをjson配列用に作り直しておく
                                        for($i=0;$i<count($keyparty2);$i++){
                                            $keysparty2[$i] = '"'.$i.'"';
                                        }
                                        //配列をjson用に連想配列に作り直しておく
                                        $rowsparty2 = array_combine($keysparty2,$rowparty2s);
                                                   
                                	    //jsonとして出力
                                        header('Content-type: application/json');
                                        echo json_encode($rowsparty2);//jsonをclientに出力
                                    break;
                                    case 'party3':
										$rowparty3= unserialize($row['party3']);
										//配列はintなのでjsonで送れるstring配列に変換する
										$rowparty3s=[];
										foreach($rowparty3 as $rows){
											$rowparty3s[] = (string)$rows;
										}
                                        $keyparty3 = array_keys($rowparty3);//配列のキーを取り出しておく
                                        //取り出したキーの分だけ文字列でキーをjson配列用に作り直しておく
                                        for($i=0;$i<count($keyparty3);$i++){
                                            $keysparty3[$i] = '"'.$i.'"';
                                        }
                                    	//配列をjson用に連想配列に作り直しておく
                                        $rowsparty3 = array_combine($keysparty3,$rowparty3s);
                                                    
                                        //jsonとして出力
                                        header('Content-type: application/json');
                                        echo json_encode($rowsparty3);//jsonをclientに出力
                                    break;
                                    case 'party4':
										$rowparty4= unserialize($row['party4']);
										//配列はintなのでjsonで送れるstring配列に変換する
										$rowparty4s=[];
										foreach($rowparty4 as $rows){
											$rowparty4s[] = (string)$rows;
										}
                                        $keyparty4 = array_keys($rowparty4);//配列のキーを取り出しておく
                                        //取り出したキーの分だけ文字列でキーをjson配列用に作り直しておく
                                        for($i=0;$i<count($keyparty4);$i++){
                                            $keysparty4[$i] = '"'.$i.'"';
                                        }
                                        //配列をjson用に連想配列に作り直しておく
                                        $rowsparty4 = array_combine($keysparty4,$rowparty4s);
                                                    
                                        //jsonとして出力
                                        header('Content-type: application/json');
                                        echo json_encode($rowsparty4);//jsonをclientに出力
									break;
									case 'items':
										$rowitem= unserialize($row['item']);
										//配列はintなのでjsonで送れるstring配列に変換する
										$rowitems=[];
										foreach($rowitem as $rows){
											$rowitems[] = (string)$rows;
										}
										$keyitem = array_keys($rowitem);//配列のキーを取り出しておく
										//取り出したキーの分だけ文字列でキーをjson配列用に作り直しておく
										for($i=0;$i<count($keyitem);$i++){
											$keysitem[$i] = '"'.$i.'"';
										}
										//配列をjson用に連想配列に作り直しておく
										$rowsitem = array_combine($keysitem,$rowitems);
													
										//jsonとして出力
										header('Content-type: application/json');
										echo json_encode($rowsitem);//jsonをclientに出力
									break;
									case 'weapons':
										$rowweapon= unserialize($row['weapon']);
										//配列はintなのでjsonで送れるstring配列に変換する
										$rowweapons=[];
										foreach($rowweapon as $rows){
											$rowweapons[] = (string)$rows;
										}
										$keyweapon = array_keys($rowweapon);//配列のキーを取り出しておく
										//取り出したキーの分だけ文字列でキーをjson配列用に作り直しておく
										for($i=0;$i<count($keyweapon);$i++){
											$keysweapon[$i] = '"'.$i.'"';
										}
										//配列をjson用に連想配列に作り直しておく
										$rowsweapon = array_combine($keysweapon,$rowweapons);
													
										//jsonとして出力
										header('Content-type: application/json');
										echo json_encode($rowsweapon);//jsonをclientに出力
									break;
									case 'gloves':
										$rowgrove= unserialize($row['grove']);
										//配列はintなのでjsonで送れるstring配列に変換する
										$rowgroves=[];
										foreach($rowgrove as $rows){
											$rowgroves[] = (string)$rows;
										}
										$keygrove = array_keys($rowgrove);//配列のキーを取り出しておく
										//取り出したキーの分だけ文字列でキーをjson配列用に作り直しておく
										for($i=0;$i<count($keygrove);$i++){
											$keysgrove[$i] = '"'.$i.'"';
										}
										//配列をjson用に連想配列に作り直しておく
										$rowsgrove = array_combine($keysgrove,$rowgroves);
												
										//jsonとして出力
										header('Content-type: application/json');
										echo json_encode($rowsgrove);//jsonをclientに出力
									break;
									case 'armored':
										$rowarmored= unserialize($row['armored']);
										//配列はintなのでjsonで送れるstring配列に変換する
										$rowarmoreds=[];
										foreach($rowarmored as $rows){
											$rowarmoreds[] = (string)$rows;
										}
										$keyarmored = array_keys($rowarmored);//配列のキーを取り出しておく
										//取り出したキーの分だけ文字列でキーをjson配列用に作り直しておく
										for($i=0;$i<count($keyarmored);$i++){
											$keysarmored[$i] = '"'.$i.'"';
										}
										//配列をjson用に連想配列に作り直しておく
										$rowsarmored = array_combine($keysarmored,$rowarmoreds);
												
										//jsonとして出力
										header('Content-type: application/json');
										echo json_encode($rowsarmored);//jsonをclientに出力
									break;
									case 'shoses':
										$rowshoes= unserialize($row['shoes']);
										//配列はintなのでjsonで送れるstring配列に変換する
										$rowshoess=[];
										foreach($rowshoes as $rows){
											$rowshoess[] = (string)$rows;
										}
										$keyshoes = array_keys($rowshoes);//配列のキーを取り出しておく
										//取り出したキーの分だけ文字列でキーをjson配列用に作り直しておく
										for($i=0;$i<count($keyshoes);$i++){
											$keysshoes[$i] = '"'.$i.'"';
										}
										//配列をjson用に連想配列に作り直しておく
										$rowsshoes = array_combine($keysshoes,$rowshoess);
													
										//jsonとして出力
										header('Content-type: application/json');
										echo json_encode($rowsshoes);//jsonをclientに出力
									break;
									case 'trip':
										$rowtrip= unserialize($row['trip']);
										$keytrip = array_keys($rowtrip);//配列のキーを取り出しておく
										//取り出したキーの分だけ文字列でキーをjson配列用に作り直しておく
										for($i=0;$i<count($keytrip);$i++){
											$keystrip[$i] = '"'.$i.'"';
										}
										//配列をjson用に連想配列に作り直しておく
										$rowstrip = array_combine($keystrip,$rowtrip);
													
										//jsonとして出力
										header('Content-type: application/json');
										echo json_encode($rowstrip);//jsonをclientに出力

										//MySqlのこのテーブルはLONGTEXTにしないと書き込みに失敗する時が出てくる
									break;
                                    default:
                                        echo 'error:POST is not done correctly.';
                                }
                                /*  配列のままだとjsonにしても配列で作成されるのでjsonで受け取れない*/
                                //普通の配列を得連想配列に変換する
                            }
                            $exists=true;//存在している
                        }
                    //アカウントがない場合は作成する
                    if(!$exists){
                        //testなので3項目
                        $sql = 'INSERT INTO '.$tb_name.' (a_time,acount,password) VALUES (:a_time,:acount,:password)';
                        $sql = $db->prepare($sql);
                        $param = array(':a_time'=>(time()-($interval*2)),':acount'=>$_POST['acount'],':password'=>$_POST['password']);
                        $sql->execute($param);
                        //echo 'アカウントを作成しました';
                    }
                }
                //close mysql
                $db = null;
            }catch(PDOException $e){
                echo "DB connect failure..." . PHP_EOL;
                echo $e->getMessage();
                exit;
            }
        }
    }else{//AndroidからappcodeがPOSTされていないなら不正ログインなのでアプリの情報を表示する
        echo '
        <html><head><title>GostScan Server</title>
        <meta name="viewport" content="width=device-width,initial-scale=1.0">
        </head>
        <body><h1>GhostScan GameServer</h1>
        <p>This game server is for GhostScan only.</p>
        <p style="color:red;">Only available from GhostScan, an Android game app.</p>
        <p style="color:red;">It is determined that the POST data sent is not from a ghost scan.</p>
        <p style="color:red;">If you install and start Ghost Scan, you will be automatically logged in to this game server.</p>
        <a href="https://play.google.com/store/apps/details?id=jp.dip.wspri.jivagoo" target="_blank">
        <img src="https://lh3.googleusercontent.com/gD5aQb8FWaQ73bwCEtgc_k8Ry-2bkM6WAaqtQmSYlKjyEGHgP0aOuT4ZzZYAX0-gyv0=s180-rw">
        <p>GhostScan Download Google Play</P></a>
        ';
        print '<p>'.$ip.'</p>';//接続元IPを表示する
        echo '
        </body></html>
        ';
        //POSTされてて不正アクセスならIPを残すか検討する
    }
}
function battle($ghosts,$ene,$mas,$par1,$par2,$par3,$par4,$loops){
	global $local;
    //ghost name set array
    if($local){
		$g_name=array(0=>0,1=>'Sayo.Akikawa',2=>'Ren.Mita',3=>'Urara.Ayase',4=>'Miu.Kira',5=>'Kiyomi.Kouchi',6=>'Kenmotu.Yokochi',
		7=>'Terumoto.Ohishi',8=>'Ieshige.Kaneko',9=>'Shiro.Kawamoto',10=>'Aoi.Shirai',11=>'Osami.Nakahata',12=>'PRESIDENT Ayase',
		13=>'Gosuke.Yuasa',14=>'Haruhisa.Amago',15=>'Narimasa.Sassa',16=>'Katsuyori.Takeda',17=>'Kagetora.Uesugi',18=>'Norihide.Matsuda',
		19=>'Yoshitaka.Ohuchi',20=>'Yoshikage.Asakura',21=>'Harukata.Sue',22=>'Yoshiteru.Ashikaga',23=>'Yoshinaga.Ohuchi',
		24=>'Nagaharu.Bessyo',25=>'LittleGhost',26=>'UnKnowGhost',27=>'OldGhost',
		28=>'PeasantFemale',29=>'PeasantMale',30=>'Female',31=>'Male',32=>'Samurai',33=>'Ashigaru',34=>'LittleMonk',35=>'Monk',36=>'Phantom',
		37=>'1stAngel',38=>'2ndAngel',39=>'3rdAngel',40=>'4thAngel',41=>'5thAngel',42=>'LastAngel');
	}else{
		$g_name=array(0=>0,1=>'秋川 サヨ',2=>'三田 レン',3=>'綾瀬 うらら',4=>'吉良 美世',5=>'河内 キヨミ',6=>'横地 監物',7=>'大石 照基',8=>'金子 家重',
		9=>'川下 士郎',
		10=>'白井 あおい',11=>'中畑 修',12=>'綾瀬 社長',13=>'湯浅 五助',14=>'尼子 晴久',15=>'佐々 成政',16=>'武田 勝頼',17=>'上杉 景虎',18=>'松田 憲秀',
		19=>'大内 義隆',20=>'朝倉 義景',21=>'陶 隆房',22=>'足利 義輝',23=>'大内 義長',24=>'別所 長治',25=>'幼いおばけ',26=>'実態不明のおばけ',
		27=>'年老いたおばけ',
		28=>'農民(女子)',29=>'農民(男子)',30=>'町民(女子)',31=>'町民(男子)',32=>'武者',33=>'足軽',34=>'小僧',35=>'僧侶',36=>'怪人',
		37=>'地獄道への天子',38=>'餓鬼道への天子',39=>'畜生道への天子',40=>'修羅道への天子',41=>'人間道への天子',42=>'天道への天子');
	}
    //まずおばけと出会う
	first($ghosts,$ene,$g_name,$mas,$par1,$par2,$par3,$par4,$loops);
}
function first($ghos,$en,$g_nam,$maste,$part1,$part2,$part3,$part4,$loop){
	global $local;
	global $host;
	global $user;
	global $pass;
	global $db_name;
	global $tb_name;
	global $acount;
	global $password;
	global $rowmaster;
	global $items;
	$G_nam = $g_nam;
	$En = $en;
	//main menber default 3
	$main = 3;
	//落し物の分母1だと1/2で2だと1/3
	$ic = rand(2,4);
	//おばけの乗算最大値デフォルト7
	if($rowmaster[6]<30){
		$gdw= 1;
		$gup= 7;
	}else if($rowmaster[6]>=30&&$rowmaster[6]<50){
		$gdw= 2;
		$gup= 7;
	}else{
		$gdw= 3;
		$gup= 7;
	}
	//Send Loop number loops
	for($counts=0;$counts<$loop;$counts++){
		if(!$counts){
			$rowghost=$ghos;
		}else{
			try{
                //Sql connect
                $db = new PDO($host,$user,$pass);
                $sql = 'use '.$db_name;//DBを選択
                if($db->query($sql)){
					//POSTされたアカウントとパスワードでテーブルをGET
					$sql = "SELECT * FROM ".$tb_name." where acount=\"".$acount."\" and password=\"".$password."\"";
					$sql=$db->query($sql);
					$rows = $sql->fetchAll();//$enemyのテーブルをデータ化しておく
					if(!empty($rows[0])){
						$row=$rows[0];//$rows[0]がGETしたテーブルのROWになっている
						$rowmaster= unserialize($row['master']);
						$rowghost= unserialize($row['ghost']);
						$items= unserialize($row['item']);
						//print ' :tubo kazu:'. $rowitem[16]. ' :ghost kazu :'. array_sum($rowghost);
					}
				}
				//exit sql
				$db=null;
			}catch(PDOException $e){
				echo "DB connect failure..." . PHP_EOL;
				echo $e->getMessage();
				exit;
			}
		}
		//count ghost
		$g_count=array_sum($rowghost);
		//delete angel count
		if($rowghost[37]){$g_count-1;}
		if($rowghost[38]){$g_count-1;}
		if($rowghost[39]){$g_count-1;}
		if($rowghost[40]){$g_count-1;}
		if($rowghost[41]){$g_count-1;}
		//count tubo
		$t_count=$items[16]-$g_count;
		if($t_count<=0){
			$t_count=0;
		}
		//$type=rand(0,10);
		if(1){//0,1=バトルの場合
			$i=1;//カウント初期化
			$ghosthp = rand($gdw,$gup);//おばけの場合の乗算HP
			$ghostap = rand($gdw,$gup);//おばけの場合の乗算AP
			//出会うおばけを選出
			if(rand(0,100)>$main){
				$enemy_id=rand(25,36);
				if($enemy_id==36){//怪人が天子を呼び出す
					if(rand(0,20)==0&&$rowghost[2]&&$rowghost[5]&&$rowghost[6]&&$rowghost[7]&&$rowghost[8]&&$rowghost[11]&&rand(0,30)==0&&!$rowghost[37]){
						$enemy_id=37;
					}else if($rowghost[37]&&!$rowghost[38]&&rand(0,20)==0){
						$enemy_id=38;
					}else if($rowghost[37]&&$rowghost[38]&&!$rowghost[39]&&rand(0,10)==0){
						$enemy_id=39;
					}else if($rowghost[37]&&$rowghost[38]&&$rowghost[39]&&!$rowghost[40]&&rand(0,1)==0){
						$enemy_id=40;
					}else if($rowghost[37]&&$rowghost[38]&&$rowghost[39]&&$rowghost[40]&&!$rowghost[41]&&rand(0,1)==0){
						$enemy_id=41;//こいつ倒したら富士山にタワーが現れる
					}
				}
			}else{
				$ghosthp=$ghosthp*rand(1,4);//主要メンバーならHPは更に倍
				$enemy_id=rand(3,24);//一回しか出ないおばけを選出
				//count init
				$icount=0;
				//get enemy id is false
				while($rowghost[($enemy_id-1)]!=0){
					$enemy_id=rand(3,24);//0おばけを選出
					if($icount>22){
						$enemy_id=rand(25,36);
					break;
					}
					$icount++;
				}
			}
			if($enemy_id==12){
				$enemy_id=rand(25,36);//綾瀬社長はまだ出現しない
			}
			//print 'Enemy id:'.$enemy_id.':';
			//出会ったおばけのステータスを取得する
			foreach($En as $ghost_on){
				if ($ghost_on['id']==$enemy_id){
					$nu1 = $enemy_id;//get id
					$na1 = $G_nam[$enemy_id];//get name
					$hp1 = $ghost_on['HP']*$ghosthp;
					$at1 = $ghost_on['AP']*$ghostap;
					$de1 = $ghost_on['DP']*$ghosthp;
					$qu1 = $ghost_on['SP'];
					$lu1 = $ghost_on['LP'];
					$he1 = $ghost_on['TP'];
					$cu1 = $ghost_on['FP'];
					$sc1 = $ghost_on['PP'];
					//echo 'id:'.$nu1.'-name:'.$na1.'-HP:'.$hp1.'-AP:'.$at1.'-DP:'.$de1.'-SP:'.$qu1.'-LP:'.$lu1.'-TP:'.$he1.'-FP:'.$cu1.'-PP:'.$sc1;
				}
			}
			//echo '--id:master:'.$maste[0].'-party1:'.$part1[0].'-part2:'.$part2[0];
			$nu2 = array($maste[0],$part1[0],$part2[0],$part3[0],$part4[0]);
			$na2 = array($g_nam[$maste[0]],$g_nam[$part1[0]],$g_nam[$part2[0]],$g_nam[$part3[0]],$g_nam[$part4[0]]);
			$hp2 = array($maste[2]+intdiv($maste[7],5),$part1[2]+intdiv($maste[7],5),$part2[2]+intdiv($maste[7],5),$part3[2]+intdiv($maste[7],5),$part4[2]+intdiv($maste[7],5));
			$at2 = array($maste[6],$part1[6],$part2[6],$part3[6],$part4[6]);
			$de2 = array($maste[3],$part1[3],$part2[3],$part3[3],$part4[3]);
			$qu2 = array($maste[4],$part1[4],$part2[4],$part3[4],$part4[4]);
			$lu2 = array($maste[5],$part1[5],$part2[5],$part3[5],$part4[5]);
			$he2 = array($maste[8],$part1[8],$part2[8],$part3[8],$part4[8]);//プレイヤーとの信頼度
			$cu2 = array($maste[7],$part1[7],$part2[7],$part3[7],$part4[7]);//魅力
			$sc2 = array($maste[9],$part1[9],$part2[9],$part3[9],$part4[9]);//memberからの信頼度

			$at2_bk=$at2;
			$de2_bk=$de2;
			//echo '--id:'.$nu2[1].'-name:'.$na2[1].'-HP:'.$hp2[1].'-AP:'.$at2[1].'-DP:'.$de2[1].'-SP:'.$qu2[1].'-LP:'.$lu2[1].'-TP:'.$he2[1].'-FP:'.$cu2[1].'-PP:'.$sc2[1];

			//バトルの回数
			$battle_loop=29;//30回で終了

			//先制攻撃のフラグ
			$first_attack=0;//一応FALSEで初期化

			//ラッキーの抽選値
			$lucky=0;//一応FALSEで初期化

			//個別のラッキーフラグ
			$uni_lucky1=0;//おばけ一応FALSEで初期化
			$uni_lucky2=0;//パーティ一応FALSEで初期化

			//攻撃力と防御力を変数にバックアップしておく
			$at1_bk=$at1;
			$de1_bk=$de1;

			//lose party init
			$p=0;

			//p_array
			$p_array = $nu2;

			//count
			$count = 0;
			foreach($nu2 as $number){
				if($number){
					$count++;
				}
			}
			//戦闘ループに入る<=にしとかないと途中でおわるので駄目だよ！
			for($i=0;$i<=$battle_loop;$i++){
				$nf=0;
				if(!$i){
					if($enemy_id>36){
						$place=3;
					}else{
						$place=rand(0,2);
					}
					$mess[] = 'START_EVENT!';
					if($local){
						switch($place){
							case 0: $ev='in the shadow of a telephone pole!';break;
							case 1: $ev='behind the mailbox!';break;
							case 2: $ev='in the shadow of the shrine!';break;
							case 3: $ev='in the halo that fell from heaven!';break;
						}
						if($place==3){
							$mess[] = 'When party was investigating the city of "dusk", I saw a Angel '.$ev;
							$mess[] = 'The Angel is losing me! You can battle '.($battle_loop+1).' times!';
						}else{
							$mess[] = 'When party was investigating the city of "dusk", I saw a ghost '.$ev;
							$mess[] = 'The ghost is losing me! You can battle '.($battle_loop+1).' times!';
						}
					}else{
						switch($place){
							case 0: $ev='電柱の影';break;
							case 1: $ev='郵便ポストの裏';break;
							case 2: $ev='祠の奥';break;
							case 3: $ev='突然まばゆい光が天から落ち、後輪の中';break;
						}
						if($place==3){
							$mess[] ='逢魔が時の街を調べていると'.$ev.'に天子が見えた！';
							$mess[] = 'うららが叫んだ！「私をおばけにしたこの世界の支配者だよ！」';
							$mess[] = '天子は神々しくもその表情は恐怖を感じる程威圧的だ！';
							$mess[] = 'バトルは'.($battle_loop+1).'回行える！さあ、天子を昇天させるチャンスだ！';
						}else{
							$mess[] ='逢魔が時の街を調べていると'.$ev.'におばけが見えた！';
							$mess[] = 'おばけは我を失っている！バトルは'.($battle_loop+1).'回行える！';
						}
					}
					if($maste[7]>=5){
						if($local){
							$mess[] = '!(^^)![BONUS!]HP has been added by "'.intdiv($maste[7],5).'" due to the charm of Master "'.$na2[0].'"!';
						}else{
							$mess[] = '!(^^)!【ボーナス！】マスター「'.$na2[0].'」の魅力によりHPがそれぞれ'.intdiv($maste[7],5).'加算された！';
						}
					}
				}
				if($local){
					$mess[] = '[Round '.($i +1).' of The '.($battle_loop +1).' Round]';
					if($i==$battle_loop) {
						if($place==3){
							$mess[] = "Both sides have become exhausted. The angel turned into light and went back to heaven.";
						}else{
							$mess[] = "Both sides have become exhausted. The ghost ran away flutteringly.";
						}
						break;
					}

				}else{
					$mess[] = '【'.($battle_loop +1).'回戦中:第'.($i +1).'回戦】';
					if($i==$battle_loop) {
						if($place==3){
							$mess[] = 'パーティ全員はへとへとだった。天子も疲弊したらしく光となって天へ帰って行った。';
						}else{
							$mess[] = "双方が疲弊してしまった。おばけはふらふらと逃げて行った。";
						}
						break;
					}
				}
				
				//続いてラッキーの抽選
				$lucky1=rand(0,99);
				$lucky2=rand(0,99);
				//キャラクタ毎のラッキーの抽選をするよ：一人目
				if(!$uni_lucky1){
					if($lucky1<$lu1){
						$at1=$at1*2;
						$de1=$de1*2;
						$uni_lucky1=1;
					}else if($lucky1==$lu1){
						$at1=$at1*4;
						$uni_lucky1=1;
					}else{
						$uni_lucky1=0;
						//バックアップに戻しておく
						$at1=$at1_bk;
						$de1=$de1_bk;
					}
				}else{
					$uni_lucky1=0;
					//バックアップに戻しておく
					$at1=$at1_bk;
					$de1=$de1_bk;
				}
				
				//count init
				$ii=0;
				foreach($p_array as $pt){
					if($pt){
						//パーティメンバーの揺らぎ
						if(!$uni_lucky2){
							if($lucky2<$lu2[$ii]){//LPが大きかったら攻撃と防御が2倍
								$at2[$ii]=$at2[$ii]*2;
								$de2[$ii]=$de2[$ii]*2;
								$uni_lucky2=1;
							}else if($lucky2==$lu2[$ii]){//同じだったら攻撃が4倍に
								$at2[$ii]=$at2[$ii]*4;
								$uni_lucky2=1;
							}else{
								$uni_lucky2=0;
								//バックアップに戻しておく
								$at2[$ii]=$at2_bk[$ii];
								$de2[$ii]=$de2_bk[$ii];
							}
						}else{
							$uni_lucky2=0;
							//バックアップに戻しておく
							$at2[$ii]=$at2_bk[$ii];
							$de2[$ii]=$de2_bk[$ii];
						}
						$qu2q2[$ii]=$qu2[$ii]+rand(0,floor($qu2[$ii]/3));//素早さの揺らぎ
						$at2a2[$ii]=$at2[$ii]+rand(0,floor($at2[$ii]/3));//攻撃力のゆらぎ
						$de2d2[$ii]=$de2[$ii]+rand(0,floor($de2[$ii]/3));//防御力のゆらぎ
						$ii++;
					}
				}

				//おばけの素早さのユラギを抽選しておく
				$qu1q1=$qu1+rand(0,floor($qu1/3));
				//おばけの攻撃力と防御力のユラギ
				$at1a1=$at1+rand(0,floor($at1/3));
				$de1d1=$de1+rand(0,floor($de1/3));

				//count init
				$c=0;
				
				//ghostのidが0ではない場合は戦う
				foreach($p_array as $id){
					if($id){
						if($hp2[$c]!=0){//HPが0になっていない場合は戦いに参加する
							//素早さがおばけのほうが大きい場合で先制フラグが立っていないか２になっている場合
							if($qu1q1 > $qu2q2[$c] && !$first_attack || $first_attack == 2){
								//先制攻撃フラグに１を入れる
								$first_attack = 1;
								//msgを出力
								if($local){
									if($place==3){
										$mess[] = '['.$na2[$c].'] is targeted! It is The Angel attack!';
									}else{
										$mess[] = '['.$na2[$c].'] is targeted! It is a Ghosts attack!';
									}
								}else{
									if($place==3){
										$mess[] = '「'.$na2[$c].'」を天子が睨んだ！天子のオーラが包み込む！';
									}else{
										$mess[] = '「'.$na2[$c].'」が狙われている！おばけが飛びかかっていく！';
									}
								}

								//攻撃がヒットするかの判定
								$bom = $qu1q1 - $qu2q2[$c];

								//ダメージの演算をしておく
								$damage=$de2d2[$c] - $at1a1;

								//素早さの差が50%以上ならば１００%ヒットする
								if($bom>=$qu2q2[$c] / 2){
									//quickOne();
									if($damage>0){
										$damage=0;
										if($local){
											$msg_second = 'can not damage ['.$na2[$c].']!';
										}else{
											$msg_second = '「'.$na2[$c].'」は咄嗟に避けた！ダメージを与えられない！';
										}
									}else{
										//攻撃力が二倍または四倍になった
										if($uni_lucky1){
											if($local){
												$msg_firstsecond = 'inflicted "intense damage" on '.$na2[$c].'!!';
											}else{
												$msg_firstsecond = '「'.$na2[$c].'」に【渾身の一撃】を放った！！';
											}
										}
										//攻撃を回避できなかった場合にはダメージ０にはしない
										if($damage>=0){
											$damage=-1;
										}
										$mdamage=$damage*-1;
										if($local){
											$msg_second = '['.$na2[$c].'] could not avoid attack!'.$mdamage.' damage!';
										}else{
											$msg_second = '「'.$na2[$c].'」は攻撃を受けてしまった！'.$mdamage.'のダメージ！';
										}
									}
									//ダメージを受けた分をhpから差し引く
									$hp2[$c] = $hp2[$c] + $damage;

									//hp2がマイナスの場合は０と表示する
									if($hp2[$c]<1){
										$hp2[$c]=0;
									}
									//素早さの差が２５%以上５０%未満ならば７５%ヒットする
								}else if($bom>=$qu2q2[$c] / 4 && $bom <$qu2q2[$c] / 2){
									$b=rand(0,99);
									if($b<74){
									//quickOne();
										
										if($damage>0){
											$damage=0;
											if($local){
												$msg_second = '['.$na2[$c].'] quickly dodged the attack!';
											}else{
												$msg_second = '「'.$na2[$c].'」は素早く攻撃をかわした！';
											}
										}else{
											//攻撃力が二倍または四倍になった
											if($uni_lucky1){
												if($local){
													$msg_firstsecond = 'inflicted "violent damage" on '.$na2[$c].'!!';
												}else{
													$msg_firstsecond = '「'.$na2[$c].'」に【痛恨の一撃】を放った！！';
												}
											}
											//攻撃を回避できなかった場合にはダメージ０にはしない
											if($damage>=0){
												$damage=-1;
											}
											$mdamage=$damage*-1;
											if($local){
												$msg_second = '['.$na2[$c].'] could not avoid attack!'.$mdamage.' damage!';
											}else{
												$msg_second = '「'.$na2[$c].'」は攻撃を避けきれなかった！'.$mdamage.'のダメージ！';
											}
										}
										//ダメージを受けた分をhpから差し引く
										$hp2[$c] = $hp2[$c] + $damage;

										//hp2がマイナスの場合は０と表示する
										if($hp2[$c]<1){
											$hp2[$c]=0;
										}
									}else{
										if($local){
											$msg_second = '['.$na2[$c].'] dodged attack!';
										}else{
											$msg_second = '「'.$na2[$c].'」は攻撃をかわした！';
										}
									}
								//それ未満なら５０%ヒットする
								}else{
									$b=rand(0,99);
									if($b<49){
										//quickOne();
										if($damage>0){
											$damage=0;
											if($local){
												$msg_second = 'attack did not hit ['.$na2[$c].']!';
											}else{
												$msg_second = '攻撃は「'.$na2[$c].'」に当たらなかった！';
											}
										}else{
											//攻撃力が二倍または四倍になった
											if($uni_lucky1){
												if($local){
													$msg_firstsecond = 'inflicted "fierce damage" on '.$na2[$c].'!!';
												}else{
													$msg_firstsecond = '「'.$na2[$c].'」に【会心の一撃】を放った！！';
												}
											}
											//攻撃を回避できなかった場合にはダメージ０にはしない
											if($damage>=0){
												$damage=-1;
											}
											$mdamage=$damage*-1;
											if($local){
												$msg_second = '['.$na2[$c].'] was attacked!'.$mdamage.' damage!';
											}else{
												$msg_second = '「'.$na2[$c].'」は攻撃を受けてしまった！'.$mdamage.'のダメージ！';
											}
										}
										//ダメージを受けた分をhpから差し引く
										$hp2[$c] = $hp2[$c] + $damage;

										//hp2がマイナスの場合は０と表示する
										if($hp2[$c]<1){
											$hp2[$c]=0;
										}
										
									}else{
										if($local){
											$msg_second = '['.$na2[$c].'] was able to avoid attack!';
										}else{
											$msg_second = '「'.$na2[$c].'」は攻撃を避ける事ができた！';
										}
									}
								}
								if(isset($msg_firstsecond)){
									$mess[] = $msg_firstsecond;		
								}
								$mess[] = $msg_second;
								
								//二人のステータスを表示
								if($local){
									if($place==3){
										$mess[] = ' --'.$na1.' HP ['.$hp1.'] / '.$na2[$c].' HP ['.$hp2[$c].'] --';
									}else{
										$mess[] = ' --Ghost HP ['.$hp1.'] / '.$na2[$c].' HP ['.$hp2[$c].'] --';
									}
								}else{
									if($place==3){
										$mess[] = ' --'.$na1.'の残HP【'.$hp1.'】対 '.$na2[$c].'の残HP【'.$hp2[$c].'】--';
									}else{
										$mess[] = ' --おばけの残HP【'.$hp1.'】対 '.$na2[$c].'の残HP【'.$hp2[$c].'】--';
									}
								}
								//ded
								if($hp2[$c]<1){
									if($local){
										$mess[] = '['.$na2[$c]."] lost...";
									}else{
										$mess[] = '「'.$na2[$c]."」は敗北してしまった。";
									}
									$p++;
								}
								//msg_firstsecondを空にしておく
								$msg_firstsecond="";
							//素早さがパーティメンバーのほうが大きい場合
							}else if($qu1<$qu2[$c] && !$first_attack ||$first_attack==1){
								//マスターの魅力が9以下でマスターではない場合でマスターがサヨレン以外で仲間がサヨレンの知り合いでない場合
								if($sc2[0]<10&&$nu2[$c]!=$nu2[0]&&$nu2[$c]>24&&$nu2[$c]<36){
									if($local){
										$mess[]= "[".$na2[$c]."] does not come out of the soul sleep jar.......";
										$mess[]= "[".$na2[$c]."] seems to have lost his fighting spirit!";
									}else{
										$mess[]= "「".$na2[$c]."」は魂眠の壺から出てこない.......";
										$mess[]= "「".$na2[$c]."」は戦意を失っているようだ！";
									}
									$hp2[$c]=0;
									$nf=1;
									$p++;
									//msg_firstsecondを空にしておく
									$msg_firstsecond="";
								}else{
									//先制攻撃フラグに2を入れる
									$first_attack = 2;
									//msgを出力
									if($local){
										$mess[] = '['.$na2[$c].'] has moved! Attack of ['.$na2[$c].']!';
									}else{
										$mess[] = '「'.$na2[$c].'」が動いた！「'.$na2[$c].'」の攻撃！';
									}

									//攻撃がヒットするかの判定
									$bom = $qu2q2[$c] - $qu1q1;

									//ダメージの演算をしておく
									$damage=$de1d1-$at2a2[$c];

									//素早さの差が50%以上ならば１００%ヒットする
									if($bom>=$qu1q1/2){
										//quickOne();
										if($damage>0){
											$damage=0;
											if($local){
												$msg_second = 'The attack of ['.$na2[$c].'] has come off! not damaged!';
											}else{
												$msg_second = '「'.$na2[$c].'」の攻撃は外れ、ダメージを与えられない！';
											}
										}else{
											//攻撃力が二倍または四倍になった
											if($uni_lucky2){
												if($local){
													$msg_firstsecond = '['.$na2[$c].'] inflicted "fierce damage"!!';
												}else{
													$msg_firstsecond = '「'.$na2[$c].'」は【渾身の一撃】を放った！！';
												}
											}
											//攻撃を回避できなかった場合にはダメージ０にはしない
											if($damage>=0){
												$damage=-1;
											}
											$mdamage=$damage*-1;
											if($local){
												$msg_second = 'attacked by ['.$na2[$c].']!'.$mdamage.' damage!';
											}else{
												$msg_second = '「'.$na2[$c].'」の一撃が放たれた！'.$mdamage.'のダメージ！';
											}
										}
										//ダメージを受けた分をhpから差し引く
										$hp1 = $hp1 + $damage;

										//hp2がマイナスの場合は０と表示する
										if($hp1<1){
											$hp1=0;
										}
										//素早さの差が２５%以上５０%未満ならば７５%ヒットする
									}else if($bom>=$qu1q1/4 && $bom <$qu1q1/2){
										$b=rand(0,99);
										if($b<74){
											if($damage>0){
												$damage=0;
												if($local){
													$msg_second = '['.$na2[$c].'] attack has been avoided!';
												}else{
													$msg_second = '「'.$na2[$c].'」の攻撃は避けられてしまった！';
												}
											}else{
												//攻撃力が二倍または四倍になった
												if($uni_lucky2){
													if($local){
														$msg_firstsecond = '['.$na2[$c].'] inflicted "violent damage"!!';
													}else{
														$msg_firstsecond = '「'.$na2[$c].'」は【痛恨の一撃】を放った！！';
													}
												}
												//攻撃を回避できなかった場合にはダメージ０にはしない
												if($damage>=0){
													$damage=-1;
												}
												$mdamage=$damage*-1;
												if($local){
													$msg_second = 'attacked by ['.$na2[$c].']!'.$mdamage.' damage!';
												}else{
													$msg_second = '「'.$na2[$c].'」の攻撃が捉えた！'.$mdamage.'のダメージ！';
												}
											}
											//ダメージを受けた分をhpから差し引く
											$hp1 = $hp1 + $damage;

											//hp2がマイナスの場合は０と表示する
											if($hp1<1){
												$hp1=0;
											}
										}else{
											if($local){
												$msg_second = '['.$na2[$c].'] attack has been avoided!';
											}else{
												$msg_second = '「'.$na2[$c].'」の攻撃はひらりとかわされた！';
											}
										}
									//それ未満なら５０%ヒットする
									}else{
										$b=rand(0,99);
										if($b<49){
											//quickOne();
											if($damage>0){
												$damage=0;
												if($local){
													$msg_second = '['.$na2[$c].'] attack did not hit!';
												}else{
													$msg_second = '「'.$na2[$c].'」の攻撃は当たらなかった！';
												}
											}else{
												//攻撃力が二倍または四倍になった
												if($uni_lucky2){
													if($local){
														$msg_firstsecond = '['.$na2[$c].'] inflicted "intense damage" on the ghost!!';
													}else{
														$msg_firstsecond = '「'.$na2[$c].'」は【渾身の一撃】を放った！！';
													}
												}
												//攻撃を回避できなかった場合にはダメージ０にはしない
												if($damage>=0){
													$damage=-1;
												}
												$mdamage=$damage*-1;
												if($local){
													$msg_second = 'attacked by ['.$na2[$c].']!'.$mdamage.' damage!';
												}else{
													$msg_second = '「'.$na2[$c].'」の攻撃を喰らってしまった！'.$mdamage.'のダメージ！';
												}
											}
											//ダメージを受けた分をhpから差し引く
											$hp1 = $hp1 + $damage;

											//hp2がマイナスの場合は０と表示する
											if($hp1<1){
												$hp1=0;
											}
											
										}else{
											if($local){
												$msg_second = '['.$na2[$c].'] attack has been avoided!';
											}else{
												$msg_second = '「'.$na2[$c].'」の攻撃から逃げられた！';
											}
										}
									}
									if(isset($msg_firstsecond)){
										$mess[] = $msg_firstsecond;		
									}
									$mess[] = $msg_second;
									
									//二人のステータスを表示
									if($local){
										if($place==3){
											$mess[] = ' --'.$na1.' HP ['.$hp1.'] / '.$na2[$c].' HP ['.$hp2[$c].'] --';
										}else{
											$mess[] = ' --Ghost HP ['.$hp1.'] / '.$na2[$c].' HP ['.$hp2[$c].'] --';
										}
									}else{
										if($place==3){
											$mess[] = ' --'.$na1.'の残HP【'.$hp1.'】対 '.$na2[$c].'の残HP【'.$hp2[$c].'】--';
										}else{
											$mess[] = ' --おばけの残HP【'.$hp1.'】対 '.$na2[$c].'の残HP【'.$hp2[$c].'】--';
										}
									}
									//ded
									if($hp1<1){
										if($local){
											if($place==3){
												$mess[] = $na1." was defeated by [".$na2[$c]."].Deported to God.";
											}else{
												$mess[] = "Ghost was defeated by [".$na2[$c]."] and purified. Ghost that returned to me was [".$na1."].";
												if(!$t_count){
													$mess[] = 'have to give up picking up "'.$na1.' Soul Fragments" because there are not enough "Soul Sleeping Pots".';
												}else if($t_count==1){
													$mess[] = $na2[0].'picks up the falling '.$na1.'"Soul Fragment" and puts it in the "Soul Sleeping jar" to release '.$na1.' from the spell.';
													$mess[] = 'Master '.$na2[0].' has added '.$na1.' to its collection.';
													$mess[] = 'used all the "soul sleep jars".';
												}else{
													$mess[] = $na2[0].'picks up the falling '.$na1.'"Soul Fragment" and puts it in the "Soul Sleeping jar" to release '.$na1.' from the spell.';
													$mess[] = 'Master '.$na2[0].' has added '.$na1.' to its collection.';
													$mess[] = 'used one of the remaining　'.$t_count.' "Soul Sleeping jar".';
												}
												
											}
										}else{
											if($place==3){
												$mess[] = $na1."は「".$na2[$c]."」に敗北し、眩い光を伴って浄土へ送還された。";
											}else{
												$mess[] = "おばけは「".$na2[$c]."」に敗北して浄化された。我に返ったおばけは「".$na1."」だった。";
												if(!$t_count){
													$mess[] = '「魂眠の壺」の数が足らないので'.$na1.'の「魂のかけら」を拾うのをあきらめざるをえない。';
												}else if($t_count==1){
													$mess[] = $na2[0].'は落ちている'.$na1.'の「魂のかけら」を拾い「魂眠の壺」に入れて'.$na1.'を呪縛から解放する。';
													$mess[] = 'マスター'.$na2[0].'は'.$na1.'をコレクションに加えた。';
													$mess[] = '魂眠の壺を全て使ってしまった。';
												}else{
													$mess[] = $na2[0].'は落ちている'.$na1.'の「魂のかけら」を拾い「魂眠の壺」に入れて'.$na1.'を呪縛から解放する。';
													$mess[] = 'マスター'.$na2[0].'は'.$na1.'をコレクションに加えた。';
													$mess[] = '魂眠の壺の残り'.$t_count.'個の内１つを使った。';
												}
											}
										}
										//Master is SAYO or REN or URARA
										switch($nu2[0]){
											case 1:
												if($local){
													if($nu1>3&&$nu1<6||$nu1==10){
														$mess[]='( ﾟДﾟ)!!!!!!!';
														$mess[]='that! What happened?';
														$mess[]='e!['.$na2[0].'] Ah! It is ['.$na2[0].']. It is me ['.$na1.']!';
														$mess[]='(; ∀;) It is been a long time! It is still at that time!';
														$mess[]='Looking closely at '.$na2[0].', It was the face of '.$na1.', At the same school!';
														$mess[]=$na2[0].' was a little happy (*艸 `) to see '.$na1.'`s smile.';
													}else if($nu1==3){
														$mess[]='!!!';
														$mess[]='that! What happened?';
														$mess[]='e!['.$na2[0].'] Ah! It is ['.$na2[0].']. It is me ['.$na1.']!';
														$mess[]='(; ∀;) It is been a long time! It is still at that time!';
														$mess[]='Looking closely at '.$na2[0].', It was the face of '.$na1.'!';
														$mess[]=$na2[0].' was a very happy( *´艸`) to see '.$na1.'`s smile.';
													}else if($nu1>=6&&$nu1<=8){
														$mess[]='!!!';
														$mess[]='What the hell! What happened!';
														$mess[]='Ah! Chiyo! Was it safe!';
														$mess[]='Suddenly a strong warrior picked up '.$na2[0].' with a big hand.';
														$mess[]='Uncle who does not know the strong side even if '.$na2[0].' looks closely (ﾟ Д ﾟ)';
														$mess[]='Uncle or Mister! I am not Chiyo! I am '.$na2[0].'. Are you mistaken for someone?';
														$mess[]=$na2[0].' is confused(*_*)';
													}else if($nu1==9){
														$mess[]='!!!';
														$mess[]='What! Ah!';
														$mess[]='princess! Miss Chiyo! it safe!';
														$mess[]='A young warrior with a fearless look kneels in front of '.$na2[0].'.';
														$mess[]='An older brother who does not know even if '.$na2[0].' looks closely( ﾟДﾟ)';
														$mess[]='Mister! I am not Chiyo! I am '.$na2[0].'. Are you mistaken for someone?';
														$mess[]=$na2[0].' is confused(*_*)';
													}
												}else{
													if($nu1>3&&$nu1<6||$nu1==10){
														$mess[]='( ﾟДﾟ)！！！！！！';
														$mess[]='あれ！どうしちゃったんだろ？';
														$mess[]='え！'.$na2[0].'さん？あ！'.$na2[0].'さんだ。私だよ「'.$na1.'」！';
														$mess[]='( ;∀;)久しぶりだねー！あの時のまんまだよ！';
														$mess[]=$na2[0].'がよく見ると大分成長しているが同じクラスの'.$na1.'の顔だった！';
														$mess[]='久しぶりに見た'.$na1.'の姿に'.$na2[0].'はちょっと嬉かった( *´艸｀)';
													}else if($nu1==3){
														$mess[]='！！！';
														$mess[]='あれ！どうしちゃったんだろ？';
														$mess[]='え！'.$na2[0].'さん？私「'.$na1.'」だよ！';
														$mess[]='( ;∀;)久しぶりだねー！あの時のままね！';
														$mess[]=$na2[0].'がよく見ると'.$na1.'の顔だった！';
														$mess[]='久しぶりに見た'.$na1.'の姿に'.$na2[0].'はかなり嬉かった( *´艸｀)';
													}else if($nu1>=6&&$nu1<=8){
														$mess[]='！！！';
														$mess[]='何事じゃぁ！どうした！';
														$mess[]='あ！千代！大事無いかっ！';
														$mess[]='突然強面の武者が'.$na2[0].'を大きな手で抱き上げた。';
														$mess[]=$na2[0].'がよく見ても強面の知らないおじさん( ﾟДﾟ)';
														$mess[]='おじさん！私は千代じゃないよ！'.$na2[0].'だよ！誰かと間違えてない？';
														$mess[]=$na1.'の行動に'.$na2[0].'は困惑している(*_*)';
														
													}else if($nu1==9){
														$mess[]='！！！';
														$mess[]='何事！あっ！';
														$mess[]='姫！千代姫様！ご無事でしたか！';
														$mess[]='精悍な顔つきの若武者が'.$na2[0].'の前に膝まづく。';
														$mess[]=$na2[0].'がよく見ても知らないお兄さん( ﾟДﾟ)';
														$mess[]='私は千代姫じゃないよ！'.$na2[0].'だよ！誰かと間違えてない？';
														$mess[]=$na1.'の神妙な対応に'.$na2[0].'は困惑している(*_*)';
													}
												}
											break;
											case 2:
												if($local){
													if($nu1>3&&$nu1<6||$nu1==10){
														$mess[]='( ﾟДﾟ)!!!!!!!';
														$mess[]='that! What happened?';
														$mess[]='e!['.$na2[0].'] Ah! It is ['.$na2[0].']. It is me ['.$na1.']!';
														$mess[]='(; ∀;) It is been a long time! It is still at that time!';
														$mess[]='Looking closely at '.$na2[0].', It was the face of '.$na1.', At the same school!';
														$mess[]=$na2[0].' was a little happy (*艸 `) to see '.$na1.'`s smile.';
														
													}else if($nu1==3){
														$mess[]='!!!';
														$mess[]='that! What happened?';
														$mess[]='e!['.$na2[0].'] Ah! It is ['.$na2[0].']. It is me ['.$na1.']!';
														$mess[]='(; ∀;) It is been a long time! It is still at that time!';
														$mess[]='Looking closely at '.$na2[0].', It was the face of '.$na1.'!';
														$mess[]=$na2[0].' was a very happy( *´艸`) to see '.$na1.'`s smile.';
													}
												}else{
													if($nu1>3&&$nu1<6||$nu1==10){
														$mess[]='( ﾟДﾟ)！！！！！！';
														$mess[]='あれ！どうしちゃったんだろ？';
														$mess[]='え！'.$na2[0].'さん？あ！'.$na2[0].'さんだ。私だよ「'.$na1.'」！';
														$mess[]='( ;∀;)久しぶりだねー！あの時のまんまだよ！';
														$mess[]=$na2[0].'がよく見ると大分成長しているが同じクラスの'.$na1.'の顔だった！';
														$mess[]=$na1.'の笑顔が'.$na2[0].'はちょっと嬉かった( *´艸｀)';
													}else if($nu1==3){
														$mess[]='！！！';
														$mess[]='あれ！どうしちゃったんだろ？';
														$mess[]='え！'.$na2[0].'さん？私「'.$na1.'」だよ！';
														$mess[]='( ;∀;)久しぶりだねー！あの時のままね！';
														$mess[]=$na2[0].'がよく見ると'.$na1.'の顔だった！';
														$mess[]=$na1.'の優しい目に見つめられて'.$na2[0].'はかなり嬉かった( *´艸｀)';
													}
												}
											break;
											case 3:
												if($local){
													if($nu1==11){
														$mess[]='( ﾟДﾟ)!!!!!!';
														$mess[]='What happened?';
														$mess[]='!!['.$na2[0].'] Ah! It is ['.$na2[0].']. It is me ['.$na1.']!';
														$mess[]='(; ∀;) It is been a long time! It is still at that time!';
														$mess[]='Looking closely at '.$na2[0].', It was the face of '.$na1.'!';
														$mess[]=$na2[0].' was not happy...';
													}
												}else{
													if($nu1==11){
														$mess[]='( ﾟДﾟ)！！！！！！';
														$mess[]='あれ！どうしちゃったんだろ？';
														$mess[]='!!'.$na2[0].'さん？あ！'.$na2[0].'さんだ。ぼくだよ「'.$na1.'」！';
														$mess[]='( ;∀;)久しぶりだねー！あの時のまんまだよ！';
														$mess[]=$na2[0].'がよく見ると同じクラスの'.$na1.'の顔だった！';
														$mess[]=$na1.'のマシンガントークに'.$na2[0].'はちょっと複雑だった...';
													}
												}
										}
										$otosimono=rand(0,$ic);
										//1/$icの確率で落とすので拾う
										if($otosimono==0){
											$mono=rand(0,1);
											if($mono==1){
												$mono=rand(0,4);
											}
											switch($mono){
												case 0:
													$emono=rand(0,16);
													if($emono==15){
														$emono=rand(0,16);
														if($emono==15){
															$emono=rand(0,16);
															if($emono==15){
																$emono=rand(0,16);
																if($emono==15){//坂巻のネジは無しにしとく
																	$emono=0;//1/759375
																}
															}
														}
													}
													if($t_count<10){
														$emono=16;
													}
													if($local){
														$gets='Item:';
														switch($emono){
															case 0: $gets=$gets.'Candy';	break;
															case 1:	$gets=$gets.'SweetBall';	break;
															case 2:	$gets=$gets.'BigSweetBall';	break;
															case 3:	$gets=$gets.'PickledPlums'; break;
															case 4:	$gets=$gets.'BontanAme';	break;
															case 5:	$gets=$gets.'Confetti';	break;
															case 6:	$gets=$gets.'KinakoStick'; break;
															case 7:	$gets=$gets.'Butamen';	break;
															case 8:	$gets=$gets.'Lillian';	break;
															case 9:	$gets=$gets.'Menko';	break;
															case 10:	$gets=$gets.'PicoPicoHammer';	break;
															case 11:	$gets=$gets.'ToySword';	break;
															case 12:	$gets=$gets.'ToyGun';	break;
															case 13:	$gets=$gets.'TantrumBall';	break;
															case 14:	$gets=$gets.'GhostSmoke';	break;
															case 15:	$gets=$gets.'Sakamaki';	break;
															case 16:$gets=$gets.'SoulSleepJar';break;
														}
													}else{
														$gets='アイテム：';
														switch($emono){
															case 0: $gets=$gets.'オーラ飴';	break;
															case 1:	$gets=$gets.'あんこ玉';	break;
															case 2:	$gets=$gets.'あんこ玉(大)';	break;
															case 3:	$gets=$gets.'すもも漬け'; break;
															case 4:	$gets=$gets.'ボンタンアメ';	break;
															case 5:	$gets=$gets.'コンペイトウ';	break;
															case 6:	$gets=$gets.'きなこボー'; break;
															case 7:	$gets=$gets.'ブタメン';	break;
															case 8:	$gets=$gets.'リリアン';	break;
															case 9:	$gets=$gets.'めんこ';	break;
															case 10:	$gets=$gets.'ピコピコハンマー';	break;
															case 11:	$gets=$gets.'セルロイドの刀';	break;
															case 12:	$gets=$gets.'銀玉鉄砲';	break;
															case 13:	$gets=$gets.'かんしゃく玉';	break;
															case 14:	$gets=$gets.'おばけけむり';	break;
															case 15:	$gets=$gets.'坂巻のネジ';	break;
															case 16:$gets=$gets.'魂眠の壺';break;
														}
													}
												break;
												case 1:
													$emono=rand(0,4);
													if($emono>3){
														$emono=rand(0,5);
														if($emono>4){
															$emono=rand(0,8);
															if($emono>7){
																$emono=rand(12,15);
																if($emono==12){
																	$emono=rand(0,17);
																}
															}
														}
													}
													if($local){
														$gets='Weapon:';
														switch($emono){
															case 0:	$gets=$gets.'Stick';	break;
															case 1:	$gets=$gets.'BigStick';	break;
															case 2:	$gets=$gets.'Y-Pachinko';	break;
															case 3:	$gets=$gets.'WaterGun';	break;
															case 4:	$gets=$gets.'BambooSword';	break;
															case 5:	$gets=$gets.'WoodSword';	break;
															case 6:	$gets=$gets.'ShortSword';	break;
															case 7:	$gets=$gets.'LongSword';	break;
															case 8:	$gets=$gets.'Sword';	break;
															case 9:	$gets=$gets.'Masamune';	break;
															case 10:	$gets=$gets.'Onikirimaru';	break;
															case 11:	$gets=$gets.'Murasame';	break;
															case 12:	$gets=$gets.'Doutanuki';	break;
															case 13:	$gets=$gets.'BambooPole';	break;
															case 14:	$gets=$gets.'PracticeSpear';	break;
															case 15:	$gets=$gets.'PracticeNaginata';	break;
															case 16:	$gets=$gets.'Tonbokiri';	break;
															case 17:	$gets=$gets.'Iwatoushi';	break;
														}
													}else{
														$gets='武器：';
														switch($emono){
															case 0:	$gets=$gets.'木の枝';	break;
															case 1:	$gets=$gets.'棍棒';	break;
															case 2:	$gets=$gets.'Y字方パチンコ';	break;
															case 3:	$gets=$gets.'水鉄砲';	break;
															case 4:	$gets=$gets.'竹刀';	break;
															case 5:	$gets=$gets.'木刀';	break;
															case 6:	$gets=$gets.'脇差(無銘)';	break;
															case 7:	$gets=$gets.'太刀(無銘)';	break;
															case 8:	$gets=$gets.'打刀(無銘)';	break;
															case 9:	$gets=$gets.'不動正宗';	break;
															case 10:	$gets=$gets.'鬼切丸';	break;
															case 11:	$gets=$gets.'村雨';	break;
															case 12:	$gets=$gets.'同田貫';	break;
															case 13:	$gets=$gets.'竿竹';	break;
															case 14:	$gets=$gets.'たんぽ槍';	break;
															case 15:	$gets=$gets.'竹製なぎなた';	break;
															case 16:	$gets=$gets.'蜻蛉切';	break;
															case 17:	$gets=$gets.'岩融';	break;
														}
													}
												break;
												case 2:
													$emono=rand(0,2);
													if($emono!=0){
														$emono=rand(0,2);
														if($emono!=0){
															$emono=rand(0,2);
															if($emono!=0){
																$emono=rand(0,2);
															}
														}
													}
													if($local){
														$gets='Glove:';
														switch($emono){
															case 0:	$gets=$gets.'Gunte';	break;
															case 1:	$gets=$gets.'LeatherGlove';	break;
															case 2:	$gets=$gets.'Gauntlet';	break;
														}
													}else{
														$gets='手袋：';
														switch($emono){
															case 0:	$gets=$gets.'軍手';	break;
															case 1:	$gets=$gets.'皮の手袋';	break;
															case 2:	$gets=$gets.'籠手';	break;
														}
													}
												break;
												case 3:
													$emono=rand(0,1);
													if($emono!=0){
														$emono=rand(0,1);
														if($emono!=0){
															$emono=rand(0,1);
															if($emono!=0){
																$emono=rand(0,1);
																if($emono!=0){
																	$emono=rand(0,2);
																	if($emono>=1){
																		$emono=rand(0,2);
																		if($emono>=1){
																			$emono=rand(0,2);
																			if($emono>=1){
																				$emono=rand(0,2);
																				if($emono>=1){
																					$emono=rand(0,2);
																					if($emono>=1){
																						$emono=rand(0,3);
																						if($emono==3){
																							$emono=rand(0,3);
																							if($emono==3){
																								$emono=rand(0,3);
																								if($emono==3){
																									$emono=rand(0,3);
																								}
																							}
																						}
																					}
																				}
																			}
																		}
																	}
																}
															}
														}
													}
													if($local){
														$gets='Armored:';
														switch($emono){
															case 0:	$gets=$gets.'BambooArmor';	break;
															case 1:	$gets=$gets.'Armor';	break;
															case 2:	$gets=$gets.'NanbanArmor';	break;
															case 3:	$gets=$gets.'PlatinumArmor';	break;
														}
													}else{
														$gets='防具：';
														switch($emono){
															case 0:	$gets=$gets.'竹胴';	break;
															case 1:	$gets=$gets.'無銘具足';	break;
															case 2:	$gets=$gets.'南蛮胴具足';	break;
															case 3:	$gets=$gets.'紺糸裾素懸威胴丸';	break;
														}
													}
												break;
												case 4:
													$emono=rand(0,1);
													if($emono==1){
														$emono=rand(0,1);
														if($emono==0){
															$emono=rand(0,1);
															if($emono==1){
																$emono=rand(0,1);
																if($emono==0){
																	$emono=rand(0,1);
																	if($emono==1){
																		$emono=rand(0,1);
																		if($emono==0){
																			$emono=rand(0,2);
																			if($emono==2){
																				$emono=rand(0,2);
																				if($emono==2){
																					$emono=rand(0,2);
																					if($emono==2){
																						$emono=rand(0,2);
																						if($emono==2){
																							$emono=rand(0,2);
																							if($emono==2){
																								$emono=rand(0,2);
																							}
																						}
																					}
																				}
																			}
																		}
																	}
																}
															}
														}
													}
													if($local){
														$gets='Shoes:';
														switch($emono){
															case 0:	$gets=$gets.'Shunsoku';	break;
															case 1:	$gets=$gets.'SafetyBoots';	break;
															case 2:	$gets=$gets.'Renshihounri';	break;
														}
													}else{
														$gets='靴：';
														switch($emono){
															case 0:	$gets=$gets.'瞬足';	break;
															case 1:	$gets=$gets.'安全靴';	break;
															case 2:	$gets=$gets.'蓮糸歩雲履';	break;
														}
													}
											}
												$mess[] = 'START_EVENT!';
												if($local){
													$mess[] = 'Suddenly found something, interested in. When I pick it up, it looks like '.$gets.'!';
													$mess[] = 'Got ['.$gets.']!';
												}else{
													$mess[] = 'ふと何か気になるものを見つけた。拾ってみると'.$gets.'のようだ！';
													$mess[] = '「'.$gets.'」をゲットした！';
												}
										}
										$battle_loop=$i;
										if($otosimono==0){
											update_sql(0,$nu1,$mono,$emono,0,$t_count);
										}else{
											update_sql(0,$nu1,100,100,0,$t_count);
										}
										continue 2;//ここから2つ前のループを抜ける
									}
									//msg_firstsecondを空にしておく
									$msg_firstsecond="";
								}
							//素早さが同じ場合
							}else if($qu1 == $qu2[$c]){
								if($local){
									$mess[] = "[".$na2[$c]."] are stuck together!";
								}else{
									$mess[] = "「".$na2[$c]."」は互いに動けないでいる！";
								}
								//抽選するよ:５０％の確率
								//素早さが同じでおばけから攻撃する場合
								if(rand(0, 1)) {
									//先制攻撃フラグに１を入れる
									$first_attack = 1;
									//msgを出力
									if($local){
										$mess[] = '['.$na2[$c].'] is targeted!';
									}else{
										$mess[] = '「'.$na2[$c].'」が狙われている！';
									}

									//攻撃がヒットするかの判定
									$bom = $qu1q1 - $qu2q2[$c];

									//ダメージの演算をしておく
									$damage=$de2d2[$c] - $at1a1;

									//素早さの差が50%以上ならば１００%ヒットする
									if($bom>=$qu2q2[$c] / 2){
										//quickOne();
										if($damage>0){
											$damage=0;
											if($local){
												$msg_second = 'attack did not hit ['.$na2[$c].']!';
											}else{
												$msg_second = '「'.$na2[$c].'」への攻撃は外れた！';
											}
										}else{
											//攻撃力が二倍または四倍になった
											if($uni_lucky1){
												if($local){
													$msg_firstsecond = 'inflicted "fierce damage" on '.$na2[$c].'!!';
												}else{
													$msg_firstsecond = '「'.$na2[$c].'」に【渾身の一撃】を放った！！';
												}
											}
											//攻撃を回避できなかった場合にはダメージ０にはしない
											if($damage>=0){
												$damage=-1;
											}
											$mdamage=$damage*-1;
											if($local){
												$msg_second = 'blow hits ['.$na2[$c].']!'.$mdamage.' damage!';
											}else{
												$msg_second = '「'.$na2[$c].'」に一撃が命中する！'.$mdamage.'のダメージ！';
											}
										}
										//ダメージを受けた分をhpから差し引く
										$hp2[$c] = $hp2[$c] + $damage;

										//hp2がマイナスの場合は０と表示する
										if($hp2[$c]<1){
											$hp2[$c]=0;
										}
									//素早さの差が２５%以上５０%未満ならば７５%ヒットする
									}else if($bom>=$qu2q2[$c] / 4 && $bom <$qu2q2[$c] / 2){
										$b=rand(0,99);
										if($b<74){
											if($damage>0){
												$damage=0;
												if($local){
													$msg_second = 'attack did not hit ['.$na2[$c].']!';
												}else{
													$msg_second = '「'.$na2[$c].'」への攻撃は空ぶった！';
												}
											}else{
												//攻撃力が二倍または四倍になった
												if($uni_lucky1){
													if($local){
														$msg_firstsecond = 'inflicted "fierce damage" on '.$na2[$c].'!!';
													}else{
														$msg_firstsecond = '「'.$na2[$c].'」に【痛恨の一撃】を放った！！';
													}
												}
												//攻撃を回避できなかった場合にはダメージ０にはしない
												if($damage>=0){
													$damage=-1;
												}
												$mdamage=$damage*-1;
												if($local){
													$msg_second = 'Blow hits ['.$na2[$c].']!'.$mdamage.' damage!';
												}else{
													$msg_second = '「'.$na2[$c].'」は'.$mdamage.'のダメージを受けてしまった！';
												}
											}
											//ダメージを受けた分をhpから差し引く
											$hp2[$c] = $hp2[$c] + $damage;

											//hp2がマイナスの場合は０と表示する
											if($hp2[$c]<1){
												$hp2[$c]=0;
											}
										}else{
											if($local){
												$msg_second = '['.$na2[$c].'] dodged the attack!';
											}else{
												$msg_second = '「'.$na2[$c].'」は見切った攻撃をかわした！';
											}
										}
									//それ未満なら５０%ヒットする
									}else{
										$b=rand(0,99);
										if($b<49){
											if($damage>0){
												$damage=0;
												if($local){
													$msg_second = 'attack did not hit ['.$na2[$c].']!';
												}else{
													$msg_second = '「'.$na2[$c].'」に攻撃をしたが外れてしまった！';
												}
											}else{
												//攻撃力が二倍または四倍になった
												if($uni_lucky1){
													if($local){
														$msg_firstsecond = 'inflicted "fierce damage" on '.$na2[$c].'!!';
													}else{
														$msg_firstsecond = '「'.$na2[$c].'」に【会心の一撃】を放った！！';
													}
												}
												//攻撃を回避できなかった場合にはダメージ０にはしない
												if($damage>=0){
													$damage=-1;
												}
												$mdamage=$damage*-1;
												if($local){
													$msg_second = 'Blow hits ['.$na2[$c].']!'.$mdamage.' damage!';
												}else{
													$msg_second = '「'.$na2[$c].'」は'.$mdamage.'のダメージを喰らってしまった！';
												}
											}
											//ダメージを受けた分をhpから差し引く
											$hp2[$c] = $hp2[$c] + $damage;

											//hp2がマイナスの場合は０と表示する
											if($hp2[$c]<1){
												$hp2[$c]=0;
											}
										
										}else{
											if($local){
												$msg_second = '['.$na2[$c].'] dodged the attack!';
											}else{
												$msg_second = '「'.$na2[$c].'」は攻撃をかわした！';
											}
										}
									}
									if(isset($msg_firstsecond)){
										$mess[] = $msg_firstsecond;		
									}
									$mess[] =  $msg_second;
								
									//二人のステータスを表示
									if($local){
										if($place==3){
											$mess[] = ' --'.$na1.' HP ['.$hp1.'] / '.$na2[$c].' HP ['.$hp2[$c].'] --';
										}else{
											$mess[] = ' --Ghost HP ['.$hp1.'] / '.$na2[$c].' HP ['.$hp2[$c].'] --';
										}
									}else{
										if($place==3){
											$mess[] = ' --'.$na1.'の残HP【'.$hp1.'】対 '.$na2[$c].'の残HP【'.$hp2[$c].'】--';
										}else{
											$mess[] = ' --おばけの残HP【'.$hp1.'】対 '.$na2[$c].'の残HP【'.$hp2[$c].'】--';
										}
									}
									//ded
									if($hp2[$c]<1){
										if($local){
											$mess[] = '['.$na2[$c]."] lost...";
										}else{
											$mess[] = '「'.$na2[$c]."」は負けてしまった。";
										}
										$p++;
									}
									//msg_firstsecondを空にしておく
									$msg_firstsecond="";
								//素早さが同じでパーティメンバーから攻撃する場合
								}else {
									//マスターの魅力が9以下でマスターではない場合でマスターがサヨかレン以外で仲間がサヨレンの知り合いではない場合
									if($sc2[0]<10&&$nu2[$c]!=$nu2[0]&&$nu2[$c]>12&&$nu2[$c]<36){
										if($local){
											$mess[]= "[".$na2[$c]."] does not come out of the soul sleep jar.......";
											$mess[]= "[".$na2[$c]."] seems to have lost his fighting spirit!";
										}else{
											$mess[]= "「".$na2[$c]."」は魂眠の壺から出てこない.......";
											$mess[]= "「".$na2[$c]."」は戦意を失っているようだ！";
										}
										$hp2[$c]=0;
										$nf=1;
										$p++;
										//msg_firstsecondを空にしておく
										$msg_firstsecond="";
									}else{
										//先制攻撃フラグに2を入れる
										$first_attack = 2;
										//msgを出力
										if($local){
											$mess[] = '['.$na2[$c].'] has moved! Attack of ['.$na2[$c].']!';
										}else{
											$mess[] = '「'.$na2[$c].'」が素早く動いた！「'.$na2[$c].'」の攻撃！';
										}

										//攻撃がヒットするかの判定
										$bom = $qu2q2[$c] - $qu1q1;

										//ダメージの演算をしておく
										$damage=$de1d1-$at2a2[$c];

										//素早さの差が50%以上ならば１００%ヒットする
										if($bom>=$qu1q1/2){
											//quickOne();
											if($damage>0){
												$damage=0;
												if($local){
													$msg_second = '['.$na2[$c].'] attack has been avoided!';
												}else{
													$msg_second = '「'.$na2[$c].'」の攻撃は見切られている！';
												}
											}else{
												//攻撃力が二倍または四倍になった
												if($uni_lucky2){
													if($local){
														$msg_firstsecond = '['.$na2[$c].'] inflicted "intense damage"!!';
													}else{
														$msg_firstsecond = '「'.$na2[$c].'」は【渾身の一撃】を放った！！';
													}
												}
												//攻撃を回避できなかった場合にはダメージ０にはしない
												if($damage>=0){
													$damage=-1;
												}
												$mdamage=$damage*-1;
												if($local){
													$msg_second = 'attacked by ['.$na2[$c].']!'.$mdamage.' damage!';
												}else{
													$msg_second = '「'.$na2[$c].'」から'.$mdamage.'のダメージを受けてしまった！';
												}
											}
											//ダメージを受けた分をhpから差し引く
											$hp1 = $hp1 + $damage;

											//hp2がマイナスの場合は０と表示する
											if($hp1<1){
												$hp1=0;
											}
										//素早さの差が２５%以上５０%未満ならば７５%ヒットする
										}else if($bom>=$qu1q1/4 && $bom <$qu1q1/2){
											$b=rand(0,99);
											if($b<74){
											//quickOne();
											
												if($damage>0){
													$damage=0;
													if($local){
														$msg_second = '['.$na2[$c].'] attack did not hit!';
													}else{
														$msg_second = '「'.$na2[$c].'」の攻撃は避けられてしまった！';
													}
												}else{
													//攻撃力が二倍または四倍になった
													if($uni_lucky2){
														if($local){
															$msg_firstsecond = '['.$na2[$c].'] inflicted "intense damage" on the ghost!!';
														}else{
															$msg_firstsecond = '「'.$na2[$c].'」は【痛恨の一撃】を放った！！';
														}
													}
													//攻撃を回避できなかった場合にはダメージ０にはしない
													if($damage>=0){
														$damage=-1;
													}
													$mdamage=$damage*-1;
													if($local){
														$msg_second = 'attacked by ['.$na2[$c].']!'.$mdamage.' damage!';
													}else{
														$msg_second = '「'.$na2[$c].'」からの攻撃で'.$mdamage.'のダメージを受けた！';
													}
												}
												//ダメージを受けた分をhpから差し引く
												$hp1 = $hp1 + $damage;

												//hp2がマイナスの場合は０と表示する
												if($hp1<1){
													$hp1=0;
												}
											}else{
												if($local){
													$msg_second = '['.$na2[$c].'] attack did not hit!';
												}else{
													$msg_second = '「'.$na2[$c].'」の攻撃を避けた！';
												}
											}
										//それ未満なら５０%ヒットする
										}else{
											$b=rand(0,99);
											if($b<49){
												//quickOne();
												if($damage>0){
													$damage=0;
													if($local){
														$msg_second = '['.$na2[$c].'] attack has been avoided!';
													}else{
														$msg_second = '「'.$na2[$c].'」の攻撃はダメージを与えられない！';
													}
												}else{
													//攻撃力が二倍または四倍になった
													if($uni_lucky2){
														if($local){
															$msg_firstsecond = '['.$na2[$c].'] inflicted "intense damage"!!';
														}else{
															$msg_firstsecond = '「'.$na2[$c].'」は【渾身の一撃】を放った！！';
														}
													}
													//攻撃を回避できなかった場合にはダメージ０にはしない
													if($damage>=0){
														$damage=-1;
													}
													$mdamage=$damage*-1;
													if($local){
														$msg_second = 'attacked by ['.$na2[$c].']!'.$mdamage.' damage!';
													}else{
														$msg_second = '「'.$na2[$c].'」の攻撃を喰らってしまった！'.$mdamage.'のダメージ！';
													}
												}
												//ダメージを受けた分をhpから差し引く
												$hp1 = $hp1 + $damage;

												//hp2がマイナスの場合は０と表示する
												if($hp1<1){
													$hp1=0;
												}
											
											}else{
												if($local){
													$msg_second = '['.$na2[$c].'] attack has been avoided!';
												}else{
													$msg_second = '「'.$na2[$c].'」の一撃を避ける事ができた！';
												}
											}
										}
										if(isset($msg_firstsecond)){
											$mess[] = $msg_firstsecond;		
										}
										$mess[] = $msg_second;
									
										//二人のステータスを表示
										if($local){
											if($place==3){
												$mess[] = ' --'.$na1.' HP ['.$hp1.'] / '.$na2[$c].' HP ['.$hp2[$c].'] --';
											}else{
												$mess[] = ' --Ghost HP ['.$hp1.'] / '.$na2[$c].' HP ['.$hp2[$c].'] --';
											}
										}else{
											if($place==3){
												$mess[] = ' --'.$na1.'の残HP【'.$hp1.'】対 '.$na2[$c].'の残HP【'.$hp2[$c].'】--';
											}else{
												$mess[] = ' --おばけの残HP【'.$hp1.'】対 '.$na2[$c].'の残HP【'.$hp2[$c].'】--';
											}
										}
										//ded
										if($hp1<1){
											if($local){
												if($place==3){
													$mess[] = $na1." was defeated by [".$na2[$c]."].Deported to God.";
												}else{
													$mess[] = "Ghost was defeated by [".$na2[$c]."] and purified. Ghost that returned to me was [".$na1."].";
													if(!$t_count){
														$mess[] = 'have to give up picking up "'.$na1.' Soul Fragments" because there are not enough "Soul Sleeping Pots".';
													}else if($t_count==1){
														$mess[] = $na2[0].'picks up the falling '.$na1.'"Soul Fragment" and puts it in the "Soul Sleeping jar" to release '.$na1.' from the spell.';
														$mess[] = 'Master '.$na2[0].' has added '.$na1.' to its collection.';
														$mess[] = 'used all the "soul sleep jars".';
													}else{
														$mess[] = $na2[0].'picks up the falling '.$na1.'"Soul Fragment" and puts it in the "Soul Sleeping jar" to release '.$na1.' from the spell.';
														$mess[] = 'Master '.$na2[0].' has added '.$na1.' to its collection.';
														$mess[] = 'used one of the remaining　'.$t_count.' "Soul Sleeping jar".';
													}
													
												}
											}else{
												if($place==3){
													$mess[] = $na1."は「".$na2[$c]."」に敗北し、眩い光を伴って浄土へ送還された。";
												}else{
													$mess[] = "おばけは「".$na2[$c]."」に敗北して浄化された。我に返ったおばけは「".$na1."」だった。";
													if(!$t_count){
														$mess[] = '「魂眠の壺」の数が足らないので'.$na1.'の「魂のかけら」を拾うのをあきらめざるをえない。';
													}else if($t_count==1){
														$mess[] = $na2[0].'は落ちている'.$na1.'の「魂のかけら」を拾い「魂眠の壺」に入れて'.$na1.'を呪縛から解放する。';
														$mess[] = 'マスター'.$na2[0].'は'.$na1.'をコレクションに加えた。';
														$mess[] = '魂眠の壺を全て使ってしまった。';
													}else{
														$mess[] = $na2[0].'は落ちている'.$na1.'の「魂のかけら」を拾い「魂眠の壺」に入れて'.$na1.'を呪縛から解放する。';
														$mess[] = 'マスター'.$na2[0].'は'.$na1.'をコレクションに加えた。';
														$mess[] = '魂眠の壺の残り'.$t_count.'個の内1つを使った。';
													}
												}
											}
											//Master is SAYO or REN or URARA
											switch($nu2[0]){
												case 1:
													if($local){
														if($nu1>3&&$nu1<6||$nu1==10){
															$mess[]='( ﾟДﾟ)!!!!!!!';
															$mess[]='that! What happened?';
															$mess[]='e!['.$na2[0].'] Ah! It is ['.$na2[0].']. It is me ['.$na1.']!';
															$mess[]='(; ∀;) It is been a long time! It is still at that time!';
															$mess[]='Looking closely at '.$na2[0].', It was the face of '.$na1.', At the same school!';
															$mess[]=$na2[0].' was a little happy (*艸 `) to see '.$na1.'`s smile.';
														}else if($nu1==3){
															$mess[]='!!!';
															$mess[]='that! What happened?';
															$mess[]='e!['.$na2[0].'] Ah! It is ['.$na2[0].']. It is me ['.$na1.']!';
															$mess[]='(; ∀;) It is been a long time! It is still at that time!';
															$mess[]='Looking closely at '.$na2[0].', It was the face of '.$na1.'!';
															$mess[]=$na2[0].' was a very happy( *´艸`) to see '.$na1.'`s smile.';
														}else if($nu1>=6&&$nu1<=8){
															$mess[]='!!!';
															$mess[]='What the hell! What happened!';
															$mess[]='Ah! Chiyo! Was it safe!';
															$mess[]='Suddenly a strong warrior picked up '.$na2[0].' with a big hand.';
															$mess[]='Uncle who does not know the strong side even if '.$na2[0].' looks closely (ﾟ Д ﾟ)';
															$mess[]='Uncle or Mister! I am not Chiyo! I am '.$na2[0].'. Are you mistaken for someone?';
															$mess[]=$na2[0].' is confused(*_*)';
														}else if($nu1==9){
															$mess[]='!!!';
															$mess[]='What! Ah!';
															$mess[]='princess! Miss Chiyo! it safe!';
															$mess[]='A young warrior with a fearless look kneels in front of '.$na2[0].'.';
															$mess[]='An older brother who does not know even if '.$na2[0].' looks closely( ﾟДﾟ)';
															$mess[]='Mister! I am not Chiyo! I am '.$na2[0].'. Are you mistaken for someone?';
															$mess[]=$na2[0].' is confused(*_*)';
														}
													}else{
														if($nu1>3&&$nu1<6||$nu1==10){
															$mess[]='( ﾟДﾟ)！！！！！！';
															$mess[]='あれ！どうしちゃったんだろ？';
															$mess[]='え！'.$na2[0].'さん？あ！'.$na2[0].'さんだ。私だよ「'.$na1.'」！';
															$mess[]='( ;∀;)久しぶりだねー！あの時のまんまだよ！';
															$mess[]=$na2[0].'がよく見ると大分成長しているが同じクラスの'.$na1.'の顔だった！';
															$mess[]='久しぶりに見た'.$na1.'の姿に'.$na2[0].'はちょっと嬉かった( *´艸｀)';
														}else if($nu1==3){
															$mess[]='！！！';
															$mess[]='あれ！どうしちゃったんだろ？';
															$mess[]='え！'.$na2[0].'さん？私「'.$na1.'」だよ！';
															$mess[]='( ;∀;)久しぶりだねー！あの時のままね！';
															$mess[]=$na2[0].'がよく見ると'.$na1.'の顔だった！';
															$mess[]='久しぶりに見た'.$na1.'の姿に'.$na2[0].'はかなり嬉かった( *´艸｀)';
														}else if($nu1>=6&&$nu1<=8){
															$mess[]='！！！';
															$mess[]='何事じゃぁ！どうした！';
															$mess[]='あ！千代！大事無いかっ！';
															$mess[]='突然強面の武者が'.$na2[0].'を大きな手で抱き上げた。';
															$mess[]=$na2[0].'がよく見ても強面の知らないおじさん( ﾟДﾟ)';
															$mess[]='おじさん！私は千代じゃないよ！'.$na2[0].'だよ！誰かと間違えてない？';
															$mess[]=$na1.'の行動に'.$na2[0].'は困惑している(*_*)';
															
														}else if($nu1==9){
															$mess[]='！！！';
															$mess[]='何事！あっ！';
															$mess[]='姫！千代姫様！ご無事でしたか！';
															$mess[]='精悍な顔つきの若武者が'.$na2[0].'の前に膝まづく。';
															$mess[]=$na2[0].'がよく見ても知らないお兄さん( ﾟДﾟ)';
															$mess[]='私は千代姫じゃないよ！'.$na2[0].'だよ！誰かと間違えてない？';
															$mess[]=$na1.'の神妙な対応に'.$na2[0].'は困惑している(*_*)';
														}
													}
												break;
												case 2:
													if($local){
														if($nu1>3&&$nu1<6||$nu1==10){
															$mess[]='( ﾟДﾟ)!!!!!!!';
															$mess[]='that! What happened?';
															$mess[]='e!['.$na2[0].'] Ah! It is ['.$na2[0].']. It is me ['.$na1.']!';
															$mess[]='(; ∀;) It is been a long time! It is still at that time!';
															$mess[]='Looking closely at '.$na2[0].', It was the face of '.$na1.', At the same school!';
															$mess[]=$na2[0].' was a little happy (*艸 `) to see '.$na1.'`s smile.';
															
														}else if($nu1==3){
															$mess[]='!!!';
															$mess[]='that! What happened?';
															$mess[]='e!['.$na2[0].'] Ah! It is ['.$na2[0].']. It is me ['.$na1.']!';
															$mess[]='(; ∀;) It is been a long time! It is still at that time!';
															$mess[]='Looking closely at '.$na2[0].', It was the face of '.$na1.'!';
															$mess[]=$na2[0].' was a very happy( *´艸`) to see '.$na1.'`s smile.';
														}
													}else{
														if($nu1>3&&$nu1<6||$nu1==10){
															$mess[]='( ﾟДﾟ)！！！！！！';
															$mess[]='あれ！どうしちゃったんだろ？';
															$mess[]='え！'.$na2[0].'さん？あ！'.$na2[0].'さんだ。私だよ「'.$na1.'」！';
															$mess[]='( ;∀;)久しぶりだねー！あの時のまんまだよ！';
															$mess[]=$na2[0].'がよく見ると大分成長しているが同じクラスの'.$na1.'の顔だった！';
															$mess[]=$na1.'の笑顔が'.$na2[0].'はちょっと嬉かった( *´艸｀)';
														}else if($nu1==3){
															$mess[]='！！！';
															$mess[]='あれ！どうしちゃったんだろ？';
															$mess[]='え！'.$na2[0].'さん？私「'.$na1.'」だよ！';
															$mess[]='( ;∀;)久しぶりだねー！あの時のままね！';
															$mess[]=$na2[0].'がよく見ると'.$na1.'の顔だった！';
															$mess[]=$na1.'の優しい目に見つめられて'.$na2[0].'はかなり嬉かった( *´艸｀)';
														}
													}
												break;
												case 3:
													if($local){
														if($nu1==11){
															$mess[]='( ﾟДﾟ)!!!!!!';
															$mess[]='What happened?';
															$mess[]='!!['.$na2[0].'] Ah! It is ['.$na2[0].']. It is me ['.$na1.']!';
															$mess[]='(; ∀;) It is been a long time! It is still at that time!';
															$mess[]='Looking closely at '.$na2[0].', It was the face of '.$na1.'!';
															$mess[]=$na2[0].' was not happy...';
														}
													}else{
														if($nu1==11){
															$mess[]='( ﾟДﾟ)！！！！！！';
															$mess[]='あれ！どうしちゃったんだろ？';
															$mess[]='!!'.$na2[0].'さん？あ！'.$na2[0].'さんだ。ぼくだよ「'.$na1.'」！';
															$mess[]='( ;∀;)久しぶりだねー！あの時のまんまだよ！';
															$mess[]=$na2[0].'がよく見ると同じクラスの'.$na1.'の顔だった！';
															$mess[]=$na1.'のマシンガントークに'.$na2[0].'はちょっと複雑だった...';
														}
													}
											}
											$battle_loop=$i;
											update_sql(0,$nu1,100,100,0,$t_count);
											continue 2;//ここから2つ目のループを抜ける
										}
										//msg_firstsecondを空にしておく
										$msg_firstsecond="";
									}
								}
							}
						}
						$c++;
					}
				}
				if($count==$p){
					if($local){
						$mess[] = 'The party has been wiped out .....';
					}else{
						$mess[] = 'パーティは全滅してしまった.....';
					}
					$battle_loop=$i;
					update_sql(0,$nu1,100,100,2,$t_count);//$type=2を読んでmasterのPP値を減算
					continue;
				}
			}
		}
	}
	$mess=array_filter($mess, 'myFilter');//配列の空を取り除く
	update_sql($mess,$nu1,100,100,1,$t_count);//メッセージを書き込む
}
function update_sql($messeges,$enemy_number,$mon,$emo,$type,$tc){//ここでsqlに書き込み
	$items = [];
	$ghost = [];
	//global value
	global $host;
	global $user;
	global $pass;
	global $db_name;
	global $tb_name;
	global $tb_ghost;
	global $acount;
	global $password;
	global $TrustPoint;
	try{
		if($enemy_number){
			//Sql connect
			$db = new PDO($host,$user,$pass);
			$sql = 'use '.$db_name;//DBを選択
			if($db->query($sql)){
				//POSTされたアカウントとパスワードでテーブルをGET
				$sql = "SELECT * FROM ".$tb_name." where acount=\"".$acount."\" and password=\"".$password."\"";
				$sql=$db->query($sql);
				$rows = $sql->fetchAll();//$enemyのテーブルをデータ化しておく
				if(!empty($rows[0])){
					$row=$rows[0];//$rows[0]がGETしたテーブルのROWになっている
					$ghosttb = "SELECT * FROM ".$tb_ghost;
					$ghosttb=$db->query($ghosttb);	
					$ghosttb = $ghosttb->fetchAll();//$enemyのテーブルをデータ化しておく
					//Getting Ghost Data
					foreach($ghosttb as $party){
						if($enemy_number==$party['id']){
							//echo ' : $party["id"]='.$enemy_number.' : ';
							$get_enemy[0]=$party['id'];
							$get_enemy[1]=0;
							$get_enemy[2]=$party['HP'];
							$get_enemy[3]=$party['DP'];
							$get_enemy[4]=$party['SP'];
							$get_enemy[5]=$party['LP'];
							$get_enemy[6]=$party['AP'];
							$get_enemy[7]=$party['FP'];//好感度この値がHPとしてパーティ全員に振り分けられる
							$get_enemy[8]=$party['TP'];//プレイヤーとの信頼度
							$get_enemy[9]=$party['PP'];//マスターの時のマスターの信頼性
							$get_enemy[10]=0;
							$get_enemy[11]=0;
							$get_enemy[12]=0;
							$get_enemy[13]=0;
							//CP
							$Get_enemy_cp=$party['HP']+$party['DP']+$party['SP']+$party['LP']+$party['AP'];
						}
					}
					if(!$type){
						$master= unserialize($row['master']);
						$party1= unserialize($row['party1']);
						$p1_CP=$party1[2]+$party1[3]+$party1[4]+$party1[5]+$party1[6];
						$party2= unserialize($row['party2']);
						$p2_CP=$party2[2]+$party2[3]+$party2[4]+$party2[5]+$party2[6];
						//print ' : $p2_CP : '.$p2_CP.' : ';
						$party3= unserialize($row['party3']);
						$p3_CP=$party3[2]+$party3[3]+$party3[4]+$party3[5]+$party3[6];
						//print ' : $p3_CP : '.$p3_CP.' : ';
						$party4= unserialize($row['party4']);
						$p4_CP=$party4[2]+$party4[3]+$party4[4]+$party4[5]+$party4[6];
						//print ' : $p4_CP : '.$p4_CP.' : ';
						//weapontb
						$weapontb= "SELECT * FROM weapon_tb";
						$weapontb=$db->query($weapontb);
						$rwep = $weapontb->fetchAll();//$weaponのテーブルをデータ化しておく
						//grovetb
						$grovetb= "SELECT * FROM glove_tb";
						$grovetb=$db->query($grovetb);
						$rgro = $grovetb->fetchAll();//$groveのテーブルをデータ化しておく
						//armortb
						$armortb= "SELECT * FROM armor_tb";
						$armortb=$db->query($armortb);
						$rarm = $armortb->fetchAll();//$armorのテーブルをデータ化しておく
						//shoestb
						$kututb= "SELECT * FROM kutu_tb";
						$kututb=$db->query($kututb);
						$rsho = $kututb->fetchAll();//$shoesのテーブルをデータ化しておく
						if($tc){//魂眠の壺の残りがある場合
							//print ' :: t count :'.$tc.' :: ';
							//print ' : $Get_enemy_cp : '.$Get_enemy_cp.' : ';
							$ghost = unserialize($row['ghost']);
							$ghost[$enemy_number-1]++;

							
							if(!$party1[0]){//party1にだれもセットされていないかったら
								//まず武器のリストを見て逆順で0でないものがあればセットする
								$wep = unserialize($row['weapon']);
								for($i=0;$i<count($wep);$i++){
									if($wep[$i]){//weaponのリストの0ではないものを探す
										$get_enemy[10]=$i+1;
										$wep[$i]--;//該当のカウントを一つデクリメント
										//add
										foreach($rwep as $weap){//weaponのテーブルから加算値を追加する
											if($weap['id']==($i+1)){
												if($get_enemy[0]==3&&$weap[0]==13||$get_enemy[0]==6&&$weap[0]==13||$get_enemy[0]==2&&$weap[0]==18){
													$plus=4;
												}else{
													$plus=1;
												}
												$get_enemy[3] =$get_enemy[3]+$weap[2];
												$get_enemy[4] =$get_enemy[4]+$weap[3];
												$get_enemy[6] =$get_enemy[6]+$weap[4]*$plus;
												$get_enemy[7] =$get_enemy[7]+$weap[5];
												$get_enemy[8] =$get_enemy[8]+$weap[6];
												$get_enemy[9] =$get_enemy[9]+$weap[7];
											}
										}
										break;
									}
								}
								//続いて手袋
								$gro = unserialize($row['grove']);
								for($i=0;$i<count($gro);$i++){
									if($gro[$i]){
										$get_enemy[11]=$i+1;
										$gro[$i]--;
										//add
										foreach($rgro as $gr){
											if($gr['id']==($i+1)){
												$get_enemy[3] =$get_enemy[3]+$gr[2];
												$get_enemy[4] =$get_enemy[4]+$gr[3];
											}
										}
										break;
									}
								}
								//続いて防具
								$armo = unserialize($row['armored']);
								for($i=0;$i<count($armo);$i++){
									if($armo[$i]){
										$get_enemy[12]=$i+1;
										$armo[$i]--;
										//add
										foreach($rarm as $armod){
											if($armod['id']==($i+1)){
												if($get_enemy[0]==1&&$armod[0]==4){
													$plus=4;//サヨが紺糸なら防御が倍
												}else{
													$plus=1;
												}
												$get_enemy[3] =$get_enemy[3]+$armod[2]*$plus;
												$get_enemy[4] =$get_enemy[4]+$armod[3];
												$get_enemy[5] =$get_enemy[5]+$armod[4];
												$get_enemy[8] =$get_enemy[8]+$armod[5];
												$get_enemy[9] =$get_enemy[9]+$armod[6];
											}
										}
										break;
									}
								}
								//そして靴
								$sho = unserialize($row['shoes']);
								for($i=0;$i<count($sho);$i++){
									if($sho[$i]){
										$get_enemy[13]=$i+1;
										$sho[$i]--;
										//add
										foreach($rsho as $sh){
											if($sh['id']==($i+1)){
												$get_enemy[3] =$get_enemy[3]+$sh[2];
												$get_enemy[4] =$get_enemy[4]+$sh[3];
											}
										}
										break;
									}
								}
								$m  = 'UPDATE '.$tb_name.' set ghost=:ghost,weapon=:weapon,grove=:grove,armored=:armored,shoes=:shoes,party1=:party1 where id=:id';
								$m = $db->prepare($m);
								$w = array(':ghost'=>serialize($ghost),':weapon'=>serialize($wep),':grove'=>serialize($gro),':armored'=>serialize($armo),':shoes'=>serialize($sho),':party1'=>serialize($get_enemy),':id'=>$row['id']);
								$m->execute($w);
							}else if(!$party2[0]){//party2にだれもセットされていないかったら
								//まず武器のリストを見て逆順で0でないものがあればセットする
								$wep = unserialize($row['weapon']);
								for($i=0;$i<count($wep);$i++){
									if($wep[$i]){
										$get_enemy[10]=$i+1;
										$wep[$i]--;
										//add
										foreach($rwep as $weap){
											if($weap['id']==($i+1)){
												if($get_enemy[0]==3&&$weap[0]==13||$get_enemy[0]==6&&$weap[0]==13||$get_enemy[0]==2&&$weap[0]==18){
													$plus=4;
												}else{
													$plus=1;
												}
												$get_enemy[3] =$get_enemy[3]+$weap[2];
												$get_enemy[4] =$get_enemy[4]+$weap[3];
												$get_enemy[6] =$get_enemy[6]+$weap[4]*$plus;
												$get_enemy[7] =$get_enemy[7]+$weap[5];
												$get_enemy[8] =$get_enemy[8]+$weap[6];
												$get_enemy[9] =$get_enemy[9]+$weap[7];
											}
										}
										break;
									}
								}
								//続いて手袋
								$gro = unserialize($row['grove']);
								for($i=0;$i<count($gro);$i++){
									if($gro[$i]){
										$get_enemy[11]=$i+1;
										$gro[$i]--;
										//add
										foreach($rgro as $gr){
											if($gr['id']==($i+1)){
												$get_enemy[3] =$get_enemy[3]+$gr[2];
												$get_enemy[4] =$get_enemy[4]+$gr[3];
											}
										}
										break;
									}
								}
								//続いて防具
								$armo = unserialize($row['armored']);
								for($i=0;$i<count($armo);$i++){
									if($armo[$i]){
										$get_enemy[12]=$i+1;
										$armo[$i]--;
										//add
										foreach($rarm as $armod){
											if($armod['id']==($i+1)){
												if($get_enemy[0]==1&&$armod[0]==4){
													$plus=4;//サヨが紺糸なら防御が倍
												}else{
													$plus=1;
												}
												$get_enemy[3] =$get_enemy[3]+$armod[2]*$plus;
												$get_enemy[4] =$get_enemy[4]+$armod[3];
												$get_enemy[5] =$get_enemy[5]+$armod[4];
												$get_enemy[8] =$get_enemy[8]+$armod[5];
												$get_enemy[9] =$get_enemy[9]+$armod[6];
											}
										}
										break;
									}
								}
								//そして靴
								$sho = unserialize($row['shoes']);
								for($i=0;$i<count($sho);$i++){
									if($sho[$i]){
										$get_enemy[13]=$i+1;
										$sho[$i]--;
										//add
										foreach($rsho as $sh){
											if($sh['id']==($i+1)){
												$get_enemy[3] =$get_enemy[3]+$sh[2];
												$get_enemy[4] =$get_enemy[4]+$sh[3];
											}
										}
										break;
									}
								}
								$m  = 'UPDATE '.$tb_name.' set ghost=:ghost,weapon=:weapon,grove=:grove,armored=:armored,shoes=:shoes,party2=:party2 where id=:id';
								$m = $db->prepare($m);
								$w = array(':ghost'=>serialize($ghost),':weapon'=>serialize($wep),':grove'=>serialize($gro),':armored'=>serialize($armo),':shoes'=>serialize($sho),':party2'=>serialize($get_enemy),':id'=>$row['id']);
								$m->execute($w);
							}else if(!$party3[0]){//party3にだれもセットされていないかったら
								//まず武器のリストを見て逆順で0でないものがあればセットする
								$wep = unserialize($row['weapon']);
								for($i=0;$i<count($wep);$i++){
									if($wep[$i]){
										$get_enemy[10]=$i+1;
										$wep[$i]--;
										//add
										foreach($rwep as $weap){
											if($weap['id']==($i+1)){
												if($get_enemy[0]==3&&$weap[0]==13||$get_enemy[0]==6&&$weap[0]==13||$get_enemy[0]==2&&$weap[0]==18){
													$plus=4;
												}else{
													$plus=1;
												}
												$get_enemy[3] =$get_enemy[3]+$weap[2];
												$get_enemy[4] =$get_enemy[4]+$weap[3];
												$get_enemy[6] =$get_enemy[6]+$weap[4]*$plus;
												$get_enemy[7] =$get_enemy[7]+$weap[5];
												$get_enemy[8] =$get_enemy[8]+$weap[6];
												$get_enemy[9] =$get_enemy[9]+$weap[7];
											}
										}
										break;
									}
								}
								//続いて手袋
								$gro = unserialize($row['grove']);
								for($i=0;$i<count($gro);$i++){
									if($gro[$i]){
										$get_enemy[11]=$i+1;
										$gro[$i]--;
										//add
										foreach($rgro as $gr){
											if($gr['id']==($i+1)){
												$get_enemy[3] =$get_enemy[3]+$gr[2];
												$get_enemy[4] =$get_enemy[4]+$gr[3];
											}
										}
										break;
									}
								}
								//続いて防具
								$armo = unserialize($row['armored']);
								for($i=0;$i<count($armo);$i++){
									if($armo[$i]){
										$get_enemy[12]=$i+1;
										$armo[$i]--;
										//add
										foreach($rarm as $armod){
											if($armod['id']==($i+1)){
												if($get_enemy[0]==1&&$armod[0]==4){
													$plus=4;//サヨが紺糸なら防御が倍
												}else{
													$plus=1;
												}
												$get_enemy[3] =$get_enemy[3]+$armod[2]*$plus;
												$get_enemy[4] =$get_enemy[4]+$armod[3];
												$get_enemy[5] =$get_enemy[5]+$armod[4];
												$get_enemy[8] =$get_enemy[8]+$armod[5];
												$get_enemy[9] =$get_enemy[9]+$armod[6];
											}
										}
										break;
									}
								}
								//そして靴
								$sho = unserialize($row['shoes']);
								for($i=0;$i<count($sho);$i++){
									if($sho[$i]){
										$get_enemy[13]=$i+1;
										$sho[$i]--;
										//add
										foreach($rsho as $sh){
											if($sh['id']==($i+1)){
												$get_enemy[3] =$get_enemy[3]+$sh[2];
												$get_enemy[4] =$get_enemy[4]+$sh[3];
											}
										}
										break;
									}
								}
								$m  = 'UPDATE '.$tb_name.' set ghost=:ghost,weapon=:weapon,grove=:grove,armored=:armored,shoes=:shoes,party3=:party3 where id=:id';
								$m = $db->prepare($m);
								$w = array(':ghost'=>serialize($ghost),':weapon'=>serialize($wep),':grove'=>serialize($gro),':armored'=>serialize($armo),':shoes'=>serialize($sho),':party3'=>serialize($get_enemy),':id'=>$row['id']);
								$m->execute($w);
							}else if(!$party4[0]){//party4にだれもセットされていないかったら
								//まず武器のリストを見て逆順で0でないものがあればセットする
								$wep = unserialize($row['weapon']);
								for($i=0;$i<count($wep);$i++){
									if($wep[$i]){
										$get_enemy[10]=$i+1;
										$wep[$i]--;
										//add
										foreach($rwep as $weap){
											if($weap['id']==($i+1)){
												if($get_enemy[0]==3&&$weap[0]==13||$get_enemy[0]==6&&$weap[0]==13||$get_enemy[0]==2&&$weap[0]==18){
													$plus=4;
												}else{
													$plus=1;
												}
												$get_enemy[3] =$get_enemy[3]+$weap[2];
												$get_enemy[4] =$get_enemy[4]+$weap[3];
												$get_enemy[6] =$get_enemy[6]+$weap[4]*$plus;
												$get_enemy[7] =$get_enemy[7]+$weap[5];
												$get_enemy[8] =$get_enemy[8]+$weap[6];
												$get_enemy[9] =$get_enemy[9]+$weap[7];
											}
										}
										break;
									}
								}
								//続いて手袋
								$gro = unserialize($row['grove']);
								for($i=0;$i<count($gro);$i++){
									if($gro[$i]){
										$get_enemy[11]=$i+1;
										$gro[$i]--;
										//add
										foreach($rgro as $gr){
											if($gr['id']==($i+1)){
												$get_enemy[3] =$get_enemy[3]+$gr[2];
												$get_enemy[4] =$get_enemy[4]+$gr[3];
											}
										}
										break;
									}
								}
								//続いて防具
								$armo = unserialize($row['armored']);
								for($i=0;$i<count($armo);$i++){
									if($armo[$i]){
										$get_enemy[12]=$i+1;
										$armo[$i]--;
										//add
										foreach($rarm as $armod){
											if($armod['id']==($i+1)){
												if($get_enemy[0]==1&&$armod[0]==4){
													$plus=4;//サヨが紺糸なら防御が倍
												}else{
													$plus=1;
												}
												$get_enemy[3] =$get_enemy[3]+$armod[2]*$plus;
												$get_enemy[4] =$get_enemy[4]+$armod[3];
												$get_enemy[5] =$get_enemy[5]+$armod[4];
												$get_enemy[8] =$get_enemy[8]+$armod[5];
												$get_enemy[9] =$get_enemy[9]+$armod[6];
											}
										}
										break;
									}
								}
								//そして靴
								$sho = unserialize($row['shoes']);
								for($i=0;$i<count($sho);$i++){
									if($sho[$i]){
										$get_enemy[13]=$i+1;
										$sho[$i]--;
										//add
										foreach($rsho as $sh){
											if($sh['id']==($i+1)){
												$get_enemy[3] =$get_enemy[3]+$sh[2];
												$get_enemy[4] =$get_enemy[4]+$sh[3];
											}
										}
										break;
									}
								}
								$m  = 'UPDATE '.$tb_name.' set ghost=:ghost,weapon=:weapon,grove=:grove,armored=:armored,shoes=:shoes,party4=:party4 where id=:id';
								$m = $db->prepare($m);
								$w = array(':ghost'=>serialize($ghost),':weapon'=>serialize($wep),':grove'=>serialize($gro),':armored'=>serialize($armo),':shoes'=>serialize($sho),':party4'=>serialize($get_enemy),':id'=>$row['id']);
								$m->execute($w);
							}else if($master[0]<3&&$party1[0]>2&&$get_enemy[0]==3||$party1[0]>10&&$Get_enemy_cp>$p1_CP&&!$TrustPoint||$master[0]<3&&!$TrustPoint&&$get_enemy[0]>2&&$get_enemy[0]<6&&$party1[0]>10||$master[0]<3&&!$TrustPoint&&$get_enemy[0]==10&&$party1[0]>10){
								//TP値がFALSEでCP値が捕まえたエネミーの方が大きかったら差し替えorTP値がFALSEでMASTERがサヨかレンなら友達優先
								for($ico=0;$ico<rand(5,10);$ico++){
									$master[7]--;
									if($master[7]<0){
										$master[7]=0;
										break;
									}
								}
								if($party1[10]){//武器を持っていたらその武器を外してリストに戻す
									$wep = unserialize($row['weapon']);
									$wep[($party1[10]-1)]++;
								}
								if($party1[11]){//手袋を持っていたらその手袋を外してリストに戻す
									$gro = unserialize($row['grove']);
									$gro[($party1[11]-1)]++;
								}
								if($party1[12]){//防具を身に着けていたらそれを外しリストに戻す
									$armo = unserialize($row['armored']);
									$armo[($party1[12]-1)]++;
								}
								if($party1[13]){//靴を履いていたら脱いでその靴をリストに戻す
									$sho = unserialize($row['shoes']);
									$sho[($party1[13]-1)]++;
								}
								//party1を捕まえたenemyに入れ替える
								//まず武器のリストを見て逆順で0でないものがあればセットする
								if(!isset($wep)){
									$wep = unserialize($row['weapon']);
								}
								for($i=0;$i<count($wep);$i++){
									if($wep[$i]){
										$get_enemy[10]=$i+1;
										$wep[$i]--;
										//add
										foreach($rwep as $weap){
											if($weap['id']==($i+1)){
												if($get_enemy[0]==3&&$weap['id']==13||$get_enemy[0]==6&&$weap['id']==13||$get_enemy[0]==2&&$weap['id']==18){
													$plus=4;
												}else{
													$plus=1;
												}
												$get_enemy[3] =$get_enemy[3]+$weap[2];
												$get_enemy[4] =$get_enemy[4]+$weap[3];
												$get_enemy[6] =$get_enemy[6]+$weap[4]*$plus;
												$get_enemy[7] =$get_enemy[7]+$weap[5];
												$get_enemy[8] =$get_enemy[8]+$weap[6];
												$get_enemy[9] =$get_enemy[9]+$weap[7];
											}
										}
										break;
									}
								}
								//続いて手袋
								if(!isset($gro)){
									$gro = unserialize($row['grove']);
								}
								for($i=0;$i<count($gro);$i++){
									if($gro[$i]){
										$get_enemy[11]=$i+1;
										$gro[$i]--;
										//add
										foreach($rgro as $gr){
											if($gr['id']==($i+1)){
												$get_enemy[3] =$get_enemy[3]+$gr[2];
												$get_enemy[4] =$get_enemy[4]+$gr[3];
											}
										}
										break;
									}
								}
								//続いて防具
								if(!isset($armo)){
									$armo = unserialize($row['armored']);
								}
								for($i=0;$i<count($armo);$i++){
									if($armo[$i]){
										$get_enemy[12]=$i+1;
										$armo[$i]--;
										//add
										foreach($rarm as $armod){
											if($armod['id']==($i+1)){
												if($get_enemy[0]==1&&$armod[0]==4){
													$plus=4;//サヨが紺糸なら防御が倍
												}else{
													$plus=1;
												}
												$get_enemy[3] =$get_enemy[3]+$armod[2]*$plus;
												$get_enemy[4] =$get_enemy[4]+$armod[3];
												$get_enemy[5] =$get_enemy[5]+$armod[4];
												$get_enemy[8] =$get_enemy[8]+$armod[5];
												$get_enemy[9] =$get_enemy[9]+$armod[6];
											}
										}
										break;
									}
								}
								//そして靴
								if(!isset($sho)){
									$sho = unserialize($row['shoes']);
								}
								for($i=0;$i<count($sho);$i++){
									if($sho[$i]){
										$get_enemy[13]=$i+1;
										$sho[$i]--;
										//add
										foreach($rsho as $sh){
											if($sh['id']==($i+1)){
												$get_enemy[3] =$get_enemy[3]+$sh[2];
												$get_enemy[4] =$get_enemy[4]+$sh[3];
											}
										}
										break;
									}
								}
								$m  = 'UPDATE '.$tb_name.' set ghost=:ghost,weapon=:weapon,grove=:grove,armored=:armored,shoes=:shoes,party1=:party1 where id=:id';
								$m = $db->prepare($m);
								$w = array(':ghost'=>serialize($ghost),':weapon'=>serialize($wep),':grove'=>serialize($gro),':armored'=>serialize($armo),':shoes'=>serialize($sho),':party1'=>serialize($get_enemy),':id'=>$row['id']);
								$m->execute($w);
							}else if($master[0]<3&&$party2[0]>2&&$get_enemy[0]==3||$Get_enemy_cp>$p2_CP&&$party2[0]>10&&!$TrustPoint||$master[0]<3&&!$TrustPoint&&$get_enemy[0]>2&&$get_enemy[0]<6&&$party2[0]>10||$master[0]<3&&!$TrustPoint&&$get_enemy[0]==10&&$party2[0]>10){
								//TP値がFALSEでCP値が捕まえたエネミーの方が大きかったら差し替えorTP値がFALSEでMASTERがサヨかレンなら友達優先
								for($ico=0;$ico<rand(5,10);$ico++){
									$master[7]--;
									if($master[7]<0){
										$master[7]=0;
									break;
									}
								}
								if($party2[10]){
									$wep = unserialize($row['weapon']);
									$wep[($party2[10]-1)]++;
								}
								if($party2[11]){
									$gro = unserialize($row['grove']);
									$gro[($party2[11]-1)]++;
								}
								if($party2[12]){
									$armo = unserialize($row['armored']);
									$armo[($party2[12]-1)]++;
								}
								if($party2[13]){
									$sho = unserialize($row['shoes']);
									$sho[($party2[13]-1)]++;
								}
								//Change party2
								//まず武器のリストを見て逆順で0でないものがあればセットする
								if(!isset($wep)){
									$wep = unserialize($row['weapon']);
								}
								for($i=0;$i<count($wep);$i++){
									if($wep[$i]){
										$get_enemy[10]=$i+1;
										$wep[$i]--;
										//add
										foreach($rwep as $weap){
											if($weap['id']==($i+1)){
												if($get_enemy[0]==3&&$weap['id']==13||$get_enemy[0]==6&&$weap['id']==13||$get_enemy[0]==2&&$weap['id']==18){
													$plus=4;
												}else{
													$plus=1;
												}
												$get_enemy[3] =$get_enemy[3]+$weap[2];
												$get_enemy[4] =$get_enemy[4]+$weap[3];
												$get_enemy[6] =$get_enemy[6]+$weap[4]*$plus;
												$get_enemy[7] =$get_enemy[7]+$weap[5];
												$get_enemy[8] =$get_enemy[8]+$weap[6];
												$get_enemy[9] =$get_enemy[9]+$weap[7];
											}
										}
										break;
									}
								}
								//続いて手袋
								if(!isset($gro)){
									$gro = unserialize($row['grove']);
								}
								for($i=0;$i<count($gro);$i++){
									if($gro[$i]){
										$get_enemy[11]=$i+1;
										$gro[$i]--;
										//add
										foreach($rgro as $gr){
											if($gr['id']==($i+1)){
												$get_enemy[3] =$get_enemy[3]+$gr[2];
												$get_enemy[4] =$get_enemy[4]+$gr[3];
											}
										}
										break;
									}
								}
								//続いて防具
								if(!isset($armo)){
									$armo = unserialize($row['armored']);
								}
								for($i=0;$i<count($armo);$i++){
									if($armo[$i]){
										$get_enemy[12]=$i+1;
										$armo[$i]--;
										//add
										foreach($rarm as $armod){
											if($armod['id']==($i+1)){
												if($get_enemy[0]==1&&$armod[0]==4){
													$plus=4;//サヨが紺糸なら防御が倍
												}else{
													$plus=1;
												}
												$get_enemy[3] =$get_enemy[3]+$armod[2]*$plus;
												$get_enemy[4] =$get_enemy[4]+$armod[3];
												$get_enemy[5] =$get_enemy[5]+$armod[4];
												$get_enemy[8] =$get_enemy[8]+$armod[5];
												$get_enemy[9] =$get_enemy[9]+$armod[6];
											}
										}
										break;
									}
								}
								//そして靴
								if(!isset($sho)){
									$sho = unserialize($row['shoes']);
								}
								for($i=0;$i<count($sho);$i++){
									if($sho[$i]){
										$get_enemy[13]=$i+1;
										$sho[$i]--;
										//add
										foreach($rsho as $sh){
											if($sh['id']==($i+1)){
												$get_enemy[3] =$get_enemy[3]+$sh[2];
												$get_enemy[4] =$get_enemy[4]+$sh[3];
											}
										}
										break;
									}
								}
								$m  = 'UPDATE '.$tb_name.' set ghost=:ghost,weapon=:weapon,grove=:grove,armored=:armored,shoes=:shoes,party2=:party2 where id=:id';
								$m = $db->prepare($m);
								$w = array(':ghost'=>serialize($ghost),':weapon'=>serialize($wep),':grove'=>serialize($gro),':armored'=>serialize($armo),':shoes'=>serialize($sho),':party2'=>serialize($get_enemy),':id'=>$row['id']);
								$m->execute($w);
							}else if($master[0]<3&&$party3[0]>2&&$get_enemy[0]==3||$Get_enemy_cp>$p3_CP&&$party3[0]>10&&!$TrustPoint||$master[0]<3&&!$TrustPoint&&$get_enemy[0]>2&&$get_enemy[0]<6&&$party3[0]>10||$master[0]<3&&!$TrustPoint&&$get_enemy[0]==10&&$party3[0]>10){
								//TP値がFALSEでCP値が捕まえたエネミーの方が大きかったら差し替えorTP値がFALSEでMASTERがサヨかレンなら友達優先
								for($ico=0;$ico<rand(5,10);$ico++){
									$master[7]--;
									if($master[7]<0){
										$master[7]=0;
									break;
									}
								}
								if($party3[10]){
									$wep = unserialize($row['weapon']);
									$wep[($party3[10]-1)]++;
								}
								if($party3[11]){
									$gro = unserialize($row['grove']);
									$gro[($party3[11]-1)]++;
								}
								if($party3[12]){
									$armo = unserialize($row['armored']);
									$armo[($party3[12]-1)]++;
								}
								if($party3[13]){
									$sho = unserialize($row['shoes']);
									$sho[($party3[13]-1)]++;
								}
								//Change party3
								//まず武器のリストを見て逆順で0でないものがあればセットする
								if(!isset($wep)){
									$wep = unserialize($row['weapon']);
								}
								for($i=0;$i<count($wep);$i++){
									if($wep[$i]){
										$get_enemy[10]=$i+1;
										$wep[$i]--;
										//add
										foreach($rwep as $weap){
											if($weap['id']==($i+1)){
												if($get_enemy[0]==3&&$weap['id']==13||$get_enemy[0]==6&&$weap['id']==13||$get_enemy[0]==2&&$weap['id']==18){
													$plus=4;
												}else{
													$plus=1;
												}
												$get_enemy[3] =$get_enemy[3]+$weap[2];
												$get_enemy[4] =$get_enemy[4]+$weap[3];
												$get_enemy[6] =$get_enemy[6]+$weap[4]*$plus;
												$get_enemy[7] =$get_enemy[7]+$weap[5];
												$get_enemy[8] =$get_enemy[8]+$weap[6];
												$get_enemy[9] =$get_enemy[9]+$weap[7];
											}
										}
										break;
									}
								}
								//続いて手袋
								if(!isset($gro)){
									$gro = unserialize($row['grove']);
								}
								for($i=0;$i<count($gro);$i++){
									if($gro[$i]){
										$get_enemy[11]=$i+1;
										$gro[$i]--;
										//add
										foreach($rgro as $gr){
											if($gr['id']==($i+1)){
												$get_enemy[3] =$get_enemy[3]+$gr[2];
												$get_enemy[4] =$get_enemy[4]+$gr[3];
											}
										}
										break;
									}
								}
								//続いて防具
								if(!isset($armo)){
									$armo = unserialize($row['armored']);
								}
								for($i=0;$i<count($armo);$i++){
									if($armo[$i]){
										$get_enemy[12]=$i+1;
										$armo[$i]--;
										//add
										foreach($rarm as $armod){
											if($armod['id']==($i+1)){
												if($get_enemy[0]==1&&$armod[0]==4){
													$plus=4;//サヨが紺糸なら防御が倍
												}else{
													$plus=1;
												}
												$get_enemy[3] =$get_enemy[3]+$armod[2]*$plus;
												$get_enemy[4] =$get_enemy[4]+$armod[3];
												$get_enemy[5] =$get_enemy[5]+$armod[4];
												$get_enemy[8] =$get_enemy[8]+$armod[5];
												$get_enemy[9] =$get_enemy[9]+$armod[6];
											}
										}
										break;
									}
								}
								//そして靴
								if(!isset($sho)){
									$sho = unserialize($row['shoes']);
								}
								for($i=0;$i<count($sho);$i++){
									if($sho[$i]){
										$get_enemy[13]=$i+1;
										$sho[$i]--;
										//add
										foreach($rsho as $sh){
											if($sh['id']==($i+1)){
												$get_enemy[3] =$get_enemy[3]+$sh[2];
												$get_enemy[4] =$get_enemy[4]+$sh[3];
											}
										}
										break;
									}
								}
								$m  = 'UPDATE '.$tb_name.' set ghost=:ghost,weapon=:weapon,grove=:grove,armored=:armored,shoes=:shoes,party3=:party3 where id=:id';
								$m = $db->prepare($m);
								$w = array(':ghost'=>serialize($ghost),':weapon'=>serialize($wep),':grove'=>serialize($gro),':armored'=>serialize($armo),':shoes'=>serialize($sho),':party3'=>serialize($get_enemy),':id'=>$row['id']);
								$m->execute($w);
							}else if($master[0]<3&&$party4[0]>2&&$get_enemy[0]==3||$Get_enemy_cp>$p4_CP&&$party4[0]>10&&!$TrustPoint||$master[0]<3&&!$TrustPoint&&$get_enemy[0]>2&&$get_enemy[0]<6&&$party4[0]>10||$master[0]<3&&!$TrustPoint&&$get_enemy[0]==10&&$party4[0]>10){
								//TP値がFALSEでCP値が捕まえたエネミーの方が大きかったら差し替えorTP値がFALSEでMASTERがサヨかレンなら友達優先
								for($ico=0;$ico<rand(5,10);$ico++){
									$master[7]--;
									if($master[7]<0){
										$master[7]=0;
										break;
									}
								}
								if($party4[10]){
									$wep = unserialize($row['weapon']);
									$wep[($party4[10]-1)]++;
								}
								if($party4[11]){
									$gro = unserialize($row['grove']);
									$gro[($party4[11]-1)]++;
								}
								if($party4[12]){
									$armo = unserialize($row['armored']);
									$armo[($party4[12]-1)]++;
								}
								if($party4[13]){
									$sho = unserialize($row['shoes']);
									$sho[($party4[13]-1)]++;
								}
								//Change party4
								//まず武器のリストを見て逆順で0でないものがあればセットする
								if(!isset($wep)){
									$wep = unserialize($row['weapon']);
								}
								for($i=0;$i<count($wep);$i++){
									if($wep[$i]){
										$get_enemy[10]=$i+1;
										$wep[$i]--;
										//add
										foreach($rwep as $weap){
											if($weap['id']==($i+1)){
												if($get_enemy[0]==3&&$weap['id']==13||$get_enemy[0]==6&&$weap['id']==13||$get_enemy[0]==2&&$weap['id']==18){
													$plus=4;
												}else{
													$plus=1;
												}
												$get_enemy[3] =$get_enemy[3]+$weap[2];
												$get_enemy[4] =$get_enemy[4]+$weap[3];
												$get_enemy[6] =$get_enemy[6]+$weap[4]*$plus;
												$get_enemy[7] =$get_enemy[7]+$weap[5];
												$get_enemy[8] =$get_enemy[8]+$weap[6];
												$get_enemy[9] =$get_enemy[9]+$weap[7];
											}
										}
										break;
									}
								}
								//続いて手袋
								if(!isset($gro)){
									$gro = unserialize($row['grove']);
								}
								for($i=0;$i<count($gro);$i++){
									if($gro[$i]){
										$get_enemy[11]=$i+1;
										$gro[$i]--;
										//add
										foreach($rgro as $gr){
											if($gr['id']==($i+1)){
												$get_enemy[3] =$get_enemy[3]+$gr[2];
												$get_enemy[4] =$get_enemy[4]+$gr[3];
											}
										}
										break;
									}
								}
								//続いて防具
								if(!isset($armo)){
									$armo = unserialize($row['armored']);
								}
								for($i=0;$i<count($armo);$i++){
									if($armo[$i]){
										$get_enemy[12]=$i+1;
										$armo[$i]--;
										//add
										foreach($rarm as $armod){
											if($armod['id']==($i+1)){
												if($get_enemy[0]==1&&$armod[0]==4){
													$plus=2;//サヨが紺糸なら防御が倍
												}else{
													$plus=1;
												}
												$get_enemy[3] =$get_enemy[3]+$armod[2]*$plus;
												$get_enemy[4] =$get_enemy[4]+$armod[3];
												$get_enemy[5] =$get_enemy[5]+$armod[4];
												$get_enemy[8] =$get_enemy[8]+$armod[5];
												$get_enemy[9] =$get_enemy[9]+$armod[6];
											}
										}
										break;
									}
								}
								//そして靴
								if(!isset($sho)){
									$sho = unserialize($row['shoes']);
								}
								for($i=0;$i<count($sho);$i++){
									if($sho[$i]){
										$get_enemy[13]=$i+1;
										$sho[$i]--;
										//add
										foreach($rsho as $sh){
											if($sh['id']==($i+1)){
												$get_enemy[3] =$get_enemy[3]+$sh[2];
												$get_enemy[4] =$get_enemy[4]+$sh[3];
											}
										}
										break;
									}
								}
								$m  = 'UPDATE '.$tb_name.' set ghost=:ghost,weapon=:weapon,grove=:grove,armored=:armored,shoes=:shoes,party4=:party4 where id=:id';
								$m = $db->prepare($m);
								$w = array(':ghost'=>serialize($ghost),':weapon'=>serialize($wep),':grove'=>serialize($gro),':armored'=>serialize($armo),':shoes'=>serialize($sho),':party4'=>serialize($get_enemy),':id'=>$row['id']);
								$m->execute($w);
							}else{
								$m  = 'UPDATE '.$tb_name.' set ghost=:ghost where id=:id';
								$m = $db->prepare($m);
								$w = array(':ghost'=>serialize($ghost),':id'=>$row['id']);
								$m->execute($w);
							}
						}
						//落し物を拾っている場合
						if($mon!=100 && $emo!=100){
							//print ' : hirotteru : ';
							switch($mon){
								case 0://item
									$items = unserialize($row['item']);
									$items[$emo]++;
									$m  = 'UPDATE '.$tb_name.' set item=:item where id=:id';
									$m = $db->prepare($m);
									$w = array(':item'=>serialize($items),':id'=>$row['id']);
									$m->execute($w);
								break;
								case 1://weapon
									foreach($rwep as $weap){
										if($weap['id']==($emo+1)){
											$weapon = $weap;
										}
									}
									$nowm_p=0;$now1_p=0;$now2_p=0;$now3_p=0;$now4_p=0;
									if($master[10]){
										foreach($rwep as $weap1){//今持っている武器
											if($weap1['id']==$master[10]){
												$nowm_w = $weap1;
												//print ' : $weap1[id] : '.$weap1['id'].' : ';
											}
										}
										$nowm_p=$nowm_w[2]+$nowm_w[3]+$nowm_w[4]+$nowm_w[5]+$nowm_w[6]+$nowm_w[7];
										//print ' : master[10] :'.$master[10].' : nowm_p : '.$nowm_p.' : ';
										//var_dump($result);
									}
									if($party1[10]){
										foreach($rwep as $weap1){//今持っている武器
											if($weap1['id']==$party1[10]){
												$now1_w = $weap1;
											}
										}
										$now1_p=$now1_w[2]+$now1_w[3]+$now1_w[4]+$now1_w[5]+$now1_w[6]+$now1_w[7];
									}
									if($party2[10]){
										foreach($rwep as $weap1){//今持っている武器
											if($weap1['id']==$party2[10]){
												$now2_w = $weap1;
											}
										}
										$now2_p=$now2_w[2]+$now2_w[3]+$now2_w[4]+$now2_w[5]+$now2_w[6]+$now2_w[7];
									}
									if($party3[10]){
										foreach($rwep as $weap1){//今持っている武器
											if($weap1['id']==$party3[10]){
												$now3_w = $weap1;
											}
										}
										$now3_p=$now3_w[2]+$now3_w[3]+$now3_w[4]+$now3_w[5]+$now3_w[6]+$now3_w[7];
									}
									if($party4[10]){
										foreach($rwep as $weap1){//今持っている武器
											if($weap1['id']==$party4[10]){
												$now4_w = $weap1;
											}
										}
										$now4_p=$now4_w[2]+$now4_w[3]+$now4_w[4]+$now4_w[5]+$now4_w[6]+$now4_w[7];
									}
									$get_w=$weapon[2]+$weapon[3]+$weapon[4]+$weapon[5]+$weapon[6]+$weapon[7];
									//print ' : get_w : '.$get_w.' : ';
									if(!$master[10]){//まず、優先的にweaponを持ってない場合は供給します
										if($master[0]==3&&$weapon[0]==13||$master[0]==6&&$weapon[0]==13||$master[0]==2&&$weapon[0]==18){
											$plus=4;//うららか監物が同田貫なら攻撃量が倍
										}else{
											$plus=1;
										}
										$master[10]=$weapon[0];
										$master[3] =$master[3]+$weapon[2];
										$master[4] =$master[4]+$weapon[3];
										$master[6] =$master[6]+$weapon[4]*$plus;
										$master[7] =$master[7]+$weapon[5];
										$master[8] =$master[8]+$weapon[6];
										$master[9] =$master[9]+$weapon[7];
										$m  = 'UPDATE '.$tb_name.' set master=:master where id=:id';
										$m = $db->prepare($m);
										$w = array(':master'=>serialize($master),':id'=>$row['id']);
										$m->execute($w);
									}else if(!$party1[10]){
										if($party1[0]==3&&$weapon[0]==13||$party1[0]==6&&$weapon[0]==13||$party1[0]==2&&$weapon[0]==18){
											$plus=4;
										}else{
											$plus=1;
										}
										$party1[10]=$weapon[0];
										$party1[3] =$party1[3]+$weapon[2];
										$party1[4] =$party1[4]+$weapon[3];
										$party1[6] =$party1[6]+$weapon[4]*$plus;
										$party1[7] =$party1[7]+$weapon[5];
										$party1[8] =$party1[8]+$weapon[6];
										$party1[9] =$party1[9]+$weapon[7];
										$m  = 'UPDATE '.$tb_name.' set party1=:party1 where id=:id';
										$m = $db->prepare($m);
										$w = array(':party1'=>serialize($party1),':id'=>$row['id']);
										$m->execute($w);
									}else if(!$party2[10]){
										if($party2[0]==3&&$weapon[0]==13||$party2[0]==6&&$weapon[0]==13||$party2[0]==2&&$weapon[0]==18){
											$plus=4;
										}else{
											$plus=1;
										}
										$party2[10]=$weapon[0];
										$party2[3] =$party2[3]+$weapon[2];
										$party2[4] =$party2[4]+$weapon[3];
										$party2[6] =$party2[6]+$weapon[4]*$plus;
										$party2[7] =$party2[7]+$weapon[5];
										$party2[8] =$party2[8]+$weapon[6];
										$party2[9] =$party2[9]+$weapon[7];
										$m  = 'UPDATE '.$tb_name.' set party2=:party2 where id=:id';
										$m = $db->prepare($m);
										$w = array(':party2'=>serialize($party2),':id'=>$row['id']);
										$m->execute($w);
									}else if(!$party3[10]){
										if($party3[0]==3&&$weapon[0]==13||$party3[0]==6&&$weapon[0]==13||$party3[0]==2&&$weapon[0]==18){
											$plus=4;
										}else{
											$plus=1;
										}
										$party3[10]=$weapon[0];
										$party3[3] =$party3[3]+$weapon[2];
										$party3[4] =$party3[4]+$weapon[3];
										$party3[6] =$party3[6]+$weapon[4]*$plus;
										$party3[7] =$party3[7]+$weapon[5];
										$party3[8] =$party3[8]+$weapon[6];
										$party3[9] =$party3[9]+$weapon[7];
										$m  = 'UPDATE '.$tb_name.' set party3=:party3 where id=:id';
										$m = $db->prepare($m);
										$w = array(':party3'=>serialize($party3),':id'=>$row['id']);
										$m->execute($w);
									}else if(!$party4[10]){
										if($party4[0]==3&&$weapon[0]==13||$party4[0]==6&&$weapon[0]==13||$party4[0]==2&&$weapon[0]==18){
											$plus=4;
										}else{
											$plus=1;
										}
										$party4[10]=$weapon[0];
										$party4[3] =$party4[3]+$weapon[2];
										$party4[4] =$party4[4]+$weapon[3];
										$party4[6] =$party4[6]+$weapon[4]*$plus;
										$party4[7] =$party4[7]+$weapon[5];
										$party4[8] =$party4[8]+$weapon[6];
										$party4[9] =$party4[9]+$weapon[7];
										$m  = 'UPDATE '.$tb_name.' set party4=:party4 where id=:id';
										$m = $db->prepare($m);
										$w = array(':party4'=>serialize($party4),':id'=>$row['id']);
										$m->execute($w);
									}else if($master[10]&&$get_w>$nowm_p){//マスターの武器を比較、ゲットした武器の方がよかったら交換
										$bk_w=$master[10];//weapon id backup
										if($master[0]==3&&$weapon[0]==13||$master[0]==6&&$weapon[0]==13||$master[0]==2&&$weapon[0]==18){
											$plus=4;
										}else{
											$plus=1;
										}
										$master[10]=$weapon[0];
										$master[3] =$master[3]-$nowm_w[2]+$weapon[2];//最初から引いちゃうとマイナスになっちゃうかも？
										$master[4] =$master[4]-$nowm_w[3]+$weapon[3];
										$master[6] =$master[6]-$nowm_w[4]+$weapon[4]*$plus;
										$master[7] =$master[7]-$nowm_w[5]+$weapon[5];
										$master[8] =$master[8]-$nowm_w[6]+$weapon[6];
										$master[9] =$master[9]-$nowm_w[7]+$weapon[7];
										$items = unserialize($row['weapon']);
										$items[($bk_w-1)]++;//外した武器のリストをインクリメントする
										$m  = 'UPDATE '.$tb_name.' set weapon=:weapon,master=:master where id=:id';
										$m = $db->prepare($m);
										$w = array(':weapon'=>serialize($items),':master'=>serialize($master),':id'=>$row['id']);
										$m->execute($w);
									}else if($party1[10]&&$get_w>$now1_p){//party1の武器を比較、ゲットした武器の方がよかったら交換
										$bk_w=$party1[10];//weapon id backup
										if($party1[0]==3&&$weapon[0]==13||$party1[0]==6&&$weapon[0]==13||$party1[0]==2&&$weapon[0]==18){
											$plus=4;
										}else{
											$plus=1;
										}
										$party1[10]=$weapon[0];
										$party1[3] =$party1[3]+$weapon[2]-$now1_w[2];//最初から引いちゃうとマイナスになっちゃうかも？
										$party1[4] =$party1[4]+$weapon[3]-$now1_w[3];
										$party1[6] =$party1[6]+$weapon[4]-$now1_w[4]*$plus;
										$party1[7] =$party1[7]+$weapon[5]-$now1_w[5];
										$party1[8] =$party1[8]-$now1_w[6]+$weapon[6];
										$party1[9] =$party1[9]+$weapon[7]-$now1_w[7];
										$items = unserialize($row['weapon']);
										$items[($bk_w-1)]++;//外した武器のリストをインクリメントする
										$m  = 'UPDATE '.$tb_name.' set weapon=:weapon,party1=:party1 where id=:id';
										$m = $db->prepare($m);
										$w = array(':weapon'=>serialize($items),':party1'=>serialize($party1),':id'=>$row['id']);
										$m->execute($w);
									}else if($party2[10]&&$get_w>$now2_p){//party1の武器を比較、ゲットした武器の方がよかったら交換
										$bk_w=$party2[10];//weapon id backup
										if($party2[0]==3&&$weapon[0]==13||$party2[0]==6&&$weapon[0]==13||$party2[0]==2&&$weapon[0]==18){
											$plus=4;
										}else{
											$plus=1;
										}
										$party2[10]=$weapon[0];
										$party2[3] =$party2[3]+$weapon[2]-$now2_w[2];//最初から引いちゃうとマイナスになっちゃうかも？
										$party2[4] =$party2[4]+$weapon[3]-$now2_w[3];
										$party2[6] =$party2[6]+$weapon[4]-$now2_w[4]*$plus;
										$party2[7] =$party2[7]+$weapon[5]-$now2_w[5];
										$party2[8] =$party2[8]-$now2_w[6]+$weapon[6];
										$party2[9] =$party2[9]+$weapon[7]-$now2_w[7];
										$items = unserialize($row['weapon']);
										$items[($bk_w-1)]++;//外した武器のリストをインクリメントする
										$m  = 'UPDATE '.$tb_name.' set weapon=:weapon,party2=:party2 where id=:id';
										$m = $db->prepare($m);
										$w = array(':weapon'=>serialize($items),':party2'=>serialize($party2),':id'=>$row['id']);
										$m->execute($w);
									}else if($party3[10]&&$get_w>$now3_p){//party1の武器を比較、ゲットした武器の方がよかったら交換
										$bk_w=$party3[10];//weapon id backup
										if($party3[0]==3&&$weapon[0]==13||$party3[0]==6&&$weapon[0]==13||$party3[0]==2&&$weapon[0]==18){
											$plus=4;
										}else{
											$plus=1;
										}
										$party3[10]=$weapon[0];
										$party3[3] =$party3[3]+$weapon[2]-$now3_w[2];//最初から引いちゃうとマイナスになっちゃうかも？
										$party3[4] =$party3[4]+$weapon[3]-$now3_w[3];
										$party3[6] =$party3[6]+$weapon[4]-$now3_w[4]*$plus;
										$party3[7] =$party3[7]+$weapon[5]-$now3_w[5];
										$party3[8] =$party3[8]-$now3_w[6]+$weapon[6];
										$party3[9] =$party3[9]+$weapon[7]-$now3_w[7];
										$items = unserialize($row['weapon']);
										$items[($bk_w-1)]++;//外した武器のリストをインクリメントする
										$m  = 'UPDATE '.$tb_name.' set weapon=:weapon,party3=:party3 where id=:id';
										$m = $db->prepare($m);
										$w = array(':weapon'=>serialize($items),':party3'=>serialize($party3),':id'=>$row['id']);
										$m->execute($w);
									}else if($party4[10]&&$get_w>$now4_p){//party1の武器を比較、ゲットした武器の方がよかったら交換
										$bk_w=$party4[10];//weapon id backup
										if($party4[0]==3&&$weapon[0]==13||$party4[0]==6&&$weapon[0]==13||$party4[0]==2&&$weapon[0]==18){
											$plus=4;
										}else{
											$plus=1;
										}
										$party4[10]=$weapon[0];
										$party4[3] =$party4[3]+$weapon[2]-$now4_w[2];//最初から引いちゃうとマイナスになっちゃうかも？
										$party4[4] =$party4[4]+$weapon[3]-$now4_w[3];
										$party4[6] =$party4[6]+$weapon[4]-$now4_w[4]*$plus;
										$party4[7] =$party4[7]+$weapon[5]-$now4_w[5];
										$party4[8] =$party4[8]-$now4_w[6]+$weapon[6];
										$party4[9] =$party4[9]+$weapon[7]-$now4_w[7];
										$items = unserialize($row['weapon']);
										$items[($bk_w-1)]++;//外した武器のリストをインクリメントする
										$m  = 'UPDATE '.$tb_name.' set weapon=:weapon,party4=:party4 where id=:id';
										$m = $db->prepare($m);
										$w = array(':weapon'=>serialize($items),':party4'=>serialize($party4),':id'=>$row['id']);
										$m->execute($w);
									}else{
										$items = unserialize($row['weapon']);
										$items[$emo]++;
										$m  = 'UPDATE '.$tb_name.' set weapon=:item where id=:id';
										$m = $db->prepare($m);
										$w = array(':item'=>serialize($items),':id'=>$row['id']);
										$m->execute($w);
									}
								break;
								case 2://glove
									foreach($rgro as $glove){
										if($glove['id']==($emo+1)){
											$grove = $glove;
										}
									}
									$nowm_p=0;$now1_p=0;$now2_p=0;$now3_p=0;$now4_p=0;
									if($master[11]){
										foreach($rgro as $grob){//今持っているgrove
											if($grob['id']==$master[11]){
												$nowm_w = $grob;
											}
										}
										$nowm_p=$nowm_w[2]+$nowm_w[3];
									}
									if($party1[11]){
										foreach($rgro as $grob){//今持っているgrove
											if($grob['id']==$party1[11]){
												$now1_w = $grob;
											}
										}
										$now1_p=$now1_w[2]+$now1_w[3];
									}
									if($party2[11]){
										foreach($rgro as $grob){//今持っているgrove
											if($grob['id']==$party2[11]){
												$now2_w = $grob;
											}
										}
										$now2_p=$now2_w[2]+$now2_w[3];
									}
									if($party3[11]){
										foreach($rgro as $grob){//今持っているgrove
											if($grob['id']==$party3[11]){
												$now3_w = $grob;
											}
										}
										$now3_p=$now3_w[2]+$now3_w[3];
									}
									if($party4[11]){
										foreach($rgro as $grob){//今持っているgrove
											if($grob['id']==$party4[11]){
												$now4_w = $grob;
											}
										}
										$now4_p=$now4_w[2]+$now4_w[3];
									}
									$get_w=$grove[2]+$grove[3];
									if(!$master[11]){//持ってない場合は優先的に支給します
										$master[11]=$grove[0];
										$master[3] =$master[3]+$grove[2];
										$master[4] =$master[4]+$grove[3];
										$m  = 'UPDATE '.$tb_name.' set master=:master where id=:id';
										$m = $db->prepare($m);
										$w = array(':master'=>serialize($master),':id'=>$row['id']);
										$m->execute($w);
									}else if(!$party1[11]){
										$party1[11]=$grove[0];
										$party1[3] =$party1[3]+$grove[2];
										$party1[4] =$party1[4]+$grove[3];
										$m  = 'UPDATE '.$tb_name.' set party1=:party1 where id=:id';
										$m = $db->prepare($m);
										$w = array(':party1'=>serialize($party1),':id'=>$row['id']);
										$m->execute($w);
									}else if(!$party2[11]){
										$party2[11]=$grove[0];
										$party2[3] =$party2[3]+$grove[2];
										$party2[4] =$party2[4]+$grove[3];
										$m  = 'UPDATE '.$tb_name.' set party2=:party2 where id=:id';
										$m = $db->prepare($m);
										$w = array(':party2'=>serialize($party2),':id'=>$row['id']);
										$m->execute($w);
									}else if(!$party3[11]){
										$party3[11]=$grove[0];
										$party3[3] =$party3[3]+$grove[2];
										$party3[4] =$party3[4]+$grove[3];
										$m  = 'UPDATE '.$tb_name.' set party3=:party3 where id=:id';
										$m = $db->prepare($m);
										$w = array(':party3'=>serialize($party3),':id'=>$row['id']);
										$m->execute($w);
									}else if(!$party4[11]){
										$party4[11]=$grove[0];
										$party4[3] =$party4[3]+$grove[2];
										$party4[4] =$party4[4]+$grove[3];
										$m  = 'UPDATE '.$tb_name.' set party4=:party4 where id=:id';
										$m = $db->prepare($m);
										$w = array(':party4'=>serialize($party4),':id'=>$row['id']);
										$m->execute($w);
									}else if($master[11]&&$get_w>$nowm_p){//マスターの手袋を比較、ゲット方がよかったら交換
										$bk_w=$master[11];//grove id backup
										$master[11]=$grove[0];
										$master[3] =$master[3]+$grove[2]-$nowm_w[2];//最初から引いちゃうとマイナスになっちゃうかも？
										$master[4] =$master[4]+$grove[3]-$nowm_w[3];
										$items = unserialize($row['grove']);
										$items[($bk_w-1)]++;//外したgroveのリストをインクリメントする
										$m  = 'UPDATE '.$tb_name.' set grove=:grove,master=:master where id=:id';
										$m = $db->prepare($m);
										$w = array(':grove'=>serialize($items),':master'=>serialize($master),':id'=>$row['id']);
										$m->execute($w);
									}else if($party1[11]&&$get_w>$now1_p){//party1比較、ゲットした方がよかったら交換
										$bk_w=$party1[11];//grove id backup
										$party1[11]=$grove[0];
										$party1[3] =$party1[3]+$grove[2]-$now1_w[2];//最初から引いちゃうとマイナスになっちゃうかも？
										$party1[4] =$party1[4]+$grove[3]-$now1_w[3];
										$items = unserialize($row['grove']);
										$items[($bk_w-1)]++;//外した武器のリストをインクリメントする
										$m  = 'UPDATE '.$tb_name.' set grove=:grove,party1=:party1 where id=:id';
										$m = $db->prepare($m);
										$w = array(':grove'=>serialize($items),':party1'=>serialize($party1),':id'=>$row['id']);
										$m->execute($w);
									}else if($party2[11]&&$get_w>$now2_p){//party1のgroveを比較、ゲットした方がよかったら交換
										$bk_w=$party2[11];//grove id backup
										$party2[11]=$grove[0];
										$party2[3] =$party2[3]+$grove[2]-$now2_w[2];//最初から引いちゃうとマイナスになっちゃうかも？
										$party2[4] =$party2[4]+$grove[3]-$now2_w[3];
										$items = unserialize($row['grove']);
										$items[($bk_w-1)]++;//外したgroveのリストをインクリメントする
										$m  = 'UPDATE '.$tb_name.' set grove=:grove,party2=:party2 where id=:id';
										$m = $db->prepare($m);
										$w = array(':grove'=>serialize($items),':party2'=>serialize($party2),':id'=>$row['id']);
										$m->execute($w);
									}else if($party3[11]&&$get_w>$now3_p){//party1のgroveを比較、ゲットした方がよかったら交換
										$bk_w=$party3[11];//grove id backup
										$party3[11]=$grove[0];
										$party3[3] =$party3[3]+$grove[2]-$now3_w[2];//最初から引いちゃうとマイナスになっちゃうかも？
										$party3[4] =$party3[4]+$grove[3]-$now3_w[3];
										$items = unserialize($row['grove']);
										$items[($bk_w-1)]++;//外したgroveのリストをインクリメントする
										$m  = 'UPDATE '.$tb_name.' set grove=:grove,party3=:party3 where id=:id';
										$m = $db->prepare($m);
										$w = array(':grove'=>serialize($items),':party3'=>serialize($party3),':id'=>$row['id']);
										$m->execute($w);
									}else if($party4[11]&&$get_w>$now4_p){//party1のgroveを比較、ゲットした方がよかったら交換
										$bk_w=$party4[11];//grove id backup
										$party4[11]=$grove[0];
										$party4[3] =$party4[3]+$grove[2]-$now4_w[2];//最初から引いちゃうとマイナスになっちゃうかも？
										$party4[4] =$party4[4]+$grove[3]-$now4_w[3];
										$items = unserialize($row['grove']);
										$items[($bk_w-1)]++;//外したgroveのリストをインクリメントする
										$m  = 'UPDATE '.$tb_name.' set grove=:grove,party4=:party4 where id=:id';
										$m = $db->prepare($m);
										$w = array(':grove'=>serialize($items),':party4'=>serialize($party4),':id'=>$row['id']);
										$m->execute($w);
									}else{
										$items = unserialize($row['grove']);
										$items[$emo]++;
										$m  = 'UPDATE '.$tb_name.' set grove=:item where id=:id';
										$m = $db->prepare($m);
										$w = array(':item'=>serialize($items),':id'=>$row['id']);
										$m->execute($w);
									}
								break;
								case 3://armored
									foreach($rarm as $armd){
										if($armd['id']==($emo+1)){
											$armor = $armd;
										}
									}
									$nowm_p=0;$now1_p=0;$now2_p=0;$now3_p=0;$now4_p=0;
									if($master[12]){
										foreach($rarm as $arm){//今持っている
											if($arm['id']==$master[12]){
												$nowm_w = $arm;
											}
										}
										$nowm_p=$nowm_w[2]+$nowm_w[3]+$nowm_w[4]+$nowm_w[5]+$nowm_w[6];
									}
									if($party1[12]){
										foreach($rarm as $arm){//今持ってい
											if($arm['id']==$party1[12]){
												$now1_w = $arm;
											}
										}
										$now1_p=$now1_w[2]+$now1_w[3]+$now1_w[4]+$now1_w[5]+$now1_w[6];
									}
									if($party2[12]){
										foreach($rarm as $arm){//今持っている
											if($arm['id']==$party2[12]){
												$now2_w = $arm;
											}
										}
										$now2_p=$now2_w[2]+$now2_w[3]+$now2_w[4]+$now2_w[5]+$now2_w[6];
									}
									if($party3[12]){
										foreach($rarm as $arm){//今持っている
											if($arm['id']==$party3[12]){
												$now3_w = $arm;
											}
										}
										$now3_p=$now3_w[2]+$now3_w[3]+$now3_w[4]+$now3_w[5]+$now3_w[6];
									}
									if($party4[12]){
										foreach($rarm as $arm){//今持っている
											if($arm['id']==$party4[12]){
												$now4_w = $arm;
											}
										}
										$now4_p=$now4_w[2]+$now4_w[3]+$now4_w[4]+$now4_w[5]+$now4_w[6];
									}
									$get_w=$armor[2]+$armor[3]+$armor[4]+$armor[5]+$armor[6];
									if(!$master[12]){
										if($master[0]==1&&$armor[0]==4){
											$plus=4;//サヨが紺糸なら防御が倍
										}else{
											$plus=1;
										}
										$master[12]=$armor[0];
										$master[3] =$master[3]+$armor[2]*$plus;
										$master[4] =$master[4]+$armor[3];
										$master[5] =$master[5]+$armor[4];
										$master[8] =$master[8]+$armor[5];
										$master[9] =$master[9]+$armor[6];
										$m  = 'UPDATE '.$tb_name.' set master=:master where id=:id';
										$m = $db->prepare($m);
										$w = array(':master'=>serialize($master),':id'=>$row['id']);
										$m->execute($w);
									}else if(!$party1[12]){
										if($party1[0]==1&&$armor[0]==4){
											$plus=4;//サヨが紺糸なら防御が倍
										}else{
											$plus=1;
										}
										$party1[12]=$armor[0];
										$party1[3] =$party1[3]+$armor[2]*$plus;
										$party1[4] =$party1[4]+$armor[3];
										$party1[5] =$party1[5]+$armor[4];
										$party1[8] =$party1[8]+$armor[5];
										$party1[9] =$party1[9]+$armor[6];
										$m  = 'UPDATE '.$tb_name.' set party1=:party1 where id=:id';
										$m = $db->prepare($m);
										$w = array(':party1'=>serialize($party1),':id'=>$row['id']);
										$m->execute($w);
									}else if(!$party2[12]){
										if($party2[0]==1&&$armor[0]==4){
											$plus=4;//サヨが紺糸なら防御が倍
										}else{
											$plus=1;
										}
										$party2[12]=$armor[0];
										$party2[3] =$party2[3]+$armor[2]*$plus;
										$party2[4] =$party2[4]+$armor[3];
										$party2[5] =$party2[5]+$armor[4];
										$party2[8] =$party2[8]+$armor[5];
										$party2[9] =$party2[9]+$armor[6];
										$m  = 'UPDATE '.$tb_name.' set party2=:party2 where id=:id';
										$m = $db->prepare($m);
										$w = array(':party2'=>serialize($party2),':id'=>$row['id']);
										$m->execute($w);
									}else if(!$party3[12]){
										if($party3[0]==1&&$armor[0]==4){
											$plus=4;//サヨが紺糸なら防御が倍
										}else{
											$plus=1;
										}
										$party3[12]=$armor[0];
										$party3[3] =$party3[3]+$armor[2]*$plus;
										$party3[4] =$party3[4]+$armor[3];
										$party3[6] =$party3[5]+$armor[4];
										$party3[8] =$party3[8]+$armor[5];
										$party3[9] =$party3[9]+$armor[6];
										$m  = 'UPDATE '.$tb_name.' set party3=:party3 where id=:id';
										$m = $db->prepare($m);
										$w = array(':party3'=>serialize($party3),':id'=>$row['id']);
										$m->execute($w);
									}else if(!$party4[12]){
										if($party4[0]==1&&$armor[0]==4){
											$plus=4;//サヨが紺糸なら防御が倍
										}else{
											$plus=1;
										}
										$party4[12]=$armor[0];
										$party4[3] =$party4[3]+$armor[2]*$plus;
										$party4[4] =$party4[4]+$armor[3];
										$party4[5] =$party4[5]+$armor[4];
										$party4[8] =$party4[8]+$armor[5];
										$party4[9] =$party4[9]+$armor[6];
										$m  = 'UPDATE '.$tb_name.' set party4=:party4 where id=:id';
										$m = $db->prepare($m);
										$w = array(':party4'=>serialize($party4),':id'=>$row['id']);
										$m->execute($w);
									}else if($master[12]&&$get_w>$nowm_p){//マスターのarmorを比較、ゲット方がよかったら交換
										$bk_w=$master[12];//armor id backup
										if($master[0]==1&&$armor[0]==4){
											$plus=4;//サヨが紺糸なら防御が倍
										}else{
											$plus=1;
										}
										$master[12]=$armor[0];
										$master[3] =$master[3]+$armor[2]-$nowm_w[2]*$plus;//最初から引いちゃうとマイナスになっちゃうかも？
										$master[4] =$master[4]-$nowm_w[3]+$armor[3];
										$master[5] =$master[5]+$armor[4]-$nowm_w[4];
										$master[8] =$master[8]+$armor[5]-$nowm_w[5];
										$master[9] =$master[9]+$armor[6]-$nowm_w[6];
										$items = unserialize($row['armored']);
										$items[($bk_w-1)]++;//外したarmorのリストをインクリメントする
										$m  = 'UPDATE '.$tb_name.' set armored=:armored,master=:master where id=:id';
										$m = $db->prepare($m);
										$w = array(':armored'=>serialize($items),':master'=>serialize($master),':id'=>$row['id']);
										$m->execute($w);
									}else if($party1[12]&&$get_w>$now1_p){//party1比較、ゲットした方がよかったら交換
										$bk_w=$party1[12];//grove id backup
										if($party1[0]==1&&$armor[0]==4){
											$plus=4;//サヨが紺糸なら防御が倍
										}else{
											$plus=1;
										}
										$party1[12]=$armor[0];
										$party1[3] =$party1[3]+$armor[2]-$now1_w[2]*$plus;//最初から引いちゃうとマイナスになっちゃうかも？
										$party1[4] =$party1[4]-$now1_w[3]+$armor[3];
										$party1[5] =$party1[5]+$armor[4]-$now1_w[4];
										$party1[8] =$party1[8]+$armor[5]-$now1_w[5];
										$party1[9] =$party1[9]+$armor[6]-$now1_w[6];
										$items = unserialize($row['armored']);
										$items[($bk_w-1)]++;//外したarmorのリストをインクリメントする
										$m  = 'UPDATE '.$tb_name.' set armored=:armored,party1=:party1 where id=:id';
										$m = $db->prepare($m);
										$w = array(':armored'=>serialize($items),':party1'=>serialize($party1),':id'=>$row['id']);
										$m->execute($w);
									}else if($party2[12]&&$get_w>$now2_p){//party1のarmorを比較、ゲットしたarmorの方がよかったら交換
										$bk_w=$party2[12];//armor id backup
										if($party2[0]==1&&$armor[0]==4){
											$plus=4;//サヨが紺糸なら防御が倍
										}else{
											$plus=1;
										}
										$party2[12]=$armor[0];
										$party2[3] =$party2[3]+$armor[2]-$now2_w[2]*$plus;//最初から引いちゃうとマイナスになっちゃうかも？
										$party2[4] =$party2[4]-$now2_w[3]+$armor[3];
										$party2[5] =$party2[5]+$armor[4]-$now2_w[4];
										$party2[8] =$party2[8]+$armor[5]-$now2_w[5];
										$party2[9] =$party2[9]+$armor[6]-$now2_w[6];
										$items = unserialize($row['armored']);
										$items[($bk_w-1)]++;//外したarmorのリストをインクリメントする
										$m  = 'UPDATE '.$tb_name.' set armored=:armored,party2=:party2 where id=:id';
										$m = $db->prepare($m);
										$w = array(':armored'=>serialize($items),':party2'=>serialize($party2),':id'=>$row['id']);
										$m->execute($w);
									}else if($party3[12]&&$get_w>$now3_p){//party1のarmorを比較、ゲットした方がよかったら交換
										$bk_w=$party3[12];//armor id backup
										if($party3[0]==1&&$armor[0]==4){
											$plus=4;//サヨが紺糸なら防御が倍
										}else{
											$plus=1;
										}
										$party3[12]=$armor[0];
										$party3[3] =$party3[3]+$armor[2]-$now3_w[2]*$plus;//最初から引いちゃうとマイナスになっちゃうかも？
										$party3[4] =$party3[4]-$now3_w[3]+$armor[3];
										$party3[5] =$party3[5]+$armor[4]-$now3_w[4];
										$party3[8] =$party3[8]+$armor[5]-$now3_w[5];
										$party3[9] =$party3[9]+$armor[6]-$now3_w[6];
										$items = unserialize($row['armored']);
										$items[($bk_w-1)]++;//外したarmorのリストをインクリメントする
										$m  = 'UPDATE '.$tb_name.' set armored=:armored,party3=:party3 where id=:id';
										$m = $db->prepare($m);
										$w = array(':armored'=>serialize($items),':party3'=>serialize($party3),':id'=>$row['id']);
										$m->execute($w);
									}else if($party4[12]&&$get_w>$now4_p){//party1のarmorを比較、ゲットした方がよかったら交換
										$bk_w=$party4[12];//armor id backup
										if($party4[0]==1&&$armor[0]==4){
											$plus=4;//サヨが紺糸なら防御が倍
										}else{
											$plus=1;
										}
										$party4[12]=$armor[0];
										$party4[3] =$party4[3]+$armor[2]-$now4_w[2]*$plus;//最初から引いちゃうとマイナスになっちゃうかも？
										$party4[4] =$party4[4]-$now4_w[3]+$armor[3];
										$party4[5] =$party4[5]+$armor[4]-$now4_w[4];
										$party4[8] =$party4[8]+$armor[5]-$now4_w[5];
										$party4[9] =$party4[9]+$armor[6]-$now4_w[6];
										$items = unserialize($row['armored']);
										$items[($bk_w-1)]++;//外したarmorのリストをインクリメントする
										$m  = 'UPDATE '.$tb_name.' set armored=:armored,party4=:party4 where id=:id';
										$m = $db->prepare($m);
										$w = array(':armored'=>serialize($items),':party4'=>serialize($party4),':id'=>$row['id']);
										$m->execute($w);
									}else{
										$items = unserialize($row['armored']);
										$items[$emo]++;
										$m  = 'UPDATE '.$tb_name.' set armored=:item where id=:id';
										$m = $db->prepare($m);
										$w = array(':item'=>serialize($items),':id'=>$row['id']);
										$m->execute($w);
									}
								break;
								case 4://get shoes
									foreach($rsho as $kutu){
										if($kutu['id']==($emo+1)){
											$shoes = $kutu;
										}
									}
									$nowm_p=0;$now1_p=0;$now2_p=0;$now3_p=0;$now4_p=0;
									if($master[13]){
										foreach($rsho as $kut){//今持っているshoes
											if($kut['id']==$master[13]){
												$nowm_w = $kut;
											}
										}
										$nowm_p=$nowm_w[2]+$nowm_w[3];
									}
									if($party1[13]){
										foreach($rsho as $kut){//今持っているshoes
											if($kut['id']==$party1[13]){
												$now1_w = $kut;
											}
										}
										$now1_p=$now1_w[2]+$now1_w[3];
									}
									if($party2[13]){
										foreach($rsho as $kut){//今持っているshoes
											if($kut['id']==$party2[13]){
												$now2_w = $kut;
											}
										}
										$now2_p=$now2_w[2]+$now2_w[3];
									}
									if($party3[13]){
										foreach($rsho as $kut){//今持っているshows
											if($kut['id']==$party3[13]){
												$now3_w = $kut;
											}
										}
										$now3_p=$now3_w[2]+$now3_w[3];
									}
									if($party4[13]){
										foreach($rsho as $kut){//今持っているshoes
											if($kut['id']==$party4[13]){
												$now4_w = $kut;
											}
										}
										$now4_p=$now4_w[2]+$now4_w[3];
									}
									$get_w=$shoes[2]+$shoes[3];
									if(!$master[13]){
										$master[13]=$shoes[0];
										$master[3] =$master[3]+$shoes[2];
										$master[4] =$master[4]+$shoes[3];
										$m  = 'UPDATE '.$tb_name.' set master=:master where id=:id';
										$m = $db->prepare($m);
										$w = array(':master'=>serialize($master),':id'=>$row['id']);
										$m->execute($w);
									}else if(!$party1[13]){
										$party1[13]=$shoes[0];
										$party1[3] =$party1[3]+$shoes[2];
										$party1[4] =$party1[4]+$shoes[3];
										$m  = 'UPDATE '.$tb_name.' set party1=:party1 where id=:id';
										$m = $db->prepare($m);
										$w = array(':party1'=>serialize($party1),':id'=>$row['id']);
										$m->execute($w);
									}else if(!$party2[13]){
										$party2[13]=$shoes[0];
										$party2[3] =$party2[3]+$shoes[2];
										$party2[4] =$party2[4]+$shoes[3];
										$m  = 'UPDATE '.$tb_name.' set party2=:party2 where id=:id';
										$m = $db->prepare($m);
										$w = array(':party2'=>serialize($party2),':id'=>$row['id']);
										$m->execute($w);
									}else if(!$party3[13]){
										$party3[13]=$shoes[0];
										$party3[3] =$party3[3]+$shoes[2];
										$party3[4] =$party3[4]+$shoes[3];
										$m  = 'UPDATE '.$tb_name.' set party3=:party3 where id=:id';
										$m = $db->prepare($m);
										$w = array(':party3'=>serialize($party3),':id'=>$row['id']);
										$m->execute($w);
									}else if(!$party4[13]){
										$party4[13]=$shoes[0];
										$party4[3] =$party4[3]+$shoes[2];
										$party4[4] =$party4[4]+$shoes[3];
										$m  = 'UPDATE '.$tb_name.' set party4=:party4 where id=:id';
										$m = $db->prepare($m);
										$w = array(':party4'=>serialize($party4),':id'=>$row['id']);
										$m->execute($w);
									}else if($master[13]&&$get_w>$nowm_p){//マスターのshoesを比較、ゲット方がよかったら交換
										$bk_w=$master[13];//shoes id backup
										$master[13]=$shoes[0];
										$master[3] =$master[3]+$shoes[2]-$nowm_w[2];//最初から引いちゃうとマイナスになっちゃうかも？
										$master[4] =$master[4]+$shoes[3]-$nowm_w[3];
										$items = unserialize($row['shoes']);
										$items[($bk_w-1)]++;//外したshoesのリストをインクリメントする
										$m  = 'UPDATE '.$tb_name.' set shoes=:shoes,master=:master where id=:id';
										$m = $db->prepare($m);
										$w = array(':shoes'=>serialize($items),':master'=>serialize($master),':id'=>$row['id']);
										$m->execute($w);
									}else if($party1[13]&&$get_w>$now1_p){//party1比較、ゲットした方がよかったら交換
										$bk_w=$party1[13];//shoes id backup
										$party1[13]=$shoes[0];
										$party1[3] =$party1[3]+$shoes[2]-$now1_w[2];//最初から引いちゃうとマイナスになっちゃうかも？
										$party1[4] =$party1[4]+$shoes[3]-$now1_w[3];
										$items = unserialize($row['shoes']);
										$items[($bk_w-1)]++;//外した武器のリストをインクリメントする
										$m  = 'UPDATE '.$tb_name.' set shoes=:shoes,party1=:party1 where id=:id';
										$m = $db->prepare($m);
										$w = array(':shoes'=>serialize($items),':party1'=>serialize($party1),':id'=>$row['id']);
										$m->execute($w);
									}else if($party2[13]&&$get_w>$now2_p){//party1の武器を比較、ゲットした武器の方がよかったら交換
										$bk_w=$party2[13];//weapon id backup
										$party2[13]=$shoes[0];
										$party2[3] =$party2[3]+$shoes[2]-$now2_w[2];//最初から引いちゃうとマイナスになっちゃうかも？
										$party2[4] =$party2[4]+$shoes[3]-$now2_w[3];
										$items = unserialize($row['shoes']);
										$items[($bk_w-1)]++;//外した武器のリストをインクリメントする
										$m  = 'UPDATE '.$tb_name.' set shoes=:shoes,party2=:party2 where id=:id';
										$m = $db->prepare($m);
										$w = array(':shoes'=>serialize($items),':party2'=>serialize($party2),':id'=>$row['id']);
										$m->execute($w);
									}else if($party3[13]&&$get_w>$now3_p){//party1の武器を比較、ゲットした武器の方がよかったら交換
										$bk_w=$party3[13];//weapon id backup
										$party3[13]=$shoes[0];
										$party3[3] =$party3[3]+$shoes[2]-$now3_w[2];//最初から引いちゃうとマイナスになっちゃうかも？
										$party3[4] =$party3[4]+$shoes[3]-$now3_w[3];
										$items = unserialize($row['shoes']);
										$items[($bk_w-1)]++;//外した武器のリストをインクリメントする
										$m  = 'UPDATE '.$tb_name.' set shoes=:shoes,party3=:party3 where id=:id';
										$m = $db->prepare($m);
										$w = array(':shoes'=>serialize($items),':party3'=>serialize($party3),':id'=>$row['id']);
										$m->execute($w);
									}else if($party4[13]&&$get_w>$now4_p){//party1の武器を比較、ゲットした武器の方がよかったら交換
										$bk_w=$party4[13];//weapon id backup
										$party4[13]=$shoes[0];
										$party4[3] =$party4[3]+$shoes[2]-$now4_w[2];//最初から引いちゃうとマイナスになっちゃうかも？
										$party4[4] =$party4[4]+$shoes[3]-$now4_w[3];
										$items = unserialize($row['shoes']);
										$items[($bk_w-1)]++;//外したshoesのリストをインクリメントする
										$m  = 'UPDATE '.$tb_name.' set shoes=:shoes,party4=:party4 where id=:id';
										$m = $db->prepare($m);
										$w = array(':shoes'=>serialize($items),':party4'=>serialize($party4),':id'=>$row['id']);
										$m->execute($w);
									}else{
										$items = unserialize($row['shoes']);
										$items[$emo]++;
										$m  = 'UPDATE '.$tb_name.' set shoes=:item where id=:id';
										$m = $db->prepare($m);
										$w = array(':item'=>serialize($items),':id'=>$row['id']);
										$m->execute($w);
									}
								break;
							}
						}
						if($Get_enemy_cp<80){
							$max=2;
						}else if($Get_enemy_cp>=80&&$Get_enemy_cp<100){
							$max=5;
						}else{
							$max=1;
						}
						for($ico=0;$ico<rand(1,$max);$ico++){
							$master[9]++;
						}
						if($master[9]>999){
							$master[9]=999;
						}
						$master[7]++;
						if($master[7]>999){
							$master[7]=999;
						}
						$m  = 'UPDATE '.$tb_name.' set master=:master where id=:id';
						$m = $db->prepare($m);
						$w = array(':master'=>serialize($master),':id'=>$row['id']);
						$m->execute($w);
					}else if($type==1){
						//echo ' : Messege Array! : '.count($message);
						$m  = 'UPDATE '.$tb_name.' set trip=:trip where id=:id';
						$m = $db->prepare($m);
						$w = array(':trip'=>serialize($messeges),':id'=>$row['id']);
						$m->execute($w);
					}else if($type==2){
						$master=unserialize($row['master']);
						if($master[9]>0){
							$master[9]--;
						}
						$m  = 'UPDATE '.$tb_name.' set master=:master where id=:id';
						$m = $db->prepare($m);
						$w = array(':master'=>serialize($master),':id'=>$row['id']);
						$m->execute($w);
					}
				}
			}
		//close mysql
		$db = null;
		}
	}catch(PDOException $e){
		echo "DB connect failure..." . PHP_EOL;
		echo $e->getMessage();
		exit;
	}
}
function myFilter($val) {
	return !($val === "" || $val === false);
  }