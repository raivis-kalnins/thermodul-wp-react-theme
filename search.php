<?php get_header(); ?>
<section class="td-page-hero"><div class="td-container"><h1>Meklēšana</h1></div></section>
<section class="td-page-content"><div class="td-container td-content"><?php get_search_form(); ?><?php if (have_posts()) : while (have_posts()) : the_post(); ?><article class="td-card mb-4"><div class="td-card-body"><h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2><?php the_excerpt(); ?></div></article><?php endwhile; the_posts_pagination(); else : ?><p>Nekas netika atrasts.</p><?php endif; ?></div></section>
<?php get_footer(); ?>
