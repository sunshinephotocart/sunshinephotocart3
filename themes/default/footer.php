</div>

<?php do_action('sunshine_after_content'); ?>
<?php wp_footer(); ?>
<script>
    jQuery(document).ready(function($){
        $('#sunshine-mobile-menu').click(function(){
            $('#sunshine-mobile-menu i').toggleClass('fa-close');
            $('#sunshine-mobile-menu i').toggleClass('fa-bars');
            $('#sunshine--main-menu-container').toggleClass('open');
        });
    });
</script>

</body>
</html>
