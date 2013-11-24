
/**
 * Some very standard jQuery fixes.
 *
 * @package monolyth
 * @author Marijn Ophorst <marijn@monomelodies.nl>
 * @copyright 2013 Monomelodies <http://www.monomelodies.nl>
 */

;(function(scripts, $) {

if (!($ && scripts)) {
    return;
}
scripts.push(function() {

$('form').on('keyup change', '.placeholder_text', function() {
    var $this = $(this);
    if ($this.val().replace(/\s+/, '').length) {
        $this.removeClass('placeholder_text');
    } else {
        $this.addClass('placeholder_text');
    }
});
$('[name=act_submit]').click(function() {
    $('.placeholder_text', $(this).parents('form')).each(function() {
        var e = $(this);
        if (e.val() == e.attr('placeholder')) {
            e.val('');
        }
    });
});
$.fn.filled = function() {
    var v = this.val();
    if ((typeof v).toLowerCase() != 'string') {
        return false;
    }
    v = v.replace(/\s+/, '');
    if (this.hasClass('placeholder_text') && v == this.attr('placeholder')) {
        return false;
    }
    return v.length ? true : false;
};

/**
 * Some default form fixing stuff. Pass a string to checkRequired to display
 * a modal alert with that string for non-supporting browsers. Pass a
 * callback to call that instead (e.g. add classes or something).
 */
$.fn.checkRequired = function(action) {
    return this.each(function() {
        $('[name=act_submit]', this).click(function() {
            $(this).attr('disabled', true);
            if (window.Modernizr.input.required) {
                $(this).removeAttr('disabled');
                return true;
            }
            // If it's a fake placeholder, remove value.
            var f = $(this).parents('form');
            $('[required]', f).each(function(i, e) {
                var $e = $(e);
                if (!$e.filled()) {
                    $e.addClass('monolyth-required');
                } else {
                    $e.removeClass('monolyth-required');
                }
            });
            if ($('.monolyth-required', f).length) {
                if (action) {
                    if (typeof action === 'function') {
                        return action();
                    }
                    window.alert(action);
                }
                $(this).removeAttr('disabled');
                $('.placeholder_text').each(function() {
                    var $this = $(this);
                    $this.val($this.attr('placeholder'));
                });
                return false;
            }
            $(this).removeAttr('disabled');
            return true;
        });
    });
};

/**
 * fadeAway acts as jQuery's standard fadeOut, only actually removes the
 * target from the DOM after the operation completes. This is such a common
 * pattern I'm surprised it's not in the core...
 *
 * @param mixed speed Optional animation speed in milliseconds.
 * @param function callback Optional callback after animation completes.
 * @see http://docs.jquery.com/Effects/fadeOut
 */
$.fn.fadeAway = function(speed, callback) {
    return this.each(function() {
        $(this).fadeOut(speed, function() {
            if (typeof callback == 'function') {
                callback();
            }
            $(this).remove();
        });
    });
};

});

})(window.Monolyth ? window.Monolyth.scripts || undefined, window.jQuery);

