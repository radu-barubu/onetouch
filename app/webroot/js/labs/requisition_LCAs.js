var __multiPageTest = true;
var __currentPageCount = 0;

function adjustRowCount() {
	if (_Report.duplicateto.name1 == '')		{
		_iMaxRowsPage1 += 4;
	}
	else 		{
		_iMaxRowsPage1 -= 4;
	}

	if (_Report.order.bill_type != "T")		{
		_iMaxRowsPage1 += 10;
	}
		
		
	if (_Tests.length <= 18) {
		_iMaxRowsPage1 -= 6;
		__multiPageTest = false;
	} else {
		__multiPageTest = true;
	}
		
}

var otemr = {};

otemr.printLabel = function(){
	
	var label =
	'<div style="height:80px; "><table class="normal" cellpadding="0" cellspacing="0" style="page-break-inside:avoid; margin-left: 40px;">';

	var cell = '<td style="padding:2px 0 2px 0; height:33px; width:170px; font-size: 10px;">' 
	+ _Report.patient.last + ', ' + _Report.patient.first + ' ' + _Report.order.order_number  +'<br />' + _Report.patient.dob  + ' ' + _Report.order.collection_date + ' ' + _Report.order.collection_time  +'</td>';
	
	
	label += '<tr>' + cell	+ cell + cell + cell +'</tr>' +
	'<tr>' + cell + cell + cell + cell + '</tr></table></div>';
	document.writeln(label);
}

function startPage() {
	__currentPageCount++;
	var sPage = "page" + _iPage;
	
	if (_iPage == 1 && !__multiPageTest) {
		document.writeln('<div id="' + sPage + '" style="height: 848px">');
		
	} else {
		
		if (__currentPageCount > 1) {
			var remaining = (_Tests.length - 24) - (22 * (__currentPageCount-2)) ;
		}
		

		if (remaining <= 16) {
			document.writeln('<div id="' + sPage + '" style="height: 848px">');
		} else {
			document.writeln('<div id="' + sPage + '" style="height: 968px">');
		}

	}
	
	_iRow = 0;
}

function finishReport()	{
	endRows();
	otemr.printLabel();
	startFooter();
	commentsTests();
	endFooter();

	if (_Report.order.order_status != "standing")
	{
		drawABN();
	}
	if (!_bDocsOnReq)
	{
		writeODocs(true);
	}
}

