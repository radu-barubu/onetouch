<h2>Schedule</h2>
<?php
$task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
$patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
$ipadAdjust = (isset($isiPad) && $isiPad) ? 20 : 0; 

echo $this->Html->css('dailog.css');
echo $this->Html->css('calendar.css');
echo $this->Html->css('dp.css');
echo $this->Html->css('alert.css');
echo $this->Html->css('main.css');
echo $this->Html->script('multiple-select-master/jquery.multiple.select.js');
echo $this->Html->script('date.format.js');
echo $this->Html->script('jquery/Plugins/Common.js');
echo $this->Html->script('jquery/Plugins/datepicker_lang_US.js');
echo $this->Html->script('jquery/Plugins/jquery.datepicker.js');
echo $this->Html->script('jquery/Plugins/jquery.alert.js');
echo $this->Html->script('jquery/Plugins/jquery.ifrmdailog.js');
echo $this->Html->script('jquery/Plugins/wdCalendar_lang_US.js');
echo $this->Html->script('jquery/Plugins/jquery.calendar.js');
?>
<link rel="stylesheet" type="text/css" href="/preferences/multiple_select" />

<?php if($task == 'addnew' && $patient_id): ?>
<div id="appointment-for-patient" class="notice center">
  <?php if (empty($appointment_request) ) {?>
  Please choose an appointment slot to continue making an appointment for <strong><?php echo $patientName; ?></strong>
  <?php } else { ?>
  On <?php echo __date($global_date_format, strtotime($appointment_request['request_date']));?> the provider requested an appointment with <?php echo $patientName;?> in <b><?php echo $appointment_request['return_time'].' '.$appointment_request['return_period'] ; ?> <button class="smallbtn" OnClick="reset_form();">Dismiss</button>
  <?php }?>
</div>
<br />
<?php endif;?>
<script type="text/javascript">
var patient_id = '<?php echo $patient_id ?>';
<?php if (!empty($appointment_request_id)) { ?>
var appointment_request_id = '<?php echo $appointment_request_id?>';
<?php } ?>
var hour_item_height = 120; // Had to moltiplicate the original heigth *2 becouse now the heigth is 80px
var scheduler_begin_hour = <?php echo $operational_hours->start; ?>;
var scheduler_end_hour = <?php echo $operational_hours->end; ?>;
var time_format = 12;
var scheduler_height = (scheduler_end_hour - scheduler_begin_hour) * (hour_item_height+0.5);
var adjacent_position = scheduler_begin_hour * hour_item_height;
var display_position = 0;
var operational_days = <?php echo json_encode($operational_days); ?>;
var today_date = new Date(<?php echo __date("Y"); ?>, <?php echo (int) __date("n") - 1; ?>, <?php echo __date("j"); ?>, <?php echo (int) __date("H"); ?>, <?php echo (int) __date("i"); ?>, <?php echo (int) __date("s"); ?>);

<?php if ($global_date_format == 'Y/m/d'): ?>
    var global_date_format = 'yyyy/mm/dd';
<?php elseif ($global_date_format == 'd/m/Y'): ?>
    var global_date_format = 'dd/mm/yyyy';
<?php else: ?>
    var global_date_format = 'mm/dd/yyyy';
<?php endif; ?>
	
var current_view = 'day';

function compareDate(date1, date2)
{
    if(date1.getDay() == date2.getDay() && date1.getMonth() == date2.getMonth() && date1.getFullYear() == date2.getFullYear())
    {
        return true;
    }
    else
    {
        return false;
    }
}
	
var $scheduleCount = <?php echo $scheduleCount; ?>;	


(function(){
	var tId = null;

	window.schedule_listener = function schedule_listener()
	{
			$.post(
					'<?php echo $html->url(array('controller' => 'schedule', 'action' => 'schedule_listener')); ?>', 
					'', 
					function(data)
					{
							// Clear any schedule listening timeout currently waiting
							if (tId) {
								clearTimeout(tId);
							}
						
							tId = window.setTimeout("schedule_listener()", 4000);

							if(data != $scheduleCount)
							{
									$("#gridcontainer").reload();
									$scheduleCount = data;
							}
					}
			);
	}
	
})();

function check_update() {
	schedule_listener();
}

function reset_form() {
  window.patient_id = '';
 <?php if (!empty($appointment_request_id)):?>
  window.appointment_request_id='';
  parent.winheight= winheight - 30;
  <?php endif; ?>
  $('#appointment-for-patient').slideUp('slow');
}

var winheight="<?php

          $ht=555;
        if (count($schedule_location) > 1)
          $ht=590;
        if(!empty($appointment_request_id))
          $ht=$ht+30;

          $ht=$ht+$ipadAdjust;

                print $ht;?>";
	
$(document).ready(function() 
{
	
	function processrequest(value){
		 if(($('#room_id').val()==null) && ($('#location_id').val()==null) && ($('#provider_id').val()==null)){
			 $("#gridcontainer").reCall([{name:"provider_id",value:$("#provider_id").val()},{name:"room",value:$("#room_id").val()},{name:"location",value:$("#location_id").val()}]);
		 } else {
				if($('#location_id').val()!=null){	
					$.post('<?php echo $this->Session->webroot; ?>schedule/getstartendTime_location/location_id:'+$("#location_id").val()+'/',  '', 
					function(data)
					{
						if(data)
						{
							scheduler_begin_hour = data.start;
							scheduler_end_hour = data.end;
							scheduler_height = (scheduler_end_hour - scheduler_begin_hour) * (hour_item_height+0.5);
							adjacent_position = scheduler_begin_hour * hour_item_height; 

						}
						$("#gridcontainer").reCall([{name:"provider_id",value:$("#provider_id").val()},{name:"room",value:$("#room_id").val()},{name:"location",value:$("#location_id").val()}]);
						$(".calmain").css({'height':'auto'});
						if($("#mvEventContainer").is(':visible')){// check  month view and adapt dimension of divs
							$("#gridcontainer").fadeTo(800,0.4,function(){
								var m_r_h = $("#mvEventContainer").outerHeight(true);
								$(".calmain").css({'height':m_r_h+20});
								$("#gridcontainer").css({'height':m_r_h});
								$("#gridcontainer").fadeTo(0,1);
							});
						}
						initAutoLogoff();
					}, 'json');
	} else {
		<?php  if (count($schedule_location) != 1){ ?>
		$("#gridcontainer").reCall([{name:"provider_id",value:$("#provider_id").val()},{name:"room",value:$("#room_id").val()},{name:"location",value:0}]);
		<?php } ?>
	}
	if($('#provider_id').val()!=null){
		 $("#gridcontainer").reCall([{name:"provider_id",value:$("#provider_id").val()},{name:"room",value:$("#room_id").val()},{name:"location",value:$("#location_id").val()}]);

        initAutoLogoff();
		
	} else {
		 $("#gridcontainer").reCall([{name:"provider_id",value:0},{name:"room",value:$("#room_id").val()},{name:"location",value:$("#location_id").val()}]);

        initAutoLogoff();
	}
	
	if($('#room_id').val()!=null){
		 $("#gridcontainer").reCall([{name:"provider_id",value:$("#provider_id").val()},{name:"room",value:$("#room_id").val()},{name:"location",value:$("#location_id").val()}]);

        initAutoLogoff();
		
	} else {
		$("#gridcontainer").reCall([{name:"provider_id",value:$("#provider_id").val()},{name:"room",value:0},{name:"location",value:$("#location_id").val()}]);
		 initAutoLogoff();
	} 
}


} 
		 
	  $('select#location_id').multipleSelect({
		  placeholder:"Locations",
		  onCheckAll: function() {
			  processrequest();
				},
		  onUncheckAll: function(){
			  processrequest();
			
		  },
		  onClick : function(){
				processrequest();
				}
	  });
	  $('select#provider_id').multipleSelect({
	  placeholder:"Providers",
		  onCheckAll: function() {
			  processrequest();
				},
		  onUncheckAll: function(){
			  processrequest(1);
			 
		  },
		  onClick : function(){
				processrequest();
				}
	  });
	  $('select#room_id').multipleSelect({
	  placeholder:"Rooms",
		  onCheckAll: function() {
			  processrequest();
				},
		  onUncheckAll: function(){
			  processrequest();
			  //$('select#room_id').multipleSelect('checkAll');
		  },
		  onClick : function(){
				processrequest();
				}
	  });
	  $('select').multipleSelect('checkAll');
	  
	

    <?php if ($appointment): ?>
    <?php 
        $ts = strtotime($appointment['ScheduleCalendar']['date'] . ' ' . $appointment['ScheduleCalendar']['starttime'] ); 
    
    ?> 
    var edit_date = new Date(<?php echo __date("Y", $ts); ?>, <?php echo (int) __date("n", $ts) - 1; ?>, <?php echo __date("j", $ts); ?>, <?php echo (int) __date("H", $ts); ?>, <?php echo (int) __date("i", $ts); ?>, <?php echo (int) __date("s", $ts); ?>);
            
    <?php else:?>             
    var edit_date = null;
    <?php endif;?>

    
    
    
    var DATA_FEED_URL = "<?php echo $html->url('/schedule/getCalendar') ?>";
    var op = 
    {
        view: "week",    
        theme:8,    
        showday:  edit_date || today_date,    
        weekstartday: 0,    
        EditCmdhandler:Edit,    
        DeleteCmdhandler:Delete,    
        ViewCmdhandler:View,    
        onWeekOrMonthToDay:wtd,    
        onBeforeRequestData: cal_beforerequest,    
        onAfterRequestData: cal_afterrequest,    
        onRequestDataError: cal_onerror, 
        quickAddHandler: false,
        autoload:true,
        <?php if($this->QuickAcl->getAccessType("schedule", "index") == 'W'): ?>enableDrag: true, <?php endif; ?>
        url: DATA_FEED_URL + "?method=list",  
        quickAddUrl: DATA_FEED_URL + "?method=add", 
        quickUpdateUrl: DATA_FEED_URL + "?method=update",    
        quickDeleteUrl: DATA_FEED_URL + "?method=remove"    
    };
		
    var $dv = $("#calhead");
    var _MH = ((scheduler_end_hour - scheduler_begin_hour) * (hour_item_height) + 120);
    var dvH = $dv.height() + 10;
    op.height = _MH - dvH;
    op.eventItems =[];
        
    var p = $("#gridcontainer").bcalendar(op).BcalGetOp();

    if (p && p.datestrshow) 
    {
        $("#txtdatetimeshow").text(p.datestrshow);
    }
            
    $("#caltoolbar").noSelect();
        
    $("#hdtxtshow").datepicker({ picker: "#txtdatetimeshow", showtarget: $("#txtdatetimeshow"),
        onReturn:function(r){
            var p = $("#gridcontainer").gotoDate(r).BcalGetOp();
            if (p && p.datestrshow) {
                $("#txtdatetimeshow").text(p.datestrshow);
				todayBtnHandler(p);
            }

            $("#gridcontainer").reload();
            $("#hdtxtshow").val($("#txtdatetimeshow").html());
        } 
    });

    function cal_beforerequest(type)
    {
        var t="Loading";
        switch(type)
        {
            case 1:
                t="Loading....";
                break;
            case 2:
            case 3:
            case 4:
                t="The request is being processed ...";
                break;
        }
        
        $("#errorpannel").hide();
        $("#loadingpannel").html(t).show();
    }

    function cal_afterrequest(type)
    {
        switch(type)
        {
            case 1:
                $("#loadingpannel").hide();
                break;
            case 2:
            case 3:
            case 4:
                $("#loadingpannel").html("Success");
                window.setTimeout(function(){ $("#loadingpannel").hide();},5000);
                break;
        }
    }

    function cal_onerror(type,data)
    {
        //alert(type+" "+data.responseText);
        $("#errorpannel").show();
    }
		
    function createDateObj(data)
    {
        var data = new String(data);

        var data_arr = data.split(' ');
        var date_arr = data_arr[0].split('/');
        var time_arr = data_arr[1].split(':');
        return new Date(date_arr[2], parseInt(date_arr[0])-1, date_arr[1], time_arr[0], time_arr[1], 0);
    }
		
    function getDateObject(data)
    {
        var data = new String(data);

        var data_arr = data.split(' ');
        var date_arr = data_arr[0].split('/');
        var time_arr = data_arr[1].split(':');

        var dateObj = new Date(date_arr[2], parseInt(date_arr[0])-1, date_arr[1], time_arr[0], time_arr[1], 0);

        return dateObj;
    }
		
    function addAdjacentHour(data)
    {			
        var current_date = getDateObject(data)
        var new_date = DateAdd("h", scheduler_begin_hour, current_date);

        var date_str = (new_date.getMonth()+1) + '/' + new_date.getDate() + '/' + new_date.getFullYear() + ' ' + new_date.getHours() + ':' + new_date.getMinutes();

        return date_str;
    }
            
    function Edit(data)
    {
        data[2] = addAdjacentHour(data[2]);
        data[3] = addAdjacentHour(data[3]);
		
		<?php if($this->QuickAcl->getAccessType("schedule", "index") == 'R'): ?>
		if(data[0] == '0')
		{
			return;	
		}
		<?php endif; ?>


        var eurl="<?php echo $html->url('/schedule/edit_calendar') ?>?id={0}&start={2}&end={3}&isallday={4}&title={1}";   
        if(data)
        {
            var url = StrFormat(eurl,data);
            OpenModelWindow(url+"&location_id="+$("#location_id").val()+"&provider_id="+$("#provider_id").val()+"&room_id="+$("#room_id").val()+"&patient_id="+window.patient_id<?php if(!empty($appointment_request_id)) echo '+"&appointment_request_id="+window.appointment_request_id'; ?>,{ width: 620, height: winheight, caption:"Manage Appointment",onclose:function(){
                    $("#gridcontainer").reload();
                }});
        }

        initAutoLogoff();
    }
    
    function View(data)
    {
        var str = "";
        $.each(data, function(i, item){
            str += "[" + i + "]: " + item + "\n";
        });
        alert(str);
    }
    
    function Delete(data,callback)
    {   
        $.alerts.okButton="OK";  
        $.alerts.cancelButton="Cancel";  
        hiConfirm("Are You Sure to Delete this Appointment", 'Confirm',function(r){ r && callback(0);});   

        initAutoLogoff();    
    }

    function wtd(p)
    {
        if (p && p.datestrshow) {
            $("#txtdatetimeshow").text(p.datestrshow);
        }
        $("#caltoolbar div.fcurrent").each(function() {
            $(this).removeClass("fcurrent");
        })
        $("#showdaybtn").addClass("fcurrent");

        $("#gridcontainer").reload();

        initAutoLogoff();
    }

    $("#showdaybtn").click(function(e){
	$(".calmain").css({'height':'auto'});
        $("#caltoolbar div.fcurrent").each(function() 
        {
            $(this).removeClass("fcurrent");
        });
        
        $(this).addClass("fcurrent");
        var p = $("#gridcontainer").swtichView("day").BcalGetOp();
        if (p && p.datestrshow) 
        {
            $("#txtdatetimeshow").text(p.datestrshow);
        }

        $("#gridcontainer").reload();

        initAutoLogoff();

        current_view = 'day';
    });

    $("#showweekbtn").click(function(e){
		$(".calmain").css({'height':'auto'});
        $("#caltoolbar div.fcurrent").each(function() 
        {
            $(this).removeClass("fcurrent");
        });
        
        $(this).addClass("fcurrent");
        var p = $("#gridcontainer").swtichView("week").BcalGetOp();
        if (p && p.datestrshow) {
            $("#txtdatetimeshow").text(p.datestrshow);
        }

        $("#gridcontainer").reload();

        initAutoLogoff();

        current_view = 'week';
    });

    $("#showmonthbtn").click(function(e) {
        $("#caltoolbar div.fcurrent").each(function() {
            $(this).removeClass("fcurrent");
        })
        $(this).addClass("fcurrent");
        var p = $("#gridcontainer").swtichView("month").BcalGetOp();
        if (p && p.datestrshow) {
            $("#txtdatetimeshow").text(p.datestrshow);
        }

        $("#gridcontainer").reload();

        initAutoLogoff();

        current_view = 'month';
    });
            
    $("#showreflashbtn").click(function(e){
        $("#gridcontainer").reload();
        initAutoLogoff();
    });
		
    function todayBtnHandler(p)
    {
        $.post('<?php echo $html->url(array('controller' => 'schedule', 'action' => 'get_server_datetime')); ?>', '', function(data)
        {
            var ret_dt = new Date(data.year, data.month, data.day, data.hour, data.minute, data.second);

            if(compareDate(p.showday, ret_dt))
            {
                $('#showtodaybtn').hide();
            }
            else
            {
                $('#showtodaybtn').show();
            }
        },
        'json');
    }

    $("#showtodaybtn").click(function(e) {
        $.post('<?php echo $html->url(array('controller' => 'schedule', 'action' => 'get_server_datetime')); ?>', '', function(data)
        {
            var ret_dt = new Date(data.year, data.month, data.day, data.hour, data.minute, data.second);

            var p = $("#gridcontainer").gotoDate(ret_dt).BcalGetOp();

            if (p && p.datestrshow) {
                $("#txtdatetimeshow").text(p.datestrshow);
            }

            todayBtnHandler(p);

            $("#gridcontainer").reload();

            initAutoLogoff();
        },
        'json');
    });
        
    

    $("#sfprevbtn").click(function(e) {
		
		if($("#mvEventContainer").is(':visible')){// check  month view and adapt dimension of divs
			$("#gridcontainer").fadeTo(800,0.4,function(){
				var m_r_h = $("#mvEventContainer").outerHeight(true);
				$(".calmain").css({'height':m_r_h+20});
				$("#gridcontainer").css({'height':m_r_h});
				$("#gridcontainer").fadeTo(0,1);
			});
		}
		
        var p = $("#gridcontainer").previousRange().BcalGetOp();
        if (p && p.datestrshow) {
            $("#txtdatetimeshow").text(p.datestrshow);
        }
        todayBtnHandler(p);
        $("#gridcontainer").reload();
        initAutoLogoff();
    });

    $("#sfnextbtn").click(function(e){
		
		if($("#mvEventContainer").is(':visible')){// check  month view and adapt dimension of divs
			$("#gridcontainer").fadeTo(800,0.4,function(){
				var m_r_h = $("#mvEventContainer").outerHeight(true);
				$(".calmain").css({'height':m_r_h+20});
				$("#gridcontainer").css({'height':m_r_h});
				$("#gridcontainer").fadeTo(0,1);
			});
		}

        var p = $("#gridcontainer").nextRange().BcalGetOp();
        if (p && p.datestrshow)
        {
            $("#txtdatetimeshow").text(p.datestrshow);
        }

        todayBtnHandler(p);

        $("#gridcontainer").reload();

        initAutoLogoff();
    });
    
    <?php if ($task != "addnew"): ?>
        $("#showdaybtn").click();
    <?php else: ?>
        $("#showweekbtn").click();
        var tmpdata= new Array();
        tmpdata[0]="";
        tmpdata[1]="New Appointment";
        tmpdata[2]="<?php echo __date("n/d/Y H:i"); ?>";
        tmpdata[3]="<?php echo __date("n/d/Y H:i"); ?>";
        //Edit(tmpdata);
    <?php endif; ?>
        
    $('#showtodaybtn').hide();

    $("#gridcontainer").reload();

    window.setTimeout("schedule_listener()", 3000);

    initAutoLogoff();

    <?php if($appointment): ?>
        var editdata= new Array();
        editdata[0]=<?php echo $appointment['ScheduleCalendar']['calendar_id'] ?>;
        editdata[1]="Edit Appointment";
        editdata[2]="<?php echo __date("n/d/Y H:i", $ts); ?>";
        editdata[3]="<?php echo __date("n/d/Y H:i", $ts + ($appointment['ScheduleCalendar']['duration']) * 60); ?>";
        Edit(editdata);
    <?php endif; ?> 

});
</script>
<div class="<?php if (isset($isMobile) && $isMobile) {echo 'mobile'; }  ?>">
<?php   echo $this->element("tutor_mode", array('tutor_mode' => $tutor_mode, 'tutor_id' => 90));  ?>
    <?php
    if (empty($schedule_location))
    {
        echo '<div class="error tab_content">No office location has been setup for scheduling use. Please go to Practice Locations under Administration, General to add at least 1 location.</div>';
    }
    else if (empty($users))
    {
        echo '<div class="error tab_content">No Providers have been added. Please go to Users under "Administration" -> "User Accounts" to add at least 1.</div>';
    }
    ?>   
    <div id="calhead" style="padding-left:1px;padding-right:1px;">

        <div class="cHead">
            <div class="ftitle">All Appointments</div>
            <div id="loadingpannel" class="ptogtitle loadicon" style="display: none;">Loading data...</div>
            <div id="errorpannel" class="ptogtitle loaderror" style="display: none;">Sorry, could not load your data, please try again later</div>
        </div>


        <div id="caltoolbar" class="ctoolbar">
            <div>
                <span>
                    <?php
                    $location_array = array();
                    foreach ($schedule_location as $location)
                    {
                        $location_array[$location['PracticeLocation']['location_id']] = $location['PracticeLocation']['location_name'];
                    }
                    
                    if (count($schedule_location) != 1)
                    {
                        echo $form->input('location_id', array('type' => 'select', 'multiple'=>'multiple','options' => $location_array, 'selected' => '','style'=>'width:200px;', 'label' => false,'id' => 'location_id'));
                    }
                    ?>
                    
                    <!--
                    <div class="input select">
                        <select name="data[location_id]" id="location_id">
                            <option value="" start="<?php echo $operational_hours->start; ?>" end="<?php echo $operational_hours->end; ?>">All Locations</option>
                            <option value="1" start="7" end="19" >Location 1</option>
                            <option value="2" start="8" end="21">Location 2</option>
                            <option value="3" start="9" end="17">Location 3</option>
                        </select>
                    </div>
                    -->
                </span>
            </div>
            <div class="btnseparator"></div>

            <div id="fshowprovider"> 
                <span>
                    <?php
                    $user_array = array();
                    foreach ($users as $user)
                    {
                        $user_array[$user['UserAccount']['user_id']] = $user['UserAccount']['firstname'] . " " . $user['UserAccount']['lastname'];
                    }
                    echo $form->input('provider_id', array('type' => 'select','multiple'=>'multiple', 'options' => $user_array,'style'=>'width:200px;', 'selected' => '', 'label' => false,'id' => 'provider_id'));
                    ?>
                </span>
            </div>
            <div class="btnseparator"></div>

            <div id="fshowroom">
                <span >
                    <?php
                    $schedule_room_array = array('0'=>'[No Room]');
                    foreach ($schedule_rooms as $schedule_room)
                    {
                        $schedule_room_array[$schedule_room['ScheduleRoom']['room_id']] = $schedule_room['ScheduleRoom']['room'];
                    }
                    echo $form->input('room_id', array('type' => 'select','multiple'=>'multiple', 'options' => $schedule_room_array, 'selected' => '','style'=>'width:200px;', 'label' => false,'id' => 'room_id'));
                    ?>
                </span>
            </div>
            <div class="btnseparator"></div>

            <div id="showdaybtn" class="fbutton">
                <div><span class="showdayview">Day</span></div>
            </div>

            <div  id="showweekbtn" class="fbutton fcurrent">
                <div><span class="showweekview">Week</span></div>
            </div>

            <div  id="showmonthbtn" class="fbutton">
                <div><span class="showmonthview">Month</span></div>
            </div>

            <div class="btnseparator"></div>

            <div class="fshowdatep fbutton">
                <div><input type="hidden" name="txtshow" id="hdtxtshow" /><span id="txtdatetimeshow"></span></div>
            </div>
            <div class="btnseparator"></div>

            <div id="sfnextbtn" class="fbutton" style="float: right;"> <span class="fnext"></span> </div>

            <div id="sfprevbtn" class="fbutton" style="float: right;"> <span class="fprev"></span> </div>

            <div class="btnseparator" style="float: right;"></div>

            <div id="showtodaybtn" class="fbutton" style="float: right;">
                <div><span class="showtoday"> Today</span></div>
            </div>
        </div>
        <div class="btnseparator"></div>
        <div class="clear"></div>
    </div>
</div>
<div style="padding:1px;">
    <div class="t1 chromeColor"> &nbsp;</div>
    <div class="t2 chromeColor"> &nbsp;</div>
    <div id="dvCalMain" class="calmain printborder">
        <div id="gridcontainer" style="overflow-y: visible; "> </div>
    </div>
    <div class="t2 chromeColor"> &nbsp;</div>
    <div class="t1 chromeColor"> &nbsp; </div>
</div>
</div>
<script type="text/javascript">
$(function(){
  <?php //if(!($task == 'addnew' && $patient_id)): ?>
	$('#txtdatetimeshow').click();					
	<?php //endif;?>
});
</script>
