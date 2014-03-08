<html>
	<head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8"><title>LifeGame</title></head>
	<body>
<?php
	$cardNum = 30;
	$cards = array(
		"1" => 1,
		"2" => 1,
		"3" => 1,
		"4" => 1,
		"5" => 2,
		"7" => 2,
		"9" => 1,
		"11" =>  2,
		"13" => 1,
		"14" => 1,
		"15" => 1,
		"17" => 1,
		"poison" => 3,
		"bom" => 3,
		"rock" => 3,
		"snake" => 3,
		"scorpion" => 3,
	);
	
	class Game{
		function __construct($peopleNum){
			global $cards;
			$this->cards = $cards;
			$this->peopleNum = $peopleNum;
			$this->outCards = array();
			$this->putDiamonds = array();
			$aveAcq = 0;
			$i = 0;
			foreach($cards as $key => $val){
				if(is_numeric($key)){
					$i += 1;
					$aveAcq += (int)$key * $val / $this->peopleNum;
				}
			}
			$aveAcq = $aveAcq / $i;
			$this->AVERAGE = 13 * (int)($aveAcq / 2);
		}
		
		public function freq($backNum = 0){
			$aveAcq = 0;
			$i = 0;
			foreach($this->cards as $key => $val){
				if(is_numeric($key)){
					$i += $val;
					$aveAcq += (int)($key / $this->peopleNum * $val);
				}
			}
			$aveAcq = (int)($aveAcq / $i);
			
			$trblFreq = 0;
			$i = 0;
			foreach($this->outCards as $key => $val){
				$i += 1;
				if(!is_numeric($key)){
					$trblFreq += 2;
				}
			}
			$trblFreq = $trblFreq / (30 - $i);
			
			return [$aveAcq, $trblFreq];
		}
		
		public function stepNext($card){
			$this->cards[$card] -= 1;
			if(isset($this->outCards[$card])){
				$this->outCards[$card] += 1;
			} else {
				$this->outCards[$card] = 1;
			}
			if(!is_numeric($card) && ($this->outCards[$card] == 2)) $getNum = false;
			elseif(is_numeric($card)){
				$getNum = (int)((int)$card / $this->peopleNum);
				$this->putDiamond[] = (int)$card - $getNum * $this->peopleNum;
			} else {
				$getNum = true;
			}
			return $getNum;
		}
				
		public function backAction($backNames ,$sim=false){
			$getNum = 0;
			if(!$sim) $this->peopleNum -= count($backNames);
			foreach($this->putDiamonds as $key => $val){
				$getNum += (int)($val / count($backNames));
				if(!$sim) $this->putDiamonds[$key] -= $getNum;
			}
			return $getNum;
		}
	}
	
	class Human{
		function __construct($getDia=0, $judgeParam=0.5){
			$this->diamond = 0;
			$this->getDia = $getDia;
			$this->judgeParam = $judgeParam;
		}
				
		public function judge($freq){
			if($freq[1] >= $this->judgeParam) return false;
			else return true;
		}
		
		public function changeParam($calcParam){
			$this->judgeParam = $calcParam;
		}
	}
	
	class Me extends Human{
		public function brain($othsCls,$freq,$GameObj,$cnt){
			$backNum = 0;
			foreach($othsCls as $key => $othCls){
				if (!$othCls->judge($freq)) {
					$backNum += 1;
				}
			}
			$backGetNum = $GameObj->backAction(array($backNum+1), true);
			$freq = $GameObj->freq($backNum);
			$goGetNum = $freq[0];
			$trblFreq = $freq[1];
			if($trblFreq == 0)$breakMe = false;
			elseif(($this->diamond + $backNum)>$GameObj->AVERAGE)$breakMe = true;
			elseif($backGetNum >= $goGetNum*(9-$cnt))$breakMe = true;
			else $breakMe = false;
			return $breakMe;
		}
	}

	session_start();

	if(isset($_GET["init"])){
		//receive
		$names = split(",", $_POST['names']);
		$players = count($names) + 1;
		echo "プレイヤー $players 人<br>";
		
		//initialize of turn
		$breakMe = false;
		$breaked = false;
		$cnt = 1;
		$freq = 0;
		$GameObj = new Game($players);
		if(isset($_SESSION['Me'])) $MeObj = $_SESSION['Me'];
		else $MeObj = new Me();
		$MeObj->getDia += $MeObj->diamond;
		$MeObj->diamond = 0;
		$OthsObj = array();
		foreach ($names as $key => $name) {
			if(isset($_SESSION['Other'][$name])) $OthsObj[$name] = $_SESSION['Other'][$name];
			if(isset($OthsObj[$name])) $OthsObj[$name]->getDia += $OthsObj[$name]->diamond;
			else if(isset($_SESSION['Next'][$name])){
				$OthsObj[$name] = $_SESSION['Next'][$name];
				$OthsObj[$name]->getDia += $OthsObj[$name]->diamond;
			}
			else $OthsObj[$name] = new Human();
			$OthsObj[$name]->diamond = 0;
		}

		echo "<form method='post' action='Diamond.php?cnt=$cnt'><br>";
		
	} else if(isset($_POST['card'])){
		$card = $_POST['card'];
		$cnt = $_GET['cnt'];
		$MeObj = $_SESSION['Me'];
		$OthsObj = $_SESSION['Other'];
		$GameObj = $_SESSION['Game'];
		$NextObj = array();
		$breakMe = $_SESSION['breakMe'];
		$breaked = $_SESSION['breaked'];
		$freq = $_SESSION['freq'];

		if(!empty($_POST['backNames'])){
			$backNames = split(",", $_POST['backNames']);
			if($breakMe && !$breaked) $backNames[] = 'Me';
			$getNum = $GameObj->backAction($backNames);
			foreach ($backNames as $key => $backName){
				if ($backName === 'Me') {
					$MeObj->diamond += $getNum;
					$breaked = true;
				}
				else {
					$OthsObj[$backName]->diamond += $getNum;
					$NextObj[$backName] = $OthsObj[$backName];
					$NextObj[$backName]->judgeParam = $freq[1];
					unset($OthsObj[$backName]);
				}
			}
		} else if($breakMe && !$breaked) {
			$getNum = $GameObj->backAction(['Me']);
			$MeObj->diamond += $getNum;
		}		

		$getNum = $GameObj->stepNext($card);
		if(is_numeric($getNum)) {
			foreach ($OthsObj as $name => $OthObj) $OthObj->diamond += $getNum;
			if (!$breakMe) $MeObj->diamond += $getNum;
		} else if(!$getNum) {
			foreach ($OthsObj as $name => $OthObj) $OthObj->diamond = 0;
			if (!$breakMe) $MeObj->diamond = 0;
			echo '<h1>Game over!!</h1>';
		}
		$freq = $GameObj->freq();

		if (!$breakMe) $breakMe = $MeObj->brain($OthsObj,$freq,$GameObj,$cnt);
		$cnt += 1;		

		if ($breakMe) echo "<br>Back<br>";
		else echo "<br>Go<br>";
		var_dump($freq);
		echo "Me $MeObj->diamond $MeObj->getDia ";
		foreach($OthsObj as $key => $OthObj) echo "$key $OthObj->diamond $OthObj->getDia ";
		echo "<br>";
		
		echo "<br><form method='post' action='Diamond.php?init=true'>";
		echo "名前<input type='text' name='names'><br>";
		echo "<input type='submit' value='new game'></form>";
		echo "<form method='post' action='Diamond.php?cnt=$cnt'><br>";
		echo "戻った人の名前<input type='text' name='backNames'><br>";
	} else {
		session_destroy();
		echo "<br><form method='post' action='Diamond.php?init=true'>";
		echo "名前<input type='text' name='names'><br>";
		echo "<input type='submit' value='new game'>";
	}
	
	echo "
		<p>出たカード</p>
		<input type='radio' name='card' value=1>1 <input type='radio' name='card' value=2>2 <input type='radio' name='card' value=3>3 <input type='radio' name='card' value=4>4 <input type='radio' name='card' value=5>5 <input type='radio' name='card' value=7>7<br>
		<input type='radio' name='card' value=9>9 <input type='radio' name='card' value=11>11 <input type='radio' name='card' value=13>13 <input type='radio' name='card' value=14>14 <input type='radio' name='card' value=15>15 <input type='radio' name='card' value=17>17<br>
		<input type='radio' name='card' value='poison'>毒 <input type='radio' name='card' value='bom'>爆発 <input type='radio' name='card' value='rock'>落盤 <input type='radio' name='card' value='snake'>蛇 <input type='radio' name='card' value='scorpion'>サソリ<br>
		<input type='submit' value='next turn'>
		</form>
	";
	echo "<br><form method='post' action='Diamond.php'><input type='submit' value='end'></form>";
	if (isset($MeObj)) $_SESSION['Me'] = $MeObj;
	if (isset($OthsObj)) $_SESSION['Other'] = $OthsObj;
	if (!empty($NextObj)) $_SESSION['Next'] = $NextObj;
	if (isset($GameObj)) $_SESSION['Game'] = $GameObj;
	if (isset($breakMe)) $_SESSION['breakMe'] = $breakMe;
	if (isset($breaked)) $_SESSION['breaked'] = $breaked;
	if (isset($freq)) $_SESSION['freq'] = $freq;
?>
</body>
</html>