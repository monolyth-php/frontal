
(function($) {
    $.fn.blank = function() {
        var re = /^http:\/\/.*?\//;
        return this.each(function() {
            var $this = $(this);
            if (typeof $this.attr('href') != 'undefined' &&
                $this.attr('href').length
            ) {
                var h1 = re.exec($this.attr('href'));
                var h2 = re.exec(window.location);
                if (h1 && h2 && h1 + '' != h2 + '') {
                    $this.click(function() {
                        window.setTimeout(function() {
                            window.open($this.attr('href'));
                        }, 100);
                        return false;
                    });
                }
            }
        });
    };
})(jQuery);

