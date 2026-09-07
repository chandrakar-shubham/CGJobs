<!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<header class="site-header">
    <div class="wrap nav">
        <a class="brand" href="<?php echo esc_url(home_url('/')); ?>">CG<span>Jobs</span></a>
        <nav class="nav-links" aria-label="Primary">
            <a href="<?php echo esc_url(home_url('/')); ?>">Home</a>
            <a href="<?php echo esc_url(home_url('/jobs/')); ?>">Jobs</a>
            <a href="<?php echo esc_url(home_url('/exams/')); ?>">Exams</a>
            <a href="<?php echo esc_url(home_url('/current-affairs/')); ?>">Current Affairs</a>
            <a href="<?php echo esc_url(home_url('/static-gk/')); ?>">Static GK</a>
            <a class="nav-cta" href="<?php echo esc_url(home_url('/jobs/')); ?>">Find Jobs</a>
        </nav>
    </div>
</header>
