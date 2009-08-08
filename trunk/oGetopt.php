<?php
/**
 * Project:	oGetopt :: o_getopt of oops c library wrapping api
 * File:	oGetopt.php
 *
 * PHP version 5
 *
 * Copyright (c) 1997-2009 JoungKyun.Kim
 *
 * LICENSE: BSD license
 *
 * @category	Core
 * @package		oGetopt
 * @author		JoungKyun.Kim <http://oops.org>
 * @copyright	1997-2009 OOPS.ORG
 * @license		BSD License
 * @version		CVS: $Id: oGetopt.php,v 1.3 2009-08-08 08:05:14 oops Exp $
 * @link		http://pear.oops.org/package/oGetopt
 * @since		File available since relase 1.0.0
 */

require_once 'ePrint.php';

/**
 * Base classes for oops getopt
 * @package		ePrint
 */
class oGetopt {
	// {{{ properties
	/**#@+
	 * @access public
	 */
	/**
	 * Current option value
	 * @var string
	 */
	static public $optarg;
	/**
	 * Arrays of command line arguments that is not option
	 * @var array
	 */
	static public $optcmd;
	/**
	 * long option mapping table
	 * @var object
	 */
	static public $longopt;
	/**
	 * Number of command line argument that is not option
	 * @var integer
	 */
	static public $optcno;
	/**#@-*/

	/**#@+
	 * @access private
	 */
	/**#@+
	 * @var integer
	 */
	/**
	 * Set 1, option command argument parsing is end
	 */
	static private $optend;
	/**
	 * command argument order
	 */
	static private $gno;
	/**#@-*/
	/**#@-*/
	// }}}

	// {{{ constructor
	/**
	 * @access public
	 * @return void
	 */
	function __construct () {
		self::init ();

		$this->optcno = &self::$optcno;
		$this->optarg = &self::$optarg;
		$this->optcmd = &self::$optcmd;
		$this->longopt = &self::$longopt;
	}
	// }}}

	// {{{ public function init ()
	/**
	 * Initialize order of arguments
	 *
	 * @access public
	 * @return void
	 */
	public function init () {
		self::$gno     = -1;
		self::$optcno  = -1;
		self::$optend  = -1;

		self::$optarg  = '';
		self::$optcmd  = array ();
		self::$longopt = (object) array ();
	}
	// }}}

	// {{{ public function exec ($argc, $argv, $optstrs)
	/**
	 * execute getopt
	 *
	 * @access public
	 * @return string return short option.<br>
	 *                If return false, end of getopt processing.
	 *                If wrong option, print error message and return null
	 * @param  integer Number of command line arguments
	 * @param  array   Command line arguments
	 * @param  string  Option format. See also 'man 3 getopt'
	 */
	public function exec ($argc, $argv, $optstrs) {
		if ( self::$gno < 0 ) self::$gno = 1;
		if ( self::$optcno < 0 ) self::$optcno = 0;
		if ( ! isset (self::$optcno) || self::$optend < 0 )
			self::$optend = 0;
		self::$optarg = '';

		$errMark = ePrint::asPrintf ('white', _('ERROR'));

		while ( true ) {
			if ( self::$gno == $argc )
				return false;

			// {{{ case by long option
			if ( preg_match ('/^--[a-z]/i', $argv[self::$gno] ) && ! self::$optend ) {
				$longops = explode ('=', $argv[self::$gno]);
				$longname = trim (substr ($longops[0], 2));
				self::$optarg = trim ($longops[1]);

				$errArg = array ($errMark, $longname);
				if ( ! ($opt = self::$longopt->$longname) ) {
					ePrint::ePrintf (_("%s: option --%s don't support"), $errArg);
					return null;
				}

				if ( preg_match ("/{$opt}:/", $optstrs) ) {
					self::$optarg = self::$optarg ? self::$optarg : $argv[self::$gno + 1];
					if ( ! trim (self::$optarg) ) {
						ePrint::ePrintf (_('%s: option --%s must need values'), $errArg);
						return null;
					}

					if ( ! preg_match ('/=/', $argv[self::$gno]) ) self::$gno++;
				}
				break;
			}
			// }}}
			// {{{ case by short option
			else if ( preg_match ('/^-[a-z]/i', $argv[self::$gno] ) && ! self::$optend ) {
				$opt = $argv[self::$gno][1];
				$optvalue_c = $argv[self::$gno][2];
				$errArg = array ($errMark, $opt);

				if ( preg_match ("/{$opt}:/", $optstrs) ) {
					if ( $optvalue_c )
						self::$optarg = substr ($argv[self::$gno], 2);
					else {
						$nextArg = $argv[self::$gno + 1];

						if ( preg_match ('/^-[a-z-]/i', $nextArg) ) {
							ePrint::ePrintf (_('%s: option -%s must need option value'), $errArg);
							return null;
						}

						self::$optarg = $nextArg;
						self::$gno++;
					}

					if ( ! trim (self::$optarg) ) {
						ePrint::ePrintf (_("%s: option -%s must need option value"), $errArg);
						return null;
					}
				} else {
					if ( $optvalue_c ) {
						ePrint::ePrintf (_("%s: option -%s must have not any value"), $errArg);
						return null;
					}

					$buf = preg_replace ('/[a-z]:/i', '', $optstrs);
					$blen = strlen ($buf);

					$_optok = 0;
					for ( $i=0; $i<$blen; $i++ ) {
						if ( $buf[$i] == $opt ) {
							$_optok++;
							break;
						}
					}

					if ( $_optok < 1 ) {
						ePrint::ePrintf (_("%s: option -%s don't support"), $errArg);
						return null;
					}
				}
				break;
			}
			// }}}
			// {{{ Case by command arg
			else {
				/*
				 * After '--' command argument, next is not options.
				 * Set self::$optend to 1.
				 */
				if ( $argv[self::$gno] == '--' ) {
					self::$optend = 1;
					continue;
				}

				self::$optcmd[self::$optcno] = $argv[self::$gno];
				self::$optcno++;
				self::$gno++;
				continue;
			}
			// }}}
		}

		self::$gno++;
		return $opt;
	}
	// }}}
}
?>
