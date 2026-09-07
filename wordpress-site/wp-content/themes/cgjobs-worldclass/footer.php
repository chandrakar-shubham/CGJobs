<footer class="site-footer">
    <div class="wrap">
        <div class="footer-grid">
            <div><strong>CGJobs</strong><p>Fast, reliable government job information for Chhattisgarh and India. Laravel powers the data; WordPress delivers the public experience.</p></div>
            <div><strong>Explore</strong><p><a href="<?php echo esc_url(home_url('/jobs/')); ?>">Latest Jobs</a><br><a href="<?php echo esc_url(home_url('/current-affairs/')); ?>">Current Affairs</a><br><a href="<?php echo esc_url(home_url('/static-gk/')); ?>">Static GK</a></p></div>
            <div><strong>Important</strong><p><a href="<?php echo esc_url(home_url('/privacy-policy/')); ?>">Privacy</a><br><a href="<?php echo esc_url(home_url('/contact/')); ?>">Contact</a></p></div>
        </div>
        <div class="copyright">© <?php echo esc_html(date('Y')); ?> CGJobs. Information is provided for educational and informational use.</div>
    </div>
</footer>
<?php wp_footer(); ?>
</body>
</html>
