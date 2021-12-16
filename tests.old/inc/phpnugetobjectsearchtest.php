<?php

require_once(__ROOT__."/inc/phpnugetobjectsearch.php");

Test::DoTest("PhpNugetObjectSearchTest");

class PhpNugetObjectSearchTest
{
	private $target;
	
	public function TestInitialize()
	{
		$this->target = new PhpNugetObjectSearch();
	}
	
	public function DoTestField_eq_version()
	{
		$res = $this->target->Parse("Field eq 1.0.0.1",array("Field"));
		
		Assert::AreEqual(1,sizeof($res));
		
		Assert::AreEqual("function",$res[0]->Type);
		Assert::AreEqual("doeq",$res[0]->Value);
		Assert::AreEqual(2,sizeof($res[0]->Children));
		
		$children = $res[0]->Children;
		
		Assert::AreEqual("field",$children[0]->Type);
		Assert::AreEqual("Field",$children[0]->Value);
		
		Assert::AreEqual("version",$children[1]->Type);
		Assert::AreEqual("1.0.0.1",$children[1]->Value);
	}
}

?>