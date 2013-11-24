
/**
 * Monolyth javascript i18n handler.
 *
 * Prior to Monolyth 0.47, this was in the core Javascript.
 *
 * @package monolyth
 * @author Marijn Ophorst <marijn@monomelodies.nl>
 * @copyright 2013 Monomelodies <http://www.monomelodies.nl>
 */

window.Monolyth.extend('text', function() {

    var store = [];

    this.get = function(id) {
        for (var i = 0; i < store.length; i++) {
            if (store[i][0] == id) {
                return store[i][1];
            }
        }
        return id;
    };

    this.load = function(ids) {
        var $this = this;
        $.post(
            '/monolyth/gettext',
            {ids: ids},
            function(data) {
                $this.setup(data);
            },
            'json'
        );
    };

    this.setup = function(texts) {
        for (var i = 0; i < texts.length; i++) {
            store.push(texts[i]);
        }
    };

});

