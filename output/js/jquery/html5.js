
/**
 * Diverse HTML5-enabling functions.
 *
 * @package monolyth
 * @author Marijn Ophorst <marijn@monomelodies.nl>
 * @copyright 2011, 2012 Monomelodies <http://www.monomelodies.nl>
 */
;(function($) {
    $('.oldie :first-child').addClass('first-child');
    $('.oldie :last-child').addClass('last-child');
    $('.oldie iframe').attr('allowtransparency', 'true');
    $.fn.setCustomValidity = function(msg) {
        return $(this).each(function() {
            this.validationMessage = msg;
            $(this).trigger('invalid');
        });
    };
    $.fn.html5 = function(options) {
        $(document).ready(function() {
            if (!('autofocus' in document.createElement('input'))) {
                var af = $(this).find('[autofocus]');
                if (af.length) {
                    af[0].focus();
                }
            }
            if (!('placeholder' in document.createElement('input'))) {
                $(this).submit(function() {
                    $(this).find('.html5-placeholder').
                            removeClass('html5-placeholder').
                            val('');
                });
                $(this).find('input, textarea').each(function(i, e) {
                    var $e = $(e);
                    if (!($e.attr('placeholder') &&
                        $e.attr('placeholder').length
                    )) {
                        return;
                    }
                    $e.focus(function() {
                        var $this = $(this);
                        if ($this.hasClass('html5-placeholder') ) {
                            $this.val('');
                            $this.removeClass('html5-placeholder');
                        }
                    });
                    $e.blur(function() {
                        var $this = $(this);
                        if (!$this.val().length) {
                            $this.addClass('html5-placeholder');
                            $this.val($this.attr('placeholder'));
                        }
                    });
                    $e.blur();
                });
            }
            $(this).bind("onFail", function(e, els) {
                $(this).find('input select textarea').blur();
            });
            if ($.tools && $.tools.validator) {
                $.tools.validator.fn(
                    "[data-equals]",
                    'Fields $1 and $2 must contain corresponding values.',
                    function(input) {
                        var name = input.attr('data-equals'),
                            field = this.getInputs().filter(
                                '[name="' + name + '"]'
                            );
                        return input.val() == field.val() ?
                            true :
                            [input.attr('name'), name];
                    }
                );
                $.tools.validator.fn(
                    "[data-notequals]",
                    'Fields $1 and $2 may not contain corresponding values.',
                    function(input) {
                        var name = input.attr('data-notequals'),
                            field = this.getInputs().filter(
                                '[name="' + name + '"]'
                            );
                        return input.val() != field.val() ?
                            true :
                            [input.attr('name'), name];
                    }
                );
            }
        });
    };
})(jQuery);

