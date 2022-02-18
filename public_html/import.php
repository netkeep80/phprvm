<?
include 'includes/Core.php';

$svn = "netkeep";
$tag = "/trunk";
$folder = "/home/rmodel/public_html";

#--------------------------------------------------------------
$cmd = "svn export https://svn.devlands.com:8443/" . $svn . $tag . " " . $folder . " --force --username sergio --password 1111 --non-interactive --trust-server-cert";
//echo "$cmd <br />";
//echo exec($cmd);

$r = mp_exec($cmd);

$file = DOC_ROOT . '/data.xml';
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

	echo "SVN: " . $svn . "<br>";
	echo "Tag: " . $tag . "<br><br>";
	Sys::debug($r['stdout']);
	echo '<br />';
	
	if ($result['Status'] == 1)
		echo 'Success. ' . $result['Msg'];
	else
		echo 'Error. ' . $result['Msg'];
	?>
	</div>
</div>
</body>
</html>


<?
function mp_exec($cmd, $input='')
{
	$proc=proc_open($cmd, array(0=>array('pipe', 'r'), 1=>array('pipe', 'w'), 2=>array('pipe', 'w')), $pipes);
	fwrite($pipes[0], $input);fclose($pipes[0]);
	$stdout=stream_get_contents($pipes[1]);fclose($pipes[1]);
	$stderr=stream_get_contents($pipes[2]);fclose($pipes[2]);
	$rtn=proc_close($proc);
	return array(
		'stdout'=>$stdout,
		'stderr'=>$stderr,
		'return'=>$rtn
	);
}




?>