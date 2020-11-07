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
//session init
if(!isset($_SESSION['cpu'])){
    $_SESSION['cpu'] = '0';
    $_SESSION['memory'] = '0';
    $_SESSION['benchi'] = '0';
}
if(!isset($_SESSION['count'])){
    $_SESSION['count'] = 0;
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
    //AndroidからappcodeがPOSTされているなら正式なログイン
    if($_POST['appcode']==$appcode || stripos($user_agent,'Android') !== false){
        //終了コードが送られてこない場合は端末のゲームシステムに依存し、サーバの処理を終了する
        //初めてのログインならばfalseを返して端末の初期値を端末で生成し、送り、アカウントを登録してDBを生成
        //echo 'LOGIN OK : ';
        if($_POST['acount']){
        	//echo 'ISSET OK : ';
            //$Login=$_POST['LoginArray'];
            $Ghost=explode("|", $_POST['ghost']);
            try{
            	//echo 'TRY OK : ';
                //Sql connect
                $db = new PDO($host,$user,$pass);
        
                //view databases
                $sql = 'SHOW DATABASES';
                $results = $db->query($sql);
                //echo 'SHOW DB : ';
                //array loop
                while ($result = $results->fetch(PDO::FETCH_NUM)){
                    //Does the database exist(DBがあった場合)
                    if($result[0]==$db_name){
                        $sql = 'use '.$db_name;//DBを選択
                        if($db->query($sql)){
                            $sql = "SELECT * FROM ".$tb_name;
                            $sql=$db->query($sql);
                            //$sql is Empty
                            if(!$sql){
                            	$sql = 'INSERT INTO '.$tb_name.' (acount,password,ghost) VALUES (:acount,:password,:ghost)';
                                $sql = $db->prepare($sql);
                                $param = array(':acount'=>$_POST['acount'],':password'=>$_POST['password'],':ghost'=>serialize($Ghost));
                                $sql->execute($param);
                                echo 'アカウントを作成しました';
                            }
                            //rowを$sqlから取り出して送られたacountとpasswordが照合するものがあるか調べる
                            foreach($sql as $row){
                                if($row['acount']==$_POST['acount'] && $row['password']==$_POST['password']){
                                    if($_POST['end_code']){
                                        //end_codeが送られてきた場合はステータスをUPDATEしてserverでの冒険を始める
                                        $sql = 'UPDATE '.$tb_name.' set ghost=:ghost where id=:id';
                                        $sql = $db->prepare($sql);
                                        $param = array(':ghost'=>serialize($Ghost),':id'=>$row['id']);
                                        $sql->execute($param);
                                        session_destroy();//セッションをクリアする
                                        //冒険の関数を作っていれる
                                        echo '最新の情報を更新しパーティは冒険に出ました';
                                    }else{
                                        //endでない場合で一回目ならはserverのデータをappへ送る
                                        if(!$_SESSION['count']){//countが0なら
                                            /*  配列のままだとjsonにしても配列で作成されるのでjsonで受け取れない*/
                                            //普通の配列を得連想配列に変換する
                                            $row = unserialize($row['ghost']);//Sqlのシリアライズを戻す
                                            $key = array_keys($row);//配列のキーを取り出しておく
                                            //取り出したキーの分だけ文字列でキーをjson配列用に作り直しておく
                                            for($i=0;$i<count($key);$i++){
                                                $keys[$i] = '"'.$i.'"';
                                            }
                                            //配列をjson用に連想配列に作り直しておく
                                            $rows = array_combine($keys,$row);
                                            
                                            //jsonとして出力
                                            header('Content-type: application/json');
                                            echo json_encode($rows);//jsonをclientに出力
                                            $_SESSION['count']++;//インクリメント
                                            //echo '本日最初のログインです更新情報を送ります';
                                        }else{//2回目以降はDBに書き込む
                                            $sql = 'UPDATE '.$tb_name.' set ghost=:ghost where id=:id';
                                            $sql = $db->prepare($sql);
                                            $param = array(':ghost'=>serialize($Ghost),':id'=>$row['id']);
                                            $sql->execute($param);
                                            echo $_SESSION['count'].'回目の更新です';
                                            $_SESSION['count']++;//インクリメント
                                        }
                                    }
                                    $sql = $db->prepare($sql);
                                    $exists=true;//存在している
                                    continue;//あったら終わりで次の処理へ
                                }
                            }
                            //アカウントがない場合は作成する
                            if(!$exists){
                                //testなので3項目
                                $sql = 'INSERT INTO '.$tb_name.' (acount,password,ghost) VALUES (:acount,:password,:ghost)';
                                $sql = $db->prepare($sql);
                                $param = array(':acount'=>$_POST['acount'],':password'=>$_POST['password'],':ghost'=>serialize($Ghost));
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
?>
