<?php
/* tutor mode element
*
*
*/
/*
  $pre = '/help/videos/view:';
   $tutor_vid[2]= $pre.'4krAFrO7bwU';
   $tutor_vid[4]= $pre.'Qa9j-dvFrs4';
   $tutor_vid[5]= $pre.'a-qXNxkzK4g';
   $tutor_vid[6]= $pre. 'Jrm7xuFIDFc';
   $tutor_vid[7]=$pre.'cSN47aFSsVE';
   $tutor_vid[13]=$pre.'3aAA1a6mGKI'; 
   $tutor_vid[14]=$pre.'qIcDuSeDjQc';
   $tutor_vid[15]=$pre.'L4ezofKkR3M';
   $tutor_vid[16]=$pre.'aY5GblDHNzQ';   
   $tutor_vid[17]=$pre.'glHpJGijXCs';  
   $tutor_vid[18]=$pre.'_uGvyqjqcV8';
   $tutor_vid[19]=$pre.'knbUIvjmYEk';    
   $tutor_vid[20]=$pre.'dj_ARszckVk';
   $tutor_vid[21]=$pre.'5_LxZVZ1IH8';                 
    $vid_blank='';
*/
  $pre = 'http://learn.onetouchemr.com/clipbucket/upload/watch_video.php?v=';
	$help = $this->Html->url(array('controller' => 'help', 'action' => 'tutorial')) .'#';
	// I'm just keeping this here for quick access in case needed - rolan
	/*
		$tutor_vid[2]= $pre.'K56S1X3O636X';
		$tutor_vid[4]= $pre.'34WHOR69B2RB';
		$tutor_vid[5]=$pre.'HOKUN1GO28NU';
		$tutor_vid[6]= $pre. 'XRD5UM7894DN';
		$tutor_vid[7]=$pre.'29937D6183MY';
		$tutor_vid[13]=$pre.'GY6165GN3MKX';
		$tutor_vid[14]=$pre.'ONMK7A3DSYXA';
		$tutor_vid[15]=$pre.'UGD23415DSRA';
		$tutor_vid[16]=$pre.'HUK2ONMA232K';
		$tutor_vid[17]=$pre.'Y2KMYWHUU7MO';  
		$tutor_vid[18]=$pre.'A4G775H29KBK';      
		$tutor_vid[19]=$pre.'DMBNYH874O1K';      
		$tutor_vid[20]=$pre.'5BW2X1KY3HRH'; 
		$tutor_vid[21]=$pre.'RYH3DM93NYKY';
	*/
	
	$tutor_vid[2]= $help . 'dashboard';
	$tutor_vid[4]= $help . 'encounter_summary';
	$tutor_vid[5]= $help . 'encounter_chief_complaint';
	$tutor_vid[6]= $help . 'history_of_present_illness';
	$tutor_vid[7]= $help . 'encounter_history';
	$tutor_vid[13]= $help . 'clinical_encounter_meds_allergies';
	$tutor_vid[14]= $help . 'encounter_review_of_systems';
	$tutor_vid[15]= $help . 'clinical_encounter_vitals';
	$tutor_vid[16]= $help . 'encounter_physical_exam';
	$tutor_vid[17]= $help . 'encounter_point_of_care';  
	$tutor_vid[18]= $help . 'results_tab'; 
	$tutor_vid[19]= $help . 'assessment_tab';     
	$tutor_vid[20]= $help . 'plan_tab';
	$tutor_vid[21]= $help . 'superbill_tab';   
	$tutor_vid[42]= $help . 'patient_summary_format_feature';
	    $tutor_vid[43]= $help . 'health_maintenance_flow_sheet';  
	 $tutor_vid[90]= $help . 'calendar'; 
	 
	$vid_blank='target=_blank';

if(!empty($tutor_mode))
{

  if( $isiPadApp ) { $prf='safari';} else { $prf='http';}
//dashboard
 $tutor_array[1]='<div class="notice" id = "notice_hide"><table><tr><td>'.$html->image('tutor.png', array('alt' => 'Tutor', 'style' => 'margin-right:9px;float:left;height:48px;width:37px')).' Welcome! "Tutor Mode" is currently "On" and will give more detailed explanations of features of the system to help you learn faster. Try the <a href="'.$prf.'://youtu.be/4jcYKBpMzlY" target=_blank>"Getting Started" video</a>.  </br>(You can turn this feature "Off" under "User Options" from the "Preferences" -> "System Settings" top menu or <a href="/preferences/user_options">Click here</a>.)</td></tr></table></div>';
  if(is_object($user)) {
   $rss_fd=$user->rss_feed;
  } else {
   $rss_fd=$user['rss_feed'];
  }
    if($rss_fd) $tp='500'; else $tp='380';
  
$tutor_array[2]='<span class="small_notice" style="top:'.$tp.'px;right:21px">'.$html->image('up_arrow_icon_small.png', array('style' => 'padding:0px 2px 0px 0px;')).' Quickly see a preview of new  Messages,<br> Rx refill requests, new Lab Results and  the <br>Order Feed -  a tracking system  so no patient <br>orders fall through the cracks! <br><br> '.$html->image('left_arrow_icon_small.png', array('style' => 'padding:0px 2px 0px 0px;')).'  Patient schedule is on the left. <br>Just touch the blue text to triage patients <br> and update information. <br><br> <a href="'.$tutor_vid[2].'" '.$vid_blank.'>See Video</a> </span>';


 //encounter
  $tutor_array[4]='<div class="small_notice" style="position:relative;font-weight:normal;">'.$html->image('down_arrow_icon_small.png', array('style' => 'padding:0px 2px 0px 0px;')).'Summary tab allows you to get acquainted with your patient before beginning a visit. It\'s similiar to what you might see on left side of a paper chart. Alerts will appear on the top right. <a href="'.$tutor_vid[4].'" '.$vid_blank.'>See Video</a></div>';
 $tutor_array[5]='<span class="small_notice" style="top:60px;left:570px">'.$html->image('left_arrow_icon_small.png', array('style' => 'padding:0px 2px 0px 0px;')).'Obtain Complaints by "Drill Menu" <br>or  "Text Search" or quickly access <br>your "Common Complaint" Macros <br>or this patient\'s Previous Complaints. <br><br> Once captured, they will appear on <br>the right. To remove, click the red X '.$html->image('right_arrow_icon_small.png', array('style' => 'padding:0px 2px 0px 0px;')).' <br><br> Changes are instantly saved! <br> <a href="'.$tutor_vid[5].'" '.$vid_blank.'>See Video</a></span>';
 $tutor_array[6]='<div class="small_notice" style="position:relative;font-weight:normal;top:-5px; ">'.$html->image('down_arrow_icon_small.png', array('style' => 'padding:0px 2px 0px 0px;')).'Select Complaint to work with (if more than 1), then either the  "(Click to Add Free Text)"  option or  the HPI element boxes by selecting "(Click to Add Description)" to enter your data.  the "E&M Helper" can assist you with how many elements you need! <a href="'.$tutor_vid[6].'" '.$vid_blank.'>See Video</a> or <a href="'.$this->Session->webroot.'preferences/common_complaints" '.$vid_blank.'>build HPI templates</a></div>';

 $dim = 'top:70px;right:25px;';
 if ($isiPadApp) {
	 $dim = 'top:55px;right:10px;width: 350px;';
 }
 $tutor_array[7]='<span class="small_notice" style="'.$dim.'">'.$html->image('left_arrow_icon_small.png', array('style' => 'padding:0px 2px 0px 0px;')).'Select Which type of History you want to review or capture <a href="'.$tutor_vid[7].'" '.$vid_blank.'>See Video</a></span>';
 
 $tutor_array[8]='<span class="small_notice" style="top:120px;right:25px;">'.$html->image('down_arrow_icon_small.png', array('style' => 'padding:0px 5px 0px 0px;')).' Search for a Diagnosis and select from the list of suggestions that will come up, or manually enter your own. Click on  your "Predefined Favorites" <br> for even quicker entry!  The rest of the elements  on this page are optional.  "Add to Problem List" is  for chronic problems and "Meaningful Use" criteria</span>';
 $tutor_array[9]='<span class="small_notice" style="top:160px;right:25px">'.$html->image('left_arrow_icon_small.png', array('style' => 'padding:0px 2px 0px 0px;')).' Search for a Diagnosis and select from the list of suggestions that will come up, or manually <br> enter your own. The rest of the elements on this page are optional. </span>';
 $tutor_array[10]='<span class="small_notice" style="top:160px;right:25px">'.$html->image('left_arrow_icon_small.png', array('style' => 'padding:0px 2px 0px 0px;')).'Choose a "Type" first, then the rest of the fields will update with options <br> applicable to the type you have chosen. Comment is optional.</span>';
 $tutor_array[11]='<span class="small_notice" style="top:160px;right:25px">'.$html->image('left_arrow_icon_small.png', array('style' => 'padding:0px 2px 0px 0px;')).'The person\'s Name, Relationship to patient, and Problem of the person needing to document is entered here.</span>';
 $tutor_array[13]='<div class="small_notice" style="position:relative;font-weight:normal;top:-5px">'.$html->image('down_arrow_icon_small.png', array('style' => 'padding:0px 5px 0px 0px;')).' Search for allergies, and drugs from the boxes below. A list of suggestions will come up to ensure accurate spelling or you may manually enter your own.  <a href="'.$tutor_vid[13].'" '.$vid_blank.'>See Video</a></div>';
 $tutor_array[14]='<div class="small_notice" style="position:relative;font-weight:normal;top:-5px">'.$html->image('down_arrow_icon_small.png', array('style' => 'padding:0px 4px 0px 0px;')).'Select your template if you wish, and select elements below as needed. 1 touch for negative, 2 touches for positive. Changes are instantly saved! <a href="'.$tutor_vid[14].'" '.$vid_blank.'>See Video</a> or <a href="'.$this->Session->webroot.'preferences/ros_template" '.$vid_blank.'>build Templates</a></div>';
 $tutor_array[15]='<span class="small_notice" style="top:125px;right:0px">'.$html->image('left_arrow_icon_small.png', array('style' => 'padding:0px 2px 0px 0px;')).'Vital signs are instantly saved, and quickly chart previous values by clicking on "Graph". <br> "Advanced" button allows for more detail as needed. <a href="'.$tutor_vid[15].'" '.$vid_blank.'>See Video</a></span>';
 $tutor_array[16]='<div class="small_notice" style="position:relative;font-weight:normal;top:-5px">'.$html->image('down_arrow_icon_small.png', array('style' => 'padding:0px 2px 0px 0px;')).'Select your template if you wish, and select elements below. Changes are instantly saved! <a href="'.$tutor_vid[16].'" '.$vid_blank.'>See Video</a> or  <a href="'.$this->Session->webroot.'preferences/pe_template" '.$vid_blank.'>build Templates</a> </div>';
 $tutor_array[17]='<div class="small_notice" style="position:relative;font-weight:normal;top:-5px">'.$html->image('down_arrow_icon_small.png', array('style' => 'padding:0px 2px 0px 0px;')).'Your "Point of Care" section. Select Which type you want to review or capture. Then, to order  a test click on it. The arrow on  right  loads   the data entry form. Practice Administrator sets these up. <a href="'.$tutor_vid[17].'" '.$vid_blank.'>See Video</a></div>';
 $tutor_array[18]='<span class="small_notice" style="top:70px;right:0px">'.$html->image('left_arrow_icon_small.png', array('style' => 'padding:0px 2px 0px 0px;')).'The "Results" area allows you to quickly review previous  Point of Care,<br> e-labs (Quest, Labcorp, etc.), and scanned Documents. <a href="'.$tutor_vid[18].'" '.$vid_blank.'>See Video</a></span>'; $tutor_array[19]='<div class="small_notice" style="position:relative;font-weight:normal;">'.$html->image('down_arrow_icon_small.png', array('style' => 'padding:0px 2px 0px 0px;')).'Capture your diagnosis quickly either by search, "Favorites" (<a href="'.$this->Session->webroot.'preferences/favorite_diagnoses" '.$vid_blank.'>add Favorites</a>), Chief Complaint, or Past diagnosis(es). Problem list is for "Meaningful use" <a href="'.$tutor_vid[19].'" '.$vid_blank.'>See Video</a></div>';
 $tutor_array[19]='<div class="small_notice" style="position:relative;font-weight:normal;">'.$html->image('down_arrow_icon_small.png', array('style' => 'padding:0px 2px 0px 0px;')).'Capture your diagnosis quickly either by search, "Favorites" (<a href="'.$this->Session->webroot.'preferences/favorite_diagnoses" '.$vid_blank.'>add Favorites</a>), Chief Complaint, or Past diagnosis(es). Problem list is for "Meaningful use" <a href="'.$tutor_vid[19].'" '.$vid_blank.'>See Video</a></div>';
 $tutor_array['19a']='<div class="small_notice" style="position:relative;font-weight:normal;float:right;top:0px;right:50px">'.$html->image('down_arrow_icon_small.png', array('style' => 'padding:0px 2px 0px 0px;')).'Assessment prints down here. Optionally, you can set the points for your biller to ensure accurate reimbursement. </div>';
 $tutor_array[20]='<div class="small_notice" style="position:relative;font-weight:normal;top:0px;left:0px">'.$html->image('down_arrow_icon_small.png', array('style' => 'padding:0px 2px 0px 0px;')).'Select the Assessment you want to work with...</div>';
 $tutor_array['20a']='<div class="small_notice" style="position:relative;font-weight:normal;top:0px;left:0px">'.$html->image('down_arrow_icon_small.png', array('style' => 'padding:0px 2px 0px 0px;')).'...then choose what you want to do for each one by choosing buttons, and you can also enter free text. <a href="'.$tutor_vid[20].'" '.$vid_blank.'>See Video</a> </div>';
 $tutor_array[21]='<div class="small_notice" style="position:relative;font-weight:normal;top:0px;left:0px">'.$html->image('down_arrow_icon_small.png', array('style' => 'padding:0px 2px 0px 0px;')).'Lastly, your Superbill gives summary of all charges for the visit. "Advanced" allows you to capture more codes. Mid-level providers can forward this encounter to their supervising physician. When finished, lock the encounter at the bottom with the PIN code you defined in Preferences. <a href="'.$tutor_vid[21].'" '.$vid_blank.'>See Video</a></div>';


// @TODO: PE Templates
// @TODO: ROS Templates

//preferences
$tutor_array[40]='<span class="notice" style="float:right;"><a href="http://youtu.be/MMq6uxYXamc" target=_blank>ROS Template Instructional Video</a></span>';
$tutor_array[41]='<span class="notice" style="float:right;"><a href="http://youtu.be/9wW3vtgz3Io" target=_blank>PE Template Instructional Video</a></span>';
$tutor_array[42]='<div class="small_notice" style="position:relative;float:right;right:65px">You can configure what appears in <br>your Patient Summary Tab <a href="'.$tutor_vid[42].'" '.$vid_blank.'>Watch Video</a></div>';
$tutor_array[43]='<div class="small_notice" style="position:relative;float:left;top:-20px;">Monitor your desired Health Maintenance items <a href="'.$tutor_vid[43].'" '.$vid_blank.'>Watch Video</a></div><br />';

//administration
$tutor_array[50]='<div class="notice">NOTE: You must add at least 1 Office in "'.$this->Html->link('Practice Locations', '/administration/practice_locations').'" (link button above) to use the system.</div>';

//calendar
$tutor_array[90]='<div class="notice">To get started and learn all the features of the calendar, <a href="'.$tutor_vid[90].'" '.$vid_blank.'>see Video</a></div>';

// patient portal
$tutor_array[100]='<div class="notice">If you would like to request an appointment, first choose a "Provider". The calendar will appear to show available slots, you can choose any white (empty) slot by clicking within a  block.</div>';
$tutor_array[101]='<div class="notice" style="margin-bottom:7px">Below is your personal demographic information. You may review or make updates where allowed. </div>';
$tutor_array[103]='<div class="notice" style="margin-bottom:7px">Below are the notes from previous visits with your provider. You may click on "Details" to see the full report.</div>';
$tutor_array[104]='<div class="notice" style="margin-bottom:7px">Below are the medications we have on record that you are taking. If this information is incorrect, please notify your provider.</div>';
$tutor_array[105]='<div class="notice" style="margin-bottom:7px">Below is the information we have regarding your history. You may review or make updates where allowed. </div>';
$tutor_array[106]='<div class="notice">Below is the Lab work we have completed within our facility. The "Outside Labs" tab are labs from outside vendors such as LabCorp or Quest.</div>';
$tutor_array[107]='<div class="notice">Below is the Labs from outside vendors such as LabCorp or Quest that have been approved for you to view.</div>';
$tutor_array[108]='<div class="notice" style="margin-bottom:7px">Below are your Allergies we have on file.</div>';
$tutor_array[109]='<div class="notice" style="margin-bottom:7px">Below are the problems actively being treated by your provider.</div>';
$tutor_array[110]='<div class="notice" style="margin-bottom:7px">These are forms which the practice may want you to download and print out. Check with the office.</div>';
$tutor_array[111]='<div class="notice" style="margin-bottom:7px">These are forms which the practice may want you to complete online. Check with the office.</div>';
$tutor_array[112]='<div class="notice" style="margin-bottom:7px">Do  you have any of the following medical conditions? Just tap or click on the ones which are present or type in the box if not found.</div>';
$tutor_array[113]='<div class="notice" style="margin-bottom:7px">Have you had any of the following surgeries? Just tap or click on the ones which are present or type in the box if not found.</div>';
$tutor_array[114]='<div class="notice" style="margin-bottom:7px">Please answer the following choices below. Click "Add" when finished.</div>';
$tutor_array[115]='<div class="notice" style="margin-bottom:7px">Please answer the following question by tapping or clicking on the the choices below. Click "Add" when finished.</div>';



 echo $tutor_array[$tutor_id];
}
?>
<script>  
function Tutor_mode()
{      
    var formobj = $("<form></form>");
	var tutor_mode_value = '<?php echo $tutor_mode; ?>';
	formobj.append('<input name="data[tutor_mode_value]" id="tutor_mode_value" type="hidden" value="'+tutor_mode_value+'">');
	//Passing values via post method to controller
	 $.post('<?php echo $this->Session->webroot; ?>preferences/user_options/', 
	 formobj.serialize(), 
	 function(data)
	     { 
	     },
	 'json'
	 );
	if(tutor_mode_value == 1)
	{
	     $('#notice_hide').hide();
	}
}	

</script>
