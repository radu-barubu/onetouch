<table width="100%" class='small_table' cellpadding="0" cellspacing="0">
<thead>
<tr style="text-align: left;" class="striped">
<?php foreach($options as $v): ?>
	<th><?php echo $v; ?></th>
<?php endforeach; ?>
</tr>
</thead>
<?php
if($rows) { 
for ($i = $start - 1; $i < $end; ++$i)
{
	echo $rows[$i]."\n";
}
	//echo implode("\n",$rows); 
} else {
	"<tr><td>No Found</td></tr>";
}
?>
</table>
