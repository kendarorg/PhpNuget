<?php
require_once(dirname(__FILE__)."/../root.php");
require_once(__ROOT__."/settings.php");
require_once(__ROOT__."/inc/api_users.php");
require_once(__ROOT__."/inc/commons/url.php");
require_once(__ROOT__."/inc/commons/http.php");
require_once(__ROOT__."/inc/api_nuget.php");

$q = UrlUtils::GetRequestParamOrDefault("q",null);

?>
<html><body>
<form style="visibility: hidden" method="POST" name='frm' action="<?php echo Settings::$SiteRoot;?>?specialType=packages"
	enctype="multipart/form-data">

	<input type="text" id="q" name="q" value="<?php echo $q;?>"/>
		<input type="submit" value="Search" ></input>
</form>
<script language="JavaScript">
document.frm.submit();
</script>
</body></html>