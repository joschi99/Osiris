<p>

<div id="footer">
<?php
if( isset($_GET['print']) or isset($_GET['xls']) ){
	echo "	$_SESSION[user], $now";
}elseif($debug){
	echo "	$cmdlbl $timlbl ".round(microtime(1) - $debug,2)." $tim[s] &nbsp;";
}else{
	echo "	<span class=\"flft\"><img src=\"img/up.png\" title=\"$toplbl\" onclick=\"window.scrollTo(0, 0);\"> $self</span> &copy; 2001-2015 Remo Rickli & contributors &nbsp;&nbsp;&nbsp;\n";
}
?>
</div>

</body>
</html>
