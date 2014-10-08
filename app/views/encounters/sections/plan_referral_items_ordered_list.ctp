<?php
$page_access = $this->QuickAcl->getAccessType("encounters", "plan"); 

$del_icon_class = 'del_icon';
$del_icon_img = 'del.png';
if($page_access == 'R')
{
	$del_icon_class = '';
	$del_icon_img = 'del_disabled.png';
}

?>


<?php foreach($items as $k => $v) :?>

<tr deleteable="true" itemvalue="<?php echo $v['name'];?>" itemid="<?php echo $k;?>">
	<td width=15>
		<span class="<?php echo $del_icon_class; ?>" itemvalue="<?php echo $v['id'];?>"><?php echo $html->image($del_icon_img, array('alt' => '')); ?></span>
	</td>
	<td class="plan_sub_item" value="<?php echo $v['name'];?>">
		<?php echo  ($v['refer_type'] == 'referred_to') ? 'Referred to' : 'Referred by'; ?> <?php echo $v['name'];?>
	</td>
</tr>

<?php endforeach;?>