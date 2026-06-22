<?php
require_once(dirname(__DIR__)."/config.inc");

$request = GlobalRegistry::get("request");
$properties = GlobalRegistry::get("properties");
$q = $request->getParam("q");

?>
<html><body>
<form style="visibility: hidden" method="POST" name='frm' action="<?php echo $properties->getProperty("siteRoot");?>?specialType=packages"
	enctype="multipart/form-data">

	<input type="text" id="q" name="q" value="<?php echo $q;?>"/>
		<input type="submit" value="Search" ></input>
</form>
<script language="JavaScript">
document.frm.submit();
</script>
</body></html>