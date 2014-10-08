<?php
App::import('Vendor', 'dompdf', array('file' =>'dompdf'.DS.'dompdf_config.inc.php'));

class PdfComponent extends Object 
{ 
	
	public static function PdfReport($html,$file_name)
	{

		$dompdf = new DOMPDF();
		$dompdf->load_html($html);
		$dompdf->set_paper('letter', 'portrait');
		$dompdf->render();
	
		return $dompdf->output(array("Attachment" => false));
	}
}


?>