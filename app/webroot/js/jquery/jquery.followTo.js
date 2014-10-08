(function($){
    $.fn.followTo = function ( pos ) {
        var $this = this,
            $window = $(window), 
            offset;

        if (pos !== false) {
            offset = $this.offset();

                    $this.data('offset', offset);

                    $window.bind('scroll.followTo', function(e){
                            if ($window.scrollTop() > pos) {
                                    $this.css({
                                            position: 'absolute',
                                            top: pos
                                    });
                            } else {
                                    $this.css({
                                            position: 'fixed',
                                            top: offset.top
                                    });
                            }
                    });

        } else {

                    offset = $this.data('offset');

                    $window.unbind('scroll.followTo');

                    if (offset) {
                        $this.css({
                                position: 'absolute',
                                top: offset.top
                        });		
                    }

        }

    };    
    
    
})(jQuery);