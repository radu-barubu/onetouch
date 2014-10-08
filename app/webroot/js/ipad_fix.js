/*
var iPadLabels = function () {
   function fix() {
	var labels = document.getElementsByTagName('label'), target_id, el;
	for (var i = 0; labels[i]; i++) {
		if (labels[i].getAttribute('for')) {
			labels[i].onclick = labelClick;
		}
	}
  };
    function labelClick() {
    el = document.getElementById(this.getAttribute('for'));
	if (['radio', 'checkbox'].indexOf(el.getAttribute('type')) != -1) {
  		el.setAttribute('selected', !el.getAttribute('selected'));
	} else {
		el.focus();
	}
	
  };
 return {
   fix: fix
}
}();

window.onload = function () {	
if (navigator.userAgent.match(/iPhone/i) || navigator.userAgent.match(/iPod/i) || navigator.userAgent.match(/iPad/i)) {
	iPadLabels.fix();
}
}
*/

$(document).ready(function () {
  if (navigator.userAgent.match(/iPhone/i) || navigator.userAgent.match(/iPod/i) || navigator.userAgent.match(/iPad/i)) {
		$('label[for]').click(function () {
			//alert('ipad label fix was executed');
			var el = $(this).attr('for');

			if ($('#' + el + '[type=radio], #' + el + '[type=checkbox]').attr('selected', !$('#' + el).attr('selected'))) {

				return;

			} else {

				$('#' + el)[0].focus();

			}

		});
   }
});