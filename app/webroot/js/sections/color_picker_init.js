$.fn.colorPicker.defaults.colors = [];

var color_palettes = [
	'ffaaaa', 'ff5656', 'ff0000', 'bf0000', '7f0000', 'ffffff',
	'ffd4aa', 'ffaa56', 'ff7f00', 'bf5f00', '7f3f00', 'e5e5e5',
	'ffffaa', 'ffff56', 'ffff00', 'bfbf00', '7f7f00', 'cccccc',
	'd4ffaa', 'aaff56', '7fff00', '5fbf00', '3f7f00', 'b2b2b2',
	'aaffaa', '56ff56', '00ff00', '00bf00', '007f00', '999999',
	'aaffd4', '56ffaa', '00ff7f', '00bf5f', '007f3f', '7f7f7f',
	'aaffff', '56ffff', '00ffff', '00bfbf', '007f7f', '666666',
	'aad4ff', '56aaff', '007fff', '005fbf', '003f7f', '4c4c4c',
	'aaaaff', '5656ff', '0000ff', '0000bf', '00007f', '333333',
	'd4aaff', 'aa56ff', '7f00ff', '5f00bf', '3f007f', '191919',
	'ffaaff', 'ff56ff', 'ff00ff', 'bf00bf', '7f007f', '000000',
	'ffaad4', 'ff56aa', 'ff007f', 'bf005f', '7f003f',
	'0082c0', 'd54e21', 'fefefe', 'eeeeee', 'e2e2e2', 'dddddd', '464646',
	'bbbbbb', '212121', '555555'
];

var default_accepted_contrast = 300;
var max_contrast = 750;
var hexDigits = new Array("0", "1", "2", "3", "4", "5", "6", "7", "8", "9", "a", "b", "c", "d", "e", "f"); 

function is_contrast_accepted(forecolor, backgroundcolor, accepted_contrast_from, accepted_contrast_to)
{
	var forecolor = new String(forecolor).replace("#", "");
	var backgroundcolor = new String(backgroundcolor).replace("#", "");
	
	var R1 = parseInt(forecolor.substr(0, 2), 16);
	var G1 = parseInt(forecolor.substr(2, 2), 16);
	var B1 = parseInt(forecolor.substr(4, 2), 16);
	
	var R2 = parseInt(backgroundcolor.substr(0, 2), 16);
	var G2 = parseInt(backgroundcolor.substr(2, 2), 16);
	var B2 = parseInt(backgroundcolor.substr(4, 2), 16);
	
	var diff = Math.max(R1, R2) - Math.min(R1, R2) + Math.max(G1, G2) - Math.min(G1, G2) + Math.max(B1, B2) - Math.min(B1, B2);
	
	if(diff >= accepted_contrast_from && diff <= accepted_contrast_to)
	{
		return true;
	}
	
	return false;
}

//Function to convert hex format to a rgb color
function rgb2hex(rgb) 
{
	rgb = rgb.match(/^rgb\((\d+),\s*(\d+),\s*(\d+)\)$/);
	return "#" + hex(rgb[1]) + hex(rgb[2]) + hex(rgb[3]);
}

function hex(x) 
{
	return isNaN(x) ? "00" : hexDigits[(x - x % 16) / 16] + hexDigits[x % 16];
}

$(document).ready(function()
{
	$('.simple_color_picker').each(function() {
		
		var accepted_color_palettes = color_palettes;
		
		//get element background color if available
		var bg_element = $(this).attr("bg_element");
		var bg_element_color = $(this).attr("bg_element_color");
		var accepted_contrast = default_accepted_contrast;
		var accepted_contrast_limit = max_contrast;
		
		if(typeof $(this).attr("contrast") != "undefined")
		{
			if($(this).attr("contrast") != "")
			{
				accepted_contrast = $(this).attr("contrast");
			}
		}
		
		if(typeof bg_element  != "undefined" || typeof bg_element_color  != "undefined") 
		{
			var bg_color_16 = '#ffffff';
			
			if(typeof bg_element  != "undefined")
			{
				var bg_color = $('#'+bg_element).css("background-color");
				var bg_color_16 = rgb2hex(bg_color);
			}
			
			if(typeof bg_element_color  != "undefined") 
			{
				var bg_color_16 = bg_element_color;
			}
			
			if(typeof $(this).attr("accepted_contrast_limit")  != "undefined") 
			{
				accepted_contrast_limit = $(this).attr("accepted_contrast_limit");
			}
			
			accepted_color_palettes = [];
			
			for(var i = 0; i < color_palettes.length; i++)
			{
				if(is_contrast_accepted(color_palettes[i], bg_color_16, accepted_contrast, accepted_contrast_limit))
				{
					accepted_color_palettes[accepted_color_palettes.length] = color_palettes[i];
				}
			}
		}
		
		$(this).colorPicker({colors: accepted_color_palettes});
	});
});