
(function($) {
    $.fn.trackoutbound = function(code) {
        var re = /^http:\/\/.*?\//;
        try {
            var pageTracker = _gat._getTracker(code);
        } catch (err) {
            return this.each(function(){});
        }
        return this.each(function() {
            var $this = $(this);
            if (typeof $this.attr('href') != undefined &&
                $this.attr('href').length
            ) {
                var h1 = re.exec($this.attr('href'));
                var h2 = re.exec(window.location);
                if (h1 + '' != h2 + '') {
                    $this.click(function() {
                        pageTracker._trackEvent(
                            'Outbound links',
                            $this.attr('href')
                        );
                        return true;
                    });
                }
            }
        });
    };
})(jQuery);

