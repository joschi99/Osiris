<?PHP
//===============================
// PostgreSQL functions.
//===============================

$dbregexp = '~';

function DbConnect($host,$user,$pass,$db){
	$l = pg_connect("host='$host' dbname='$db' user='$user' password='$pass'") or die("Could not connect to $db@$host with user $user.");
	return $l;
}

function DbQuery($q,$l){
	return pg_query($l,$q);
}

function DbClose($l){
        return pg_close($l);
}

function DbFieldName($r, $f){
        return pg_field_name($r, $f);
}

function DbNumFields($r){
        return pg_num_fields($r);
}

function DbNumRows($r){
        return pg_num_rows($r);
}

function DbFetchRow($r){
        return pg_fetch_row($r);
}

function DbFetchArray($r){
        return pg_fetch_assoc($r);
}

function DbFreeResult($r){

	global $debug;

	if($debug){
		echo "<div class=\"textpad code pre good xxl\">";
		debug_print_backtrace();
		echo DecFix(memory_get_usage(),1).'B @'.round(microtime(1) - $debug,2)."s</div>\n";
	}
        return pg_free_result($r);
}

function DbAffectedRows($r){										# GH: different results if the last query failed, depending on database types
        return pg_affected_rows($r);
}

function DbEscapeString($r){
        return pg_escape_string($r);
}

# We might get 'table.column' sent to this function, not just a bare column name.
#
# Do not further quote the result of this function.
function DbEscapeIdentifier($i){
	# We actually want:
	#     return pg_escape_identifier($i);
	# or perhaps some similar code that also invokes implode() and
	# array_map(), but pg_escape_identifier() is not available in PHP
	# 5.2.17.  So we have to settle for the following approximation.
	$c = explode(".", $i);
	return '"' . implode('"."', array_map(pg_escape_string, $c)) . '"';
}

function DbError($r){
        return pg_last_error($r);
}

function DbCast($v,$t){											# Based on GH's idea
	return "cast($v as $t)";
}

function DbIPv6($v){
	return $v;
}

//===================================================================
// Add record if it doesn't exist yet
function AddRecord($link,$table,$key,$col,$val){

	global $alrlbl, $addlbl;

	$mres	= DbQuery("SELECT * FROM $table WHERE $key",$link);
	if($mres){
		if( DbNumRows($mres) ){
			$status = "<img src=\"img/16/bdis.png\" title=\"$alrlbl OK ($key)\">";
		}else{
			if( !DbQuery("INSERT INTO $table ($col) VALUES ($val)",$link) ){
				$status = "<img src=\"img/16/bcnl.png\" title=\"".DbError($link)."\">";
			}else{
				$status = "<img src=\"img/16/bchk.png\" title=\"$addlbl OK\">";
			}
		}
	}else{
		print DbError($link);
	}
	return $status;
}

//===================================================================
// Adds devices. to device columns. This callback function is needed for certain join queries
function AddDevs($col){
	if($col == 'device'){
		return 'devices.device';
	}else{
		return $col;
	}
}

//===================================================================
// Adapt operator and value for special fields
function AdOpVal($c,$o,$v){

	if( preg_match("/^(first|last|start|end|time|(if|ip|os|as)?update)/",$c) and !preg_match("/^[0-9]+$/",$v) ){
		$v = strtotime($v);
	}elseif( preg_match("/^(if)?mac$/",$c) ){
		$v = preg_replace("/[.:-]/","", $v);
	}elseif(preg_match("/^(dev|orig|nod|if|mon)ip$/",$c) and !preg_match('/^[0-9]+$/',$v) ){	# Do we have an dotted IP?
		if( strstr($v,'/') ){									# CIDR?
			list($ip, $prefix) = explode('/', $v);
			$dip = sprintf("%u", ip2long($ip));
			$dmsk = 0xffffffff << (32 - $prefix);
			$dnet = sprintf("%u", ip2long($ip) & $dmsk );
			$c = "$c & $dmsk";
			$v = $dnet;
		}elseif( $v != 'NULL' ){
			if( preg_match('/~/',$o) ){							# regexp operator?
				$c = "inet_ntoa($c)";
			}else{										# converting plain address
				$v = sprintf("%u", ip2long($v));
			}
		}
	}
	if( strstr($o, 'COL ') ){
		$o = substr($o,4);
	}elseif( $o == '=' and $v == 'NULL' ){
		$o = 'IS';
	}elseif( $o == '!=' and $v == 'NULL' ){
		$o = 'IS NOT';
	}else{
		$v = "'$v'";
	}
	if( strstr($o, '~') or strstr($o, 'LIKE') )$c = "CAST($c AS text)";
	
	return "$c $o $v";
}

//===============================================================================
// Generates SQL queries:
//
// $tbl	= table to apply query to
// $do 	s= select (is default), i=insert (using $in for columns and $st for values), o=optimize, d=delete, p=drop db
//	b=show DBs ($col used as operator with $tbl), h=show tables, c=show columns, t=truncate, u=update (using $in,$op,$st to set values 
//	and "WHERE $col $ord $lim" to match), g=group
// $col	= column(s) to display, with $do=g column(s)[;sum functions or -[;having condition]] or columns(s)#x to limit location level
// $ord	= order by (where ifname also takes numerical interface sorting (e.g. 0/1) into account)
// $lim	= limiting results
// $in,op,st	= array of columns,operators and strings to be used for WHERE in UPDATE, INSERT, SELECT and DELETE queries
// $co	= combines current values with the next series of $in,op,st
//
// SELECT and DELETE columns treatment: 
// * ip:	Input will be converted to decimal, in case of dotted notation and masked if a prefix is set.
// * time:	Time will be turned into EPOC, if it's not a number already.
// * mac:	. : - are removed
//
function GenQuery($tbl,$do='s',$col='*',$ord='',$lim='',$rawin=array(),$rawop=array(),$rawst=array(),$rawco=array(),$jn=''){

	global $debug;

	$tbl = pg_escape_string($tbl);									# Mitigate SQL injection
	$ord = pg_escape_string($ord);
	$lim = pg_escape_string($lim);
	
	$in = array_map( 'pg_escape_string', $rawin );
	$op = array_map( 'pg_escape_string', $rawop );
	$st = array_map( 'pg_escape_string', $rawst );
	$co = array_map( 'pg_escape_string', $rawco );
	if($do == 'i'){
		$qry = "INSERT INTO $tbl (". implode(',',$in) .") VALUES ('". implode("','",$st) ."')";
	}elseif($do == 'u'){# TODO refactor this and all calls (grep ",'u'," *php) and use Condition($in,$op,$st,$co,2)?
		if( $in[0] ){
			$x = 0;
			foreach ($in as $c){
				$o = ( array_key_exists($x, $op) )?$op[$x]:'=';				# Use '=' if no operator is set
				if($c){$s[]="$c $o '$st[$x]'";}
				$x++;
			}
			$qry = "UPDATE $tbl SET ". implode(',',$s) ." WHERE $col $ord $lim";
		}
	}elseif($do ==  'b'){
		$qry = "SELECT datname FROM pg_database WHERE datistemplate = false and datname $col '$tbl'";
	}elseif($do ==  'p'){
		$qry = "DROP DATABASE $tbl";
	}elseif($do ==  'h'){
		$qry = "SELECT relname from pg_stat_user_tables ORDER BY relname";
	}elseif($do ==  't'){
		$qry = "TRUNCATE $tbl";
	}elseif($do ==  'o'){
		$qry = "VACUUM $tbl";
	}elseif($do == 'c'){
		$qry = "SELECT column_name,data_type,is_nullable,'-',column_default from INFORMATION_SCHEMA.COLUMNS where table_name = '$tbl' ORDER BY ordinal_position";
	}elseif($do == 'r'){
		$qry = "VACUUM FULL $tbl";
	}elseif($do == 'v'){
		$qry = "SELECT VERSION()";
	}elseif($do == 'x'){
		$qry = "SELECT procpid,usename,datname FROM pg_stat_activity";
	}else{
		$l = ($lim) ? "LIMIT $lim" : "";
		if( strstr($ord, 'ifname') ){
			$desc = strpos($ord, 'desc')?" desc":"";
			$ord  = ($desc)?substr($ord,0,-5):$ord;						# Cut away desc for proper handling below
			$oar = explode(".", $ord);							# Handle table in join queries
			$icol = ($oar[0] == 'ifname' or $oar[0] == 'nbrifname')?'ifname':"$oar[0].ifname";
			$dcol = ($oar[0] == 'ifname' or $oar[0] == 'nbrifname')?'device':"$oar[0].device";
			$od = "ORDER BY $dcol $desc,substring($icol from '.*/')";
			#TODO rework? GH:$od = "ORDER BY $dcol $desc,SUBSTRING_INDEX($icol, '/', 1), case when SUBSTRING_INDEX($icol, '/', -1) ~ '^\d+$' then cast(SUBSTRING_INDEX($icol, '/', -1) as bigint) else 0 end";
			# Use split_part(string text, delimiter text, field int) ?
		}elseif($ord){
			$od = "ORDER BY $ord";
		}else{
			$od = "";
		}


		$w = Condition($in,$op,$st,$co,2);

		if(isset($_SESSION['view']) and $_SESSION['view'] and (strstr($jn,'JOIN devices') or $tbl == 'devices')){
			$viewq = explode(' ', $_SESSION['view']);
			$w = (($w)?"WHERE ($w) AND ":"WHERE ").AdOpVal( 'devices.'.$viewq[0],$viewq[1],$viewq[2] );
		}elseif($w){
			$w = "WHERE $w";
		}

		if($do == 'd'){
			$qry = "DELETE FROM $tbl WHERE ctid IN (SELECT ctid FROM $tbl $w $od $l)";
		}elseif($do == 's'){
			$qry = "SELECT $col FROM $tbl $jn $w $od $l";
		}else{
			$cal = '';
			$hav = '';
			if( strpos($col,'#') ){
				$xcol = explode('#',$col);
				$col = preg_replace('/(location|modloc)/',"split_part($1, '$locsep', $xcol[1])",$xcol[0] );
			}elseif( strpos($col,';') ){
				$xcol = explode(";",$col);
				$col = $xcol[0];
				if( $xcol[1] != '-'){$cal = ", $xcol[1]";}
				if(array_key_exists(2,$xcol) and $xcol[2]){$hav = "having($xcol[2])";}
			}
			$qry = "SELECT $col,count(*) as cnt$cal FROM  $tbl $jn $w GROUP BY $col $hav $od $l";
		}
	}

	if($debug){
		echo "<div class=\"textpad code pre warn xxl\">";
		debug_print_backtrace();
		echo "<p><a href=\"System-Database.php?act=c&query=".urlencode($qry)."\">$qry</a>\n";
		echo DecFix(memory_get_usage(),1).'B @'.round(microtime(1) - $debug,2)."s</div>\n";
	}

	return $qry;
}

?>
