<?
include 'includes/Core.php';


$html_header_title = '';
$html_header_add = <<<EOC

EOC;
include DOC_ROOT . '/html/controls/html_header.inc.php';

?>
<body>
<style>
a { cursor:pointer; }
ul.tree li { list-style:none; padding:5px; border:1px solid #ddd; margin-left:15px; }
.interrior { position:absolute; left:0; top:0; bottom:0; width:100%; }
.col-left { position:absolute; top:0; left:0; bottom:0; width:50%; overflow:auto;  }
.col-right { position:absolute; top:0; right:10px; bottom:0; width:49.5%; overflow:auto;  }
a.el { color:#000 !important; }
.curr_ { font-weight:600 !important; }
</style>
<div class="exterrior">
	<h2>Иерархия агрегирования сущностей</h2>
	<br/>
	<br/>
	<div class="interrior">
		<div class="col-left">
			<ul class="tree" type="disc">
				<li>
					<a class="a-tree" ref=""><b>^</b></a>
					<a class="el" ref="">[Root entities]</a>
					<div id="tree-place-"></div>
				</li>
			</ul>
		</div>
		<div class="col-right">
		</div>
	</div>
	<script type="text/javascript" src="/js/jquery.min.js?1017-01"></script>
	<script type="text/javascript" src="/js/example.ui.js?1017-01" ></script>
</div>
</body>
</html>