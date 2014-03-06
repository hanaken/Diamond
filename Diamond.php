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
					$point[$key] += (int)$key * $val / $this->peopleNum;
				}
			}
			$aveAcq = $aveAcq / $i;
			$this->AVERAGE = 13 * $aveAcq / 2;
		}
		
		public function freq($backNum = 0){
			$aveAcq = 0;
			foreach($this->cards as $key => $val){
				if(is_numeric($key)){
					$i += $val;
					$aveAcq += (int)$key / $this->peopleNum * $val;
				}
			}
			$aveAcq = $aveAcq / $i;
			
			$trblFreq = 0;
			$i = 0;
			foreach($this->outCards as $key => $val){
				$i += 1;
				if(!is_numeric($key)){
					$trblFreq += 2;
				}
			}
			$trblFreq = $trblFreq / (30 - $i);
		}
		
		public function stepNext($card){
			$this->cards[$card] -= 1;
			if(isset($this->outCards[$card])){
				$this->outCards[$card] += 1;
			} else {
				$this->outCards[$card] = 1;
			}
			if(is_numeric($card)){
				$getNum = false;
			}
			elseif(!is_numeric($card)){
				$getNum = (int)$card / $this->peopleNum;
				$this->putDiamond[] = (int)card - $getNum * $this->peopleNum;
			} else {
				$getNum = true;
			}
			return getNum;
		}
				
		public function backAction($backNames){
			$getNum = 0;
			$this->peopleNum -= count($backNames);
			foreach($this->putDiamonds as $key => $val){
				$getNum += $val / count($backNames);
			}
			return $getNum;
		}
	}
	
	class Human{
		function __construct($getDia=0, $judgeParam=0.5){
			$this->diamond = 0;
			$this->getDia = getDia;
			$this->judgeParam = $judgeParam;
		}
		
		//Fix me!!
		//いらない
		public function addDiamond($num){
			$this->diamonds += $num;
		}
		
		public function judge($freq){
			if(freq[1] >= $this->judgeParam) return false;
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
				if (!$othCls.judge($freq)) {
					$backNum += 1;
				}
			}
			$backGetNum = $GameObj.backAction(array($backNum+1));
			$freq = $GameObj.freq($backNum);
			$goGetNum = $freq[0];
			$trblFreq = $freq[1];
			if($trblFreq == 0)$breakMe = false;
			elseif(($this->diamonds + $backNum)>GameObj.AVARAGE)$breakMe = true;
			elseif($backGetNum >= $goGetNum*(9-$cnt))$breakMe = true;
			else $breakMe = false;
			return $breakMe;
		}
	}

	session_start();
	echo "<form method='post' action='Daimond.php?cnt=$cnt'><br>"

	if(isset($_GET["init"])){
		//receive
		$names = split(",", $_POST['names']);
		$players = count($name);
		echo "プレイヤー$players人<br>";
		
		//initialize of turn
		$cnt = 1;
		$GameObj = new Game($players);
		$MeObj = $_POST['Me'];
		$MeObj->getDia += $MeObj->diamond;
		$MeObj->diamond = 0;
		$OthsObj = $_POST['Other'];
		for ($names as $key => $name) {
			if(isset($OthsObj[$name])) $OthsObj[$name]->getDia += $OthsObj[$name]->diamond;
			else {
				$OthsObj[$name] = $_POST['Next'][$name];
				$OthsObj[$name]->getDia += $OthsObj[$name]->diamond;
			}
			$OthsObj[$name]->diamond = 0;
		}
		
	} else if(isset($_POST['card'])){
		$NextObj = array();
		$breakMe = $_SESSION['breakMe'];
		if(!empty($_POST['backNames'])){
			$backNames = split(",", $_POST['backNames']);
			if($breakMe) $backNames[] = 'Me';
			$getNum = $GameObj->backAction($backNames);
			for($backNames as $key => $backName){
				if ($backName === 'Me') $MeObj->diamond += $getNum;
				else {
					$OthsObj[$backName] += $getNum;
					$NextObj[$backName] = $OthsObj[$backName];
					unset($OthsObj[$backName]);
				}
			}
		} else if($breakMe) {
			$getNum = $GameObj->backAction(['Me']);
			$MeObj->diamond += $getNum;
		}
		$getNum = $GameObj->stepNext(card);
		if(is_numeric($getNum){
			for ($OthsObj as $name => $OthObj) $OthObj->diamond += $getNum;
			if (!$breakMe) $MeObj->diamond += $getNum;
		} else if(!$getNum) {
			for ($OthsObj as $name => $OthObj) $OthObj->diamond = 0;
			if (!$breakMe) $MeObj->diamond = 0;
			echo '<h1>Game over!!</h1>';
		}
		$freq = $GameObj->freq();
		$breakMe = $MeObj->brain($OthsObj,$freq,$GameObj,$cnt);
		echo "戻った人の名前<input type='text' name='backNames'><br>";
		
		$cnt++;
	} else {
		echo "名前<input type='text' name='names'><br>";
	}
	
	echo "
		<p>出たカード</p>
		<input type='radio' name='card' value=1><input type='radio' name='card' value=2><input type='radio' name='card' value=3><br>
		<input type='radio' name='card' value=4><input type='radio' name='card' value=5><input type='radio' name='card' value=7><br>
		<input type='radio' name='card' value=9><input type='radio' name='card' value=11><input type='radio' name='card' value=13><br>
		<input type='radio' name='card' value=14><input type='radio' name='card' value=15><input type='radio' name='card' value=17><br>
		<input type='radio' name='card' value='poison'><input type='radio' name='card' value='bom'><input type='radio' name='card' value='rock'><br>
		<input type='radio' name='card' value='snake'><input type='radio' name='card' value='scorpion'><br>
		<input type='submit' name='next turn'>
		</form>
	";
	echo "<form method='post' action='Diamond.php?init=true'><input type='submit' name='next game'><br>";
	echo "<br><form method='post' action='Diamond.php'><input type='submit' name='new game'>";
	$_SESSION['Me'] = $MeObj;
	$_SESSION['Other'] = $OthsObj;
	if (!empty($NextObj)) $_SESSION['Next'] = $NextObj;
	$_SESSION['Game'] = $GameObj;
	$_SESSION['breakMe'] = $breakMe;
?>
</body>
</html>