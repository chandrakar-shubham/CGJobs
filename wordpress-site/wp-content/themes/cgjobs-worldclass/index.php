<?php get_header(); ?>
<main class="section"><div class="wrap">
<?php if (have_posts()): while (have_posts()): the_post(); ?>
<article class="cgjobs-detail"><h1><?php the_title(); ?></h1><?php the_content(); ?></article>
<?php endwhile; else: ?><div class="empty-state">Nothing to show yet.</div><?php endif; ?>
</div></main>
<?php get_footer(); ?>
