<?php
require_once(__DIR__."/vendor/autoload.php");
use lib\http\Request;
use lib\utils\Properties;

$request = new Request();
$properties = new Properties();
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