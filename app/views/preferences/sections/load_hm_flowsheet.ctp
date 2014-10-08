<script>
function increaseHmCount()
{
        var hm_count = $("#hm_count").val();
}
function decreaseHmCount(id, field1)
{
	$(document.getElementById("hmtest[" + field1 + "]")).val("");
	$(document.getElementById("hmtype[" + field1 + "]")).val("");
        var hm_count = $("#hm_count").val();
	var formobj = $("<form></form>");

	formobj.append('<input name="hmdelete[submitted][id]" type="hidden" value="'+id+'">');
	$.post('<?php echo $this->Session->webroot; ?>preferences/user_options/', formobj.serialize(), 
	function(data){ }
	);

}

</script>
	<?php
		$hm_count = isset($hmtest)?count($hmtest):1;
		$record_count=15;
	?>	
           <input type="hidden" name="hm_count" id="hm_count" value="<?php echo $hm_count ?>"/>
           <?php
            for ($i = 1; $i <= $record_count; ++$i)
            {
                $hmtest[$i]= (!empty($hmtest[$i]))?$hmtest[$i]:"";
                $hmtype[$i]= (!empty($hmtype[$i]))?$hmtype[$i]:"";
  		$hm_id[$i]=(!empty($hm_id[$i]))?$hm_id[$i]:"";  
                echo "
                <table border=0 id=\"temp_table$i\" style=\"display:".(($i > 1 and $hm_count < $i)?"none":"table")."; margin-bottom:0px\" class=\"form\">
                <tr>   <td style='padding-right:5px'>Test Type: #".$i." </td>
				  <td><select id='hmtype[$i]' style='width: 130px;' name='hmtype[$i]' >
					<option value='' selected='selected'></option>
				";
					foreach($hmTestTypes as $hmTestType) {
					  if ( $hmtype[$i] === $hmTestType) {$sel='selected';}else{$sel="";}
					  echo "<option value='".$hmTestType."' ".$sel.">".$hmTestType."</option> \n";
					}
				  echo "</select>&nbsp;&nbsp;</td>
				<td  width='115'>Test Name: #".$i."</td>
				<td><input type='text' size='25' name='hmtest[$i]' id='hmtest[$i]' value=\"".$hmtest[$i]."\">
					<input type='hidden' size='25' name='hm_id[$i]' id='hm_id[$i]' value=\"".$hm_id[$i]."\">

						</td>
				<td valign=middle>";
                if ($i > 0 and $i < $record_count)
                {
                    if($hm_count > $i)
				    {
				       $display = 'display: none;';
				    }
				    else
				    {
				       $display = '';
				    }
				    echo "<a id='hmadd_$i' removeonread='true' class=btn style='float:none;".$display."' onclick=\"document.getElementById('temp_table".($i + 1)."').style.display='block';jQuery('#hm_count').val('".($i + 1)."');this.style.display='none'; increaseHmCount(); document.getElementById('hmdelete_".($i+1)."').style.display='';".($i>1?"document.getElementById('hmdelete_".$i."').style.display='none';":"")."\" ".($hm_count <= $i?"":"style=\"display:none\"").">Add</a>";
                }
                if ($i > 1 and $i <= $record_count)
                {
                    if($hm_count > $i)
				    {
				       $display = 'display: none;';
				    }
				    else
				    {
				       $display = '';
				    }
					echo "<a  id=\"hmdelete_$i\" removeonread='true' class=btn 
style='float:none;".$display."'  
onclick=\"document.getElementById('temp_table".$i."').style.display='none';
jQuery('#hm_count').val('".($i - 1)."');
this.style.display='none'; 
decreaseHmCount('".$hm_id[$i]."','$i'); 
document.getElementById('hmadd_".($i-1)."').style.display='';
jQuery('#hmdelete_".($i-1)."').css('display', '');\" 
".($hm_count <= $i?"":"style=\"display:none\"").">Delete</a>";
                }
                echo "</td></tr></table>";
            }
            
            ?>

