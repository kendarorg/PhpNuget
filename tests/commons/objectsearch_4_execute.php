<?php
require_once(__ROOT__."/inc/commons/objectSearch.php");

Test::DoTest("ObjectSearchTest_4");

class ObjectSearchTest_4_001
{
	public $Field;
}

class ObjectSearchTest_4
{
	private $target;
	
	function BuildOb($value)
	{
		$r = new ObjectSearchTest_4_001();
		$r->Field = $value;
		return $r;
	}
	
	public function TestInitialize()
	{
		$this->target = new ObjectSearch();
	}
	
	public function DoTestString_number_eq_field()
	{
		$o = $this->BuildOb(224);
		$r = $this->target->Parse("224 eq Field",$o);
		echo Test::PrettyDump($r);
		$result = $this->target->Execute($o);
		
		Assert::IsTrue($result);
	}
	
	public function DoTestString_number_neq_field()
	{
		$o = $this->BuildOb(224);
		$r = $this->target->Parse("224 neq Field",$o);
		echo Test::PrettyDump($r);
		$result = $this->target->Execute($o);
		
		Assert::IsFalse($result);
	}
	
	public function DoTestString_number_gt_field()
	{
		$o = $this->BuildOb(200);
		$r = $this->target->Parse("224 gt Field",$o);
		echo Test::PrettyDump($r);
		$result = $this->target->Execute($o);
		
		Assert::IsTrue($result);
	}
	
	public function DoTestString_number_gte_field()
	{
		$o = $this->BuildOb(224);
		$r = $this->target->Parse("224 gte Field",$o);
		echo Test::PrettyDump($r);
		$result = $this->target->Execute($o);
		
		Assert::IsTrue($result);
	}
	
	public function DoTestString_number_lt_field()
	{
		$o = $this->BuildOb(225);
		$r = $this->target->Parse("224 lt Field",$o);
		echo Test::PrettyDump($r);
		$result = $this->target->Execute($o);
		
		Assert::IsTrue($result);
	}
	
	public function DoTestString_number_lte_field()
	{
		$o = $this->BuildOb(224);
		$r = $this->target->Parse("224 lte Field",$o);
		echo Test::PrettyDump($r);
		$result = $this->target->Execute($o);
		
		Assert::IsTrue($result);
	}
	
	public function DoTestString_strint_eq_field()
	{
		$o = $this->BuildOb("test");
		$r = $this->target->Parse("'test' eq Field",$o);
		echo Test::PrettyDump($r);
		$result = $this->target->Execute($o);
		
		Assert::IsTrue($result);
	}
	
	public function DoTestString_strint_beq_field()
	{
		$o = $this->BuildOb("test");
		$r = $this->target->Parse("'ciosp' neq Field",$o);
		echo Test::PrettyDump($r);
		$result = $this->target->Execute($o);
		
		Assert::IsTrue($result);
	}
}

?>