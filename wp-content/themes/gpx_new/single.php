<?php
/**
 * Theme: GPX
 */
get_header(); ?>

<div id="primary" class="content-area">
	<main id="main" class="site-main" role="main">
		<!-- section class="wbanner">
				<div id="slider-home" class="royalSlider heroSlider rsMinW rsFullScreen">
					<div class="slider-item rsContent"><img class="rsImg" src="<?php echo get_template_directory_uri(); ?>/images/california.jpg" alt="" /></div>
				</div>
		</section -->
		<section class="wcontent" style="margin-top:70px;">
			<div class="dgt-container">
				<div class="single_content">
				<div class="breadcrumbs">
                    <?php
                    if ( function_exists('yoast_breadcrumb') ) {
                        yoast_breadcrumb( '<p id="yoast-breadcrumbs">','</p>' );
                    }
                    ?>
				</div>
				<br />
					<?php while ( have_posts() ) : the_post(); ?>
						<h1><?php the_title(); ?></h1>
						<div class="single_content">
							<?php the_content() ?>
						</div>
					<?php endwhile; ?>
				</div>
				<div class="single_sidebar">
					<?php get_sidebar(); ?>
				</div>
			</div>
		</section>
	</main>
</div>
<?php get_footer(); ?>
