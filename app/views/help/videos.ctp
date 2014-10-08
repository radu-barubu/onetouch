<?php
$view = (isset($this->params['named']['view'])) ? $this->params['named']['view'] : "";
?>
<div style="overflow: hidden;">
    <div class="title_area">
		<div class="title_text">
		<div class="title_item active">Training Videos</div>
		</div>
	</div>

<?php
if ($view)
{
?>
<div><a href="javascript:history.back();" class=btn>Back to Videos</a></div>
<div style="clear:both;height:30px"></div>
<div>
<iframe width="640" height="480" src="http://www.youtube.com/embed/<?php echo $view;?>" frameborder="0" allowfullscreen></iframe> 
</div>

<p><em>Tip: to increase quality, click on the Youtube Gear (setting) icon and change to 720</em>
<?php
} 
else
{
?>
<script language="javascript" type="text/javascript">
	function viewIt(item) {
	self.location.href = '<?php echo $this->Session->webroot; ?>help/videos/view:'+item;
	}
        
        $(function(){
            var $videoTr = $('#video-list-table tbody tr');
            
            
            $videoTr.click(function(){
                var item = $(this).attr('rel');
                
                viewIt(item);
            });
            
            $('#video_name')
                .keyup(function(){
                    var 
                        val = $.trim($(this).val()),
                        re = new RegExp(val, 'i');


                    $videoTr.each(function(){
                        var name = $(this).attr('vname');

                        if (!name) {
                            $(this).show();
                            return true;
                        }

                        if (name.search(re) === -1) {
                            $(this).hide();
                        } else {
                            $(this).show();
                        }
                    })

                })
                .addClear({
			closeImage: "/img/clear.png",
                        onClear: function(){
                            $videoTr.show();
                        }
		});            
            
        })
        
</script>
<?php
   $f='http://onetouchemr.com/support/youtube_videos_library.php';
   $fp=file($f);


?>

<form id="dummy-form">
    <table class="form" cellspacing="0" cellpadding="0" border="0">
        <tbody>
            <tr>
                <td style="padding-right: 10px;">Find video:</td>
                <td style="padding-right: 10px;">
                    <input name="video_name" class="noDragon" type="text" id="video_name" autofocus="autofocus" size="40"/>
                </td>
            </tr>
        </tbody>
    </table>
</form>

<table id="video-list-table" class="listing" cellspacing="0" cellpadding="0" >
    <thead>
        <th>Video</th>
    </thead>
    <tbody>
        <?php $row = 0; ?> 
        <?php foreach($fp as $value): ?>
        <?php $value = trim($value);
         if($value){
        	list($vid,$name)=explode('|', $value); ?> 
        <tr rel="<?php echo $vid; ?>" vname="<?php echo htmlentities($name); ?>">
            <td><?php echo $name; ?></td>
        </tr>
        
        <?php }
        endforeach;?> 
    </tbody>
</table>
<?
}
?>


</div>
