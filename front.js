 jQuery(document).ready(function($) {
 
    $( "form[name^='anketa_']" ).submit(function( event ) {
      if (( $(this).find( "input:checked" ).val() == null ) == ( ($(this).find( "input[name='alt_odpoved']").length != 1) || ($(this).find( "input[name='alt_odpoved']").val() == "")   )) {    //XOR
        $(".anketa_field").show().fadeOut( 4000 );
        event.preventDefault();
      } 
      return;
    });
    
  $("input[name='alt_odpoved']").click(function() {
    $('input[name=odpoved]').attr('checked',false); 
  });  
   
  $("input[name=odpoved]").click(function() {
    $('input[name=alt_odpoved]').val(''); 
  });  
  
  $("a[name='show']").click(function(){ $(this).next().show(); $(this).hide(); return false; });  
});