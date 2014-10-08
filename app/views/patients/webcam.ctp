<?php

$url_port = ($_SERVER['SERVER_PORT'] == '80') ? '' : ':'.$_SERVER['SERVER_PORT'];
$url_pre = (!empty($_SERVER['HTTPS'])) ? "https://".$_SERVER['SERVER_NAME'].$url_port : "http://".$_SERVER['SERVER_NAME'].$url_port;

?>
<script language="javascript" type="text/javascript">
	function webcam_callback(data)
	{
		parent.updateWebcamPhoto(data);
	}
</script>
<div id="flashArea" class="flashArea" style="height:370; margin: 0px; padding: 0px;"></div>
<script type="text/javascript">
var flashvars = {
  save_file: "<?php echo $url_pre . $html->url(array('controller' => 'patients', 'action' => 'webcam_save')); ?>",
  parentFunction: "webcam_callback",
  snap_sound: "<?php echo $this->Session->webroot; ?>sound/camera_sound.mp3",
  save_sound: "<?php echo $this->Session->webroot; ?>sound/save_sound.mp3"
};
var params = {
  scale: "noscale",
  wmode: "window",
  allowFullScreen: "true"
};
var attributes = {};
swfobject.embedSWF("<?php echo $this->Session->webroot; ?>swf/webcam.swf", "flashArea", "700", "370", "9.0.0", "<?php echo $this->Session->webroot; ?>swf/expressInstall.swf", flashvars, params, attributes);
</script>
<object width="0" height="0" type="application/x-shockwave-flash">
<p>Please <a href="http://get.adobe.com/flashplayer/" target=_blank>install Adobe Flash</a> to use this Web Cam feature</p>
</object>
