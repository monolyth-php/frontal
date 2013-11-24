
(function($) {
    $.fn.antispam = function() {
        return this.each(function() {
            var $this = $(this);
            try {
                var ps = $this.attr('href').substring(1).split(',');
                if (ps.length != 2) {
                    return;
                }
                var b = Monolyth.Base64;
                var uri = decodeURIComponent(b.decode(ps[0]));
                $this.attr('href', uri.removeEntities());
                $this.html(decodeURIComponent(b.decode(ps[1])));
            } catch (err) {
            }
        });
    };
})(jQuery);

