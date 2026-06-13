<?php

if (!defined('ABSPATH')) {
	exit;
}

get_header();
?>
<div class="content home-content">
	<div class="container container--wide">
		<main id="primary" class="site-main home-main">
			<?php require get_template_directory() . '/inc/front-page-sections.php'; ?>
		</main>
	</div>
</div>
<?php
get_footer();
