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
//セッションの開始
session_start();
//イベントの発生件数
$events = rand(1,9);
//session init
if(!isset($_SESSION['cpu'])){
    $_SESSION['cpu'] = '0';
    $_SESSION['memory'] = '0';
    $_SESSION['benchi'] = '0';
}

//POSTされていない場合はその案内
if ($_SERVER['REQUEST_METHOD']!='POST'){
    //ローカルからの接続の場合
    $iparray = explode(".", $ip);
    if($iparray[0] == '192' && $iparray[1] == '168' && $iparray[2] == '128'){
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
        
                //view databases
                $sql = 'SHOW DATABASES';
                $results = $db->query($sql);
                //array loop
                while ($result = $results->fetch(PDO::FETCH_NUM)){
                    //Does the database exist(DBがあった場合)
                    if($result[0]==$db_name){
                        $sql = 'use '.$db_name;//DBを選択
                        if($db->query($sql)){
                            $sql = "SELECT * FROM ".$tb_name;
                            $enemy = "SELECT * FROM ".$tb_ghost;
                            $sql=$db->query($sql);
							$enemy=$db->query($enemy);
							$result = $enemy->fetchAll();//$enemyのテーブルをデータ化しておく

                            //rowを$sqlから取り出して送られたacountとpasswordが照合するものがあるか調べる
                            foreach($sql as $row){
                                if($row['acount']==$acount && $row['password']==$password){
                                    if(isset($_POST['end_code'])){
                                        //end_codeが送られてきた場合はステータスをUPDATEしてserverでの冒険を始める
                                        $sql = 'UPDATE '.$tb_name.' set a_time=:a_time,ghost=:ghost,item=:items,weapon=:weapons,grove=:gloves,armored=:armored,shoes=:shoses,master=:master,party1=:party1,party2=:party2,party3=:party3,party4=:party4 where id=:id';
                                        $sql = $db->prepare($sql);
                                        $param = array(':a_time'=>time(),':ghost'=>serialize($Ghost),':items'=>serialize($items),':weapons'=>serialize($weapons),':gloves'=>serialize($gloves),':armored'=>serialize($armored),':shoses'=>serialize($shoses),':master'=>serialize($master),':party1'=>serialize($party1),':party2'=>serialize($party2),':party3'=>serialize($party3),':party4'=>serialize($party4),':id'=>$row['id']);
                                        $sql->execute($param);
										//冒険の関数を作っていれる
										battle($Ghost,$result,$master,$party1,$party2,$party3,$party4,$events);
                                    }else if(time()>$row['a_time']+3600){//3600:1hour
                                        //endでない場合でPOSTから1時間を経過していたらはserverのデータをappへ送る
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
											break;
                                                default:
                                                echo 'error:POST is not done correctly.';
                                        }
                                            /*  配列のままだとjsonにしても配列で作成されるのでjsonで受け取れない*/
                                            //普通の配列を得連想配列に変換する
                                    }else{
										if($_POST['getdata']=='trip'){
											$prepare=array("\"0\""=>"START_EVENT!",
															"\"1\""=>"パーティのメンバーで旅の準備をしている最中です。",
															"\"2\""=>"暫くしてから再度起動してみてください。");
											//jsonとして出力
											header('Content-type: application/json; charset=utf-8');
											echo json_encode($prepare);//jsonをclientに出力
										}
										//旅に出してから一時間を経過しないとデータは送信しない
										//print ('Still in the middle of an adventure!');
									}
                                    $exists=true;//存在している
                                }
                            }
                            //アカウントがない場合は作成する
                            if(!$exists){
                                //testなので3項目
                                $sql = 'INSERT INTO '.$tb_name.' (a_time,acount,password) VALUES (:a_time,:acount,:password)';
                                $sql = $db->prepare($sql);
                                $param = array(':a_time'=>time(),':acount'=>$_POST['acount'],':password'=>$_POST['password']);
                                $sql->execute($param);
                                echo 'アカウントを作成しました';
                            }
                        }
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
		//echo 'No.......POST....';
        //終了コードが送られてきた場合：勝手に冒険が始まりログインするまでサーバのゲームシステムに依存
        //端末のGPSの移動距離を元にサーバMapを移動してイベントを発生させる

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
	//global $events;
    //echo '最新の情報を更新しパーティは冒険に出ました-';
    //ghost name set array
	$g_name=array(0=>0,1=>'秋川 サヨ',2=>'三田 レン',3=>'綾瀬 うらら',4=>'吉良 美世',5=>'河内 キヨミ',6=>'横地 監物',7=>'大石 照基',8=>'金子 家重',
	9=>'川下 士郎',
    10=>'白井 あおい',11=>'中畑 修',12=>'綾瀬 社長',13=>'湯浅 五助',14=>'尼子 晴久',15=>'佐々 成政',16=>'武田 勝頼',17=>'上杉 景虎',18=>'松田 憲秀',
	19=>'大内 義隆',20=>'朝倉 義景',21=>'陶 隆房',22=>'足利 義輝',23=>'大内 義長',24=>'別所 長治',25=>'幼いおばけ',26=>'実態不明のおばけ',
	27=>'年老いたおばけ',
    28=>'農民(女子)',29=>'農民(男子)',30=>'町民(女子)',31=>'町民(男子)',32=>'武者',33=>'足軽',34=>'小僧',35=>'僧侶',36=>'犬');
    //まずおばけと出会う
	first($ghosts,$ene,$g_name,$mas,$par1,$par2,$par3,$par4,$loops);
    //var_dump($par1);
}
function first($ghos,$en,$g_nam,$maste,$part1,$part2,$part3,$part4,$loop){
	
	$Ghos = $ghos;//おばけの場合の加算HP
	$G_nam = $g_nam;//おばけの場合の加算AP
	$En = $en;
	//Send Loop number loops
	//print ' LOOP: '.$loop.' : ';
	for($counts=0;$counts<$loop;$counts++){
		//$type=rand(0,10);
		if(1){//0,1=バトルの場合
			$mono=100;
			$emono=100;
			$i=1;//カウント初期化
			$ghosthp = rand(0,7);//おばけの場合の加算HP
			$ghostap = rand(0,3);//おばけの場合の加算AP
			//出会うおばけを選出
			if(rand(0,100)>3){
				$enemy_id=rand(25,36);
			}else{
				//$enemy_id=rand(3,24);//一回しか出ないおばけを選出
				foreach($Ghos as $gho){
					if($i >= 3 && $i <=24 && !$gho && $i!=12){
						$enemy_id = $i;
						continue;
					}
					$i++;
				}
			}
			//print 'Enemy id:'.$enemy_id.':';
			//出会ったおばけのステータスを取得する
			foreach($En as $ghost_on){
				if ($ghost_on['id']==$enemy_id){
					$nu1 = $enemy_id;//get id
					$na1 = $G_nam[$enemy_id];//get name
					$hp1 = $ghost_on['HP']*$ghosthp;
					$at1 = $ghost_on['AP']*$ghostap;
					$de1 = $ghost_on['DP'];
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
			$hp2 = array($maste[2],$part1[2]+intdiv($maste[7],5),$part2[2]+intdiv($maste[7],5),$part3[2]+intdiv($maste[7],5),$part4[2]+intdiv($maste[7],5));
			$at2 = array($maste[6],$part1[6],$part2[6],$part3[6],$part4[6]);
			$de2 = array($maste[3],$part1[3],$part2[3],$part3[3],$part4[3]);
			$qu2 = array($maste[4],$part1[4],$part2[4],$part3[4],$part4[4]);
			$lu2 = array($maste[5],$part1[5],$part2[5],$part3[5],$part4[5]);
			$he2 = array($maste[8],$part1[8],$part2[8],$part3[8],$part4[8]);
			$cu2 = array($maste[7],$part1[7],$part2[7],$part3[7],$part4[7]);
			$sc2 = array($maste[9],$part1[9],$part2[9],$part3[9],$part4[9]);

			$at2_bk=$at2;
			$de2_bk=$de2;
			//echo '--id:'.$nu2[1].'-name:'.$na2[1].'-HP:'.$hp2[1].'-AP:'.$at2[1].'-DP:'.$de2[1].'-SP:'.$qu2[1].'-LP:'.$lu2[1].'-TP:'.$he2[1].'-FP:'.$cu2[1].'-PP:'.$sc2[1];

			//バトルの回数
			$battle_loop=49;//50回で終了

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
			//戦闘ループに入る
			for($i=0;$i<=$battle_loop;$i++){
				if(!$i){
					$mess[] = 'START_EVENT!';
					if($maste[7]>=5){
						//echo ' : FP : '.$maste[7].' : ';
						$mess[] = 'マスター「'.$na2[0].'」の魅力によりHPがそれぞれ'.intdiv($maste[7],5).'加算された！';
					}
				}
				$mess[] = '【'.($battle_loop +1).'回戦中:第'.($i +1).'回戦】';
				if($i==$battle_loop) {
					$mess[] = "双方が疲弊してしまった。おばけはふらふらと逃げて行った。";
					//$mess[] = 'END_EVENT!';
					//update_sql($mess,0);
					break;
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
								$mess[] = '「'.$na2[$c].'」が狙われている！おばけが飛びかかっていく！';

								//攻撃がヒットするかの判定
								$bom = $qu1q1 - $qu2q2[$c];

								//ダメージの演算をしておく
								$damage=$de2d2[$c] - $at1a1;

								//素早さの差が50%以上ならば１００%ヒットする
								if($bom>=$qu2q2[$c] / 2){
									//quickOne();
									if($damage>0){
										$damage=0;
										$msg_second = 'おばけは「'.$na2[$c].'」にダメージを与えられない！';
									}else{
										//攻撃力が二倍または四倍になった
										if($uni_lucky1){
											$msg_firstsecond = 'おばけは「'.$na2[$c].'」に【渾身の一撃】を放った！！';
										}
										//攻撃を回避できなかった場合にはダメージ０にはしない
										if($damage>=0){
											$damage=-1;
										}
										$mdamage=$damage*-1;
										$msg_second = '「'.$na2[$c].'」はおばけの攻撃を避けきれなかった！'.$mdamage.'のダメージ！';
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
											$msg_second = '「'.$na2[$c].'」は素早く攻撃をかわした！';
										}else{
											//攻撃力が二倍または四倍になった
											if($uni_lucky1){
												$msg_firstsecond = 'おばけは「'.$na2[$c].'」に【痛恨の一撃】を放った！！';
											}
											//攻撃を回避できなかった場合にはダメージ０にはしない
											if($damage>=0){
												$damage=-1;
											}
											$mdamage=$damage*-1;
											$msg_second = '「'.$na2[$c].'」はおばけの攻撃を避けきれなかった！'.$mdamage.'のダメージ！';
										}
										//ダメージを受けた分をhpから差し引く
										$hp2[$c] = $hp2[$c] + $damage;

										//hp2がマイナスの場合は０と表示する
										if($hp2[$c]<1){
											$hp2[$c]=0;
										}
									}else{
										$msg_second = '「'.$na2[$c].'」はおばけの攻撃をかわした！';
									}
								//それ未満なら５０%ヒットする
								}else{
									$b=rand(0,99);
									if($b<49){
										//quickOne();
										if($damage>0){
											$damage=0;
											$msg_second = 'おばけの攻撃は「'.$na2[$c].'」に当たらなかった！';
										}else{
											//攻撃力が二倍または四倍になった
											if($uni_lucky1){
												$msg_firstsecond = 'おばけは「'.$na2[$c].'」に【会心の一撃】を放った！！';
											}
											//攻撃を回避できなかった場合にはダメージ０にはしない
											if($damage>=0){
												$damage=-1;
											}
											$mdamage=$damage*-1;
											$msg_second = '「'.$na2[$c].'」はおばけの攻撃を受けてしまった！'.$mdamage.'のダメージ！';
										}
										//ダメージを受けた分をhpから差し引く
										$hp2[$c] = $hp2[$c] + $damage;

										//hp2がマイナスの場合は０と表示する
										if($hp2[$c]<1){
											$hp2[$c]=0;
										}
										
									}else{
										$msg_second = '「'.$na2[$c].'」はおばけの攻撃を避ける事ができた！';
									}
								}
								if(isset($msg_firstsecond)){
									$mess[] = $msg_firstsecond;		
								}
								$mess[] = $msg_second;
								
								//二人のステータスを表示
								$mess[] = ' --おばけの残HP【'.$hp1.'】対 '.$na2[$c].'の残HP【'.$hp2[$c].'】--';
								/*/tableTAG
								echo '<br>'.$tabletag0;
								//出力する
								echo '<tr><th>'.$nu1.'</th><th>'.$na1.'</th><th>'.$le1.'</th><th>'.$hp1.'</th><th>'.$de1.'</th><th>'.$qu1.'</th><th>'.$lu1.'</th><th>'.$at1.'</th><th>'.$cu1.'</th><th>'.$he1.'</th><th>'.$sc1.'</th></tr>';
								//出力する
								echo '<tr><th>'.$nu2[$c].'</th><th>'.$na2[$c].'</th><th>'.$le2[$c].'</th><th>'.$hp2[$c].'</th><th>'.$de2[$c].'</th><th>'.$qu2[$c].'</th><th>'.$lu2[$c].'</th><th>'.$at2[$c].'</th><th>'.$cu2[$c].'</th><th>'.$he2[$c].'</th><th>'.$sc2[$c].'</th></tr>';
								//tableTAG_END
								echo '</table><br>';*/
								//ded
								if($hp2[$c]<1){
									$mess[] = '「'.$na2[$c]."」はおばけに敗北してしまった。";
									$p++;
								}
								//msg_firstsecondを空にしておく
								$msg_firstsecond="";
							//素早さがパーティメンバーのほうが大きい場合
							}else if($qu1<$qu2[$c] && !$first_attack ||$first_attack==1){
								//マスターの魅力が9以下でマスターではない場合
								if($sc2[0]<10&&$nu2[$c]!=$nu2[0]){
									$mess[]= "「".$na2[$c]."」は"."「".$na2[0]."の後ろに怯えて隠れてしまった.......";
									$mess[]= "「".$na2[$c]."」は戦力にならない！";
									//msg_firstsecondを空にしておく
									$msg_firstsecond="";
								}else{
									//先制攻撃フラグに2を入れる
									$first_attack = 2;
									//msgを出力
									$mess[] = '「'.$na2[$c].'」が動いた！「'.$na2[$c].'」の攻撃！';

									//攻撃がヒットするかの判定
									$bom = $qu2q2[$c] - $qu1q1;

									//ダメージの演算をしておく
									$damage=$de1d1-$at2a2[$c];

									//素早さの差が50%以上ならば１００%ヒットする
									if($bom>=$qu1q1/2){
										//quickOne();
										if($damage>0){
											$damage=0;
											$msg_second = '「'.$na2[$c].'」の攻撃は外れ、おばけにダメージを与えられない！';
										}else{
											//攻撃力が二倍または四倍になった
											if($uni_lucky2){
												$msg_firstsecond = '「'.$na2[$c].'」は【渾身の一撃】を放った！！';
											}
											//攻撃を回避できなかった場合にはダメージ０にはしない
											if($damage>=0){
												$damage=-1;
											}
											$mdamage=$damage*-1;
											$msg_second = 'おばけに「'.$na2[$c].'」の一撃が放たれた！'.$mdamage.'のダメージ！';
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
												$msg_second = '「'.$na2[$c].'」の攻撃は避けられてしまった！';
											}else{
												//攻撃力が二倍または四倍になった
												if($uni_lucky2){
													$msg_firstsecond = '「'.$na2[$c].'」は【痛恨の一撃】を放った！！';
												}
												//攻撃を回避できなかった場合にはダメージ０にはしない
												if($damage>=0){
													$damage=-1;
												}
												$mdamage=$damage*-1;
												$msg_second = '「'.$na2[$c].'」の攻撃がおばけを捉えた！'.$mdamage.'のダメージ！';
											}
											//ダメージを受けた分をhpから差し引く
											$hp1 = $hp1 + $damage;

											//hp2がマイナスの場合は０と表示する
											if($hp1<1){
												$hp1=0;
											}
										}else{
											$msg_second = 'おばけは「'.$na2[$c].'」の攻撃をひらりとかわした！';
										}
									//それ未満なら５０%ヒットする
									}else{
										$b=rand(0,99);
										if($b<49){
											//quickOne();
											if($damage>0){
												$damage=0;
												$msg_second = '「'.$na2[$c].'」の攻撃は当たらなかった！';
											}else{
												//攻撃力が二倍または四倍になった
												if($uni_lucky2){
													$msg_firstsecond = '「'.$na2[$c].'」は【渾身の一撃】を放った！！';
												}
												//攻撃を回避できなかった場合にはダメージ０にはしない
												if($damage>=0){
													$damage=-1;
												}
												$mdamage=$damage*-1;
												$msg_second = 'おばけは「'.$na2[$c].'」の攻撃を喰らってしまった！'.$mdamage.'のダメージ！';
											}
											//ダメージを受けた分をhpから差し引く
											$hp1 = $hp1 + $damage;

											//hp2がマイナスの場合は０と表示する
											if($hp1<1){
												$hp1=0;
											}
											
										}else{
											$msg_second = 'おばけは「'.$na2[$c].'」の攻撃から逃げた！';
										}
									}
									if(isset($msg_firstsecond)){
										$mess[] = $msg_firstsecond;		
									}
									$mess[] = $msg_second;
									
									//二人のステータスを表示
									$mess[] = ' --おばけの残HP【'.$hp1.'】対 '.$na2[$c].'の残HP【'.$hp2[$c].'】--';
									/*/tableTAG
									echo '<br>'.$tabletag0;
									//出力する
									echo '<tr><th>'.$nu1.'</th><th>'.$na1.'</th><th>'.$le1.'</th><th>'.$hp1.'</th><th>'.$de1.'</th><th>'.$qu1.'</th><th>'.$lu1.'</th><th>'.$at1.'</th><th>'.$cu1.'</th><th>'.$he1.'</th><th>'.$sc1.'</th></tr>';
									//出力する
									echo '<tr><th>'.$nu2[$c].'</th><th>'.$na2[$c].'</th><th>'.$le2[$c].'</th><th>'.$hp2[$c].'</th><th>'.$de2[$c].'</th><th>'.$qu2[$c].'</th><th>'.$lu2[$c].'</th><th>'.$at2[$c].'</th><th>'.$cu2[$c].'</th><th>'.$he2[$c].'</th><th>'.$sc2[$c].'</th></tr>';
									//tableTAG_END
									echo '</table><br>';*/
									//ded
									if($hp1<1){
										$mess[] = "おばけは「".$na2[$c]."」に敗北して浄化された。我に返ったおばけは「".$na1."」だった。";
										//$mess[] = 'END_EVENT!';
										$otosimono=rand(0,20);
										//1/20の確率で落とすので拾う
										if($otosimono==0){
											$mono=rand(0,4);
											switch($mono){
												case 0:
													$emono=rand(0,15);
													if($emono==15){
														$emono=rand(0,15);
														if($emono==15){
															$emono=rand(0,15);
															if($emono==15){
																$emono=rand(0,15);
																if($emono==15){
																	$emono=rand(0,15);//1/759375
																}
															}
														}
													}
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
													}
												break;
												case 1:
													$emono=rand(0,4);
													if($emono>3){
														$emono=rand(0,5);
														if($emono>4){
															$emono=rand(0,8);
															if($emono>7){
																$emono=rand(0,17);
																if($emono>8){
																	$emono=rand(0,17);
																}
															}
														}
													}
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
													$gets='手袋：';
													switch($emono){
														case 0:	$gets=$gets.'軍手';	break;
														case 1:	$gets=$gets.'皮の手袋';	break;
														case 2:	$gets=$gets.'籠手';	break;
													}
												break;
												case 3:
													$emono=rand(0,3);
													if($emono!=0){
														$emono=rand(0,3);
														if($emono!=0){
															$emono=rand(0,3);
															if($emono!=0){
																$emono=rand(0,3);
																if($emono!=0){
																	$emono=rand(0,3);
																	if($emono>=1){
																		$emono=rand(0,3);
																		if($emono>=1){
																			$emono=rand(0,3);
																			if($emono>=1){
																				$emono=rand(0,3);
																				if($emono>=1){
																					$emono=rand(0,3);
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
													$gets='防具：';
													switch($emono){
														case 0:	$gets=$gets.'竹胴';	break;
														case 1:	$gets=$gets.'無銘具足';	break;
														case 2:	$gets=$gets.'南蛮胴具足';	break;
														case 3:	$gets=$gets.'紺糸裾素懸威胴丸';	break;
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
													$gets='靴：';
													switch($emono){
														case 0:	$gets=$gets.'瞬足';	break;
														case 1:	$gets=$gets.'安全靴';	break;
														case 2:	$gets=$gets.'歩雲履';	break;
													}
											}
												$mess[] = 'START_EVENT!';
												$mess[] = '何かを落とすのが見えた。拾ってみると'.$gets.'のようだ！';
												$mess[] = '「'.$gets.'」をゲットした！';
												//$mess[] = 'END_GET!';
										}
										$battle_loop=$i;
										update_sql($mess,$nu1,$mono,$emono,0);
										//update_sql($mess,$nu1);
										break;
										//continue;
									}
									//msg_firstsecondを空にしておく
									$msg_firstsecond="";
								}
							//素早さが同じ場合
							}else if($qu1 == $qu2[$c]){
								$mess[] = "おばけと「".$na2[$c]."」は互いに動けないでいる！";
								//抽選するよ:５０％の確率
								//素早さが同じでおばけから攻撃する場合
								if(rand(0, 1)) {
									//先制攻撃フラグに１を入れる
									$first_attack = 1;
									//msgを出力
									$mess[] = 'おばけが飛びかかっていく！「'.$na2[$c].'」が狙われている！';

									//攻撃がヒットするかの判定
									$bom = $qu1q1 - $qu2q2[$c];

									//ダメージの演算をしておく
									$damage=$de2d2[$c] - $at1a1;

									//素早さの差が50%以上ならば１００%ヒットする
									if($bom>=$qu2q2[$c] / 2){
										//quickOne();
										if($damage>0){
											$damage=0;
											$msg_second = 'おばけは「'.$na2[$c].'」への攻撃を外した！';
										}else{
											//攻撃力が二倍または四倍になった
											if($uni_lucky1){
												$msg_firstsecond = 'おばけは「'.$na2[$c].'」に【渾身の一撃】を放った！！';
											}
											//攻撃を回避できなかった場合にはダメージ０にはしない
											if($damage>=0){
												$damage=-1;
											}
											$mdamage=$damage*-1;
											$msg_second = '「'.$na2[$c].'」におばけの一撃が命中する！'.$mdamage.'のダメージ！';
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
												$msg_second = 'おばけの「'.$na2[$c].'」への攻撃は空ぶった！';
											}else{
												//攻撃力が二倍または四倍になった
												if($uni_lucky1){
													$msg_firstsecond = 'おばけは「'.$na2[$c].'」に【痛恨の一撃】を放った！！';
												}
												//攻撃を回避できなかった場合にはダメージ０にはしない
												if($damage>=0){
													$damage=-1;
												}
												$mdamage=$damage*-1;
												$msg_second = '「'.$na2[$c].'」は'.$mdamage.'のダメージを受けてしまった！';
											}
											//ダメージを受けた分をhpから差し引く
											$hp2[$c] = $hp2[$c] + $damage;

											//hp2がマイナスの場合は０と表示する
											if($hp2[$c]<1){
												$hp2[$c]=0;
											}
										}else{
											$msg_second = '「'.$na2[$c].'」は見切ったおばけの攻撃をかわした！';
										}
									//それ未満なら５０%ヒットする
									}else{
										$b=rand(0,99);
										if($b<49){
											//quickOne();
											if($damage>0){
												$damage=0;
												$msg_second = 'おばけは「'.$na2[$c].'」に攻撃をしたが外れてしまった！';
											}else{
												//攻撃力が二倍または四倍になった
												if($uni_lucky1){
													$msg_firstsecond = 'おばけは「'.$na2[$c].'」に【会心の一撃】を放った！！';
												}
												//攻撃を回避できなかった場合にはダメージ０にはしない
												if($damage>=0){
													$damage=-1;
												}
												$mdamage=$damage*-1;
												$msg_second = '「'.$na2[$c].'」は'.$mdamage.'のダメージを喰らってしまった！';
											}
											//ダメージを受けた分をhpから差し引く
											$hp2[$c] = $hp2[$c] + $damage;

											//hp2がマイナスの場合は０と表示する
											if($hp2[$c]<1){
												$hp2[$c]=0;
											}
										
										}else{
											$msg_second = '「'.$na2[$c].'」はおばけの攻撃をかわした！';
										}
									}
									if(isset($msg_firstsecond)){
										$mess[] = $msg_firstsecond;		
									}
									$mess[] =  $msg_second;
								
									//二人のステータスを表示
									$mess[] = ' --おばけの残HP【'.$hp1.'】対 '.$na2[$c].'の残HP【'.$hp2[$c].'】--';
									/*/tableTAG
									echo '<br>'.$tabletag0;
									//出力する
									echo '<tr><th>'.$nu1.'</th><th>'.$na1.'</th><th>'.$le1.'</th><th>'.$hp1.'</th><th>'.$de1.'</th><th>'.$qu1.'</th><th>'.$lu1.'</th><th>'.$at1.'</th><th>'.$cu1.'</th><th>'.$he1.'</th><th>'.$sc1.'</th></tr>';
									//出力する
									echo '<tr><th>'.$nu2[$c].'</th><th>'.$na2[$c].'</th><th>'.$le2[$c].'</th><th>'.$hp2[$c].'</th><th>'.$de2[$c].'</th><th>'.$qu2[$c].'</th><th>'.$lu2[$c].'</th><th>'.$at2[$c].'</th><th>'.$cu2[$c].'</th><th>'.$he2[$c].'</th><th>'.$sc2[$c].'</th></tr>';
									//tableTAG_END
									echo '</table><br>';*/
									//ded
									if($hp2[$c]<1){
										$mess[] = '「'.$na2[$c]."」はおばけに負けてしまった。";
										$p++;
									}
									//msg_firstsecondを空にしておく
									$msg_firstsecond="";
								//素早さが同じでパーティメンバーから攻撃する場合
								}else {
									//マスターの魅力が9以下でマスターではない場合
									if($sc2[0]<10&&$nu2[$c]!=$nu2[0]){
										$mess[]= "「".$na2[$c]."」は"."「".$na2[0]."の後ろに怯えて隠れてしまった.......";
										$mess[]= "「".$na2[$c]."」は戦力にならない！";
										//msg_firstsecondを空にしておく
										$msg_firstsecond="";
									}else{
										//先制攻撃フラグに2を入れる
										$first_attack = 2;
										//msgを出力
										$mess[] = '「'.$na2[$c].'」が素早く動いた！「'.$na2[$c].'」の攻撃！';

										//攻撃がヒットするかの判定
										$bom = $qu2q2[$c] - $qu1q1;

										//ダメージの演算をしておく
										$damage=$de1d1-$at2a2[$c];

										//素早さの差が50%以上ならば１００%ヒットする
										if($bom>=$qu1q1/2){
											//quickOne();
											if($damage>0){
												$damage=0;
												$msg_second = '「'.$na2[$c].'」の攻撃はおばけに見切られてしまった！';
											}else{
												//攻撃力が二倍または四倍になった
												if($uni_lucky2){
													$msg_firstsecond = '「'.$na2[$c].'」は【渾身の一撃】を放った！！';
												}
												//攻撃を回避できなかった場合にはダメージ０にはしない
												if($damage>=0){
													$damage=-1;
												}
												$mdamage=$damage*-1;
												$msg_second = 'おばけは「'.$na2[$c].'」から'.$mdamage.'のダメージを受けてしまった！';
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
													$msg_second = '「'.$na2[$c].'」の攻撃は避けられてしまった！';
												}else{
													//攻撃力が二倍または四倍になった
													if($uni_lucky2){
														$msg_firstsecond = '「'.$na2[$c].'」は【痛恨の一撃】を放った！！';
													}
													//攻撃を回避できなかった場合にはダメージ０にはしない
													if($damage>=0){
														$damage=-1;
													}
													$mdamage=$damage*-1;
													$msg_second = 'おばけは「'.$na2[$c].'」からの攻撃で'.$mdamage.'のダメージを受けた！';
												}
												//ダメージを受けた分をhpから差し引く
												$hp1 = $hp1 + $damage;

												//hp2がマイナスの場合は０と表示する
												if($hp1<1){
													$hp1=0;
												}
											}else{
												$msg_second = 'おばけは「'.$na2[$c].'」の攻撃を避けた！';
											}
										//それ未満なら５０%ヒットする
										}else{
											$b=rand(0,99);
											if($b<49){
												//quickOne();
												if($damage>0){
													$damage=0;
													$msg_second = '「'.$na2[$c].'」の攻撃はおばけにダメージを与えられない！';
												}else{
													//攻撃力が二倍または四倍になった
													if($uni_lucky2){
														$msg_firstsecond = '「'.$na2[$c].'」は【渾身の一撃】を放った！！';
													}
													//攻撃を回避できなかった場合にはダメージ０にはしない
													if($damage>=0){
														$damage=-1;
													}
													$mdamage=$damage*-1;
													$msg_second = 'おばけは「'.$na2[$c].'」の攻撃を喰らってしまった！'.$mdamage.'のダメージ！';
												}
												//ダメージを受けた分をhpから差し引く
												$hp1 = $hp1 + $damage;

												//hp2がマイナスの場合は０と表示する
												if($hp1<1){
													$hp1=0;
												}
											
											}else{
												$msg_second = 'おばけは「'.$na2[$c].'」の一撃を避ける事ができた！';
											}
										}
										if(isset($msg_firstsecond)){
											$mess[] = $msg_firstsecond;		
										}
										$mess[] = $msg_second;
									
										//二人のステータスを表示
										$mess[] = ' --おばけの残HP【'.$hp1.'】対 '.$na2[$c].'の残HP【'.$hp2[$c].'】--';
										/*/tableTAG
										echo '<br>'.$tabletag0;
										//出力する
										echo '<tr><th>'.$nu1.'</th><th>'.$na1.'</th><th>'.$le1.'</th><th>'.$hp1.'</th><th>'.$de1.'</th><th>'.$qu1.'</th><th>'.$lu1.'</th><th>'.$at1.'</th><th>'.$cu1.'</th><th>'.$he1.'</th><th>'.$sc1.'</th></tr>';
										//出力する
										echo '<tr><th>'.$nu2[$c].'</th><th>'.$na2[$c].'</th><th>'.$le2[$c].'</th><th>'.$hp2[$c].'</th><th>'.$de2[$c].'</th><th>'.$qu2[$c].'</th><th>'.$lu2[$c].'</th><th>'.$at2[$c].'</th><th>'.$cu2[$c].'</th><th>'.$he2[$c].'</th><th>'.$sc2[$c].'</th></tr>';
										//tableTAG_END
										echo '</table>';*/
										//ded
										if($hp1<1){
											$mess[] = "おばけは「".$na2[$c]."」に敗北して浄化され正気に戻った。おばけは「".$na1."」だった。";
											//$mess[] = 'END_EVENT!';
											$battle_loop=$i;
											update_sql($mess,$nu1,100,100,0);
											//update_sql($mess,$nu1);
											break;
											//continue;
										}
										//msg_firstsecondを空にしておく
										$msg_firstsecond="";
									}
								}
							}
						}else{
							$mess[] =  'もう「'.$na2[$c].'」は力尽きている...';
						}
						$c++;
					}
				}
				if($count==$p){
					$mess[] = 'パーティは全滅してしまった.....';
					//$mess[] = 'END_EVENT!';
					$battle_loop=$i;
					$nu1=1;
					continue;
				}
			}
		}
	}
	$mess=array_filter($mess, 'myFilter');//配列の空を取り除く
	if($counts==$loop){
		if(!empty($mess)){
			update_sql($mess,$nu1,$mono,$emono,1);
		}else{
			update_sql('Empty Array!',$nu1,$mono,$emono,1);
		}
	}
	//var_dump($mess);
	//echo ' : '.$counts.'=='.$loop.' : ';
}
function update_sql($messeges,$enemy_number,$mon,$emo,$type){//ここでsqlに書き込み
	
	//array_push($messeges,'END_EVENT!');
	global $host;
	global $user;
	global $pass;
	global $db_name;
	global $tb_name;
	global $tb_ghost;
	global $acount;
	global $password;
	try{
		if($enemy_number){
			//Sql connect
			$db = new PDO($host,$user,$pass);
			//echo '	：tripに書き込みログインOK:　';
			//view databases
			$sql = 'SHOW DATABASES';
			$results = $db->query($sql);
			//array loop
			while ($result = $results->fetch(PDO::FETCH_NUM)){
				//Does the database exist(DBがあった場合)
				if($result[0]==$db_name){
					$sql = 'use '.$db_name;//DBを選択
					if($db->query($sql)){
						$player = "SELECT * FROM ".$tb_name;
						$player=$db->query($player);
						$ghosttb = "SELECT * FROM ".$tb_ghost;
						$ghosttb=$db->query($ghosttb);
						//
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
								$get_enemy[7]=$party['FP'];
								$get_enemy[8]=$party['TP'];
								$get_enemy[9]=$party['PP'];
								$get_enemy[10]=0;
								$get_enemy[11]=0;
								$get_enemy[12]=0;
								$get_enemy[13]=0;
							}
						}
						//echo '  :acount:  '.$acount.' :PASS: '.$password;
						foreach($player as $row){
							if($row['acount']==$acount && $row['password']==$password){
								if(!$type){
									print ' : battle : ';
									$ghost = unserialize($row['ghost']);
									for($i=0;$i<count($ghost);$i++){
										if($i==($enemy_number)){
											$ghost[$i]++;
										}
										$ghost[$i]=$ghost[$i];
									}
									$party1= unserialize($row['party1']);
									$party2= unserialize($row['party2']);
									$party3= unserialize($row['party3']);
									$party4= unserialize($row['party4']);
									if(!$party1[0]){//party1にだれもセットされていないかったら
										
										$m  = 'UPDATE '.$tb_name.' set party1=:party1 where id=:id';
										$m = $db->prepare($m);
										$w = array(':party1'=>serialize($get_enemy),':id'=>$row['id']);
										$m->execute($w);
									}else if(!$party2[0]){//party2にだれもセットされていないかったら
										
										$m  = 'UPDATE '.$tb_name.' set party2=:party2 where id=:id';
										$m = $db->prepare($m);
										$w = array(':party2'=>serialize($get_enemy),':id'=>$row['id']);
										$m->execute($w);
									}else if(!$party3[0]){//party3にだれもセットされていないかったら
										
										$m  = 'UPDATE '.$tb_name.' set party3=:party3 where id=:id';
										$m = $db->prepare($m);
										$w = array(':party3'=>serialize($get_enemy),':id'=>$row['id']);
										$m->execute($w);
									}else if(!$party4[0]){//party4にだれもセットされていないかったら
										
										$m  = 'UPDATE '.$tb_name.' set party4=:party4 where id=:id';
										$m = $db->prepare($m);
										$w = array(':party4'=>serialize($get_enemy),':id'=>$row['id']);
										$m->execute($w);
									}
									//落し物を拾っている場合
									if($mon!=100&&$emo!=100){
										print ' : hirotteru : ';
										switch($mon){
											case 0:
												$items = unserialize($row['item']);
												for($i=0;$i<count($items);$i++){
													if($i==$emo){
														$items[$i]++;
													}
													$items[$i]=$items[$i];
												}
												$m  = 'UPDATE '.$tb_name.' set ghost=:ghost,item=:item where id=:id';
												$m = $db->prepare($m);
												$w = array(':ghost'=>serialize($ghost),':item'=>serialize($items),':id'=>$row['id']);
												$m->execute($w);
											break;
											case 1:
												$items = unserialize($row['weapon']);
												for($i=0;$i<count($items);$i++){
													if($i==$emo){
														$items[$i]++;
													}
													$items[$i]=$items[$i];
												}
												$m  = 'UPDATE '.$tb_name.' set ghost=:ghost,weapon=:item where id=:id';
												$m = $db->prepare($m);
												$w = array(':ghost'=>serialize($ghost),':item'=>serialize($items),':id'=>$row['id']);
												$m->execute($w);
											break;
											case 2:
												$items = unserialize($row['grove']);
												for($i=0;$i<count($items);$i++){
													if($i==$emo){
														$items[$i]++;
													}
													$items[$i]=$items[$i];
												}
												$m  = 'UPDATE '.$tb_name.' set ghost=:ghost,grove=:item where id=:id';
												$m = $db->prepare($m);
												$w = array(':ghost'=>serialize($ghost),':item'=>serialize($items),':id'=>$row['id']);
												$m->execute($w);
											break;
											case 3:
												$items = unserialize($row['armored']);
												for($i=0;$i<count($items);$i++){
													if($i==$emo){
														$items[$i]++;
													}
													$items[$i]=$items[$i];
												}
												$m  = 'UPDATE '.$tb_name.' set ghost=:ghost,armored=:item where id=:id';
												$m = $db->prepare($m);
												$w = array(':ghost'=>serialize($ghost),':item'=>serialize($items),':id'=>$row['id']);
												$m->execute($w);
											break;
											case 4:
												$items = unserialize($row['shoes']);
												for($i=0;$i<count($items);$i++){
													if($i==$emo){
														$items[$i]++;
													}
													$items[$i]=$items[$i];
												}
												$m  = 'UPDATE '.$tb_name.' set ghost=:ghost,shoes=:item where id=:id';
												$m = $db->prepare($m);
												$w = array(':ghost'=>serialize($ghost),':item'=>serialize($items),':id'=>$row['id']);
												$m->execute($w);
											break;
										}
									}else{
										print ' : nongetitem : ';
										$m  = 'UPDATE '.$tb_name.' set ghost=:ghost where id=:id';
										$m = $db->prepare($m);
										$w = array(':ghost'=>serialize($ghost),':id'=>$row['id']);
										$m->execute($w);
									}
								}else{
									if($messeges=='Empty Array!'){
										echo ' : Empty Array! : ';
										$m  = 'UPDATE '.$tb_name.' set trip=:trip where id=:id';
										$m = $db->prepare($m);
										$w = array(':trip'=>'',':id'=>$row['id']);
										$m->execute($w);
									}else{
										echo ' : Messege Array! : ';
										$m  = 'UPDATE '.$tb_name.' set trip=:trip where id=:id';
										$m = $db->prepare($m);
										$w = array(':trip'=>serialize($messeges),':id'=>$row['id']);
										$m->execute($w);
										session_destroy();
									}
								}
							}
						}
					}
				}
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
function myFilter($val) {
	return !($val === "" || $val === false);
  }