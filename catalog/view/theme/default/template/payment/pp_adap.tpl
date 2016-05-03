<h2><?php echo $entry_form_onlus_title; ?></h2>
<div class="content" id="payment">

	<?php if(isset($onlus) && !empty($onlus)){ ?>
	<table class="form" id="pp_adap_complete">
		<tr>
			<td colspan="3"><?php echo sprintf($entry_choose_onlus); ?></td>
			<td>
				<select name="onlus_id">
				<?php foreach($onlus as $o): ?>
				<option value="<?php echo $o['onlus_id'];?>"><?php echo $o['name']; ?></option>
				<?php endforeach; ?>
				</select>
			</td>
		</tr>
	</table>
	<?php } ?>
</div>
<div class="buttons">
  <div class="right">
    <input type="button" value="<?php echo $button_confirm; ?>" id="button-confirm" class="button" />
  </div>
</div>
<script type="text/javascript">
$('#button-confirm').bind('click', function() {
	$.ajax({
		url: 'index.php?route=payment/pp_adap/setExpressCheckout',
		type: 'post',
		data: $('#payment :input'),
		dataType: 'json',		
		beforeSend: function() {
			$('#button-confirm').attr('disabled', true);
			$('#payment').before('<div class="attention"><img src="catalog/view/theme/default/image/loading.gif" alt="" /> <?php echo $text_wait; ?></div>');
		},
		complete: function(data) {
			$('#button-confirm').attr('disabled', false);
			$('.attention').remove();
// 			console.log(jQuery.parseJSON(data.responseText));
		},
		error: function(data){
			var reason = data.responseText.array;
// 			var response = JSON.stringify(jQuery.parseJSON(data.responseText),null,2);
			var response = jQuery.parseJSON(data.responseText);
			for(var i in response){
				for(var key in response[i]){
					if(key=='L_LONGMESSAGE0'){
						var reason = response[i][key];
					}
				}
			}
			$('#pp_adap_complete').append('<tr><td colspan="4" style="color:red"><div>Error:</div><div><pre>'+reason+'</pre></div></td></tr>');
		},
		success: function(json) {
			console.log(json);
			window.location.replace(json.url);
// 			if (json['error']) {
// 				alert(json['error']);
// 			}
// 
// 			if (json['success']) {
// 				location = json['success'];
// 			}
		}
	});
});
</script> 
