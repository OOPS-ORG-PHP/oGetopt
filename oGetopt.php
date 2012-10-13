<?php
/**
 * Project:	oGetopt :: OOPS C library (olibc)의 o_getopt wrapping api<br>
 * File:	oGetopt.php<br>
 * Dependency: {@link ePrint} over 1.0.1
 *
 * OOPS C library의 o_getopt wrapping Class
 * 이 패키지는 getopt function의 대안을 제공한다.
 *
 * 이 패키지는 {@link ePrint} 1.0.1 이상 버전이 필요하다.
 * 
 * @category	Core
 * @package		oGetopt
 * @author		JoungKyun.Kim <http://oops.org>
 * @copyright	(c) 2012 JoungKyun.Kim
 * @version		$Id$
 * @link		http://pear.oops.org/package/oGetopt
 * @since		File available since relase 1.0.0
 * @example     pear_oGetopt/test.php 샘플 예제 코드
 * @filesource
 */

/**
 * import ePrint class
 * @see ePrint
 */
require_once 'ePrint.php';

/**
 * Base classes for oops getopt
 * @package		oGetopt
 */
class oGetopt extends ePrint {
	// {{{ properties
	/**#@+
	 * @access public
	 */
	/**
	 * 현재 옵션 값
	 * @var string
	 */
	static public $optarg;
	/**
	 * 옵션이 아닌 명령행 인자가 할당되는 배열
	 * @var array
	 */
	static public $optcmd;
	/**
	 * long 옵션 매핑 테이블
	 * @var object
	 */
	static public $longopt;
	/**
	 * 옵션이 아닌 명령행 인자의 수
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
	 * 인자의 순서를 초기화
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
	 * getopt 실행
	 *
	 * @access public
	 * @return string short option을 반환.
	 *
	 *                false를 반환할 경우, getopt 수행이 완료 되었음을 의미.
	 *                잘못된 옵션이 있을 경우, 에러 메시지 출력 후 null 반환.
	 *
	 * @param  integer 명령행 인자 수
	 * @param  array   명령행 인자 배열
	 * @param  string  옵션 형식.
	 *                 '{@link http://man.kldp.net/wiki/ManPage/getopt.3 man 3 getopt}'
	 *                 참조
	 */
	public function exec ($argc, $argv, $optstrs) {
		if ( self::$gno < 0 ) self::$gno = 1;
		if ( self::$optcno < 0 ) self::$optcno = 0;
		if ( ! isset (self::$optcno) || self::$optend < 0 )
			self::$optend = 0;
		self::$optarg = '';

		$errMark = self::asPrintf ('white', _('ERROR'));

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
					self::ePrintf (_("%s: option --%s don't support"), $errArg);
					return null;
				}

				if ( preg_match ("/{$opt}:/", $optstrs) ) {
					self::$optarg = self::$optarg ? self::$optarg : $argv[self::$gno + 1];
					if ( ! trim (self::$optarg) ) {
						self::ePrintf (_('%s: option --%s must need values'), $errArg);
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
							self::ePrintf (_('%s: option -%s must need option value'), $errArg);
							return null;
						}

						self::$optarg = $nextArg;
						self::$gno++;
					}

					if ( ! trim (self::$optarg) ) {
						self::ePrintf (_("%s: option -%s must need option value"), $errArg);
						return null;
					}
				} else {
					if ( $optvalue_c ) {
						self::ePrintf (_("%s: option -%s must have not any value"), $errArg);
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
						self::ePrintf (_("%s: option -%s don't support"), $errArg);
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
