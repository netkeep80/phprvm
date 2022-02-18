<?
include 'includes/Core.php';
include 'includes/classes/Rmodel/Rmodel.php';
$id = Web::Get('id','int','',true);
$enable_log = TRUE;
?><textarea cols="90" rows="40" READONLY="readonly">
Execution log:
<?
$Ent = PHPExec( $id );
?></textarea><?
?>
<textarea cols="90" rows="40" READONLY="readonly">
Relation execution result:
Ent <?
print_r(array_keys($Ent));
?>

Sub <?
print_r(array_keys($Ent['Sub']));
?>

Rel <?
print_r(array_keys($Ent['Rel']));
?>

Obj <?
print_r(array_keys($Ent['Obj']));
?>

PHPView
(
<?=$Ent['PHPView']?>

)

EngView
(
<?=$Ent['EngView']?>

)

RusView
(
<?=$Ent['RusView']?>

)

</textarea>
<br>
eval( $Ent['PHPView'] ):
<br>
<?
eval( $Ent['PHPView'] );
?>
