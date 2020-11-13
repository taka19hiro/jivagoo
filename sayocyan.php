<?php
/***************************************************************************/
/*		
		MySqlのtimepoolデータベースに接続してTABLE1テーブルから
		一覧を見るまたは指定したキャラクタを見て対戦相手を決めて
		バトルさせて勝敗を求め、経験値を所得してレベルアップするなら
		レベルアップして各パラメータを加算してどんどん強くなって行く
		ゲームプログラムのPHP版
		
		cp値とexpが同じ値になるとレベルアップする。expの一割をランダムに
		HP、Attack、Defence、Quicknessに振り分ける。
		
		MySqlサーバからキャラクタデータをSQLiteにコピーして
		レベルアップやアイテムイベントデータは端末に書き込む
		
		まあ、このMySqlとphp移植バージョンではタイトルの表示後、画面遷移し
		キャラクタイラストの表示とゲームの説明をして、
		出場者一覧を見て、出場者を二名選択して、説明を見て（見ない場合も）
		スタートさせる。
		一戦ごとアニメーションを見せ、勝敗を見せる演出を入れて雌雄を決したら
		おしまいでエンディングへという感じ。
		
                                                                                                                                                                              */
/***************************************************************************/
/*$hos = 'localhost';
$us = 'root';
$pas = '05233309';
$db_name = 'timepool';
$tb_name = 'TABLE1';*/
//↑まではMySql接続情報
//Mysql info//////////////////////////////
//DB name
$db_name="GhostScanAR_db";
//table name
$tb_name="ghost_tb";
//host name
$host = 'mysql:host=localhost';
//sql user name
$user = 'takayama';
//sql password
$pass = 'Masahiro4612*';

//一覧を見るか見ないかのフラグ:今は見るになっている
$allviewflg = 1;
//↓は抽出キャラクタ2名を指定
$numb1 = 1;//これはサヨ
$numb2 = 3;//これはうらら
//これは抽出したキャラクタ詳細を見るか見ないかのフラグ：見るに設定
$viewchar = 1;
//見るキャラを指定
$viewcharNo = 77;//横地監物に指定

//tabletagHeader
$tabletag0 =  '<table border=1 width=800>
			<tr><th>id</th><th>Name</th><th>cp</th><th>HP</th><th>DP</th><th>SP</th><th>LP</th><th>AP</th><th>FP</th><th>TP</th><th>PP</th></tr>';

//MySqlに接続してみる
//$db = new PDO($host,$user,$pass);
//$c = mysqli_connect($hos, $us, $pas, $db_name) ;
//charsetをutf8にしないと文字化けしちゃうよ
//mysqli_set_charset($c,'utf8');

//$cがFALSEなら接続失敗
try{
	$db = new PDO($host,$user,$pass);
	$sql = 'use '.$db_name;//DBを選択
	$db->query($sql);
	//一覧を見る場合のフラグはTRUE
	if($allviewflg){
		//テーブルに接続
		$sql = "SELECT * FROM ".$tb_name;
		$sql=$db->query($sql);
		//$result = mysqli_query($c,'SELECT * from '.$tb_name);
		//SQLクエリを実行：配列を読み込む
		if(!$sql){
			exit("そんな名前のテーブルはありませんよ？");
		}else{
			//tableTAG
			echo $tabletag0;
			//配列の終端まで繰り返し出力する
			foreach($sql as $row){
				$cp = $row['HP']+$row['DP']+$row['SP']+$row['LP']+$row['AP'];
				echo '<tr><th>'.$row['id'].'</th><th>'.$row['name'].'</th><th>'.$cp.'</th><th>'.$row['HP'].'</th><th>'.$row['DP'].'</th><th>'.$row['SP'].'</th><th>'.$row['LP'].'</th><th>'.$row['AP'].'</th><th>'.$row['FP'].'</th><th>'.$row['TP'].'</th><th>'.$row['PP'].'</th></tr>';
			}
			//一覧は見ないに設定
			$allviewflg = 0;
			//tableTAG_END
			echo '</table>';
		}
		//この項は出る
	}
	//二人分読み込むなので二回よむforで処理スべきだがいまはいいや:一回目
	$sql = 'SELECT * from '.$tb_name.' where id = '.$numb1;
	$sql=$db->query($sql);
	//指定したnumberのキャラクタのみ読み込む
	if($sql){
		//tableTAG
		echo '<br>'.$tabletag0;

		//配列の終端まで繰り返し出力する
		foreach($sql as $row){
			$cp = $row['HP']+$row['DP']+$row['SP']+$row['LP']+$row['AP'];
			echo '<tr><th>'.$row['id'].'</th><th>'.$row['name'].'</th><th>'.$cp.'</th><th>'.$row['HP'].'</th><th>'.$row['DP'].'</th><th>'.$row['SP'].'</th><th>'.$row['LP'].'</th><th>'.$row['AP'].'</th><th>'.$row['FP'].'</th><th>'.$row['TP'].'</th><th>'.$row['PP'].'</th></tr>';
		
			//一人目を変数に格納しておくよ
			$nu1=$row['id'];//number
			$na1=$row['name'];//name
			$le1=$cp;//level
			$hp1=$row['HP'];//HP
			$at1=$row['AP'];//AttackPoint
			$de1=$row['DP'];//DefencePoint
			$qu1=$row['SP'];//Quickness
			$he1=$row['TP'];//HealingPoint
			$cu1=$row['FP'];//CutePoint
			$sc1=$row['PP'];//ScaryPoint
			$lu1=$row['LP'];//LuckyPoint
			$pr1=$row['PP'];//Propaty
			$ta1=$row['FP'];//Tak
			$bo1=$row['PP'];//Bond
			$tr1=$row['TP'];//trust me
		}
		//tableTAG_END
		echo '</table>';
	}
	//二人分読み込むなので二回よむforで処理スべきだがいまはいいや:二回目
	$sql = 'SELECT * from '.$tb_name.' where id = '.$numb2;
	$sql=$db->query($sql);
	//指定したnumberのキャラクタのみ読み込む
	if($sql){
		//tableTAG
		echo '<br>'.$tabletag0;

		//配列の終端まで繰り返し出力する
		foreach($sql as $row){
			$cp = $row['HP']+$row['DP']+$row['SP']+$row['LP']+$row['AP'];
			echo '<tr><th>'.$row['id'].'</th><th>'.$row['name'].'</th><th>'.$cp.'</th><th>'.$row['HP'].'</th><th>'.$row['DP'].'</th><th>'.$row['SP'].'</th><th>'.$row['LP'].'</th><th>'.$row['AP'].'</th><th>'.$row['FP'].'</th><th>'.$row['TP'].'</th><th>'.$row['PP'].'</th></tr>';
		
			//一人目を変数に格納しておくよ
			$nu2=$row['id'];//number
			$na2=$row['name'];//name
			$le2=$cp;//level
			$hp2=$row['HP'];//HP
			$at2=$row['AP'];//AttackPoint
			$de2=$row['DP'];//DefencePoint
			$qu2=$row['SP'];//Quickness
			$he2=$row['TP'];//HealingPoint
			$cu2=$row['FP'];//CutePoint
			$sc2=$row['PP'];//ScaryPoint
			$lu2=$row['LP'];//LuckyPoint
			$pr2=$row['PP'];//Propaty
			$ta2=$row['FP'];//Tak
			$bo2=$row['PP'];//Bond
			$tr2=$row['TP'];//trust me
		}
		//tableTAG_END
		echo '</table>';
	}
	//キャラクタの詳細を見る場合はTRUE
	if($viewchar){

		//tableTAG
		echo '<br>'.$tabletag0;
		//指定したnumberのキャラクタのみ読み込む
		$sql ='SELECT * from '.$tb_name.' where id = '.$viewcharNo;
		$sql=$db->query($sql);
		if($sql){

			//配列の終端まで繰り返し出力する
			foreach($sql as $row){
				$cp = $row['HP']+$row['DP']+$row['SP']+$row['LP']+$row['AP'];
				echo '<tr><th>'.$row['id'].'</th><th>'.$row['name'].'</th><th>'.$cp.'</th><th>'.$row['HP'].'</th><th>'.$row['DP'].'</th><th>'.$row['SP'].'</th><th>'.$row['LP'].'</th><th>'.$row['AP'].'</th><th>'.$row['FP'].'</th><th>'.$row['TP'].'</th><th>'.$row['PP'].'</th></tr>';
			}
			//tableTAG_END
			echo '</table>';
		}
			
	}
	//$resultを開放します
	//mysqli_free_result($result);
	$db = NULL;
}catch(PDOException $e){
	echo "DB connect failure..." . PHP_EOL;
	echo $e->getMessage();
	exit;
}
//Mysqlを閉じます
//$close_m = mysqli_close($c);

//このあと抽出した二人：今は一人：のバトルルーチンを書く：LinuxCから移植:移植じゃなくって作りなおしじゃん！
//別にSQLを使っていればclassを使う必要ないかな？
//print rand(0,99);
//バトルの回数
$battle_loop=49;//50回で終了

//先制攻撃のフラグ
$first_attack=0;//一応FALSEで初期化

//ラッキーの抽選値
$lucky=0;//一応FALSEで初期化

//個別のラッキーフラグ
$uni_lucky1=0;//一人目一応FALSEで初期化
$uni_lucky2=0;//二人目一応FALSEで初期化

//攻撃力と防御力を変数にバックアップしておく
$at1_bk=$at1;
$de1_bk=$de1;
$at2_bk=$at2;
$de2_bk=$de2;

//まず、総合攻撃力の判定:総合攻撃力が大きい場合で尚2倍以上のあるか調べる
if($le1>$le2 && $le1>$le2*2 || $le2>$le1 && $le2>$le1*2){
	$at='1';//バトルになんない
}else{
	$at='0';
}
//バトルの有効判定：cp値で比較
if($nu1>=1 && $nu1<=11 && $nu2>=1 && $nu2<=11){
	$no_battle=0;//子どもたち同士では傷つけ合わない
}else if($at){
	$no_battle=1;//戦力差が二倍以上なので勝負になんないよ
}else{
	$no_battle=2;//バトルになるよ
}
	
if(!$no_battle){
	$msg_first='おっと！向かい合っていた二人はにっこり微笑んでいる！<br>この仲間同士では戦えない！<br>残念だが絆が強いので、'.$na1.'と'.$na2.'は戦う意志がないぞ！引き分け！<br>';
}else if($no_battle==1){
	$msg_first='これは！'.$na1.'と'.$na2.'の戦闘力に差がありすぎて勝負にならない！<br>なので初めからやり直し！<br>';
}else{
	$msg_first='おお！互いにどちらが強いか興味があるみたいだ！<br>'.$na1.'と'.$na2.'は互いに戦闘態勢に入っているぞ！<br>';
}

//ようやく戦闘に入るぜ
if($no_battle==2){
	print $msg_first;
	//戦闘ループに入る
	for($i=0;$i<=$battle_loop;$i++){
		print '【'.$i.'】<br>'.'【'.$battle_loop.'】<br>';
		if($i==$battle_loop) {
			exit("タイムアップ！！<br><br>引き分けに終わった！！<br>これ以上戦っても勝負にならない！！<br>");
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
		//二人目
		if(!$uni_lucky2){
			if($lucky2<$lu2){
				$at2=$at2*2;
				$de2=$de2*2;
				$uni_lucky2=1;
			}else if($lucky2==$lu2){
				$at2=$at2*4;
				$uni_lucky2=1;
			}else{
				$uni_lucky2=0;
				//バックアップに戻しておく
				$at2=$at2_bk;
				$de2=$de2_bk;
			}
		}else{
			$uni_lucky2=0;
			//バックアップに戻しておく
			$at2=$at2_bk;
			$de2=$de2_bk;
		}

		//どちらが先制攻撃をかけるか判定

		//素早さのユラギを抽選しておく
		$qu1q1=$qu1+rand(0,floor($qu1/3));
		$qu2q2=$qu2+rand(0,floor($qu2/3));

		//攻撃力と防御力のユラギ
		$at1a1=$at1+rand(0,floor($at1/3));
		$at2a2=$at2+rand(0,floor($at2/3));
		$de1d1=$de1+rand(0,floor($de1/3));
		$de2d2=$de2+rand(0,floor($de2/3));


		//素早さが一人目のほうが大きい場合で先制フラグが立っていないか２になっている場合
		if($qu1q1 > $qu2q2 && !$first_attack || $first_attack == 2){
			//先制攻撃フラグに１を入れる
			$first_attack = 1;
			//msgを出力
			print 'おっと！'.$na1.'が飛びかかっていく！<br>'.$na1.'の攻撃だ！！<br>';

			//攻撃がヒットするかの判定
			$bom = $qu1q1 - $qu2q2;

			//ダメージの演算をしておく
			$damage=$de2d2-$at1a1;

			//素早さの差が50%以上ならば１００%ヒットする
			if($bom>=$qu2q2/2){
				//quickOne();
				if($damage>0){
					$damage=0;
					$msg_second = $na1.'は'.$na2.'にダメージを与えられない！攻撃は効いていないぞ！<br>';
				}else{
					//攻撃力が二倍または四倍になった
					if($uni_lucky1){
						$msg_firstsecond = $na1.'は【渾身の一撃】を放った！！<br>';
					}
					//攻撃を回避できなかった場合にはダメージ０にはしない
					if($damage>=0){
						$damage=-1;
					}
					$mdamage=$damage*-1;
					$msg_second = $na2.'は'.$na1.'の攻撃を避けきれなかった！<br>'.$mdamage.'のダメージ！<br>';
				}
				//ダメージを受けた分をhpから差し引く
				$hp2 = $hp2 + $damage;

				//hp2がマイナスの場合は０と表示する
				if($hp2<1){
					$hp2=0;
				}
				//素早さの差が２５%以上５０%未満ならば７５%ヒットする
			}else if($bom>=$qu2q2/4 && $bom <$qu2q2/2){
				$b=rand(0,99);
				if($b<74){
				//quickOne();
					
					if($damage>0){
						$damage=0;
						$msg_second = $na1.'は'.$na2.'にダメージを与えられない！攻撃は効いていないぞ！<br>';
					}else{
						//攻撃力が二倍または四倍になった
						if($uni_lucky1){
							$msg_firstsecond = $na1.'は【痛恨の一撃】を放った！！<br>';
						}
						//攻撃を回避できなかった場合にはダメージ０にはしない
						if($damage>=0){
							$damage=-1;
						}
						$mdamage=$damage*-1;
						$msg_second = $na2.'は'.$na1.'の攻撃を避けきれなかった！<br>'.$mdamage.'のダメージ！<br>';
					}
					//ダメージを受けた分をhpから差し引く
					$hp2 = $hp2 + $damage;

					//hp2がマイナスの場合は０と表示する
					if($hp2<1){
						$hp2=0;
					}
				}else{
					$msg_second = $na2.'は'.$na1.'の攻撃を素早くかわした！！ダメージは無いようだ！！<br>';
				}
			//それ未満なら５０%ヒットする
			}else{
				$b=rand(0,99);
				if($b<49){
					//quickOne();
					if($damage>0){
						$damage=0;
						$msg_second = $na1.'は'.$na2.'にダメージを与えられない！攻撃は効いていないぞ！<br>';
					}else{
						//攻撃力が二倍または四倍になった
						if($uni_lucky1){
							$msg_firstsecond = $na1.'は【会心の一撃】を放った！！<br>';
						}
						//攻撃を回避できなかった場合にはダメージ０にはしない
						if($damage>=0){
							$damage=-1;
						}
						$mdamage=$damage*-1;
						$msg_second = $na2.'は'.$na1.'の攻撃を避けきれなかった！<br>'.$mdamage.'のダメージ！<br>';
					}
					//ダメージを受けた分をhpから差し引く
					$hp2 = $hp2 + $damage;

					//hp2がマイナスの場合は０と表示する
					if($hp2<1){
						$hp2=0;
					}
					
				}else{
					$msg_second = $na2.'は'.$na1.'の攻撃を素早くかわした！！ダメージは無いようだ！！<br>';
				}
			}
			if(isset($msg_firstsecond)){
				print $msg_firstsecond;		
			}
			print $msg_second;
			
			//二人のステータスを表示
			//tableTAG
			echo '<br>'.$tabletag0;
			//出力する
			echo '<tr><th>'.$nu1.'</th><th>'.$na1.'</th><th>'.$le1.'</th><th>'.$hp1.'</th><th>'.$at1.'</th><th>'.$de1.'</th><th>'.$qu1.'</th><th>'.$cu1.'</th><th>'.$sc1.'</th><th>'.$pr1.'</th><th>'.$tr1.'</th></tr>';
			//出力する
			echo '<tr><th>'.$nu2.'</th><th>'.$na2.'</th><th>'.$le2.'</th><th>'.$hp2.'</th><th>'.$at2.'</th><th>'.$de2.'</th><th>'.$qu2.'</th><th>'.$cu2.'</th><th>'.$sc2.'</th><th>'.$pr2.'</th><th>'.$tr2.'</th></tr>';
			//tableTAG_END
			echo '</table>';
			//ded
			if($hp2<1){
				exit($na2."は".$na1."に敗北してしまった。残念無念！<br><br>【Winner：".$na1."！】<br>");
			}
			//msg_firstsecondを空にしておく
			$msg_firstsecond="";
		//素早さが二人目のほうが大きい場合
		}else if($qu1<$qu2 && !$first_attack ||$first_attack==1){
			//先制攻撃フラグに2を入れる
			$first_attack = 2;
			//msgを出力
			print 'おっと！'.$na2.'が動いた！<br>'.$na2.'の攻撃だ！！<br>';

			//攻撃がヒットするかの判定
			$bom = $qu2q2 - $qu1q1;

			//ダメージの演算をしておく
			$damage=$de1d1-$at2a2;

			//素早さの差が50%以上ならば１００%ヒットする
			if($bom>=$qu1q1/2){
				//quickOne();
				if($damage>0){
					$damage=0;
					$msg_second = $na2.'は'.$na1.'にダメージを与えられない！攻撃は効いていないぞ！<br>';
				}else{
					//攻撃力が二倍または四倍になった
					if($uni_lucky2){
						$msg_firstsecond = $na2.'は【渾身の一撃】を放った！！<br>';
					}
					//攻撃を回避できなかった場合にはダメージ０にはしない
					if($damage>=0){
						$damage=-1;
					}
					$mdamage=$damage*-1;
					$msg_second = $na1.'は'.$na2.'の攻撃を避けきれなかった！<br>'.$mdamage.'のダメージ！<br>';
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
						$msg_second = $na2.'は'.$na1.'にダメージを与えられない！攻撃は効いていないぞ！<br>';
					}else{
						//攻撃力が二倍または四倍になった
						if($uni_lucky2){
							$msg_firstsecond = $na2.'は【痛恨の一撃】を放った！！<br>';
						}
						//攻撃を回避できなかった場合にはダメージ０にはしない
						if($damage>=0){
							$damage=-1;
						}
						$mdamage=$damage*-1;
						$msg_second = $na1.'は'.$na2.'の攻撃を避けきれなかった！<br>'.$mdamage.'のダメージ！<br>';
					}
					//ダメージを受けた分をhpから差し引く
					$hp1 = $hp1 + $damage;

					//hp2がマイナスの場合は０と表示する
					if($hp1<1){
						$hp1=0;
					}
				}else{
					$msg_second = $na1.'は'.$na2.'の攻撃を素早くかわした！！ダメージは無いようだ！！<br>';
				}
			//それ未満なら５０%ヒットする
			}else{
				$b=rand(0,99);
				if($b<49){
					//quickOne();
					if($damage>0){
						$damage=0;
						$msg_second = $na2.'は'.$na1.'にダメージを与えられない！攻撃は効いていないぞ！<br>';
					}else{
						//攻撃力が二倍または四倍になった
						if($uni_lucky2){
							$msg_firstsecond = $na2.'は【渾身の一撃】を放った！！<br>';
						}
						//攻撃を回避できなかった場合にはダメージ０にはしない
						if($damage>=0){
							$damage=-1;
						}
						$mdamage=$damage*-1;
						$msg_second = $na1.'は'.$na2.'の攻撃を避けきれなかった！<br>'.$mdamage.'のダメージ！<br>';
					}
					//ダメージを受けた分をhpから差し引く
					$hp1 = $hp1 + $damage;

					//hp2がマイナスの場合は０と表示する
					if($hp1<1){
						$hp1=0;
					}
					
				}else{
					$msg_second = $na1.'は'.$na2.'の攻撃を素早くかわした！！ダメージは無いようだ！！<br>';
				}
			}
			if(isset($msg_firstsecond)){
				print $msg_firstsecond;		
			}
			print $msg_second;
			
			//二人のステータスを表示
			//tableTAG
			echo '<br>'.$tabletag0;
			//出力する
			echo '<tr><th>'.$nu1.'</th><th>'.$na1.'</th><th>'.$le1.'</th><th>'.$hp1.'</th><th>'.$at1.'</th><th>'.$de1.'</th><th>'.$qu1.'</th><th>'.$cu1.'</th><th>'.$sc1.'</th><th>'.$pr1.'</th><th>'.$tr1.'</th></tr>';
			//出力する
			echo '<tr><th>'.$nu2.'</th><th>'.$na2.'</th><th>'.$le2.'</th><th>'.$hp2.'</th><th>'.$at2.'</th><th>'.$de2.'</th><th>'.$qu2.'</th><th>'.$cu2.'</th><th>'.$sc2.'</th><th>'.$pr2.'</th><th>'.$tr2.'</th></tr>';
			//tableTAG_END
			echo '</table>';
			//ded
			if($hp1<1){
				exit($na1."は".$na2."に敗北してしまった。残念無念！<br><br>【Winner：".$na2."！】<br>");
			}
			//msg_firstsecondを空にしておく
			$msg_firstsecond="";
		//素早さが同じ場合
		}else if($qu1 == $qu2){
			print("おおっ！".$na1."と".$na2."は互いに動けないでいる！どちらが先に動くのか！！<br>");
			//抽選するよ:５０％の確率
			if(rand(0, 1)) {
				//先制攻撃フラグに１を入れる
				$first_attack = 1;
				//msgを出力
				print 'おっと！'.$na1.'が飛びかかっていく！<br>'.$na1.'の攻撃だ！！<br>';

				//攻撃がヒットするかの判定
				$bom = $qu1q1 - $qu2q2;

				//ダメージの演算をしておく
				$damage=$de2d2-$at1a1;

				//素早さの差が50%以上ならば１００%ヒットする
				if($bom>=$qu2q2/2){
					//quickOne();
					if($damage>0){
						$damage=0;
						$msg_second = $na1.'は'.$na2.'にダメージを与えられない！攻撃は効いていないぞ！<br>';
					}else{
						//攻撃力が二倍または四倍になった
						if($uni_lucky1){
							$msg_firstsecond = $na1.'は【渾身の一撃】を放った！！<br>';
						}
						//攻撃を回避できなかった場合にはダメージ０にはしない
						if($damage>=0){
							$damage=-1;
						}
						$mdamage=$damage*-1;
						$msg_second = $na2.'は'.$na1.'の攻撃を避けきれなかった！<br>'.$mdamage.'のダメージ！<br>';
					}
					//ダメージを受けた分をhpから差し引く
					$hp2 = $hp2 + $damage;

					//hp2がマイナスの場合は０と表示する
					if($hp2<1){
						$hp2=0;
					}
				//素早さの差が２５%以上５０%未満ならば７５%ヒットする
				}else if($bom>=$qu2q2/4 && $bom <$qu2q2/2){
					$b=rand(0,99);
					if($b<74){
					//quickOne();
					
						if($damage>0){
							$damage=0;
							$msg_second = $na1.'は'.$na2.'にダメージを与えられない！攻撃は効いていないぞ！<br>';
						}else{
							//攻撃力が二倍または四倍になった
							if($uni_lucky1){
								$msg_firstsecond = $na1.'は【痛恨の一撃】を放った！！<br>';
							}
							//攻撃を回避できなかった場合にはダメージ０にはしない
							if($damage>=0){
								$damage=-1;
							}
							$mdamage=$damage*-1;
							$msg_second = $na2.'は'.$na1.'の攻撃を避けきれなかった！<br>'.$mdamage.'のダメージ！<br>';
						}
						//ダメージを受けた分をhpから差し引く
						$hp2 = $hp2 + $damage;

						//hp2がマイナスの場合は０と表示する
						if($hp2<1){
							$hp2=0;
						}
					}else{
						$msg_second = $na2.'は'.$na1.'の攻撃を素早くかわした！！ダメージは無いようだ！！<br>';
					}
				//それ未満なら５０%ヒットする
				}else{
					$b=rand(0,99);
					if($b<49){
						//quickOne();
						if($damage>0){
							$damage=0;
							$msg_second = $na1.'は'.$na2.'にダメージを与えられない！攻撃は効いていないぞ！<br>';
						}else{
							//攻撃力が二倍または四倍になった
							if($uni_lucky1){
								$msg_firstsecond = $na1.'は【会心の一撃】を放った！！<br>';
							}
							//攻撃を回避できなかった場合にはダメージ０にはしない
							if($damage>=0){
								$damage=-1;
							}
							$mdamage=$damage*-1;
							$msg_second = $na2.'は'.$na1.'の攻撃を避けきれなかった！<br>'.$mdamage.'のダメージ！<br>';
						}
						//ダメージを受けた分をhpから差し引く
						$hp2 = $hp2 + $damage;

						//hp2がマイナスの場合は０と表示する
						if($hp2<1){
							$hp2=0;
						}
					
					}else{
						$msg_second = $na2.'は'.$na1.'の攻撃を素早くかわした！！ダメージは無いようだ！！<br>';
					}
				}
				if(isset($msg_firstsecond)){
					print $msg_firstsecond;		
				}
				print $msg_second;
			
				//二人のステータスを表示
				//tableTAG
				echo '<br>'.$tabletag0;
				//出力する
				echo '<tr><th>'.$nu1.'</th><th>'.$na1.'</th><th>'.$le1.'</th><th>'.$hp1.'</th><th>'.$at1.'</th><th>'.$de1.'</th><th>'.$qu1.'</th><th>'.$cu1.'</th><th>'.$sc1.'</th><th>'.$pr1.'</th><th>'.$tr1.'</th></tr>';
				//出力する
				echo '<tr><th>'.$nu2.'</th><th>'.$na2.'</th><th>'.$le2.'</th><th>'.$hp2.'</th><th>'.$at2.'</th><th>'.$de2.'</th><th>'.$qu2.'</th><th>'.$cu2.'</th><th>'.$sc2.'</th><th>'.$pr2.'</th><th>'.$tr2.'</th></tr>';
				//tableTAG_END
				echo '</table>';
				//ded
				if($hp2<1){
					exit($na2."は".$na1."に敗北してしまった。残念無念！<br><br>【Winner：".$na1."！】<br>");
				}
				//msg_firstsecondを空にしておく
				$msg_firstsecond="";
		
			}else {
				//先制攻撃フラグに2を入れる
				$first_attack = 2;
				//msgを出力
				print 'おっと！'.$na2.'が動いた！<br>'.$na2.'の攻撃だ！！<br>';

				//攻撃がヒットするかの判定
				$bom = $qu2q2 - $qu1q1;

				//ダメージの演算をしておく
				$damage=$de1d1-$at2a2;

				//素早さの差が50%以上ならば１００%ヒットする
				if($bom>=$qu1q1/2){
					//quickOne();
					if($damage>0){
						$damage=0;
						$msg_second = $na2.'は'.$na1.'にダメージを与えられない！攻撃は効いていないぞ！<br>';
					}else{
						//攻撃力が二倍または四倍になった
						if($uni_lucky2){
							$msg_firstsecond = $na2.'は【渾身の一撃】を放った！！<br>';
						}
						//攻撃を回避できなかった場合にはダメージ０にはしない
						if($damage>=0){
							$damage=-1;
						}
						$mdamage=$damage*-1;
						$msg_second = $na1.'は'.$na2.'の攻撃を避けきれなかった！<br>'.$mdamage.'のダメージ！<br>';
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
							$msg_second = $na2.'は'.$na1.'にダメージを与えられない！攻撃は効いていないぞ！<br>';
						}else{
							//攻撃力が二倍または四倍になった
							if($uni_lucky2){
								$msg_firstsecond = $na2.'は【痛恨の一撃】を放った！！<br>';
							}
							//攻撃を回避できなかった場合にはダメージ０にはしない
							if($damage>=0){
								$damage=-1;
							}
							$mdamage=$damage*-1;
							$msg_second = $na1.'は'.$na2.'の攻撃を避けきれなかった！<br>'.$mdamage.'のダメージ！<br>';
						}
						//ダメージを受けた分をhpから差し引く
						$hp1 = $hp1 + $damage;

						//hp2がマイナスの場合は０と表示する
						if($hp1<1){
							$hp1=0;
						}
					}else{
						$msg_second = $na1.'は'.$na2.'の攻撃を素早くかわした！！ダメージは無いようだ！！<br>';
					}
				//それ未満なら５０%ヒットする
				}else{
					$b=rand(0,99);
					if($b<49){
						//quickOne();
						if($damage>0){
							$damage=0;
							$msg_second = $na2.'は'.$na1.'にダメージを与えられない！攻撃は効いていないぞ！<br>';
						}else{
							//攻撃力が二倍または四倍になった
							if($uni_lucky2){
								$msg_firstsecond = $na2.'は【渾身の一撃】を放った！！<br>';
							}
							//攻撃を回避できなかった場合にはダメージ０にはしない
							if($damage>=0){
								$damage=-1;
							}
							$mdamage=$damage*-1;
							$msg_second = $na1.'は'.$na2.'の攻撃を避けきれなかった！<br>'.$mdamage.'のダメージ！<br>';
						}
						//ダメージを受けた分をhpから差し引く
						$hp1 = $hp1 + $damage;

						//hp2がマイナスの場合は０と表示する
						if($hp1<1){
							$hp1=0;
						}
					
					}else{
						$msg_second = $na1.'は'.$na2.'の攻撃を素早くかわした！！ダメージは無いようだ！！<br>';
					}
				}
				if(isset($msg_firstsecond)){
					print $msg_firstsecond;		
				}
				print $msg_second;
			
				//二人のステータスを表示
				//tableTAG
				echo '<br>'.$tabletag0;
				//出力する
				echo '<tr><th>'.$nu1.'</th><th>'.$na1.'</th><th>'.$le1.'</th><th>'.$hp1.'</th><th>'.$at1.'</th><th>'.$de1.'</th><th>'.$qu1.'</th><th>'.$cu1.'</th><th>'.$sc1.'</th><th>'.$pr1.'</th><th>'.$tr1.'</th></tr>';
				//出力する
				echo '<tr><th>'.$nu2.'</th><th>'.$na2.'</th><th>'.$le2.'</th><th>'.$hp2.'</th><th>'.$at2.'</th><th>'.$de2.'</th><th>'.$qu2.'</th><th>'.$cu2.'</th><th>'.$sc2.'</th><th>'.$pr2.'</th><th>'.$tr2.'</th></tr>';
				//tableTAG_END
				echo '</table>';
				//ded
				if($hp1<1){
					exit($na1."は".$na2."に敗北してしまった。残念無念！<br><br>【Winner：".$na2."！】<br>");
				}
				//msg_firstsecondを空にしておく
				$msg_firstsecond="";
				
			}
		}
	}
}else{
	//メッセージを表示して終了
	print $msg_first;
}

//関数にまとめてみよう
function quickOne() {
	if($damage>0){
		$damage=0;
		$msg_second = $na1.'は'.$na2.'にダメージを与えられない！攻撃は効いていないぞ！<br>';
	}else{
		//攻撃力が二倍または四倍になった
		if($uni_lucky1){
			$msg_firstsecond = $na1.'は【渾身の一撃】を放った！！<br>';
		}
		//攻撃を回避できなかった場合にはダメージ０にはしない
		if($damage>=0){
			$damage=-1;
		}
		$mdamage=$damage*-1;
		$msg_second = $na2.'は'.$na1.'の攻撃を避けきれなかった！<br>'.$mdamage.'のダメージ！<br>';
	}
	//ダメージを受けた分をhpから差し引く
	$hp2 = $hp2 + $damage;

	//hp2がマイナスの場合は０と表示する
	if($hp2<1){
		$hp2=0;
	}
}
/*
if($close_m){
	print('切断に成功<br>');
}else{
	print('<br>切断出来てませんよ！<br>');
}*/

?>

