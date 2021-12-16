<?php

require_once(__ROOT__."/inc/commons/objectsearch.php");
require_once(__ROOT__."/inc/commons/utils.php");

Test::DoTest("ObjectSearchTest_5");


class ObjectSearchTest_5_001
{
	public $Field;
}

class ObjectSearchTest_5
{
	private $target;
	
	function BuildOb($value)
	{
		$r = new ObjectSearchTest_5_001();
		$r->Field = $value;
		return $r;
	}
	
	public function TestInitialize()
	{
		$this->target = new ObjectSearch();
	}
	
	public function DoTestString_null_eq_field()
	{
		$o = $this->BuildOb(null);
		$r = $this->target->Parse("null eq Field",$o);
		echo Test::PrettyDump($r);
		$result = $this->target->Execute($o);
		
		Assert::IsTrue($result);
	}
	
	public function DoTestString_field_eq_null()
	{
		$o = $this->BuildOb(null);
		$r = $this->target->Parse("Field eq null",$o);
		echo Test::PrettyDump($r);
		$result = $this->target->Execute($o);
		
		Assert::IsTrue($result);
	}
	
	public function DoTestString_null_eq_emptyString()
	{
		$o = $this->BuildOb(null);
		$r = $this->target->Parse("null eq ''",$o);
		echo Test::PrettyDump($r);
		$result = $this->target->Execute($o);
		
		Assert::IsTrue($result);
	}
	
	public function DoTestString_null_neq_number()
	{
		$o = $this->BuildOb(null);
		$r = $this->target->Parse("null neq 22",$o);
		echo Test::PrettyDump($r);
		$result = $this->target->Execute($o);
		
		Assert::IsTrue($result);
	}
	
	public function DoTestString_null_neq_string()
	{
		$o = $this->BuildOb(null);
		$r = $this->target->Parse("null neq 'test'",$o);
		echo Test::PrettyDump($r);
		$result = $this->target->Execute($o);
		
		Assert::IsTrue($result);
	}
}

?>