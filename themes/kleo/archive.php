<?php

/**

 * The template for displaying Archive pages

 *

 * Used to display archive-type pages if nothing more specific matches a query.

 * For example, puts together date-based pages if no date.php file exists.

 *

 * If you'd like to further customize these archive views, you may create a

 * new template file for each specific one. For example, Twenty Fourteen

 * already has tag.php for Tag archives, category.php for Category archives,

 * and author.php for Author archives.

 *

 * @link http://codex.wordpress.org/Template_Hierarchy

 *

 * @package WordPress

 * @subpackage Kleo

 * @since Kleo 1.0

 */



get_header(); ?>



<?php

//Specific class for post listing */

$blog_type = sq_option('blog_type','masonry');

$blog_type = apply_filters( 'kleo_blog_type', $blog_type );



$template_classes = $blog_type . '-listing';

if ( sq_option( 'blog_archive_meta', 1 ) == 1 ) {

    $template_classes .= ' with-meta';

} else {

    $template_classes .= ' no-meta';

}



if ( $blog_type == 'standard' && sq_option('blog_standard_meta', 'left' ) == 'inline' ) {

    $template_classes .= ' inline-meta';

}

add_filter('kleo_main_template_classes', create_function('$cls','$cls .=" posts-listing ' . $template_classes . '"; return $cls;'));

?>






<?php get_template_part('page-parts/general-before-wrap'); ?>












<?php



if ( 'fotobuch-gestalten' == get_post_type() AND is_post_type_archive( $post_types ) ) {



	echo do_shortcode('[do_widget id="text-53"]');



} elseif ( 'fotos-bestellen' == get_post_type() AND is_post_type_archive( $post_types ) ) {



	echo do_shortcode('[do_widget id="text-60"]');



} elseif ( 'wanddeko' == get_post_type() AND is_post_type_archive( $post_types )) {



	echo do_shortcode('');


} elseif ( 'geschenk-mit-foto' == get_post_type() AND is_post_type_archive( $post_types ) ) {



    echo do_shortcode('[do_widget id="text-61"]');



} elseif ( 'fotokarten-gestalten' == get_post_type() AND is_post_type_archive( $post_types ) ) {



    echo do_shortcode('[do_widget id="text-55"]');


} elseif ( 'kalender-erstellen' == get_post_type() AND is_post_type_archive( $post_types ) ) {



    echo do_shortcode('[do_widget id="text-54"]');


} elseif ( has_term( 'puzzle-selbst-gestalten', 'fotogeschenke-typ' ) AND is_tax( 'fotogeschenke-typ', 'puzzle-selbst-gestalten' ) ) {



    echo do_shortcode('');


} elseif ( has_term( 'mousepad-selbst-gestalten', 'fotogeschenke-typ' ) AND is_tax( 'fotogeschenke-typ', 'mousepad-selbst-gestalten' ) ) {



    echo do_shortcode('');


} elseif ( has_term( 't-shirt-selbst-gestalten', 'fotogeschenke-typ' ) AND is_tax( 'fotogeschenke-typ', 't-shirt-selbst-gestalten' )  ) {



    echo do_shortcode('');


} elseif ( has_term( 'tasse-selbst-gestalten', 'fotogeschenke-typ' ) AND is_tax( 'fotogeschenke-typ', 'tasse-selbst-gestalten' ) ) {



    echo do_shortcode('');



} elseif ( has_term( 'kissen-selbst-gestalten', 'fotogeschenke-typ' ) AND is_tax( 'fotogeschenke-typ', 'kissen-selbst-gestalten' ) ) {



    echo do_shortcode('');


} elseif ( has_term( 'handyhuelle-selbst-gestalten', 'fotogeschenke-typ' ) AND is_tax( 'fotogeschenke-typ', 'handyhuelle-selbst-gestalten' ) ) {



    echo do_shortcode('');


} elseif ( has_term( 'gutscheine', 'fotogeschenke-typ' ) AND is_tax( 'fotogeschenke-typ', 'gutscheine' ) ) {



    echo do_shortcode('');


} elseif ( has_term( 'acrylglasbilder', 'wanddeko-typ' ) AND is_tax( 'wanddeko-typ', 'acrylglasbilder' ) ) {



    echo do_shortcode('[do_widget id="text-58"]');


} elseif ( has_term( 'foto-auf-aluminium', 'wanddeko-typ' ) AND is_tax( 'wanddeko-typ', 'foto-auf-aluminium' ) ) {



    echo do_shortcode('[do_widget id="text-64"]');


} elseif ( has_term( 'foto-auf-leinwand', 'wanddeko-typ' ) AND is_tax( 'wanddeko-typ', 'foto-auf-leinwand' ) ) {



    echo do_shortcode('[do_widget id="text-56"]');



} elseif ( has_term( 'foto-auf-forex', 'wanddeko-typ' ) AND is_tax( 'wanddeko-typ', 'foto-auf-forex' ) ) {



    echo do_shortcode('[do_widget id="text-65"]');


} elseif ( has_term( 'poster-selbst-gestalten', 'wanddeko-typ' ) AND is_tax( 'wanddeko-typ', 'poster-selbst-gestalten' ) ) {



    echo do_shortcode('[do_widget id="text-57"]');


} elseif ( has_term( 'wandkalender-selbst-gestalten', 'fotokalender-typ' ) AND is_tax( 'fotokalender-typ', 'wandkalender-selbst-gestalten' ) ) {



    echo do_shortcode('[do_widget id="text-59"]');


} elseif ( has_term( 'tischkalender-selbst-gestalten', 'fotokalender-typ' ) AND is_tax( 'fotokalender-typ', 'tischkalender-selbst-gestalten' ) ) {



    echo do_shortcode('[do_widget id="text-62"]');



}  else {


}



?>









<?php if ( have_posts() ) : ?>



    <?php if (sq_option('blog_switch_layout', 0) == 1 ) : /* Blog Layout Switcher */ ?>



        <?php kleo_view_switch( sq_option( 'blog_enabled_layouts' ), $blog_type ); ?>



    <?php endif; ?>



    <?php

    if ($blog_type == 'masonry') {

        echo '<div class="row responsive-cols kleo-masonry per-row-' . sq_option( 'blog_columns', 3 ) . '">';

    }

    ?>



    <?php

    // Start the Loop.

    while ( have_posts() ) : the_post();



        /*

         * Include the post format-specific template for the content. If you want to

         * use this in a child theme, then include a file called called content-___.php

         * (where ___ is the post format) and that will be used instead.

         */



        if ($blog_type != 'standard') :

            get_template_part( 'page-parts/post-content-' . $blog_type );

        else:

            $post_format = kleo_get_post_format();

            get_template_part( 'content', $post_format );

        endif;



    endwhile;

    ?>



    <?php

    if ($blog_type == 'masonry') {

        echo '</div>';

    }

    ?>



    <?php

    // page navigation.

    kleo_pagination();



else :

    // If no content, include the "No posts found" template.

    get_template_part( 'content', 'none' );



endif;

?>





<?php



if ( 'fotobuch-gestalten' == get_post_type() AND is_post_type_archive( $post_types ) ) {



	echo do_shortcode('[do_widget id="text-2"]');



} elseif ( 'fotos-bestellen' == get_post_type() AND is_post_type_archive( $post_types ) ) {



	echo do_shortcode('[do_widget id="text-3"]');



} elseif ( 'wanddeko' == get_post_type() AND is_post_type_archive( $post_types )) {



	echo do_shortcode('[do_widget id="text-4"]');


} elseif ( 'geschenk-mit-foto' == get_post_type() AND is_post_type_archive( $post_types ) ) {



    echo do_shortcode('[do_widget id="text-12"]');



} elseif ( 'fotokarten-gestalten' == get_post_type() AND is_post_type_archive( $post_types ) ) {



    echo do_shortcode('[do_widget id="text-13"]');


} elseif ( 'kalender-erstellen' == get_post_type() AND is_post_type_archive( $post_types ) ) {



    echo do_shortcode('[do_widget id="text-14"]');


} elseif ( has_term( 'puzzle-selbst-gestalten', 'fotogeschenke-typ' ) AND is_tax( 'fotogeschenke-typ', 'puzzle-selbst-gestalten' ) ) {



    echo do_shortcode('[do_widget id="text-5"]');


} elseif ( has_term( 'mousepad-selbst-gestalten', 'fotogeschenke-typ' ) AND is_tax( 'fotogeschenke-typ', 'mousepad-selbst-gestalten' ) ) {



    echo do_shortcode('[do_widget id="text-6"]');


} elseif ( has_term( 't-shirt-selbst-gestalten', 'fotogeschenke-typ' ) AND is_tax( 'fotogeschenke-typ', 't-shirt-selbst-gestalten' )  ) {



    echo do_shortcode('[do_widget id="text-7"]');


} elseif ( has_term( 'tasse-selbst-gestalten', 'fotogeschenke-typ' ) AND is_tax( 'fotogeschenke-typ', 'tasse-selbst-gestalten' ) ) {



    echo do_shortcode('[do_widget id="text-8"]');



} elseif ( has_term( 'kissen-selbst-gestalten', 'fotogeschenke-typ' ) AND is_tax( 'fotogeschenke-typ', 'kissen-selbst-gestalten' ) ) {



    echo do_shortcode('[do_widget id="text-9"]');


} elseif ( has_term( 'handyhuelle-selbst-gestalten', 'fotogeschenke-typ' ) AND is_tax( 'fotogeschenke-typ', 'handyhuelle-selbst-gestalten' ) ) {



    echo do_shortcode('[do_widget id="text-10"]');


} elseif ( has_term( 'gutscheine', 'fotogeschenke-typ' ) AND is_tax( 'fotogeschenke-typ', 'gutscheine' ) ) {



    echo do_shortcode('[do_widget id="text-11"]');


} elseif ( has_term( 'acrylglasbilder', 'wanddeko-typ' ) AND is_tax( 'wanddeko-typ', 'acrylglasbilder' ) ) {



    echo do_shortcode('[do_widget id="text-15"]');


} elseif ( has_term( 'foto-auf-aluminium', 'wanddeko-typ' ) AND is_tax( 'wanddeko-typ', 'foto-auf-aluminium' ) ) {



    echo do_shortcode('[do_widget id="text-16"]');


} elseif ( has_term( 'foto-auf-leinwand', 'wanddeko-typ' ) AND is_tax( 'wanddeko-typ', 'foto-auf-leinwand' ) ) {



    echo do_shortcode('[do_widget id="text-17"]');


} elseif ( has_term( 'poster-selbst-gestalten', 'wanddeko-typ' ) AND is_tax( 'wanddeko-typ', 'poster-selbst-gestalten' ) ) {



    echo do_shortcode('[do_widget id="text-18"]');


} elseif ( has_term( 'hochzeitskarten-selbst-gestalten', 'fotokarten-typ' ) AND is_tax( 'fotokarten-typ', 'hochzeitskarten-selbst-gestalten' ) ) {



    echo do_shortcode('');


}  else {


}



?>


<div class="entry"><?php echo do_shortcode('[types termmeta="beschreibung"][/types]'); ?></div>




<div class="entry">
<?php if ( category_description() ) : ?>

    <div class="archive-description"><?php echo category_description(); ?></div>


<?php endif; ?>

</div>






<?php get_template_part('page-parts/general-after-wrap'); ?>






<?php get_footer(); ?>
