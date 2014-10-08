<?php 
$logo=$url_abs_paths['administration'].'/'.$details['profile']['PracticeProfile']['logo_image'];
 $clogo=($logo)?'<img src="'.Router::url("/", true).$logo.'">':'';
?>
<div style="overflow: hidden;">
    <div class="title_area">
		<div class="title_text">
		<div class="title_item active">Contact Us</div>
		</div>
	</div>
    <form id="frm"  accept-charset="utf-8" enctype="multipart/form-data">
	<?php echo $clogo;?>
    	<table cellpadding="0" cellspacing="10" class="form" width=100%>
<?php if ($details['profile']['PracticeProfile']['practice_name']):?>
        	<tr>
			<td><h2><?php echo $details['profile']['PracticeProfile']['practice_name'].'</h2>';
		echo (!empty($details['profile']['PracticeProfile']['type_of_practice']))? ''.$details['profile']['PracticeProfile']['type_of_practice']:'';
		echo (!empty($details['profile']['PracticeProfile']['description']))?  "<br> <em>".$details['profile']['PracticeProfile']['description'].'</em>':'';
		?>

			</td>
		</tr>
<?php endif; 

      if(sizeof($details['locations']) > 0) : 
?>
            <tr>
                <td>
                
               <?php 
		foreach($details['locations'] as $location):
               $loc = '';
               if(!empty($location['PracticeLocation']['location_name']))
               { 
               $loc .= '<h3>'.$location['PracticeLocation']['location_name'].'</h3>'; 
               }
               if(!empty($location['PracticeLocation']['address_line_1']))
               {
               $loc .= $location['PracticeLocation']['address_line_1'].' '; 
               }
               if(!empty($location['PracticeLocation']['address_line_2']))
               {
               $loc .= $location['PracticeLocation']['address_line_2'].' '; 
               }
               if(!empty($location['PracticeLocation']['city']))
               {
               $loc .= '<br>'.$location['PracticeLocation']['city'].', '; 
               }
               if(!empty($location['PracticeLocation']['state']))
               {
               $loc .= $location['PracticeLocation']['state'].', '; 
               }
               if(!empty($location['PracticeLocation']['zip']))
               {
               $loc .= $location['PracticeLocation']['zip'].' ';
               }
               if(!empty($location['PracticeLocation']['country']))
               {
               $loc .= '('.$location['PracticeLocation']['country'].')'; 
               }
               if(!empty($location['PracticeLocation']['phone']))
               {
               $loc .= '<br>Phone: '.$location['PracticeLocation']['phone'];
               }
               if(!empty($location['PracticeLocation']['fax']))
               {
               $loc .= '<br>Fax: '.$location['PracticeLocation']['fax'];
               }
               echo "$loc <br /><br />";
		endforeach;
                ?>
                </td>
            </tr>
<? endif; ?>
        </table>
    </form>
</div>
