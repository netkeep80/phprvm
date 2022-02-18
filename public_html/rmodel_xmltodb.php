<?
include 'includes/functions.php';
include DOC_ROOT . '/includes/classes/Rmodel/Import.php';

$file = DOC_ROOT . '/xml/data.xml';
$result = Rmodel_Import::XmlToDb($file);


$html_header_add = <<<EOC

EOC;
include DOC_ROOT . '/html/controls/html_header.inc.php';

?>
<body>
<div class="exterrior">
	<div class="interrior">
		<br />
	<?
	
	if ($result['Status'] == 1)
		echo 'Success. ' . $result['Msg'];
	else
		echo 'Error. ' . $result['Msg'];
	?>
	</div>
</div>
</body>
</html>