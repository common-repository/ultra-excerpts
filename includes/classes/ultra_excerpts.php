<?php
class ultra_excerpts {
    
    /* ultra excerpts construct */
    function __construct($plugin_file_path) {
        
     $this ->ultra_excerpt_default_options = [
    'read_more' => 'Read More',
    'excerpt_length' => 25,
    'excerpt_length_type' => 'Words',
    'excerpt_custom_class' => '',
    'excerpt_custom_class_read_more' => '',
    'excerpt_read_more_flag' => 0,
    'excerpt_strip_all_tags' => 0,
    'excerpt_remove_all_headings' => 0,
    'excerpt_override_all'=>0,
    'excerpt_ellipsis' =>'...'
    ];
    $this->excerpt_length_type_options = [
        '1' => 'Words',
        '2' => 'Characters'
    ];
    $this->plugin_file_path = $plugin_file_path;
    $this ->plugin_dir_url =plugin_dir_url( $plugin_file_path );
    $this ->plugin_dir_path =plugin_dir_path(  $plugin_file_path);
    $this->plugin_path = 'ultra_excerpts';
    $this->plugin_base = plugin_basename($plugin_file_path); 
    $this->plugin_options = [];
    
    if ( is_user_logged_in() ) {
        
        if ( ! empty( $_POST ) && $_REQUEST['page'] = $this->plugin_path ) {
              $this->plugin_options =   $this->ultra_excerpt_update_options();
        }
        
        if ( is_admin() ) {
    		$this->ultra_excerpt_admin_init();
        }
	}

    add_action( 'loop_start', array( $this, 'ultra_excerpt_hook_filters' ) );
           
    }
    
    /* Update options */
    
    function ultra_excerpt_update_options(){
        $options_db = get_option('ultra_excerpt_fields');
        
        $options_merged = $this->ultra_excerpt_recursive_parse_args( $options_db, $this->ultra_excerpt_default_options );
        
        update_option( 'ultra_excerpt_fields', $options_merged );     
        
        return get_option('ultra_excerpt_fields');
        
    }
    
    /* Main filter hooks */
    
    function ultra_excerpt_hook_filters(){
        
         $options =  get_option('ultra_excerpt_fields');
        
        if($options['excerpt_override_all']){
            remove_all_filters( 'get_the_excerpt');
            remove_all_filters( 'the_excerpt'); 
            remove_all_filters( 'wp_trim_excerpt'); 
            remove_all_filters('excerpt_more');
            add_filter( 'wp_trim_excerpt',array ( $this , 'ultra_excerpt_main' )  );  
            add_filter( 'get_the_excerpt',array ( $this , 'ultra_excerpt_main' ) );
            add_filter('excerpt_more', array ( $this , 'ultra_excerpt_more' ) );
            add_filter('the_content', array ( $this , 'ultra_excerpt_remove_content_filters' ), 9);
        }
        
        add_filter( 'the_excerpt', array ( $this , 'ultra_excerpt_main' )  );
        

        
    }
    
    /* Admin functions action hooks */
    
    function ultra_excerpt_admin_init(){
         add_action( 'admin_menu', array( $this , 'ultra_excerpt_admin_setup' ) );
         add_action( 'admin_init', array ( $this , 'ultra_excerpt_register_settings' )  );
        add_filter("plugin_action_links_".$this->plugin_base,array ( $this , 'ultra_excerpt_settings_link' )  );
    }
    
    /* Add settings link to plugin options */
    
    function ultra_excerpt_settings_link($links) { 
    $settings_link = '<a href="options-general.php?page='.$this->plugin_path.'.php">Settings</a>'; 
    array_unshift($links, $settings_link); 
    return $links; 
    }
    
    /* Admin menu set up */
    
    function ultra_excerpt_admin_setup(){
    
        $page_title = 'Ultra Excerpts';
        $menu_title = 'Ultra Excerpts';
        
        add_options_page($page_title,  $menu_title,  'manage_options', 
        $this->plugin_path ,array ( $this , 'ultra_excerpt_custom_menu_page' ));
    
    
    }
    

    function ultra_excerpt_remove_content_filters($content){

        if (!is_singular() || !is_page()) { 
            remove_all_filters( 'the_content' );
            add_filter( 'the_content',array ( $this , 'ultra_excerpt_main' )  );
            
            return $this->ultra_excerpt_main($content);
        } 
    return $content;
    }
    
    function ultra_excerpt_more(){
        global $post;
        $options = get_option('ultra_excerpt_fields');
        if($options['excerpt_read_more_flag'] ){
        $read_more = '<a href="'.get_permalink($post->ID).'" class="ultra_excerpt_read_more '.$options['excerpt_custom_class_read_more'].'">'. $options['read_more'].'</a>';
        }
        else{
             $read_more = '';
        }
        return  $read_more;
    }
     
    /* custom menu page and admin asset loading */
    
    function ultra_excerpt_custom_menu_page(){
        $plugin_path = $this -> plugin_path;
        $plugin_url = $this ->plugin_dir_url;
        global $pagenow; 
        if($pagenow == 'options-general.php'){
            wp_enqueue_style('admin-ultra-excerpt-css', $plugin_url. 'includes/assets/ultra-excerpt-admin.css');
            wp_enqueue_script('admin-ultra-excerpt-js', $plugin_url. 'includes/assets/ultra-excerpt-admin.js');
        }
        require_once $this ->plugin_dir_path. 'includes/templates/options-page.php';
    }
    
    /* Merge options */
    function ultra_excerpt_recursive_parse_args( $args, $defaults ) {
        $new_args = (array) $defaults;
        
        if(!empty($args)){
            foreach ( $args as $key => $value ) {
                if ( is_array( $value ) && isset( $new_args[ $key ] ) ) {
                    $new_args[ $key ] = $this->ultra_excerpt_recursive_parse_args( $value, $new_args[ $key ] );
                }
                else {
                    $new_args[ $key ] = $value;
                }
            }
        }
        return $new_args;
    }
    
    
     /* Function to manipulate excerpt content as per user settings */
      
    function ultra_excerpt_main( $excerpt ) {
        
        if ( is_singular( 'post' ) ) {

              return $excerpt;
        }
            
        $options = get_option('ultra_excerpt_fields');
         
        $postId =  get_the_ID();
       
        if($options['excerpt_override_all']){
           $excerpt_str =  get_post_field('post_content', $postId);
        }
        else{
           $excerpt_str = $excerpt; 
        }
        
        if($options['excerpt_remove_all_headings']){
        $excerpt_str = preg_replace('/(\<h[1-6]\>)(.*)(\<\/h[1-6]\>)/', '', $excerpt_str);
        }
        if($options['excerpt_strip_all_tags']){
        $excerpt_str = strip_tags($excerpt_str);
        }
        $excerpt_str =  trim($excerpt_str);
        
        
        if($options['excerpt_read_more_flag'] ){
        $read_more = '<a href="'.get_permalink( $postId).'" class="ultra_excerpt_read_more '.$options['excerpt_custom_class_read_more'].'">'. $options['read_more'].'</a>';
        }
        else{
        $read_more = '';
        }

        if(intval(strlen($excerpt_str)) != 0 ){
            
        	$matches = []; 
            $space_between_matches = ' ';
            /** Strip HTML Comments **/
           $excerpt_str = preg_replace('/<!--(.|\s)*?-->/', '', $excerpt_str);
           
        	if($options['excerpt_length_type']=='Words'){
        	    
        	preg_match_all("/(<[^>]+>)|[^<\/>\s]\S+/", $excerpt_str, $matches);
        	
        	}else{ 
        	    
        	preg_match_all("/(<[^>]+>)|[^<\/>]/",$excerpt_str,$matches);
        	$space_between_matches = '';
        	}
            
        	$i=0;
        	$excerpt_temp = '';
            
        	foreach($matches[0] as $key=>$match){
        	    
        		if($i < intval($options['excerpt_length'] ) ){
        			if($match[0] != '<'){
        				$i++;
        			}else{
        			    if($key == intval(count($matches[0]))-1){
        			        break;
        			    }
        			}
        			$excerpt_temp = $excerpt_temp .$space_between_matches .$match;
        		}
        		else{
        			break;
        		}
        	}
        	
        	$excerpt_ellipsis = trim($options['excerpt_ellipsis']);
        
        	$excerpt = balanceTags($excerpt_temp.$excerpt_ellipsis, true);
    
        }else{
            $excerpt = '';
            
        }
    
        $excerpt_final =  '<div class="'.$options['excerpt_custom_class'].'">'.$excerpt .$read_more.'</div>';
        return $excerpt_final ;
        
        }


      /* Register plugin settings and form display functions */
      
    function ultra_excerpt_register_settings() {
         
          $this->plugin_options =   $this->ultra_excerpt_update_options();
    
        register_setting( 'ultra_excerpt_fields', 'ultra_excerpt_fields',array ( $this , 'ultra_excerpt_fields_validate' )); 
    
    	add_settings_section('main_section', 'Main Settings', array ( $this , 'section_text_fn' ),  $this->plugin_path);
    	add_settings_field('plugin_excerpt_length', 'Excerpt Length', array ( $this , 'setting_excertp_length_fn'), $this->plugin_path, 'main_section');
    	
    	add_settings_field('plugin_read_more_string', 'Read More Text', array ( $this , 'setting_read_more_fn'), $this->plugin_path, 'main_section');
        
        add_settings_field('plugin_strip_tags_bool', 'Strip All Tags', array ( $this , 'setting_strip_tags_fn'), $this->plugin_path , 'main_section');
        
        add_settings_field('plugin_remove_all_headings_bool', 'Remove All Headings', array ( $this , 'setting_remove_all_headings_fn'), $this->plugin_path , 'main_section');
        
        add_settings_field('plugin_override_all_bool', 'Override All Other Excerpt Filters', array ( $this , 'setting_override_all_fn'), $this->plugin_path , 'main_section');
        
        add_settings_field('plugin_ellipsis_string', 'Excerpt Ellipsis', array ( $this , 'setting_ellipsis_fn'), $this->plugin_path , 'main_section');
        
        add_settings_field('plugin_custom_class_string', 'Excerpt Custom Class', array ( $this , 'setting_custom_class_fn'), $this->plugin_path , 'main_section');
        add_settings_field('plugin_custom_class_read_more_string', 'Read More Custom Class', array ( $this , 'setting_custom_class_read_more_fn'), $this->plugin_path , 'main_section');
        
        
    }
    
    function setting_read_more_fn() {
    	$options = $this->plugin_options;
    	$classes= '';
    	 $classes_off = '';
    	if($options['excerpt_read_more_flag']){
    	    $classes = 'active';
    	}
    	else{
    	    $classes_off = 'active';
    	}
    	echo '<div class="read_more_section"><label class="switch"><input onchange="show_read_more_options(this);" name="ultra_excerpt_fields[excerpt_read_more_flag]" value="1" type="checkbox" '. checked( $options['excerpt_read_more_flag'], 1 ,false ) .'><span class="slider round"></span>
    </label><span class="toggle_tag toggle_enabled '.$classes.' ">Enabled</span><span class="toggle_tag  toggle_disabled '.$classes_off.' ">Disabled</span>';
    	echo "<input id='plugin_read_more_string' class='".$classes."' name='ultra_excerpt_fields[read_more]' size='40' type='text' value='{$options['read_more']}' /></div>";
    }
    
    
    function setting_custom_class_fn() {
    	$options = $this->plugin_options;
    	echo "<input id='plugin_excerpt_custom_class' name='ultra_excerpt_fields[excerpt_custom_class]'  type='text' value='{$options['excerpt_custom_class']}' />";
    }
    function setting_custom_class_read_more_fn() {
    	$options = $this->plugin_options;
    	echo "<input id='plugin_excerpt_custom_class_read_more' name='ultra_excerpt_fields[excerpt_custom_class_read_more]'  type='text' value='{$options['excerpt_custom_class_read_more']}' />";
    }
    
    function setting_excertp_length_fn() {
    	$options = $this->plugin_options;
    
    	echo "<div class='form_wrap'><input id='plugin_excerpt_length' name='ultra_excerpt_fields[excerpt_length]' type='number' value='{$options['excerpt_length']}' />";
    	
    	    	echo "<select id='plugin_excerpt_length_type' name='ultra_excerpt_fields[excerpt_length_type]'>";
    	    	
        	foreach($this->excerpt_length_type_options as $option){
        	    $class='';
        	    if($option == $options['excerpt_length_type'] ){
        	        $class='selected';
        	    }
        	    echo "<option value='".$option."' ".$class.">".$option."</option>";
        	}
    
        echo "</select>
        
        </div><div class='ultra-excerpt-hint'>Length does not include ellipsis and read more link.</div>";
        
    }
    function setting_ellipsis_fn(){
       $options = $this->plugin_options;
    	echo "<input id='plugin_excerpt_ellipsis' name='ultra_excerpt_fields[excerpt_ellipsis]'  type='text' value='{$options['excerpt_ellipsis']}' />";
    }
    
    /* No output for section text */
    function  section_text_fn() {
        echo '';
    }

    function setting_strip_tags_fn(){
        $options = $this->plugin_options;
        
        $classes= '';
    	 $classes_off = '';
    	if(isset($options['excerpt_strip_all_tags']) && $options['excerpt_strip_all_tags']){
    	    $classes = 'active';
    	}
    	else{
    	    $classes_off = 'active';
    	}
    	   
        	echo '<div class="read_more_section"><label class="switch"><input  name="ultra_excerpt_fields[excerpt_strip_all_tags]" value="1" type="checkbox" '. checked( $options['excerpt_strip_all_tags'], 1 ,false ) .'><span class="slider round"></span>
    </label><span class="toggle_tag toggle_enabled '.$classes.' ">Enabled</span><span class="toggle_tag  toggle_disabled '.$classes_off.' ">Disabled</span>';
        
    }

    function setting_remove_all_headings_fn(){
        $options = $this->plugin_options;
        
        $classes= '';
    	 $classes_off = '';
    	if(isset($options['excerpt_remove_all_headings']) && $options['excerpt_remove_all_headings']){
    	    $classes = 'active';
    	}
    	else{
    	    $classes_off = 'active';
    	}
    	   
        	echo '<div class="read_more_section"><label class="switch"><input  name="ultra_excerpt_fields[excerpt_remove_all_headings]" value="1" type="checkbox" '. checked( $options['excerpt_remove_all_headings'], 1 ,false ) .'><span class="slider round"></span>
    </label><span class="toggle_tag toggle_enabled '.$classes.' ">Enabled</span><span class="toggle_tag  toggle_disabled '.$classes_off.' ">Disabled</span>';
        
    }
    
    function setting_override_all_fn(){
        $options = $this->plugin_options;
        
        $classes= '';
    	 $classes_off = '';
    	if(isset($options['excerpt_override_all']) && $options['excerpt_override_all']){
    	    $classes = 'active';
    	}
    	else{
    	    $classes_off = 'active';
    	}
    	   
        	echo '<div class="read_more_section"><label class="switch"><input  name="ultra_excerpt_fields[excerpt_override_all]" value="1" type="checkbox" '. checked( $options['excerpt_override_all'], 1 ,false ) .'><span class="slider round"></span>
    </label><span class="toggle_tag toggle_enabled '.$classes.' ">Enabled</span><span class="toggle_tag  toggle_disabled '.$classes_off.' ">Disabled</span>';
        
    }
    
    /* form input sanitization function */
    
    function ultra_excerpt_fields_validate($input_fields){
        
        $input_fields['excerpt_length'] = intval( $input_fields['excerpt_length']);
        
        if(!$input_fields['excerpt_length_type'] == 'Words'  ){
            $input_fields['excerpt_length_type'] == 'Characters';
        }
        
        $input_fields['excerpt_read_more_flag'] = $this->ultra_excerpt_sanitize_toggle($input_fields['excerpt_read_more_flag']);
        
        $input_fields['excerpt_strip_all_tags'] = $this->ultra_excerpt_sanitize_toggle($input_fields['excerpt_strip_all_tags']);
        
        $input_fields['excerpt_remove_all_headings'] = $this->ultra_excerpt_sanitize_toggle($input_fields['excerpt_remove_all_headings']);
        
        $input_fields['excerpt_override_all'] = $this->ultra_excerpt_sanitize_toggle($input_fields['excerpt_override_all']);
        
        $input_fields['read_more']  = preg_replace('/[^a-z\d ]/i', '', $input_fields['read_more'] );
        
        $input_fields['excerpt_ellipsis']  =  htmlspecialchars($input_fields['excerpt_ellipsis'], ENT_QUOTES);
        
        $input_fields['excerpt_custom_class']  = $this->ultra_excerpt_sanitize_html_classes($input_fields['excerpt_custom_class'] );
        
        $input_fields['excerpt_custom_class_read_more'] = $this->ultra_excerpt_sanitize_html_classes($input_fields['excerpt_custom_class_read_more']);
        
        return $input_fields;
        
    }
    
    
    function ultra_excerpt_sanitize_toggle($toggle_field){
        if(intval($toggle_field) != 0){
           return 1;
        }
        
        return 0;
    }
    
    function ultra_excerpt_sanitize_html_classes( $classes_string, $return_format = 'input' ) {
        $sanitized_classes = '';
        if( !empty( $classes_string )){
           $classes =  explode(" ", $classes_string);
        
        
        foreach($classes as $class){
            $sanitized_classes = $sanitized_classes .' '. sanitize_html_class( $class );
        }
        }
        return trim($sanitized_classes);
}

}
