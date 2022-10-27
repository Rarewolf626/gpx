<?php
/**
 * Theme: GPX
 * The template for displaying 404 pages (not found)
 *
 * @var array $args
 * @var ?string $message
 */

$title = $args['title'] ?? null;
$message = $args['message'] ?? null;

get_header(); ?>
	<section class="wcontent">
		<div class="dgt-container">
			<div class="error_content">
				<div style="margin-top:200px;">
					<h1 style="font-size:30px;font-weight:bold;margin-bottom:20px;"><?= $title ? esc_html($title) : 'Error 404' ?></h1>
                    <div style="font-size:20px;">
                        <?php if($message):?>
                            <div><?= wp_kses($message, 'post') ?></div>
                        <?php else: ?>
                            <div>Sorry, we couldn't find the page you're looking for.</div>
                        <?php endif; ?>
                        <br /><br />
                        Start booking your next vacation by searching for a <a href="/resorts/" style="color:#009bd9;">location or resort</a>.
                        Please <a href="/contact/" style="color:#009bd9;">contact us</a> at (866) 325-6295 for GPX help.
                    </p>
				</div>
			</div>
		</div>
	</section>
<?php get_footer(); ?>
