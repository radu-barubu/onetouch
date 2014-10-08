$(document).ready(function()
{
	$("#dTtoggle").click(function() {
	  if($("#dToptions").is(":visible")) {
		hidedT();
	  } else {
		$("#dTtoggle").html('Cancel');
		$("#dToptions").show('500');
	  }
	});
	$("#dtSave").click(function() {
	  dtv=$.trim($("#dtValue").val());
	  if (dtv)
	  {
		$( "#dialog-confirm" ).show();
		$( "#dialog-confirm" ).dialog({
      		  resizable: false,
      		  //height:auto,
      		  modal: true,
      		  buttons: {
        		"Add": function() {
		/*	$('#document_type').append($("<option/>", { 
        			value: dtv,
        			text : dtv 
    			}));
		*/
			 //$('#document_type').append($(document.createElement("option")).attr("value",dtv).text(dtv));
			//$('#document_type').html("<option>Select\\</option><option value='"+dtv+"'>"+dtv+"</option>");
/*selectValues= {"1": {id: "4321", value: "option 1"}, "2": {id: "1234", value: "option 2"}};
$.fn.append.apply($('#document_type'),
    $.map(selectValues, function(val, idx) {
        return $("<option/>")
            .val(val.key)
            .text(val.value);
    })
);
*/
			$('<option/>').attr("value",dtv).text(dtv).appendTo("#document_type");
			 $('#document_type').val(dtv);
			 hidedT();
		 
         		  $( this ).dialog( "close" );
        		},
        	  	Cancel: function() {
			  hidedT(); 
          	  	  $( this ).dialog( "close" );
        		}
      		  }
    		});		
	  }
	});	

	function hidedT()
	{
                $("#dTtoggle").html('Edit');
                $("#dToptions").hide('slow');
                $("#dtValue").val("");
	}
});
