
(function($) {
    $.fn.cookiecontrol = function(options) {
        if (Monolyth.cookies().queried || !Monolyth.cookies().accept) {
            return this;
        }
        var ns = '\\monolyth\\utils\\cookies/';
        var settings = $.extend({
            opacity: .1,
            id: 'cookies',
            className: '',
            leader: '<h1>' + Monolyth.text.get(ns + 'title') + '</h1>' +
                Monolyth.text.get(ns + 'explain'),
            cookies: {submit: Monolyth.text.get(ns + 'options/submit')}
        }, options);
        return this.each(function() {
            var $this = $(this);
            $this.append('<form id="' + settings.id + '" class="' +
                settings.className + '"/>');
            var c = $('#' + settings.id);
            c.css({position: 'fixed', left: 0, right: 0, bottom: 0});
            c.append(settings.leader);
            c.append('<div class="buttons"><button type="button">' +
                settings.cookies.submit + '</div>');
            $('button', c).click(function() {
                $.post(
                    '/monolyth/cookiestore/',
                    {e: 1},
                    function(data) { window.location.reload(); }
                );
                c.slideDown(200, function() { $(this).remove(); });
                return false;
            });
            $this.animate({'padding-bottom': c.outerHeight() + 10});
        });
    };
})(jQuery);

