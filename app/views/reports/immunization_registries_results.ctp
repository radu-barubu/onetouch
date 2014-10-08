<div style="overflow: hidden;">

<?php

$page = (isset($this->data['page'])) ? $this->data['page'] : "1";
$rows = array();
$data = array();

foreach( $patient_list as $k => $v ) {

	if (@count($v['PatientProblemList']) == 0 and @count($v['PatientMedicationList']) == 0 and @count($v['EncounterMaster']) == 0 and @count($v['PatientLabResult']) == 0)
	{
		continue;
	}

	$patient = $v['PatientDemographic'];

	if (@count($v['PatientProblemList']) > 0)
	{
		for ($i = 0; $i < count($v['PatientProblemList']); ++$i)
		{
			$row = array();

			$row[] = $patient['first_name']. ' ' .$patient['last_name'];
		
			$row[] = floor((time() - strtotime($patient['dob'])) / 31556926);
			
			$row[] = $patient['gender'] =='M'? 'Male':'Female';
			
			$row[] = $v['PatientProblemList'][$i]['diagnosis'];
			
			$row[] = '';

			$row[] = '';

			$row[] = '';

			$rows[] = $this->element('row', array('options' => $row ));
			$data[] = implode("|", $row);
		}
	}

	if (@count($v['PatientMedicationList']) > 0)
	{
		for ($i = 0; $i < count($v['PatientMedicationList']); ++$i)
		{
			$row = array();

			$row[] = $patient['first_name']. ' ' .$patient['last_name'];
		
			$row[] = floor((time() - strtotime($patient['dob'])) / 31556926);
			
			$row[] = $patient['gender'] =='M'? 'Male':'Female';
			
			//$row[] = $v['PatientMedicationList'][$i]['diagnosis'];
			$row[] = '';
			
			$row[] = $v['PatientMedicationList'][$i]['medication'];
			
			$row[] = '';
			
			$row[] = '';
			
			$rows[] = $this->element('row', array('options' => $row ));
			$data[] = implode("|", $row);
		}
	}

	if (@count($v['EncounterMaster']) > 0)
	{
		for ($i = 0; $i < count($v['EncounterMaster']); ++$i)
		{
			if (@count($v['EncounterMaster'][$i]['EncounterImmunization']) > 0)
			{
				for ($j = 0; $j < count($v['EncounterMaster'][$i]['EncounterImmunization']); ++$j)
				{
					if ($this->data['search_method'] == "Condition" and $this->data['conditions_1'] == "Immunization" and $this->data['condition_present_1'] == "Exclude")
					{
						continue;
					}

					$row = array();
		
					$row[] = $patient['first_name']. ' ' .$patient['last_name'];
				
					$row[] = floor((time() - strtotime($patient['dob'])) / 31556926);
					
					$row[] = $patient['gender'] =='M'? 'Male':'Female';
					
					$row[] = '';
					
					$row[] = '';
		
					$row[] = $v['EncounterMaster'][$i]['EncounterImmunization'][$j]['vaccine_name'];
		
					$row[] = '';
					
					$rows[] = $this->element('row', array('options' => $row ));
					$data[] = implode("|", $row);
				}
			}

		}
	}

	if ($this->data['search_method'] == "Condition" and $this->data['conditions_1'] == "Immunization" and $this->data['condition_present_1'] == "Exclude")
	{
		$row = array();

		$row[] = $patient['first_name']. ' ' .$patient['last_name'];
	
		$row[] = floor((time() - strtotime($patient['dob'])) / 31556926);
		
		$row[] = $patient['gender'] =='M'? 'Male':'Female';
		
		$row[] = '';
		
		$row[] = '';

		$row[] = '';
		
		$row[] = '';
		
		$rows[] = $this->element('row', array('options' => $row ));
		$data[] = implode("|", $row);
	}

	if (@count($v['PatientLabResult']) > 0)
	{
		for ($i = 0; $i < count($v['PatientLabResult']); ++$i)
		{
			$row = array();

			$row[] = $patient['first_name']. ' ' .$patient['last_name'];
		
			$row[] = floor((time() - strtotime($patient['dob'])) / 31556926);
			
			$row[] = $patient['gender'] =='M'? 'Male':'Female';
			
			//$row[] = $v['PatientLabResult'][$i]['diagnosis'];
			$row[] = '';

			$row[] = '';
			
			$row[] = '';

			$lab_result_1 = ($v['PatientLabResult'][$i]['test_name1']?$v['PatientLabResult'][$i]['test_name1'].": ":"").($v['PatientLabResult'][$i]['result_value1']?$v['PatientLabResult'][$i]['result_value1']:"").($v['PatientLabResult'][$i]['unit1']?$v['PatientLabResult'][$i]['unit1']:"");
			$lab_result_2 = ($v['PatientLabResult'][$i]['test_name2']?$v['PatientLabResult'][$i]['test_name2'].": ":"").($v['PatientLabResult'][$i]['result_value2']?$v['PatientLabResult'][$i]['result_value2']:"").($v['PatientLabResult'][$i]['unit2']?$v['PatientLabResult'][$i]['unit2']:"");
			$lab_result_3 = ($v['PatientLabResult'][$i]['test_name3']?$v['PatientLabResult'][$i]['test_name3'].": ":"").($v['PatientLabResult'][$i]['result_value3']?$v['PatientLabResult'][$i]['result_value3']:"").($v['PatientLabResult'][$i]['unit3']?$v['PatientLabResult'][$i]['unit3']:"");
			$lab_result_4 = ($v['PatientLabResult'][$i]['test_name4']?$v['PatientLabResult'][$i]['test_name4'].": ":"").($v['PatientLabResult'][$i]['result_value4']?$v['PatientLabResult'][$i]['result_value4']:"").($v['PatientLabResult'][$i]['unit4']?$v['PatientLabResult'][$i]['unit4']:"");
			$lab_result_5 = ($v['PatientLabResult'][$i]['test_name5']?$v['PatientLabResult'][$i]['test_name5'].": ":"").($v['PatientLabResult'][$i]['result_value5']?$v['PatientLabResult'][$i]['result_value5']:"").($v['PatientLabResult'][$i]['unit5']?$v['PatientLabResult'][$i]['unit5']:"");

			$row[] = substr(($lab_result_1?$lab_result_1."<br>":"").($lab_result_2?$lab_result_2."<br>":"").($lab_result_3?$lab_result_3."<br>":"").($lab_result_4?$lab_result_4."<br>":"").($lab_result_5?$lab_result_5."<br>":""), 0, -4);

			$rows[] = $this->element('row', array('options' => $row ));
			$data[] = implode("|", $row);
		}
	}
}

$limit = 20;
$start = (($page - 1) * $limit) + 1;
$end = $page * $limit;
if ($end > count($data))
{
	$end = count($data);
}
$total = ceil(count($data) / $limit);
$modulus = 5;
$first = 2;
$last = 2;

$columns = array('<a href="javascript: void(0);" onclick="sortBy(\'patient_name\')">Name</a>','<a href="javascript: void(0);" onclick="sortBy(\'age\')">Age</a>','<a href="javascript: void(0);" onclick="sortBy(\'gender\')">Gender</a>','<a href="javascript: void(0);" onclick="sortBy(\'diagnosis\')">Diagnosis</a>','<a href="javascript: void(0);" onclick="sortBy(\'drug\')">Medication</a>','<a href="javascript: void(0);" onclick="sortBy(\'vaccine_name\')">Vaccine Name</a>','<a href="javascript: void(0);" onclick="sortBy(\'lab_results\')">Lab Test Results</a>');

echo $this->element( 'table', array( 'options' => $columns, 'rows' => $rows, 'start' => $start, 'end' => $end) );

?>

<form id="frm" method="post" action="<?php echo $this->Session->webroot.'reports/immunization_registries/task:export_imm'; ?>" accept-charset="utf-8" enctype="multipart/form-data" target="_blank">
<?php
for ($i = 0; $i < count($data); ++$i)
{
	echo '<input type="hidden" name="data[data]['.$i.']" id="data'.$i.'" value="'.$data[$i].'" />';
}
?>
</form>

<div style="width: 40%; float: left;">
	<div class="actions">
		<ul>
			<li><a href="javascript: void(0);" onclick="$('#frm').submit()">Download</a></li>
		</ul>
	</div>
</div>
<div style="width: 60%; float: right; margin-top: 15px;">
	<div class="paging">
	<?php
	echo "Display $start-$end of ".count($data);
	if (count($data) > $limit)
	{
		echo '&nbsp;&nbsp;&mdash;&nbsp;&nbsp;';
		if ($page != 1)
		{
			echo "<a href='javascript: void(0);' onclick='goTo(\"".($page - 1)."\")'><< Previous</a>&nbsp;&nbsp;";
		}
		for ($i = 1; $i <= $total; ++$i)
		{
			if ($page < ($modulus + 1))
			{
				if ($i <= ($modulus + 1) or $i > ($total - $last))
				{
					if ($page == $i)
					{
						echo $i;
					}
					else
					{
						echo "<a href='javascript: void(0);' onclick='goTo(\"$i\")'>$i</a>";
					}
					if ($total > ($modulus + $first) and $i == ($modulus + 1) and $total != ($modulus + $first + 1))
					{
						echo "...";
					}
					else if ($i != $total)
					{
						echo ",&nbsp;&nbsp;";
					}
				}
			}
			else
			{
				if ($i <= $first or $i > ($total - $last) or ($i >= ($page - ceil($modulus / 2)) and $i <= $page) or ($i >= $page and $i <= ($page + floor($modulus / 2))) or ($page > ($total - $last) and $i >= ($total - $modulus)))
				{
					if ($page == $i)
					{
						echo $i;
					}
					else
					{
						echo "<a href='javascript: void(0);' onclick='goTo(\"$i\")'>$i</a>";
					}
					if ($total > ($modulus + $first) and $total != ($modulus + $first + 1) and ($i == $first and (($i + 1) != ($page - ceil($modulus / 2)))) or ($i < ($total - $last) and $i == ($page + floor($modulus / 2))))
					{
						echo "...";
					}
					else if ($i != $total)
					{
						echo ",&nbsp;&nbsp;";
					}
				}
			}
		}
		if ($page < $total)
		{
			echo "&nbsp;&nbsp;<a href='javascript: void(0);' onclick='goTo(\"".($page + 1)."\")'>Next >></a>";
		}
	}
	?>
	</div>
</div>
</div>
