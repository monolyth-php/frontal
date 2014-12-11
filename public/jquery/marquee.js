
(function($) {
    $.fn.marquee = function(timemout) {
        if (typeof timeout == 'undefined') {
            timeout = 20;
        }
        if (document.all) {
            timeout = parseInt(timeout / 2);
        }
        return this.each(function() {
            var width = 0;
            var s = $(this).children('div');
            s.children().each(function(i, el) {
                width += el.width + 45;
            });
            s.css('width', width * 2);
            s.html(s.html() + s.html());
            var pos = 0;
            var slide = function() {
                pos--;
                if (pos == -width) {
                    pos = 0;
                }
                s.css('margin', '0 0 0 ' + pos + 'px');
                window.setTimeout(slide, timeout);
            };
            window.setTimeout(slide, timeout);
        });
    };
})(jQuery);

