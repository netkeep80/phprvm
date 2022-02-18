<?
include 'includes/Core.php';
include 'includes/classes/Rmodel/Rmodel.php';
$id = Web::Get('id','int','',true);
$enable_log = FALSE;
if ( !$is_ajax )
{
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
</head>
<body>
<?
}

$Ent = PHPExec( $id ); 
eval( $Ent['PHPView'] );

if ( !$is_ajax ) { ?>
</body>
</html>
<? } ?>