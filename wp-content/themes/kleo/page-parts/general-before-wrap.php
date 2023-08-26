<?php
/**
 * Before content wrap
 * Used in all templates
 */
?>
<?php
$main_tpl_classes = apply_filters('kleo_main_template_classes', '');

if (kleo_has_shortcode('kleo_bp_')) {
	$section_id = 'id="buddypress" ';
}	else {
	$section_id = '';
}

$container = apply_filters('kleo_main_container_class','container');

/**
 * Before main content - action
 */
do_action('kleo_before_content');

?>

<section class="container-wrap main-color">
	<div id="main-container" class="<?php echo $container; ?>">
		<?php if($container == 'container') { ?><div class="row"> <?php } ?>

			<div <?php echo $section_id;?>class="template-page <?php echo $main_tpl_classes; ?>">
				<div class="wrap-content">
				
				<?php if(is_archive() || is_search()) { 
				
				echo '<div class="kleo-search-wrap kleo-search-form search-style-archive">';
					get_search_form( 'true' );
					$class = '';
					$post_types = get_post_types();
					if(get_post_type() == 'kalender') {
						$sidebar_no = 7;
					} else if(get_post_type() == 'fotobuch') {
						$sidebar_no = 8;
					} else if(get_post_type() == 'fotokarte') {
						$class = " archive-sub";
						$sidebar_no = 9;
					} else if(get_post_type() == 'fotogeschenk') {
						$sidebar_no = 10;
					} else if(get_post_type() == 'fotoabzug') {
						$sidebar_no = 11;
					} else if(get_post_type() == 'wanddeko') {
						$sidebar_no = 12;
					}
					if ( ($sidebar_no>0) && is_active_sidebar( 'cs-'.$sidebar_no ) ) : 
						?>
						<div class="kleo-archive-filters<?php echo $class; ?>">
							<?php 
							if(!is_search()) { dynamic_sidebar( 'cs-'.$sidebar_no ); } ?>
						</div>
						<?php 
					endif;
				echo '</div>';
				 
				} else {  } ?>
					
				<?php
				/**
				 * Before main content - action
				 */
				do_action('kleo_before_main_content');
				?>
