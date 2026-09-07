<?php
/* Template Name: Jobs */
get_header(); ?>
<main class="section"><div class="wrap">
    <div class="section-head"><div><span class="eyebrow">LIVE JOB DATABASE</span><h1>Latest Government Jobs</h1><p>Search by title, department or category.</p></div></div>
    <form class="search-box" method="get"><input name="job_search" type="search" value="<?php echo esc_attr(sanitize_text_field(wp_unslash($_GET['job_search'] ?? ''))); ?>" placeholder="Search jobs…"><button class="btn" type="submit">Search</button></form>
    <div style="margin-top:24px"><?php
    $query = sanitize_text_field(wp_unslash($_GET['job_search'] ?? ''));
    echo do_shortcode('[cgjobs_jobs limit="50"]');
    ?></div>
</div></main>
<?php get_footer(); ?>
