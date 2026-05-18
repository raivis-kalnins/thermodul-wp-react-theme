<?php get_header(); ?>
<section class="td-page-hero"><div class="td-container"><h1><?php the_archive_title(); ?></h1></div></section>
<section class="td-page-content"><div class="td-container"><div class="row g-4">
<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
<div class="col-md-6 col-lg-4"><article <?php post_class('td-card'); ?>><?php if (has_post_thumbnail()) { the_post_thumbnail('large'); } ?><div class="td-card-body"><h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2><?php the_excerpt(); ?></div></article></div>
<?php endwhile; the_posts_pagination(); else : ?><p>Nekas netika atrasts.</p><?php endif; ?>
</div></div></section>
<?php get_footer(); ?>
