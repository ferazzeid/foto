<?php
/**
 * The template for List Blog entry
 *
 * @package WordPress
 * @subpackage Kleo
 * @since Kleo 1.0
 */
?>

<article id="post-<?php the_ID(); ?>" <?php post_class(array("post-item")); ?>>
    <div class="row post-content animated animate-when-almost-visible el-appear">

        <div class="col-xs-3 no-padding" style="padding-left:10px;">
        <?php
        global $kleo_config;
        $kleo_post_format = get_post_format();

        /* For portfolio post type */
        if ( get_post_type() == 'portfolio' ) {
            if ( get_cfield( 'media_type' ) && get_cfield( 'media_type' ) != '' ) {
                $media_type = get_cfield( 'media_type' );
                switch ( $media_type ) {
                    case 'slider':
                        $kleo_post_format = 'gallery';
                        break;

                    case 'video':
                    case 'hosted_video':
                        $kleo_post_format = 'video';
                        break;
                }
            }
        }

        switch ( $kleo_post_format ) {

            case 'video':

                //oEmbed video
                $video = get_cfield( 'embed' );
                // video bg self hosted
                $bg_video_args = array();
                $k_video = '';

                if (get_cfield( 'video_mp4' ) ) {
                    $bg_video_args['mp4'] = get_cfield( 'video_mp4' );
                }
                if (get_cfield( 'video_ogv' ) ) {
                    $bg_video_args['ogv'] = get_cfield( 'video_ogv' );
                }
                if (get_cfield( 'video_webm' ) ) {
                    $bg_video_args['webm'] = get_cfield( 'video_webm' );
                }

                if ( !empty( $bg_video_args ) ) {
                    $attr_strings = array(
                        'preload="none"'
                    );

                    if (get_cfield( 'video_poster' ) ) {
                        $attr_strings[] = 'poster="' . get_cfield( 'video_poster' ) . '"';
                    }

                    $k_video .= '<div class="kleo-video-wrap"><video ' . join( ' ', $attr_strings ) . ' controls="controls" class="kleo-video" style="width: 100%; height: 100%;">';

                    $source = '<source type="%s" src="%s" />';
                    foreach ( $bg_video_args as $video_type => $video_src ) {
                        $video_type = wp_check_filetype( $video_src, wp_get_mime_types() );
                        $k_video .= sprintf( $source, $video_type['type'], esc_url( $video_src ) );
                    }

                    $k_video .= '</video></div>';

                    echo $k_video;
                }
                // oEmbed
                elseif ( !empty( $video ) ) {
                    global $wp_embed;
                    echo apply_filters( 'kleo_oembed_video', $video );
                }

                break;

            case 'audio':

                $audio = get_cfield('audio');
                if (!empty($audio)) { ?>
                    <div class="post-audio">
                        <audio preload="none" class="kleo-audio" id="audio_<?php the_id();?>" style="width:100%;" src="<?php echo $audio; ?>"></audio>
                    </div>
                <?php
                }
                break;

            case 'gallery':

                $slides = get_cfield('slider');
                echo '<div class="kleo-banner-slider">'
                    .'<div class="kleo-banner-items modal-gallery">';
                if ( $slides ) {
                    foreach( $slides as $slide ) {
                        if ( $slide ) {
                            $image = aq_resize( $slide, $kleo_config['post_gallery_img_width'], $kleo_config['post_gallery_img_height'], true, true, true );
                            //small hack for non-hosted images
                            if (! $image ) {
                                $image = $slide;
                            }
                            echo '<article>
								<a href="'. $slide .'" data-rel="modalPhoto[inner-gallery]">
									<img src="'.$image.'" alt="'. get_the_title() .'">'
                                . kleo_get_img_overlay()
                                . '</a>
							</article>';
                        }
                    }
                }

                echo '</div>'
                    . '<a href="#" class="kleo-banner-prev"><i class="icon-angle-left"></i></a>'
                    . '<a href="#" class="kleo-banner-next"><i class="icon-angle-right"></i></a>'
                    . '<div class="kleo-banner-features-pager carousel-pager"></div>'
                    .'</div>';

                break;


            case 'aside':
                echo '<div class="post-format-icon"><i class="icon icon-doc"></i></div>';
                break;

            case 'link':
                echo '<div class="post-format-icon"><i class="icon icon-link"></i></div>';
                break;

            case 'quote':
                echo '<div class="post-format-icon"><i class="icon icon-quote-right"></i></div>';
                break;

            case 'image':
            default:
                if ( kleo_get_post_thumbnail_url() != '' ) {
                    echo '<div class="post-image">';

                    $img_url = kleo_get_post_thumbnail_url();
                    $image = aq_resize( $img_url, 200, null, true, true, true );
                    if( ! $image ) {
                        $image = $img_url;
                    }
                    echo ''
                        . '<img width="124px" height="auto" src="' . $image . '" alt="" />'
                        
                        ;

                    echo '</div><!--end post-image-->';
                } else {
                    $post_icon = $kleo_post_format == 'image' ? 'picture' : 'doc';
                    echo '<div class="post-format-icon"><i class="icon icon-' . $post_icon . '"></i></div>';
                }

                break;

        }
        ?>

		<span class="ifolor_img"><?php foreach (get_the_category() as $cat) : ?><img height="auto" width="50"  src="<?php echo z_taxonomy_image_url($cat->term_id); ?>" />  <?php endforeach; ?></span>




        </div>





        <div class="col-xs-9">

            <?php if ( ! in_array( $kleo_post_format, array('status', 'quote', 'link') ) ): ?>
            <div class="post-header">

                

                
                <span class="post-item-title" style="font-size:21px;"><?php $link_url_alt = get_post_meta($post->ID, 'url_alt', true); ?><?php if (!empty($link_url_alt)): ?><?php echo do_shortcode('[field fotoservice]'); ?> <?php echo do_shortcode('[field produkt]'); ?> <?php echo do_shortcode('[field produktname]'); ?><?php else: ?>
                <?php echo do_shortcode('[field fotoservice]'); ?> <?php echo do_shortcode('[field produkt]'); ?> <?php echo do_shortcode('[field produktname]'); ?>

                <?php endif; ?>

                <?php $image_featured = get_post_meta($post->ID, 'featured', true); ?>
                <?php
                if (!empty($image_featured)) {
                echo '<img style="display: inline; margin-top:1px; margin-left:1px;" src="http://fotoservice24.ch/wp-content/uploads/images/top-angebot.png" height="19" width="19" alt="Top Angebot">';
                }
                ?></br>

                
        

                </span>
                <span class="post-item-info" style="font-size:14px;margin-left:1px;margin-bottom:4px;"><?php echo do_shortcode('[field zu_info]'); ?></span>

                <span class="post-meta">
                    <!--?php kleo_entry_meta();?>-->
                </span>

            </div><!--end post-header-->
            <?php endif; ?>

 
             
            
             


            <div class="post-footer"> 

                 <?php

                if ( 'fotobuch' == get_post_type() ) { ?>

                 
                <span class="ver-sec" style="font-size:10px;margin-left:1px;"> Versand Ab: CHF <?php echo do_shortcode('[field versand]'); ?> |</span>  
                <span class="pre-sec" style="font-size:10px;margin-left:1px;"> Preis Ab: CHF <?php echo do_shortcode('[field preis_von]'); ?></span>

                <?php } elseif ( 'fotoabzug' == get_post_type() ) { ?>

                <span class="ver-sec" style="font-size:10px;margin-left:1px;"><?php echo do_shortcode('[field groessen]'); ?> |</span>
                <span class="ver-sec" style="font-size:10px;margin-left:1px;">Versand Ab: CHF <?php echo do_shortcode('[field versand]'); ?> |</span>  
                <span class="pre-sec" style="font-size:10px;margin-left:1px;"> Preis Ab: CHF <?php echo do_shortcode('[field preis_von]'); ?></span>

                <?php } elseif ( 'fotogeschenk' == get_post_type() ) { ?>

                
                <span class="ver-sec" style="font-size:10px;margin-left:1px;">Versand Ab: CHF <?php echo do_shortcode('[field versand]'); ?> |</span>  
                <span class="pre-sec" style="font-size:10px;margin-left:1px;"> Preis Ab: CHF <?php echo do_shortcode('[field preis_von]'); ?></span>


                <?php } elseif ( 'kalender' == get_post_type() ) { ?>

                
                <span class="ver-sec" style="font-size:10px;margin-left:1px;">Versand Ab: CHF <?php echo do_shortcode('[field versand]'); ?> |</span>  
                <span class="pre-sec" style="font-size:10px;margin-left:1px;"> Preis Ab: CHF <?php echo do_shortcode('[field preis_von]'); ?></span>

                <?php } elseif ( 'wanddeko' == get_post_type() ) { ?>

                
                <span class="ver-sec" style="font-size:10px;margin-left:1px;">Versand Ab: CHF <?php echo do_shortcode('[field versand]'); ?> |</span>  
                <span class="pre-sec" style="font-size:10px;margin-left:1px;"> Preis Ab: CHF <?php echo do_shortcode('[field preis_von]'); ?></span>

                <?php } elseif ( 'fotokarte' == get_post_type() ) { ?>

                <span class="" style="font-size:10px;margin-left:1px;">Stück: <?php echo do_shortcode('[field anzahl_karten]'); ?></span> | 
                <span class="ver-sec" style="font-size:10px;margin-left:1px;">Versand Ab: CHF <?php echo do_shortcode('[field versand]'); ?> |</span>  
                <span class="pre-sec" style="font-size:10px;margin-left:1px;"> Preis Ab: CHF <?php echo do_shortcode('[field preis_von]'); ?></span>


                


                <?php }

                ?>




                <?php $link_url_alt = get_post_meta($post->ID, 'url_alt', true); ?>


                <?php if (!empty($link_url_alt)): ?>
                    <span class="butt-righ" style="font-size:18px;margin-left:15px;float:right"> <b><a title="<?php echo do_shortcode('[field fotoservice]'); ?> <?php echo do_shortcode('[field produkt]'); ?> gestalten" rel="nofollow" class="zurWebsiteSingle" target="_blank" href="<?php echo do_shortcode('[field url_alt]'); ?>">  Gestalten</a></b></span>

                <?php else: ?>
                    <span class="butt-righ" style="font-size:18px;margin-left:15px;float:right"> <b><a title="<?php echo do_shortcode('[field fotoservice]'); ?> <?php echo do_shortcode('[field produkt]'); ?> gestalten" rel="nofollow" class="zurWebsiteSingle2" target="_blank" href="<?php echo do_shortcode('[field link_url]'); ?>"> Gestalten</a></b></span>

                <?php endif; ?>
                
                

                <small>
                    <!--?php do_action('kleo_post_footer');?>-->

                    <!--a href="<?php the_permalink();?>"><span class="muted pull-right"><?php esc_html_e("Read more","kleo_framework");?></span></a>-->
                </small>



            </div><!--end post-footer-->



                <?php edit_post_link(); ?>

        </div>


    </div><!--end post-content-->
</article>

