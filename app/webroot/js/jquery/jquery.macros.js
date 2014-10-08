/*
 * 
 * OneTouch EMR Macro jQuery Plugin
 * 
 * Dependencies:
 * - jquery.insertAtCaret.js (for insertAtCaret() method)
 * - jquery.maskedinput-1.3.js (for caret() method)
 * 
 * 
 * 
 */

(function(){
  jQuery.fn.macros = function(options){
    var settings = jQuery.extend({
      'target': '',
      macros: {},
      'class': 'macro-select'
    }, options);
    
    
    if (jQuery.isEmptyObject(settings.macros)) {
      settings.macros = MacrosArr || {};
    }
    
    
    return this.each(function(){

      if ($.isEmptyObject(settings.macros) || !settings.target) {
        return true;
      }
      
      var 
        lastCaret = 0,
        macros = null, 
        input = jQuery(settings.target);
      
                
      input.blur(function(){
          lastCaret = jQuery(this).caret();
      });      
      
      
      macros = $('<select />').addClass(settings.class).append('<option value="">...</option>');

      macros.append($.map(MacrosArr, function(val, i) {
       return $('<option />').attr('value', val).html(i);
      }));

      macros
          .change(function(){
              var val = $(this).val();

              if (!val) {
                  return true;
              }

              input.caret(lastCaret);
              input.insertAtCaret(val);
              input.caret(lastCaret.begin, lastCaret.begin + val.length);
          });
          
       jQuery(this).append(macros);
            
    });
  };
})();