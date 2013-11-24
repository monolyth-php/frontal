
if (!('getWeek' in Date)) {
    // Source: http://javascript.about.com/library/blweekyear.htm
    // Improved by yours truly to correctly detect ISO week 1.
    Date.prototype.getWeek = function() {
        var onejan = new Date(this.getFullYear(), 0, 1);
        // While onejan isn't a thursday, the week hasn't started yet.
        while (onejan.getDay() > 4) {
            onejan.setDate(onejan.getDate() + 1);
        }
        var week = Math.ceil(
            (
                ((this - onejan) / 86400000) +
                onejan.getDay() + 1
            ) / 7
        );
        if (week < 1) {
            week = (new Date(this.getFullYear() - 1, 11, 31)).getWeek();
        }
        return week;
    }
}

(function($) {
    $.fn.setCustomValidity = function(msg) {
        return $(this).each(function() {
            this.validationMessage = msg;
            $(this).trigger('invalid');
        });
    };
    var makeRequired = function(e, fns) {
        e.bind('html5-invalid-required', function() {
            $(this).addClass('validate-required');
        });
        e.bind('html5-valid-required', function() {
            $(this).removeClass('validate-required');
        });
        fns.required = function(el) {
            if (!el.val().trim().length || 
                el.hasClass('html5-placeholder')
            ) {
                el.trigger('html5-invalid-required');
            } else {
                el.trigger('html5-valid-required');
            }
        };
        return fns;
    }
    $.fn.html5form = function(options) {
        // "Private" functions with some shorthands.
        var bindEvents = function(e) {
            e.bind('invalid', function() {
                $(this).addClass('validate-fail');
            });
            e.bind('html5-valid', function() {
                $(this).removeClass('validate-fail');
            });
        };
        var bindHandling = function(e, functions) {
            e.bind(options.events, function() {
                for (i in functions) {
                    var fn = functions[i];
                    fn($(this));
                }
                var cs = false;
                if ($(this).attr('class')) {
                    cs = $(this).attr('class').match(/\bvalidate-\w+\b/g);
                }
                if (!cs || (cs.length == 1 && cs[0] == 'validate-fail')) {
                    $(this).trigger('html5-valid');
                } else {
                    $(this).trigger('invalid');
                }
            });
        };
        var defaults = {
            events: 'blur change',
            submit: {
                success: function(form) {
                    // You can do your mojo here, for instance submit the
                    // form using Ajax.
                    return true;
                },
                failure: function(form) {
                    // You can handle failure here, too. For instance, display
                    // all encountered errors (if you choose not to use the
                    // default blur/change events for checking).
                    return false;
                }
            },
            date: {
                // Override how months should be displayed. It's a zero-based
                // array where 0 = january, 1 = february etc. You can localize
                // the datepicker from your initialization.
                months: [
                    'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
                    'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec',
                ],
                weeks: 'wk',
                // Idem for the days.
                days: ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa'],
                // Content for "today" button.
                today: 'Today',
                // Override the default datepicker. For instance, you could
                // prefer jQuery-UI to do the lifting. If set, it should be of
                // the type function(el, type, functions) { ... }, where the
                // function doing the validation should be added to the
                // functions-object so it can be passed back.
                picker: function(e, type, functions) {
                    var f = e.parent('form');
                    if (type == 'date' || type == 'datetime' ||
                        type == 'datetime-local' || type == 'month' ||
                        type == 'week'
                    ) {
                        var name = e.attr('name');
                        var curr = e.val();
                        var isRequired = e.attr('required') != undefined;
                        e.replaceWith(
                            '<select name="' + name + '"' + (isRequired ?
                                ' required' : '') + '>' +
                            '<option value="" selected></option></select>'
                        );
                        e = $('form select[name=' + name + ']');
                        if (isRequired) {
                            functions = makeRequired(e, functions);
                        }
                        e.addClass('html5-date');
                        bindEvents(e);
                        e.focus(function() {
                            var e = $(this);
                            var name = e.attr('name');
                            var current = e.val();
                            var date = false;
                            if (current.length) {
                                var parts = current.split('-');
                                if (!parts[2]) {
                                    parts.push(1);
                                }
                                var curmonth = parts[1];
                                var curdate = parts[2];
                                if (type == 'week') {
                                    var onejan = new Date(parts[0], 0, 1);
                                    while (onejan.getDay()) {
                                        onejan.setDate(onejan.getDate() + 1);
                                    }
                                    curdate = onejan.getDate() +
                                        (curmonth.substring(1) - 1) * 7 + 1;
                                    curmonth = 0;
                                } else {
                                    curmonth--;
                                }
                                date = new Date(
                                    parts[0],
                                    curmonth,
                                    curdate,
                                    1
                                );
                            }
                            $(this).blur();
                            var curs = $('.html5-datepicker');
                            if (curs.length) {
                                curs.fadeOut(400, function() {
                                    curs.remove();
                                });
                                return;
                            }                        
                            $(this).blur();
                            var pos = $(this).offset();
                            var d = $(document.createElement('DIV'));
                            d.addClass('html5-datepicker');
                            if (type == 'week') {
                                d.addClass('html5-week');
                            }
                            if (type == 'month') {
                                d.addClass('html5-month');
                            }
                            d.css({
                                left: pos.left,
                                top: pos.top + $(this).height()
                            });
                            var month = date ?
                                date.getMonth() :
                                (new Date()).getMonth();
                            var year = date ?
                                date.getFullYear() :
                                (new Date()).getFullYear();
                            var day = date ?
                                date.getDate() :
                                (new Date()).getDate();
                            var fillmonth = function(year, month, day) {
                                var c = new Date(year, month, 1);
                                var p = new Date(
                                    year,
                                    month,
                                    typeof day == 'undefined' ? 1 : day
                                );
                                var curweek = current.length ?
                                    p.getWeek() :
                                    undefined;
                                p.setDate(c.getDate() - 1);
                                var o = c.getDay();
                                var str = '';
                                var ws = c.getWeek();
                                if (type == 'week' || type == 'month') {
                                    day = undefined;
                                }
                                str += '<tr' + (type == 'week' &&
                                    ws == curweek ?
                                        ' class="selected"' : '') +
                                    '>';
                                if (type == 'week') {
                                    str += '<td class="week">' +
                                        ws + '</td>';
                                }
                                for (var i = o; i > 0; i--) {
                                    str += '<td class="disabled">' +
                                        (p.getDate() - i + 1) + '</td>';
                                }
                                for (var i = 0; i < 7 - o; i++) {
                                    str += '<td' + (day && day == i + 1 ?
                                            ' class="selected"' :
                                            ''
                                        ) + '><a href="#">' + (i + 1) + '</td>';
                                }
                                str += '</tr>';
                                if (ws >= 52) {
                                    ws = 0;
                                }
                                // Figure out last day of the current month.
                                var p = new Date(year, month, 27);
                                while (p.getMonth() == month) {
                                    p.setDate(p.getDate() + 1);
                                }
                                p.setDate(p.getDate() - 1);
                                var end = p.getDate();
                                var cnt = 0;
                                for (var i = 7 - o; i < end; i++) {
                                    if (cnt % 7 == 0) {
                                        ws++;
                                        str += '<tr' + (type == 'week' &&
                                            ws == curweek ?
                                                ' class="selected"' : '') +
                                            '>';
                                        if (type == 'week') {
                                            str += '<td class="week">' +
                                                ws + '</td>';
                                        }
                                    }
                                    str += '<td' +
                                        (day && day == i + 1 ?
                                            ' class="selected"' :
                                            ''
                                        ) + '><a href="#">' + (i + 1) + '</td>';
                                    if (cnt % 7 == 6) {
                                        str += '</tr>';
                                    }
                                    cnt++;
                                }
                                var pad = 42 - end - o;
                                for (i = 0; i < pad; i++) {
                                    if ((end + o + i) % 7 == 0) {
                                        str += '<tr>';
                                        if (type == 'week') {
                                            // If an entire row is filler, reset
                                            // the week number if we're in the
                                            // new year already.
                                            if (ws >= 52) {
                                                ws = 0;
                                            }
                                            str += '<td class="week">' +
                                                (++ws) + '</td>';
                                        }
                                    }
                                    str += '<td class="disabled">' + (i + 1) +
                                        '</td>';
                                    if ((end + o + i) % 7 == 6) {
                                        str += '</tr>';
                                    }
                                }
                                var selected = '';
                                if (type == 'month' && current.length) {
                                    var parts = current.split('-');
                                    if (parseInt(parts[0]) + '' == year + '' &&
                                        (parseInt(parts[1]) - 1) + '' == month + ''
                                    ) {
                                        selected = ' selected';
                                    }
                                }
                                return '<table class="html5-datepicker-month' +
                                    selected + '">' +
                                    '<thead><tr>' +
                                        (type == 'week' ?
                                            '<th>' + options.date.weeks +
                                            '</th>' :
                                        ''
                                    ) +
                                    '<th>' + options.date.days[0] + '</th>' +
                                    '<th>' + options.date.days[1] + '</th>' +
                                    '<th>' + options.date.days[2] + '</th>' +
                                    '<th>' + options.date.days[3] + '</th>' +
                                    '<th>' + options.date.days[4] + '</th>' +
                                    '<th>' + options.date.days[5] + '</th>' +
                                    '<th>' + options.date.days[6] + '</th>' +
                                '</tr></thead>' +
                                '<tbody>' + str +
                                '</tbody>' +
                                '</table>';

                            };
                            d.html(
                                '<div class="html5-datepicker-monthcycle">' +
                                '<a class="prev" href="#">&#9668;</a>' +
                                '<span data-value="' + month + '">' +
                                options.date.months[month] +
                                '</span>' +
                                '<a class="next" href="#">&#9658;</a>' +
                                '</div>' +
                                '<div class="html5-datepicker-yearcycle">' +
                                '<a class="prev" href="#">&#9668;</a>' +
                                '<input type="number" value="' + year + '"' +
                                ' size="4" maxlength="5"/>' +
                                '<a class="next" href="#">&#9658;</a>' +
                                '</div>' +
                                fillmonth(year, month, day) +
                                '<form><button type="button">' +
                                    options.date.today + '</button></form>'
                            );
                            var ud = function() {
                                var cm = parseInt(
                                    $('.html5-datepicker-monthcycle span').
                                        attr('data-value')
                                ) + 1 + '';
                                var cd = $(this).html();
                                var i = $('.html5-datepicker input');
                                switch (type) {
                                    case 'week':
                                        var val = parseInt(i.val()) +
                                            '-W' + (new Date(parseInt(i.val()),
                                                parseInt(cm) - 1, parseInt(cd)
                                            )).getWeek();
                                        break;
                                    default:
                                        if (cm.length == 1) {
                                            cm = '0' + cm;
                                        }
                                        if (cd.length == 1) {
                                            cd = '0' + cd;
                                        }
                                        var val = parseInt(i.val()) + '-' + cm;
                                        if (type != 'month') {
                                            val += '-' + cd;
                                        }
                                        break;
                                }
                                e.children().val(val).html(val);
                                e.focus();
                                e.blur();
                                return false;
                            };
                            d.find('.html5-datepicker-monthcycle a').click(
                                function() {
                                    var mod = $(this).hasClass('next') ? 1 : -1;
                                    var s = $(this).parent('div').find('span');
                                    var date = new Date(
                                        d.find('input').val(),
                                        parseInt(s.attr('data-value')) + mod
                                    );
                                    s.html(options.date.months[
                                        date.getMonth()
                                    ]);
                                    d.find('table').replaceWith(fillmonth(
                                        date.getFullYear(),
                                        date.getMonth()
                                    ));
                                    d.find('table a').click(ud);
                                    s.attr('data-value', date.getMonth());
                                    d.find('input').val(date.getFullYear());
                                    return false;
                                }
                            );
                            var uy = function() {
                                var mod = 0;
                                if ($(this).hasClass('next')) {
                                    mod = 1;
                                }
                                if ($(this).hasClass('prev')) {
                                    mod = -1;
                                }
                                var i = d.find('input');
                                if (!i.val().length) {
                                    return false;
                                }
                                i.val(parseInt(i.val()) + mod);
                                var s = $(this).
                                    parents('div.html5-datepicker').
                                    find('span');
                                d.find('table').replaceWith(fillmonth(
                                    parseInt(i.val()),
                                    parseInt(s.attr('data-value'))
                                ));
                                d.find('table a').click(ud);
                                return false;
                            }
                            d.find('.html5-datepicker-yearcycle a').click(uy);
                            d.find('.html5-datepicker-yearcycle input').bind(
                                'blur change keyup',
                                uy
                            );
                            d.find('table a').click(ud);
                            d.find('button').click(function() {
                                var d = new Date();
                                var val = d.getFullYear();
                                switch (type) {
                                    case 'week':
                                        val += '-W' + d.getWeek();
                                        break;
                                    default:
                                        var cm = d.getMonth() + 1 + '';
                                        if (cm.length == 1) {
                                            cm = '0' + cm;
                                        }
                                        val += '-' + cm;
                                        if (type != 'month') {
                                            var cd = d.getDate() + '';
                                            if (cd.length == 1) {
                                                cd = '0' + cd;
                                            }
                                            val += '-' + cd;
                                        }
                                }
                                e.children().val(val).html(val);
                                e.focus();
                                e.blur();
                            });
                            d.css({display: 'none'});
                            $(document.body).append(d);
                            d.fadeIn();
                        });
                    }
                    if (type == 'time' || type == 'datetime'
                        || type == 'datetime-local'
                    ) {
                        functions.time = function(e) {
                            var test = e.val().trim();
                            if (!test.match(/\d{2}:\d{2}/)) {
                                e.trigger('html5-invalid-time');
                                return;
                            }
                            var parts = test.split(':');
                            if (parseInt(parts[0]) < 0 ||
                                parseInt(parts[0]) > 23 ||
                                parseInt(parts[1]) < 0 ||
                                parseInt(parts[1]) > 59
                            ) {
                                e.trigger('html5-invalid-time');
                                return;
                            }
                            e.trigger('html5-valid-time');                                
                        };
                        if (e.is('select')) {
                            var d = e;
                            var e = $(document.createElement('INPUT'));
                            d.attr('name', name + '[date]');
                            e.attr('name', name + '[time]');
                            e.attr('type', 'time');
                            e.insertAfter(d);
                            bindEvents(e);
                            bindHandling(e, functions);
                        }
                        if (curr) {
                            var date = new Date(curr);
                            e.val(date.getHours() + ':' + date.getMinutes());
                        } else {
                            e.val('00:00');
                        }
                        e.addClass('html5-time');
                        e.bind('html5-invalid-time', function() {
                            $(this).addClass('validate-time');
                        });
                        e.bind('html5-valid-time', function() {
                            $(this).removeClass('validate-time');
                        });
                    }
                    return functions;
                }
            },
            types: {},
            patterns: {
                email: "^[a-z0-9!#$%&'*+/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&'*+/"
                     + "=?^_`{|}~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\."
                     + ")+(?:[A-Z]{2}|com|org|net|edu|gov|mil|biz|info|mobi"
                     + "|name|aero|asia|jobs|museum)$",
                url: "^(https?|ftp)://(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)"
                   + "+(?:[A-Z]{2}|com|org|net|edu|gov|mil|biz|info|mobi|na"
                   + "me|aero|asia|jobs|museum)$",
                number: "^[0-9]+(\.[0-9]+)?$"
            }
        };
        options = $.extend(true, defaults, options);
        for (i in options.patterns) {
            options.patterns[i] = new RegExp(options.patterns[i], 'i');
        }
        var behaviors = [
            'autofocus', 'required', 'pattern', 'min', 'max', 'placeholder'
        ];
        for (var i = 0; i < behaviors.length; i++) {
            options[behaviors[i]] = behaviors[i] in
                document.createElement('input');
        }
        var inputs = [
            'search', 'tel', 'url', 'email', 'datetime', 'date', 'month',
            'week', 'time', 'datetime-local', 'number', 'color', 'range'
        ];
        for (var i = 0; i < inputs.length; i++) {
            options.types[inputs[i]] = false;
            var input = document.createElement('input');
            input.setAttribute('type', inputs[i]);
            var notText = input.type !== 'text';
            if (notText) {
                if (input.type !== 'search' && input.type !== 'tel') {
                    input.value = 'testing';
                    if (input.value !== 'testing') {
                        options.types[inputs[i]] = true;
                    }
                } else {
                    options.types[inputs[i]] = true;
                }
            }
        }
        return this.each(function() {
            var f = $(this);
            f.bind('invalid', function() {
                $(this).addClass('validate-fail');
            });
            f.bind('html-valid', function() {
                $(this).removeClass('validate-fail');
            });
            // First of all, check if we should actually do anything.
            if (f.attr('novalidate') != undefined) {
                return;
            }
            // Add special html5-$type classes to the unsupported elements, so
            // we may identify them later.
            var unsupported = '';
            for (type in options.types) {
                if (!options.types[type]) {
                    unsupported += (unsupported.length ? '|' : '') + type;
                }
            }
            var match = '<input.*?type="?(' + unsupported + ')\\b.*?>';
            var els = f.html().match(new RegExp(match, 'ig'));
            if (els) {
                for (var i = 0; i < els.length; i++) {
                    var tc = els[i].match(/class="?(.*?)\b/ig);
                    var type = els[i].match(/type=("{0,1}.*?)[^a-z0-9\s-]/ig)[0];
                    type = type.replace(/^type="?(.*?)"?$/, '$1');
                    var cls = 'html5-' + type;
                    var fixed = '';
                    if (tc) {
                        fixed = els[i].replace(tc, tc + cls + ' ');
                    } else {
                        fixed = els[i].replace(
                            /(\/?>)/,
                            ' class="' + cls + '"$1'
                        );
                    }
                    f.html(f.html().replace(els[i], fixed));
                }
            }
            f.submit(function() {
                var ok = true;
                f.find('input, textarea, select').each(function(i, element) {
                    var e = $(element);
                    // Force a random event from the defined list to trigger
                    // validation.
                    e.trigger(options.events.split(' ').shift());
                    if (e.attr('class') &&
                        e.attr('class').match(/\bvalidate-[a-z]+\b/)
                    ) {
                        ok = false;
                    }
                });
                if (ok) {
                    f.trigger('html5-valid');
                    return options.submit.success(f);
                } else {
                    f.trigger('invalid');
                    return options.submit.failure(f);
                }
            });
            f.find('input, textarea, select').each(function(i, element) {
                var e = $(element);
                var fns = {};
                bindEvents(e);
                if (!options.placeholder &&
                    e.attr('placeholder') != undefined
                ) {
                    if (!e.val().length) {
                        e.focus(function() {
                            $(this).removeClass('html5-placeholder');
                            $(this).val('');
                        });
                        e.blur(function() {
                            if (!e.val().length) {
                                e.addClass('html5-placeholder');
                                e.val(e.attr('placeholder'));
                            }
                        });
                        e.blur();
                    }
                }
                if (!options.autofocus && e.attr('autofocus') != undefined) {
                    e.focus();
                }
                if (!options.required && e.attr('required') != undefined) {
                    fns = makeRequired(e, fns);
                }
                var type = e.attr('type');
                if (e.is('input')
                    && !options.pattern
                    && e.attr('pattern')
                ) {
                    e.bind('html5-invalid-pattern', function() {
                        $(this).addClass('validate-pattern');
                    });
                    e.bind('html5-valid-pattern', function() {
                        $(this).removeClass('validate-pattern');
                    });
                    fns.pattern = function(el) {
                        var test = el.val().trim();
                        if (test.length && !test.match(el.attr('pattern'))) {
                            el.trigger('html5-invalid-pattern');
                        } else {
                            el.trigger('html5-valid-pattern');
                        }
                    };
                }
                if ((e.is('input') || e.is('textarea'))
                    && e.attr('maxlength')
                    && e.attr('maxlength').length
                ) {
                    e.bind('html5-invalid-maxlength', function() {
                        $(this).addClass('validate-maxlength');
                    });
                    e.bind('html5-valid-maxlength', function() {
                        $(this).removeClass('validate-maxlength');
                    });
                    fns.maxlength = function(el) {
                        if (el.val().trim().length > el.attr('maxlength')) {
                            el.trigger('html5-invalid-maxlength');
                        } else {
                            el.trigger('html5-valid-maxlength');
                        }
                    };
                }
                if (e.is('input') && type == 'text') {
                    // Non-supporting browsers will return text in every case.
                    // We cater for this by adding html5-$type as a class,
                    // e.g. <input type="email" class="html5-email">
                    var cs = null;
                    if (type == 'text') {
                        if (e.attr('class') &&
                            (cs = e.attr('class').match(/\bhtml5-(\w+)\b/))
                        ) {
                            type = cs[1];
                        }
                    }
                    if (options.patterns[type] && !options[type]) {
                        e.bind('html5-invalid-' + type, function() {
                            $(this).addClass('validate-' + type);
                        });
                        e.bind('html5-valid-' + type, function() {
                            $(this).removeClass('validate-' + type);
                        });
                        fns[type] = function(el) {
                            var test = el.val().trim();
                            if (test.length &&
                                !options.patterns[type].test(test)
                            ) {
                                el.trigger('html5-invalid-' + type);
                            } else {
                                el.trigger('html5-valid-' + type);
                            }
                        }
                    }
                    switch (type) {
                        case 'number':
                        case 'range':
                            var tests = {
                                min: e.attr('min'),
                                max: e.attr('max'),
                                step: e.attr('step')
                            };
                            for (t in tests) {
                                if (tests[t] != undefined) {
                                    e.bind('html5-invalid-' + t, function() {
                                        $(this).addClass('validate-' + t);
                                    });
                                    e.bind('html5-valid-' + t, function() {
                                        $(this).removeClass('validate-' + t);
                                    });
                                    fns[t] = function(el) {
                                        var test = el.val().trim();
                                        if (test.length &&
                                            options.patterns.number.test(test)
                                        ) {
                                            var e = null;
                                            switch (t) {
                                                case 'min':
                                                    e = test < tests[t];
                                                    break;
                                                case 'max':
                                                    e = test > tests[t];
                                                    break;
                                                case 'step':
                                                    e = test % tests[t];
                                                    break;
                                            }
                                            if (e) {
                                                el.trigger('html5-invalid-' + t);
                                            } else {
                                                el.trigger('html5-valid-' + t);
                                           }
                                       }
                                    };
                                }
                            }
                            break;
                        case 'date':
                        case 'month':
                        case 'week':
                        case 'time':
                        case 'datetime':
                        case 'datetime-local':
                            fns = $.extend(
                                fns,
                                options.date.picker(e, type, fns)
                            );
                            break;
                    }
                }
                bindHandling(e, fns);
            });
        });
    };
})(jQuery);

