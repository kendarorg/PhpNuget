<?php

require_once(__ROOT__."/inc/commons/objectsearch.php");

Test::DoTest("ObjectSearchTest_3");

class ObjectSearchTest_3
{
	private $target;
	
	public function TestInitialize()
	{
		$this->target = new ObjectSearch();
	}
	
	public function DoTestString_number_eq_field()
	{
		$res = $this->target->Parse("224 eq Field",array("Field"));
		
		Assert::AreEqual(1,sizeof($res));
		
		Assert::AreEqual("function",$res[0]->Type);
		Assert::AreEqual("doeq",$res[0]->Value);
		Assert::AreEqual(2,sizeof($res[0]->Children));
		
		$children = $res[0]->Children;
		
		Assert::AreEqual("number",$children[0]->Type);
		Assert::AreEqual("224",$children[0]->Value);
		
		Assert::AreEqual("field",$children[1]->Type);
		Assert::AreEqual("Field",$children[1]->Value);
	}
}

?>