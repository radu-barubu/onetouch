<h2>Administration</h2>
<?php 

$task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
$thisURL = $this->Session->webroot . $this->params['url']['url'];
$user = $this->Session->read('UserAccount');
 
$autologoff=$settings->autologoff_portal;

?>
<script type="text/javascript">
$(document).ready(function()
{
                $("#frm").validate({errorElement: "div"});
                //create bubble popups for each element with class "button"
                $('.practice_lbl').CreateBubblePopup();
                   //set customized mouseover event for each button
                   $('.practice_lbl').mouseover(function(){ 
                        //show the bubble popup with new options
                        $(this).ShowBubblePopup({
                                alwaysVisible: true,
                                closingDelay: 200,
                                position :'top',
                                align    :'left',
                                tail     : {align: 'middle'},
                                innerHtml: '<b> ' + $(this).attr('name') + '</b> ',
                                innerHtmlStyle: { color: ($(this).attr('id')!='azure' ? '#FFFFFF' : '#333333'), 'text-align':'center'},                                                                         
                                                themeName: $(this).attr('id'),themePath:'<?php echo $this->Session->webroot; ?>img/jquerybubblepopup-theme'                                                              
                   
                         });
                   });
                   
});                 
</script>                   
	<div style="overflow: hidden;">
		<?php echo $this->element("administration_general_links"); ?>
        	<?php echo $this->element("administration_patient_portal_links"); ?>
  <form id="frm" method="post" action="<?php echo $thisURL; ?>" accept-charset="utf-8" enctype="multipart/form-data">
        <table cellpadding="0" cellspacing="0" class="form">		
            <tr>
                <td><span class="practice_lbl" id="azure" name="How long if patient's are idle before being auto logged out" style="text-align:center; width:89px; "><label>Auto-logoff Timer:</label> <?php echo $html->image('help.png'); ?></span></td>
                <td><table cellpadding="0" cellspacing="">
                        <tr>
                            <td width="200"><div id="slider_autologoff"></div></td>
                            <td style="padding-left: 10px;">
                                <input type="hidden" name="data[PracticeSetting][autologoff_portal]" id="autologoff" readonly="readonly" size="2" />
                                <span id="autologoff_value"></span>
                            </td>
                        </tr>
                    </table>
                    <script>
                                                $(function() {
                                                        $( "#slider_autologoff").slider({
                                                                range: "max",
                                                                min: 1,
                                                                max: 20,
                                                                step: 1,
                                                                value: <?php echo $autologoff; ?>,
                                                                slide: function( event, ui ) {
                                                                        $( "#autologoff" ).val( ui.value + ' minutes' );
                                                                        $('#autologoff_value').html(ui.value + ' minutes');
                                                                }
                                                        });
                                                        $("#autologoff").val($("#slider_autologoff").slider("value") + ' minutes');
                                                        $('#autologoff_value').html($("#slider_autologoff").slider("value") + ' minutes');
                                                });
                                        </script>
                </td>
            </tr>		
</table>		
		
		
		
	
		</form>
	</DIV>
	<div class="actions" removeonread="true">
    <ul>
        <li><a href="javascript: void(0);" onclick="$('#frm').submit();">Save</a></li>
    </ul>
</div>
<?php
 echo $this->element("enable_acl_read", array('page_access' => $page_access)); ?>

