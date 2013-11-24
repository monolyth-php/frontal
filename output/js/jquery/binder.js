
/**
 * Monolyth data binder.
 */
;(function($, Monolyth) {

Monolyth.extend('binder', function() {

var bound = {};

this.create = function(type, element, model) {
    bound[type] = bound[type] || [];
    var b = {
        element: element,
        model: model
    };
    b.template = $(
        '[data-binder-template="' + type + '"]',
        b.element
    ).first();
    bound[type].push(b);
};

this.update = function(type, element, data) {
    if (!(type in bound)) {
        return;
    }
    for (var i = 0, b; i < bound[type].length; i++) {
        if (bound[type][i].element.get(0) === element.get(0)) {
            b = bound[type][i];
            break;
        }
    }
    if (!b) {
        return;
    }
    if (typeof data == 'string') {
        data = [data];
    }
    var e = b.template.siblings(), u;
    for (var name in b.model) {
        u = name;
        break;
    }
    for (var i = 0; i < data.length; i++) {
        var s = b.template.clone(),
            c = e.filter('[data-binder-id=' + data[i][u] + ']');
        if (c.length) {
            s = c.first();
        }
        s.removeAttr('data-binder-template');
        for (var prop in data[i]) {
            if (!(prop in b.model)) {
                continue;
            }
            var h = b.model[prop];
            if (h == null) {
                continue;
            }
            if (typeof h == 'string') {
                h = [h];
            }
            for (var j = 0; j < h.length; j++) {
                var p = h[j].match(/\[(\w+?)(=.*?)?\]$/),
                    obj = h[j].substring(0, 1) == '&' ?
                        s :
                        s.find(h[j]);
                if (p && p.length) {
                    obj.attr(p[1], data[i][prop]);
                } else {
                    obj.html(data[i][prop]);
                }
            }
        }
        s.attr('data-binder-id', data[i][u]);
        if (b.template.attr('data-binder-append')) {
            b.template.before(s);
        } else {
            b.template.after(s);
        }
    }

    this.init();
};

this.init = function() {
    bound = {};
    cnt = 0;
    $('[data-binder]').each(function() {
        var $this = $(this), binders = $.parseJSON($this.attr('data-binder'));
        for (var name in binders) {
            Monolyth.binder.create(
                name,
                $this,
                binders[name]
            );
        }
    });
};

});

$(document).ready(Monolyth.binder.init);

})(window.jQuery, window.Monolyth);

