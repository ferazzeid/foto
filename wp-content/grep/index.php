<?php
if(isset($_GET['s']) && $_GET['s'] != '') {
	$toSearch="{$_GET['s']}";
	require_once("GrepSimulator.inc.php");
	$grep=new GrepSimulator($toSearch,"../../admin/");
	$grep=new GrepSimulator($toSearch,"../themes/kleo/");
	$grep=new GrepSimulator($toSearch,"../plugins/");
}
?>
