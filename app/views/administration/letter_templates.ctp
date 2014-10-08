<h2>Administration</h2>
<?php

$task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
$template_id = (isset($this->params['named']['template_id'])) ? $this->params['named']['template_id'] : "";
$thisURL = $this->Session->webroot . $this->params['url']['url'];
$deleteURL = $this->Session->webroot . $this->params['controller'] . '/' . $this->params['action'] . '/' . 'task:delete' . '/';
$pdf_download = $this->Session->webroot.'administration/letter_templates/task:get_pdf';
if(isset($items_address))
{
 extract($items_address);

}



$http = 'http://';
if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'
    || $_SERVER['SERVER_PORT'] == 443) {

    $http = 'https://';
}

$adminUrl = $http . $_SERVER['SERVER_NAME'] . $url_abs_paths['administration'];


$adminFiles = scandir($paths['administration']);

$availableImages = array();

$allowFiles = array('gif', 'png', 'jpg', 'jpeg');
foreach ($adminFiles as $f) {
  $ext = array_pop(explode('.', $f));
  
  if (in_array($ext, $allowFiles)) {
    $availableImages[] = $f;
  }
  
}

$allClasses = array();
//debug($items_logo);
?>

<link rel="stylesheet" type="text/css" href="<?php echo $this->Session->webroot; ?>css/colorselect.css" />
    <link rel="stylesheet" type="text/css" href="<?php echo $this->Session->webroot; ?>css/jquery.autocomplete.css" />
  <style>
    <?php foreach(LetterTemplate::$fonts as $f): ?>
    <?php 
      $className = str_replace(' ', '_', $f);
      $allClasses[] =  $className;
    ?>
    .<?php echo $className; ?> {
      font-family: '<?php echo $f; ?>';
    }
    <?php endforeach;
    
    
    $allClasses = implode(' ', $allClasses);
    ?>
  </style>    
        <script language="javascript" type="text/javascript">
          
jQuery.fn.extend({
insertAtCaret: function(myValue){
  return this.each(function(i) {
    if (document.selection) {
      //For browsers like Internet Explorer
      this.focus();
      var sel = document.selection.createRange();
      sel.text = myValue;
      this.focus();
    }
    else if (this.selectionStart || this.selectionStart == '0') {
      //For browsers like Firefox and Webkit based
      var startPos = this.selectionStart;
      var endPos = this.selectionEnd;
      var scrollTop = this.scrollTop;
      this.value = this.value.substring(0, startPos)+myValue+this.value.substring(endPos,this.value.length);
      this.focus();
      this.selectionStart = startPos + myValue.length;
      this.selectionEnd = startPos + myValue.length;
      this.scrollTop = scrollTop;
    } else {
      this.value += myValue;
      this.focus();
    }
  });
}
});

$(function(){
  var $content = $('#contenttxt');
  var $availableTags = $('#available-tags');
  var $availableImages = $('#available-images');
  var adminUrl = '<?php echo $adminUrl; ?>';
  
  $('#available-fonts').change(function(){
    var className = $(this).val().replace(' ', '_');
    
    $content.removeClass('<?php echo $allClasses ?>');
    
    if (className) {
      $content.addClass(className);
    }
    
  }).trigger('change');
  
  $('#insert-tag').click(function(evt){
    evt.preventDefault();
    $content.insertAtCaret($availableTags.val());
  });
  
  $('#insert-image').click(function(evt){
    evt.preventDefault();
    var img = $.trim($availableImages.val());
    
    if (!img) {
      return false;
    }
    
    $content.insertAtCaret('[image:' + img + ']');
  });
  
		$('#file_upload_image').uploadify(
		{
			'fileDataName' : 'file_input',
			'uploader'  : '<?php echo $this->Session->webroot; ?>swf/uploadify.swf',
			'script'    : '<?php echo $html->url(array('controller' => 'patients', 'action' => 'upload_file', 'session_id' => $session->id())); ?>',
			'cancelImg' : '<?php echo $this->Session->webroot; ?>img/cancel.png',
			'scriptData': {'data[path_index]' : 'administration'},
			'auto'      : true,
			'height'    : 35,
			'width'     : 192,
			'wmode'     : 'transparent',
			'hideButton': true,
			'imageArea'	: 'logo_image_field',
			'fileDesc'  : 'Image Files',
			'fileExt'   : '*.gif; *.jpg; *.jpeg; *.png;', 
			
			'onSelect'  : function(event, ID, fileObj) 
			{

 				return false;
			},
			'onProgress': function(event, ID, fileObj, data) 
			{
				return true;
			},
			'onOpen' : function(event, ID, fileObj) 
			{
				//$(window).css("cursor", "wait");
			},
			'onComplete': function(event, queueID, fileObj, response, data) 
			{
				var url = new String(response);
				var filename = url.substring(url.lastIndexOf('/')+1);
        var displayName = filename.substring(filename.indexOf('_')+1)
        $availableImages.append($('<option/>').attr('value', filename).text(displayName));
        
        $availableImages.val(filename);
        $content.insertAtCaret('[image:' + $availableImages.val() + ']');
				return true;
			},
			'onError' : function(event, ID, fileObj, errorObj) 
			{

			}
		});  
  
  
});
          
          
        function deleteData()
        {
                        var total_selected = 0;
                        
                        $(".child_chk").each(function()
                        {
                                if($(this).is(":checked"))
                                {
                                                total_selected++;
                                }
                        });
                        
                        if(total_selected > 0)
                        {
                                $("#frm_template").submit();
                        }
                        else
                        {
                                alert("No Item Selected.");
                        }
        }


</script>
<div style="overflow: hidden;">
        <?php echo $this->element("administration_general_links"); ?>
        <span id="imgLoad" style="float: left; display:none; margin-top: -2px;"><?php echo $html->image('ajax_loaderback.gif', array('alt' => 'Loading...')); ?></span>
        <div id="lab_records_area" class="tab_area"> 
<?php
if($task == 'addnew' || $task == 'edit')
{
        if($task == 'addnew')
        {
                        //Init default value here
                        $id_field = "";
                        $template_name = "";
                        $content = "[date]

To Whom It May Concern:

This is an example letter. You can customize it any way you want.

Sincerely,
[PCP]";
                        $use_practice_logo = 1;
                        $use_practice_address = 1;
                        $location_id = "";
                        $logo_position = "";
                        $address_position = "";
                        $font = "";
        }
        else
        {
                        extract($EditItem['LetterTemplate']);
                        $id_field = '<input type="hidden" name="data[LetterTemplate][template_id]" id="template_id" value="'.$template_id.'" />';   
        }
        ?>
        
        <script language="javascript" type="text/javascript">
        $(document).ready(function()
        {
                   $("#frm").validate({errorElement: "div"});
                
                var duplicate_rules = {
                        remote: 
                        {
                                url: '<?php echo $html->url(array('action' => 'check_duplicate')); ?>',
                                type: 'post',
                                data: {
                                        'data[model]': 'LetterTemplate', 
                                        'data[template_name]': function()
                                        {
                                                return $('#template_name', $("#frm")).val();
                                        },
                                        'data[exclude]': '<?php echo $template_id; ?>'
                                }
                        },
                        messages: 
                        {
                                remote: "Duplicate value entered."
                        }
                };
                
                $("#template_name", $("#frm")).rules("add", duplicate_rules);
                
                });
        
</script>

        <div style="overflow: hidden;">
                <form id="frm" method="post" action="<?php echo $thisURL; ?>" accept-charset="utf-8" enctype="multipart/form-data">
                <input type="hidden" name="data[no_redirect]" id="no_redirect" value="false" />
                        <?php
            echo $id_field;
            ?>
             <table cellpadding="0" cellspacing="0" class="form" width=100%>

      <tr>
          <td width="250"><label>Template Name:</label></td>
          <td>
                <table border="0" cellspacing="0" cellpadding="0">
                <tr>
                    <td style="padding-left: 0px; padding-right: 5px;"><input type="text" name="data[LetterTemplate][template_name]" id="template_name" value="<?php echo $template_name; ?>" /> </td>
                    <td style="padding-right: 5px;"><label class="label_check_box" for="use_practice_logo"><input type="checkbox" name="data[LetterTemplate][use_practice_logo]" id="use_practice_logo" <?php echo ($use_practice_logo == 1?'checked':''); ?> value="1"> Use practice logo</label> </td>
                    <td style="padding-right: 5px;"><label class="label_check_box" for="use_practice_address"><input type="checkbox" name="data[LetterTemplate][use_practice_address]" id="use_practice_address" <?php echo ($use_practice_address ==1?'checked':''); ?> value="1"> Use practice address</label></td>
                    <td>
                    
                    <select  id="location_id" name="data[LetterTemplate][location_id]"  style="width: 214px;">
                        <?php foreach($locations as $current_location_id => $location_name): ?>
                        <option value="<?php echo $current_location_id; ?>" <?php if($location_id == $current_location_id) { echo 'selected'; }?>><?php echo $location_name; ?></option>
                        <?php endforeach; ?>
                        </select>
                  
                  </td>
                </tr>
            </table>    
         </td>
      </tr>
          <tr>
              <td class="top_pos">
              
                Font 
                <br />
                <select id="available-fonts" name="data[LetterTemplate][font]">
                  <option value="">Use Default</option>
                  <?php foreach (LetterTemplate::$fonts as $f):?>
                  <option value="<?php echo $f ?>" <?php if($f == $font): ?>selected="selected"<?php endif; ?>><?php echo $f; ?></option>
                  <?php endforeach;?>
                </select>
              
                <br />
                <br />
                Available Tags:
                <br />
                <select id="available-tags" name="available-tags">
                  <option value="[date]">Date</option>
                  <!--<option value="[first]">First Name</option>
                  <option value="[last]">Last Name</option>-->
                  <option value="[patient_name]">Full Patient Name</option>
                  <option value="[pcp]">PCP</option>
                </select>
                <button id="insert-tag" type="button" class="btn no-float">Insert</button>
              
              
                
                <br />
                <br />
                Available Images:
                <br />
                <select id="available-images" name="available-images">
                  <?php foreach($availableImages as $i): ?>
                  <?php
                      $displayName = explode('_', $i);
                      unset($displayName[0]);
                      $displayName = implode('_', $displayName);                  
                  
                  ?>
                  <option value="<?php echo htmlentities($i) ?>"><?php echo htmlentities($displayName); ?></option>
                  <?php endforeach;?>
                </select>
                <button id="insert-image" type="button" class="btn no-float">Insert</button>

                <br />
                <br />
                        <table cellpadding="0" cellspacing="0">
                            <tr>
                                <td>
                                    <div class="file_upload_area" style="position: relative; width: 100%; height: auto !important">
                                        <div style="position: absolute; top: 1px; right: -120px;">
                                            <div style="position: relative;" removeonread="true"> 
                                            	<a href="#" class="btn" style="float: left; margin-top: -2px;">Upload Image</a>
<div style="position: absolute; top: 0px; left: 0px;">
                                                    <input id="file_upload_image" name="file_upload_image" type="file" />
                                                </div>  
                                            </div>
                                      </div>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td style="padding-top: 10px;">
                                    <input type="hidden" name="data[PracticeProfile][logo_image]" id="logo_image_field" value="<?php echo !empty($logo_image)?$logo_image:'';?>">
                                    <input type="hidden" name="data[PracticeProfile][logo_is_uploaded]" id="logo_is_uploaded" value="false" />
                                </td>
                            </tr>
                        </table>
                                            
                                      </div> 
                                    </div>                
                
                
              </td>
                          <td style="padding-bottom: 10px;">
                            <label >Content:</label>
                            
                            <textarea rows="5" cols="20" name="data[LetterTemplate][content]" id="contenttxt" style="height: 357px; width: 839px;"><?php echo $content; ?></textarea></td>
          </tr>
          <tr>
            <td><label>Logo Position:</label></td>
            <td>
                    <select  id="logo_position" name="data[LetterTemplate][logo_position]"  style="width: 214px;">
                                <option value="" selected>Select Logo Position</option>
                        <option value="center" <?php if($logo_position =='center') { echo 'selected'; }?>>Center</option>
                    <option value="right" <?php if($logo_position =='right') { echo 'selected'; }?>>Right</option>
                                <option value="left" <?php if($logo_position == 'left') { echo 'selected'; }?>>Left</option>
                  </select>
               </td>
          </tr>
           <tr>
            <td><label>Address Position:</label></td>
            <td>
                <select  id="address_position" name="data[LetterTemplate][address_position]"  style="width: 214px;">
                <option value="" selected>Select Address Position</option>
                <option value="top" <?php if($address_position =='top') { echo 'selected'; }?>>Top</option>
                <option value="bottom" <?php if($address_position =='bottom') { echo 'selected'; }?>>Bottom</option>
                </select>
               </td>
          </tr>
          
</table>
 </form></div>
        <div class="actions">
                <ul>
                        <li removeonread="true"><a href="javascript: void(0);" onclick="$('#frm').submit();">Save</a></li>
                        <li><?php echo $html->link(__('Cancel', true), array('action' => 'letter_templates'));?></li>
            			<?php if(!isset($isiPad)||!$isiPad){ ?><li><a class = "btn" href="javascript: void(0);" id="preview_template" >Preview Template</a></li><?php } ?>
                </ul>
        </div>
        <script language="javascript" type="text/javascript">
        
        $(document).ready(function()
        {
                     <?php  if ($task == "edit" && $use_practice_address == 0) {  ?>  
                         $('#location_id').hide();
                         <?php } ?>     
            
                        $("#frm_template").validate({errorElement: "div"});
                        
             $('#iframe_close').bind('click',function()
                        {
                                $(this).hide();
                                $('.visit_summary_load').attr('src','').fadeOut(400,function()
                                {
                                                $(this).removeAttr('style');
                                });
                        });
                                                
                        $('#use_practice_address').click(function(){
                                if ($(this).is(':checked')) {
                                        $('#location_id').show();
                                }
                         
                                else {
                                        $('#location_id').hide();
                                }
                        });

                        
                        /**
             * To assign address value from practice location section to letter template section.
                         * To send variable from letter_templates to controller
                     */
                                         
                                         function generateTemplate(show)
                                         {               
                                                $.post('<?php echo $this->Session->webroot; ?>administration/letter_templates/task:letter_content/', 
                                                        $('#frm').serialize(), 
                                                        function(data)
                                                        { 
                                                                var data_link = '<?php echo $url_abs_paths['temp']; ?>'+ data.target_file+'#zoom=100&scrollbar=1&toolbar=1&navpanes=0';
                                                                
                                                                if(show)
                                                                {
                                                                        $('.visit_summary_load').fadeIn(400,function()
                                                                        {
                                                                                $('#frmFrameView').attr('src',data_link); 
                                                                                $('#letter_template_temp_form').remove();
                                                                                $('.iframe_close').show();
                                                                                
                                                                                $('#letter_template_temp_form').remove();
                                                                                
                                                                                $('.iframe_close').show();
                                                                        });
                                                                }
                                                        },
                                                   'json'
                                                ); 
                                                                        
                                                /*if(show)
                                                {
                                                        $('#no_redirect').val('true');
                                                        $.post(
                                                                $('#frm').attr("action"), 
                                                                $('#frm').serialize(), 
                                                                function(data){
                                                                        <?php if($task == 'addnew'): ?>
                                                                        $('#frm').attr("action", data.new_post_url)
                                                                        $('#frm').append('<input type="hidden" name="data[LetterTemplate][template_id]" id="template_id" value="'+data.template_id+'" />');
                                                                        <?php endif; ?>
                                                                        
                                                                        $('#no_redirect').val('false');
                                                                },
                                                                'json'
                                                        );
                                                }*/
                                         }
                         
                                $('#preview_template').click(function()
                                {
                                         generateTemplate(true);
                                                
                                 });
                         
        });
    </script>
    <div id="iframe_close" class="iframe_close"></div>
        <iframe id="frmFrameView" name="frmFrameView" class="visit_summary_load" src="" frameborder="0" ></iframe>
        <?php
}
else
{
        ?>
        <div style="overflow: hidden;">
                <form id="frm_template" method="post" action="<?php echo $thisURL.'/task:delete'; ?>" accept-charset="utf-8" enctype="multipart/form-data">
                        <table cellpadding="0" cellspacing="0" class="listing">
                        <tr>
                                <th width="15" removeonread="true">
                <label  class="label_check_box">
                <input type="checkbox" class="master_chk" />
                </label>
                </th>
                                <th><?php echo $paginator->sort('Template Name', 'template_name', array('model' => 'LetterTemplate'));?></th>
                        </tr>

                        <?php
                        $i = 0;
                        foreach ($LetterTemplate as $LetterTemplate):
                        ?>
                                <tr editlink="<?php echo $html->url(array('action' => 'letter_templates', 'task' => 'edit',  'template_id' => $LetterTemplate['LetterTemplate']['template_id']), array('escape' => false)); ?>">
                                
                                    <td class="ignore" removeonread="true">
                                    <label  class="label_check_box">
                                    <input name="data[LetterTemplate][template_id][<?php echo $LetterTemplate['LetterTemplate']['template_id']; ?>]" type="checkbox" class="child_chk" value="<?php echo $LetterTemplate['LetterTemplate']['template_id']; ?>" />
                                    </label>
                                    </td>
                                    <td><?php echo $LetterTemplate['LetterTemplate']['template_name']; ?></td>
                        
                                </tr>
                        <?php endforeach; ?>

                        </table>
                </form>
                
                <div style="width: auto; float: left;" removeonread="true">
                        <div class="actions">
                                <ul>
                                        <li><a class="ajax" href="<?php echo $html->url(array('action' => 'letter_templates', 'task' => 'addnew')); ?>">Add New</a></li>
                                        <li><a href="javascript:void(0);" onclick="deleteData();">Delete Selected</a></li>
                                </ul>
                        </div>
                </div>

                        <div class="paging">
                                <?php echo $paginator->counter(array('model' => 'LetterTemplate', 'format' => __('Display %start%-%end% of %count%', true))); ?>
                                <?php
                                        if($paginator->hasPrev('LetterTemplate') || $paginator->hasNext('LetterTemplate'))
                                        {
                                                echo '  &mdash;  ';
                                        }
                                ?>
                                <?php 
                                        if($paginator->hasPrev('LetterTemplate'))
                                        {
                                                echo $paginator->prev('<< Previous', array('model' => 'LetterTemplate', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
                                        }
                                ?>
                                <?php echo $paginator->numbers(array('model' => 'LetterTemplate', 'modulus' => 5, 'first' => 2, 'last' => 2, 'separator' => '  ')); ?>
                                <?php 
                                        if($paginator->hasNext('LetterTemplate'))
                                        {
                                                echo $paginator->next('Next >>', array('model' => 'LetterTemplate', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
                                        }
                                ?>
                        </div>
        </div>
        <?php
}
?>
        </div>
</div>
<?php echo $this->element("enable_acl_read", array('page_access' => $page_access)); ?>
