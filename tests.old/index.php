<?php
require_once(dirname(__FILE__)."/../src/root.php");
require_once(dirname(__FILE__)."/engine/test.php");
require_once(dirname(__FILE__)."/engine/assert.php");
?>
<html>
	<body>
		Test results
		<ul> 
<?php
//Test::$TestOnly = "DoTestField_gt_semantic_1";
if(true){
require_once(dirname(__FILE__)."/commons/objectsearch_1_parse.php");
require_once(dirname(__FILE__)."/commons/objectsearch_2_parse.php");
require_once(dirname(__FILE__)."/commons/objectsearch_3_parse.php");
require_once(dirname(__FILE__)."/commons/objectsearch_4_execute.php");
require_once(dirname(__FILE__)."/commons/objectsearch_5_execute.php");
require_once(dirname(__FILE__)."/inc/phpnugetobjectsearchtest.php");
require_once(dirname(__FILE__)."/inc/phpnugetobjectsearchTest_execute.php");
}
?>
		<ul>
	</body>
</html>