
(function($) {
    $.fn.lightbox = function() {
        return this.each(function() {
            $(this).attr('href', $(this).attr('href').replace(
                /\/([\w-]+)(\.\d+)?\.(jpe?g|png)$/,
                '/$1.' + ($(document.body).getHeight() - 200) + '.$3'
            );
        });
    }
})(jQuery);

