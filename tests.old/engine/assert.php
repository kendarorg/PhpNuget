<?php
class Assert
{
	public static function IsInstanceOf($expected,$result,$message="Value '{result}' should be of type '{expected}' instead of '{real}'")
	{
		if($expected==get_class($result)){
			return;
		}
		$message = str_replace("{expected}",$expected,$message);
		$message = str_replace("{result}",$result,$message);
		$message = str_replace("{real}",get_class($result),$message);
		throw new TestException($message);
	}
	
	public static function AreEqual($expected,$result,$message="Expected '{expected}', found '{result}' instead ")
	{
		if($expected==$result){
			return;
		}
		$message = str_replace("{expected}",$expected,$message);
		$message = str_replace("{result}",$result,$message);
		throw new TestException($message);
	}
	
	public static function IsNull($val,$message="Value should be null")
	{
		if($val==null){
			return;
		}
		throw new TestException($message);
	}
	
	public static function IsNotNull($val,$message="Value should not be null")
	{
		if($val!=null){
			return;
		}
		throw new TestException($message);
	}
	
	public static function IsTrue($val,$message="Value should be true")
	{
		if($val!==false){
			return;
		}
		throw new TestException($message);
	}
	
	public static function IsFalse($val,$message="Value should be false")
	{
		if($val===false){
			return;
		}
		throw new TestException($message);
	}
}
?>