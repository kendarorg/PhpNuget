<?php

require_once(__ROOT__."/inc/phpnugetobjectsearch.php");

Test::DoTest("PhpNugetObjectSearchTest_Execute");

class PhpNugetObjectSearchTest_Execute_001
{
	public $Field;
}

class PhpNugetObjectSearchTest_Execute
{
	private $target;
	
	function BuildOb($value)
	{
		$r = new PhpNugetObjectSearchTest_Execute_001();
		$r->Field = $value;
		$r->Type="version";
		return $r;
	}
	
	public function TestInitialize()
	{
		$this->target = new PhpNugetObjectSearch();
	}
	
	public function DoTestField_eq_version()
	{
		$o = $this->BuildOb("1.0.0.1");
		$r = $this->target->Parse("1.0.0.1 eq Field",$o);
		//echo Test::PrettyDump($r);
		$result = $this->target->Execute($o);
		
		Assert::IsTrue($result);
	}
	
	public function DoTestField_neq_version()
	{
		$o = $this->BuildOb("1.0.0.1");
		$r = $this->target->Parse("1.0.0.2 neq Field",$o);
		//echo Test::PrettyDump($r);
		
		
		$result = $this->target->Execute($o);
		
		Assert::IsTrue($result);
	}
	
	public function DoTestField_gt_version()
	{
		$o = $this->BuildOb("1.0.0.1");
		$r = $this->target->Parse("1.0.0.2 gt Field",$o);
		//echo Test::PrettyDump($r);
		$result = $this->target->Execute($o);
		
		Assert::IsTrue($result);
	}
	
	public function DoTestField_gt_version_letters()
	{
		$o = $this->BuildOb("1.0.0.1-alpha");
		$r = $this->target->Parse("1.0.0.1-beta gt Field",$o);
		//echo Test::PrettyDump($r);
		$result = $this->target->Execute($o);
		
		Assert::IsTrue($result);
	}
	
	public function DoTestField_gt_version_letters_pre_alpha()
	{
		$o = $this->BuildOb("1.0.0.1-alpha");
		$r = $this->target->Parse("1.0.0.1-pre-alpha gt Field",$o);
		//echo Test::PrettyDump($r);
		$result = $this->target->Execute($o);
		
		Assert::IsTrue($result);
	}
	
	public function DoTestField_gt_version_letters_pre_alpha_casing()
	{
		$o = $this->BuildOb("1.0.0.1-alpha");
		$r = $this->target->Parse("1.0.0.1-preAlpha gt Field",$o);
		//echo Test::PrettyDump($r);
		$result = $this->target->Execute($o);
		
		Assert::IsTrue($result);
	}
	
	public function DoTestField_lt_version_letters_pre_alpha_casing()
	{
		$o = $this->BuildOb("1.0.0.1-preBeta");
		$r = $this->target->Parse("1.0.0.1-preAlpha lt Field",$o);
		//echo Test::PrettyDump($r);
		$result = $this->target->Execute($o);
		
		Assert::IsTrue($result);
	}
	
	public function DoTestField_gt_semantic_1()
	{
		$o = $this->BuildOb("1.0.0.1-alpha");
		$r = $this->target->Parse("1.0.0.1 gt Field",$o);
		echo Test::PrettyDump($r);
		$result = $this->target->Execute($o);
		
		Assert::IsTrue($result);
	}
}

?>