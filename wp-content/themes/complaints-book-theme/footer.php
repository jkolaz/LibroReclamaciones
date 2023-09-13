<footer id="colophon" class="site-footer">
    <div class="site-info">
        <?php
        if (function_exists('the_privacy_policy_link')) {
            the_privacy_policy_link('', '<span role="separator" aria-hidden="true"></span>');
        }
        ?>
        <a href="<?php echo esc_url(__('https://wordpress.org/', 'yourtheme')); ?>" class="imprint">
            <?php printf(__('Powered by %s', 'yourtheme'), 'WordPress'); ?>
        </a>
    </div>
</footer>
<?php wp_footer(); ?>
</body>
</html>
