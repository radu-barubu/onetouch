<?php

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 *                                                                                         *
 *  XPertMailer is a PHP Mail Class that can send and read messages in MIME format.        *
 *  This file is part of the XPertMailer package (http://xpertmailer.sourceforge.net/)     *
 *  Copyright (C) 2007 Tanase Laurentiu Iulian                                             *
 *                                                                                         *
 *  This library is free software; you can redistribute it and/or modify it under the      *
 *  terms of the GNU Lesser General Public License as published by the Free Software       *
 *  Foundation; either version 2.1 of the License, or (at your option) any later version.  *
 *                                                                                         *
 *  This library is distributed in the hope that it will be useful, but WITHOUT ANY        *
 *  WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A        *
 *  PARTICULAR PURPOSE. See the GNU Lesser General Public License for more details.        *
 *                                                                                         *
 *  You should have received a copy of the GNU Lesser General Public License along with    *
 *  this library; if not, write to the Free Software Foundation, Inc., 51 Franklin Street, *
 *  Fifth Floor, Boston, MA 02110-1301, USA                                                *
 *                                                                                         *
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

/* Purpose:
   - set Text and HTML version of message
   - add attachment
   - embed image into HTML
*/

$mailto = $argv[1];
$subject = $argv[2];
$text2 = $argv[3];

if ($mailto && $text2 && $subject)
{

 $text2 = "<br><br>".$text2;

 // manage errors
 error_reporting(E_ALL); // php errors
 define('DISPLAY_XPM4_ERRORS', true); // display XPM4 errors
 //define('LOG_XPM4_ERRORS', serialize(array('type' => 1, 'destination' => 'errors@domain.tld', 'headers' => 'From: xpm4@domain.tld'))); // <- send mail
 //define('LOG_XPM4_ERRORS', serialize(array('type' => 3, 'destination' => '/var/tmp/XPM4.log'))); // <- append file

 // path to 'MIME.php' file from XPM4 package
 require_once './MIME.php';

 // get ID value (random) for the embed image
 $id = MIME::unique();

 // set text/plain version of message
 $text = MIME::message($text2, 'text/plain');
 // set text/html version of message
$message='
<html>
<body>
<table width=100%>
 <tr>
   <td bgcolor=black><a href="http://www.onetouchemr.com"><img src="cid:'.$id.'"></a></td>
 </tr>
 <tr>
   <td><font size="2"><b>'.$text2.'</b></font></td>
  </tr>
</table>
</body>
</html>
';

 $html = MIME::message($message, 'text/html');

 // add attachment with name 'file.txt'
 //$at[] = MIME::message('source file', 'text/plain', 'file.txt', 'ISO-8859-1', 'base64', 'attachment');
 $file = 'logo-small.png';
 // add inline attachment '$file' with name 'XPM.gif' and ID '$id'
 $at[] = MIME::message(file_get_contents($file), FUNC::mime_type($file), $file, null, 'base64', 'inline', $id);

 // compose mail message in MIME format
 $mess = MIME::compose($text, $html, $at);

 // send mail
 $send = mail($mailto, $subject, $mess['content'], 'From: sales@onetouchemr.com'."\n".$mess['header']);

	// print result
	// echo $send ? 'Sent !' : 'Error !';
	// echo "";
} else {
	echo "no text was sent over to mail out.\n";
}
?>
