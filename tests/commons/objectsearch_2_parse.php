<?php
require_once(__ROOT__."/inc/commons/objectsearch.php");

Test::DoTest("ObjectSearchTest_2");

class ObjectSearchTest_2
{
	private $target;
	
	public function TestInitialize()
	{
		$this->target = new ObjectSearch();
	}
	
	public function DoTestString_test_eq_field()
	{
		$res = $this->target->Parse("'test' eq Field",array("Field"));
		
		Assert::AreEqual(1,sizeof($res));
		
		Assert::AreEqual("function",$res[0]->Type);
		Assert::AreEqual("doeq",$res[0]->Value);
		Assert::AreEqual(2,sizeof($res[0]->Children));
		
		$children = $res[0]->Children;
		
		Assert::AreEqual("string",$children[0]->Type);
		Assert::AreEqual("test",$children[0]->Value);
		
		Assert::AreEqual("field",$children[1]->Type);
		Assert::AreEqual("Field",$children[1]->Value);
	}
	
	public function DoTestSubstring_test_field()
	{
		$res = $this->target->Parse("substringof('test', Field)",array("Field"));
		
		echo Test::PrettyDump($res);
		Assert::AreEqual(1,sizeof($res));
		
		Assert::AreEqual("function",$res[0]->Type);
		Assert::AreEqual("substringof",$res[0]->Value);
		Assert::AreEqual(2,sizeof($res[0]->Children));
		
		$children = $res[0]->Children;
		
		Assert::AreEqual("string",$children[0]->Type);
		Assert::AreEqual("test",$children[0]->Value);
		
		Assert::AreEqual("field",$children[1]->Type);
		Assert::AreEqual("Field",$children[1]->Value);
	}
	
	public function DoTestSubstring_test_field_eq_true()
	{
		$res = $this->target->Parse("substringof('test', Field) eq true",array("Field"));
		
		echo Test::PrettyDump($res);
		Assert::AreEqual(1,sizeof($res));
		
		Assert::AreEqual("function",$res[0]->Type);
		Assert::AreEqual("doeq",$res[0]->Value);
		Assert::AreEqual(2,sizeof($res[0]->Children));
		
		$children = $res[0]->Children;
		
		Assert::AreEqual("function",$children[0]->Type);
		Assert::AreEqual("substringof",$children[0]->Value);
		
		Assert::AreEqual("boolean",$children[1]->Type);
		Assert::AreEqual(true,$children[1]->Value);
	}
	
	public function DoTestSubstring_test_eq_field_eq_true()
	{
		$res = $this->target->Parse("(true and Third) eq true",array("Third"));
		
		echo Test::PrettyDump($res);
		Assert::AreEqual(1,sizeof($res));
		
		Assert::AreEqual("function",$res[0]->Type);
		Assert::AreEqual("doeq",$res[0]->Value);
		
		$children = $res[0]->Children;
		Assert::AreEqual(2,sizeof($children));
				
		Assert::AreEqual("boolean",$children[1]->Type);
		Assert::AreEqual(true,$children[1]->Value);
		
		Assert::AreEqual("function",$children[0]->Type);
		Assert::AreEqual("doand",$children[0]->Value);
		$children = $children[0]->Children;
		
		Assert::AreEqual(2,sizeof($children));
		
		Assert::AreEqual("boolean",$children[0]->Type);
		Assert::AreEqual(true,$children[0]->Value);
		
		Assert::AreEqual("field",$children[1]->Type);
		Assert::AreEqual("Third",$children[1]->Value);
	}
	
	public function DoTestString_test_eq_field_with_parenthesis()
	{
		$res = $this->target->Parse("('test' eq Field)",array("Field"));
		
		Assert::AreEqual(1,sizeof($res));
		
		Assert::AreEqual("function",$res[0]->Type);
		Assert::AreEqual("doeq",$res[0]->Value);
		Assert::AreEqual(2,sizeof($res[0]->Children));
		
		$children = $res[0]->Children;
		
		Assert::AreEqual("string",$children[0]->Type);
		Assert::AreEqual("test",$children[0]->Value);
		
		Assert::AreEqual("field",$children[1]->Type);
		Assert::AreEqual("Field",$children[1]->Value);
	}
}

?>