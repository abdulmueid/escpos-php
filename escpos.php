<?php
/**
 * Class for generating ESC/POS printer control commands, as documented at the following URL:
 * http://content.epson.de/fileadmin/content/files/RSD/downloads/escpos.pdf
 * 
 * @author Michael Billington <michael.billington@gmail.com>
 * 
 * See test.php for example usage.
 * Some functions have not been implemented:
 * 		- set paper sensors
 * 		- generate pulse (for cash drawer)
 * 		- select print colour
 * 		- select character code table
 * 		- turn white/black reverse printing mode on/off
 * 		- code93 and code128 barcodes‎
 */
class escpos {
	/* ASCII codes */
	const NUL = "\x00";
	const LF = "\x0a";
	const ESC = "\x1b";
	const GS = "\x1d";
	
	/* Print mode constants */
	const MODE_FONT_A = 0;
	const MODE_FONT_B = 1;
	const MODE_EMPHASIZED = 8;
	const MODE_DOUBLE_HEIGHT = 16;
	const MODE_DOUBLE_WIDTH = 32;
	const MODE_UNDERLINE = 128;
	
	/* Fonts */
	const FONT_A = 0;
	const FONT_B = 1;
	const FONT_C = 2;
	
	/* Justifications */
	const JUSTIFY_LEFT = 0;
	const JUSTIFY_CENTER = 1;
	const JUSTIFY_RIGHT = 2;
	
	/* Cut types */
	const CUT_FULL = 65;
	const CUT_PARTIAL = 66;
	
	/* Barcode types */
	const BARCODE_UPCA = 0;
	const BARCODE_UPCE = 1;
	const BARCODE_JAN13 = 2;
	const BARCODE_JAN8 = 3;
	const BARCODE_CODE39 = 4;
	const BARCODE_ITF = 5;
	const BARCODE_CODABAR = 6;
	private $fp;
	
	/**
	 * @param resource $fp File pointer to print to
	 */
	function __construct($fp = null) {
		if(is_null($fp)) {
			$fp = fopen("php://stdout", "wb");
		}
		$this -> fp = $fp;
		
		$this -> initialize();
	}

	/**
	 * Add text to the buffer
	 * 
	 * @param string $str Text to print
	 */
	function text($str = "") {
		fwrite($this -> fp, $str);
	}

	/**
	 * Print and feed line / Print and feed n lines
	 * 
	 * @param int $lines Number of lines to feed
	 */
	function feed($lines = 1) {
		if($lines <= 1) {
			fwrite($this -> fp, self::LF);
		} else {
			fwrite($this -> fp, self::ESC . "d" . chr($lines));
		}
	}
	
	/**
	 * Select print mode(s).
	 * 
	 * Arguments should be OR'd together MODE_* constants: 
	 * MODE_FONT_A
	 * MODE_FONT_B
	 * MODE_EMPHASIZED
	 * MODE_DOUBLE_HEIGHT
	 * MODE_DOUBLE_WIDTH
	 * MODE_UNDERLINE
	 * 
	 * @param int $mode
	 */
	function select_print_mode($mode = self::NUL) {
		fwrite($this -> fp, self::ESC . "!" . chr($mode));
	}
	
	/**
	 * Turn underline mode on/off
	 * 
	 * @param int $underline 0 for no underline, 1 for underline, 2 for heavy underline
	 */
	function set_underline($underline = 1) {
		fwrite($this -> fp, self::ESC . "-". chr($underline));
	}
	
	/**
	 * Initialize printer
	 */
	function initialize() {
		fwrite($this -> fp, self::ESC . "@");
	}
	
	/**
	 * Turn emphasized mode on/off
	 * 
	 *  @param boolean $on true for emphasis, false for no emphasis
	 */
	function set_emphasis($on = false) {
		fwrite($this -> fp, self::ESC . "E". ($on ? chr(1) : chr(0)));
	}
	
	/**
	 * Turn double-strike mode on/off
	 * 
	 * @param boolean $on true for double strike, false for no double strike
	 */
	function set_double_strike($on = false) {
		fwrite($this -> fp, self::ESC . "G". ($on ? chr(1) : chr(0)));
	}
	
	/**
	 * Select character font.
	 * Font must be FONT_A, FONT_B, or FONT_C.
	 * 
	 * @param int $font
	 */
	function set_font($font = self::FONT_A) {
		fwrite($this -> fp, self::ESC . "M" . chr($font));
	}

	/**
	 * Select justification
	 * Justification must be JUSTIFY_LEFT, JUSTIFY_CENTER, or JUSTIFY_RIGHT.
	 */
	function set_justification($justification = self::JUSTIFY_LEFT) {
		fwrite($this -> fp, self::ESC . "a" . chr($justification));
	}
	
	/**
	 * Print and reverse feed n lines
	 * 
	 * @param int $lines number of lines to feed
	 */
	function feed_reverse($lines = 1) {
		fwrite($this -> fp, self::ESC . "e" . chr($lines));
	}
	
	/**
	 * Cut the paper
	 * 
	 * @param int $mode Cut mode, either CUT_FULL or CUT_PARTIAL
	 * @param int $lines Number of lines to feed
	 */
	function cut($mode = self::CUT_FULL, $lines = 3) {
		fwrite($this -> fp, self::GS . "V" . chr($mode) . chr($lines));
	}

	/**
	 * Set barcode height
	 * 
	 * @param int $height Height in dots
	 */
	function set_barcode_height($height = 8) {
		fwrite($this -> fp, self::GS . "h" . chr($height));
	}
	
	/**
	 * Print a barcode
	 * 
	 * @param string $content
	 * @param int $type
	 */
	function barcode($content, $type = self::BARCODE_CODE39) {
		fwrite($this -> fp, self::GS . "k" . chr($type) . $content . self::NUL);
	}

}
