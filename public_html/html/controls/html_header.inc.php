<?
$icoRnd = rand(10,99);
$html_header_title = SITE_NAME . ((isset($html_header_title)) ? '' . $html_header_title : '');

$html_header_description = (isset($html_header_description)) ? $html_header_description : '';
$html_header_keywords = (isset($html_header_keywords)) ? $html_header_keywords : '';

$html_header_description = (!empty($html_header_description)) ? '<meta name="description" content="'.$html_header_description.'" />' : '';
$html_header_keywords = (!empty($html_header_keywords)) ? '<meta name="keywords" content="'.$html_header_keywords.'" />' : '';

$html_header_add = (isset($html_header_add)) ? $html_header_add : '';

?>
<!DOCTYPE html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?=$html_header_title?></title>
<?=$html_header_keywords?>
<?=$html_header_description?>
<!-- link rel="shortcut icon" href="/favicon<? echo $icoRnd; ?>.xico" type="image/x-icon" / -->
<link href="/css/index.css?v1" rel="stylesheet" type="text/css" />

<script type="text/javascript" src="/js/iutils.js"></script>
<script type="text/javascript" src="/js/jquery-1.3.2.min.js"></script>
<script type="text/javascript" src="/js/index.js?v2"></script>

<?=$html_header_add?>
</head>
