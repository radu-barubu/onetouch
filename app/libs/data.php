<?php

class data {

	
	function html2Text($html)
	{
		App::import('Lib', 'html2text', array('file' => 'html2text.class.php'));
	
		// Instantiate a new instance of the class. Passing the string
		// variable automatically loads the HTML for you.
		$h2t = new html2text($html);
		
		// Simply call the get_text() method for the class to convert
		// the HTML to the plain text. Store it into the variable.
		$text = $h2t->get_text();
		
		return $text;
	}
	
	function hexDecode($encoded)
	{
		return preg_replace("'([\S,\d]{2})'e","chr(hexdec('\\1'))" ,$encoded);
	}
	
	function hexEncode($text)
	{
	   return  preg_replace("'(.)'e","dechex(ord('\\1'))",$text);
	}

	/**
	 * Decrypt given cipher text using the key with RC4 algorithm.
	 * All parameters and return value are in binary format.
	 *
	 * @param string key - secret key for decryption
	 * @param string ct - cipher text to be decrypted
	 * @return string
	*/
	public function rc4Decrypt($key, $ct) {
		return self::rc4Encrypt($key, $ct);
	}
		
	/* RC4 symmetric cipher encryption/decryption
	 * Copyright (c) 2006 by Ali Farhadi.
	 * released under the terms of the Gnu Public License.
	 * see the GPL for details.
	 *
	 * Email: ali[at]farhadi[dot]ir
	 * Website: http://farhadi.ir/
	 */
	
	/**
	 * Encrypt given plain text using the key with RC4 algorithm.
	 * All parameters and return value are in binary format.
	 *
	 * @param string key - secret key for encryption
	 * @param string pt - plain text to be encrypted
	 * @return string
	 */
	function rc4Encrypt($key, $pt) {
		$s = array();
		for ($i=0; $i<256; $i++) {
			$s[$i] = $i;
		}
		$j = 0;
		$x;
		for ($i=0; $i<256; $i++) {
			$j = ($j + $s[$i] + ord($key[$i % strlen($key)])) % 256;
			$x = $s[$i];
			$s[$i] = $s[$j];
			$s[$j] = $x;
		}
		$i = 0;
		$j = 0;
		$ct = '';
		$y;
		for ($y=0; $y<strlen($pt); $y++) {
			$i = ($i + 1) % 256;
			$j = ($j + $s[$i]) % 256;
			$x = $s[$i];
			$s[$i] = $s[$j];
			$s[$j] = $x;
			$ct .= $pt[$y] ^ chr($s[($s[$i] + $s[$j]) % 256]);
		}
		return $ct;
	}
	
	
	public  function object($array)
	{
		$data  = new data();
		
		if($array) {
			foreach($array as $k => $v) {
				$data->$k = $v;
			}
		}
		return $data;
	}
	
	public function __get($setting)
	{
		return;
	}
	
	public static function generatePassword($length	=	8)
	{
		//	start	with	a	blank	password
		$password	=	"";
		
		//	define	possible	characters	-	any	character	in	this	string	can	be
		//	picked	for	use	in	the	password,	so	if	you	want	to	put	vowels	back	in
		//	or	add	special	characters	such	as	exclamation	marks,	this	is	where
		//	you	should	do	it						
		$possible	=	"2346789bcdfghjkmnpqrtvwxyzBCDFGHJKLMNPQRTVWXYZ";
		//	we	refer	to	the	length	of	$possible	a	few	times,	so	let's	grab	it	now
								
		$maxlength	=	strlen($possible);
		//	check	for	length	overflow	and	truncate	if	necessary						
		if	($length	>	$maxlength)	{
			$length	=	$maxlength;
		}									
		//	set	up	a	counter	for	how	many	characters	are	in	the	password	so	far
							$i	=	0;													
		//	add	random	characters	to	$password	until	$length	is	reached						
		while($i < $length) {
			//	pick	a	random	character	from	the	possible	ones								
			$char	=	substr($possible,	mt_rand(0,	$maxlength-1),	1);
																					
			//	have	we	already	used	this	character	in	$password?								
			if	(!strstr($password,	$char))	{
				//	no,	so	it's	OK	to	add	it	onto	the	end	of	whatever	we've	already	got...
														
				$password	.=	$char;										
				//	...	and	increase	the	counter	by	one										
				$i++;								
			}
		}
		//	done!						
		return	$password;						
	}
        
        /**
         * Convert a date from a given format (practice setting)
         * to the standard MySQL date format(Y-m-d)
         * 
         * @param string $format Date format (from Practice Setting)
         * @param string $date Date to convert
         * @return string Equivalent date in Y-m-d format
         */
        public static function formatDateToStandard($format, $date) {
                // Create a translation map for converting date based on date format
                $map = array_flip(explode('/', $format));
                
                // Parse given date into components
                $date = explode('/', $date);
                if( count($date) == 1 ){
                	$ipadDate = explode('-', $date[0]);
                	if( count($ipadDate) == 3 ){
                		$date = array();
                		$date[$map['Y']] = $ipadDate[0];
                		$date[$map['m']] = $ipadDate[1];
                		$date[$map['d']] = $ipadDate[2];
                	}
                }
                
                // Map given date
                // We can concatenate Y-m-d directly, but it is safer
                // if we pass it to strtotime()+date() combo
                // to ensure date correctness
                return __date('Y-m-d', strtotime($date[$map['Y']] . '-' . $date[$map['m']] . '-' . $date[$map['d']]));            
        }
	
}

