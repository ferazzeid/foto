<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="wrap">
<?php
if(current_user_can('manage_options')):


if(isset($_REQUEST['sidebardel']) && $_REQUEST['sidebardel']!=''){
	if(get_option( 'ri_widget_area_builder_id' )){
		$stky_option = unserialize(get_option( 'ri_widget_area_builder_id' ));
	}
	$key = array_search($_REQUEST['sidebardel'],$stky_option);
	print_r($stky_option);
	if($key!==false){
		if(delete_option( 'ri_widget_area_builder_'.$_REQUEST['sidebardel'] )){
			unset($stky_option[$key]);
		}else{ unset($stky_option[$key]); }
		if(update_option( 'ri_widget_area_builder_id', serialize($stky_option) )){  }
	}
	
}

$optset = 0; $stky_option = array();

if(isset($_POST['_ricsmnonce']) && wp_verify_nonce( $_POST['_ricsmnonce'], 'ricsm-nonce' )){
	if(isset($_POST['ssn'])){
		$sw_data = $_POST['indx'];
		if(get_option( 'ri_widget_area_builder_id' )){
			$stky_option = unserialize(get_option( 'ri_widget_area_builder_id' ));
			$optset = 1;
		}
		if(!array_search($sw_data, $stky_option, true)){ $stky_option[] = $sw_data; }
		
		$ssbdet = array();
		$ssbdet[name] = $_POST['ssn'];
		$ssbdet[des] = $_POST['ssd'];
		
		if(!get_option( 'ri_widget_area_builder_'.$sw_data )){ 
			if(add_option( 'ri_widget_area_builder_'.$sw_data , serialize($ssbdet) )){  } 
		}else{  
			
		}
		update_option( 'ri_widget_area_builder_id', serialize($stky_option) );
	}else{  }
}

if(get_option( 'ri_widget_area_builder_id' )){
	$stky_option = unserialize(get_option( 'ri_widget_area_builder_id' ));
	$optset = 1;
}
else{ if(add_option( 'ri_widget_area_builder_id' )){  } }


?>


	<table class="manage-social wp-list-table widefat fixed striped pages">
    <thead>
    	<tr><td class="column-date"> S. No. </td> <td> Sidebar name </td> <td> Description </td> <td>Shortcode</td><td>Remove</td></tr>
    </thead>
    <?php if($stky_option): $i=1;
		foreach($stky_option as $id=>$val):
		if(get_option( 'ri_widget_area_builder_'.$val )){ $snmd = unserialize(get_option( 'ri_widget_area_builder_'.$val )); }
			?>
            <tr> 
            	<td class="column-date"> <label><?php echo $i; ?></label> </td>
                <td><label><?php echo $snmd[name]; ?></label> </td>
                <td><?php echo $snmd[des]; ?></td> 
                <td><label><?php echo '[ri_custom_sidebar '.$val.']'; ?></label></td>
                <td><a href="admin.php?page=sidebar-builder/main.php&sidebardel=<?php echo $val; ?>">Remove</a></td>
            </tr>
            <?php $i++;
		endforeach;
		
		else: ?>
		<tr><td colspan="4">No sticky sidebar available</td></tr>
  <?php endif;
	?>
    <tr> <td colspan="4"></td></tr>
    </table>

<?php $val = 0;
if($stky_option){ $val = max( $stky_option ); } ?>
<h2>Add new sidebar</h2>
<form method="post"><input type="hidden" name="_ricsmnonce" value="<?php echo wp_create_nonce( 'ricsm-nonce' ); ?>" />
	<p><input type="text" placeholder="Sidebar Name" name="ssn" />
    	<input type="hidden" name="indx" value="<?php echo $val+1; ?>" />
    </p>
    <p><textarea name="ssd" placeholder="Description"></textarea></p>
    <p><input type="submit" name="ss" value="Save" /></p>
</form>

<?php
endif;	
?>
</div>