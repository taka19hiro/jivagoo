<?php
/***************************************************************************
	BATTLE
***************************************************************************/

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
/***************************/
$b_ghost = 20;//渡されるバトル相手のおばけ
$p_array = array(1,2,3,6,9) ;//渡されるパーティの配列数値はid

//一覧を見るか見ないかのフラグ:今は見るになっている
$allviewflg = 1;
//↓は抽出キャラクタ2名を指定
$numb1 =$b_ghost;//渡されるバトル相手のおばけ
$numb2 = 3;//これはうらら
//これは抽出したキャラクタ詳細を見るか見ないかのフラグ：見るに設定
$viewchar = 1;
//見るキャラを指定
$viewcharNo = 77;//横地監物に指定
//count init
$count = 0;

//tabletagHeader
$tabletag0 =  '<table border=1 width=800>
			<tr><th>id</th><th>Name</th><th>cp</th><th>HP</th><th>DP</th><th>SP</th><th>LP</th><th>AP</th><th>FP</th><th>TP</th><th>PP</th></tr>';

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
			$hp1=$row['HP']*4;//HP
			$at1=$row['AP'];//AttackPoint
			$de1=$row['DP'];//DefencePoint
			$qu1=$row['SP'];//Quickness
			$he1=$row['TP'];//HealingPoint
			$cu1=$row['FP'];//CutePoint
			$sc1=$row['PP'];//ScaryPoint
			$lu1=$row['LP'];//LuckyPoint
		}
		//tableTAG_END
		echo '</table>';
	}
	//パーティの数だけ読み込む
	foreach($p_array as $numb){
		if($numb){
			//二人分読み込むなので二回よむforで処理スべきだがいまはいいや:二回目
			$sql = 'SELECT * from '.$tb_name.' where id = '.$numb;
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
					$nu2[$count]=$row['id'];//number
					$na2[$count]=$row['name'];//name
					$le2[$count]=$cp;//level
					$hp2[$count]=$row['HP'];//HP
					$at2[$count]=$row['AP'];//AttackPoint
					$de2[$count]=$row['DP'];//DefencePoint
					$qu2[$count]=$row['SP'];//Quickness
					$he2[$count]=$row['TP'];//HealingPoint
					$cu2[$count]=$row['FP'];//CutePoint
					$sc2[$count]=$row['PP'];//ScaryPoint
					$lu2[$count]=$row['LP'];//LuckyPoint

					
					$at2_bk[$count]=$at2[$count];
					$de2_bk[$count]=$de2[$count];
				}
				//tableTAG_END
				echo '</table>';
			}
			$count++;
		}
	}
	//close database
	$db = NULL;
}catch(PDOException $e){
	echo "DB connect failure..." . PHP_EOL;
	echo $e->getMessage();
	exit;
}
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

//ようやく戦闘に入るぜ
if(true){
	print $msg_first;
	//戦闘ループに入る
	for($i=0;$i<=$battle_loop;$i++){
		print '【第'.$i.'回戦】<br>【回戦制限'.$battle_loop.'ターン】<br>';
		if($i==$battle_loop) {
			exit("双方が疲弊してしまった。おばけはふらふらと逃げて行った。<br>");
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
		//print 'おばけのラッキー初期化までOK<br>';
		//count init
		$ii=0;
		foreach($p_array as $pt){
			if($pt){
				//二人目
				if(!$uni_lucky2){
					if($lucky2<$lu2[$ii]){
						$at2[$ii]=$at2[$ii]*2;
						$de2[$ii]=$de2[$ii]*2;
						$uni_lucky2=1;
					}else if($lucky2==$lu2[$ii]){
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
				$qu2q2[$ii]=$qu2[$ii]+rand(0,floor($qu2[$ii]/3));
				$at2a2[$ii]=$at2[$ii]+rand(0,floor($at2[$ii]/3));
				$de2d2[$ii]=$de2[$ii]+rand(0,floor($de2[$ii]/3));
				$ii++;
			}
		}
		//どちらが先制攻撃をかけるか判定

		//素早さのユラギを抽選しておく
		$qu1q1=$qu1+rand(0,floor($qu1/3));
		//$qu2q2=$qu2+rand(0,floor($qu2/3));

		//攻撃力と防御力のユラギ
		$at1a1=$at1+rand(0,floor($at1/3));
		//$at2a2=$at2+rand(0,floor($at2/3));
		$de1d1=$de1+rand(0,floor($de1/3));
		//$de2d2=$de2+rand(0,floor($de2/3));

		//count init
		$c=0;
		//lose party
		$p=0;
		//print 'ここまでOK';
		foreach($p_array as $id){
			if($id){
				if($hp2[$c]!=0){
					//素早さが一人目のほうが大きい場合で先制フラグが立っていないか２になっている場合
					if($qu1q1 > $qu2q2[$c] && !$first_attack || $first_attack == 2){
						//先制攻撃フラグに１を入れる
						$first_attack = 1;
						//msgを出力
						print $na1.'が飛びかかっていく！<br>'.$na1.'の攻撃<br>';

						//攻撃がヒットするかの判定
						$bom = $qu1q1 - $qu2q2[$c];

						//ダメージの演算をしておく
						$damage=$de2d2[$c] - $at1a1;

						//素早さの差が50%以上ならば１００%ヒットする
						if($bom>=$qu2q2[$c] / 2){
							//quickOne();
							if($damage>0){
								$damage=0;
								$msg_second = $na1.'は'.$na2[$c].'にダメージを与えられない！<br>';
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
								$msg_second = $na2[$c].'は'.$na1.'の攻撃を避けきれなかった！<br>'.$mdamage.'のダメージ！<br>';
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
									$msg_second = $na1.'は'.$na2[$c].'にダメージを与えられない！<br>';
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
									$msg_second = $na2[$c].'は'.$na1.'の攻撃を避けきれなかった！<br>'.$mdamage.'のダメージ！<br>';
								}
								//ダメージを受けた分をhpから差し引く
								$hp2[$c] = $hp2[$c] + $damage;

								//hp2がマイナスの場合は０と表示する
								if($hp2[$c]<1){
									$hp2[$c]=0;
								}
							}else{
								$msg_second = $na2[$c].'は'.$na1.'の攻撃を素早くかわした！<br>';
							}
						//それ未満なら５０%ヒットする
						}else{
							$b=rand(0,99);
							if($b<49){
								//quickOne();
								if($damage>0){
									$damage=0;
									$msg_second = $na1.'は'.$na2[$c].'にダメージを与えられない！<br>';
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
									$msg_second = $na2[$c].'は'.$na1.'の攻撃を避けきれなかった！<br>'.$mdamage.'のダメージ！<br>';
								}
								//ダメージを受けた分をhpから差し引く
								$hp2[$c] = $hp2[$c] + $damage;

								//hp2がマイナスの場合は０と表示する
								if($hp2[$c]<1){
									$hp2[$c]=0;
								}
								
							}else{
								$msg_second = $na2[$c].'は'.$na1.'の攻撃を素早くかわした！<br>';
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
						echo '<tr><th>'.$nu1.'</th><th>'.$na1.'</th><th>'.$le1.'</th><th>'.$hp1.'</th><th>'.$de1.'</th><th>'.$qu1.'</th><th>'.$lu1.'</th><th>'.$at1.'</th><th>'.$cu1.'</th><th>'.$he1.'</th><th>'.$sc1.'</th></tr>';
						//出力する
						echo '<tr><th>'.$nu2[$c].'</th><th>'.$na2[$c].'</th><th>'.$le2[$c].'</th><th>'.$hp2[$c].'</th><th>'.$de2[$c].'</th><th>'.$qu2[$c].'</th><th>'.$lu2[$c].'</th><th>'.$at2[$c].'</th><th>'.$cu2[$c].'</th><th>'.$he2[$c].'</th><th>'.$sc2[$c].'</th></tr>';
						//tableTAG_END
						echo '</table>';
						//ded
						if($hp2[$c]<1){
							print ($na2[$c]."は".$na1."に敗北してしまった。<br>");
							$p++;
						}
						//msg_firstsecondを空にしておく
						$msg_firstsecond="";
					//素早さが二人目のほうが大きい場合
					}else if($qu1<$qu2[$c] && !$first_attack ||$first_attack==1){
						//先制攻撃フラグに2を入れる
						$first_attack = 2;
						//msgを出力
						print $na2[$c].'が動いた！<br>'.$na2[$c].'の攻撃！<br>';

						//攻撃がヒットするかの判定
						$bom = $qu2q2[$c] - $qu1q1;

						//ダメージの演算をしておく
						$damage=$de1d1-$at2a2[$c];

						//素早さの差が50%以上ならば１００%ヒットする
						if($bom>=$qu1q1/2){
							//quickOne();
							if($damage>0){
								$damage=0;
								$msg_second = $na2[$c].'は'.$na1.'にダメージを与えられない！<br>';
							}else{
								//攻撃力が二倍または四倍になった
								if($uni_lucky2){
									$msg_firstsecond = $na2[$c].'は【渾身の一撃】を放った！！<br>';
								}
								//攻撃を回避できなかった場合にはダメージ０にはしない
								if($damage>=0){
									$damage=-1;
								}
								$mdamage=$damage*-1;
								$msg_second = $na1.'は'.$na2[$c].'の攻撃を避けきれなかった！<br>'.$mdamage.'のダメージ！<br>';
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
									$msg_second = $na2[$c].'は'.$na1.'にダメージを与えられない！<br>';
								}else{
									//攻撃力が二倍または四倍になった
									if($uni_lucky2){
										$msg_firstsecond = $na2[$c].'は【痛恨の一撃】を放った！！<br>';
									}
									//攻撃を回避できなかった場合にはダメージ０にはしない
									if($damage>=0){
										$damage=-1;
									}
									$mdamage=$damage*-1;
									$msg_second = $na1.'は'.$na2[$c].'の攻撃を避けきれなかった！<br>'.$mdamage.'のダメージ！<br>';
								}
								//ダメージを受けた分をhpから差し引く
								$hp1 = $hp1 + $damage;

								//hp2がマイナスの場合は０と表示する
								if($hp1<1){
									$hp1=0;
								}
							}else{
								$msg_second = $na1.'は'.$na2[$c].'の攻撃を素早くかわした！<br>';
							}
						//それ未満なら５０%ヒットする
						}else{
							$b=rand(0,99);
							if($b<49){
								//quickOne();
								if($damage>0){
									$damage=0;
									$msg_second = $na2[$c].'は'.$na1.'にダメージを与えられない！<br>';
								}else{
									//攻撃力が二倍または四倍になった
									if($uni_lucky2){
										$msg_firstsecond = $na2[$c].'は【渾身の一撃】を放った！！<br>';
									}
									//攻撃を回避できなかった場合にはダメージ０にはしない
									if($damage>=0){
										$damage=-1;
									}
									$mdamage=$damage*-1;
									$msg_second = $na1.'は'.$na2[$c].'の攻撃を避けきれなかった！<br>'.$mdamage.'のダメージ！<br>';
								}
								//ダメージを受けた分をhpから差し引く
								$hp1 = $hp1 + $damage;

								//hp2がマイナスの場合は０と表示する
								if($hp1<1){
									$hp1=0;
								}
								
							}else{
								$msg_second = $na1.'は'.$na2[$c].'の攻撃を素早くかわした！<br>';
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
						echo '<tr><th>'.$nu1.'</th><th>'.$na1.'</th><th>'.$le1.'</th><th>'.$hp1.'</th><th>'.$de1.'</th><th>'.$qu1.'</th><th>'.$lu1.'</th><th>'.$at1.'</th><th>'.$cu1.'</th><th>'.$he1.'</th><th>'.$sc1.'</th></tr>';
						//出力する
						echo '<tr><th>'.$nu2[$c].'</th><th>'.$na2[$c].'</th><th>'.$le2[$c].'</th><th>'.$hp2[$c].'</th><th>'.$de2[$c].'</th><th>'.$qu2[$c].'</th><th>'.$lu2[$c].'</th><th>'.$at2[$c].'</th><th>'.$cu2[$c].'</th><th>'.$he2[$c].'</th><th>'.$sc2[$c].'</th></tr>';
						//tableTAG_END
						echo '</table>';
						//ded
						if($hp1<1){
							exit($na1."は".$na2[$c]."に敗北してしまった。<br>");
						}
						//msg_firstsecondを空にしておく
						$msg_firstsecond="";
					//素早さが同じ場合
					}else if($qu1 == $qu2[$c]){
						print($na1."と".$na2[$c]."は互いに動けないでいる！<br>");
						//抽選するよ:５０％の確率
						if(rand(0, 1)) {
							//先制攻撃フラグに１を入れる
							$first_attack = 1;
							//msgを出力
							print $na1.'が飛びかかっていく！<br>'.$na1.'の攻撃だ！<br>';

							//攻撃がヒットするかの判定
							$bom = $qu1q1 - $qu2q2[$c];

							//ダメージの演算をしておく
							$damage=$de2d2[$c] - $at1a1;

							//素早さの差が50%以上ならば１００%ヒットする
							if($bom>=$qu2q2[$c] / 2){
								//quickOne();
								if($damage>0){
									$damage=0;
									$msg_second = $na1.'は'.$na2[$c].'にダメージを与えられない！<br>';
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
									$msg_second = $na2[$c].'は'.$na1.'の攻撃を避けきれなかった！<br>'.$mdamage.'のダメージ！<br>';
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
										$msg_second = $na1.'は'.$na2[$c].'にダメージを与えられない！<br>';
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
										$msg_second = $na2[$c].'は'.$na1.'の攻撃を避けきれなかった！<br>'.$mdamage.'のダメージ！<br>';
									}
									//ダメージを受けた分をhpから差し引く
									$hp2[$c] = $hp2[$c] + $damage;

									//hp2がマイナスの場合は０と表示する
									if($hp2[$c]<1){
										$hp2[$c]=0;
									}
								}else{
									$msg_second = $na2[$c].'は'.$na1.'の攻撃を素早くかわした！<br>';
								}
							//それ未満なら５０%ヒットする
							}else{
								$b=rand(0,99);
								if($b<49){
									//quickOne();
									if($damage>0){
										$damage=0;
										$msg_second = $na1.'は'.$na2[$c].'にダメージを与えられない！<br>';
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
										$msg_second = $na2[$c].'は'.$na1.'の攻撃を避けきれなかった！<br>'.$mdamage.'のダメージ！<br>';
									}
									//ダメージを受けた分をhpから差し引く
									$hp2[$c] = $hp2[$c] + $damage;

									//hp2がマイナスの場合は０と表示する
									if($hp2[$c]<1){
										$hp2[$c]=0;
									}
								
								}else{
									$msg_second = $na2[$c].'は'.$na1.'の攻撃を素早くかわした！<br>';
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
							echo '<tr><th>'.$nu1.'</th><th>'.$na1.'</th><th>'.$le1.'</th><th>'.$hp1.'</th><th>'.$de1.'</th><th>'.$qu1.'</th><th>'.$lu1.'</th><th>'.$at1.'</th><th>'.$cu1.'</th><th>'.$he1.'</th><th>'.$sc1.'</th></tr>';
							//出力する
							echo '<tr><th>'.$nu2[$c].'</th><th>'.$na2[$c].'</th><th>'.$le2[$c].'</th><th>'.$hp2[$c].'</th><th>'.$de2[$c].'</th><th>'.$qu2[$c].'</th><th>'.$lu2[$c].'</th><th>'.$at2[$c].'</th><th>'.$cu2[$c].'</th><th>'.$he2[$c].'</th><th>'.$sc2[$c].'</th></tr>';
							//tableTAG_END
							echo '</table>';
							//ded
							if($hp2<1){
								print ($na2[$c]."は".$na1."に敗北してしまった。<br>");
								$p++;
							}
							//msg_firstsecondを空にしておく
							$msg_firstsecond="";
					
						}else {
							//先制攻撃フラグに2を入れる
							$first_attack = 2;
							//msgを出力
							print $na2[$c].'が動いた！<br>'.$na2[$c].'の攻撃！<br>';

							//攻撃がヒットするかの判定
							$bom = $qu2q2[$c] - $qu1q1;

							//ダメージの演算をしておく
							$damage=$de1d1-$at2a2[$c];

							//素早さの差が50%以上ならば１００%ヒットする
							if($bom>=$qu1q1/2){
								//quickOne();
								if($damage>0){
									$damage=0;
									$msg_second = $na2[$c].'は'.$na1.'にダメージを与えられない！<br>';
								}else{
									//攻撃力が二倍または四倍になった
									if($uni_lucky2){
										$msg_firstsecond = $na2[$c].'は【渾身の一撃】を放った！！<br>';
									}
									//攻撃を回避できなかった場合にはダメージ０にはしない
									if($damage>=0){
										$damage=-1;
									}
									$mdamage=$damage*-1;
									$msg_second = $na1.'は'.$na2[$c].'の攻撃を避けきれなかった！<br>'.$mdamage.'のダメージ！<br>';
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
										$msg_second = $na2[$c].'は'.$na1.'にダメージを与えられない！<br>';
									}else{
										//攻撃力が二倍または四倍になった
										if($uni_lucky2){
											$msg_firstsecond = $na2[$c].'は【痛恨の一撃】を放った！！<br>';
										}
										//攻撃を回避できなかった場合にはダメージ０にはしない
										if($damage>=0){
											$damage=-1;
										}
										$mdamage=$damage*-1;
										$msg_second = $na1.'は'.$na2[$c].'の攻撃を避けきれなかった！<br>'.$mdamage.'のダメージ！<br>';
									}
									//ダメージを受けた分をhpから差し引く
									$hp1 = $hp1 + $damage;

									//hp2がマイナスの場合は０と表示する
									if($hp1<1){
										$hp1=0;
									}
								}else{
									$msg_second = $na1.'は'.$na2[$c].'の攻撃を素早くかわした！<br>';
								}
							//それ未満なら５０%ヒットする
							}else{
								$b=rand(0,99);
								if($b<49){
									//quickOne();
									if($damage>0){
										$damage=0;
										$msg_second = $na2[$c].'は'.$na1.'にダメージを与えられない！<br>';
									}else{
										//攻撃力が二倍または四倍になった
										if($uni_lucky2){
											$msg_firstsecond = $na2[$c].'は【渾身の一撃】を放った！！<br>';
										}
										//攻撃を回避できなかった場合にはダメージ０にはしない
										if($damage>=0){
											$damage=-1;
										}
										$mdamage=$damage*-1;
										$msg_second = $na1.'は'.$na2[$c].'の攻撃を避けきれなかった！<br>'.$mdamage.'のダメージ！<br>';
									}
									//ダメージを受けた分をhpから差し引く
									$hp1 = $hp1 + $damage;

									//hp2がマイナスの場合は０と表示する
									if($hp1<1){
										$hp1=0;
									}
								
								}else{
									$msg_second = $na1.'は'.$na2[$c].'の攻撃を素早くかわした！<br>';
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
							echo '<tr><th>'.$nu1.'</th><th>'.$na1.'</th><th>'.$le1.'</th><th>'.$hp1.'</th><th>'.$de1.'</th><th>'.$qu1.'</th><th>'.$lu1.'</th><th>'.$at1.'</th><th>'.$cu1.'</th><th>'.$he1.'</th><th>'.$sc1.'</th></tr>';
							//出力する
							echo '<tr><th>'.$nu2[$c].'</th><th>'.$na2[$c].'</th><th>'.$le2[$c].'</th><th>'.$hp2[$c].'</th><th>'.$de2[$c].'</th><th>'.$qu2[$c].'</th><th>'.$lu2[$c].'</th><th>'.$at2[$c].'</th><th>'.$cu2[$c].'</th><th>'.$he2[$c].'</th><th>'.$sc2[$c].'</th></tr>';
							//tableTAG_END
							echo '</table>';
							//ded
							if($hp1<1){
								exit($na1."は".$na2[$c]."に敗北してしまった。<br>");
							}
							//msg_firstsecondを空にしておく
							$msg_firstsecond="";
							
						}
					}
				}else{
					print 'もう'.$na2[$c].'は力尽きている...<br>';
				}
					$c++;
			}
			if($count==$p){
				exit('パーティは全滅してしまった.....<br>');
			}
		}
	}
}else{
	//メッセージを表示して終了
	print $msg_first;
}