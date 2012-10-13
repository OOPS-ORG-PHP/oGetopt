<?php
/**
 * OOPS C library getopt wrapping API
 *
 * oGetopt class
 *
 * PHP version 5
 *
 * LICENSE: BSD license
 *
 * @category	Core
 * @package		eGetopt
 * @author		JoungKyun.Kim <http://oops.org>
 * @copyright	1997-2009 OOPS.ORG
 * @license		BSD License
 * @version		CVS: $Id$
 * @link		http://pear.oops.org/package/oGetopt
 * @since		File available since relase 1.0.0
 */

$incs = file_exists ('./oGetopt.php') ? './' : '';
$incs .= 'oGetopt.php';
require_once "{$incs}";

echo "#############################################################\n";
echo "Command Lines:\n    ";
foreach ( $argv as $v ) {
	echo "$v ";
}
echo "\n\n";

# Initialized getopt order variables
oGetopt::init ();

oGetopt::$longopt = (object) array (
	'first'  => 'f',
	'second' => 's',
	'third'  => 't',
);

$first = false;
$third = false;
while ( ($opt = oGetopt::exec ($argc, $argv, "f:s:t")) !== false ) {
	switch ( $opt ) {
		case 'f' :
			$first = oGetopt::$optarg;
			break;
		case 's' :
			$second = oGetopt::$optarg;
			break;
		case 't' :
			$third = true;
			break;
		default :
			exit (1);
	}
}


echo "Result:\n";
echo "  Option f (value )  => $first\n";
echo "  Option s (value )  => $second\n";
echo "  Option t (flag  )  => $third\n";
echo "  Number of \$optcmd => " . oGetopt::$optcno . "\n";
echo "Print_r (oGetopt::\$optcmd):\n";
print_r (oGetopt::$optcmd);

echo "\n\n";

$getopt = new oGetopt;
# Initialized getopt order variables
#$getopt->init ();

$getopt->longopt = (object) array (
	'first'  => 'f',
	'second' => 's',
	'third'  => 't',
);

$first = false;
$third = false;
while ( ($opt = $getopt->exec ($argc, $argv, "fs:t:")) !== false ) {
	switch ( $opt ) {
		case 'f' :
			$first = true;
			break;
		case 's' :
			$second = $getopt->optarg;
			break;
		case 't' :
			$third = $getopt->optarg;
			break;
		default :
			exit (1);
	}
}


echo "Result:\n";
echo "  Option f (flag  )  => $first\n";
echo "  Option s (value )  => $second\n";
echo "  Option t (value )  => $third\n";
echo "  Number of \$optcmd => " . $getopt->optcno . "\n";
echo "Print_r (oGetopt::\$optcmd):\n";
print_r ($getopt->optcmd);

echo "#############################################################\n";
echo "\n";
?>
