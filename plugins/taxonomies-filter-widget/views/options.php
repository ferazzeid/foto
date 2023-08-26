<!-- This file is used to markup the administration form of the plugin. -->

<div class="wrap">
    <?php screen_icon(); ?>
    <h2>Taxonomies Filter Widget</h2>			
    <form method="post" action="options.php">
        <?php
        // This prints out all hidden setting fields
	    settings_fields('tfw_options_group');	
	    do_settings_sections('tfw_options_page');
	?>
        <?php submit_button(); ?>
    </form>
</div>
<?php
