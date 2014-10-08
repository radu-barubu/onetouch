<?php

class Charts 
{
    public static function getAgeInMonth($birthdate)
    {
        if(strlen($birthdate) == 0)
        {
            return 1;
        }
        
        $age_in_months = (date_diff(date_create($birthdate), date_create('now'))->y * 12) + date_diff(date_create($birthdate), date_create('now'))->m + 1;
        return $age_in_months;
    }
    
    public static function getGrowthChartDisplayLayout(&$controller, $age_in_months, $gender)
    {
        $weight = (isset($controller->params['named']['weight'])) ? $controller->params['named']['weight'] : "";
        
        $controller->set('age', isset($age_in_months) ? floatval($age_in_months) : '');
        
        if ($weight && $age_in_months <= 24 && $gender == "M")
        {
            $controller->set('weight', floatval($weight));
            $controller->layout = "boys_weight_for_age_0_2_years";
        }
        if ($weight && $age_in_months <= 24 && $gender == "F")
        {
            $controller->set('weight', floatval($weight));
            $controller->layout = "girls_weight_for_age_0_2_years";
        }
        if ($weight && $age_in_months <= 240 && $age_in_months > 24 && $gender == "M")
        {
            $controller->set('weight', floatval($weight));
            $controller->layout = "boys_weight_for_age_2_20_years";
        }
        if ($weight && $age_in_months <= 240 && $age_in_months > 24 && $gender == "F")
        {
            $controller->set('weight', floatval($weight));
            $controller->layout = "girls_weight_for_age_2_20_years";
        }
        
        $height = (isset($controller->params['named']['height'])) ? $controller->params['named']['height'] : "";
        
        if ($height && $age_in_months <= 24 && $gender == "M")
        {
            $controller->set('length', floatval($height));
            $controller->layout = "boys_length_for_age_0_2_years";
        }
        
        if ($height && $age_in_months <= 24 && $gender == "F")
        {
            $controller->set('length', floatval($height));
            $controller->layout = "girls_length_for_age_0_2_years";
        }
        
        if ($height && $age_in_months <= 240 && $age_in_months > 24 && $gender == "M")
        {
            $controller->set('length', floatval($height));
            $controller->layout = "boys_length_for_age_2_20_years";
        }
        
        if ($height && $age_in_months <= 240 && $age_in_months > 24 && $gender == "F")
        {
            $controller->set('length', floatval($height));
            $controller->layout = "girls_length_for_age_2_20_years";
        }
        
        if ($weight && $height && $age_in_months <= 24 && $gender == "M")
        {
            $controller->set('weight', floatval($weight));
            $controller->set('length', floatval($height));
            $controller->layout = "boys_weight_for_length_0_2_years";
        }
        
        if ($weight && $height && $age_in_months <= 24 && $gender == "F")
        {
            $controller->set('weight', floatval($weight));
            $controller->set('length', floatval($height));
            $controller->layout = "girls_weight_for_length_0_2_years";
        }
        
        if ($weight && $height && $age_in_months <= 240 && $age_in_months > 24 && $gender == "M")
        {
            $controller->set('weight', floatval($weight));
            $controller->set('length', floatval($height));
            $controller->layout = "boys_weight_for_length_2_20_years";
        }
        
        if ($weight && $height && $age_in_months <= 240 && $age_in_months > 24 && $gender == "F")
        {
            $controller->set('weight', floatval($weight));
            $controller->set('length', floatval($height));
            $controller->layout = "girls_weight_for_length_2_20_years";
        }
        
        $headcircumference = (isset($controller->params['named']['headcircumference'])) ? $controller->params['named']['headcircumference'] : "";
        if ($headcircumference && $age_in_months <= 24 && $gender == "M")
        {
            $controller->set('headcircumference', floatval($headcircumference));
            $controller->layout = "boys_headcircumference_for_age_0_2_years";
        }
        
        if ($headcircumference && $age_in_months <= 24 && $gender == "F")
        {
            $controller->set('headcircumference', floatval($headcircumference));
            $controller->layout = "girls_headcircumference_for_age_0_2_years";
        }
        
        $bmi = (isset($controller->params['named']['bmi'])) ? $controller->params['named']['bmi'] : "";
        if ($bmi && $age_in_months <= 240 && $gender == "M")
        {
            $controller->set('bmi', floatval($bmi));
            $controller->layout = "boys_bmi_for_age_2_20_years";
        }
        
        if ($bmi && $age_in_months <= 240 && $gender == "F")
        {
            $controller->set('bmi', floatval($bmi));
            $controller->layout = "girls_bmi_for_age_2_20_years";
        }
    }
    
    public static function getLineChart(&$controller, $encounter_id)
    {
        $controller->layout = "line_chart";
        $cname = (isset($controller->params['named']['name'])) ? $controller->params['named']['name'] : "";
        $demographic_items = $controller->EncounterMaster->find('all', array('conditions' => array('EncounterMaster.encounter_id' => $encounter_id)));
        $gender = $demographic_items[0]['PatientDemographic']['gender'];
        $controller->set('gender', $gender);
        
        //$vital_items = $controller->EncounterVital->find("all", array('limit' => 10, 'order' => array('EncounterVital.vital_id DESC'), 'conditions' => array('AND' => array('EncounterMaster.patient_id' => $demographic_items[0]['PatientDemographic']['patient_id'], 'DATEDIFF(NOW(),EncounterVital.modified_timestamp) < ' => "180"))));
        //$vital_items = array_reverse($vital_items);
        
        $vital_items = $controller->EncounterMaster->getPreviousVitalsFull($encounter_id, "DESC", 10);
        $vital_items = array_reverse($vital_items);
        
        $data_points = "";
        $x_axis = "";
        $title = "";
        $y_label="";
        $data_points1 = "";
        $data_points2 = "";
		$practicesetting = $controller->PracticeSetting->getSettings();
        
        for ($i = 0; $i < count($vital_items); $i++)
        {
            if ($cname == "blood_pressure1")
            {
                
                $vital_items[$i]['EncounterVital'][0][$cname] = explode("/", $vital_items[$i]['EncounterVital'][0][$cname]);
                $y_label = "Blood Pressure";
                $title = "Blood Pressure ";
            }
            elseif ($cname == "pulse1")
            {
                
                $vital_items[$i]['EncounterVital'][0][$cname] = str_replace("bpm", "", $vital_items[$i]['EncounterVital'][0][$cname]);
                $y_label = "Pulse (bpm)";
                $title = "Pulse ";
            }
            elseif ($cname == "respiratory")
            {
                
                $vital_items[$i]['EncounterVital'][0][$cname] = str_replace("RR", "", $vital_items[$i]['EncounterVital'][0][$cname]);
                $y_label = "Respiratory (RR)";
                $title = "Respiratory ";
            }
            elseif ($cname == "temperature1")
            {
				if($practicesetting->scale=="English")
                {
	                $y_label = "Temperature (F)";
                }
				else
                {
	                $y_label = "Temperature (C)";
                }
                
                $vital_items[$i]['EncounterVital'][0][$cname] = str_replace("&deg;F", "", $vital_items[$i]['EncounterVital'][0][$cname]);
                
                $title = "Temperature ";
            }
            elseif ($cname == "spo2")
            {
                
                $vital_items[$i]['EncounterVital'][0][$cname] = str_replace("%", "", $vital_items[$i]['EncounterVital'][0][$cname]);
                $y_label = "SpO2 (%)";
                $title = "SpO2 ";
            }
            elseif ($cname == "english_height")
            {
                $vital_items[$i]['EncounterVital'][0][$cname] = str_replace("'", ".", $vital_items[$i]['EncounterVital'][0][$cname]);
                $inches = explode(".", $vital_items[$i]['EncounterVital'][0][$cname]);
                
                $tmp = $inches[0] * 12;
                
                if (isset($inches[1])) {
                    $tmp += $inches[1];
                }
                $vital_items[$i]['EncounterVital'][0][$cname] = $tmp;
                
                $controller->set('feet_inches', true);
                $y_label = "Height (ft)";
                $title = "Height ";
            }
			elseif ($cname == "metric_height")
            {
                $vital_items[$i]['EncounterVital'][0][$cname] = ($vital_items[$i]['EncounterVital'][0][$cname]);
                $vital_items[$i]['EncounterVital'][0][$cname] = $vital_items[$i]['EncounterVital'][0][$cname];
                $y_label = "Height (cm)";
                $title = "Height ";
            }            
            elseif ($cname == "english_weight")
            {
	            $y_label = "Weight (lb)";
                $title = "Weight ";
            }
			elseif ($cname == "metric_weight")
            {
	            $y_label = "Weight (kg)";
                $title = "Weight ";
            }
            elseif ($cname == "bmi")
            {
                $y_label = "BMI";
                $title = "BMI";
            }
            elseif ($cname == "last_menstrual_start")
            {
                $datetime1 = strtotime($vital_items[$i]['EncounterVital'][0]['last_menstrual_start']);
                $datetime2 = strtotime($vital_items[$i]['EncounterVital'][0]['last_menstrual_end']);
                $interval = (abs($datetime2 - $datetime1) / 60 / 60 / 24);
                $vital_items[$i]['EncounterVital'][0][$cname] = $interval;
                $y_label = "Number of Days";
                $title = "Last Menstrual";
            }
            else
            {
            	if($practicesetting->scale=="English")
	                $y_label = "$cname (in)";
				else
					$y_label = "$cname (cm)";
                $title = ucwords(str_replace("_", " ", $cname));
            }
        
            if (trim($y_label) == trim("Blood Pressure"))
            {
                if ($i == 0)
                {
                    $data_points1 .= "" . (trim($vital_items[$i]['EncounterVital'][0][$cname][0]) == "" ? "0" : $vital_items[$i]['EncounterVital'][0][$cname][0]) . "";
                    $data_points2 .= "" . (!isset($vital_items[$i]['EncounterVital'][0][$cname][1]) || trim($vital_items[$i]['EncounterVital'][0][$cname][1]) == "" ? "0" : $vital_items[$i]['EncounterVital'][0][$cname][1]) . "";
                    $x_axis .= "'" . __date("n/j", strtotime($vital_items[$i]['EncounterMaster']['encounter_date'])) . "'";
                }
                else
                {
        
                    $pt1 = (isset($vital_items[$i]['EncounterVital'][0][$cname][0])) ? trim($vital_items[$i]['EncounterVital'][0][$cname][0]) : '';
                    $pt2 = (isset($vital_items[$i]['EncounterVital'][0][$cname][1])) ? trim($vital_items[$i]['EncounterVital'][0][$cname][1]) : '';
                    
                    $data_points1 .= "," . ($pt1 == "" ? "0" : $pt1) . "";
                    
                    $data_points2 .= "," . ($pt2 == "" ? "0" : $pt2) . "";
                    $x_axis .= ",'" . __date("n/j", strtotime($vital_items[$i]['EncounterMaster']['encounter_date'])) . "'";
                }
            }
            else
            {
        
                if ($i == 0)
                {
                    $data_points .= "" . floatval(trim($vital_items[$i]['EncounterVital'][0][$cname]) == "" ? "0" : $vital_items[$i]['EncounterVital'][0][$cname]) . "";
                    $x_axis .= "'" . __date("n/j", strtotime($vital_items[$i]['EncounterMaster']['encounter_date'])) . "'";
                }
                else
                {
            
                    $data_points .= "," . floatval(trim($vital_items[$i]['EncounterVital'][0][$cname]) == "" ? "0" : $vital_items[$i]['EncounterVital'][0][$cname]) . "";
                    $x_axis .= ",'" . __date("n/j", strtotime($vital_items[$i]['EncounterMaster']['encounter_date'])) . "'";
                }
            }
        }
        
        if ($y_label == "Blood Pressure")
        {
            $controller->set('data_points', "{data:[$data_points1],showInLegend: false},{data:[$data_points2],showInLegend: false}");
        }
        else
        {
        
            $controller->set('data_points', "{data:[$data_points],showInLegend: false}");
        }
        
        $controller->set('x_axis', $x_axis);
        $controller->set('title', $title);
        $controller->set('x_label', "Visit Date");
        $controller->set('y_label', (str_replace("_", " ", $y_label)));
        $birthdate = $demographic_items[0]['PatientDemographic']['dob'];
        
        $age_in_months = self::getAgeInMonth($birthdate);
        
        $controller->set('age', isset($age_in_months) ? $age_in_months : '');
    }
    
    public static function getGrowthPoints(&$controller, $encounter_id)
    {
        $controller->layout = "blank";
        $name = (isset($controller->params['named']['name'])) ? $controller->params['named']['name'] : "";
        $demographic_items = $controller->EncounterMaster->find('all', array('conditions' => array('EncounterMaster.encounter_id' => $encounter_id)));
        $gender = $demographic_items[0]['PatientDemographic']['gender'];
        $birthdate = $demographic_items[0]['PatientDemographic']['dob'];
        $practicesetting = $controller->PracticeSetting->getSettings();
        $age_in_months = self::getAgeInMonth($birthdate);
        
        $controller->set('age', isset($age_in_months) ? $age_in_months : 0);
        
        if ($name == "length_age")
        {
            if ($gender == "M" && $age_in_months <= 24)
            {
                $controller->loadModel("BoysLengthForAge_0_2Years");
                $chart_points = $controller->BoysLengthForAge_0_2Years->find('all');
				$chart_points = array_reverse($chart_points);
                $points = "";
                
                for ($i = 0; $i < count($chart_points); $i++)
                {
                    foreach ($chart_points[$i]['BoysLengthForAge_0_2Years'] as $key => $value)
                    {
                        if ($key == "series_name"){
                            $points .= "\n" . $value;
						}
                        else{
							if($practicesetting->scale=="English"){
	                            $points .= "," . (floatval($value)*(0.393));
							}
							else{
								$points .= "," . $value;
							}
						}
                    }
                }
            }
            elseif ($gender == "F" && $age_in_months <= 24)
            {
                $controller->loadModel("GirlsLengthForAge_0_2Years");
                $chart_points = $controller->GirlsLengthForAge_0_2Years->find('all');
				$chart_points = array_reverse($chart_points);
                $points = "";
                
                for ($i = 0; $i < count($chart_points); $i++)
                {
                    foreach ($chart_points[$i]['GirlsLengthForAge_0_2Years'] as $key => $value)
                    {
                        if ($key == "series_name"){
                            $points .= "\n" . $value;
						}
                        else{
							if($practicesetting->scale=="English"){
	                            $points .= "," . (floatval($value)*(0.393));
							}
							else{
								$points .= "," . $value;
							}
						}
                    }
                }
            }
            elseif ($gender == "M" && $age_in_months <= 240 && $age_in_months > 24)
            {
                $controller->loadModel("BoysLengthForAge_2_20Years");
                $chart_points = $controller->BoysLengthForAge_2_20Years->find('all');
				$chart_points = array_reverse($chart_points);
                $points = "";
                
                for ($i = 0; $i < count($chart_points); $i++)
                {
                    foreach ($chart_points[$i]['BoysLengthForAge_2_20Years'] as $key => $value)
                    {
                        if ($key == "series_name"){
                            $points .= "\n" . $value;
						}
                        else{
							if($practicesetting->scale=="English"){
	                            $points .= "," . (floatval($value)*(0.393));
							}
							else{
								$points .= "," . $value;
							}
						}
                    }
                }
            }
            elseif ($gender == "F" && $age_in_months <= 240 && $age_in_months > 24)
            {
                $controller->loadModel("GirlsLengthForAge_2_20Years");
                $chart_points = $controller->GirlsLengthForAge_2_20Years->find('all');
				$chart_points = array_reverse($chart_points);
                $points = "";
                
                for ($i = 0; $i < count($chart_points); $i++)
                {
                    foreach ($chart_points[$i]['GirlsLengthForAge_2_20Years'] as $key => $value)
                    {
                        if ($key == "series_name"){
                            $points .= "\n" . $value;
						}
                        else{
							if($practicesetting->scale=="English"){
	                            $points .= "," . (floatval($value)*(0.393));
							}
							else{
								$points .= "," . $value;
							}
						}
                    }
                }
            }
        
            echo $points;
            exit();
        }
        elseif ($name == "weight_age")
        {
            if ($gender == "M" && $age_in_months <= 24)
            {
                $controller->loadModel("BoysWeightForAge_0_2Years");
                $chart_points = $controller->BoysWeightForAge_0_2Years->find('all');
				$chart_points = array_reverse($chart_points);
                $points = "";
                
                for ($i = 0; $i < count($chart_points); $i++)
                {
                    foreach ($chart_points[$i]['BoysWeightForAge_0_2Years'] as $key => $value)
                    {
                        if ($key == "series_name"){
                            $points .= "\n" . $value;
						}
                        else{
							if($practicesetting->scale=="English"){
	                            $points .= "," . (floatval($value)*(2.20));
							}
							else{
								$points .= "," . $value;
							}
						}
                    }
                }
            }
            elseif ($gender == "F" && $age_in_months <= 24)
            {
                $controller->loadModel("GirlsWeightForAge_0_2Years");
                $chart_points = $controller->GirlsWeightForAge_0_2Years->find('all');
				$chart_points = array_reverse($chart_points);
                $points = "";
                
                for ($i = 0; $i < count($chart_points); $i++)
                {
                    foreach ($chart_points[$i]['GirlsWeightForAge_0_2Years'] as $key => $value)
                    {
                        if ($key == "series_name"){
                            $points .= "\n" . $value;
						}
                        else{
							if($practicesetting->scale=="English"){
	                            $points .= "," . (floatval($value)*(2.20));
							}
							else{
								$points .= "," . $value;
							}
						}
                    }
                }
            }
            elseif ($gender == "M" && $age_in_months <= 240 && $age_in_months > 24)
            {
                $controller->loadModel("BoysWeightForAge_2_20Years");
                $chart_points = $controller->BoysWeightForAge_2_20Years->find('all');
				$chart_points = array_reverse($chart_points);
				
                $points = "";
                
                for ($i = 0; $i < count($chart_points); $i++)
                {
                    foreach ($chart_points[$i]['BoysWeightForAge_2_20Years'] as $key => $value)
                    {
                        if ($key == "series_name"){
                            $points .= "\n" . $value;
						}
                        else{
							if($practicesetting->scale=="English"){
	                            $points .= "," . (floatval($value)*(2.20));
							}
							else{
								$points .= "," . $value;
							}
						}
                    }
                }
            }
            elseif ($gender == "F" && $age_in_months <= 240 && $age_in_months > 24)
            {
                $controller->loadModel("GirlsWeightForAge_2_20Years");
                $chart_points = $controller->GirlsWeightForAge_2_20Years->find('all');
				$chart_points = array_reverse($chart_points);
                $points = "";
                
                for ($i = 0; $i < count($chart_points); $i++)
                {
                    foreach ($chart_points[$i]['GirlsWeightForAge_2_20Years'] as $key => $value)
                    {
                        if ($key == "series_name"){
                            $points .= "\n" . $value;
						}
                        else{
							if($practicesetting->scale=="English"){
	                            $points .= "," . (floatval($value)*(2.20));
							}
							else{
								$points .= "," . $value;
							}
						}
                    }
                }
            }
            
            echo $points;
            exit();
        }
        elseif ($name == "weight_length")
        {
            if ($gender == "M" && $age_in_months <= 24)
            {
                $controller->loadModel("BoysWeightForLength_0_2Years");
                $chart_points = $controller->BoysWeightForLength_0_2Years->find('all');
				$chart_points = array_reverse($chart_points);
                $points = "";
                
                for ($i = 0; $i < count($chart_points); $i++)
                {
                    foreach ($chart_points[$i]['BoysWeightForLength_0_2Years'] as $key => $value)
                    {
                        if ($key == "series_name"){
							{
								$points .= "\n" . $value;
							}                            
						}
                        else{
							if($practicesetting->scale=="English"){
	                            $points .= "," . (floatval($value)*(2.20));
							}
							else{
								$points .= "," . $value;
							}
						}
                    }
                }
            }
            elseif ($gender == "F" && $age_in_months <= 24)
            {
                $controller->loadModel("GirlsWeightForLength_0_2Years");
                $chart_points = $controller->GirlsWeightForLength_0_2Years->find('all');
				$chart_points = array_reverse($chart_points);
                $points = "";
                for ($i = 0; $i < count($chart_points); $i++)
                {
                    foreach ($chart_points[$i]['GirlsWeightForLength_0_2Years'] as $key => $value)
                    {
                        if ($key == "series_name")
						{
							{
								$points .= "\n" . $value;
							}                            
						}
                        else{
							if($practicesetting->scale=="English")
							{
	                            $points .= "," . (floatval($value)*(2.20));
							}
							else
							{
								$points .= "," . $value;
							}
						}
                	}
				}
            }
            elseif ($gender == "M" && $age_in_months <= 240 && $age_in_months > 24)
            {
                $controller->loadModel("BoysWeightForLength_2_20Years");
                $chart_points = $controller->BoysWeightForLength_2_20Years->find('all');
				$chart_points = array_reverse($chart_points);
                $points = "";
                
                for ($i = 0; $i < count($chart_points); $i++)
                {
                    foreach ($chart_points[$i]['BoysWeightForLength_2_20Years'] as $key => $value)
                    {
                        if ($key == "series_name"){
							{
								$points .= "\n" . $value;
							}                            
						}
                        else{
							if($practicesetting->scale=="English"){
	                            $points .= "," . (floatval($value)*(2.20));
							}
							else{
								$points .= "," . $value;
							}
						}
                    }
                }
            }
            elseif ($gender == "F" && $age_in_months <= 240 && $age_in_months > 24)
            {
                $controller->loadModel("GirlsWeightForLength_2_20Years");
                $chart_points = $controller->GirlsWeightForLength_2_20Years->find('all');
				$chart_points = array_reverse($chart_points);
                $points = "";
                for ($i = 0; $i < count($chart_points); $i++)
                {
                    foreach ($chart_points[$i]['GirlsWeightForLength_2_20Years'] as $key => $value)
                    {
                        if ($key == "series_name"){
							{
								$points .= "\n" . $value;
							}                            
						}
                        else{
							if($practicesetting->scale=="English"){
	                            $points .= "," . (floatval($value)*(2.20));
							}
							else{
								$points .= "," . $value;
							}
						}
                    }
                }
            }
            
            echo $points;
            exit();
        }
        elseif ($name == "bmi_age")
        {
            if ($gender == "M" && $age_in_months <= 240 && $age_in_months > 24)
            {
                $controller->loadModel("BoysBmiForAge_2_20Years");
                $chart_points = $controller->BoysBmiForAge_2_20Years->find('all');
				$chart_points = array_reverse($chart_points);
                $points = "";
                for ($i = 0; $i < count($chart_points); $i++)
                {
                    foreach ($chart_points[$i]['BoysBmiForAge_2_20Years'] as $key => $value)
                    {
                        if ($key == "series_name")
                            $points .= "\n" . $value;
                        else
                            $points .= "," . $value;
                    }
                }
            }
            elseif ($gender == "F" && $age_in_months <= 240 && $age_in_months > 24)
            {
                $controller->loadModel("GirlsBmiForAge_2_20Years");
                $chart_points = $controller->GirlsBmiForAge_2_20Years->find('all');
				$chart_points = array_reverse($chart_points);
                $points = "";
                for ($i = 0; $i < count($chart_points); $i++)
                {
                    foreach ($chart_points[$i]['GirlsBmiForAge_2_20Years'] as $key => $value)
                    {
                        if ($key == "series_name")
                            $points .= "\n" . $value;
                        else
                            $points .= "," . $value;
                    }
                }
            }
            
            echo $points;
            exit();
        }
        elseif ($name == "headcircumference_age")
        {
            if ($gender == "M" && $age_in_months <= 24)
            {
                $controller->loadModel("BoysHeadcircumferenceForAge_0_2Years");
                $chart_points = $controller->BoysHeadcircumferenceForAge_0_2Years->find('all');
				$chart_points = array_reverse($chart_points);
                $points = "";
                for ($i = 0; $i < count($chart_points); $i++)
                {
                    foreach ($chart_points[$i]['BoysHeadcircumferenceForAge_0_2Years'] as $key => $value)
                    {
                        if ($key == "series_name")
                            $points .= "\n" . $value;
                        else{
                            if($practicesetting->scale=="English"){
	                            $points .= "," . (floatval($value)*(0.393));
							}
							else{
								$points .= "," . $value;
							}
						}
                    }
                }
            }
            elseif ($gender == "F" && $age_in_months <= 24)
            {
                $controller->loadModel("GirlsHeadcircumferenceForAge_0_2Years");
                $chart_points = $controller->GirlsHeadcircumferenceForAge_0_2Years->find('all');
				$chart_points = array_reverse($chart_points);
                $points = "";
                for ($i = 0; $i < count($chart_points); $i++)
                {
                    foreach ($chart_points[$i]['GirlsHeadcircumferenceForAge_0_2Years'] as $key => $value)
                    {
                        if ($key == "series_name")
                            $points .= "\n" . $value;
                        else{
                            if($practicesetting->scale=="English"){
	                            $points .= "," . (floatval($value)*(0.393));
							}
							else{
								$points .= "," . $value;
							}
						}
                    }
                }
            }
            echo $points;
            exit();
        }
    }
}


?>