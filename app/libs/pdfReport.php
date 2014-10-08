<?php

require_once(APP."libs/dompdf_0.6.0-beta3/dompdf_config.inc.php");

//require_once (APP.'libs'.DS.'dompdf-0.5.2/dompdf/dompdf_config.inc.php');

#App::import('Lib', 'DOMPDF', array('file' =>'dompdf-0.5.2/dompdf/dompdf_config.inc.php'));

class pdfReport {
	
	public static function generate($html, $orientation = "portrait")
	{

		$dompdf = new DOMPDF();
		$dompdf->load_html($html);
		$dompdf->set_paper('letter', $orientation);
		$dompdf->render();
	
		return $dompdf->output(array("Attachment" => false));
	}
	
}