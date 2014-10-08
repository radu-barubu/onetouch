  if(!$.isEmptyObject(MacrosArr)) { //only exec if defined
    $("input[type=text],textarea").die('keyup.macro').live('keyup.macro', function() {
	var replace,k,newstr;
 	   str = $.trim($(this).val());
	   lastWord=str.split(/\r\n|\r|\n|\ /g).pop(); //pop off the last word
	   $.each(MacrosArr, function(key, value) {
	    if(lastWord == key) {
		k=key;
		replace=value;
	    }
	  });
	if (replace) {
	   newstr=str.replace(k,'');
	  $(this).val(newstr+replace);
	}
   });
 }

