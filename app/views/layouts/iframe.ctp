<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title><?php echo $title_for_layout; ?></title>
<link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>preferences/css/random:<?php echo md5(microtime()); ?>/" media="all" />
<?php
	echo $this->Html->css(array(
		'jquery.jscrollpane.css',
		'jquery.jscrollpane.lozenge.css'
	), 'stylesheet', array('media' => 'all'));
	
	echo $this->Html->script(array(
		'jquery/jquery-1.8.2.min.js',
		'jquery/jquery.mousewheel.js',
		'jquery/jquery.jscrollpane.min.js',
		'swfobject.js'
	));
	
	echo $scripts_for_layout;
?>
<link rel="stylesheet" type="text/css" href="<?php echo $this->webroot; ?>preferences/css/random:<?php echo md5(microtime()); ?>/" />
<style type="text/css">
    #content {
        background-color: #FFFFFF;
    }
    .dashboard-hoverable.hovered , .dashboard-hoverable a.hovered {
        background-color: #FDF5C8;
    }
    
    
</style>
<script type="text/javascript"> 
$(function()
{
	var win = $(window);
	// Full body scroll
	var isResizing = false;
	win.bind(
		'resize',
		function()
		{
			if (!isResizing) 
			{
				isResizing = true;
				var container = $('#content');
				// Temporarily make the container tiny so it doesn't influence the
				// calculation of the size of the document
				container.css(
					{
						'width': 1,
						'height': 1
					}
				);
				// Now make it the size of the window...
				container.css(
					{
						'width': win.width(),
						'height': win.height()
					}
				);
				isResizing = false;
				container.jScrollPane(
					{
						'verticalDragMaxHeight': 40,
						'showArrows': true
					}
				);
			}
		}
	).trigger('resize');
	
	window.resizeScroller = function(){
		win.trigger('resize');
	}
	// Workaround for known Opera issue which breaks demo (see
	// http://jscrollpane.kelvinluck.com/known_issues.html#opera-scrollbar )
	$('body').css('overflow', 'hidden');

	// IE calculates the width incorrectly first time round (it
	// doesn't count the space used by the native scrollbar) so
	// we re-trigger if necessary.
	if ($('#full-page-container').width() != win.width()) {
		win.trigger('resize');
	}
        
        $('.dashboard-hoverable').each(function(i){
            
            if (i%2) {
                $(this).addClass('striped');
            }
            
            $(this)
                .click(function(){
                    $(this).addClass('hovered');
                })
                .hover(function(){
                    $(this).addClass('hovered');
                }, function(){
                    $(this).removeClass('hovered');
                })
                .find('a')
                    .css('display', 'block')
                    .click(function(){
                        $(this).addClass('hovered');
                    });



        });
            
        
});
</script>
</head>
<body>
<div id="content">
<?php
	echo $this->Session->flash();
	echo $content_for_layout;
?>
</div>
</body>
</html>