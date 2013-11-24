
(function($) {
    $.fn.links = function(method) {
        var methods = {
            init: function(options) {
            },
            targetBlank: function() {
                var re = /^http:\/\/.*?\//;
                return this.each(function() {
                    if (typeof $(this).attr('href') != 'undefined' &&
                        $(this).attr('href').length
                    ) {
                        var h1 = re.exec($(this).attr('href'));
                        var h2 = re.exec(window.location);
                        if (h1 + '' != h2 + '') {
                            $(this).click(function() {
                                window.open($(this).attr('href'));
                                return false;
                            });
                        }
                    }
                });
            },
            trackOutbound : function(code, newWindow) {
                var re = /^http:\/\/.*?\//;
                try {
                    var pageTracker = _gat._getTracker(code);
                } catch (err) {
                    return;
                }
                return $(this).each(function() {
                    if (typeof $(this).attr('href') != undefined &&
                        $(this).attr('href').length
                    ) {
                        var h1 = re.exec($(this).attr('href'));
                        var h2 = re.exec(window.location);
                        if (h1 && h1 + '' != h2 + '') {
                            $(this).click(function() {
                                pageTracker._trackEvent(
                                    'Outbound links',
                                    $(this).attr('href')
                                );
                                var e = $(this);
                                window.setTimeout(function() {
                                    if (newWindow) {
                                        window.open(e.attr('href'));
                                    } else {
                                        document.location = e.attr('href');
                                    }
                                }, 100);
                                return false;
                            });
                        }
                    }
                });
            },
            ajaxify : function(update) {
                return;
                var ac = $(document.body).find('._ac');
                if (!ac) {
                    return;
                }
                var l = window.location.toString();
                var domain = l.match(/^(https?:\/\/[a-zA-Z0-9-\.]+?\/)/)[1];
                l = l.replace(domain, '');
                if (l.length && update) {
                    // We want to persist filled out forms. You can place them
                    // outside the _ca-class element, but maybe that's not
                    // always viable.
                    var fc = {};
                    $(document.body).find('input, select, textarea').
                                     each(function(i, child) {
                            fc[$(child).attr('id')] = {
                                value: $(child).attr('value'),
                                checked: $(child).attr('checked'),
                                selected: $(child).attr('selected')
                            };
                        }
                    );
                    new Request.HTML({
                        update: ac,
                        filter: '._ac',
                        method: 'get',
                        url: l.split('#!').pop() + '?ajax',
                        onSuccess: function() {
                            Monolyth.core.links.ajaxify(false);
                            for (i in fc) {
                                if ($(i)) {
                                    for (j in {'value': 0, 'checked': 0, 'selected': 0}) {
                                        $(i).set(j, fc[i][j]);
                                    }
                                }
                            }
                        }
                    }).send();
                }
                $each($(document.body).getElements('a'), function(child) {
                    var h = $(child).get('href');
                    if (h == null) {
                        h = '/';
                    }
                    if (h.match(/\/#!\//)) {
                        return;
                    }
                    if (!h.match(/^https?:/)) {
                        h = domain + h;
                    }
                    var d = h.match(/^(https?:\/\/[a-zA-Z0-9-\.]+?\/)/);
                    if (!d) {
                        d = [h, h];
                    }
                    if (d[1] == domain) {
                        $(child).set('href', d[1] + '#!' + h.replace(d[1], ''));
                        $(child).click(function() {
                            window.location = '#';
                            window.location = $(this).attr('href');
                            Monolyth.core.links.ajaxify(true);
                            return false;
                        });
                    }
                });
            }
        };

        if (methods[method]) {
            return methods[method].apply(
                this,
                Array.prototype.slice.call(arguments, 1)
            );
        } else if (typeof method === 'object' || !method) {
            return methods.init.apply(this, arguments);
        } else {
            $.error('Method ' + method + 'does not exists on jQuery.monolink');
        }
    }
})(jQuery);

