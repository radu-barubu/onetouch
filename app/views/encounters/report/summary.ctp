<?php

global $global_date_format;

$isiPad = (bool) strpos($_SERVER['HTTP_USER_AGENT'],'iPad');
$isDroid = (bool) strpos($_SERVER['HTTP_USER_AGENT'],'Android');
$isiPhone = (bool) strpos($_SERVER['HTTP_USER_AGENT'],'iPhone');		

if($isiPad || $isDroid || $isiPhone)
{
  $isMobile=1;
}
else
{
  $isMobile=0;		
}

// Get date format
App::import('Model', 'PracticeSetting');
$ps = new PracticeSetting();
$pSetting = $ps->find('first');
$global_date_format = $pSetting['PracticeSetting']['general_dateformat'];

        $defaultParams = array(
            'related_information' => array(
                'cc' => 1,
		'hpi' => 1,
                'medical_history' => 1,
                'meds_allergies' => 1,
                'ros' => 1,
                'pe' => 1,
                'labs_procedures' => 1,
                'poc' => 1,
                'assessment' => 1,
                'plan' => 1,
								'vitals' => 1,            ),
        );

 if(!isset($info)) {
     $info = $defaultParams['related_information'];
 }  

 ob_start();
?>
<html>
<head>
       <title>Encounter Report</title>
       
       <style>
       .btn, a.btn {
	color: #464646;
	cursor: pointer;
	padding: 5px 6px;
	margin-right: 5px;
	text-decoration: none;
	font-weight: bold;
	//float: left;
	-moz-border-radius: 4px;
	-webkit-border-radius: 4px;
	border-radius: 4px;
	border: 1px solid #ddd;
	background: -moz-linear-gradient(center top, #fefefe, #eee) repeat scroll 0 0 transparent;
	background: -webkit-gradient(linear, left top, left bottom, from(#fefefe), to(#eee));
	filter: progid:DXImageTransform.Microsoft.gradient(startColorstr='#fefefe', endColorstr='#eeeeee');
	}
       h1 {
               font-family:Georgia,serif;
               color:#000;
               font-variant: small-caps; text-transform: none; font-weight: bold;
               margin-top: 5px; margin-bottom: -5px;
       }

       h3 {
               font-family: times, Times New Roman, times-roman, 	, serif;
               margin-top: 5px; margin-bottom: 2px;
               letter-spacing: -1px;color: #000;
       }
       ol {
        	margin-top: 3px;
        	margin-bottom: 1px;
        	margin-left: 0px;
       }
       
       ul {
               margin: 1px 0px 1px 0px;
       }
	.lrg {
		font-size: 20px; font-weight:bold; font-variant: small-caps; text-transform: none;
	}
	.reportborder { background-color:#E0F2F7; padding:7px 0px 7px 5px; font-weight:bold;}
	
       body,table {
               font-family: "Helvetica Neue", "Lucida Grande", Helvetica, Arial, Verdana, sans-serif;
       font-size: 14px;
       color: #000;
       }
		@media print{
		  .hide_for_print {
			display: none;
		  }
                  
                  a {
                      color: black;
                      text-decoration: none;
                  }
                  
		}
		.toggle-format {
			float: right;
			width: auto;
		}
		.font-size-format{
			float: right;
			width: auto;
			margin-left: 10px;
		}			
		.clear {
			clear: both;
		}
		.summarylist {
			float:left;
			width:250px;	
		}	
	</style>
	<script type="text/javascript">
		
	function isTouchEnabled(){
		return ("ontouchstart" in document.documentElement) ? true : false;
	} 
	
	var __ua = navigator.userAgent;
	var __platform = {
		iphone: __ua.match(/iPhone/),
		ipad: __ua.match(/iPad/),
		blackberry: __ua.match(/BlackBerry/),
		android: __ua.match(/Android/)
	};
	
	
function readCookie(name) {
	var nameEQ = name + "=";
	var ca = document.cookie.split(';');
	for(var i=0;i < ca.length;i++) {
		var c = ca[i];
		while (c.charAt(0)==' ') c = c.substring(1,c.length);
		if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
	}
	return null;
}	

var __isiPadApp = readCookie('iPad');

	</script>
	<?php
		echo $this->Html->script('/js/jquery/jquery.js', array('id' => 'jquery-script'));
	?>
		<script>
			(function(){
				var script = document.createElement( 'script' );
				script.type = 'text/javascript';
				script.src = '<?php echo $this->webroot ?>/js/iPad/jquery.ipadapp.js';

				if (__isiPadApp) {
					$("#jquery-script").after( script );		
				}
				
				$(function(){
					
						if (!isTouchEnabled() && !__isiPadApp) {
							$('#ccr-btn').show();
						}				
					
				});
				
			})();
			
			if (!isTouchEnabled()) {
				$(function(){

						$('.toggle-format').click(function(){
								$(this).text('Switching ....');
						});    

				});


				if (window.parent) {
						$(function(){
								$('.hot-link')
										.click(function(evt){
												evt.preventDefault();
												var url = $(this).attr('href');
												window.parent.hotLink(url);
										});
						});
				} 			
			}

		</script>       
</head>
<?php

//print_r($provider);
//print_r($user);
//print_r($demographics);



$fullname = $demographics->first_name.' '.$demographics->last_name;

if ($demographics->gender == 'M')
{
  $gendr='Male'; $prep='his';
}
 else if ($demographics->gender == 'F')
{
  $gendr='Female'; $prep='her';
}
else
{
  $gendr = ''; $prep='';
}

$dob = __date($global_date_format, strtotime($demographics->dob));
$visit_date = __date($global_date_format, strtotime($provider->date));

if (!function_exists('formVitals')) {
	function formVitals($val,$global_date_format) {
	//make date match practice settings
	if(preg_match('/\d{4}-\d{2}-\d{2}/',$val)){
	  $val =  __date($global_date_format, strtotime($val));
	}
	 return str_replace(', @ 00:00:00','',
	 str_replace('Position:','',
	 str_replace('Exact Time:','@',
	 str_replace('Location:','',
	 str_replace('Description:','',
	 str_replace('Source:','',$val))))));
	}	
}

if (!function_exists('Addendum')) {
	function Addendum($o) {
	 $stmp='';
	 $data='';
	 if(count($o->ADDENDUM) > 0) {
	 ?>
	 <p><div class="hide_for_referral">
		 <b><i>Addendum(s):</i></b><br>
	 <?php  
		foreach($o->ADDENDUM as $val) {
				 foreach($val as $key=>$value) {
					if($key == 'modified_timestamp') {
				$stmp = __date("m/d/Y H:m", strtotime($value));
			} else if ($key == 'addendum') {
				$data = $value;
			} else {
				$data = ''; $stmp = '';
			}
			if($stmp && $data)
			echo "<li> ".$stmp. ' from '. Sanitize::escape($val['user_fullname']) .' : ' . $data. '<br>';

				 }
		}
	 ?>   
	 </div>
	 <?php
	 }

	}	
}

$encounter_id =$encounter->encounter_id;

$hotlink = $this->Html->link(
               '{name}', 
               array(
                    'controller' => 'encounters',
                    'action' => 'index',
                    'task' => 'edit',
                    'encounter_id' => $encounter_id .'#{hash}'
                ), array(
                    'class' => 'hot-link'
                ));    

if (!function_exists('hotLink')) {
	function hotLink($text, $hash, $hotlink){
			return str_replace(array('{name}', '{hash}'), array(htmlentities($text), $hash), $hotlink);
	}
}


if (!function_exists('dateOrText')) {
	function dateOrText ($val) {
		global $global_date_format;

		if (preg_match('/^\d{4}\-\d{2}\-\d{2}$/', $val)) {
				$date = __date($global_date_format, strtotime($val));

				if ($date) {
						return $date;
				}
				return 'Date not specified';
		} else {
				return $val;
		}
	}
	
}

if (!function_exists('mkpretty')) {
	function mkpretty($value) {
	 //$value=strtolower($value);

	 if ( stripos($value, ' +') !== 0 && stripos($value, ' -') !== 0 )
	 {

		if($value == 'R' or $value == 'right')
			$ret = 'on Right';
		else if ($value == 'L' or $value == 'left')
			$ret = 'on Left';
		else if ($value == 'Both' or $value == 'both')
			$ret = 'on Both';
		else 
			$ret = $value;

	 } else {
		$ret ='';
	 }

	return $ret;

	}	
}

if (!function_exists('formatNo')) {
	function formatNo($val) {
	$return=false;
	//hack - sometimes mkpretty() function adds suffix before this function is called. need it removed
	$val = str_replace('on Both','', str_replace('on Left','', str_replace('on Right','',$val)));
	//how many words?
	$words=str_word_count($val);
	//only look at first word
	if($words == 1) { 
	  $suffix=substr(trim($val), -2); 
	  $suffix2=substr(trim($val), -3); 
	  $s=array('ur','ia','sh');
	  $s2=array('ous','age','ion','thy');
	  if (in_array($suffix, $s) || in_array($suffix2, $s2)) {
		 $return ="no";
	  }
	
	 //acronym's will be 1 word and all caps, and should be "NO"
	 if( strtoupper ($val) === $val) {
		$return='no';
	 }

	} else {
	//	 
	//	 $nextVal = trim($val);
	//	 $nextVal = rtrim($nextVal, '+-');
	//	 
	//	 if (is_numeric($nextVal)) {
	//		 return 'not';
	//	 }
		 
		 $return= "no";
	}
	if(!$return)
	 $return ='not';

	  return $return;
	}	
}

if (!function_exists('formatROS')) {
	function formatROS($report, $note_type) {
		$rosnegflag=0; $roscommentsflag=0; $rosflag=0;
					if(!empty($report->ROSNEGATIVE))
					{
						$rosnegflag=1;
						if($note_type == 'full') {
							echo "<div style='margin-bottom:2px'>All system reviewed and negative (except those listed in HPI)</div>";
						} else {
							echo "All system reviewed and negative (except those listed in HPI)";
						}

		 } 
		 else //if they choose ALL ROS NEGATIVE, do not print the below data
		 {
					 $ros_comments=array();
		if (!empty($report->ROSCOMMENTS) ) {
			foreach($report->ROSCOMMENTS as $kz => $vz) {
				 $vz=trim($vz);
				 if($vz) {
				$ros_comments[$kz]= $vz;
				$roscommentsflag=1;	
				 }
			}
		}

		if (!empty($report->ROS) ) {
			$rosflag=1;
								 foreach($report->ROS as $kr => $vr) {
										 if($note_type == 'full') {
												 echo "<div style='margin-bottom:2px'>";
												 echo "<span style='font-weight:bold'>".$kr."</span>: ";
										 } else {
													echo " <i><u>".strtolower($kr)."</u></i>: ";                  
										 }
										 $Rneg=array(); $Rpos=array();
										 foreach( $vr as $k2r => $v2r) {
														 // ucwords($k2r.' '.$v2r.', ');
														 if($v2r == '-')
															 $Rneg[] = ($k2r);
														 else if ($v2r == '+')
															 $Rpos[] = ($k2r);
														 else
															 continue;

										 }
										 if (sizeof($Rneg) > 0) {
												print ' <i>denies</i> '. implode(', ', $Rneg);
												if (sizeof($Rpos) > 0) print ';';

										 }
										 if (sizeof($Rpos) > 0) {
												print ' <i>positive</i> for '. implode(', ', $Rpos);
										 }


										 //tack on any ROS comments to end if exists.
										 if (@$ros_comments["$kr"]){  
											 print '. <br /><i>comments:</i><br /> '. nl2br(htmlentities($ros_comments["$kr"])) . '<br /><br />';
										 }

										 if($note_type == 'full') { print '</div>';}
								 }

					} else if (count($ros_comments) > 0) {
						 //if no standard ROS elements are selected, but they provided comment(s)
							if($note_type == 'full') {
									 echo "<div style='margin-bottom:2px'>";
							}
						 foreach ($ros_comments as $ros_comment_key => $ros_comment_val) {  
					if(trim($ros_comment_val))
					{
												 echo "<span style='font-weight:bold'>".$ros_comment_key."</span>: ";
												 print ' <i>comments:</i> '.$ros_comment_val;

								}
					}
							if($note_type == 'full') {
														echo "</div>";
										 }
				 }
				 if (empty($rosnegflag) && empty($roscommentsflag) && empty($rosflag))
				 {
					 echo 'Not Completed';
				 } 
			}
	}	
}



if (!function_exists('_make_url_clickable_cb')) {
	function _make_url_clickable_cb($matches) {
					$ret = '';
					$url = $matches[2];

					if ( empty($url) )
									return $matches[0];
					// removed trailing [.,;:] from URL
					if ( in_array(substr($url, -1), array('.', ',', ';', ':')) === true ) {
									$ret = substr($url, -1);
									$url = substr($url, 0, strlen($url)-1);
					}
					return $matches[1] . "<a href=\"$url\" target=\"_blank\">$url</a>" . $ret;
	}	
}

if (!function_exists('_make_email_clickable_cb')) {
	function _make_email_clickable_cb($matches) {
					$email = $matches[2] . '@' . $matches[3];
					return $matches[1] . "<a href=\"mailto:$email\">$email</a>";
	}	
}

if (!function_exists('make_clickable')) {
	function make_clickable($ret) {
					$ret = ' ' . $ret;
					// in testing, using arrays here was found to be faster
					$ret = preg_replace_callback('#([\s>])([\w]+?://[\w\\x80-\\xff\#$%&~/.\-;:=,?@\[\]+]*)#is', '_make_url_clickable_cb', $ret);
					//$ret = preg_replace_callback('#([\s>])((www|ftp)\.[\w\\x80-\\xff\#$%&~/.\-;:=,?@\[\]+]*)#is', '_make_web_ftp_clickable_cb', $ret);
					$ret = preg_replace_callback('#([\s>])([.0-9a-z_+-]+)@(([0-9a-z-]+\.)+[0-9a-z]{2,})#i', '_make_email_clickable_cb', $ret);

					// this one is not in an array because we need it to run last, for cleanup of accidental links within links
					$ret = preg_replace("#(<a( [^>]+?>|>))<a [^>]+?>([^>]+?)</a></a>#i", "$1$3</a>", $ret);
					$ret = trim($ret);
					return $ret;
	}	
}

?>
<body>
       <div class="hide_for_referral">
			<div class="hide_for_print reportborder"> <a class=btn href="javascript:window.print()">Print <img src="<?php echo Router::url("/", true).'img/printer_icon_small.png'; ?>"  style="vertical-align:bottom;padding-left:3px"></a>  <span style="margin-left:20px"> <a class=btn href="<?php echo $html->url(array('controller' => ($isPatient) ? 'dashboard' : 'encounters', 'action' => 'superbill', 'encounter_id' => $demographics->encounter_id, 'task' => 'get_report_pdf')); ?>/?format=<?php echo $dofull ? 'full': 'soap'; ?>" target="_top">PDF <img src="<?php echo Router::url("/", true).'img/pdf.png'; ?>" style="vertical-align:bottom;padding-left:3px"></a></span><span style="margin-left:20px"> <a class=btn href="<?php echo $html->url(array('controller' => ($isPatient) ? 'dashboard' : 'encounters', 'action' => 'superbill', 'encounter_id' => $demographics->encounter_id, 'task' => 'get_report_pdf','view' => 'fax')); ?>/?format=<?php echo $dofull ? 'full': 'soap'; ?>" target="_top">FAX </a></span>  
                            <span id="ccr-btn" style="margin-left:20px; display: none;"><a class=btn href="<?php echo $html->url(array('controller' => ($isPatient) ? 'dashboard' : 'encounters', 'action' => 'superbill', 'encounter_id' => $demographics->encounter_id, 'task' => 'get_report_ccr')); ?>">CCR <img src="<?php echo Router::url("/", true).'img/exchange_icon.png'; ?>" style="vertical-align:bottom;padding-left:3px"></a></span> 


                           <a href="javascript:void(0)" class="font-size-format btn increaseFont"><img src="<?php echo Router::url("/", true).'img/ico-font-increase.png'; ?>"  style="vertical-align:bottom;padding-left:3px"></a> <a href="javascript:void(0)" class="font-size-format btn decreaseFont"><img src="<?php echo Router::url("/", true).'img/ico-font-decrease.png'; ?>"  style="vertical-align:bottom;padding-left:3px"></a>
                           
                            <?php if (!$isPatient): // Patients should not be able to change format?> 
                            <?php 
                             if (!$dofull):
                            
                                $url = $this->Html->url(array(
                                    'controller' => 'encounters',
                                    'action' => 'superbill',
                                    'encounter_id' => $encounter_id,
                                    'task' => 'get_report_html',
                                    'phone' => $phone
                              
                            ));  
                            
                            else:
                       
                                $url = $this->Html->url(array(
                                    'controller' => 'encounters',
                                    'action' => 'superbill',
                                    'encounter_id' => $encounter_id,
                                    'task' => 'get_report_html',
				    'phone' => $phone
                                    ));
                                   endif;  
                            ?> 
                            
                            <?php if ($dofull): ?> 
                            <a href="<?php echo $url ?>/?format=soap" class="btn toggle-format">View SOAP Format</a>
                            <?php else:?> 
                            <a href="<?php echo $url ?>/?format=full" class="btn toggle-format">View Full H&amp;P Format</a>
                            <?php endif;?> 
                            <br class="clear">
                            <?php endif;?> 
                        </div>

               <hr />
              
			   <table cellpadding=0 cellspacing=0 width=100%><tr><td width=25% style="text-align:left;vertical-align:top;">
                <?php
               
                 /* --------------------------------------------------------------------------------------------------------
                 	NOTE: any modifications to the code header/demographics will also likely need to be applied to superbill_print.ctp
                  --------------------------------------------------------------------------------------------------------- */ 
                        echo '<div class=lrg> '.$demographics->first_name.' '.$demographics->last_name.'</div>'; 
		       print 'DOB: '.$dob.' <br />';
			$demographics->custom_patient_identifier? printf("ID: %s, ", $demographics->custom_patient_identifier ) : '';
		       print 'MRN: '.$demographics->mrn. '<br />';
                       $demographics->address1? printf("%s <br>", $demographics->address1 ) : '<br>';
                       $demographics->address2? printf("%s <br />", $demographics->address2 ) : '<br>';

                       $demographics->city? printf("%s, ", $demographics->city ) : '';
                       $demographics->state? printf("%s, ", $demographics->state ) : '';
                       $demographics->zipcode? printf("%s <br />", $demographics->zipcode ) : '<br />';


		$corp_logo = $url_abs_paths['administration'].'/'.$provider->logo_image;
		if (trim($provider->override_practice_logo)) {
			
			if (file_exists($paths['temp'].'/'.$provider->override_practice_logo)) {
				$corp_logo = $url_abs_paths['temp'].'/'.$provider->override_practice_logo;
			} elseif (file_exists($paths['preferences'].'/'.$provider->override_practice_logo)) {
				$corp_logo = $url_abs_paths['preferences'].'/'.$provider->override_practice_logo;
				
			}
			
			
		} else {
			$corp_logo = $url_abs_paths['administration'].'/'.$provider->logo_image;
		}
											 
		if(is_file($corp_logo))
		{
			print '</td><td width=40% ><center><img src="'.Router::url("/", true).$corp_logo.'"></center></td>';
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
                        $location = $report->location;
                        echo htmlentities($location['location_name']), '<br />';
                        
                        $fullAddress = '';
                        
                        $fullAddress = htmlentities($location['address_line_1']) . '<br />';
                        
                        $addr2 = (isset($location['address_line_2'])) ? trim($location['address_line_2']) : '';
                        
                        if ($addr2) {
                            $fullAddress .= $addr2 . '<br />';
                        }
                        
                        $fullAddress .= htmlentities($location['city']) .', ' . htmlentities($location['state']) . ' ' . $location['zip'];
                        $fullAddress .= (isset($location['phone'])) ? '<br>'.trim($location['phone']). ' ' : '';
                        $fullAddress .= (isset($location['fax'])) ? 'Fax: '.trim($location['fax']) : '';
                        echo $fullAddress;                       
                        
		?>
				</td></tr></table>
               <hr />
       </div>
	<div class="hide_for_referral" style="text-align:center"><i>date of service: <?php echo $visit_date;?> </i></div>
        
        <!--[BEGIN_CC]-->
					<?php if (isset($info['cc']) && $info['cc']): ?> 
           <h3><?php echo hotLink($tabNameMap['CC'], 'cc', $hotlink);?></h3>
					 <div style="margin-left: 15px">
						 <?php
                   if($report->CC) {

                           echo "This is a ".$demographics->age." old ".$gendr. " ";

                           $ttl_cc=count($report->CC);
                           $ttl_cc1=$ttl_cc - 1;

                       if(empty($ttl_cc1)) //if only 1 complaint
                       {
                         if(stristr($report->CC[0],'follow') OR stristr($report->CC[0],'f/u'))
                         {
                             echo ' here for '.trim($report->CC[0]).'.';                     
                         }
                         else
                         {
                            echo ' with "'  . trim($report->CC[0]).'"';                    
                         }


                       } else { //if more than 1 complaint
                           $ccout='';
                           for ($i = 0; $i < $ttl_cc; $i++)
                           {
                               if(empty($i))
                                   $ccout .= '';
                               else if ($i == $ttl_cc1)
                                   $ccout .= ' and ';
                               else
                                   $ccout .= ', ';

                                   $ccout .= ucfirst($report->CC[$i]) ;

                           }
                            echo 'with "'.$ccout.'"';
                      }

                        if ($report->CCInfo['EncounterChiefComplaint']['no_more_complaint']) {
                            echo '  No other complaints. ';
                        }

                        if($report->Hx_Source)
                        {
                            echo " [Source: ".$report->Hx_Source . '] ';
                        }

			   echo (isset($report->CC_Scribe))? $report->CC_Scribe:'';

                   } else {
										 echo 'None.';
									 }						 
						 ?>
					 </div>
					<?php endif;?> 
        <!--[END_CC]-->
				
				<!--[BEGIN_HPI]-->
				<?php if (isset($info['hpi']) && $info['hpi']): ?>
           <h3><?php echo hotLink($tabNameMap['HPI'], 'hpi', $hotlink);?></h3>		
					 <div style="margin-left: 15px">
						 <?php
						 
								if ($report->hpi) {
													 if(!$isPatient) 
													 {
															 print '<p>';
															 foreach($report->hpi as $v) {

									 echo '<p>';
																		 if(!empty($v['free_text']))
																		 {
																						 if (substr($v['free_text'], -1) != '.' && strpos($v['free_text'], "\n") === FALSE ) //does a period already exist at end?
																								$period='.';
																						 else
																								$period='';


										//if more than 1 HPI, prefix the free text with each complaint so its not confusing
										if(count($report->hpi) > 1 && $report->CCInfo['EncounterChiefComplaint']['multiple_hpi_flag'] == '1' ):
										 echo ucfirst($v['chief_complaint']). ': ';
										endif;

																						 echo ucfirst(nl2br(htmlentities($v['free_text']))).$period;
																		 }

																		 if($v['duration'] OR $v['duration_date'] OR $v['location'] OR $v['quality'] OR $v['timing'] OR $v['severity'] OR $v['associated_sign_symptom'] OR $v['context_other'] OR $v['modifying_factors'])
																		 {
			if(!empty($v['free_text'])) {
			  echo '<br /><br />';
			}																	$isAre = 'is';
										if(empty($v['free_text'])) // only need to print this if no HPI free text is defined since it already prefix's the complaint
										{										 

										if ($report->CCInfo['EncounterChiefComplaint']['multiple_hpi_flag'] == '0') {
											print ' The ' .$report->Hx_Source .' reports the complaints ';
										$isAre = 'are';
										} else if(!stristr($v['chief_complaint'],'follow') AND !stristr($v['chief_complaint'],'f/u')){
											print ' The ' .$report->Hx_Source .' reports the complaint of '.$v['chief_complaint'].'  <br />';
										} else {
											print ' The ' .$report->Hx_Source .' reports '.$v['chief_complaint'].' <br />';
										}
									 }									 

																				 ////////////////////////////////////////
																				 $hpi_elements=array();
																				 if($v['duration']) { 
																							$hpi_elements['Duration']= trim(empty($v['duration'])?'': $v['duration']. ' ' . $v['duration_length']. ' ago');
																				 } else if ($v['duration_date']) { 
																							$hpi_elements['Duration']=  trim(empty($v['duration_date'])?'': __date("m/d/Y", strtotime($v['duration_date']))); 
																				 }
																							$hpi_elements['Location']=  trim(empty($v['location'])?'' : $v['location']);
																							$hpi_elements['Quality']=  trim(empty($v['quality'])?'' : $v['quality']);
																							$hpi_elements['Timing']=  trim(empty($v['timing'])?'':' '.$v['timing']);
																							$hpi_elements['Severity']=  trim(empty($v['severity'])?'' : $v['severity']. '/10 at its most intense');
																							$hpi_elements['Associated Sign/Symptom']=  trim(empty($v['associated_sign_symptom'])?'':$v['associated_sign_symptom']);
																							$hpi_elements['Context']=  trim(empty($v['context_other'])?'':$v['context_other']);
																							$hpi_elements['Modifying Factors']=  trim(empty($v['modifying_factors'])?'':'  '.$v['modifying_factors']);

																					//   if (substr($v['modifying_factors'], -1) != '.') //does a period already exist at end?
																					//       print '.';
																				 ////////////////////////////////////////  
																				 foreach ($hpi_elements as $element => $val) {
																					 $val = trim($val);
																					 if (empty($val)) {
																						 continue;
																					 }
																					 
																					 echo '<strong>'.$element.'</strong> <br />';
																					 echo nl2br(htmlentities($val));
																					 echo '<br /><br />';
																				 }

																	 }

																	 if ($report->CCInfo['EncounterChiefComplaint']['multiple_hpi_flag'] == '0') {
																		 break;
																	 }
														 }

														 //echo '<br /><br /><br />';
														 if($report->hpi_advice) {
																		 foreach($report->hpi_advice as $val1) {
																							$val1 = nl2br(htmlentities($val1));
																						 echo empty($val1)?'':' '.ucfirst($val1);
																						 if (substr($val1, -1) != '.' && !empty($val1)) //does a period already exist at end?
																							 print '.';
																						 //echo '<br /><br />';
																		 }
														 }

														 if (!$dofull) {
																// formatROS($report, "soap");
														 }
									}									
								} else {
									echo 'None';
								}
						 
						 
						 
echo (isset($report->hpi_Scribe))?'<br>'.$report->hpi_Scribe:'';						 
						 ?>
					 </div>
				<?php endif;?>
				<!--[END_HPI]-->
				
<?php if (($dofull && $phone != 'yes') ||  $isPatient) { //if a full h&p AND not phone encounter; Patients should see this ?>				
		<!--[BEGIN_MEDS_ALLERGY]-->
       <?php if (isset($info['meds_allergies']) && $info['meds_allergies']): ?> 
           <h3><?php echo hotLink('Medication', 'meds_allergies', $hotlink);?></h3>
              <div style='margin-left: 15px'>
           <?php
                   if($report->medication) {
                       $meds = $report->medication;
					   
					   // Display active medications only. Do not include any medications ordered in Plan/Rx from this visit.
					   $new_meds = array();
					   
					   foreach($meds[0] as $meds_info)
					   {
						   $erx_found = false;
						   
						   if(count($report->GetDosespotMedication) > 0)
						   {  
							   foreach($report->GetDosespotMedication as $erx_meds)
							   {
									if($erx_meds['dosespot_medication_id'] == $meds_info['PatientMedicationList']['dosespot_medication_id'])
									{
										$erx_found = true;
										break;
									}
							   }
						   }
						   
						   if(!$erx_found)
						   {
							   $new_meds[] = $meds_info;
						   }
					   }
					   
					   $meds[0] = $new_meds;
					   //End
					   
					   // Count how many chunks we have
                       $totalCols = count($meds);
                        // Count large possible size of chunk
                       $totalRows = count($meds[0]);
						
					   if(count($meds[0]) > 0) {
                       ?>
															<table>
																<tr>
                                <?php for($col = 0; $col < $totalCols; $col++): ?> 
																	<td width="<?php echo floor(99/$totalCols); ?>%" style="vertical-align: top;">
                                        <?php foreach($meds[$col] as $m): ?> 
                                        <li>
                                            <?php 
                                                $m = $m['PatientMedicationList'];
                                               echo htmlentities($m['medication']); 
                                            ?>
                                            &nbsp;
                                            <?php if (intval($m['quantity'])): ?> 
                                            <em><?php
                                                echo htmlentities($m['quantity'] .' ' . $m['unit'] . ' ' . $m['route'] . ' ' . $m['frequency'] . ' '.$m['rx_alt']);
                                            ?></em>
                                            <?php endif; ?> 
											<em><?php echo ($m['quantity'] && $m['direction'])? ', ' : ''; echo htmlentities($m['direction']); ?></em>
                                        </li>
                                        <?php endforeach;?> 
																	</td>
                                <?php endfor;?> 
																</tr>
															</table>
																				
                                    
                                <br style="clear: left;" />
                       <?php
					   } else {
						   echo 'None.';
					   }
					   			   
                   } else {
                       echo 'None.';
                   }
			//print who reconciled med list if present
			if($report->reconciled_user)
			{
				echo " <br /><em>Reconciled by: ";
				for($m=0;$m<count($report->reconciled_user);$m++) {
				   echo trim($report->reconciled_user[$m]);
				   if($m < (count($report->reconciled_user) - 1))
				      echo ', '; 
				}
				echo '</em>';
			}                   
           ?>
           </div>           
       <?php endif;?>
           
       <?php if (isset($info['meds_allergies']) && $info['meds_allergies']): ?>
           <h3><?php echo hotLink('Allergies', 'meds_allergies', $hotlink);?></h3>
           <div style='margin-left: 15px'>
           <?php
                    if($report->allergies) {

                           $allergies = $report->allergies;
                           // Count how many chunks we have
                           $totalCols = count($allergies);
                            // Count large possible size of chunk
                           $totalRows = count($allergies[0]);
														?><table style="width: 99%"><tr>
                                    <?php for($col = 0; $col < $totalCols; $col++): ?> 
																<td style="width: <?php echo floor(99/$totalCols); ?>%; vertical-align: top;"><ul>
                                            <?php foreach($allergies[$col] as $a): ?> 
                                            <li>
                                                <?php 
                                                    	$a = $a['PatientAllergy'];
                                                	echo htmlentities(ucwords($a['agent']));
													$reaction_array = "";
													for ($i = 1; $i <= $a['reaction_count']; ++$i)
													{
														if (trim($a['reaction'.$i]))
														{
															if ($a['severity'.$i])
															{
																$reaction_array[] = '<em>'.strtolower(htmlentities($a['severity'.$i])).'</em> '.trim(htmlentities(ucwords($a['reaction'.$i])));
															}
															else
															{
																$reaction_array[] = trim(htmlentities(ucwords($a['reaction'.$i])));
															}
														}
													}
													if ($reaction_array)
													{
														echo " (".implode(", ", $reaction_array).")";
													}
                                                ?>
                                            </li>
                                            <?php endforeach;?> 
																	</ul></td>
                                    <?php endfor;?> 
															</tr></table>
                                    <br style="clear: left;" />
                           <?php                       








		   } else if ($encounter->allergy_none ) {
		   	  echo "NKA";
                   } else {
                          echo "None Given.";
                   }
           ?>
           </div>           
       <?php endif;?> 
       <!--[END_MEDS_ALLERGY]-->
<?php } ?>       
<?php if ($dofull && !$isPatient && $phone != 'yes') { //if a full h&p AND NOT Patient Role AND not phone encounter ?>
        
		<!--[BEGIN_HX]-->
        <?php if (isset($info['medical_history']) && $info['medical_history']): ?>
					<?php if (isset($subHeadings['HX']['Medical History']['hide']) && !intval($subHeadings['HX']['Medical History']['hide'])): ?> 
						<h3><?php echo hotLink(isset($subHeadings['HX']['Medical History']['name']) ? $subHeadings['HX']['Medical History']['name'] : 'Past Medical History' , 'hx:medical', $hotlink);?></h3>
						 <div style='margin-left: 15px'>
						 <?php
						 if($report->medical_history) {

												 $medHis = $report->medical_history;
												 // Count how many chunks we have
												 $totalCols = count($medHis);
													// Count large possible size of chunk
												 $totalRows = count($medHis[0]);

												 ?><table style="width: 99%"><tr>
																	<?php for($col = 0; $col < $totalCols; $col++): ?> 
														 <td style="width: <?php echo (99/$totalCols); ?>%; vertical-align: top;">
																					<?php foreach($medHis[$col] as $m): ?> 
															 &bull;
																							<?php 
																									$m = $m['PatientMedicalHistory'];

																								 echo htmlentities(ucwords($m['diagnosis']));

																								 if(@!strstr($m['diagnosis'], $m['icd_code'])) //if icd9 doesn't already exist

																										echo empty($m['icd_code'])?'':' [' . $m['icd_code'].']';

																								 echo empty($m['status'])?'':' (' . $m['status'].')';

																							?>
																							&nbsp;

																					<br />
																					<?php endforeach;?> 
															 </td>
																	<?php endfor;?> 
													 </tr>
												 </table>
																	<br style="clear: left;" />
												 <?php               


						 } else {
							 print 'None or Non-Contributory.';
						 }
						 ?>
																	
							<?php if (isset($report->reconciliationInfo['hx_medical']) && !empty($report->reconciliationInfo['hx_medical'])): ?> 
							<br />
							<em>Reconciled by: </em> <?php echo implode(', ', $report->reconciliationInfo['hx_medical']); ?>
							<br />
							<?php endif;?> 
																	
																	
																	
						 </div>					
					<?php endif;?>

					 
					<?php if (isset($subHeadings['HX']['Ob/Gyn History']['hide']) && !intval($subHeadings['HX']['Ob/Gyn History']['hide'])): ?> 
           <?php if (intval($provider->obgyn_feature_include_flag) == 1 && $demographics->gender == 'F')
	        { 
	        ?>
           <h3><?php echo hotLink(isset($subHeadings['HX']['Ob/Gyn History']['name']) ? $subHeadings['HX']['Ob/Gyn History']['name'] : 'Ob/Gyn History', 'hx:obgyn', $hotlink);?></h3>
           <div style='margin-left: 15px'>
           <?php if($report->obgyn_history): ?>
               
           <?php 
                $ignoreFields = array(
                    'ob_gyn_history_id', 'patient_id', 'encounter_id', 'type', 
                    'date_modified', 'modified_timestamp', 'modified_user_id',
                );
           
           ?> 
                    <ul style="margin-left: 15px; padding-left: 0;">
                    <?php foreach ($report->obgyn_history as $obgynType => $obgyn):?>
                        <li style="margin-left: 0;">
                                <?php echo $obgynType; ?>

                            <ul>
                                <?php foreach ($obgyn as $field => $value): ?> 

																<?php 
																	if ($field == 'Deliveries') {
																		
																		$deliveries = json_decode($value, true);
																		
																		if (!$value) {
																			continue;
																		}
																		
																?>
															<li>
																Deliveries
																<ul>
																		<?php foreach($deliveries as $d): ?>
																	<li>
																		<em>Type:</em> <?php echo htmlentities($d['type']); ?>
<?php if(trim($d['weight'])): ?>, <?php echo htmlentities($d['weight']); echo ($pSetting['PracticeSetting']['scale']=='English') ? ' lb(s)':' gram(s)';  ?>
<?php echo (isset($d['ounces']) && !empty($d['ounces']))? ' '.htmlentities($d['ounces']).' ounce(s)' : ''; ?>
<?php endif;?> 
																		<?php $_date =__date('F j, Y', strtotime($d['date'])); 
																				if(trim($_date)):?>
																		<em>date:</em> <?php echo dateOrText($d['date']); ?> 
																		<?php endif;?> 
																	</li>
																		<?php endforeach;?>
																</ul>
															</li>
																<?php
																		continue;
																	}
																?> 
															
                                <li>
                                  <?php 
                                  
                                    if ($field == 'Pregnancy Comment') {
                                      $field = 'Comments';
                                    }
                                  ?>
                                  <?php echo $field ?>:
                                    <?php if (is_array($value)): ?> 
                                    
                                    <?php $txt = array();?> 
                                    <?php foreach ($value as $subfield => $subvalue): ?> 
                                    <?php $txt[] = dateOrText($subvalue);?>  
                                    <?php endforeach;?>
                                    
                                    <?php echo implode(', ', $txt);?> 
                                    
                                    <?php else: ?> 
                                    <?php echo dateOrText($value);?> 
                                    <?php endif; ?> 
                                </li>
                                <?php endforeach;?> 
                            </ul>
                            
                        </li>
                    <?php endforeach;?> 
                    </ul>
           <?php else: ?>
             <?php print 'None or Non-Contributory.'; ?>
           <?php endif; ?>
           </div> 
					 
							<?php if (isset($report->reconciliationInfo['hx_obgyn']) && !empty($report->reconciliationInfo['hx_obgyn'])): ?> 
							<br />
							<em>Reconciled by: </em> <?php echo implode(', ', $report->reconciliationInfo['hx_obgyn']); ?>
							<br />
							<?php endif;?> 					 
					 
					 
           <?php  } ?>					
					<?php endif;?>
					 

					 <div style="clear:left"></div>   
					 
					<?php if (isset($subHeadings['HX']['Surgical History']['hide']) && !intval($subHeadings['HX']['Surgical History']['hide'])): ?> 
						 <h3><?php echo hotLink(isset($subHeadings['HX']['Surgical History']['name']) ? $subHeadings['HX']['Surgical History']['name'] : 'Surgical History', 'hx:surgical', $hotlink);?></h3>
						 <div style='margin-left: 15px'>
						 <?php
						 if($report->surgical_history) {
												 $surgicalHis = $report->surgical_history;
												 // Count how many chunks we have
												 $totalCols = count($surgicalHis);
													// Count large possible size of chunk
												 $totalRows = count($surgicalHis[0]);

												 ?><table style="width: 99%"><tr>
																	<?php for($col = 0; $col < $totalCols; $col++): ?> 
														 <td style="width: <?php echo (99/$totalCols); ?>%; vertical-align: top;">
																					<?php foreach($surgicalHis[$col] as $s): ?> 
															 &bull;
																							<?php 
																									$s = $s['PatientSurgicalHistory'];
																								 echo empty($s['surgery'])?'':''.ucwords($s['surgery']);
																								 //echo $s['date_from'] == '0000'?'':' ('.date("Y", strtotime($s['date_from'])) . ')' ;
													 if ($s['date_from'] != '0000' || $s['date_to'] != '0000')
													 {
														echo ' (';
															echo $s['date_from'] == '0000'?'':$s['date_from']; 
														if ($s['date_from'] != '0000'  && $s['date_to'] != '0000')
														{
															echo ' - ';
														}
															echo $s['date_to'] == '0000'?'':$s['date_to']; 
														echo ')';
													 }

						//show reason if present
						if(!empty($s['reason'])) {
						  print ' <br> &nbsp&nbsp; <i>Reason:</i> '.$s['reason'].' ';
						}
                                                //show outcome if present
                                                if(!empty($s['outcome'])) {
                                                  print ' <br> &nbsp&nbsp; <i>Outcome:</i> '.$s['outcome'].' ';
                                                }
																							?>
																					<br />
																					<?php endforeach;?> 
																			</td>
																	<?php endfor;?> 
													 </tr></table>
																	<br style="clear: left;" />
												 <?php                
						 } else {
							 print 'None or Non-Contributory.';
						 }
						 ?>
							<?php if (isset($report->reconciliationInfo['hx_surgical']) && !empty($report->reconciliationInfo['hx_surgical'])): ?> 
							<br />
							<em>Reconciled by: </em> <?php echo implode(', ', $report->reconciliationInfo['hx_surgical']); ?>
							<br />
							<?php endif;?> 																	
						 </div>					
					<?php endif;?>
					 


					<?php if (isset($subHeadings['HX']['Social History']['hide']) && !intval($subHeadings['HX']['Social History']['hide'])): ?> 
						 <h3><?php echo hotLink(isset($subHeadings['HX']['Social History']['name']) ? $subHeadings['HX']['Social History']['name'] : 'Social History', 'hx:social', $hotlink);?></h3>
						 <div style='margin-left: 15px'>
						 <?php
						 if($report->social_history) {
										 foreach($report->social_history as $vsx) {
														 // eventually need to make this so that if more than 5, start a new row on right
														 foreach($vsx as $k => $gynhx) {
								 if(isset($gynhx['PatientSocialHistory']))
										$gynhx = $gynhx['PatientSocialHistory'];
																		 echo "<li>";
																																 if(isset($gynhx['type']) && $gynhx['type']) {
																																		echo $gynhx['type'].": ";
																																	 if($gynhx['type'] == "Occupation") {
																																		 echo empty($demographics->occupation)?'':''.ucwords($demographics->occupation);

																																	 }
																																	 if($gynhx['type'] == "Marital Status") {
																																		 echo empty($demographics->marital_status)?'':''.ucwords($demographics->marital_status);
																																	 }
																																	 if($gynhx['type'] == "Pets") {
																																		 $gynhx['pets']=trim($gynhx['pets']);
																																		 if($gynhx['pets']) {
																																			 $petsline = null ;
																																			 //$petsline = str_replace("|", ", ", $gynhx['pets'] );
																																			 //echo str_replace(", ", " ", $petsline);
																																			 $petsline=explode('|', $gynhx['pets']);
																																			 if(count($petsline) > 0)
																																			 {
																																				$petsline=array_filter($petsline);
																																				echo join(', ', $petsline);
																																			 }
																																		 }
																																	 }
																																 }
																																 echo empty($gynhx['living_arrangement'])?'':''.ucwords($gynhx['living_arrangement']);

																		 echo empty($gynhx['substance'])?'':''.ucwords($gynhx['substance']);
																																 echo empty($gynhx['routine'])?'':' ('.ucwords($gynhx['routine'].')');
																		 //echo empty($gynhx['routine_status'])?'':' ('.ucwords($gynhx['routine_status'].')');
																		 echo empty($gynhx['consumption_status'])?'':' ('.ucwords($gynhx['consumption_status'].')');
																		 echo empty($gynhx['smoking_status'])?'':' ('.ucwords($gynhx['smoking_status'].')');
										 echo (isset($gynhx['type']) && $gynhx['type'] == "Other")? $gynhx['comment'] : '';
																		 echo "</li>";
														 }
										 }
						 } else {
							 print 'None or Non-Contributory.';
						 }
						 ?>
							<?php if (isset($report->reconciliationInfo['hx_social']) && !empty($report->reconciliationInfo['hx_social'])): ?> 
							<br />
							<em>Reconciled by: </em> <?php echo implode(', ', $report->reconciliationInfo['hx_social']); ?>
							<br />
							<?php endif;?> 							 
						 </div>					
					<?php endif;?>
						 
						 
						 
					<?php if (isset($subHeadings['HX']['Family History']['hide']) && !intval($subHeadings['HX']['Family History']['hide'])): ?> 
						 <h3><?php echo hotLink(isset($subHeadings['HX']['Family History']['name']) ? $subHeadings['HX']['Family History']['name'] : 'Family History', 'hx:family', $hotlink);?></h3>
						 <div style='margin-left: 15px'>
						 <?php
						 if($report->family_history) {
										 foreach($report->family_history as $vf) {

														 // eventually need to make this so that if more than 5, start a new row on right
														 foreach($vf as $v2f) {
															 //if(!empty($v2f['problem']) && !empty($v2f['relationship']))
																 //if(!empty($v2f['relationship']))
																 //{
																		 echo "<li>";
																		 echo empty($v2f['PatientFamilyHistory']['problem'])?'':ucwords($v2f['PatientFamilyHistory']['problem']) . ':  ';
																																		 echo ucwords($v2f['PatientFamilyHistory']['relationship']);
																		 echo empty($v2f['PatientFamilyHistory']['status'])?'':' ('.ucwords($v2f['PatientFamilyHistory']['status'].')');
																		 echo empty($v2f['PatientFamilyHistory']['comment'])?'': ' ' .$v2f['PatientFamilyHistory']['comment'];
																		 echo "</li>";

																 //}
														 }
										 }

						 } else {
							 print 'None or Non-Contributory.';
						 }
						 ?>
							<?php if (isset($report->reconciliationInfo['hx_family']) && !empty($report->reconciliationInfo['hx_family'])): ?> 
							<br />
							<em>Reconciled by: </em> <?php echo implode(', ', $report->reconciliationInfo['hx_family']); ?>
							<br />
							<?php endif;?> 							 
						 </div>					
					<?php endif;?>
        <?php endif;?>
        <!--[END_HX]-->

       <?php if($phone != 'yes'): ?>
	   <!--[BEGIN_ROS]-->
       <?php if (isset($info['ros']) && $info['ros']): ?>
           <h3><?php echo hotLink($tabNameMap['ROS'], 'ros', $hotlink);?></h3>
           <div style='margin-left: 15px'>
           <?php
           if($report->ROS || $report->ROSNEGATIVE || $report->ROSCOMMENTS) {
             formatROS($report, "full");
           } else {
             print 'Not Completed.';
           }
           ?>
           </div>           
       <?php endif;?>
	   <!--[END_ROS]-->
       <?php endif;?>


<?php } // end if a full note ?>

       <?php if ($phone != 'yes'): ?>
	   
	   <!--[BEGIN_VITALS]-->
	   <?php if (isset($info['vitals']) && $info['vitals'] && !$isPatient): //if not a patient role ?> 
	   <h3><?php echo hotLink($tabNameMap['Vitals'], 'vitals', $hotlink);?></h3>
	   <div style='margin-left: 15px'>
           <?php
           if($report->VITALS) {
                    print '<table ><tr>';
                    $cnt=0;
                   foreach($report->VITALS as $vital) {

                           foreach($vital as $k2v => $v2v)  {
				if($k2v == 'modified_user_id' || $k2v == 'modified_timestamp')
				    continue;

                              $cnt++;
                              if($k2v=='Blood Pressure')
                                 $k2v0='BP';
                              else if ($k2v=='Respiratory Rate')
                                 $k2v0='Resp';
                              else if ($k2v=='Temperature')
                                 $k2v0='Temp';
                              else if ($k2v=='Height')
                                 $k2v0='Ht';
                              else if ($k2v=='Weight')
                                 $k2v0='Wt';
                              else if ($k2v=='Head Circumference')
                                 $k2v0='Head Circ';
                              else if ($k2v=='Breathing Pattern')
                                 $k2v0='';
                              else
                                 $k2v0=$k2v;

                            if($cnt == 10) {
                              echo '</tr><tr>';
                            } 
                                print " <td valign=top align=right><b><nobr>".$k2v0.":</nobr></b></td><td valign=top align=left>";
                                   if(is_array($v2v)) {
                                              $f='';
                                              foreach( $v2v as $v3v) {
                                                    $f=trim(formVitals($v3v,$global_date_format));
                                                    $f2=substr($f, 0, 1);
                                                    if($f2 !='/')
                                                     print '<nobr>'.$f.'</nobr><br>';
                                              }
                                   } else {
                                           print '<nobr>'.formVitals($v2v,$global_date_format).'</nobr>';
                                   }
                                   print "</td>";
                           }
                   }
                   print '</tr></table>';
		   echo (isset($report->VITALS_Scribe))? $report->VITALS_Scribe:'';
           }
          print '<div style="clear:both"></div>'; ?> 
	   </div>
	   <?php endif;?> 
	   <!--[END_VITALS]-->
	  <!--[BEGIN_PE]-->
       <?php if (isset($info['pe']) && $info['pe'] && !$isPatient): //if not a patient role ?> 
           <h3><?php echo hotLink($tabNameMap['PE'], 'pe', $hotlink);?></h3>
           <div style='margin-left: 15px'>
		  <?php
                  if($report->PE) {
                           foreach($report->PE as $kp => $vp) {
																		if (empty($vp) && (!isset($report->PE_COMMENTS[$kp]) || !trim($report->PE_COMMENTS[$kp]))  ) {
																			continue;
																		}
                                   echo "<div style='margin-bottom:2px;'>";
                                   echo "<b>".ucwords($kp)."</b>: ";

                                   foreach($vp as $k2p => $v2p) {
									  echo '&nbsp;';
									  if(strlen($k2p) > 0) {
                                      	echo '<u>' .($k2p).'</u>: ';
									  }
                                      $pf_ttl=''; $pf_neg=array(); $pf_pos=array(); $prd=''; $cnt1=0; $cnt2=0;
                                      
																			$pf_arr = array();  
																			foreach($v2p as $v2p2) {
                                      //echo "===>".$v2p2. "<== <br>";
                                        if(!strstr($v2p2,' NC' )) {
                                           $pf=explode('�',$v2p2);
                                            //pr($pf);

					    // remove
					    $pf[1]=str_replace('(finding)','',$pf[1]);
                                            if(!empty($pf[0]))
                                            {
                                                    $pf_ttl .= ' <i>'.$pf[0].'</i>: ';//.str_replace(' +',' ',str_replace(' -',' ',$pf[1])). ' ' . mkpretty($pf[2]).',';
																										
																														if (!isset($pf_arr[$pf[0]])) {
																															$pf_arr[$pf[0]] = array();
																														}
																										
                                                    if  (substr($pf[1], -1) == '+')
                                                    {
                                                            $pf_ttl .=ltrim(rtrim($pf[1],'+'));
																														$pf_arr[$pf[0]][]= ltrim(rtrim($pf[1],'+'));
                                                    } 				        	
                                                    else if  (substr($pf[1], -1) == '-') 
                                                    {
                                                            $pf2= ltrim(rtrim($pf[1],'-'));
                                                            $pf_ttl .= formatNo($pf2). ' ' . $pf2. '';
																														$pf_arr[$pf[0]][]= formatNo($pf2). ' ' . $pf2. '';
                                                    }
																										
                                                    if($pf[2])
                                                    {
																															$len = count($pf_arr[$pf[0]]);
                                                           $pf_ttl .= ' '.mkpretty($pf[2]);
																													 		$pf_arr[$pf[0]][$len-1] .= ' '.mkpretty($pf[2]);
                                                    }

                                                    $pf_ttl .= '';
                                            }
                                            else if  (substr($pf[1], -1) == '+')  
                                            {
                                                    $cnt1++;
						    $pf_pos[]=ltrim(rtrim($pf[1],'+')).' '.mkpretty($pf[2]);
                                            }
                                            else if (substr($pf[1], -1) == '-')
                                            {
                                                    $cnt2++;
						    $pf_neg[]=ltrim(rtrim($pf[1],'-')).' '.mkpretty($pf[2]);
                                            }
                                            else 
                                            {
                                                    $pf_ttl .=''; 
                                            }


                                        }
                                      }
																			
																			$pf_ttl = '';
																			foreach ($pf_arr as $sub => $obs) {
																				
																				if ($sub =='[text]') {
																					$sub = 'Other';
																				}
																				
																				$obs = array_unique($obs);
																				$pf_ttl .= ' <i>'. $sub .'</i> : ' . implode(', ', $obs) . '; ';
																			}
																			
																			$pf_ttl .='';
																			
																			$pf_pos = array_unique($pf_pos);
																						
																			$tmp_pos = $pf_pos;
																			$pf_pos = array();
																			foreach ($tmp_pos as $p) {
																				if (trim($p) == '[text]') {
																					continue;
																				}
																				$pf_pos[] = $p;
																			}
																			
                                        if(sizeof($pf_pos) > 0)
                                        { 
                                            $cnt1--;  $_and='';
                                            for($i=0;$i<count($pf_pos);$i++)
                                            {
                                                    if($i == $cnt1)
                                                    {
                                                      if(!empty($cnt1) && $cnt1 > 1)
                                                            $_and='and ';

                                                       $prd='.';
                                                    }
                                                    else 
                                                    {
                                                      $_and=''; $prd='';
                                                    }
                                                    if($i >= 1) { $comma=',';} else {$comma='';}
																											$pf_ttl .= $comma."  ".$_and. trim($pf_pos[$i]). $prd;  
																										}

                                        }
																				
                                        if(sizeof($pf_neg) > 0)
                                        { 
                                            $cnt2--; 
                                            for($y=0;$y<count($pf_neg);$y++)
                                            {
                                                    if($y == $cnt2 && !empty($cnt2))
                                                    {
                                                      $_and='and ';
                                                    }
                                                    else 
                                                    {
                                                      $_and='';
                                                    }
                                                    if($y >= 1) { $comma=',';} else {$comma='';}

                                                    $pf_ttl .= $comma."  ".$_and. formatNo($pf_neg[$y]). ' '.trim($pf_neg[$y]);   
                                            }					    	 
                                        }


                                      $return=trim($pf_ttl).';'; unset($pf_pos,$pf_neg,$pf_ttl,$_and);
																			$return = preg_replace('/;;+/', ';', $return);
                                      echo str_replace('.;',';',str_replace(',;',';',$return));
                                       /*  
                                       print '<pre>';
                                       print_r($v2p);    
                                       print '</pre>';
                                       */ 
                                   }
																	 
																	 if (isset($report->PE_COMMENTS[$kp]) && trim($report->PE_COMMENTS[$kp])) {
																		 echo '<br />';
																		 echo nl2br(htmlentities($report->PE_COMMENTS[$kp]));
																		 echo '<br />';
																	 }
																	 
                                   echo "</div>";

                           }
                   } else {
             print 'Not Completed.';
           }
           ?>
		   <?php if ($report->PE_images): ?> 
			<?php 
			
			$paths['patient_encounter_img'] = $paths['patients'] . $demographics->patient_id . DS . 'images' . DS . $encounter_id . DS;
			
			
			?>
		   <div>
			   <ul style="list-style-type: none; margin: 0; padding: 0;">
				   <?php foreach($report->PE_images as $peImg): ?>
				   <li style="float: left; margin-right: 5px; margin-bottom: 10px; width: 350px; page-break-inside: avoid;">
						 <img style="width: 350px; height: 350px;" src="<?php echo Router::url("/", true) . UploadSettings::toUrl(UploadSettings::existing($paths['encounters'] . $peImg['EncounterPhysicalExamImage']['image'], $paths['patient_encounter_img'] . $peImg['EncounterPhysicalExamImage']['image']))  ?>" />
					   <?php if ($peImg['EncounterPhysicalExamImage']['comment']): ?>
					   <p>
					   <em><?php echo htmlentities($peImg['EncounterPhysicalExamImage']['comment']); ?></em>
					   </p>
					   <?php endif;?> 
				   </li>
				   <?php endforeach;?> 
			   </ul>
			   <br style="clear: both;"/>
		   </div>
		   <?php endif;?> 		   
            </div>
       <?php endif;?>
	   <!--[END_PE]-->
       <?php endif;?> 
       <!--[BEGIN_RESULTS]-->    
       
       <?php $doc = $report->DOC['patient_document_reviewed_items'];
       
       if (isset($info['labs_procedures']) && $info['labs_procedures'] && !$isPatient) : //if not patient role ?>
            <h3><?php echo hotLink($tabNameMap['Results'], 'results', $hotlink);?></h3>
            <div style='margin-left: 15px'>
		<?php
		$noDocs = 1;
		if( !empty($doc) ){
			$noDocs = 0;
			echo "<ul>";
			foreach($doc as $docs)	
			{
			   echo "<li>".$docs['PatientDocument']['document_name'];
			   if(!empty($docs['PatientDocument']['description']))
			   echo " Comment: ".$docs['PatientDocument']['description'];
			   if(!empty($docs['PatientDocument']['comment']))
			   echo "<br />Reviewed Comment: ".$docs['PatientDocument']['comment'];
			   echo "</li>";
			}
			echo "</ul>";
		}
						   $noLabs = 1;
											 
											 $labs = $report->POC['patient_lab_reviewed_items'];
											if ($labs) {
												 $noLabs = 0;
												 if(count($labs) < 3){
													 $chunks = count($labs);
												 }else{
													$chunks = floor(count($labs) / 3);
												 }
												 $labs = array_chunk($labs, $chunks);
												 $totalCols = count($labs);

												?>
							<table style="width: 99%;">
								<tr>
												<?php
												 for($ct = 0; $ct < $totalCols; $ct++) {
													 ?>
														<td style="width: <?php echo floor(99/$totalCols) ?>%; vertical-align: top;">

															<?php foreach($labs[$ct] as $l): ?> 
																
								<strong><?php echo $l['EncounterPointOfCare']['lab_test_name']; ?></strong>
																	<br />
																	<?php if($l['EncounterPointOfCare']['lab_test_type'] == 'Panel'): ?> 
																	<?php $panels = json_decode($l['EncounterPointOfCare']['lab_panels'], true); ?>
																			<?php if ($panels): ?> 
																			<?php foreach($panels as $p => $val): ?> 
															&bull;
																					<?php echo $p . ': ' . $val; ?>
																	<br />
																			<?php endforeach;?> 
																			<?php endif;?> 
																	
																	<?php elseif (!empty($l['EncounterPointOfCare']['lab_test_result'])): ?> 
																	<?php echo '<br />' . $l['EncounterPointOfCare']['lab_test_result'] ; ?> 
																	<?php endif;?> 
																	
																
															<?php endforeach;?>
														</td>
							
													 <?php
												 }
												 ?>
												</tr></table>
												<?php
												 echo '<br style="clear: left;" /><br />';
											 }
											 
                       $noImg = 1;
                       for($i=0;$i<count($report->POC['patient_radiology_reviewed_items']);$i++) {
                            $subval=$report->POC['patient_radiology_reviewed_items'][$i]['EncounterPointOfCare'];
                            if(!empty($subval['radiology_procedure_name'])) {
                                      $noImg = 0;
                                    echo '<li> ' .$subval['radiology_procedure_name'];
                                    if(!empty($subval['radiology_test_result'])) {
                                            echo ' <i>result:</i> '.$subval['radiology_test_result']; 
                                    }
                                    if(!empty($subval['radiology_comment'])) {
                                            echo '<ul> <li>'.nl2br(htmlentities($subval['radiology_comment'])) . '</li></ul>'; 
                                    }
                            }
                     }   

                       $noProc = 1;
                       for($i=0;$i<count($report->POC['patient_procedure_reviewed_items']);$i++) {
                  $subval=$report->POC['patient_procedure_reviewed_items'][$i]['EncounterPointOfCare'];
                  if(!empty($subval['procedure_name'])) {
                                      $noProc = 0;
                      echo '<li> ' .$subval['procedure_name'];
                      if( !empty($subval['procedure_body_site']) 
                            OR !empty($subval['procedure_details']) 
                            OR !empty($subval['procedure_comment'])
                        ) {
                            echo '<ul>'; $tab2='</ul>';
                      }
                      if(!empty($subval['procedure_body_site'])) {
                        echo ' <li><i>body site:</i> '.$subval['procedure_body_site']; 
                      }                  
                      if(!empty($subval['procedure_details'])) {
                        echo ' <li><i>details:</i> <br><div>'.nl2br(htmlentities($subval['procedure_details'])).'</div>'; 
                      }
                      if(!empty($subval['procedure_comment'])) {
                        echo ' <li>'.nl2br(htmlentities($subval['procedure_comment'])) . '</li>'; 
                      }
                    echo empty($tab2)?'':$tab2;
                  }
               }           	 

                       $noImm = 1;
               for($i=0;$i<count($report->POC['patient_immunization_reviewed_items']);$i++) {
                  $subval=$report->POC['patient_immunization_reviewed_items'][$i]['EncounterPointOfCare'];
                  if(!empty($subval['vaccine_name'])) {
                      $noImm  = 0;
                                      echo '<li>Vaccine: ' .$subval['vaccine_name'];
                  }
               }  

                       $noInj = 1;
             for($i=0;$i<count($report->POC['patient_injection_reviewed_items']);$i++) {
                  $subval=$report->POC['patient_injection_reviewed_items'][$i]['EncounterPointOfCare'];
                  if(!empty($subval['injection_name'])) {
                      $noInj = 0;
                                      echo '<li>Injection: ' .$subval['injection_name'];
                      if(!empty($subval['injection_dose'])) {
                        echo ' '.$subval['injection_dose']; 
                      }
                  }
               }  

                       $noMed = 1;
            for($i=0;$i<count($report->POC['patient_meds_reviewed_items']);$i++) {
                  $subval=$report->POC['patient_meds_reviewed_items'][$i]['EncounterPointOfCare'];
                  if(!empty($subval['drug'])) {
                      $noMed = 0;
                                      echo '<li>Med: ' .$subval['drug'];
                      if(!empty($subval['unit'])) {
                        echo ' '.$subval['unit']; 
                      }
                      if(!empty($subval['quantity'])) {
                        echo ' (#'.$subval['quantity']. ')'; 
                      }
                      if(!empty($subval['drug_route'])) {
                        echo ' '.$subval['drug_route']; 
                      }
                  }
               } 
							 
							 
							 $noLabResult = 1;
							 
							 
							 //pr($report->outsideLabReports);
							 foreach ($report->reviewedLabs as $emdeon_order) {
								 $noLabResult = 0;
								 
								 //echo '<li> ' . ucwords(strtolower($emdeon_order['EmdeonOrder']['test_ordered'])) . '';
							 }
							 
							 $breakCount = 0; 
							 ?>
							<table>
							 <?php
							 $tr = '<tr>';
							 foreach ($report->outsideLabReports as $l): ?>
									<?php if ($tr){ 
										echo $tr; 
										$tr = '';
										
									} ?>
								
									<td style="vertical-align: top;">
								<?php echo ucwords(strtolower($l['order_description'])) . ' (on ' . __date($global_date_format, strtotime($l['datetime'])).')'; ?>
								<ul style="list-style-type: circle;">
									<?php foreach($l['results'] as $r): ?>
									<li>
										<?php echo ucwords(strtolower($r['test_name'])); ?>: 
										
											<?php if($r['result_value']): ?> 
												<?php echo $r['result_value'] ?> <?php echo $r['unit'] ?>
											<?php else:?> 
												<?php echo $r['comment']; ?>
											<?php endif;?> 
										
										
									</li>
									<?php endforeach;?>
								</ul>
								
									</td>
							
							<?php 
								$breakCount++;
								
								if ($breakCount == 3) {
									$breakCount = 0;
									$tr = '<tr>';
								}
							
							
							
							 endforeach; 
							 ?>
								</tr></table>
									<?php
                       if($noLabs && $noImg && $noProc && $noImm && $noInj && $noMed && $noLabResult && $noDocs) echo 'None.';
               ?><br class="clear" />
            </div>           
       <?php endif;?>
		<!--[END_RESULTS]-->
	
		<!--[BEGIN_POC]-->
       <?php if (isset($info['poc']) && $info['poc'] && !$isPatient): //if not patient role ?> 
            <?php
                    //optional POC items. print if exist
                   if(!empty($report->POC['patient_lab_order_items']) 
                       || !empty($report->POC['patient_radiology_order_items'])
                       || !empty($report->POC['patient_procedure_order_items'])
                       || !empty($report->POC['patient_immunization_order_items'])
                       || !empty($report->POC['patient_injection_order_items'])
                       || !empty($report->POC['patient_meds_order_items'])
                       || !empty($report->POC['patient_supplies_order_items'])
                       ) {
            ?>
                   <h3><?php echo hotLink($tabNameMap['POC'], 'poc', $hotlink);?></h3>
                   <div style='margin-left: 15px'>
                   <?php
                       for($i=0;$i<count($report->POC['patient_lab_order_items']);$i++) {
                          $subval=$report->POC['patient_lab_order_items'][$i]['EncounterPointOfCare'];
                          if(!empty($subval['lab_test_name'])) {
                              echo '<br /><li> ' .$subval['lab_test_name'];
                                $lpr = (!empty($subval['lab_priority']))?$subval['lab_priority']:''; 
                                $ldt = (!strstr($subval['lab_date_performed'], '0000')) ?'@'.__date('H:i', strtotime($subval['lab_date_performed'])). ' on '. __date('m/d/Y', strtotime($subval['lab_date_performed'])):''; 
                                $lby= ($report->POC['patient_lab_order_items'][$i]['OrderBy']['firstname'])? 'by '.$report->POC['patient_lab_order_items'][$i]['OrderBy']['firstname'] . ' '. $report->POC['patient_lab_order_items'][$i]['OrderBy']['lastname']. ' '.$report->POC['patient_lab_order_items'][$i]['OrderBy']['degree']:'';
                         	if($lpr || $ldt || $lby) {
                              	  echo ' ( '.$lpr .' '.$ldt. ' '. $lby.  ' ) ';
				} 
                              	echo '<ul class=poc>';              
                              if(!empty($subval['lab_test_result'])) {
                                echo '<li> <i>result:</i> '.$subval['lab_test_result']; 
                              
                              	if(!empty($subval['lab_unit'])) {
                                	echo ' '.$subval['lab_unit'];
                              	}
                              	if(!empty($subval['lab_normal_range'])) {
                                	echo ' (range: '.$subval['lab_normal_range']. ' '.$subval['lab_unit']. ')';
                              	}

                             } 	
                              	if(!empty($subval['lab_abnormal']) || !empty($subval['lab_test_result_status'])) {
                              	    $lst= (!empty($subval['lab_test_result_status']))?'<i>status:</i> ' .$subval['lab_test_result_status'].'':'';
                              	    $labn= (!empty($subval['lab_abnormal']))? ' <i>Abnormal:</i> '.$subval['lab_abnormal']. '':'';
                                    echo '<li> '.$lst.' '.$labn.'';
                                }
                                                            
                              if ($subval['lab_test_type'] == 'Panel'):
                                $panels = json_decode($subval['lab_panels'], true);
                                    if ($panels):
                              ?>
                                <ul class=poc>
                                <?php foreach ($panels as $key => $val): ?>
                                    <?php if (!empty($val)) echo '<li>'.htmlentities($key) . ' : '. htmlentities($val); ?> 
                                <?php endforeach; ?>
                                </ul>
                              <?php
                                    endif;
                              endif;
                              if(!empty($subval['lab_comment'])) {
                                echo ' <li> <em> comments:</em> '.nl2br(htmlentities($subval['lab_comment'])) . '</li>'; 
                              }
                              echo '</ul>';
                          }
                       }
                      for($i=0;$i<count($report->POC['patient_radiology_order_items']);$i++) {
                          $subval=$report->POC['patient_radiology_order_items'][$i]['EncounterPointOfCare'];
                          if(!empty($subval['radiology_procedure_name'])) {
                              echo '<br /><li> ' .$subval['radiology_procedure_name'];
                              if(!empty($subval['radiology_number_of_views'])) {
                                echo '  '.$subval['radiology_number_of_views']. ' view';
                                echo ($subval['radiology_number_of_views'] == 1) ? '':'s';  
                              }                              
                                $lpr = (!empty($subval['radiology_priority']))?$subval['radiology_priority']:''; 
                                $ldt = (!strstr($subval['radiology_date_performed'], '0000'))?'@'.__date('H:i', strtotime($subval['radiology_date_performed'])). ' on '. __date('m/d/Y', strtotime($subval['radiology_date_performed'])):''; 
                                $lby= ($report->POC['patient_radiology_order_items'][$i]['OrderBy']['firstname'])? 'by '.$report->POC['patient_radiology_order_items'][$i]['OrderBy']['firstname'] . ' '. $report->POC['patient_radiology_order_items'][$i]['OrderBy']['lastname']. ' '.$report->POC['patient_radiology_order_items'][$i]['OrderBy']['degree']:'';
                         	if($lpr||$ldt||$lby){
                              	  echo ' ( '.$lpr .' '.$ldt. ' '. $lby.  ' ) '; 
				}
                              echo '<ul class=poc>';	                              
                              if(!empty($subval['radiology_body_site1'])) {
                                echo '<li> <i>body site:</i> '; 
                                echo (!empty($subval['radiology_body_site1']))? $subval['radiology_body_site1']:'';
                                echo (!empty($subval['radiology_body_site2']))? ', '.$subval['radiology_body_site2']:'';
                                echo (!empty($subval['radiology_body_site3']))? ', '.$subval['radiology_body_site3']:'';
                                echo (!empty($subval['radiology_body_site4']))? ', '.$subval['radiology_body_site4']:'';
                                echo (!empty($subval['radiology_body_site5']))? ', '.$subval['radiology_body_site5']:'';                               
                              }                              
                                                            
                              if(!empty($subval['radiology_test_result'])) {
                                echo '<li> <i>result:</i> '.$subval['radiology_test_result']; 
                              }
                              if(!empty($subval['radiology_comment'])) {
                                echo '<li> <em>comments:</em> '.nl2br(htmlentities($subval['radiology_comment'])) . ''; 
                              }
                              echo '</ul>';
                          }
                       }
                       
                      $model = ClassRegistry::init('AdministrationPointOfCare');
                      App::import('Lib', 'FormBuilder');
                      $formBuilder = new FormBuilder();
                      
                      for($i=0;$i<count($report->POC['patient_procedure_order_items']);$i++) {
                          $subval=$report->POC['patient_procedure_order_items'][$i]['EncounterPointOfCare'];
                          if(!empty($subval['procedure_name'])) {
                              echo '<br /><li> ' .$subval['procedure_name'];
                                $lpr = (!empty($subval['procedure_priority']))?$subval['procedure_priority']:''; 
                                $ldt = (!strstr($subval['procedure_date_performed'], '0000')) ? '@' .__date('H:i', strtotime($subval['procedure_date_performed'])). ' on '. __date('m/d/Y', strtotime($subval['procedure_date_performed'])):''; 
                            	$lby= ($subval['procedure_administered_by'])? 'by '.$subval['procedure_administered_by']:''; 

				if ($lpr||$ldt||$lby) {                         
                              	  echo ' ( '.$lpr .' '.$ldt. ' '. $lby.  ') '; 
                              	}                              
                              if( !empty($subval['procedure_body_site']) 
                                    OR !empty($subval['procedure_details']) 
                                    OR !empty($subval['procedure_comment'])
                                ) {
                                    echo '<ul class=poc>'; $tab2='</ul>';
                              }										
 																										
                              if(!empty($subval['procedure_body_site'])) {
                                echo ' <li><i>body site:</i> '.$subval['procedure_body_site']; 
                              }    
                              if(!empty($subval['procedure_unit'])) {
                                echo ' <li><i>unit(s):</i> '.$subval['procedure_unit']; 
                              }                                             
                              if(!empty($subval['procedure_details'])) {
                                echo ' <li><i>details:</i> <br><div>'.nl2br(htmlentities($subval['procedure_details'])).'</div>'; 
                              }
                              if(!empty($subval['procedure_comment'])) {
                                echo ' <li><i>comments:</i> '.nl2br(htmlentities($subval['procedure_comment'])) . '</li>'; 
                              }
                              
                              if(!empty($subval['poc_form'])) {
                                
                                $formTemplate = $model->loadForm($subval['procedure_name']);
                                if ($formTemplate) {
                                  $map = $formBuilder->getDataMap($formTemplate, $subval['poc_form'], array('preserve_columns' => true));
                                  echo '<li> <i>Procedure Form:</i>';

                                  foreach ($map as $m) {
																		
																		if (isset($m['question'])) {
																			
																			if (!$m['question'] && isset($m['snippet'])) {
																				echo 	'<div>' . $m['snippet'] .'</div>'	;
																			} else {
																				if (is_array($m['answer'])) {
																					$m['answer'] = implode(', ', $m['answer']);
																				}
																				echo '<ul><li><strong>'. htmlentities($m['question']) . ':</strong> ' . htmlentities($m['answer']) . '</li></ul>';
																			}
																			
																		
																		} else {
																			$colWidth = floor(99/count($m));
																			
																			echo '<table style="width: 99%;"><tr>';
																			
																			foreach ($m as $cl) {
																				if (is_array($cl['answer'])) {
																					$cl['answer'] = implode(', ', $cl['answer']);
																				}
																				
																				echo '<td style="width:' . $colWidth . '%; vertical-align: top;"><ul><li><strong>'. htmlentities($cl['question']) . ':</strong> ' . htmlentities($cl['answer']) . '</li></ul></td>';
																				
																			}
																			
																			echo '</tr></table>';
																			
																		}
																		
																		
                                  }

                                  echo '</li>';
                                  
                                }
                              }
                              
                            echo empty($tab2)?'':$tab2;
                          }
                       }
                      for($i=0;$i<count($report->POC['patient_immunization_order_items']);$i++) {
                          $subval=$report->POC['patient_immunization_order_items'][$i]['EncounterPointOfCare'];
                          if(!empty($subval['vaccine_name'])) {
                              echo '<br /><li>Vaccine: ' .$subval['vaccine_name'];
                                $lpr = (!empty($subval['vaccine_priority']))?$subval['vaccine_priority']:''; 
                                $ldt = (!strstr($subval['vaccine_date_performed'], '0000'))?'@'.__date('H:i', strtotime($subval['vaccine_date_performed'])). ' on '. __date('m/d/Y', strtotime($subval['vaccine_date_performed'])):''; 
                                $lby= ($subval['vaccine_administered_by'])? 'by '.$subval['vaccine_administered_by']:'';                           
				if($lpr||$ldt||$lby) {
                              	  echo ' ( '.$lpr .' '.$ldt. ' '. $lby.  ' ) ';
				}                              
                          echo '<ul class=poc>';
			   echo (!empty($subval['vaccine_dose']))? '<li><i>dose:</i> ' .$subval['vaccine_dose']:'';
			   echo (!empty($subval['administered_units']))? ' '.$subval['administered_units']:'';			  
			   echo (!empty($subval['vaccine_lot_number']))? '<li><i>lot #:</i> ' .$subval['vaccine_lot_number']:'';      														
			   echo (!empty($subval['vaccine_manufacturer']))? '<li><i>manufacturer:</i> ' .$subval['vaccine_manufacturer']:'';
			   echo (!empty($subval['manufacturer_code']))? ' (' .$subval['manufacturer_code']. ')':'';			   
			   echo (!empty($subval['vaccine_body_site']))? '<li><i>body site:</i> ' .$subval['vaccine_body_site']:'';
			   echo (!empty($subval['vaccine_route']))? '<li><i>route:</i> ' .$subval['vaccine_route']:'';
			   echo (!empty($subval['vaccine_expiration_date']))? '<li><i>expiration:</i> ' .__date('m/d/Y', strtotime($subval['vaccine_expiration_date'])):'';
			   echo (!empty($subval['vaccine_comment']))? '<li><i>comment:</i> ' .nl2br(htmlentities($subval['vaccine_comment'])):'';		
			   
			   echo '</ul>';
			   }	   			   													
                       }                           
                      for($i=0;$i<count($report->POC['patient_injection_order_items']);$i++) {
                          $subval=$report->POC['patient_injection_order_items'][$i]['EncounterPointOfCare'];
                          if(!empty($subval['injection_name'])) {
                              echo '<br /><li>Injection: ' .$subval['injection_name'];
                              
                                $lpr = (!empty($subval['injection_priority']))?$subval['injection_priority']:''; 
                                $ldt =(!strstr($subval['injection_date_performed'], '0000')) ?'@'.__date('H:i', strtotime($subval['injection_date_performed'])). ' on '. __date('m/d/Y', strtotime($subval['injection_date_performed'])):''; 
                                $lby= ($subval['injection_administered_by'])? 'by '.$subval['injection_administered_by']:'';                         
				if($lpr||$ldt||$lby) {
                              	  echo ' ( '.$lpr .' '.$ldt. ' '. $lby.  ' ) ';
				}                               
                      		echo '<ul class=poc>';	      												
			   	echo (!empty($subval['injection_dose']))? '<li><i>dose:</i> ' .$subval['injection_dose']:'';		  
				 echo (!empty($subval['injection_unit']))? '<li><i>unit(s):</i> ' .$subval['injection_unit']:'';
			   echo (!empty($subval['injection_lot_number']))? '<li><i>lot #:</i> ' .$subval['injection_lot_number']:'';      														
			   echo (!empty($subval['injection_manufacturer']))? '<li><i>manufacturer:</i> ' .$subval['injection_manufacturer']:'';
			   echo (!empty($subval['injection_body_site']))? '<li><i>body site:</i> ' .$subval['injection_body_site']:'';
			   echo (!empty($subval['injection_route']))? '<li><i>route:</i> ' .$subval['injection_route']:'';
			   echo (!empty($subval['injection_expiration_date']))? '<li><i>expiration:</i> ' .__date('m/d/Y', strtotime($subval['injection_expiration_date'])):'';
			   echo (!empty($subval['injection_comment']))? '<li><i>comment:</i> ' .nl2br(htmlentities($subval['injection_comment'])):'';			   	
			   echo '</ul>';				         															
                          }
                       }     
       
                      for($i=0;$i<count($report->POC['patient_meds_order_items']);$i++) {
                          $subval=$report->POC['patient_meds_order_items'][$i]['EncounterPointOfCare'];
                          if(!empty($subval['drug'])) {
                              echo '<br /><li>Medication: ' .$subval['drug'];
                            
                                $lpr = (!empty($subval['drug_priority']))?$subval['drug_priority']:''; 
                                $ldt = (!strstr($subval['drug_date_given'], '0000')) ?'@'.__date('H:i', strtotime($subval['drug_date_given'])). ' on '. __date('m/d/Y', strtotime($subval['drug_date_given'])):''; 
                                $lby= ($subval['drug_administered_by'])? 'by '.$subval['drug_administered_by']:'';
                         	if($lpr||$ldt||$lby) {
                              	  echo ' ( '.$lpr .' '.$ldt. ' '. $lby.  ' ) ';
				}                               
                                echo '<ul class=poc>';
                                echo (!empty($subval['quantity']))? '<li><i>quantity:</i> #'.$subval['quantity']:''; 
                                echo (!empty($subval['unit']))? ' '.$subval['unit']:''; 
                                echo (!empty($subval['drug_route'])) ? '<li><i>route:</i> '.$subval['drug_route']:''; 								
  				echo (!empty($subval['drug_comment']))? '<li><i>comment:</i> ' . nl2br(htmlentities($subval['drug_comment'])):'';			 
				echo '</ul>';															
                          }
                       }
											 
											 if ($report->POC['patient_supplies_order_items']) {
												 foreach ($report->POC['patient_supplies_order_items'] as $s) {
													 echo '<br /><li> Supply: ' . $s['EncounterPointOfCare']['supply_name'] . '</li>';
													  echo '<ul class=poc>';	
													 echo (!empty($s['EncounterPointOfCare']['supply_description']))? '<li><i>Description:</i> '.$s['EncounterPointOfCare']['supply_description']:'';
													 echo (!empty($s['EncounterPointOfCare']['supply_quantity']))?'<li><i>Quantity: </i>'.$s['EncounterPointOfCare']['supply_quantity']:'';
													 echo '</ul>';
												 }
													
											 }

                   print '       </div> ';                    		                
                   } 
                   ?>            
            
            
       <?php endif;?>
		<!--[END_POC]-->
		<!--[BEGIN_ASSESSMENT]-->
       <?php if (isset($info['assessment']) && $info['assessment']):?>                        
           <h3><?php echo hotLink($tabNameMap['Assessment'], 'assessment', $hotlink);?></h3>
           <div style='margin-left: 15px'>
           <?php

           if($report->ASSESSMENT) {
                        //if they want the assessment summary printed (from user options 'assessment_pmh_summary')
             if ($report->ASSESSMENT_SUMMARY && $provider->assessment_pmh_summary) {
               $summary = trim($report->ASSESSMENT_SUMMARY['EncounterAssessmentSummary']['summary']);
               if ($summary) {
                   echo ''.nl2br(htmlentities($summary)).':';
               }
           }            
             
                    print '<ol>';
                   $comment='';
                   foreach($report->ASSESSMENT as $k => $v) {
                           if($v['comment']) {
                             list($comment,)=explode('(',$v['comment']);
                             $comment = ' - <i>' .strtolower(str_replace('Dx', 'diagnosis', trim($comment))).'</i>';
                           }
                           if($v['diagnosis'] != 'No Match') {
                                print '<li>'.ucwords($v['diagnosis']) . ' ' . $comment;
                           } else {
                                print '<li>'.ucwords($v['occurence']) . ' ' . $comment;
                           }

                           $comment='';
                   }
                  print '</ol>'; 
           } else {
             print 'Not Completed.';
           }
           ?>
           </div>                       
       <?php endif;?>
	   <!--[END_ASSESSMENT]-->
       <!--[BEGIN_PLAN]-->                
       <?php if (isset($info['plan']) && $info['plan']):?>      
           <h3><?php echo hotLink($tabNameMap['Plan'], 'plan', $hotlink);?></h3>
           <div style='margin-left: 15px'>
           <?php
           if($report->plan || $report->GetDosespotMedication || $report->hm_enrolment|| !empty($report->rx_changes))
           {
             print '<ol>';
			 
						 if (!$report->plan) {
							 $report->plan = array();
						 }
             foreach($report->plan as $k => $v) {
               //if they want assessment separate from the plan
                if(empty($provider->assessment_plan)) {
                   echo "<li>\n";
                   printf( '%s',ucwords($k) );
		}             

                   if(isset($v['free_text']) && $v['free_text']) {
                   
                      //provider can decide to hide free text comments from patient report - #2248
		      $hide_free_text_comments= ($isPatient && !empty($provider->hide_freetext_comments)) ? TRUE:FALSE;
	      	
		      if(!$hide_free_text_comments)
		      {
                       		//if they want assessment separate from the plan
                       		if(!empty($provider->assessment_plan)) {
			
                           		echo "<li> ";
                       		} else {      
                           		echo "<br /> <i>comment:</i> <br />";
                       		}    
                           		echo nl2br(htmlentities(ucfirst($v['free_text'])));
                      }     		
                   }
                    //if they want assessment separate from the plan
                   if(empty($provider->assessment_plan)) {
                   	echo "<ul>\n";
                   }
				   
                   foreach($v as $k2 =>  $v2) {
                           if(!is_array($v2)) {

                                   continue;
                           }

                           $data = array();
                           switch($k2) {
                                   case 'emdeon_lab_orders':
					if(is_array($v2['items']) && count($v2['items']) > 0) {
                                           echo "<li>e-Lab Order(s):  ".implode(', ', $v2['items'])."</li>";
					}
                                           break;
                                   case 'lab':                               
                                   case 'radiology':
                                   case 'procedure':
                                           if($phone != 'yes'):
                                           echo "<li>".ucwords($k2).": <ul>";
                                           foreach($v2['items'] as $eachRX) {
                                                        $rxDetail = $v2[$eachRX];
                                                        echo '<li>', $eachRX;
                                                        echo "</li>";
                                                }
                                           echo '</ul>';
                                           endif;
                                       
                                       break;
								   case 'emdeon_rx':
								   			echo "<li>Rx: <ul>";
										   
										   foreach($v2['items'] as $eachRX) {
												$rxDetail = $v2[$eachRX];
												echo '<li>', $eachRX;
												echo ($rxDetail['sig'])? ', SIG: '. $rxDetail['sig'] : '';
												echo ($rxDetail['quantity'])? ', Dispense: '.$rxDetail['quantity'] : '';
												echo ($rxDetail['refills'])? ', Refills: '.$rxDetail['refills'] : '';
												echo "</li>";
											}
										   
										   echo '</ul>';
                                           break;
                                   case 'rx':
								   		   echo "<li>".ucwords($k2).": <ul>";
										   
										   foreach($v2['items'] as $eachRX) {
												$rxDetail = $v2[$eachRX];
												echo '<li>', $eachRX;
												echo ($rxDetail['quantity'])? ', SIG: '. $rxDetail['quantity'].' '.$rxDetail['unit'].' '.$rxDetail['route'].' '.$rxDetail['frequency'] : '';
												echo ($rxDetail['direction'])? ', '. $rxDetail['direction'] : '';
												echo ($rxDetail['dispense'])? ', Dispense: '.$rxDetail['dispense'] : '';
												echo ($rxDetail['refill_allowed'])? ', Refills: '.$rxDetail['refill_allowed'] : '';
												echo "</li>";
											}
                      
                      
										   echo '</ul>';
                                           break;
                                   case 'advice':
                                           echo "<li>Advice: <ul>";
                                           foreach($v2 as $k4 => $v4) {
                                              $v4=nl2br(trim($v4));
                                              if($v4) {
                                                //if($k4 == 'patient_instruction') echo "<li> <i>instructions</i>: ".make_clickable($v4). " ";
                                                if($k4 == 'patient_education_comment') echo "<li> <i>education/instructions</i>: ".make_clickable($v4)."";
                                              }
                                           }
                                            echo '</ul>';
                                            break;
                                   case 'referral':
                                           if($phone != 'yes'):
                                           echo "<li>Referrals: <ul>";
                                           foreach($v2 as $ref) {

                                               $refName = htmlentities(trim($ref['referred_to']));
                                               
                                               $specialties = trim($ref['specialties']);
                                               
                                               $refName = $refName  . ($specialties ? ', ' . $specialties : '');
                                               
                                              if($refName) {
                                                  echo '<li>', $refName, '</li>';
                                              }
                                           }
                                            echo '</ul>';  
                                            endif;                                     
                                   break;
                           }
                   }
                   echo "</ul>\n";
                   echo "</li>\n";
             }
                      foreach ($report->rx_changes as $c) {

			  switch($c['EncounterPlanRxChanges']['medication_status']) {
				case 'Active':
				   $med_status='Activated';
				    break;
				case 'Cancelled': 
				case 'InActive':
				   $med_status='Cancel'; 
				  break;
				case 'Discontinued':
				   $med_status='Discontinue';
 				  break;
				default: $med_status=$c['EncounterPlanRxChanges']['medication_status']; 
			  }

                        echo '<li>';
                        echo $med_status. ': ' .htmlentities($c['EncounterPlanRxChanges']['medication_details']);
                        echo '</li>';
                      }
			 
			 //Dosespot Medication Section
			 //debug($report->GetDosespotMedication);
             if(count($report->GetDosespotMedication) > 0)
			 {
				echo '<li>e-Rx:';
				echo '<ul>';
				
				foreach($report->GetDosespotMedication as $GetDosespotMedication)
				{
				  $direction = '';
				  $quantity = '';
				  $refill_allowed = '';
				  $days_supply = '';
				  $direction_value = $GetDosespotMedication['direction'];
				  $quantity_value = $GetDosespotMedication['quantity_value'];
				  $refill_allowed_value = $GetDosespotMedication['refill_allowed'];
				  //$days_supply_value = $GetDosespotMedication['days_supply'];
				  if($direction_value != "")
				  {
				      $direction = ', '.$direction_value;
				  }
				  if($quantity_value != "")
				  {
				      $quantity = ', Dispense: '.$quantity_value;
				  }
				  if($refill_allowed_value != "")
				  {
				      $refill_allowed = ', Refills: '.$refill_allowed_value;
				  }
				  /*if($days_supply_value != "")
				  {
				      $days_supply = ', Days Supply: '.$days_supply_value;
				  }*/
					echo '<li>'.$GetDosespotMedication['medication'].$direction.$quantity.$refill_allowed.'</li>';	
				}
				
				echo '</ul>';
				echo '</li>'; 
			 }
			 
			 //health maintenance here
             
			 if($phone != 'yes'):
			 if(count($report->hm_enrolment) > 0)
			 {
				echo '<li>Health Maintenance';
				echo '<ul>';
				
				foreach($report->hm_enrolment as $hm_enrolment)
				{
					echo '<li>'.$hm_enrolment['plan_name'].'</li>';	
				}
				
				echo '</ul>';
				echo '</li>'; 
			 }
			 endif;

             if($encounter->followup == 'Yes' || !empty($encounter->return_time) || !empty($encounter->return_period) ) {
               echo "<li>Follow Up: in {$encounter->return_time} {$encounter->return_period}";
             }
             			 
             print '</ol>';
           }
           else
           {
              if($encounter->followup == 'Yes' || !empty($encounter->return_time) || !empty($encounter->return_period) ) {
               echo "<ol><li>Follow Up: in {$encounter->return_time} {$encounter->return_period} </li></ol>";
              } else {
                 echo "Not Completed.";
	      }
           }           
           
           ?>
           </div>   
       <?php              
           //if they want a Gratitude Statement to print (usually specialists like this feature)
           if($provider->gratitude_statement)
           {
           	print '<p>'.$provider->gratitude_statement;
           }
	?>
           
                   
       <?php endif;?>
		   <!--[END_PLAN]-->

<!--[TAB_SECTION_1]-->		   
<!--[TAB_SECTION_2]-->		   
<!--[TAB_SECTION_3]-->		   
<!--[TAB_SECTION_4]-->		   
<!--[TAB_SECTION_5]-->		   
<!--[TAB_SECTION_6]-->		   
<!--[TAB_SECTION_7]-->		   
<!--[TAB_SECTION_8]-->		   
<!--[TAB_SECTION_9]-->		   
<!--[TAB_SECTION_10]-->		   
<!--[TAB_SECTION_11]-->		   
		   

	<?php
       if($encounter->visit_summary_given == 'Yes') {
         echo "<p><i>A copy of this report was given to the patient.</i></p>";
       } 
       ?>
			<!--[BEGIN_ADDENDUM]-->		 
       <?php Addendum($report); ?>
			<!--[END_ADDENDUM]-->		 

<?php
       
	?>
      
<?php if(isset($_SESSION['api']) && $superbillComments): ?> 
      <h3>Comments</h3>
      <p><?php echo $superbillComments; ?></p>
      <br />
      <br />
      <br />
<?php endif;?>      
<p class=""><b>Signed by Provider: 
<br>
<?php 

$docImg=$url_abs_paths['preferences'].'/'.$provider->signature_image;
if(is_file($docImg)) echo '<img src="'.Router::url("/", true).$docImg.'"><br>';

   if($provider->title) echo $provider->title. ' ';
echo $provider->firstname . ' ' .$provider->lastname; 
if($provider->degree) echo ', ' .$provider->degree. ' ';

$custom_pt_id= (!empty($demographics->custom_patient_identifier))? 'ID: '. $demographics->custom_patient_identifier. ', ' : '';
?>
</b>
<?php if ($coProvider): ?>
  <br />
  <br />
<p class=""><b>Co-Signed by Provider: 
<br>
<?php
$docImg=$url_abs_paths['preferences'].'/'.$coProvider->signature_image;
if(is_file($docImg)) echo '<img src="'.Router::url("/", true).$docImg.'"><br>';

   if($coProvider->title) echo $coProvider->title. ' ';
echo $coProvider->firstname . ' ' .$coProvider->lastname; 
if($coProvider->degree) echo ', ' .$coProvider->degree. ' ';

?>
  
<?php endif;?>



  
       <hr class="hide_for_referral" />
       <table class="hide_for_referral" border=0 width=100%><tr><td width=50%><b>Patient: <?php echo $demographics->first_name.' '.$demographics->last_name . ' ('.$custom_pt_id. 'MRN: ' . $demographics->mrn. ')';?></b></td><td><b>Date of Service: <?php echo $visit_date; ?></b></td><td align=right><b>DOB: <?php echo $dob; ?></td></tr></table>
	<hr class="hide_for_referral" />
       <div class="hide_for_referral" style="text-align:center">Report generated by: One Touch EMR Software (www.onetouchemr.com) </div>
<?php




?>
<script type="text/javascript" language="javascript">
//$(document).ready(function(){
 // Reset Font Size
  var originalFontSize = $('body,table').css('font-size');
  $(".resetFont").click(function(){
  $('body,table').css('font-size', originalFontSize);
  });
  // Increase Font Size
  $(".increaseFont").click(function(){
  	var currentFontSize = $('body,table').css('font-size');
 	var currentFontSizeNum = parseFloat(currentFontSize, 10);
        var newFontSize = currentFontSizeNum*1.2;
	$('body,table').css('font-size', newFontSize);
	return false;
  });
  // Decrease Font Size
  $(".decreaseFont").click(function(){
  	var currentFontSize = $('body,table').css('font-size');
 	var currentFontSizeNum = parseFloat(currentFontSize, 10);
        var newFontSize = currentFontSizeNum*0.8;
	$('body,table').css('font-size', newFontSize);
	return false;
  });
//});

</script>
	<script>
		if (isTouchEnabled()) {
				$('.toggle-format').click(function(evt){
						evt.preventDefault();
						var url = $(this).attr('href');
						$(this).text('Switching ....');
						window.location.href = url;
				});    
				if (window.parent) {
					$('.hot-link')
							.click(function(evt){
									evt.preventDefault();
									var url = $(this).attr('href');
									window.parent.hotLink(url);
							});
				} 			
		}

	</script>
	<br />
	<br />
</body>
</html>
<?php

$output = ob_get_clean();

$sections = array('CC', 'HPI','HX', 'PE', 'VITALS', 'MEDS_ALLERGY', 'ROS', 'POC', 'RESULTS', 'PLAN', 'ASSESSMENT');

$sectionData = array();

foreach ($sections as $s) {
	$pattern = '/(<!--\[BEGIN_' . $s . '\]-->.+?)+(<!--\[END_' . $s . '\]-->)/is';
	
	$matches = array();
	
	if (preg_match($pattern, $output, $matches)) {
		$sectionData[$s] = $matches[0];
		$output = preg_replace($pattern, '', $output);
	} else {
		$sectionData[$s] = '';
	}
}
App::import('Model', 'PracticeEncounterTab');
$PracticeEncounterTab = new PracticeEncounterTab();

$tabs = $PracticeEncounterTab->getAccessibleTabs($schedule['ScheduleType']['encounter_type_id'], EMR_Account::getCurretUserId());
$ct = 1;
foreach ($tabs as $t) {
	$tabName = strtoupper($t['PracticeEncounterTab']['tab']);
	$tabName = preg_replace('/[^A-Z]/', ' ', $tabName);
	$tabName = preg_replace('/[\s]+/', '_', $tabName);
	
	if (!isset($sectionData[$tabName])) {
		continue;
	}
	
	
	$output = str_replace('<!--[TAB_SECTION_' . $ct++ . ']-->', $sectionData[$tabName], $output);
}

echo $output;
