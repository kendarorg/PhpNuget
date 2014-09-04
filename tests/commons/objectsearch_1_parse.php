<?php
require_once(__ROOT__."/inc/commons/objectsearch.php");

Test::DoTest("ObjectSearchTest_1");

class ObjectSearchTest_1
{
	private $target;
	
	public function TestInitialize()
	{
		$this->target = new ObjectSearch();
	}
	
	public function DoTestField_eq_true()
	{
		$res = $this->target->Parse("Field eq true",array("Field"));
		
		Assert::AreEqual(1,sizeof($res));
		
		Assert::AreEqual("function",$res[0]->Type);
		Assert::AreEqual("doeq",$res[0]->Value);
		Assert::AreEqual(2,sizeof($res[0]->Children));
		
		$children = $res[0]->Children;
		
		Assert::AreEqual("field",$children[0]->Type);
		Assert::AreEqual("Field",$children[0]->Value);
		
		Assert::AreEqual("boolean",$children[1]->Type);
		Assert::AreEqual(true,$children[1]->Value);
	}
	
	public function DoTestfalse_and_true()
	{
		$res = $this->target->Parse("false and true",array("Field"));
		
		Assert::AreEqual(1,sizeof($res));
		
		Assert::AreEqual("function",$res[0]->Type);
		Assert::AreEqual("doand",$res[0]->Value);
		Assert::AreEqual(2,sizeof($res[0]->Children));
		
		$children = $res[0]->Children;
		
		Assert::AreEqual("boolean",$children[0]->Type);
		Assert::AreEqual(false,$children[0]->Value);
		
		Assert::AreEqual("boolean",$children[1]->Type);
		Assert::AreEqual(true,$children[1]->Value);
	}
	
	
	public function DoTestfalse_or_true()
	{
		$res = $this->target->Parse("false or true",array("Field"));
		
		Assert::AreEqual(1,sizeof($res));
		
		Assert::AreEqual("function",$res[0]->Type);
		Assert::AreEqual("door",$res[0]->Value);
		Assert::AreEqual(2,sizeof($res[0]->Children));
		
		$children = $res[0]->Children;
		
		Assert::AreEqual("boolean",$children[0]->Type);
		Assert::AreEqual(false,$children[0]->Value);
		
		Assert::AreEqual("boolean",$children[1]->Type);
		Assert::AreEqual(true,$children[1]->Value);
	}
	
	public function DoTestField_eq_true_And_Other_neq_false()
	{
		$res = $this->target->Parse(
			"Field eq true and Other neq false",
			array("Field","Other"));
		
		
		Assert::AreEqual(1,sizeof($res));
		
		Assert::AreEqual("function",$res[0]->Type);
		Assert::AreEqual("doand",$res[0]->Value);
		Assert::AreEqual(2,sizeof($res[0]->Children));
		
		$children = $res[0]->Children;
		
		Assert::AreEqual("function",$children[0]->Type);
		Assert::AreEqual("doeq",$children[0]->Value);
		$doeq = $children[0]->Children;
		Assert::AreEqual(2,sizeof($doeq));
		
		Assert::AreEqual("field",$doeq[0]->Type);
		Assert::AreEqual("Field",$doeq[0]->Value);
		
		Assert::AreEqual("boolean",$doeq[1]->Type);
		Assert::AreEqual(true,$doeq[1]->Value);
		
		Assert::AreEqual("function",$children[1]->Type);
		Assert::AreEqual("doneq",$children[1]->Value);
		$doneq = $children[1]->Children;
		Assert::AreEqual(2,sizeof($doneq));
		
		Assert::AreEqual("field",$doneq[0]->Type);
		Assert::AreEqual("Other",$doneq[0]->Value);
		
		Assert::AreEqual("boolean",$doneq[1]->Type);
		Assert::AreEqual(false,$doneq[1]->Value);
	}
}

?>