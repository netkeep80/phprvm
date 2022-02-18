<?
include 'includes/Core.php';


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
		  <th width="5%">ID</th>
		  <th align="center">Entity</th>
		  <th align="right">( Subject</th>
		  <th align="center">---Relation--></th>
		  <th align="left">Object )</th>
		  <th></th>
		  <th></th>		  
		  <th></th>
		 </tr>
		 <?
		 $alt = ' class="alt"';
		 foreach ($dt as $row)
		 {
		 	$alt = (empty($alt)) ? ' class="alt"' : '';
		 ?>
		  <tr<?=$alt?>>
		  <td><?=$row['Id']?></td>
		  <td align="center"><?=$row['EngView']?></td>
		  <td align="right"><?=$row['SubEngView']?></td>
		  <td align="center"><?=$row['RelEngView']?></td>
		  <td align="left"><?=$row['ObjEngView']?></td>
		  <td><a href="/Entity_PHPView.php?id=<?=$row['Id']?>">View</a></td>
		  <td><a href="/Entity_Edit.php?id=<?=$row['Id']?>">Edit</a></td>
		  <td><a href="/Entity_PHPDebug.php?id=<?=$row['Id']?>">Debug</a></td>
		 </tr>
		 <?
		 }
		 ?>
		</table>
		<br/>
		<div class="rmodel" style="margin:10px;">
			<h2>New entity creation</h2>
			<form id="myForm" name="myForm" onsubmit="return false;">
				<fieldset>
					<b>EntId----></b><input type="text" name="EntId" id="EntId" value="" placeholder="" /><br /><br />
					<b>SubId----></b><input type="text" name="SubId" id="SubId" value="" placeholder="" /><br /><br />
					<b>RelId----></b><input type="text" name="RelId" id="RelId" value="" placeholder="" /><br /><br />
					<b>ObjId----></b><input type="text" name="ObjId" id="ObjId" value="" placeholder="" /><br /><br />
					<b>PHPView--></b><input type="text" name="PHPView" id="PHPView" value="" placeholder="" /><br /><br />
					<b>EngView--></b><input type="text" name="EngView" id="EngView" value="" placeholder="" /><br /><br />
					<b>RusView--></b><input type="text" name="RusView" id="RusView" value="" placeholder="" /><br /><br />
					<button id="AddEnt">Add Entity</button>
				</fieldset>
			</form>
			<script type="text/javascript" src="/js/jquery.min.js?1017-01"></script>
			<script type="text/javascript" src="/js/example.ui.js?1017-01" ></script>
		</div>
	</div>
</div>
</body>
</html>