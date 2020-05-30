<div class="wrap">
    <h2>Goodnow Slide Show</h2>
    <form method="post" action="options.php"> 
        <?php @settings_fields('goodnow_slide_show-group'); ?>
        <?php @do_settings_fields('goodnow_slide_show-group'); ?>

        <?php do_settings_sections('goodnow_slide_show'); ?>

        <?php @submit_button(); ?>
    </form>
</div>