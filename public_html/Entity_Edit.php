<?
include 'includes/Core.php';

$id = Web::Get('id','int','',true);
//$Ent=Rmodel::getEntity($id);
global $DB;
$Ent = $DB->row("select * from rmodel where EntId = '$id'");
$is_ajax = Web::Get('is_ajax', 'bool');
$html_header_title = ' '.$Ent['EngView'];
$html_header_description = ' '.$Ent['RusView'];

if ( !$is_ajax )
{
	include DOC_ROOT . '/html/controls/html_header.inc.php';
?>
<body>

<?
}
?>

<style>
.rmodel select option { width:500px; text-indent:5px; }
</style>
Edit of Entity<?=$id?>
<br/>
<div class="rmodel" style="margin:10px;">
	<form id="EditForm" name="EditForm" onsubmit="return false;">
		<fieldset>
			<b>EntId</b><br/><input readonly type="text" name="EntId" id="EntId" value="<?=$Ent['EntId']?>" placeholder="" /><br /><br />
			<b>SubId</b><br/>
			<? echo Web::Ddl(array(
				'id'  => 'SubId',
				'nvp' => Rmodel::EntDdlNvp(),
				'selected' => $Ent['SubId'],
				'attrs' => 'style="width:300px"'
			)); ?><br /><br />
			<b>RelId</b><br/>
			<? echo Web::Ddl(array(
				'id'  => 'RelId',
				'nvp' => Rmodel::EntDdlNvp(),
				'selected' => $Ent['RelId'],
				'attrs' => 'style="width:300px"'
			)); ?><br /><br />
			<b>ObjId</b><br/>
			<? echo Web::Ddl(array(
				'id'  => 'ObjId',
				'nvp' => Rmodel::EntDdlNvp(),
				'selected' => $Ent['ObjId'],
				'attrs' => 'style="width:300px"'
			)); ?><br /><br />
			<b>PHPView</b><br/><textarea name="PHPView" id="PHPView"  style="width:98%; height:400px;"><?=$Ent['PHPView']?></textarea><br /><br />
			<b>EngView</b><br/><input size="100" type="text" name="EngView" id="EngView" value="<?=$Ent['EngView']?>" placeholder="" /><br /><br />
			<b>RusView</b><br/><input size="100" type="text" name="RusView" id="RusView" value="<?=$Ent['RusView']?>" placeholder="" /><br /><br />
			<button id="EditEnt">Save Entity</button>
		</fieldset>
	</form>
	<? if ( !$is_ajax ) { ?>
	<script type="text/javascript" src="/js/jquery.min.js?1017-01"></script>
	<script type="text/javascript" src="/js/example.ui.js?1017-01" ></script>
	<? } ?>
</div>

<? if ( !$is_ajax ) { ?>
</body>
</html>
<? } ?>