<?
include 'includes/Core.php';
include 'includes/classes/Rmodel/Rmodel.php';

$dt = Rmodel::DbModel();
if( is_null($dt) )
{
	$dt = Array();
}

$html_header_title = '';
$html_header_add = <<<EOC

EOC;
include DOC_ROOT . '/html/controls/html_header.inc.php';

?>
<body>
<div class="exterrior">
	<div class="interrior">
		Таблица триединых сущностей (субъект -отношение-> объект) с возможностью их редактирования
		<br />
		<br />
		<table class="data" width="800">
		 <tr>
		  <th width="5%">ИД</th>
		  <th align="center">Сущность</th>
		  <th align="right">( Субъект</th>
		  <th align="center">---Отношение--></th>
		  <th align="left">Объект )</th>
		  <th>PHPView</th>
		  <th>Exec</th>		  
		  <th>Debug</th>
		 </tr>
		 <?
		 $alt = ' class="alt"';
		 foreach ($dt as $row)
		 {
		 	$alt = (empty($alt)) ? ' class="alt"' : '';
		 ?>
		  <tr<?=$alt?>>
		  <td><?=$row['Id']?></td>
		  <td align="center"><?=$row['RusView']?></td>
		  <td align="right"><?=$row['SubRusView']?></td>
		  <td align="center"><?=$row['RelRusView']?></td>
		  <td align="left"><?=$row['ObjRusView']?></td>
		  <td>
			<a href="/Entity_PHPView.php?id=<?=$row['Id']?>">View(<?=$row['Id']?>)</a></td>
		  <td>
			<a href="/Entity_PHPExec.php?id=<?=$row['Id']?>">Exec(<?=$row['Id']?>)</a></td>
		  <td>
			<a href="/Entity_PHPDebug.php?id=<?=$row['Id']?>">Debug(<?=$row['Id']?>)</a></td>
		 </tr>
		 <?
		 }
		 ?>
		</table><br><br>
	</div>
</div>
</body>
</html>