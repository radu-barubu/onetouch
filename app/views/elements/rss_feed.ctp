<?php 
echo $this->Html->css('jslider');
echo $this->Html->script(array('jquery/jquery.jfeed.pack','jquery/jquery.flips.min'));
?>
<script type="text/javascript">
jQuery(function() {

    jQuery.getFeed({
		// RSS path must be local unless using a proxy
        url: '/fetch_rss.php?get=<?php echo $user['rss_file'];?>',
        success: function(feed) {
            
            var html = '';
        //    if (feed.items) {
			// Selects how many RSS items to parse
               for(var i = 0; i < feed.items.length && i < 50; i++) {
            
                var item = feed.items[i];
		<?php if ($isiPadApp): ?>
		  var lnk=item.link.replace('http','safari');
		<?php else: ?>
		  var lnk=item.link;
		<?php endif;?>
                html += '<div class=block>'
				// Gets the RSS link and title
				+ '<h3>' + '<a href="' + lnk + '" <?php if (!$isiPadApp) echo 'target="_blank"';?> >' + item.title + '</a>' + '</h3>';
                
                html += '<div class="grey">'
				// Gets the RSS pubDate
                + item.updated
                + '</div>'               
                // Gets the RSS description
				+ item.description
				+ '</div>';
               }
                 jQuery('#latestnews').append(html);
			
			// Adds the jQuery Flips element. The direction can left, top or bottom/
			$('#news').flips( { autorun_delay:1000, direction: 'right'});
          // } else {
	 //	html += 'See medical news here';
	//	jQuery('#latestnews').append(html);
	  // }
	}    
    });
});
</script>
<?php if (!empty($user['tutor_mode'])) $tp='300'; else $tp='220'; ?>
  <div style="position:absolute; top:<?php echo $tp; ?>px; right:21px;" id="news_feed_box">
    <div class="to-flips" id="news">
	<span class="title">News <a href="/preferences/user_options#rss"><?php echo $this->Html->image("icons/edit.png", array("class" => "edit_icon","style" => 'float:right'));?></a></span>
        <div class="content">
          <div id="latestnews"></div>
        </div>
        <div class="flipnav"></div>
    </div>
 </div>
