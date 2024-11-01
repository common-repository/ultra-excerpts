<div>

<div class="wrap ultra-excerpt-settings-container">

    <div class="ultra_excerpt_header_wrap">
       <h2></h2>
    <img class="ultra_exceprt_logo" src="<?php echo $plugin_url ; ?>/includes/assets/ultra_excerpt_icon.jpg" />
    <h2 class="wp-heading">Welcome to Ultra Excerpts!	</h2>
    <p> Here you can find options to customize how excerpt shows up on your website.</p>
    </br>
    </br>
</div>


<form method="POST" action="options.php"  autocomplete="off"> 

    <?php   settings_fields('ultra_excerpt_fields'); ?>

    <?php  do_settings_sections($plugin_path); 
    
    submit_button();

    wp_nonce_field( 'ultra_excerpt_form_post','ultra_excerpt_form_fields' ); 
    ?>

</form>
    
<div class="pulgin_footer">
    Plugin by 
    <a target="_blank" href="https://www.exsamp.com">
        Exsamp Inc</a>
    </div>
<?php settings_errors($plugin_path); ?>
</div>
</div>