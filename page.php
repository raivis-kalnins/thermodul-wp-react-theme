<?php get_header(); ?>
<?php while (have_posts()) : the_post(); ?>
<section class="td-page-hero"><div class="td-container"><h1><?php the_title(); ?></h1></div></section>
<section class="td-page-content"><div class="td-container td-content"><?php the_content(); ?></div></section>
<?php endwhile; ?>
<?php get_footer(); ?>
