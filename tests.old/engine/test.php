<?php
class TestResult
{
	public $TestName;
	public $Reason;
	public $Exception;
	public $Success = true;
	public $StackTrace = array();
	public $Dump="";
}

class FixtureResult
{
	public $TestClass;
	public $Results = array();
	public $Reason;
	public $Exception;
	public $Success = true;
	public $StackTrace = array();
}

class TestException extends Exception 
{ 
	public function TestException($reason="")
	{
		$this->Reason = $reason;
	}
	public $Reason;
}

class Test
{
	public static $TestOnly="";
	
	static function _printArray($array,$start="",$end="")
	{
		for($i=0;$i<sizeof($array);$i++){
			$t = $array[$i];
			echo $start.$t.$end;
		}
	}
	static function _getStackTrace($ex,$full =true)
	{
		$trace = $ex->getTrace();
		$result = array();
		
		$firstCrush = array();
		
		for($i=0;$i<sizeof($trace);$i++){
			$t = $trace[$i];

			if(array_key_exists("file",$t)){
				$temp = $t["file"].":".$t["line"];
				if(array_key_exists("class",$t)){
					$temp.=" at ".$t["class"]."::".$t["function"];
				}else{
					$temp.=" at ".$t["function"];
				}
				$firstCrush[]= $temp;
			}
		}
		
		if($full == false){
			$result = array($firstCrush[0]);
		}else{
			for($i=0;$i<sizeof($firstCrush);$i++){
				$result[] = $firstCrush[$i];
			}
		}
		return $result;
	}
	
	static function _findString($what,$where)
	{
		$pos = strpos($where,$what);
		if ($pos !== false) {
			return $pos;
		} else {
			return -1;
		}
	}
	static function _findMethodsStartingWith($methodName,$methods)
	{
		$result = array();
		for($i=0;$i<sizeof($methods);$i++){
			$m = $methods[$i];
			$fs = Test::_findString($methodName,$m);
			if($fs==0){
				$result[]=$m;
			}
		}
		return $result;
	}
	static function _methodExist($methodName,$methods)
	{
		for($i=0;$i<sizeof($methods);$i++){
			$m = $methods[$i];
			if($m==$methodName) return true;
		}
		return false;
	}
	
	static function _doTest($className,$showDump)
	{
		$f = new FixtureResult();
		$f->TestClass = $className;
		
		$mts = get_class_methods ($className);
		$testClass = new $className();
		$initializeTest = Test::_methodExist("TestInitialize",$mts);
		$cleanUpTest = Test::_methodExist("TestCleanUp",$mts);
		
		if(Test::_methodExist("FixtureInitialize",$mts)){
			try{
				call_user_func(array($testClass, 'FixtureInitialize'));
			}catch(Exception $ex){
				$f->Success = false;
				$f->Reason = "Error calling FixtureInitialize";
				$f->Exception = $ex;
				$f->StackTrace = Test::_getStackTrace($ex);
				return f;
			}
		}
		$allTests = Test::_findMethodsStartingWith("DoTest",$mts);
		
		for($i=0;$i<sizeof($allTests);$i++){
			$r = new TestResult();
			$fullTestName = $allTests[$i];
			if(Test::$TestOnly!=""){
				if(Test::$TestOnly!=$allTests[$i]){
					continue;
				}
			}
			$r->TestName = substr($allTests[$i],6);
			if($initializeTest){
				try{
					call_user_func(array($testClass, 'TestInitialize'));
				}catch(Exception $ex){
					$f->Success = false;
					$r->Success = false;
					$r->Reason = "Error executing TestInitialize";
					$r->Exception = $ex;
					$r->StackTrace = Test::_getStackTrace($ex);
					$f->Results[] = $r;
					continue;
				}
			}
			
			if(!$showDump) ob_start();
			try{
				call_user_func(array($testClass,"DoTest".$r->TestName));
			}catch(TestException $ex){
				$f->Success = false;
				$r->Success = false;
				$r->Reason = $ex->Reason;
				$r->Exception = $ex;
				$r->StackTrace = Test::_getStackTrace($ex,false);
			}catch(Exception $ex){
				$f->Success = false;
				$r->Success = false;
				$r->Reason = "Error executing ".$r->TestName;
				$r->Exception = $ex;
				$r->StackTrace = Test::_getStackTrace($ex);
			}
			if(!$showDump){
				$r->Dump = Test::GetContent();
			}
			if(!$showDump)ob_end_clean();
			
			$methods[] = $allTests[$i];
			if($cleanUpTest){
				try{
					call_user_func(array($testClass, 'TestCleanUp'));
				}catch(Exception $ex){
					$f->Success = false;
					$r->Success = false;
					$r->Reason = "Error executing TestCleanUp";
					$r->Exception = $ex;
					$r->StackTrace = Test::_getStackTrace($ex);
				}
			}
			$f->Results[] = $r;
		}
		
		if(Test::_methodExist("FixtureCleanUp",$mts)){
			try{
				call_user_func(array($testClass, 'FixtureCleanUp'));
			}catch(Exception $ex){
				$f->Success = false;
				$f->Reason = "Error calling FixtureCleanUp";
				$f->Exception = $ex;
				$f->StackTrace = Test::_getStackTrace($ex);
			}
		}
		return $f;
	}

	public static function GetContent()
	{
		return ob_get_contents();
	}
	
	public static function DoTest($className,$showDump = false)
	{
		$res = Test::_doTest($className,$showDump);
		?><li><?php 
				echo "<B>".$res->TestClass."</b> "; 
				if($res->Success==false){
					echo "FAILED";
					if($res->Exception!=null){
						echo "<br>EXCEPTION: ".$res->Exception->getMessage()."<br>";
						Test::_printArray($res->StackTrace,"At ","<br>");
					}
				}else{
					echo " COMPLETED";
				}
				
				?>
			<ul><?php
				for($i=0;$i<sizeof($res->Results);$i++){
					$r = $res->Results[$i];
					?><li><?php 
						echo "<B>".$r->TestName."</B>"; 
						if($r->Success==false){
							//
							if($r->Exception!=null){
								if(get_class($r->Exception)=="TestException"){
									echo "<br>ASSERT FAIL: ".$r->Reason."<br>";
									Test::_printArray($r->StackTrace,"At ","<br>");
								}else{
									echo "<br>EXCEPTION: ".$r->Exception->getMessage()."<br>";
									Test::_printArray($r->StackTrace,"At ","<br>");
								}
							}
							if($r->Dump!=""){
								?>
								<!--<table border=1><tr><td>-->
								<?php echo $r->Dump; ?>
								<!--</td></tr></table>-->
								<?php
							}
						}else{
							echo " SUCCESS";
						}?>
					</li><?php
				}
			?></ul>
		</li>
		<?php
	}
	
	public static function PrettyDump($src)
	{
		return "<pre>".var_export($src,true)."<pre>";
	}
	
}
?>