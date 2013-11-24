
/**
 * Generic HTML5 History API implementation for Monolyth-based sites.
 *
 * The method should be called on a collection of anchors or forms, passing the
 * content element (jQuery object) to inject the new HTML into as a parameter.
 * While getting content the body has a 'html5loading' class appended, which
 * you could use to give visual feedback to the user.
 *
 * The optional second argument 'animation' can be a object containing callables
 * used to animate something before or after loading the new page (use 'before'
 * and 'after' keys for this). The default is to add the class 'html5loading'
 * to the body tag, and remove it again. Pass an empty object to prevent
 * anything from animating. The 'before' animation receives a single argument
 * (a callable) which is called after the animation ends.
 *
 * The optional third argument is an object of options (see below).
 *
 * After success, the history.Monolyth event is fired for additional processing.
 *
 * Links between different domains (which have cross-domain issues, and might
 * not be Monolyth sites anyway) are excluded, as are links with the special
 * data-history attribute set to 0 (e.g. a logout link, see below).
 *
 * Example usage:
 * <code>
 * $('body').on('click', 'a', function() {
 *     return $(this).history($('#content'), {
 *         before: function(fn) {
 *             // Add custom action/animation.
 *             fn();
 *         },
 *         after: function(fn) {
 *             fn = fn || function() {};
 *             // Add custom action/animation.
 *             fn();
 *         }
 *     });
 * });
 * </code>
 *
 * The special data-history attribute can be used to control the API's behaviour
 * further. If set to 0, the link or form is ignored entirely and treated as a
 * regular http-request.
 *
 * data-history can also contain a base64-encoded, json-encoded options object
 * with further instructions on how to handle this specific link (you could also
 * handle this in your onclick-method by manually passing in options as the
 * third argument, of course - whichever you prefer).
 *
 * Supported options (v0.46) are:
 * - refresh: the selector to refresh for this specific link. This is of course
 *   nearly identical in behaviour to the first argument, but is meant to allow
 *   overrides on a per-link basis. For example, when ALL links should refresh
 *   the entire content area, except one or two special ones.
 *   The selector can be passed as either a string or a jQuery-object.
 */
(function($) {
    $.fn.history = function(contentElement, animation, options) {
        if (!(window.history && window.history.pushState)) {
            return true;
        }
        options = options || {};
        var $this = $(this),
            form = $this.prop('tagName') == 'FORM',
            url = $this.attr(form ? 'action' : 'href'),
            test = url ? url.split('?').shift() : null,
            r1 = new RegExp('/$'),
            r2 = new RegExp('\.(html|php)$');
        // Forms with targets other than _self usually do something special
        // (e.g. upload an image in the background) and should be ignored.
        if (form && $this.attr('target') && $this.attr('target') != '_self') {
            return true;
        }
        if (!url) {
            return true;
        }
        var hd = $this.attr('data-history');
        if (hd === '0' ||
            url.substring(0, 1) == '#' ||
            (!r1.test(test) && !r2.test(test))
        ) {
            return true;
        }
        var imgloader = function() {
            $(this).removeClass('html5loading');
        };
        var domain = window.location.protocol + '//' + window.location.hostname,
            urltest = new RegExp('^' + domain);
        if ((url.substring(0, 1) != '/' && !urltest.test(url))
            || url.substring(0, 1) == '#'
        ) {
            // Ignore URLs on a different domain.
            return true;
        }
        animation = $.extend({
            before: function(fn) {
                $('body').addClass('html5loading html5loading-images');
                fn();
            },
            after: function(fn) {
                fn = fn || function() {};
                $('body').removeClass('html5loading');
                fn();
            }
        }, animation);
        var styles = $('head').html().match(/<link.*?>/g).join('\n');
        var parts = $('title').html().split(' - ');
        parts.pop();
        var initial = {
            body: contentElement.html(),
            title: parts.join(' - '),
            styles: styles,
            url: window.location.href
        };
        var render = function(data) {
            if (!data) {
                return;
            }
            contentElement.html(data.body);
            $('title').html(data.title);
            for (i in data) {
                if (i == 'body' || i == 'status' || i == 'styles') {
                    continue;
                }
                if (typeof options[i] != 'undefined') {
                    options[i](data[i]);
                }
            }
            window.Monolyth.scripts.execute(true);
            $('body').trigger('ready.MonolythHistory');
            $('head link').remove();
            $('head').append(data.styles);
            if (data.scripts) {
                if (typeof data.scripts != 'string') {
                    for (var i = 0; i < data.scripts.length; i++) {
                        Deputy.load(data.scripts[i]);
                    }
                } else {
                    Deputy.load(data.scripts);
                }
            }
        };
        window.addEventListener(
            'popstate',
            function(event) {
                var fn = animation.before || function(fn) { return fn(); };
                fn(function() {
                    render(event.state == null ? initial : event.state);
                    if (animation.after != undefined) {
                        (animation.after)();
                    }
                });
            }
        );
        function fixuri(l, url) {
            if (l == undefined) {
                return;
            }
            var m = 'redir';
            if (l.indexOf(m) != -1) {
                var p = l.split('?');
                var qa = p[1].split('&');
                var substrs = [];
                for (var i = 0; i < qa.length; i++) {
                    substrs.push(qa[i].substring(0, 5));
                    if (qa[i].split(/=/)[0] == m) {
                        qa[i] = m + '=' + encodeURIComponent(url);
                    }
                }
                l = p[0] + '?' + qa.join('&');
            }
            return l;
        };
        function success(data, url) {
            url = url.replace(/\?html5history&?/, '?').
                      replace(/&html5history&?/, '&').
                      replace(/\?&/, '?').
                      replace(/[\?&]$/, '');
            if (window.Monolyth.html5status &&
                window.Monolyth.html5status != data.status
            ) {
                window.location.replace(url);
                return;
            }
            window.Monolyth.html5status = data.status;
            data.url = url;
            data.body = window.Monolyth.Base64.decode(data.body);
            history[url] = data;
            render(data);
            $('img', contentElement).each(function() {
                var i = $(this), src = i.attr('src');
                i.attr('src', '');
                i.load(imgloader).attr('src', src);
            });
            if (animation.after != undefined) {
                (animation.after)();
            }
            var full = url;
            if (!url.match(/^https?:\/\//)) {
                // URL is not absolute (this will usually be the case), so we
                // need to force so redirects keep working.
                full = domain + url;
            }
            $('form').each(function() {
                var f = $(this);
                f.attr('action', fixuri(f.attr('action'), full));
            });
            $('a').each(function() {
                var $this = $(this);
                $this.attr('href', fixuri($this.attr('href'), full));
            });
            window.history.pushState(data, data.title, url);
        };
        function error(jqXHR, textStatus, errorThrown) {
            // Probably something got posted and wants a redirect.
            // There should be better ways to fix this, but not now :)
            window.location.replace(window.location.href);
        };
        var fn = animation.before || function(fn) { return fn(); };
        var ps = url.split(/#/);
        ps[0] += (ps[0].indexOf('?') == -1 ? '?' : '&') + 'html5history';
        if (form && $this.attr('method') == 'get') {
            ps[0] += '&' + $this.serialize();
        }
        url = ps.join('#');
        fn(function() {
            $.ajax({
                url: ps.join('#'),
                data: form && $this.attr('method') == 'post' ?
                    $this.serialize() :
                    {},
                method: form && $this.attr('method') == 'post' ? 'post' : 'get',
                success: function(data) { success(data, url) },
                error: error,
                dataType: 'json'
            });
        });
        return false;
    };
})(jQuery);

