<?php 

$thisURL = $this->Session->webroot . $this->params['url']['url'];

?>
<div style="overflow: hidden;">
	<div class="title_area">
         <div class="title_text">
            <div class="title_item active">Upgrade</div>
         </div>
    </div>
    <form id="frm" method="post" action="<?php echo $thisURL; ?>" accept-charset="utf-8" enctype="multipart/form-data">
    	<input type="hidden" name="data[anything]" value="1" />
    	<table cellpadding="0" cellspacing="0" class="form" width=100%>
        	<tr>
				<td colspan="2">This action will upgrade the system with the lastest changes.</td>
			</tr>
            <tr>
                <td colspan="2" style="color:#F00;">&nbsp;</td>
            </tr>
            <tr>
				<td colspan="2" style="color:#F00;">Please back up your database before applying this upgrade.</td>
			</tr>
        </table>
    </form>
    <div class="actions">
		<ul>
			<li><a href="javascript: void(0);" onclick="$('#frm').submit();">Upgrade</a></li>
		</ul>
	</div>
</div>