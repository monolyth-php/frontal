
window.Monolyth = (function(w, d) {
    var e = (!+"\v1"), h = d.getElementsByTagName('head')[0];
    d.c = d.createElement;
    if (e) {
        var ef = function() {
            var t =
'article,aside,canvas,details,figcaption,figure,footer,header,menu,nav,section,time'.
                split(',');
            for (var i = 0; i < t.length; i++) {
                d.c(t[i]);
            }
        };
        ef();
    }
    this.ie = function() { return e; };
    this.html5 = ef;
    this.httpimg = '{$HTTPIMG}';
    this.language = {$LANGUAGE};

    /**
     * Use the extend method to augment Monolyth's default functionality.
     *
     * @param string name The name of the extension (e.g. 'foo' will result in
     *                    Monolyth.foo existing).
     * @param function body The body of your extension. this maps to the new
     *                      extension.
     */
    this.extend = function(name, body) {
        this[name] = (function(prop) {
            body.call(prop);
            return prop;
        })(this[name] || {});
    };

    /**
     * We'll (virtually) always need the basic script handler, so include it as
     * an inline extension here.
     */
    this.extend('scripts', function() {
        var storage = [[], []];
        
        /**
         * 'push' some code onto the stack.
         *
         * @param function code The function to call later.
         */
        this.push = function(code) {
            storage[0].push(code);
        };
        
        /**
         * Identical to push, only these functions ALWAYS get executed after
         * any other functions in the stack (useful if dependencies are
         * present).
         *
         * @param function code The function to call eventually.
         */
        this.eventually = function(code) {
            storage[1].push(code);
        };
        
        /**
         * Execute all defined deferred scripts.
         *
         * @param bool force If true, the scripts execute immediately instead of
         *                   (when possible) on document.ready.
         */
        this.execute = function(force) {
            var exec = function() {
                for (var i = 0; i < storage.length; i++) {
                    while (storage[i].length) {
                        (storage[i].shift())();
                    }
                }
            };
            if (force || !jQuery) {
                exec();
            } else {
                jQuery(document).ready(exec);
            }
        };
    });

    var ca = false,
        co = 1,
        cq = document.cookie.match(/mocoqu=/) ? true : false;

    this.cookies = function() {
        return {
            accept: ca,
            ok: co,
            queried: cq,
            TECHNICAL: 1,
            FIRSTPARTY: 2,
            THIRDPARTY: 4,
            ANALYTICS: 8,
            set: function(a, o, q) {
                ca = a || ca;
                co = o || co;
                cq = q || cq;
            }
        };
    };

    var a = new Date();
    a.setDate(a.getDate() + 36500);
    d.cookie = 'mocote=1; path=/; expires="' + a.toUTCString();
    d.write('<script src="/monolyth/cookietest.js"><' + '/script>');

    return this;

}).call({}, window, document);

