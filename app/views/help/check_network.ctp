<?php

echo $this->Html->script('boomerang/boomerang.js?'.time());

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
?>
<style type="text/css">
div#results {
    background-color: #FFFFAA;
    border: 2px solid #DD8822;
    padding: 1em;
		width: 90%;
}	

dl dd {
	margin-left: 2em;
}

dl dt {
	font-style:  italic;
}
</style>
<div style="overflow: hidden;">
	<h1>Network Test</h1>
	<div id="results">
		Running test, please wait...
	</div>	
	
	<br />
	<br />
	<div>
		<p><strong>Notes</strong></p>
		<dl>
			<dt>boomerang</dt>
			<dd>The time it took boomerang to load up from first byte to last byte</dd>
			
			<dt>boomr_fb</dt>
			<dd>The time it took from the start of page load to the first byte of boomerang</dd>
			
			<dt>t_domloaded</dt>
			<dd>Time taken to execute DOM Content Loaded</dd>
			

			<dt>t_resp</dt>
			<dd>Time taken from the user initiating the request to the first byte of the response</dd>
			
			<dt>t_page</dt>
			<dd>Time taken from the head of the page to page_ready</dd>
			
			
			<dt>rt_start</dt>
			<dd>
				Specifies where the start time came from. May be one of cookie for the start cookie, navigation for the W3C navigation timing API, csi for older versions of Chrome or gtb for the Google Toolbar.			
			</dd>

		<dt>rt.bstart</dt>
			<dd>The timestamp when boomerang showed up on the page</dd>
			<dt>rt.end</dt>
			<dd>The timestamp when the done() method was called</dd>			
			
		</dl>

	</div>
</div>
<script>
	// Since we don't set a beacon_url, we'll just subscribe to the before_beacon function
	// and print the results into the browser itself.
	BOOMR.subscribe('before_beacon', function(o) {
		var html = "", t_name, t_other, others = [];

		if(!o.t_other) o.t_other = "";

		for(var k in o) {
			if(!k.match(/^(t_done|t_other|bw|lat|bw_err|lat_err|u|r2?)$/)) {
				if(k.match(/^t_/)) {
					o.t_other += "," + k + "|" + o[k];
				}
				else {
					others.push(k + " = " + o[k]);
				}
			}
		}

		if(o.t_done) { html += "This page took " + o.t_done + " ms to load<br>"; }
		if(o.t_other) {
			t_other = o.t_other.replace(/^,/, '').replace(/\|/g, ' = ').split(',');
			html += "Other timers measured: <br>";
			for(var i=0; i<t_other.length; i++) {
				html += "&nbsp;&nbsp;&nbsp;" + t_other[i] + " ms<br>";
			}
		}
		if(o.bw) { html += "Your bandwidth to this server is " + parseInt(o.bw*8/1024) + "kbps (&#x00b1;" + parseInt(o.bw_err*100/o.bw) + "%)<br>"; }
		if(o.lat) { html += "Your latency to this server is " + parseInt(o.lat) + "&#x00b1;" + o.lat_err + "ms<br>"; }

		var r = document.getElementById('results');
		r.innerHTML = html;

		if(others.length) {
			r.innerHTML += "Other parameters:<br>";

			for(var i=0; i<others.length; i++) {
				var t = document.createTextNode(others[i]);
				r.innerHTML += "&nbsp;&nbsp;&nbsp;";
				r.appendChild(t);
				r.innerHTML += "<br>";

			}
		}

	});	
	
   BOOMR.init({
		BW: {
			base_url: '<?php echo $this->Html->url('/js/boomerang/images/'); ?>',
			cookie: 'HOWTO-BA'
		}		 
		 
	 });
</script>
