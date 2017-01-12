<?php
require_once(dirname(__FILE__)."/apibase.php");

class SmallTextDbApiBase extends ApiBase
{
	protected function _openDb(){throw new Exception("Not implemented.");}
	protected function _buildEntityFromRequest($db){throw new Exception("Not implemented.");}
	protected function _buildKeysFromRequest($db){throw new Exception("Not implemented.");}
	protected function _isMatch($keyArray,$item){throw new Exception("Not implemented.");}
	protected function _verifyInsert($db,$item){}
	protected function _verifyUpdate($db,$old,$new){}
	protected function _verifyDelete($db,$keysArray){}
	protected function _preExecute(){}
	
	public function doget()
	{
		$this->_preExecute();
		$this->GetAll();
	}
	public function dogetsingle()
	{
		$this->_preExecute();
		$this->GetSingle();
	}
	public function dopost()
	{
		$this->_preExecute();
		$this->Insert();
	}
	public function doput()
	{
		$this->_preExecute();
		$this->Update();
	}
	public function dodelete()
	{
		$this->_preExecute();
		$this->Delete();
	}
	
	public function GetAll()
	{
		$pg= $this->_getPagination();
		$db = $this->_openDb();
		$result = $db->Query(null,$pg->Top,$pg->Skip);
		ApiBase::ReturnSuccess($result);
	}
	
	public function GetSingle()
	{
		$db = $this->_openDb();
		$keyArray = $this->_buildKeysFromRequest($db);
		$q = array();
		
		foreach($keyArray as $k=>$v){
			array_push($q,"(".$k." eq '".$v."')");
		}
		$query = join(" and ",$q);
		
		$allRows = $db->Query($query);
		
		if(sizeof($allRows)==1){
			ApiBase::ReturnSuccess($allRows[0]);
		}
		ApiBase::ReturnError("Item not found",404);
	}
	
	public function Delete()
	{
		$db = $this->_openDb();
		$keyArray = $this->_buildKeysFromRequest($db);
		
		$this->_verifyDelete($db,$keyArray);
		
		$q = array();
		
		foreach($keyArray as $k=>$v){
			array_push($q,"(".$k." eq '".$v."')");
		}
		$query = join(" and ",$q);
		$allRows = $db->Query($query);
		
		if(sizeof($allRows)==1){
			$db->DeleteRow($allRows[0]);
			ApiBase::ReturnSuccess($allRows[0]);
		}
		ApiBase::ReturnSuccess(null);
	}
	
	public function Insert()
	{
		$db = $this->_openDb();
		$entity = $this->_buildEntityFromRequest($db);
		$this->_verifyInsert($db,$entity);
		
		$result = $db->AddRow($entity,true);
		
		if($result){
			ApiBase::ReturnSuccess($entity);
		}else{
			ApiBase::ReturnError("Error inserting item",500);
		}
	}
	
	public function Update()
	{
		
		$db = $this->_openDb();
		$entity = $this->_buildEntityFromRequest($db);
		
		$keyArray = $this->_buildKeysFromRequest($db);
		
		$q = array();
		
		foreach($keyArray as $k=>$v){
			array_push($q,"(".$k." eq '".$v."')");
		}
		$query = join(" and ",$q);
		
		$allRows = $db->Query($query);
		
		$founded = null;
		if(sizeof($allRows)==1){
			$founded = $allRows[0];
		}
		
		if($founded==null){
			ApiBase::ReturnError("Item not found",404);
		}
		
		$this->_verifyUpdate($db,$founded,$entity);
		
		$result = $db->AddRow($entity,true);
		
		if($result){
			ApiBase::ReturnSuccess($entity);
		}else{
			ApiBase::ReturnError("Error updating item",500);
		}
	}
}
?>