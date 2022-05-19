<?php

require_once(__ROOT__."/inc/commons/objectsearch.php");
require_once(__ROOT__."/inc/commons/utils.php");

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
	
	public function DoTestString_string_eq_field()
	{
		$o = $this->BuildOb("test");
		$r = $this->target->Parse("'test' eq Field",$o);
		echo Test::PrettyDump($r);
		$result = $this->target->Execute($o);
		
		Assert::IsTrue($result);
	}
	
	public function DoTestString_string_beq_field()
	{
		$o = $this->BuildOb("test");
		$r = $this->target->Parse("'ciosp' neq Field",$o);
		echo Test::PrettyDump($r);
		$result = $this->target->Execute($o);
		
		Assert::IsTrue($result);
	}
	
	public function DoTestString_string_substringof()
	{
		$o = $this->BuildOb("ciospolo");
		
		$r = $this->target->Parse("substringof('ciosp',Field)",$o);
		echo Test::PrettyDump($r);
		$result = $this->target->Execute($o);
		
		Assert::IsTrue($result);
	}
	
	public function DoTestString_string_substringof_false()
	{
		$o = $this->BuildOb("ciospolo");
		
		$r = $this->target->Parse("substringof('ciospx',Field)",$o);
		echo Test::PrettyDump($r);
		$result = $this->target->Execute($o);
		
		Assert::IsFalse($result);
	}
	
	public function DoTestString_string_substringof_inverted()
	{
		$o = $this->BuildOb("ciosp");
		
		$r = $this->target->Parse("substringof(Field,'ciospolo')",$o);
		echo Test::PrettyDump($r);
		$result = $this->target->Execute($o);
		
		Assert::IsTrue($result);
	}
	
	public function DoTestString_string_substringof_inverted_false()
	{
		$o = $this->BuildOb("ciospx");
		
		$r = $this->target->Parse("substringof(Field,'ciospolo')",$o);
		echo Test::PrettyDump($r);
		$result = $this->target->Execute($o);
		
		Assert::IsFalse($result);
	}
	
	public function DoTestString_string_tolower_neq()
	{
		$o = $this->BuildOb("CIOSP");
		$r = $this->target->Parse("tolower(Field) neq 'CIOSP'",$o);
		
		$result = $this->target->Execute($o);
		
		Assert::IsTrue($result);
	}
	
	public function DoTestString_string_tolower_neq_diff_case()
	{
		$o = $this->BuildOb("CIOSP");
		$r = $this->target->Parse("tolower(Field) neq 'ciosp'",$o);
		
		$result = $this->target->Execute($o);
		
		Assert::IsFalse($result);
	}
	
	public function DoTestString_string_tolower_eq()
	{
		$o = $this->BuildOb("CIOSP");
		
		$r = $this->target->Parse("tolower(Field) eq 'ciosp'",$o);
		echo Test::PrettyDump($r);
		$result = $this->target->Execute($o);
		
		Assert::IsTrue($result);
	}	
	
	public function DoTestMixAndMatch()
	{
		$o = $this->BuildOb("CIOSP");
		
		$r = $this->target->Parse("substringof(tolower(Field),'ciospolo')",$o);
		echo Test::PrettyDump($r);
		$result = $this->target->Execute($o);
		
		Assert::IsTrue($result);
	}
	
	public function DoTestMixAndMatchFalse()
	{
		$o = $this->BuildOb("CIOSPx");
		
		$r = $this->target->Parse("substringof(tolower(Field),'ciospolo')",$o);
		echo Test::PrettyDump($r);
		$result = $this->target->Execute($o);
		
		Assert::IsFalse($result);
	}
	
	//substringof('class',tolower(Id))
	public function DoTestMixAndMatch_nest()
	{
		$o = $this->BuildOb("CIOSP");
		
		$r = $this->target->Parse("(Field eq 'CIOSP') and (substringof(tolower(Field),'ciospolo'))",$o);
		echo Test::PrettyDump($r);
		$result = $this->target->Execute($o);
		
		Assert::IsTrue($result);
	}
	public function DoTestMixAndMatch_nest_invert()
	{
		$o = $this->BuildOb("CIOSPOLO");
		
		$r = $this->target->Parse("(Field neq 'ciospolo') and (substringof('ciosp',tolower(Field)))",$o);
		echo Test::PrettyDump($r);
		$result = $this->target->Execute($o);
		
		Assert::IsTrue($result);
	}
}

?>