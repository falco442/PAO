<?php echo $header; ?>
<div id="content">
	<div class="breadcrumb">
		<?php foreach ($breadcrumbs as $breadcrumb) { ?>
		<?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
		<?php } ?>
	</div>
  <?php if ($error_warning) { ?>
  <div class="warning"><?php echo $error_warning; ?></div>
  <?php } ?>
	<div class="box">
		<div class="heading">
		<h1><img src="view/image/payment.png" alt="" /> <?php echo $heading_title; ?></h1>
		<div class="buttons"><a onclick="$('#form').submit();" class="button"><?php echo $button_save; ?></a><a href="<?php echo $cancel; ?>" class="button"><?php echo $button_cancel; ?></a></div>
		</div>
		<div class="content">
		<form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form">
			<table class="form">
			<tr>
				<td><span class="required">*</span> <?php echo $entry_username; ?></td>
				<td><input type="text" name="pp_adap_username" value="<?php echo $pp_adap_username; ?>" />
				<?php if ($error_username) { ?>
				<span class="error"><?php echo $error_username; ?></span>
				<?php } ?></td>
			</tr>
			<tr>
				<td><span class="required">*</span> <?php echo $entry_password; ?></td>
				<td><input type="text" name="pp_adap_password" value="<?php echo $pp_adap_password; ?>" />
				<?php if ($error_password) { ?>
				<span class="error"><?php echo $error_password; ?></span>
				<?php } ?></td>
			</tr>
			<tr>
				<td><span class="required">*</span> <?php echo $entry_signature; ?></td>
				<td><input type="text" name="pp_adap_signature" value="<?php echo $pp_adap_signature; ?>" />
				<?php if ($error_signature) { ?>
				<span class="error"><?php echo $error_signature; ?></span>
				<?php } ?></td>
			</tr>
			<tr>
				<td><?php echo $entry_test; ?></td>
				<td><?php if ($pp_adap_test) { ?>
				<input type="radio" name="pp_adap_test" value="1" checked="checked" />
				<?php echo $text_yes; ?>
				<input type="radio" name="pp_adap_test" value="0" />
				<?php echo $text_no; ?>
				<?php } else { ?>
				<input type="radio" name="pp_adap_test" value="1" />
				<?php echo $text_yes; ?>
				<input type="radio" name="pp_adap_test" value="0" checked="checked" />
				<?php echo $text_no; ?>
				<?php } ?></td>
			</tr>
			<tr>
				<td><?php echo $entry_transaction; ?></td>
				<td><select name="pp_adap_transaction">
					<?php if (!$pp_adap_transaction) { ?>
					<option value="0" selected="selected"><?php echo $text_authorization; ?></option>
					<?php } else { ?>
					<option value="0"><?php echo $text_authorization; ?></option>
					<?php } ?>
					<?php if ($pp_adap_transaction) { ?>
					<option value="1" selected="selected"><?php echo $text_sale; ?></option>
					<?php } else { ?>
					<option value="1"><?php echo $text_sale; ?></option>
					<?php } ?>
				</select></td>
			</tr>
			<tr>
				<td><?php echo $entry_total; ?></td>
				<td><input type="text" name="pp_adap_total" value="<?php echo $pp_adap_total; ?>" /></td>
			</tr>          
			<tr>
				<td><?php echo $entry_order_status; ?></td>
				<td><select name="pp_adap_order_status_id">
					<?php foreach ($order_statuses as $order_status) { ?>
					<?php if ($order_status['order_status_id'] == $pp_adap_order_status_id) { ?>
					<option value="<?php echo $order_status['order_status_id']; ?>" selected="selected"><?php echo $order_status['name']; ?></option>
					<?php } else { ?>
					<option value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
					<?php } ?>
					<?php } ?>
				</select></td>
			</tr>
			<tr>
				<td><?php echo $entry_geo_zone; ?></td>
				<td><select name="pp_adap_geo_zone_id">
					<option value="0"><?php echo $text_all_zones; ?></option>
					<?php foreach ($geo_zones as $geo_zone) { ?>
					<?php if ($geo_zone['geo_zone_id'] == $pp_adap_geo_zone_id) { ?>
					<option value="<?php echo $geo_zone['geo_zone_id']; ?>" selected="selected"><?php echo $geo_zone['name']; ?></option>
					<?php } else { ?>
					<option value="<?php echo $geo_zone['geo_zone_id']; ?>"><?php echo $geo_zone['name']; ?></option>
					<?php } ?>
					<?php } ?>
				</select></td>
			</tr>
			<tr>
				<td><?php echo $entry_status; ?></td>
				<td><select name="pp_adap_status">
					<?php if ($pp_adap_status) { ?>
					<option value="1" selected="selected"><?php echo $text_enabled; ?></option>
					<option value="0"><?php echo $text_disabled; ?></option>
					<?php } else { ?>
					<option value="1"><?php echo $text_enabled; ?></option>
					<option value="0" selected="selected"><?php echo $text_disabled; ?></option>
					<?php } ?>
				</select></td>
			</tr>
			<tr>
				<td><?php echo $entry_sort_order; ?></td>
				<td><input type="text" name="pp_adap_sort_order" value="<?php echo $pp_adap_sort_order; ?>" size="1" /></td>
			</tr>
			
			<tr>
				<td><span class="required">*</span> <?php echo $entry_onlus_amount; ?></td>
				<td><input type="text" name="pp_adap_onlus_amount" value="<?php echo $pp_adap_onlus_amount; ?>" />
				<?php if ($error_onlus_amount) { ?>
				<span class="error"><?php echo $error_onlus_amount; ?></span>
				<?php } ?></td>
			</tr>
			
			
			</table>
			
			<table class="list" id="onlus">
				<thead>
					<tr>
						<td class="left"><?php echo $text_onlus; ?></td>
						<td class="left"><?php echo $text_onlus_paypal_id; ?></td>
						<td class="center"><?php echo $text_onlus_add; ?></td>
					</tr>
				</thead>
				
				<tbody>
					<tr class="filter"></tr>
				<?php foreach($onlus as $o): ?>
					<tr>
						<td class="left"><?php echo $o['name']; ?></td>
						<td class="left"><?php echo $o['paypal_id']; ?></td>
						<td class="center">
							<a class="button add-onlus" onclick="appendToTable();">Add</a>
							<a class="button remove-onlus" onclick="deleteOnlus(<?php echo $o['onlus_id'] ?>);">Remove</a>
						</td>
					</tr>
				<?php endforeach; ?>
					<tr>
						<td class="left"><input type="text" name="onlus[0][name]"></td>
						<td class="left"><input type="text" name="onlus[0][paypal_id]"></td>
						<td class="center"><a class="button add-onlus" onclick="appendToTable();">Add</a></td>
					</tr>
				</tbody>
			</table>
			
		</form>
		</div>
	</div>
</div>

<script type="text/javascript">
	var new_row = 1;

	function appendToTable(button){
		var tbody = $('#onlus>tbody');
		html = '<tr>';
		html += '<td class="left"><input type="text" name="onlus['+new_row+'][name]"></td>';
		html += '<td class="left"><input type="text" name="onlus['+new_row+'][paypal_id]"></td>';
		html += '<td class="center">';
		html += '<a class="button add-onlus" onclick="appendToTable();">Add</a>';
		html += '<a class="button" onclick="removeFromTable($(this))">Remove</a>';
		html += '</td>';
		html += '</tr>';
		tbody.append(html);
		new_row++;
	}
	
	function removeFromTable(el){
		var row = el.parent().parent();
		row.remove();
	}
	
	function deleteOnlus(id){
		$.ajax({
			url: "index.php?route=payment/pp_adap&token=<?php echo $token; ?>",
			method: 'POST',
			data: {
				id: id
			}
		});
	}
</script>

<?php echo $footer; ?>