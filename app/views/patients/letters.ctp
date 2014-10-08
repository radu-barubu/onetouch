<?php

$task = (isset($this->params['named']['task'])) ? $this->params['named']['task'] : "";
$thisURL = $this->Session->webroot . $this->params['url']['url'];
$patient_id = (isset($this->params['named']['patient_id'])) ? $this->params['named']['patient_id'] : "";
$addURL = $html->url(array('patient_id' => $patient_id, 'task' => 'addnew')) . '/';
$deleteURL = $html->url(array('task' => 'delete', 'patient_id' => $patient_id)) . '/';
$mainURL = $html->url(array('patient_id' => $patient_id)) . '/';

$added_message = "Item(s) added.";
$edit_message = "Item(s) saved.";
$current_message = ($task == 'addnew') ? $added_message : $edit_message;
$letter_id = (isset($this->params['named']['letter_id'])) ? $this->params['named']['letter_id'] : "";

$page_access = $this->QuickAcl->getAccessType('patients', 'attachments');
echo $this->element("enable_acl_read", array('page_access' => $page_access));



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

?>

<script language="javascript" type="text/javascript">
  var adminUrl = '<?php echo $adminUrl; ?>';

function preview_template()
{
    
        <?php if (isset($isMobile) && $isMobile): ?> 
                $.post('<?php echo $this->Session->webroot; ?>patients/letters/task:letter_content/', 
                        $('#frmPatientLetters').serialize(), 
                        function(data)
                        { 
                                var link_data = data.target_file;
                                var data_link = '<?php echo $url_abs_paths['temp']; ?>'+ link_data+'#zoom=100&scrollbar=1&toolbar=1&navpanes=0';
                                
                                window.location.href = data_link;
                        },
                   'json'
                );

        <?php else:?>
        $('.visit_summary_load').fadeIn(400,function()
        {
                $.post('<?php echo $this->Session->webroot; ?>patients/letters/task:letter_content/', 
                        $('#frmPatientLetters').serialize(), 
                        function(data)
                        { 
                                var link_data = data.target_file;
                                var data_link = '<?php echo $url_abs_paths['temp']; ?>'+ link_data+'#zoom=100&scrollbar=1&toolbar=1&navpanes=0';
                   
                                $('#frmFrameView').attr('src',data_link); 
                                $('#letters_temp_form').remove();
                                $('.iframe_close').show();
                        },
                   'json'
                );

                $('#letters_temp_form').remove();
                $('.iframe_close').show();
        });     
        <?php endif;?> 

}

$(document).ready(function()
{    
    $("#frmPatientLettersGrid").validate({errorElement: "div"});
                        
        $('#iframe_close').bind('click',function()
        {
                        $(this).hide();
                        $('.visit_summary_load').attr('src','').fadeOut(400,function()
                        {
                                        $(this).removeAttr('style');
                        });
        });
        
        $('#template_id').change(function()
        {
                var patient_name = $('#patient_name').val();
                var pcp = $('#pcp').val();
                var date_performed = "<?php echo  __date($global_date_format); ?>";
                
                //Passing values via post method to controller
                $.post('<?php echo $this->Session->webroot; ?>patients/letters/task:get_content/', 
                        {'data[template_id]': $('#template_id').val()}, 
                        function(data)
                        {
																var content_name = data.content.replace(/\[patient_name\]/i, patient_name);
																var content_name_date = content_name.replace(/\[date\]/i, date_performed);
																var content = content_name_date.replace(/\[pcp\]/i, pcp);
                                
                                /*content = content.replace(/\[image\:([0-9a-zA-Z_.]*)]/gi, function(match, img){

                                  return '<img src="'+adminUrl + img +'" />';
                                });                                
                                */
                                
                                
                                $('#contenttxt').val(content);
                        },
                        'json'
                );
         });
         
         
        /*$('#preview_template').click(function()
        {
                $('#preview_mode').val('true');
                $("#frmPatientLetters").submit();
        });*/
   
        initCurrentTabEvents('patient_letter_area');
        
    $("#frmPatientLetters").validate(
    {
        errorElement: "div",
                errorPlacement: function(error, element) 
                {
                        //$('#preview_mode').val('false');
                        
                        if(element.attr("id") == "status_open")
                        {
                                $("#status_error").append(error);
                        }
                        else
                        {
                                error.insertAfter(element);
                        }
                },
        submitHandler: function(form) 
        {
            $('#frmPatientLetters').css("cursor", "wait");
            
            $.post(
                '<?php echo $thisURL; ?>', 
                $('#frmPatientLetters').serialize(), 
                function(data)
                {
                    showInfo("<?php echo $current_message; ?>", "notice");
                                        loadTab($('#frmPatientLetters'), '<?php echo $mainURL; ?>');
                },
                'json'
            );
        }
    });
        
        <?php if($task == 'addnew' || $task == 'edit'): ?>
        var duplicate_rules = {
                remote: 
                {
                        url: '<?php echo $html->url(array('action' => 'check_duplicate')); ?>',
                        type: 'post',
                        data: {
                                'data[model]': 'PatientLetter', 
                                'data[patient_id]': <?php echo $patient_id; ?>, 
                                'data[patient_name]': function()
                                {
                                        return $('#patient_name', $("#frmPatientLetters")).val();
                                },
                                /*'data[subject]': function()
                                {
                                        return $('#subject', $("#frmPatientLetters")).val();
                                },*/
                                'data[exclude]': '<?php echo $letter_id; ?>'
                        }
                },
                messages: 
                {
                        remote: "Duplicate value entered."
                }
        }
        
        $("#patient_name", $("#frmPatientLetters")).rules("add", duplicate_rules);
        //$("#subject", $("#frmPatientLetters")).rules("add", duplicate_rules);
        <?php endif; ?>
        
         });
        

</script>
<div id="patient_letter_area" class="tab_area">
        <?php
    if($task == "addnew" || $task == "edit")
    {
                if($task == "addnew")
                {
                        $date_performed = __date($global_date_format,strtotime("now"));
                        $subject = "";
                        $content = "";
                        $template_id = "";
                        $by = $session->read("UserAccount.firstname") . ' ' . $session->read("UserAccount.lastname");
                        $patient_name = $PatientDemo['PatientDemographic']['first_name']." ".$PatientDemo['PatientDemographic']['last_name'];           
                        $id_field = "";
                        $pcp_text =  $user;
                }
                else
                {
                        extract($EditItem['PatientLetter']);
                        $id_field = '<input type="hidden" name="data[PatientLetter][letter_id]" id="letter_id" value="'.$letter_id.'" />';
                        $date_performed = __date($global_date_format, strtotime($date_performed));
                        $pcp_text =  $user;
                }
        ?>
                <form id="frmPatientLetters" method="post" action="<?php echo $thisURL; ?>" accept-charset="utf-8" enctype="multipart/form-data">
        <!--<input type="hidden" name="data[preview_mode]" id="preview_mode" value="false" />-->
                <? echo $id_field; ?>
                <table cellpadding="0" cellspacing="0" class="form" width="100%">
                        <tr height="35">
                                <td width="130"><label>Patient:</label></td>
                                <td><?php echo $patient_name; ?>
                                <input type="hidden" name="data[PatientLetter][patient_name]" id="patient_name" value="<?php echo $patient_name; ?>" /></td>
                        </tr>
                        <tr>
            <td width="150"><label>Template:</label></td>
            
            <td>
                <select name="data[PatientLetter][template_id]" id="template_id" class="required">
                <option value="" selected>Select Template</option>
                 <?php foreach($types as $current_template_id => $template_name): ?>
                    <option value="<?php echo $current_template_id; ?>" <?php if($template_id==$current_template_id) { echo 'selected'; }?>><?php echo $template_name; ?></option>
                    <?php endforeach; ?>
                </select>
            </td>
            <input type="hidden" id="pcp" value="<?php echo $pcp_text; ?>" />
            </tr>                       
                        <tr>
                                <td style="vertical-align: top;"><label>Subject:</label></td>
                                <td><input type="text" name="data[PatientLetter][subject]" id="subject" style="width:984px;" value="<?php echo $subject; ?>" class="required" /></td>
                        </tr>                   
                        <tr>
                                <td style="vertical-align: top;"><label >Content:</label></td>
                                <td style="padding-bottom: 10px;"><textarea rows="5" cols="20" name="data[PatientLetter][content]" id="contenttxt" style="height: 357px; width: 839px;"><?php echo $content; ?></textarea></td>
                        </tr>
                        <tr>
                                <td style="vertical-align: top;"><label>Date Performed:</label></td>
                                <td><?php echo $this->element("date", array('name' => 'data[PatientLetter][date_performed]', 'id' => 'date_performed', 'value' => $date_performed, 'required' => false)); ?></td>
                        </tr>
                        <tr>
                                <td width="130"><label>By:</label></td>
                                <td><input type="text" name="data[PatientLetter][by]" id="by" value="<?php echo $by; ?>" /></td>
                        </tr>
                                        
                </table></form>
                <div class="actions">
                        <ul>
                                <?php if($page_access == 'W'): ?><li removeonread="true"><a href="javascript: void(0);" onclick="$('#frmPatientLetters').submit();">Save</a></li><?php endif; ?>
                                <li><a class="ajax" href="<?php echo $mainURL; ?>">Cancel</a></li>
                                <li><a class="btn" href="javascript: void(0);" id="preview_template"  onclick="preview_template();">Preview Letter</a></li>
                        </ul>
                </div>
<?php
    }
    else
    {
        ?>
        <form id="frmPatientLettersGrid" method="post" accept-charset="utf-8">
            <table cellpadding="0" cellspacing="0" class="listing" border=1>
            <tr>
                <?php if($page_access == 'W'): ?><th width="3%"><label for="master_chk_letters" class="label_check_box_hx"><input type="checkbox" id="master_chk_letters" class="master_chk" /></label></th><?php endif; ?>
                                <th width="25%"><?php echo $paginator->sort('Subject', 'subject', array('model' => 'PatientLetter', 'class' => 'ajax'));?></th>
                <th width="32%"><?php echo $paginator->sort('Template', 'template_id', array('model' => 'PatientLetter', 'class' => 'ajax'));?></th>
                <th width="22%"><?php echo $paginator->sort('By', 'by', array('model' => 'PatientLetter', 'class' => 'ajax'));?></th>                           
                <th width="18%"><?php echo $paginator->sort('Date Performed', 'date_performed', array('model' => 'PatientLetter', 'class' => 'ajax'));?></th>
            </tr>
            <?php
            $i = 0;
            foreach ($patient_letters as $patient_letter):
            foreach ($types as $patient_template_id => $patient_template_name):
                        //checking condition if both template id are same proceed
                        if($patient_letter['PatientLetter']['template_id'] == $patient_template_id):
            ?>
                <tr editlinkajax="<?php echo $html->url(array('action' => 'letters', 'task' => 'edit', 'patient_id' => $patient_id, 'letter_id' => $patient_letter['PatientLetter']['letter_id'])); ?>">
                    <?php if($page_access == 'W'): ?><td class="ignore"><label for="child_chk<?php echo $patient_letter['PatientLetter']['letter_id']; ?>" class="label_check_box_hx"><input name="data[PatientLetter][letter_id][<?php echo $patient_letter['PatientLetter']['letter_id']; ?>]" id="child_chk<?php echo $patient_letter['PatientLetter']['letter_id']; ?>" type="checkbox" class="child_chk" value="<?php echo $patient_letter['PatientLetter']['letter_id']; ?>" /></td><?php endif; ?>
                    <td><?php echo $patient_letter['PatientLetter']['subject']; ?></td>
                    <td><?php echo $patient_template_name; ?></td>
                    <td><?php echo $patient_letter['PatientLetter']['by']; ?></td>                                      
                    <td><?php echo __date($global_date_format, strtotime($patient_letter['PatientLetter']['date_performed'])); ?></td>
                                        
                </tr>
                <?php endif; ?>
            <?php endforeach; ?> 
            <?php endforeach; ?>
                        
            </table>
        </form>
        
        <?php if($page_access == 'W'): ?>
        <div style="width: auto; float: left;">
            <div class="actions">
                <ul>
                                        
                                        <?php if (isset($types) && $types): ?> 
                    <li><a class="ajax" href="<?php echo $addURL; ?>">Add New</a></li>
                    <?php endif;?> 
                    <li><a href="javascript:void(0);" onclick="deleteData('frmPatientLettersGrid', '<?php echo $deleteURL; ?>');">Delete Selected</a></li>
                </ul>
            </div>
        </div>
        <?php endif; ?>
            <div class="paging">
                <?php echo $paginator->counter(array('model' => 'PatientLetter', 'format' => __('Display %start%-%end% of %count%', true))); ?>
                <?php
                    if($paginator->hasPrev('PatientLetter') || $paginator->hasNext('PatientLetter'))
                    {
                        echo '  &mdash;  ';
                    }
                ?>
                <?php 
                    if($paginator->hasPrev('PatientLetter'))
                    {
                        echo $paginator->prev('<< Previous', array('model' => 'PatientLetter', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
                    }
                ?>
                <?php echo $paginator->numbers(array('model' => 'PatientLetter', 'modulus' => 5, 'first' => 2, 'last' => 2, 'separator' => '&nbsp;&nbsp;')); ?>
                <?php 
                    if($paginator->hasNext('Demo'))
                    {
                        echo $paginator->next('Next >>', array('model' => 'PatientLetter', 'url' => $paginator->params['pass']), null, array('class'=>'disabled')); 
                    }
                ?>
            </div>

        <?php
    }
    ?>
</div>
<?php echo $this->element("enable_acl_read", array('page_access' => $page_access)); ?>