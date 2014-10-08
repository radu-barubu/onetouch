$(document).ready(function()
{	
	$("#frm_demographics").validate(
	{
		errorElement: "div",
		rules: 
		{
			'data[PatientDemographic][email]': 
			{
				required: false,
				email: true
			},
		},
		errorPlacement: function(error, element) 
		{
			if(element.attr("id") == "gender_male")
			{
				$("#gender_error").append(error);
			}
			else if(element.attr("id") == "home_phone")
			{
				element.parent().append(error);
			}
			else if(element.attr("id") == "dob")
			{
				$("#dob_error").append(error);
			}
			else
			{
				error.insertAfter(element);
			}
		},
		submitHandler: function(form) 
		{
			if(!$('#patient_id').val()) {
				$('.btn').removeAttr("onclick");
			}
			$('#frm_demographics').css("cursor", "wait");
			$('#imgLoad').css('display', 'block');
			$.post(
				thisURL, 
				$('#frm_demographics').serialize(), 
				function(data)
				{
					if(data.mrn_error == 'yes') 
					{
						$('#frm_demographics').css("cursor", "auto");
						$('#imgLoad').css('display', 'none');
						showInfo("MRN already in used.", "error");
						return;
					}
					if(data.task == 'addnew')
					{
						window.location = webroot + 'patients/index/task:edit/patient_id:'+data.patient_id+'/';
					}
					else
					{
                                                // Update patient's user account id
                                                // in case a new one was just created
                                                $('#patient_user_user_id').val(data.patient_user_id);
                                                $('#user_id').val(data.patient_user_id);
                                                
						$('#frm_demographics').css("cursor", "auto");
						$('#imgLoad').css('display', 'none');
						showInfo("Item(s) saved.", "notice");
						if (patientCheckin) {
						   goToNext();
						} else {
						   load_quick_visit_btn(); //reload quick visit encounter button after the patient status updated
						}
					}
				},
				'json'
			);
		}
	});
	var areacodelistseparated = '!201!202!203!204!205!206!207!208!209!210!212!213!214!215!216!217!218!219!224!225!226!228!229!231!234!239!240!242!246!248!250!251!252!253!254!256!260!262!264!267!268!269!270!276!281!284!289!301!302!303!304!305!306!307!308!309!310!312!313!314!315!316!317!318!319!320!321!323!325!330!331!334!336!337!339!340!343!345!347!351!352!360!361!385!386!401!402!403!404!405!406!407!408!409!410!412!413!414!415!416!417!418!419!423!424!425!430!432!434!435!438!440!441!442!443!450!456!458!469!470!473!475!478!479!480!484!500!501!502!503!504!505!506!507!508!509!510!512!513!514!515!516!517!518!519!520!530!533!534!540!541!551!559!561!562!563!567!570!571!573!574!575!579!580!581!585!586!587!600!601!602!603!604!605!606!607!608!609!610!612!613!614!615!616!617!618!619!620!623!626!630!631!636!641!646!647!649!650!651!657!660!661!662!664!670!671!678!681!682!684!700!701!702!703!704!705!706!707!708!709!710!712!713!714!715!716!717!718!719!720!724!727!731!732!734!740!747!754!757!758!760!762!763!765!767!769!770!772!773!774!775!778!779!780!781!784!785!786!787!800!801!802!803!804!805!806!807!808!809!810!812!813!814!815!816!817!818!819!828!829!830!831!832!843!845!847!848!849!850!855!856!857!858!859!860!862!863!864!865!866!867!868!869!870!872!876!877!878!888!900!901!902!903!904!905!906!907!908!909!910!912!913!914!915!916!917!918!919!920!925!928!931!936!937!938!939!940!941!947!949!951!952!954!956!970!971!972!973!978!979!980!985!989!';
	jQuery.validator.addMethod("areacode", function(value, element)
    	{    
	    var isValid = false;
            var pnum = value;

            if (pnum != null && pnum.length > 3)
	    {
                var ac = pnum.substring(0, 3);
        	if ((areacodelistseparated.indexOf('!' + ac + '!') > -1) || $("#no_home_phone").is(":checked"))
            		isValid = true;

            }

    	    return isValid;
	}, "Invalid Area code"); 
	
    var select_photo_btn_width = parseInt($('#patient_photo_upload_button').width()) + 
        parseInt($('#patient_photo_upload_button').css("padding-left").replace("px", "")) + 
        parseInt($('#patient_photo_upload_button').css("padding-right").replace("px", "")) + 
        parseInt($('#patient_photo_upload_button').css("margin-left").replace("px", "")) + 
        parseInt($('#patient_photo_upload_button').css("margin-right").replace("px", ""))
    ;
    
    var select_photo_btn_height = parseInt($('#patient_photo_upload_button').height()) +
        parseInt($('#patient_photo_upload_button').css("padding-top").replace("px", "")) + 
        parseInt($('#patient_photo_upload_button').css("padding-bottom").replace("px", "")) + 
        parseInt($('#patient_photo_upload_button').css("margin-top").replace("px", "")) + 
        parseInt($('#patient_photo_upload_button').css("margin-bottom").replace("px", ""))
    ;
	
	var allowedFileTypes = '*.gif; *.jpg; *.jpeg; *.png;'; // photo file types
	var validExt = false;	// true when a photo has a valid extension
	$('#photo').uploadify({
		'fileDataName' : 'file_input',
		'uploader'  : webroot + 'swf/uploadify.swf',
		'script'    : uploadify_script,
		'cancelImg' : webroot + 'img/cancel.png',
		'scriptData': {'data[path_index]' : 'temp'},
		'auto'      : true,
		'height'    : select_photo_btn_height,
		'width'     : select_photo_btn_width,
		'wmode'     : 'transparent',
		'hideButton': true,
		'imageArea'	: 'photo_img',
		'fileDesc'  : 'Image Files',
		'fileExt'   : allowedFileTypes, 
		'onSelect'  : function(event, ID, fileObj){
			var photoName = fileObj.name; // get filename
			var ext = photoName.substring(photoName.lastIndexOf('.') + 1).toLowerCase(); // get file extension
			var checkExt = allowedFileTypes.indexOf('.'+ext+';');
			
			if(checkExt<=0 && typeof($ipad)!=='object') {	// Don't check on the ipad
				// File type is not allowed
				showInfo("Please select correct image file types", "notice");
				validExt = false; // set false to stop the onProgress and onComplete call back
			} else {
				$('#photo_img').attr('src', webroot + 'img/blank.png');
				$('#photo_area_div').html("Uploading: 0%");
				validExt = true; 
			}
			return false;
		},
		'onProgress': function(event, ID, fileObj, data){
			if(!validExt) 
				return false;
			$('#photo_area_div').html("Uploading: "+data.percentage+"%");
			return true;
		},
		'onOpen'    : function(event, ID, fileObj){
			return true;
		},
		'onComplete': function(event, queueID, fileObj, response, data){
			if(!validExt) 
				return false;
			var url = new String(response);
			var filename = url.substring(url.lastIndexOf('/')+1);
                        
			$('#photo_area_div').html("").closest('td').find('.delete-image:hidden').show();
            
			/*           
			setTimeout(function(){
				$('#photo_img').attr('src', url_abs_path + filename);
			}, 1000);
			*/
			
			$('#photo_val').val(filename);
			
			saveDemographicPhoto(patient_id, filename, 'photo');
			
			return true;
		},
		'onError'   : function(event, ID, fileObj, errorObj){
			return true;
		}
	});
	
    var select_license_btn_width = parseInt($('#patient_licene_upload_button').width()) + 
        parseInt($('#patient_licene_upload_button').css("padding-left").replace("px", "")) + 
        parseInt($('#patient_licene_upload_button').css("padding-right").replace("px", "")) + 
        parseInt($('#patient_licene_upload_button').css("margin-left").replace("px", "")) + 
        parseInt($('#patient_licene_upload_button').css("margin-right").replace("px", ""))
    ;
    
    var select_license_btn_height = parseInt($('#patient_licene_upload_button').height()) +
        parseInt($('#patient_licene_upload_button').css("padding-top").replace("px", "")) + 
        parseInt($('#patient_licene_upload_button').css("padding-bottom").replace("px", "")) + 
        parseInt($('#patient_licene_upload_button').css("margin-top").replace("px", "")) + 
        parseInt($('#patient_licene_upload_button').css("margin-bottom").replace("px", ""))
    ;
	
	$('#driving_license').uploadify(
	{
		'fileDataName' : 'file_input',
		'uploader'  : webroot + 'swf/uploadify.swf',
		'script'    : uploadify_script,
		'cancelImg' : webroot + 'img/cancel.png',
		'scriptData': {'data[path_index]' : 'temp'},
		'auto'      : true,
		'height'    : select_license_btn_height,
		'width'     : select_license_btn_width,
		'wmode'     : 'transparent',
		'hideButton': true,
		'imageArea'	: 'driving_license_img',
		'fileDesc'  : 'Image Files',
		'fileExt'   : allowedFileTypes, 
		'onSelect'  : function(event, ID, fileObj) 
		{
			$('#driving_license_img').attr('src', webroot + 'img/blank.png');
			$('#driving_license_div').html("Uploading: 0%");
			return false;
		},
		'onProgress': function(event, ID, fileObj, data) 
		{
			$('#driving_license_div').html("Uploading: "+data.percentage+"%");
			return true;
		},
		'onOpen'    : function(event, ID, fileObj) 
		{
			return true;
		},
		'onComplete': function(event, queueID, fileObj, response, data) 
		{
			$('#driving_license_div')
                            .html("")
                            .closest('td').find('.delete-image:hidden').show();

			var url = new String(response);
			var filename = url.substring(url.lastIndexOf('/')+1);
			
			/*
			setTimeout(function(){
				$('#driving_license_img')
					.attr('src', url_abs_path + filename)
					.parent().attr('href', url_abs_path + filename)
						.CloudZoom();
			}, 1000);
			*/         
			
			$('#driving_license_val').val(filename);
			
			saveDemographicPhoto(patient_id, filename, 'license');
			
			return true;
		},
		'onError'   : function(event, ID, fileObj, errorObj) 
		{
			return true;
		}
	});
	
	$("#webcam_capture_area").dialog(
	{
		width: 730,
		height: 455,
		modal: true,
		resizable: false,
		autoOpen: false
	});
        
        $('.cloud-zoom').CloudZoom();

        $('.delete-image').each(function(){
            
            if ($(this).is('.hide')) {
                $(this).hide();
            }
            
            $(this).click(function(evt){
               evt.preventDefault();
               
               var 
                    self = this,
                    url = $(this).attr('href'),
                    patient_id = $(this).attr('rel');

               $(self).closest('td')
                    .find('.photo_area_text').append($('<img />').attr('src', ajax_loader));


               $.post(url, {patient_id: patient_id}, function(){
                   var 
                    data = 
                       $(self).closest('td')
                            .find('.p_img').attr('src', blank_img)
                            .end()
                            .find('.photo_area_text').html('Image Not Available')
                            .end()
                            .find('.cloud-zoom')
                                .data('zoom')
                            
                   if (data) {
                       data.destroy();
                   }
                            
                   $(self).hide();
               });
               
               
            });
            
            
            
        });
});

var current_photo_mode = 'photo';

function saveDemographicPhoto(patient_id, photo, pic_type)
{
	if(patient_id == '')
	{
		if(pic_type == 'photo')
		{
			$('#photo_img').attr('src', url_abs_path + photo);
		}
		else
		{
			$('#driving_license_img').attr('src', url_abs_path + photo).parent().attr('href', url_abs_path + photo).CloudZoom();
		}
			
		return;
	}
	
	var formobj = $("<form></form>");
	formobj.append('<input name="data[PatientDemographic][patient_id]" type="hidden" value="'+patient_id+'">');
	
	if(pic_type == 'photo')
	{
		formobj.append('<input name="data[PatientDemographic][patient_photo]" type="hidden" value="'+photo+'">');
		formobj.append('<input name="data[photo_type]" type="hidden" value="photo">');
	}
	else
	{
		formobj.append('<input name="data[PatientDemographic][driver_license]" type="hidden" value="'+photo+'">');
		formobj.append('<input name="data[photo_type]" type="hidden" value="license">');
	}
	
	$.post(
		save_photo_url, 
		formobj.serialize(), 
		function(data){
			if(pic_type == 'photo')
			{
				$('#photo_img').attr('src', url_abs_path + photo);
			}
			else
			{
				$('#driving_license_img').attr('src', url_abs_path + photo).parent().attr('href', url_abs_path + photo).CloudZoom();
			}
		}
	);
}

function updateWebcamPhoto(response)
{
	var url = new String(response);
	var filename = url.substring(url.lastIndexOf('/')+1);
	
	if(current_photo_mode == 'photo')
	{
		$('#photo_area_div')
                    .html("")
                    .closest('td').find('.delete-image:hidden').show();
                    
                setTimeout(function(){
                    $('#photo_img').attr('src', url_abs_path + filename);
                }, 1000);
                
		$('#photo_val').val(filename);
		
		saveDemographicPhoto(patient_id, filename, 'photo');
	}
	else
	{
		$('#driving_license_div')
                    .html("")
                    .closest('td').find('.delete-image:hidden').show();
                    
                    setTimeout(function(){
			$('#driving_license_img')
                            .attr('src', url_abs_path + filename)
                            .parent().attr('href', url_abs_path + filename)
                                .CloudZoom();
                        
                    }, 1000);
                                
		$('#driving_license_val').val(filename);
		
		saveDemographicPhoto(patient_id, filename, 'license');
	}
	
	$("#webcam_capture_area").dialog("close");
}
