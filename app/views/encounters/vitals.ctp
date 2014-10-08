<?php
$encounter_id = (isset($this->params['named']['encounter_id'])) ? $this->params['named']['encounter_id'] : "";
echo $this->Html->script('ipad_fix.js');

$page_access = $this->QuickAcl->getAccessType("encounters", "ros");
echo $this->element("enable_acl_read", array('page_access' => $page_access));

if(isset($VitalItem['EncounterVital']))
{
    extract($VitalItem['EncounterVital']);
	for ($i = 1; $i <= 3; ++$i)
   {
   		if(strpos(${"blood_pressure$i"},"/"))
	        list(${"systolic$i"},${"diastolic$i"}) = explode("/",${"blood_pressure$i"}) ;		
		else{
			${"systolic$i"}="";${"diastolic$i"}="";
		}
	}
   if(strpos(${"english_height"},"'")){
	   list($feet,$inches)=explode("'",$english_height);
	}
   else{
   	$feet="";$inches="";
   }
   if((${"metric_height"})){
	   $mtrs = intval(($metric_height)/100);
	   $cmtrs = ($metric_height)%100;
	}
   else{
   	$mtrs="";$cmtrs="";
   }
	$Age = "";
}
//var_dump($VitalItems);
else
{

   for ($i = 1; $i <= 3; ++$i)
   {
   		$feet="";$inches="";
        ${"blood_pressure$i"} = "";
		${"diastolic$i"} = "";
		${"systolic$i"} = "";
        ${"position$i"} = "";
		${"exact_time$i"} = "";
		${"pulse$i"} = "";
		${"location$i"} = "";
		${"description$i"} = "";
		${"temperature$i"} = "";
		${"temperature_type$i"} = "";
		${"source$i"} = "";
   }
   $respiratory = "";
   $breath_pattern = "";
   $spo2 = "";
   $head_circumference ="";
   $waist = "";
   $hip = "";
   $bmi = "";
   $english_weight = "";
   $english_height = "";
   $english_height_inches = "";
   $Age = "";
   $mtrs="";$cmtrs="";
   $metric_weight = "";
   $metric_height = "";
}
$bp_count = isset($bp_count)?$bp_count:1;
$pulse_count = isset($pulse_count)?$pulse_count:1;
$temp_count = isset($temp_count)?$temp_count:1;
if(!isset($operation_scale))
	$operation_scale = "English";

if($exact_time1)
{
	$split_time = explode(':',$exact_time1);	
	$exact_time1 = ($exact_time1 and $exact_time1!="00:00:00")?($split_time[0].':'.$split_time[1]):"";				
}

?>
<?php echo $this->element("tutor_mode", array('tutor_mode' => $tutor_mode, 'tutor_id' => 15)); ?>
<table cellpadding="0" cellspacing="0" class="form">
   <tr>
       <td>  <a href="javascript:void(0);" class="btn section_btn" id="default" style="float: none;">Simple</a></td>
	   <td>  <a href="javascript:void(0);" class="btn section_btn" id="advanced" style="float: none;">Advanced</a></td>
       <!--<td><input type="radio" name="pageType" id="default" checked="checked" /></td>
       <td><label for="default">Simple</label></td>
       <td width="15">&nbsp;</td>
       <td><input type="radio" name="pageType" id="advanced" /></td>
       <td><label for="advanced">Advanced</label></td>-->
       <td width="15">&nbsp;</td>
       <td><span id="imgLoading" style="display: none;"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?></span></td>
   </tr>
   <tr><td colspan="7">&nbsp;</td></tr>
</table>
<form id="vitalsform" name="vitalsform" action="" method="post">
<div align="left">
		<!-- added condition, if advanced data available hide simple -->
        <table id='temp_table_default' <?php echo($temp_count>1 || $bp_count>1 || $pulse_count>1 || !empty(${"source1"}) || !empty(${"position1"}) || !empty(${"exact_time1"}) || !empty(${"location1"}) || !empty(${"description1"}))?'style="display:none;"':'' ?> border=0 style="margin-bottom:0px" class="form" width="auto">
           <tr>
	           <td width="115">Temperature: &nbsp;&nbsp;</td>
               <td width="150"><input size="6" maxlength="6" name="temperature" id="temperature" class="numeric_only" pattern="[0-9]*" value="<?php echo $temperature1; ?>" onblur="fmttemp('1');" type="text">&nbsp;<?php if($operation_scale=="English") echo "&deg;F"; else echo "&deg;C"; ?>&nbsp;</td>
			   <td  align="right"><a id='temperature_graph_link' name='temperature_graph_link'  class="btn graph_btn" chart="temperature1" field="temperature" style="float:none;">Graph </a></td>
           </tr>
		</table>
        <!-- added condition, if advanced data available show advanced -->
        <table id='temp_table_advanced' <?php echo($temp_count>1 || $bp_count>1 || $pulse_count>1 || !empty(${"source1"}) || !empty(${"position1"}) || !empty(${"exact_time1"}) || !empty(${"location1"}) || !empty(${"description1"}))?'':'style="display:none;"' ?> class="form">
		    <tr><td>
           <input type="hidden" name="temp_count" id="temp_count" value="<?php echo $temp_count ?>"/>
           <?php
            
            for ($i = 1; $i <= 3; ++$i)
            {
                echo "
                <table border=0 id=\"temp_table$i\" style=\"display:".(($i > 1 and $temp_count < $i)?"none":"table")."; margin-bottom:0px\" class=\"form\">
                <tr><td  width='115'>Temperature #$i: &nbsp;&nbsp;</td>
			  <td><input type='text' size='4' maxlength='4' name='temperature$i' id='temperature$i' value=\"".${"temperature$i"}."\" onblur=\"fmttemp('$i');\">&nbsp;"; if($operation_scale=="English"){ echo "&deg;F";} else{ echo "&deg;C";} echo "&nbsp;&nbsp;</td>
			  ";
			  
			  /*if($operation_scale == "Metric")
			  {
				  echo "<input type='hidden' name='temperature_type$i' id='temperature_type$i' value='Celcius' />";
				  echo "&nbsp;&#176C,";
				  
			  }
			  else
			  {
				  echo "<input type='hidden' name='temperature_type$i' id='temperature_type$i' value='Farenheit' />";
				  echo "&nbsp;&#176F";
			  }*/
        
				echo "
				  <td>&nbsp;&nbsp;&nbsp;&nbsp;Source:</td>
				  <td><select id='source$i' style='width: 130px;' name='source$i' onchange='saveInfo(this)'>
					<option value='' selected='selected'></option>
					<option value='Oral' ".(${"source$i"}=="Oral"?"selected":"").">Oral</option>
					<option value='Rectal' ".(${"source$i"}=="Rectal"?"selected":"").">Rectal</option>
					<option value='Eardum' ".(${"source$i"}=="Eardum"?"selected":"").">Eardrum</option>
				  </select>&nbsp;&nbsp;</td>
				<td valign=middle>";
                if ($i > 0 and $i < 3)
                {
                    if($temp_count > $i)
				    {
				       $display = 'display: none;';
				    }
				    else
				    {
				       $display = '';
				    }
				    echo "<a id='tempadd_$i' removeonread='true' class=btn style='float:none;".$display."' onclick=\"document.getElementById('temp_table".($i + 1)."').style.display='block';jQuery('#temp_count').val('".($i + 1)."');this.style.display='none'; increaseTempCount(); document.getElementById('tempdelete_".($i+1)."').style.display='';".($i>1?"document.getElementById('tempdelete_".$i."').style.display='none';":"")."\" ".($temp_count <= $i?"":"style=\"display:none\"").">Add</a>";
                }
                if ($i > 1 and $i <= 3)
                {
                    if($temp_count > $i)
				    {
				       $display = 'display: none;';
				    }
				    else
				    {
				       $display = '';
				    }
					echo "<a  id=\"tempdelete_$i\" removeonread='true' class=btn style='float:none;".$display."'  onclick=\"document.getElementById('temp_table".$i."').style.display='none';jQuery('#temp_count').val('".($i - 1)."');this.style.display='none'; decreaseTempCount(); document.getElementById('tempadd_".($i-1)."').style.display='';jQuery('#tempdelete_".($i-1)."').css('display', '');\" ".($temp_count <= $i?"":"style=\"display:none\"").">Delete</a>";
                }
                echo "</td></tr></table>";
            }
            
            ?>
			</td></tr>
	</table>
</div>
	<!-- added condition, if advanced data available hide simple -->
   <div id='bp_table_default' <?php echo($temp_count>1 || $bp_count>1 || $pulse_count>1 || !empty(${"source1"}) || !empty(${"position1"}) || !empty(${"exact_time1"}) || !empty(${"location1"}) || !empty(${"description1"}))?'style="display:none;"':'' ?> >
     <table border=0 style="margin-bottom:0px" class="form" width="auto">
     <tr>
        <td width="115">Blood Pressure:</td>
        <td width="150"><input size="2" style="width:25px" maxlength="3"  id="systolic" class="numeric_only" pattern="[0-9]*" value="<?php echo ${"systolic1"}; ?>" onblur="updatebp(this)" type="text">&nbsp;/&nbsp;<input size="2" maxlength="3" style="width:25px" class="numeric_only" pattern="[0-9]*" id="diastolic"  value="<?php echo ${"diastolic1"}; ?>" onblur="updatebp(this)" type="text">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		<input  type="hidden" id="blood_pressure" name="blood_pressure" value="<?php echo $blood_pressure1; ?>" >&nbsp;</td>
        <td align="right"><a id='bp_graph_link' name='bp_graph_link'  class="btn graph_btn" chart="blood_pressure1" field="systolic|diastolic" style="float:none;">Graph </a></td>
     </tr>
     </table>
   </div>
<?php
        
	echo '<input type="hidden" name="bp_count" id="bp_count" value="<?php echo $bp_count ?>"/>';
	?>
    <!-- added condition, if advanced data available show advanced -->
	<div id='bp_table_advanced' <?php echo($temp_count>1 || $bp_count>1 || $pulse_count>1 || !empty(${"source1"}) || !empty(${"position1"}) || !empty(${"exact_time1"}) || !empty(${"location1"}) || !empty(${"description1"}))?'':'style="display:none;"' ?> >
	<?php
		for ($i = 1; $i <= 3; ++$i)
		{
			if(${"exact_time$i"})
			{
				$split_time = explode(':',${"exact_time$i"});					
			}
			$exact_time = (${"exact_time$i"} and ${"exact_time$i"}!="00:00:00")?($split_time[0].':'.$split_time[1]):"";
			echo "<div id=\"bp_table$i\" style=\"display:".(($i > 1 and $bp_count < $i)?"none":"block").";\">
			<table border=0 style=\"margin-bottom:0px\" class=\"form\">
			<tr><td style='width:125px;'>Blood Pressure #$i:</td>
		    <td><input size=\"2\" style=\"width:25px\" maxlength=\"3\"  id=\"systolic$i\" pattern=\"[0-9]*\" value=\"".${"systolic$i"}."\" onblur=\"updatebp1('blood_pressure$i',$i);saveInfo(document.getElementById('blood_pressure$i'));\" type=\"text\">&nbsp;/&nbsp;<input size=\"2\" maxlength=\"3\" style=\"width:25px\"  id=\"diastolic$i\" pattern=\"[0-9]*\" value=\"".${"diastolic$i"}."\" onblur=\"updatebp1('blood_pressure$i', $i);saveInfo(document.getElementById('blood_pressure$i'));\" type=\"text\"><input type='hidden' size='4' maxlength='4' id='blood_pressure$i' name='blood_pressure$i' / value=\"".${"blood_pressure$i"}."\" onblur=''></td>
		    <td><table cellspacing=0 cellpadding=0><tr><td>&nbsp;Position:&nbsp;</td><td><select style='width: 90px;' name='position$i' id='position$i' class='dhtmlxsel' onchange='saveInfo(this)'>
			<option value='' selected='selected'></option>
			<option value='Sitting' ".(${"position$i"}=="Sitting"?"selected":"").">Sitting</option>
			<option value='Standing' ".(${"position$i"}=="Standing"?"selected":"").">Standing</option>
			<option value='Laying' ".(${"position$i"}=="Laying"?"selected":"").">Laying</option>
			<option value='Running' ".(${"position$i"}=="Running"?"selected":"").">Running</option>
		  </select></td>
		  <td>&nbsp;&nbsp;Exact Time:</td><td>&nbsp;<input type='text' id='exact_time$i' size='4' maxlength='4' name='exact_time$i' / onkeyup=\"calcMinutes($i)\" value=\"".$exact_time."\" onblur='saveInfo(this)' >&nbsp;</td><td><a href=\"javascript:void(0)\" id='exacttimebtn$i' onclick=\"showNow($i)\">".$html->image('time.gif', array('alt' => 'Time now'))."</a></td><td width=2></td><td>&nbsp;(<span id='minutes$i'>".(isset(${"minutes$i"})?${"minutes$i"}:"0")."</span> mins wait)&nbsp;&nbsp;</td>
";
			if ($i > 0 and $i < 3)
			{
				if($bp_count > $i)
				 {
				    $display = 'display: none;';
				 }
				 else
				 {
				    $display = '';
				 }
				echo "<td valign=middle> <a id='bpadd_$i' removeonread='true' class=btn style='float:none;".$display."' onclick=\"document.getElementById('bp_table".($i + 1)."').style.display='block';jQuery('#bp_count').val('".($i + 1)."');this.style.display='none'; increaseBpCount(); document.getElementById('bpdelete_".($i+1)."').style.display='';".($i>1?"document.getElementById('bpdelete_".$i."').style.display='none';":"")."\" ".($bp_count <= $i?"":"style=\"display:none\"").">Add</a></td>";
			}
			if ($i > 1 and $i <= 3)
			{
				if($bp_count > $i)
				{
				   $display = 'display: none;';
				}
				else
				{
				   $display = '';
				}
				echo "<td valign=middle> <a id=\"bpdelete_$i\" removeonread='true' class=btn style='float:none;".$display."' onclick=\"document.getElementById('bp_table".$i."').style.display='none';jQuery('#bp_count').val('".($i - 1)."');this.style.display='none'; decreaseBpCount(); document.getElementById('bpadd_".($i-1)."').style.display='';jQuery('#bpdelete_".($i-1)."').css('display', '');\" ".($bp_count <= $i?"":"style=\"display:none\"").">Delete</a></td>";
			}
			echo "<td style='padding:0px; margin:0px;'>*</td></tr></table><div class='alert_msg' id='bp$i'></div></td></tr></table></div>";
		}
        ?>
	</div>
    <input type="hidden" name="pulse_count" id="pulse_count" value="<?php echo $pulse_count ?>"/>
    <!-- added condition, if advanced data available hide simple -->
    <div id='pulse_table_default' <?php echo($temp_count>1 || $bp_count>1 || $pulse_count>1 || !empty(${"source1"}) || !empty(${"position1"}) || !empty(${"exact_time1"}) || !empty(${"location1"}) || !empty(${"description1"}))?'style="display:none;"':'' ?>>
    <table border=0 style="margin-bottom:0px" class="form" width="auto">
    <tr>
        <td width="115">Pulse:</td>
		<td width="150"><input size="6" name="pulse" id="pulse" class="numeric_only" pattern="[0-9]*" value="<?php echo $pulse1; ?>" onblur="updateVital('pulse1', this.value); " type="text">&nbsp;bpm&nbsp;</td>
		<td><a id='pulse_graph_link' name='pulse_graph_link' class="btn graph_btn" chart="pulse1" field="pulse" style="float:none;">Graph </a></td>
    </tr>
    </table>
    </div>
    <!-- added condition, if advanced data available show advanced -->
    <div id='pulse_table_advanced' <?php echo($temp_count>1 || $bp_count>1 || $pulse_count>1 || !empty(${"source1"}) || !empty(${"position1"}) || !empty(${"exact_time1"}) || !empty(${"location1"}) || !empty(${"description1"}))?'':'style="display:none;"' ?>>
    <?php
        for ($i = 1; $i <= 3; ++$i)
        {
            echo "<div id=\"pulse_table$i\" style=\"display:".(($i > 1 and $pulse_count < $i)?"none":"block").";\">"; ?>
          <table id="tb2" class="default_form" style="margin-bottom:0px " width="115%" border="0">
          <tr height="10">
             <td width='115'>Pulse #<?php echo $i ?>:</td>
             <td><table cellpadding="0" cellspacing="0" style="margin-bottom:-5px " border="0"><tr><td><input type="text" size="6" name="pulse<?php echo $i ?>" id="pulse<?php echo $i ?>" value="<?php echo ${"pulse$i"}; ?>" onblur="saveInfo(this)" />&nbsp;bpm</td>
		     <td>&nbsp;&nbsp;&nbsp;&nbsp;Location:&nbsp; </td>
             <td><select style="width: 120px;" name="location<?php echo $i ?>" id="location<?php echo $i ?>" onchange="saveInfo(this)">
				 <option value="" selected="selected"></option>
				 <option value="Radial" <?php echo (${"location$i"}=="Radial"?"selected":"") ?>>Radial</option>
				 <option value="Carotid" <?php echo (${"location$i"}=="Carotid"?"selected":"") ?>>Carotid</option>
				 <option value="Femoral" <?php echo (${"location$i"}=="Femoral"?"selected":"") ?>>Femoral</option>
				 <option value="Popliteal" <?php echo (${"location$i"}=="Popliteal"?"selected":"") ?>>Popliteal</option>
				 <option value="DorsalisPedis" <?php echo (${"location$i"}=="DorsalisPedis"?"selected":"") ?>>Dorsalis Pedis</option>
			     </select>
		     </td>
			 <td>&nbsp;&nbsp;&nbsp;&nbsp;Description:&nbsp;</td>
			 <td><select style="width: 130px;" name="description<?php echo $i ?>" id="description<?php echo $i ?>" onchange="saveInfo(this)">
				 <option value="" selected="selected"></option>
				 <option value="Weak" <?php echo (${"description$i"}=="Weak"?"selected":"") ?>>Weak</option>
				 <option value="Full" <?php echo (${"description$i"}=="Full"?"selected":"") ?>>Full</option>
				 <option value="Shallow" <?php echo (${"description$i"}=="Shallow"?"selected":"") ?>>Shallow</option>
				 <option value="Irregular" <?php echo (${"description$i"}=="Irregular"?"selected":"") ?>>Irregular</option>
				 <option value="HardlyPalpable" <?php echo (${"description$i"}=="HardlyPalpable"?"selected":"") ?>>Hardly Palpable</option>
			     </select></td>
			 <td valign=middle><?php
				if ($i > 0 and $i < 3)
				{
					if($pulse_count > $i)
				    {
				       $display = 'display: none;';
				    }
				    else
				    {
				       $display = '';
				    }
					echo "&nbsp;&nbsp;<a id='pulseadd_$i' removeonread='true' style='float:none;".$display."'  class='btn' onclick=\"document.getElementById('pulse_table".($i + 1)."').style.display='block';jQuery('#pulse_count').val('".($i + 1)."');this.style.display='none'; increasePulseCount(); document.getElementById('pulsedelete_".($i+1)."').style.display='';".($i>1?"document.getElementById('pulsedelete_".$i."').style.display='none';":"")."\" ".($pulse_count <= $i?"":"style=\"display:none\"").">Add</a>";
					
				}
				if ($i > 1 and $i <= 3)
				{
					if($pulse_count > $i)
				    {
				       $display = 'display: none;';
				    }
				    else
				    {
				       $display = '';
				    }					
					echo "&nbsp;&nbsp;<a  id=\"pulsedelete_$i\" removeonread='true' style='float:none;".$display."'  class='btn' onclick=\"document.getElementById('pulse_table".$i."').style.display='none';jQuery('#pulse_count').val('".($i - 1)."');this.style.display='none'; decreasePulseCount(); document.getElementById('pulseadd_".($i-1)."').style.display='';jQuery('#pulsedelete_".($i-1)."').css('display', '');\" ".($pulse_count <= $i?"":"style=\"display:none\"").">Delete</a>";

				} ?>
              </td>
			  </tr>
			  </table>
		  </td>
         <!--</tr>
		 <tr height="10">
          <td width='130'>&nbsp;</td>-->
          <td></td>
         </tr>
         <tr><td width='130'></td><td></td>
        </tr>
    </table></div><?php
    } ?>
</div>
<div>
<table class="form" width="auto">
    <tr>
       <td width='115'>Respiratory Rate:</td>
       <td width="150"><input type="text" size="6" name="respiratory" id="respiratory" class="numeric_only" pattern="[0-9]*" value="<?php echo $respiratory; ?>" onblur="saveInfo(this)"/>&nbsp;RR&nbsp;</td>
       <td align="right"><a id='respiratory_graph_link' name='respiratory_graph_link' class="btn graph_btn" chart="respiratory" field="respiratory" style="float:none;">Graph </a></td>
    </tr>
</table>
<!--<table class="form" width="310">
    <tr id="breath_pattern_row" style="display:none;"> 
       <td>Breathing Pattern:</td>
       <td><select style="width: 120px;" name="breath_pattern" id="breath_pattern" onchange="saveInfo(this)">
        <option value="" selected="selected"></option>
        <option value="Shallow" <?php echo ($breath_pattern=="Shallow"?"selected":"") ?>>Shallow</option>
        <option value="Rapid" <?php echo ($breath_pattern=="Rapid"?"selected":"") ?>>Rapid</option>
        <option value="Deep" <?php echo ($breath_pattern=="Deep"?"selected":"") ?>>Deep</option>
        <option value="Cheyne-Stokes" <?php echo ($breath_pattern=="Cheyne-Stokes"?"selected":"") ?>>Cheyne-Stokes</option>
        </select></td>
    </tr>
</table>-->
<table class="form" width="auto">
    <tr>
      <td width='115'>SpO2:</td>
      <td width="150"><input type="text" id="spo2" size="6" class="numeric_only" maxlength="6" name="spo2" pattern="[0-9]*" value="<?php echo $spo2; ?>" onblur="saveInfo(this)" />&nbsp;%&nbsp;</td>
      <td align="right"><a id='spo2_graph_link' name='spo2_graph_link' class="btn graph_btn" chart="spo2" field="spo2" style="float:none;">Graph </a></td>
    </tr>
</table>
</div>
<div>
<table cellpadding="0" cellspacing="0">
    <tr style='display: <?php echo ($operation_scale=="English"?"table-row":"none") ?> '>
      <td colspan="2">
      <table class="form" style="margin-bottom:0px " width="auto">
        <tr>
          <td width="115">Height: </td>
          <td width="150">
			  <input type="text"  style="width:15px"  id="feet" class="numeric_only" pattern="[0-9]*" size="1" maxlength="2" onblur="checkHeight(this);" value="<?php echo ($feet!='')?$feet:''; ?>"/>&nbsp;ft&nbsp;<input type="text" style="width:30px" id="inches"  pattern="[0-9]*"  maxlength="9" onblur="checkHeight(this); " value="<?php echo trim($inches); ?>"/>&nbsp;in&nbsp;
            	<input type="hidden"  name="english_height" id="english_height" size="6" maxlength="1"  value="<?php echo ($english_height!=0)?$english_height:''; ?>"/></td>
                
                <td align="right"><a id='height_graph_link' name='height_graph_link' class="btn graph_btn" chart="english_height" field="feet|inches" style="float:none;">Graph </a></td>      
		</tr>
     </table>
     <table class="form" style="margin-bottom:0px " width="auto">
        <tr>
          <td width="115">Weight: </td>
          <td width="150">
            <input type="text" name="english_weight" id="english_weight" class="numeric_only" pattern="[0-9]*" size="6" maxlength="6" onblur="saveInfo(this); calcEnglishBMI();" value="<?php echo (($english_weight!=0)?($english_weight):''); ?>" />&nbsp;lb&nbsp;</td>
            <td align="right"><a id='weight_graph_link' name='weight_graph_link' class="btn graph_btn" chart="english_weight" field="english_weight" style="float:none;">Graph </a></td>
        </tr>
      </table>
	  </td>
    </tr>
    <tr style="display: <?php echo ($operation_scale=="Metric"?"table-row":"none") ?> ">
      <td colspan="2"><table class="form" style="margin-bottom:0px ">
          <tr>
            <td width="115">Height: </td>
            <td width="150">
			  <input type="text"  style="width:15px"  id="mtrs" pattern="[0-9]*" size="1" maxlength="1" onblur="checkmetricHeight(this);" value="<?php echo ($mtrs!=0)?$mtrs:''; ?>"/>&nbsp;m&nbsp;<input type="text" style="width:27px" id="cmtrs" pattern="[0-9]*" size="2" maxlength="2" onblur="checkmetricHeight(this); " value="<?php echo ($cmtrs!=0)?$cmtrs:''; ?>"/>&nbsp;cm&nbsp;
              <input name="metric_height" id="metric_height" type="hidden" size="6" maxlength="6"  value="<?php echo ($metric_height!=0)?$metric_height:''; ?>"/></td><td align="right"><a id='height_graph_link' name='height_graph_link' class="btn graph_btn" chart="metric_height" field="mtrs|cmtrs" style="float:none;">Graph </a>
		  </td>
          </tr>
          <tr>
            <td  width="115"><div align="left">Weight:</div></td>
            <td width="150">
              <input type="text" name="metric_weight" id="metric_weight" size="6" maxlength="5" pattern="[0-9]*" onblur="calcMetricBMI();saveInfo(this)" value="<?php echo ($metric_weight!=0)?$metric_weight:''; ?>"/>&nbsp;
              kg&nbsp;&nbsp;&nbsp;</td><td align="right"><a id='weight_graph_link' name='weight_graph_link' class="btn graph_btn" chart="metric_weight" field="metric_weight" style="float:none;">Graph </a>
		  </td>
		  
          </tr>
      </table></td>
    </tr>
	<tr>
      <td colspan="2"><table class="form" width="auto">
        <tbody>
          <tr>
		  <td width="115"><div align="left">BMI:</div></td>
          <td  width="150">
            <input type="text" name="bmi" id="bmi" size="6" maxlength="6" readonly="readonly" value="<?php echo ($bmi!=0)?$bmi:''; ?>"/ style="background-color:#EEEEEE " ><!--<a href="javascript:void(0)" onclick="calcEnglishBMI()" >Calculate</a>&nbsp;&nbsp;--><span id="bmierror" name="bmierror"></span>
          </td>
		  <td align="right"><a id='bmi_graph_link' name='bmi_graph_link' class="btn graph_btn" chart="bmi" field="feet|inches|english_weight" style="float:none;">Graph </a></td>
		  </tr>
		</tbody>
	</table>
	</td>
	</tr>
<tr>
      <td colspan="2">
      <table class="form" width="auto">
          <tr>
            <td width="115"><div align="left">Head Circ:</div></td>
            <td width="150">
              <input type="text" name="head_circumference" id="head_circumference" class="numeric_only" pattern="[0-9]*" size="6" maxlength="6" value="<?php echo (($head_circumference!=0)?($head_circumference):''); ?>" onblur="saveInfo(this)"/>&nbsp;<?php if($operation_scale=="English") echo "in"; else echo "cm"; ?>&nbsp;
            </td>
			<td align="right"><a id='head_circumference_link' name='head_circumference_link' class="btn graph_btn" chart="head_circumference" field="head_circumference" style="float:none;">Graph </a></td>
          </tr>
      </table>
      <table class="form" width="auto">
          <tr>
            <td width="115"><div align="left">Waist:</div></td>
            <td width="150">
              <input type="text" name="waist" id="waist" class="numeric_only" pattern="[0-9]*" size="6" maxlength="6" value="<?php echo (($waist!=0)?($waist):''); ?>" onblur="saveInfo(this)"/>&nbsp;<?php if($operation_scale=="English") echo "in"; else echo "cm"; ?>&nbsp;
            </td>
			<td align="right"><a id='waist_graph_link' name='waist_graph_link'  class="btn graph_btn" chart="waist" field="waist" style="float:none;">Graph </a></td>
          </tr>
      </table>
      <table class="form" width="auto">
          <tr>
            <td width="115"><div align="left">Hip:</div></td>
            <td width="150">
              <input type="text" name="hip" id="hip" class="numeric_only" pattern="[0-9]*" size="6" maxlength="6" value="<?php echo (($hip!=0)?($hip):''); ?>" onblur="saveInfo(this)"/>&nbsp;<?php if($operation_scale=="English") echo "in"; else echo "cm"; ?>&nbsp;
            </td>
			<td align="right"><a id='hip_graph_link' name='hip_graph_link' class="btn graph_btn" chart="hip" field="hip" style="float:none;">Graph </a></td>
          </tr>
	   </table>
			<table class="form" <?php if($gender!="F") echo "style='display:none'";  ?> >
			  <tr><td width="115" style="vertical-align: top;">Last Menstrual:</td>
			    <td style="vertical-align: top;">Start&nbsp;</td><td valign='top'><?php echo $this->element("date", array('name' => 'data[EncounterVital][last_menstrual_start]', 'id' => 'last_menstrual_start', 'value' => (isset($last_menstrual_start) and (!strstr($last_menstrual_start, "0000")))?date($global_date_format, strtotime($last_menstrual_start)):'', 'required' => false , 'onselect' => 'function(){ saveInfo(this); }')); ?></td>
				<td style="vertical-align: top;">&nbsp;&nbsp;End&nbsp;</td><td valign='top'><?php echo $this->element("date", array('name' => 'data[EncounterVital][last_menstrual_end]', 'id' => 'last_menstrual_end', 'value' => (isset($last_menstrual_start) and (!strstr($last_menstrual_end, "0000")))?date($global_date_format, strtotime($last_menstrual_end)):'', 'required' => false, 'onselect' => 'function(){ saveInfo(this); }')); ?></td>						
				<td style="vertical-align: top;">&nbsp;&nbsp;<a id='last_menstrual_graph_link' name='last_menstrual_graph_link'  class="btn" style="float:none;" onclick="showLineChartframe('last_menstrual_start')">Graph </a></td>
			</tr>
		</table>
		</td>
    </tr>
</table>
</div>
</form>
<div id="growthchartContainer">
<?php if($age<=240 && $age>=0) { ?>
		<a class="btn" value="" id="weight_age" onclick="showChartframe(this.id)">Weight-Age Chart</a>
		<a class="btn" value="" id="length_age" onclick="showChartframe(this.id)">Stature-Age Chart</a>				
		<a class="btn" value="" id="weight_length" onclick="showChartframe(this.id)">Weight-Stature Chart</a>
		<?php if($age<=240 && $age>24) { ?>
		<a class="btn" value="" id="bmi_age" onclick="showChartframe(this.id)">BMI-Age Chart</a>
		<?php } if($age<=24 && $age>0) { ?>
		<a class="btn" value="" id="headcircumference_age" onclick="showChartframe(this.id)">Head Circumference-Age Chart</a>
		<?php } ?>				  
<?php } ?>
<div class="clear"></div>	
<div id="error_message_vital" class="error" style="float: left; width: 685px; margin-top: 10px; display: none;"></div>			 
</div>

<div id="linechartContainer2">
<div id="linechart_close2" title="close chart"></div>
<iframe id="chartIFrame" name="chartIFrame" src="" style="display:none; border-color:#FFFFFF;" scrolling="no" height="700" width="800" frameBorder="0" align="left"></iframe>
</div>
<div id="linechartContainer">
<div id="linechart_close" title="close chart"></div>
<iframe id="linechartIFrame" name="linechartIFrame" src="" style="display:none;" scrolling="no" height="450" width="800" frameBorder="0" align="left"></iframe>
</div>
<script type="text/javascript">

function addErrorToField(fields)
{
	$("#linechartContainer").hide();
	$('#linechartContainer2').hide();
	$('.vital_error_field').remove();
	
	$('.error_fields').each(function()
	{
		$(this).removeClass("error");
		$(this).removeClass("error_fields");
	});
	
	for(var i in fields)
	{
		var field = $('#'+fields[i]);
		
		var parent_tr = field.parents("tr");
		var total_column = $('td', parent_tr).length;
		
		var parent_table = field.parents("table.form");
		
		$('.vital_error_field', parent_table).remove();
		
		var html = '<tr class="vital_error_field"><td colspan="'+total_column+'"><div class="error">This field is required.</div></td></tr>';
		
		parent_table.append(html);
		
		field.addClass("error").addClass("error_fields");
		
		field.keyup(function()
		{
			$(this).removeClass("error");
			$(this).removeClass("error_fields");
			$('.vital_error_field', $(this).parents("table.form")).remove();
		});
	}
}

$(document).ready(function() {

	$('#last_menstrual_start, #last_menstrual_end').bind('blur', function(){
		if (!$('#ui-datepicker-div').is(':visible')) {
			saveInfo(this);	
		}
	});


	$('.graph_btn').click(function()
	{
		$('#error_message_vital').hide();
		$("#linechartContainer").hide();
		$('#linechartContainer2').hide();
		$('.vital_error_field').remove();
		
		$('.error_fields').each(function()
		{
			$(this).removeClass("error");
			$(this).removeClass("error_fields");
		});
	
		var chart = $(this).attr("chart");
		var fields = new String($(this).attr("field"));
		
		var fields_array = fields.split("|");
		
		var has_content = true;
		
		for(var i in fields_array)
		{
			if($('#'+fields_array[i]).val() == "")
			{
				has_content = false;
			}
		}
		
		if(has_content)
		{
			showLineChartframe(chart);
		}
		else
		{
			var parent_tr = $(this).parents("tr");
			var total_column = $('td', parent_tr).length;
			
			var parent_table = $(this).parents("table.form");
			
			var html = '<tr class="vital_error_field"><td colspan="'+total_column+'"><div class="error">This field is required.</div></td></tr>';
			
			parent_table.append(html);
			
			//add error class to fields
			for(var i in fields_array)
			{
				if($('#'+fields_array[i]).val() == "")
				{
					$('#'+fields_array[i]).addClass("error").addClass("error_fields");
				}
			}
			
			$('.error_fields').keyup(function()
			{
				$(this).removeClass("error");
				$(this).removeClass("error_fields");
				$('.vital_error_field', $(this).parents("table.form")).remove();
			});
		}
	});
	
	$("#blood_pressure").change( function() {
		var tempval=$("#blood_pressure").val();
  		$("#blood_pressure1").val(tempval);
	});
	$("#pulse").change( function() {
		var tempval=$("#pulse").val();
  		$("#pulse1").val(tempval);
	});
	$("#temperature").change( function() {
		var tempval=$("#temperature").val();
  		$("#temperature1").val(tempval);
	});
	$("#blood_pressure1").change( function() {
		var tempval=$("#blood_pressure1").val();
  		$("#blood_pressure").val(tempval);
	});
	$("#pulse1").change( function() {
		var tempval=$("#pulse1").val();
  		$("#pulse").val(tempval);
	});
	$("#temperature1").change( function() {
		var tempval=$("#temperature1").val();
  		$("#temperature").val(tempval);
	});

    $("#default").click(function()
	{
        $("#bp_table_default").css('display','table');
		$("#bp_table_advanced").css('display','none');	
		
		$("#pulse_table_default").css('display','table');
		$("#pulse_table_advanced").css('display','none');
			
		$("#temp_table_default").css('display','table');
		$("#temp_table_advanced").css('display','none');
		
		$("#breath_pattern_row").css('display','none');		
		//$("#growthchartContainer").css("margin-top", "-500px");
		if($("#linechartIFrame").attr('src') == ''){
			$("#linechartContainer").hide(0);
		} else{
			$("#linechartContainer").show(0);	
		}
		if($("#chartIFrame").attr('src') == ''){
			$("#linechartContainer2").hide(0);
		} else{
			$("#linechartContainer2").show(0);	
		}
	});
	$("#advanced").click(function()
	{
		$("#bp_table_advanced").css('display','table');
		$("#bp_table_default").css('display','none');	
		
		$("#pulse_table_advanced").css('display','table');
		$("#pulse_table_default").css('display','none');
			
		$("#temp_table_advanced").css('display','table');
		$("#temp_table_default").css('display','none');	
		
		$("#breath_pattern_row").css('display','table-row');
		$("#linechartContainer").hide(0); 
		$("#linechartContainer2").hide(0);
		$('#encounter_content_area').css('height','auto');	
		//$("#growthchartContainer").css("margin-top", "0px"); 
	});
	$("#linechart_close").click(function(){
		$("#linechartContainer").hide(0); 
		$("#linechartIFrame").attr('src','');
		$('#encounter_content_area').css('height','auto');		
	});
	$("#linechart_close2").click(function(){
		$("#linechartContainer2").hide(0); 
		$("#chartIFrame").attr('src',''); 		
		$('#encounter_content_area').css('height','auto');
	});
	for (i = 1; i <= 3; ++ i)
    {
        calcMinutes(i);
    }
	<?php echo $this->element('dragon_voice'); ?>
});
function fmttemp(index){
	temperatureid='temperature'+index;

		var temperatureobj = document.getElementById(temperatureid);

	if($('#'+temperatureid).val() !== "")
	{
		temp_val = new Number(temperatureobj.value);
		save_temp_val = temp_val.toFixed(1);
		$('#'+temperatureid).val(save_temp_val);

		if(index == 1)
		{
			$('#temperature').val(save_temp_val);
		}
	}
	
	
	saveInfo(temperatureobj);
}

// Show the current Date and Time
function showNow(i)
{
    var currentTime = new Date();
    var hours = currentTime.getHours();
    var minutes = currentTime.getMinutes();

    if (minutes < 10)
        minutes = "0" + minutes;

    var time = hours + ":" + minutes ;
        document.getElementById('exact_time'+i).value=time;
    updateVital('exact_time'+i, time);
    calcMinutes(i);
}

function calcMinutes(i)
{
    if (i > 1)
    {
        var time1 = document.vitalsform.elements["exact_time"+(i - 1)].value;
        var time2= document.vitalsform.elements["exact_time"+i].value;
        time1 = time1.split(":")
        time2 = time2.split(":")
        time1 = parseInt(time1[0] * 60) + parseInt(time1[1]);
        time2 = parseInt(time2[0] * 60) + parseInt(time2[1]);
        var time = parseInt(time2) - parseInt(time1);
        if (isNaN(time))
        {
            time = 0;
        }
        document.getElementById('minutes'+i).innerHTML = time;
    }
}

function updatebp(obj){
	var systolicobj = document.getElementById('systolic');
	var diastolicobj = document.getElementById('diastolic');
	
	if($('#systolic').val() == "" && $('#diastolic').val() == "")
	{
		return;
	}
	
	if($('#systolic').val() != "")
	{
		if($('#diastolic').val() == "")
		{
			$('#diastolic').val('');
		}
	}
	
	if($('#diastolic').val() != "")
	{
		if($('#systolic').val() == "")
		{
			$('#systolic').val('');
		}
	}
	
	var blood_pressureobj = document.getElementById('blood_pressure');
	blood_pressureobj.value=systolicobj.value+"/"+diastolicobj.value;
	updateVital('blood_pressure1', blood_pressureobj.value)
}
function updatebp1(obj,index){
	sysid='systolic'+index;
	diaid='diastolic'+index;
	bpid='blood_pressure'+index;
	var systolicobj = document.getElementById(sysid);
	var diastolicobj = document.getElementById(diaid);
	var blood_pressureobj = document.getElementById(bpid);
	blood_pressureobj.value=systolicobj.value+"/"+diastolicobj.value;
	updateVital(bpid, blood_pressureobj.value)
}


//Check Height
function checkHeight(obj){
	if($('#inches').val() != "")
	{
		if($('#feet').val() == "")
		{
			$('#feet').val('');
		}
	}
	
	if($('#feet').val() != "")
	{
		if($('#inches').val() == "")
		{
			$('#inches').val('');
		}
	}
	
	var inches = $('#inches').val();
        var feet = parseFloat($('#feet').val());
	if(inches >= 12)
	{
		var feet_val = Math.floor(inches / 12);
		var inch_val = inches % 12;
		
		feet = feet + feet_val;
		inches = inch_val;
		
		$('#inches').val(inches);
		$('#feet').val(feet);
	}
	
	var heightobj = document.getElementById('english_height');
	
	if($('#feet').val() != "" || $('#inches').val() != "")
	{
		heightobj.value = feet+"' "+inches+'"';
		saveInfo(heightobj);
	}
	
	calcEnglishBMI();
}
function checkmetricHeight(obj){
	var cmtrs  = parseInt(document.getElementById('cmtrs').value);
    var mtrs = parseInt(document.getElementById('mtrs').value);
	var heightobj = document.getElementById('metric_height');
	heightobj.value = ((mtrs)*100)+(cmtrs);
	calcMetricBMI(); saveInfo(heightobj);		
}

// Calculate English BMI
function calcEnglishBMI()
{
    document.getElementById('bmierror').innerHTML="";
    document.getElementById('bmi').value="";
	var height = document.vitalsform.elements["english_height"].value;
	var splitted_height = height.split("'");
    var feet = parseInt(splitted_height[0]);
    var inches = parseInt(splitted_height[1]);
	
    var weight = document.vitalsform.elements["english_weight"].value;
	
    if ((! inches) || isNaN(inches))
     inches = 0
     if ((! inches) || isNaN(inches))
     inches = 0
    var TotalInches = parseInt(feet*12) + (inches)
    var bmi = Math.round((weight * 703*10) / (TotalInches * TotalInches))/10; 
    if ((! bmi) || isNaN(bmi))
    {
     //document.getElementById('english_bmierror').innerHTML="<font color='#FF0000'>Missing or bad data</font>";
    }
    else
    {
        document.getElementById('bmi').value=parseFloat(bmi);
	 
	    //Insert BMI to DB
	    updateVital('bmi', parseFloat(bmi).toString());
    }
	
}

// calculate the BMI in Metric
function calcMetricBMI() {
   document.getElementById('bmierror').innerHTML="";
   document.getElementById('bmi').value="";
   var height = (document.vitalsform.elements["metric_height"].value)/100;
   var weight = document.vitalsform.elements["metric_weight"].value;
   var bmi = Math.round((weight *10) / (height * height))/10;
   if ((!bmi) || isNaN(bmi))
    {
     //document.getElementById('metric_bmierror').innerHTML="<font color='#FF0000'>Missing or bad data</font>";
    }
    else
    {
        document.getElementById('bmi').value=parseFloat(bmi);	 	 
	    //Insert BMI to DB
        updateVital('bmi', parseFloat(bmi).toString());
    }
	 
}

function showLineChartframe(type)
{	
	$('#encounter_content_area').css('height','auto');
	$("#linechartContainer").show();
	$('#linechartContainer2').hide();
	$("#chartIFrame").attr('src','');
	IframeUrl = '<?php echo $this->Session->webroot; ?>encounters/vitals/encounter_id:<?php echo $encounter_id; ?>/task:linechart/name:'+type;
	$("#linechartIFrame").attr("src", IframeUrl); 
	$("#linechartIFrame").css("display", ""); 
}

function showVitalError(msg)
{
	$('#error_message_vital').html(msg);
	$('#error_message_vital').show();
}

function hideVitalError()
{
	$('#error_message_vital').hide();
}

function showChartframe(type)
{	
	//	if($("#bp_table_advanced").css('display')=="block" || $("#linechartIFrame").css('display')=="block"){
	//		$("#growthchartContainer").css("margin-top", "0px"); 
	//		$("#growthchartContainer").css("margin-right", "225px"); 
	//	}
	//	else{
	//		$("#growthchartContainer").css("margin-top", "-500px"); 
	//	}
	hideVitalError();
	
	$('#linechartContainer2').show();
	$('#encounter_content_area').css('height','830px');
	$("#linechartContainer").hide(0);
	$("#linechartIFrame").attr('src','');
    
	if(type=="weight_age"){
		var weightValue = $("#english_weight").val();
		if(weightValue != "")
		{
			uniqueId = Math.random();
			var split_weight = weightValue.split('lb');
			weightValue = split_weight[0];
			IframeUrl = '<?php echo $this->Session->webroot; ?>encounters/vitals/encounter_id:<?php echo $encounter_id; ?>/task:growthchart/weight:'+weightValue+'/';
			$("#graph_loading").css("display", "block");
			$("#chartIFrame").attr("src", IframeUrl); 
			$("#chartIFrame").css("display", "block"); 
			$("#graph_loading").css("display", "none");
		}
		else
		{
			$("#chartIFrame").hide();
            $('#linechartContainer2').hide();
			
			showVitalError('Please put in data for Weight.');
			
			addErrorToField(['english_weight']);
		}
	}
	else if(type=="length_age"){
		var heightValue = $("#english_height").val();
		if(heightValue != "")
		{
			uniqueId = Math.random();
			var split_height = heightValue.split("'");
			heightValue = parseInt(split_height[0]*12)+parseInt(split_height[1]);
			IframeUrl = '<?php echo $this->Session->webroot; ?>encounters/vitals/encounter_id:<?php echo $encounter_id; ?>/task:growthchart/height:'+heightValue+'/';
			$("#graph_loading").css("display", "block");
			$("#chartIFrame").attr("src", IframeUrl); 
			$("#chartIFrame").css("display", "block"); 
			$("#graph_loading").css("display", "none");
		}
		else
		{
			$("#chartIFrame").css("display", "none"); 
            $('#linechartContainer2').hide();
			//$('#weight_error').html('<div htmlfor="Weight" generated="true" class="error">This field is required.</div>');
			//$('#Weight').addClass("error");
			
			showVitalError('Please put in data for Height.');
			
			addErrorToField(['feet', 'inches']);
		}
	}
	else if(type=="headcircumference_age"){
		var headcircumferenceValue = $("#head_circumference").val();
		if(headcircumferenceValue != "")
		{
			uniqueId = Math.random();
			var split_headcircumference = headcircumferenceValue.split('in');
			headcircumferenceValue = split_headcircumference[0];
			IframeUrl = '<?php echo $this->Session->webroot; ?>encounters/vitals/encounter_id:<?php echo $encounter_id; ?>/task:growthchart/headcircumference:'+headcircumferenceValue+'/';
			$("#graph_loading").css("display", "block");
			$("#chartIFrame").attr("src", IframeUrl); 
			$("#chartIFrame").css("display", "block"); 
			$("#graph_loading").css("display", "none");
		}
		else
		{
			$("#chartIFrame").css("display", "none");
            $('#linechartContainer2').hide();
			//$('#weight_error').html('<div htmlfor="Weight" generated="true" class="error">This field is required.</div>');
			//$('#Weight').addClass("error");
			
			showVitalError('Please put in data for Head Circumference.');
			
			addErrorToField(['head_circumference']);
		}
	}
	else if(type=="weight_length"){
		var weightValue = $("#english_weight").val();
		var heightValue = $("#english_height").val();
		if(weightValue != "" && heightValue != "")
		{
			uniqueId = Math.random();
			var split_weight = weightValue.split('lb');
			weightValue = split_weight[0];
			var split_height = heightValue.split("'");
			heightValue = parseInt(split_height[0]*12)+parseInt(split_height[1]);
			IframeUrl = '<?php echo $this->Session->webroot; ?>encounters/vitals/encounter_id:<?php echo $encounter_id; ?>/task:growthchart/weight:'+weightValue+'/height:'+heightValue+'/';
			$("#graph_loading").css("display", "block");
			$("#chartIFrame").attr("src", IframeUrl); 
			$("#chartIFrame").css("display", "block"); 
			$("#graph_loading").css("display", "none");
		}
		else
		{
			$("#chartIFrame").css("display", "none"); 
            $('#linechartContainer2').hide();
			//$('#weight_error').html('<div htmlfor="Weight" generated="true" class="error">This field is required.</div>');
			//$('#Weight').addClass("error");
			
			showVitalError('Please put in data for Weight and Height.');
			
			addErrorToField(['feet', 'inches', 'english_weight']);
		}
	}
	else if(type=="bmi_age"){
		var bmi = $("#bmi").val();
		if(bmi != "" )
		{
			uniqueId = Math.random();
			IframeUrl = '<?php echo $this->Session->webroot; ?>encounters/vitals/encounter_id:<?php echo $encounter_id; ?>/task:growthchart/bmi:'+bmi+'/';
			$("#graph_loading").css("display", "block");
			$("#chartIFrame").attr("src", IframeUrl); 
			$("#chartIFrame").css("display", "block"); 
			$("#graph_loading").css("display", "none");
		}
		else
		{
			$("#chartIFrame").css("display", "none"); 
            $('#linechartContainer2').hide();
			//$('#weight_error').html('<div htmlfor="Weight" generated="true" class="error">This field is required.</div>');
			//$('#Weight').addClass("error");
            
            showVitalError('Please put in data for Weight and Height.');
			addErrorToField(['feet', 'inches', 'english_weight']);
		}
	}
}

function saveInfo(data)
{
    var field_id = data.id;
	var field_val = data.value;
    updateVital(field_id, field_val);
}
function updateVital(field_id, field_val)
{
	if($.trim(field_val) == "")
	{
		//return;
	}
	
	var myRegExp = /_/;
    var matchPosition = field_val.search(myRegExp);
    if(matchPosition == -1)
    {
		if(field_id=="last_menstrual_start" || field_id=="last_menstrual_end"){
			field_val=mysqlDate(field_val);
		}
	   // var formobj = $("<form></form>");
	   // formobj.append('<input name="data[submitted][id]" type="hidden" value="'+field_id+'">');
	   // formobj.append('<input name="data[submitted][value]" type="hidden" value="'+field_val+'">');

	    $.post('<?php echo $this->Session->webroot; ?>encounters/vitals/encounter_id:<?php echo $encounter_id; ?>/task:edit/', {'data[submitted][id]':field_id, 'data[submitted][value]':field_val}, 
	    function(data){ }
	    );
        initAutoLogoff();
	}		
}
function mysqlDate(dateStr)
{
	if(dateStr.indexOf("/")!=-1)
		dArr = dateStr.split("/");  
	else
		dArr = dateStr.split("-");  
  return dArr[2]+ "-" +dArr[0]+ "-" +dArr[1]; 
}

function updateVitalEmpty(field_id, field_val)
{
	var formobj = $("<form></form>");
	formobj.append('<input name="data[submitted][id]" type="hidden" value="'+field_id+'">');
	formobj.append('<input name="data[submitted][value]" type="hidden" value="'+field_val+'">');

	$.post('<?php echo $this->Session->webroot; ?>encounters/vitals/encounter_id:<?php echo $encounter_id; ?>/task:edit/', formobj.serialize(), 
	function(data){ }
	);
      initAutoLogoff();
}
function increaseBpCount()
{
	var bp_count = $("#bp_count").val();
    updateVital('bp_count', bp_count);	
}
function decreaseBpCount()
{
	var bp_count = $("#bp_count").val();
	var formobj = $("<form></form>");
	updateVital('bp_count', bp_count);

	for(var i = parseInt(bp_count) + 1; i <= 3; i++)
	{
	    var inner_variables = ["blood_pressure", "position", "exact_time"];
		for(j=0; j< inner_variables.length; j++)
		{
			updateVitalEmpty(inner_variables[j]+i, "");
		}
	}
}

function increasePulseCount()
{
	var pulse_count = $("#pulse_count").val();
    updateVital('pulse_count', pulse_count);
}
function decreasePulseCount()
{
	var pulse_count = $("#pulse_count").val();
	updateVital('pulse_count', pulse_count);
    
	for(var i = parseInt(pulse_count) + 1; i <= 3; i++)
	{
	    var inner_variables = ["pulse", "location", "description"];
	    for(j=0; j< inner_variables.length; j++)
	    {
		    updateVitalEmpty(inner_variables[j]+i, "");
	    }
	}
}

function increaseTempCount()
{
	var temp_count = $("#temp_count").val();
	updateVital('temp_count', temp_count);	
}
function decreaseTempCount()
{
	var temp_count = $("#temp_count").val();
	updateVital('temp_count', temp_count);

	for(var i = parseInt(temp_count) + 1; i <= 3; i++)
	{
	    var inner_variables = ["temperature", "source"];
	    for(j=0; j< inner_variables.length; j++)
	    {
		   updateVitalEmpty(inner_variables[j]+i, "");
	    }
	}
}
</script>
