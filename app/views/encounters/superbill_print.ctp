<?php
$dob = __date("m/d/Y", strtotime($demographics->dob));
$visit_date = __date("m/d/Y H:m", strtotime($provider->date));
ob_start();
?>
<html>
<head>
       <title>Superbill Report</title>
       <style>
       h1 {
               font-family:Georgia,serif; font-size: 12px;
               color:#000;
               font-variant: small-caps; text-transform: none; font-weight: bold;
               margin-top: 5px; margin-bottom: -5px;
       }

       h3 {
               font-family: times, Times New Roman, times-roman, georgia, serif;
               margin-top: 5px; margin-bottom: 2px;
               letter-spacing: -1px;color: #000;
       }

       b {
               font-family: Georgia,"Times New Roman",serif;
               font-size: 12px;
               font-weight: bold;
               color: #000;
               line-height: 17px;
               margin: 0;
               letter-spacing: 1px
       }
       ol {
        	margin-top: 3px;
        	margin-bottom: 1px;
        	margin-left: 0px;
       }
       /*
       li {
               margin: 1px 0px 1px 0px;
       }
	*/
	.lrg {
		font-size: 20px; font-weight:bold; font-variant: small-caps; text-transform: none;
	}
   body,table {
   font-family: "Helvetica Neue", "Lucida Grande", Helvetica, Arial, Verdana, sans-serif;
   font-size: 12px;
   color: #000;
   }

   .loading_swirl {
		display:none !important;
	} 
	
</style>
<style type="text/css">
	@media print{
	  .hide_for_print {
		display: none ;
	  }
    
    input[type=radio], input[type=checkbox] {
      display: none;
    }
    
    input, textarea {
      border: none;
    }
    
    .full-width {
      width: auto !important;
    }
    
	}
  
</style>

<script type="text/javascript" src="<?php echo $this->Html->url('/js/jquery/jquery-1.8.2.min.js'); ?>"></script>
<script type="text/javascript" src="<?php echo $this->Html->url('/js/jquery/jquery-ui-1.9.1.custom.min.js'); ?>"></script>
<script type="text/javascript" src="<?php echo $this->Html->url('/js/jquery/jquery.bubblepopup.v2.3.1.js'); ?>"></script>
<script type="text/javascript" src="<?php echo $this->Html->url('/js/jquery/jquery.keypad.min.js'); ?>"></script>
</head>
<body>
<div>			
	   <hr />
	   <table cellpadding=0 cellspacing=0 width=100% ><tr><td width=25% style="text-align:left;vertical-align:top;">
	   <?php
	   
	                    /* --------------------------------------------------------------------------------------------------------
                 	NOTE: any modifications to the code header/demographics will also likely need to be applied to report/summary.ctp
                  --------------------------------------------------------------------------------------------------------- */ 
                  
                       echo '<div class=lrg> '.$demographics->first_name.' '.$demographics->last_name.'</div>'; 
		       print 'DOB: '.$dob.' <br />';
                       $demographics->address1? printf("%s <br>", $demographics->address1 ) : '<br>';
                       $demographics->address2? printf("%s <br />", $demographics->address2 ) : '<br>';

                       $demographics->city? printf("%s, ", $demographics->city ) : '';
                       $demographics->state? printf("%s ", $demographics->state ) : '';
                       $demographics->zipcode? printf("%s", $demographics->zipcode ) : '<br />';

		$logo_image = isset($provider->logo_image)?$provider->logo_image:'';
		$corp_logo=$paths['administration'].$logo_image;
		if(is_file($corp_logo))
		{
      $corp_logo= $url_rel_paths['administration'].$logo_image;
			print '</td><td width=40% ><center><img src="'. Router::url("/", true). $corp_logo.'" ></center></td>';
		}
		else
		{
			print '</td><td width=40%>&nbsp;</td>';
		}
               ?>
			   </td><td style="text-align:right;vertical-align:top;" width="35%"><?php
			echo empty($provider->practice_name)?'': '<span class=lrg>'.ucwords($provider->practice_name). '</span><br>';
			if (!empty($provider->type_of_practice) && $provider->type_of_practice != 'Other') echo ucwords($provider->type_of_practice). '<br>';
			//echo empty($provider->description)?'': ucfirst($provider->description). '<br>';
                        //$location = $report->location;
                        echo htmlentities($location['location_name']), '<br />';
                        
                        $fullAddress = '';
                        
                        $fullAddress = htmlentities($location['address_line_1']) . '<br />';
                        
                        $addr2 = (isset($location['address_line_2'])) ? trim($location['address_line_2']) : '';
                        
                        if ($addr2) {
                            $fullAddress .= $addr2.'<br />';
                        }
                        
                        $fullAddress .= htmlentities($location['city']) .', ' . htmlentities($location['state']) . ' ' . $location['zip'];
                        
                        $fullAddress .= (isset($location['phone'])) ? '<br>'.trim($location['phone']). ' ' : '';
                        $fullAddress .= (isset($location['fax'])) ? 'Fax: '.trim($location['fax']) : '';
                        echo $fullAddress."<br/>";                       
		?>	   
	   
		</td></tr></table>
	   <hr />
</div>
<div style="text-align:center"><i>date of service: <?php echo $visit_date;?> </i></div>
<?php echo $this->element('../encounters/superbill'); ?>      
<?php echo '<div style="margin-left:5px;font-weight:bold">Provider: '.$provider_data['firstname']. ' '. $provider_data['lastname']. ' '.$provider_data['degree']. '</div>';?>

<?php if (isset($this->params['named']['autoprint'])): ?>
<script type="text/javascript">
  
    if ($.browser.mozilla) {
      window.print();
    } else {
      document.execCommand('print', false, null);
    }
  
    
</script>
<?php endif;?>
</body>
</html>
<?php
	$content = ob_get_contents();
	ob_end_clean();
  
  if (isset($this->params['named']['autoprint'])) {
    echo $content;
    die();
  }
  
  
  if (!isset($this->params['named']['cli'])) {
  
    //echo $content; exit;
    $file = 'encounter_' . $encounter_id . '_superbill.pdf';//rand(1111, 999999).
    $targetPath = $paths['temp'];  
    $targetFile = str_replace('//', '/', $targetPath) . $file;           
    site::write(pdfReport::generate($content), $targetPath . $file);

    if (!is_file($targetFile))
    {
      die("Invalid File: does not exist");
    }
    header('Content-Type: application/octet-stream; name="' . $file . '"');
    header('Content-Disposition: attachment; filename="' . $file . '"');
    header('Accept-Ranges: bytes');
    header('Pragma: no-cache');
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Content-transfer-encoding: binary');
    header('Content-length: ' . @filesize($targetFile));
    @readfile($targetFile);
    exit;    
  } else {
    echo $content;
  }

?>
