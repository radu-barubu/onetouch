<?php 

echo $this->Html->script('/jwplayer/jwplayer.js', array('inline' => false));

$lastMenu = isset($_COOKIE['last_menu']) ? $_COOKIE['last_menu'] : '' ;
$autoOpen = 'welcome';

if ($lastMenu) {
	$lastMenu = explode('/', ltrim($lastMenu, '/'));
	
	$autoOpen = $lastMenu[0];
	
}



?> 
<div style="overflow: hidden;">
 <h2>Help</h2>
    <!--<div class="title_area">
		<div class="title_text">
		<div class="title_item active">Tutorial</div>
		</div>
	</div>
   --><h3>Tutorial</h3>
</div>

<div id="tutorial-section">
	<div id="tutorial-navigation">
		<h3>Table Of Contents</h3>
		<form>
			<input type="text" id="tag-search" name="tag-search" value="" autocomplete="off" autocorrect="off" autocapitalize="off" /> 
			<input type="button" id="btn-search-tutorial" value="Search" class="btn"/>
		</form>
		<div id="tutorial-navigation-menu">
		</div>
	</div>
	
	<div id="tutorial-content" class="iframe-container-1">
		<h2>Please choose a video from the table of contents</h2>
		<div id="player-area">
			<div id="player"></div>
		</div>
	</div>
	
	<br class="clear" />
</div>	


<style type="text/css">
	
	<?php if ($isiPad || $isiPadApp): ?>
	.btn {
		font-size: 20px !important;
	}
	<?php endif;?> 
	
	#tutorial-navigation {
		width: 30%;
		float: left;
		/*background-color: #F6F6F6;*/
	}
	
	#tutorial-navigation h3 {
		font-size: 1.5em;
		margin: 0.5em;
	}
	
	#tutorial-navigation form {
		margin: 0.5em 0 2em 0;
		text-align: right;
	}	

	#tutorial-navigation form input[type=text] {
		width: 315px;
		text-align: left;
	}	
	
	.iframe-container-1 {
		width: 68%;
		float: right;
		padding: 0.25em;
		text-align: center;
	}
	
	.iframe-container-full {
		position: fixed;
		padding: 0.25em;
		text-align: center;
		top: 0;
		background-color: #fff;
		z-index: 9999999;
	}	
	
	.clear {
		clear: both;
	}
	
	#tutorial-navigation-menu {
		width: 320px;
		margin: auto;
		min-height: 1000px;
	}

	#tutorial-navigation-menu .btn {
		float: none;
		width: 99%;
		display: block;
		margin-top: 0.5em;
	}	
	
	div.top-level li {
		margin-left: 1.5em;
	}

	div.top-level {
		margin-bottom: 0.5em;
	}
	
	#btn-search-tutorial {
		float: none;
	}

	.toggleImage {
		margin-right: 0.5em;
	}
	
	#player-area {
		text-align: center;
		width: 100%;
		background-color: #fff;
	}	
	
	#player_wrapper {
		margin-bottom: 1em;
	}
	
	#next-btn {
		display: block;
		float: right;
	}
	
	#expand-btn {
		float: none;
		display: block;
		margin: auto;
		width: 200px;
	}
	
</style>
<script type="text/javascript">
	
var toc = <?php echo $toc ?>;
	
$(function(){
	var
		$currentItem = null,
		isSearching = false,
		$playerArea = $('#player-area'),
		$nav = $('#tutorial-navigation-menu'),
		$ul = $('<ul/>'),
		$btnSearch = $('#btn-search-tutorial'),
		$expander = 
			$('<a />')
				.attr({
					href: '',
					id: 'expand-btn'
				})
				.addClass('btn')
				.text('Expand')
				.click(function(evt){
					evt.preventDefault();


					if ($(this).is('.expanded')) {
						$(this).removeClass('expanded');


						$(this).text('Expand');

						$playerArea.trigger('contract');

					} else {

						$(this).addClass('expanded');

						$(this).text('Done ');
						
						$playerArea.trigger('expand');

					}


				}),	
		$next = 
			$('<a/>')
				.text('Next >')
				.addClass('btn')
				.attr({
					href: '',
					id: 'next-btn'
				})
				.click(function(evt){
					evt.preventDefault();
					$(this).trigger('showNext');
				})
				.bind('checkNext', function(){
					var 
						$available = null,
						total = 0,
						current = 0
					;
					
					if (!$currentItem) {
						$(this).hide();
						return false;
					}

					$available = $('div.playable')

					if (isSearching) {
						$available = $available.filter(':visible');
					} 

					total = $available.length;

					if (!total) {
						$(this).hide();
						return false;
					}

					current = $available.index($currentItem);

					if (current < 0) {
						$(this).hide();
						return false;
					}

					current++;
					if (current === total) {
						$(this).hide();
						return false;
					}
					
					$(this).show();
					$next.data('next', $available.eq(current));
					
				})
				.bind('showNext', function(){
					var $video = $(this).data('next');
					
					if ($video) {
						$video.children('a').click();
					}
				})
		,
		$content = $('#tutorial-content'),
		
		$tagSearch = $('#tag-search').addClear({
			onClear: function(){
				doSearch();
			}
		}),
		expandImage = '<?php echo $this->Html->url('/img/expand.png');?>',
		collapseImage = '<?php echo $this->Html->url('/img/collapse.png');?>'
	;
	
	$(window).resize(function(){
		$playerArea.trigger('autoFit');
	});
	
	$(document.body).keyup(function(evt){
		if (evt.which == 27 && $expander.is('.expanded')) {
			$expander.click();
		}
	});
	

	$playerArea.data('startWidth', $playerArea.width());
	$playerArea.data('startHeight', $playerArea.height());
	$playerArea.css('position', 'absolute');
	$playerArea.data('startPos', $playerArea.position());
	$playerArea.width($playerArea.data('startWidth'));
	
	
	$playerArea
		.bind('buildPlayer', function(evt, opts){
			evt.stopPropagation();
			
			$content.find('h2').remove();
			jwplayer('player').setup({
				flashplayer: '<?php echo $this->Html->url('/jwplayer/player.swf?'.time()); ?>',
				height: 270,
				width: 480,
				autoplay: 1
			});

			$(this)
				.append($expander)
				.append($next)
				.addClass('player-on')
				.trigger('autoFit')
		})
		.bind('removePlayer', function(evt){
			evt.preventDefault();
			jwplayer('player').remove();
			$(this).find('a').remove();
		})
		.bind('play', function(evt, file){
			evt.preventDefault();
			
			if (!$(this).is('.player-on')) {
				$(this).trigger('buildPlayer');
			}
			
			file = file.replace('http://lundeen.onetouchemr.com', '')
			
			
			jwplayer('player').load({
				file: file
			});
			
			$next.trigger('checkNext');
		})
		.bind('autoFit', function(evt){
			evt.stopPropagation();
			var 
				width = $(this).width(),
				height = width * (9/16) + 50,
				viewportHeight = $(window).height();
				
			if (height > viewportHeight) {
				height = viewportHeight - 50;
				width = height * (16/9);
			}
			
			
			jwplayer('player').resize(width, height);
			$('#player_wrapper')
				.css('margin-left', 'auto')
				.css('margin-right', 'auto')
			
		})
		.bind('contract', function(evt){
			evt.stopPropagation();
			
			var 
				width = $(this).data('startWidth'),
				height = $(this).data('startHeight'),
				position = $(this).data('startPos')
			;
			
			$(this)
				.css({
					width: width,
					left: position.left
				})
				.trigger('autoFit');
			
		})
		.bind('expand', function(evt){
			evt.stopPropagation();
			
			$(this)
				.css({
					width: '99%',
					height: '99%',
					left: 0
				})
				.trigger('autoFit');
			
		});
	
	
	
	
	function doSearch() {
		var tag = $.trim($tagSearch.val());
		
		$currentItem = null;
		
		if (tag == '') {
			$('div.navigation-item')
				.show()
				.trigger('showEntries');
				
				
			$('div.top-level').children('a').click();	
				
			isSearching = false;
			$next.trigger('checkNext');			
			return false;
		}
		
		if (tag.length < 2) {
			isSearching = false;
			$next.trigger('checkNext');			
			return false;
		}
		
		isSearching = true;
		
		$('div.navigation-item')
			.trigger('hideEntries')

		$('div.navigation-item.top-level')
			.hide();

		$('div.navigation-item').each(function(){
				if (hasTag($(this), tag)) {
					$(this)
						.addClass('filtered')
						.closest('.top-level')
							.show();
						
						
				} else {
					$(this)
						.removeClass('filtered')
						.closest('.top-level')
							.hide;
				}
		});


		$('div.top-level').has('.filtered').each(function(){
			var $entries = $(this).children('ul');

			$(this)
				.show()
				.trigger('showEntries');
				
			if ($entries.length) {
				$entries.children('li').each(function(){
					
					if ($(this).has('.filtered').length) {
						showFiltered($(this).children('.navigation-item'));
					} else if ($(this).is('.filtered')) {
						
					} else {
						$(this)
							.children('.navigation-item')
								.trigger('hideEntries')
								.hide()
					}
					
				})
			}
			
			$next.trigger('checkNext');			
		});


	}
	
	function showFiltered(el){
			var $entries = $(el).children('ul');
			
			$(el)
				.show()
				.trigger('showEntries');
				
			if ($entries.length) {
				$entries.children('li').each(function(){
					if ($(this).has('.filtered').length) {
						showFiltered($(this).children('.navigation-item'));
					} else if ($(this).is('.filtered')) {
						
					} else {
						$(this)
							.children('.navigation-item')
								.trigger('hideEntries')
								.hide()
					}
				})
			}

	}
	
	
	function hasTag(el, tags){
		var 
			tags = tags.split(' '),
			tagCount = tags.length,
			tagList = $(el).data('tags'),
			matchCount = 0
		;
		
		$.each(tags, function(){
			if (tagList.indexOf(this) !== -1) {
				matchCount++;
			}
			
		});
		
		if (matchCount == tagCount) {
			return true;
		}
		
		return false;
	}
	
	
	function createItem(node) {
		var 

			$div = 
					$('<div/>')
						.addClass('navigation-item')
						.addClass((node.url) ? 'playable' : 'non-playable')
						.bind('hideEntries', function(evt){
								evt.stopPropagation();
								var $entries = $(this).children('ul');

								if ($entries.length) {
									$(this).children('a').find('img').attr('src', expandImage);
									$entries.hide();
								}
							
						})
						.bind('showEntries', function(evt){
								evt.stopPropagation();
								var $entries = $(this).children('ul');

								if ($entries.length) {
									$(this).children('a').find('img').attr('src', collapseImage);
									$entries.show();
								} 
							
						})
						.bind('toggleEntries', function(evt){
								evt.stopPropagation();
								var $entries = $(this).children('ul');

								if ($entries.length) {
									if ($entries.is(':visible')) {
										$(this).children('a').find('img').attr('src', expandImage);
										$entries.hide();
									} else {
										$(this).children('a').find('img').attr('src', collapseImage);
										$entries.show();
									}
								
								}
						})
						.append(
							$('<a>').text('' + node.title)
								.addClass('btn')
								.attr('href', node.url)
								.click(function(evt){
									evt.preventDefault();
									var url = $.trim($(this).attr('href'));
									
									if (url) {
										<?php if(!isset($isiPadApp) || !$isiPadApp): /*browser -- use normal jquery ready functions */ ?>
											$currentItem = $(this).closest('div.navigation-item');
											$playerArea.trigger('play', url);
										<?php else:?>
											window.location = url;
										<?php endif; ?>	
									} else {
										$(this).parent().trigger('toggleEntries');
									}
								})
						)
						.data('tags', node.tags),
						
			$entries = $('<ul />')
		;
		
		if (node.entries) {
		
				$div.find('a').prepend(
						$('<img />')
							.addClass('toggleImage')
							.attr('src', expandImage)
					)
		
		
			$.each(node.entries, function(){
				var item = createItem(this);
				$entries.append($('<li/>').append(item));
			});
			
			$div.append($entries);
		}
		
		return $div;
	}
	
	function nextItem () {

		
	
	}
	
	$.each(toc, function(){
		var item = createItem(this);
		
		item.addClass('autoOpen-'+ this.autoOpen);
		$ul.append($('<li/>').append(item.addClass('top-level')));
		
	});

	$nav.append($ul);
		
	
	$btnSearch.click(function(){
		doSearch();
	});
	
	$tagSearch
		.keyup(function(evt){
				doSearch();
		})
		.closest('form')
			.submit(function(evt){
				evt.preventDefault();
			});
	
	
	$('div.top-level').trigger('hideEntries');


	var 
		hash = document.location.hash,
		$autoPlay = null
	;

	if (hash) {

		hash = hash.replace('#', '') + '.mp4';
		
		$('div.playable').each(function(){
			var url = $(this).children('a').attr('href');
			
			if (url.indexOf(hash) !== -1) {
				$autoPlay = $(this);
				return false;
			}
			
			
		})
		
		$autoPlay.closest('.top-level').children('a').click();
		
		$autoPlay.children('a').click();
		
	} else {
		var 
			autoOpen = '.autoOpen-<?php echo $autoOpen; ?>',
			$div = $(autoOpen);

		if (!$div.length) {
			$div = $('.autoOpen-welcome'); 
		} 

		$div.children('a').click();
	}
	
})	
</script>	
