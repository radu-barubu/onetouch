 <div style="overflow: hidden;">
  <h2>Help</h2>
    <div class="title_area">
		<div class="title_text">
		<?php echo $html->link('About', array('action' => 'about')); ?>
		<div class="title_item active">Changes Log</div>
		</div>
	</div>
<div>These are the changes for this <b><?php echo $version; ?></b> release. It includes fixes, and new features!

<p><pre>
<?php echo $contents; ?>
</pre>
</div>


	
</div>	
