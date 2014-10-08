<?php

class elements {
		
	function addselectBox($array = array(),$selected=null,$id=null,$extra=null)
	{
		if($extra) {
			$extra = " $extra";
		}
		if(!$extra) {
			$extra = " style='padding-right: 1px;'";
		} else {
			$extra = " $extra";
		}
		if($id) {
			$id =  " name='$id' id='$id'";
		}
		$html = "\n<select  class='select_input' style='width: 214px;' {$id}$extra>\n";
		if(!empty($array)  && is_array($array))  {
			foreach($array as $k =>$v) {
				if(is_array($v) &&  !empty($v)) {
					if(!empty($v)) {
					
						$options = array();
	
						foreach($v as $k2 => $v2) {
							$select = (((($selected && $selected!==null) || (is_numeric($k) && $selected==0)) && $selected==$k2)? " selected":"");
							$options[] = " <option value='$k2'$select>".$v2."&nbsp;</option>";
						}
	
						if(is_numeric($k)) {
							$label = "";
						} else {
							$label = " label='&nbsp;$k&nbsp;'";
						}
	
						$html .= "<optgroup$label>\n";
						$html .= implode("\n\t",$options);
						$html .= "\n\t</optgroup>\n";
						continue;
					}
					else {
						unset($array[$k]);
						continue;
					}
				}
				$select = null;
				if($selected && $selected==$k && !is_numeric($k)) {
					$select = " selected";
				} elseif ((is_numeric($k) && $selected==0) && $selected==$k){
					//for now
					$select = " selected";
				} elseif($selected && $selected==$k) {
					$select = " selected";
				}
				if($select) {
					$v = "* $v";
				}
				$html .="\t<option value=\"$k\"$select>$v&nbsp;\n";
			}
		}
		$html .= "</select>\n";
		return $html;
	}
}