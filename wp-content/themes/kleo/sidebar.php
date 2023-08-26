<?php
/**
 * The Sidebar containing the main widget area
 *
 * @package WordPress
 * @subpackage Kleo
 * @since Kleo 1.0
 */
?>
<?php
$sidebar_classes = apply_filters('kleo_sidebar_classes', '');
$sidebar_name = apply_filters('kleo_sidebar_name', '0');

if(is_page_template( 'page-templates/col3-left-right-sidebar.php' )) { ?>

	<div class="sidebar sidebar-main <?php echo $sidebar_classes; ?>">
		<div class="inner-content widgets-container">
			<?php generated_dynamic_sidebar('cs-1');?>
		</div><!--end inner-content-->
	</div><!--end sidebar-->
	
<?php } else { ?>

	<div class="sidebar sidebar-main <?php echo $sidebar_classes; ?>">
		<div class="inner-content widgets-container">
			<?php generated_dynamic_sidebar($sidebar_name);?>
		</div><!--end inner-content-->
	</div><!--end sidebar-->
	
<?php } ?>
