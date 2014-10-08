$(function() {
	
	// Initialize some jQuery objects
	var 
		//$error = $('#error'),
		//$info = $('#info'),
		$entry_section = $('#add-entry-section'),
		//$entry_name = $('#entry-name'),
		$schedule = $('#schedule'),
		//$clone = $('#clone'),
		$generate = $('#generate');
	
	// Start the setup
	init();
	
	// Setup code
	function init() {
		// Hide Stuff
		//$entry_section.hide();
		//$error.hide();
		//$info.hide();
		
		// Activate buttons

		$('#add-row')
			.click(addRow)
			.hover(addRowHighlight, addRowUndoHighlight);
		$('#remove-row')
			.click(removeRow)
			.hover(removeRowHighlight, removeRowUndoHighlight);
		$('#remove-entries')
			.click(removeEntries)
			.hover(removeEntriesHighlight, removeEntriesUndoHighlight);
		$('#generate').click(generateSchedule);
		
		// Whenever entries are created, let them be deletable
		$('.deletable').live('click', removeEntry);
		
		// Prevent text selection on double-click
		$('.actions a')
			.attr('unselectable','on')
			.css('MozUserSelect','none')
			.bind('selectstart.ui', function() {
				return false;
			});
			$schedule.selectable({
			filter: 'td',
			selected: function(event, ui)
			{ 
			    var classname = $(ui.selected).attr('class');
				$('#date').val('');
				$('#starttime').val('');
				$('#ampm').val('');
			    //User can only select the slots that doesn't have Appointments already.
			    if(classname=='ui-selectee ui-selected')
			    {
			       var selected_date = $(ui.selected).attr('date');
				   var selected_time = $(ui.selected).attr('time');
				   // alert('date:'+selected_date+' time:'+selected_time);
				   var splitted_time = selected_time.split('|')
				   $('#date').val(selected_date);
				   $('#starttime').val(splitted_time[0]);
				   $('#ampm').val(splitted_time[2]);
				   //$entry_section.hide();
				   $('.ui-selected').removeClass("ui-selected");
				   $(ui.selected).addClass("ui-selected");
				   $("#starttime_error").css('display','none');
			       // $("#" + ui.selected.id).text("I have been selected!")
				}
			}
		});
	}
	
	// Add a row to the table
	function addRow() {
		$schedule.find('tbody').append('<tr><th><input type="text" /></th><td></td><td></td><td></td><td></td><td></td></tr>');
		addRowUndoHighlight();
		addRowHighlight();
		return false;
	}
	
	// Highlight bottom row of table to cue user of add action
	function addRowHighlight() {
		$schedule.find('tbody tr:last-child *').addClass('adding');
	}
	
	// Undo highlight
	function addRowUndoHighlight() {
		$schedule.find('tbody tr *').removeClass('adding');
	}
	
	// Delete last row from the table
	function removeRow() {
		$schedule.find('tbody tr').last().remove();
		removeRowUndoHighlight();
		removeRowHighlight();
		return false;
	}
	
	// Highlight row to be deleted to cue user of delete action
	function removeRowHighlight() {
		$schedule.find('tbody tr:last-child *').addClass('deleting');
	}
	
	// Undo highlight
	function removeRowUndoHighlight() {
		$schedule.find('tbody tr *').removeClass('deleting');
	}
	
	

	
	// Delete entry from the table
	function removeEntry() {
		$(this).effect('explode', 500, function() {
			$(this).remove();
		});
		return false;
	}
	
	// Delete all entries from the table
	function removeEntries() {
		// Find all entries
		var $entries = $schedule.find('.entry');
		
		// Delete them
		$entries.fadeOut(function() {
			$entries.remove();
		});
		
		return false;
	}
	
	// Highlight entries to be deleted to cue user of deletion action
	function removeEntriesHighlight() {
		$('.entry').addClass('deleting');
	}
	
	// Undo highlight
	function removeEntriesUndoHighlight() {
		$('.entry').removeClass('deleting');
	}

	
	// Generate a schedule from the table
	function generateSchedule() {
		// Clone the table
		$clone.empty()
		$schedule.clone().removeAttr('id').removeClass('ui-selectable').addClass('cloned').appendTo($clone);
		
		// Remove input fields
		// Prevent entries from deletion
		// Display random entry for each cell
		$clone
			.find('input').each(function(index) {
				var text = $(this).val();
				$(this).replaceWith('<div>' + text + '</div>');
			}).end()
			.find('.deletable').removeClass('deletable').removeAttr('title').end()
			.find('.ui-selectee').removeClass('ui-selectee').end()
			.find('.ui-selected').removeClass('ui-selected').end()
			.find('.cloned tbody td').randomChild();
			
		return false;
	}

});

;(function($) {
	/* 
	 * Random Child (0.1)
	 * by Mike Branski (www.leftrightdesigns.com)
	 * mikebranski@gmail.com
	 *
	 * Copyright (c) 2008 Mike Branski (www.leftrightdesigns.com)
	 * Licensed under GPL (www.leftrightdesigns.com/library/jquery/randomchild/gpl.txt)
	 */
	$.fn.randomChild = function(settings) {
		return this.each(function(){
			var c = $(this).children().length;
			var r = Math.ceil(Math.random() * c);
			$(this).children().hide().parent().children(':nth-child(' + r + ')').show();
		});
	};

	// My extensions
	$.fn.message = function(strongText, plainText) {
		return this.each(function() {			
			$(this)
				.empty().fadeIn()
				.html('<p><strong>' + strongText + '</strong> ' + plainText + '</p>')
				.delay(2000).fadeOut();
		});
	};
})(jQuery);