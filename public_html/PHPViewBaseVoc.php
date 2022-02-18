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
		<h2>        Базовый словарь на PHP</h2>
		<br/>
		    Правила написания словаря:
		<br/>
		  1. Если вы хотите просто задать значение сущности (кэш её проекции), то это должен быть шаблон исполняемого PHP кода
		<br/>
		  2. Если вы хотите описать шаблон кода проектора, то для генерации проекции строка PHP должна начинаться с "$PHPView .= " а далее текстовое значение с шаблоном PHP проекции сущности $Obj
		<br/>
		( Виртуальная машина написана исходя из принципа самокомпилируемости, поэтому значение сущности должно состоять из самодостаточного кода предазначенного для непосредственного исполнения без предварительной обработки )
		<br/>
		  3. Закэшированное значение сущности может включать обращение к переменным доступным в контексте исполнения сущности:
		<br/>
		<br/>
		$RelId --> идентификатор сущности PHPView которой исполняется
		<br/>
		$EntId --> идентификатор этой, т.е. контекстной сущности
		<br/>
		$Ent --> эта, т.е. контекстная сущность
		<br/>
		$Ent['Ent'] --> проекция сущности
		<br/>
		$Parent = $Ent['Sub'] --> проекция субъекта исполнения - ParentView
		<br/>
		$Ent['Rel'] --> проекция отношения исполнения - Controller (то что сейчас исполняется)
		<br/>
		$Model = $Ent['Obj'] --> проекция объекта исполнения - Model
		<br/>
		$PHPView --> результат проецирования - View
		<br/>
		$Ent['SubId'] --> идентификатор субъекта
		<br/>
		$Ent['RelId'] --> идентификатор отношения
		<br/>
		$Ent['ObjId'] --> идентификатор объекта
		<br/>
		$Ent['PHPView'] --> кэш PHP проекции контекстной сущности
		<br/>
		$Ent['EngView'] --> кэш названия контекстной сущности
		<br/>
		$Ent['RusView'] --> кэш описания контекстной сущности
		<br/>
		$Ent['name'] --> проекция атрибута с типом name
		<br/>
		<br/>
		<table class="data" width="800">
		 <tr>
		  <th width="5%">ID</th>
		  <th>EngView</th>
		  <th>PHPView</th>
		  <th></th>
		  <th></th>		  
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
		  <td><?=$row['EngView']?></td>
		  <td><textarea cols="100" rows="5" READONLY="readonly"><?=$row['PHPView']?></textarea></td>
		  <td><a href="/Entity_PHPView.php?id=<?=$row['Id']?>">View</a></td>
		  <td><a href="/Entity_Edit.php?id=<?=$row['Id']?>">Edit</a></td>
		  <td><a href="/Entity_PHPExec.php?id=<?=$row['Id']?>">Exec</a></td>
		  <td><a href="/Entity_PHPDebug.php?id=<?=$row['Id']?>">Debug</a></td>
		  <td><button class="DeleteButton" ref="<?=$row['Id']?>">Delete</button></td>		  
		 </tr>
		 <?
		 }
		 ?>
		</table>
	</div>
</div>
</body>
</html>