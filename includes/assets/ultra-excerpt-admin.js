    function show_read_more_options(caller){
        
        jQuery('#plugin_read_more_string').toggleClass('active');
       
    
    }
    
   
  // jQuery('.read_more_section .toggle_tag').toggleClass('active');
   
   jQuery( ".read_more_section .slider" ).click(function() {
  jQuery(this ).closest('.read_more_section').find('.toggle_tag').toggleClass('active');
});