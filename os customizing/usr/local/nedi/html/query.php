<?php
#============================================================================
# Program: query.php (NeDi DB Interface)
# Programmers: Remo Rickli & community
#
#    This program is free software: you can redistribute it and/or modify
#    it under the terms of the GNU General Public License as published by
#    the Free Software Foundation, either version 3 of the License, or
#    (at your option) any later version.

#    This program is distributed in the hope that it will be useful,
#    but WITHOUT ANY WARRANTY; without even the implied warranty of
#    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
#    GNU General Public License for more details.

#    You should have received a copy of the GNU General Public License
#    along with this program.  If not, see <http://www.gnu.org/licenses/>.
#============================================================================
# Visit http://www.nedi.ch/ for more information.
#============================================================================
#error_reporting(E_ALL);

require_once ("inc/libmisc.php");
ReadConf('usr');
require_once ("inc/libdb-" . strtolower($backend) . ".php");

#$_POST['u'] = "admin";
#$_POST['p'] = "admin";
#$_POST['m'] = "j";
#$_POST['t'] = "devices";
#$_POST['q'] = "snmpversion > 0";
#print $_SERVER['REMOTE_ADDR'];

$_GET  = sanitize($_GET);
$_POST = sanitize($_POST);
$table = isset($_GET['t']) ? $_GET['t'] : '';
$table = isset($_POST['t']) ? $_POST['t'] : $table;
$query = isset($_GET['q']) ? $_GET['q'] : '';
$query = isset($_POST['q']) ? $_POST['q'] : $query;

header("Content-type: text/plain");

$link = DbConnect( $dbhost,$dbuser,$dbpass,$dbname );
if( isset($_POST['u']) and isset($_POST['p']) ){
	$pass = hash( "sha256","NeDi".$_POST['u'].$_POST['p'] );					# Salt & pw
	$usrq = GenQuery( 'users','s','*','','',array('usrname','password','groups'),array('=','=','&'),array($_POST['u'],$pass,1),array('AND','AND') );
	$res  = DbQuery( $usrq,$link );
	$uok  = DbNumRows( $res );
	DbFreeResult( $res );
}

if( $uok == 1 ){
	if( $table ){
		$res = DbQuery( "SELECT * FROM $table".(($query)?" WHERE $query":""),$link );
		$sys = posix_uname();
		if( array_key_exists( 'domainname',$sys ) ) unset($sys['domainname']);
		$sys['nedi'] = "1.5.225";
		if( $_POST['m'] == 'csv' ){
			echo implode( ';;',$sys )."\n";
			if( $res ){
				while( $l = DbFetchRow( $res ) ){
					echo implode( ';;',$l )."\n";
				}
			}else{
				echo "ERR :CSV - ".DbError( $link );
			}
		}else{
			if( $res ){
				$rows[] = $sys;
				while( $l = DbFetchArray( $res ) ){
					$rows[] = $l;
				}
				header('Content-type: application/json');
				print json_encode($rows);
			}else{
				echo "ERR :JSON - ".DbError($link);
			}
		}
		DbFreeResult( $res );
	}else{
		echo "ERR :Missing table!";
	}
}else{
	echo "ERR :Invalid credentials!";
}
