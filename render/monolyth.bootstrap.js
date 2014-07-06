
;(function() {
"use strict";

var base = angular.module('monolyth', ['ngRoute']);

base.config(['$locationProvider', function($locationProvider) {

$locationProvider.html5Mode(true);

}]);

base.controller('MonolythController', ['$scope', '$route', function($scope, $route) {

$scope.template = {url: ''};
$scope.$on('$routeChangeSuccess', function() {
    $scope.template.url = $route.current.templateUrl;
});
$scope.Site = {
    language: {
        current: {
            code: 'en'
        }
    },
    favicons: [],
    mobileOptimized: true,
    title: 'Monolyth/Angular project'
};
$scope.Page = {
    meta: {
        keywords: ['Monolyth', 'site', 'boilerplate'],
        description: 'You should override this in your project\'s main controller.'
    },
    separator: ' - ',
    title: '[...loading...]',
    head: '',
    stylesheets: [],
    body: '/html/template/body.html'
};

}]);

base.filter('monolyth.url.http', ['monolyth.Project', '$window', function(Project, $window) {

return function(url) {
    if ($window.location.toString().match(Project.https)) {
        url = Project.http + url;
    }
    return url;
};

}]);

base.filter('monolyth.url.secure', ['monolyth.Project', '$window', function(Project, $window) {

return function(url) {
    if ($window.location.toString().match(Project.http)) {
        url = Project.https + url;
    }
    return url;
};

}]);

base.filter('monolyth.url.static', ['monolyth.Project', function(Project) {

return function(url) {
    var secure = Project.secure;
    if (!secure && !(Project.staticServers && Project.staticDomain)) {
        return url;
    }
    if (secure && !(Project.staticSecureServers && Project.staticSecureDomain)) {
        return url;
    }
    url = url.replace(/^https?:\/\/([a-z0-9\.-]+)?\//, '/');
    var i = 0;
    for (var j = 0; j < url.length; j++) {
        i += url.charCodeAt(j);
    }
    var nr = i % Project[secure ? 'staticSecureServers' : 'staticServers'].length;
    var domain = secure ? Project.staticSecureDomain : Project.staticDomain;
    return (secure ? Project.protocols : Project.protocol) + '://' + (secure ?
            Project.staticSecureServers[nr] :
            Project.staticServers[nr]
        ) + '.' + domain + (secure ? '' : '/' + Project.site) + url;
};

}]);

base.directive('monolythHtmlAllow', ['$compile', function($compile) {

return {
    replace: false,
    transclude: true,
    template: '{{element.html()}}',
    link: function(scope, ele, attrs) {
        scope.$watch(attrs.monolythHtmlAllow, function(html) {
            ele.html(html);
            $compile(ele.contents())(scope);
        });
    }
};

}]);

base.factory('monolyth.Base64', function() {

return new (function() {

var keyStr = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=";

this.encode = function(input) {
    var output = "";
    var chr1, chr2, chr3, enc1, enc2, enc3, enc4;
    var i = 0;
    
    input = utf8_encode(input);
    
    while (i < input.length) {
        chr1 = input.charCodeAt(i++);
        chr2 = input.charCodeAt(i++);
        chr3 = input.charCodeAt(i++);
        enc1 = chr1 >> 2;
        enc2 = ((chr1 & 3) << 4) | (chr2 >> 4);
        enc3 = ((chr2 & 15) << 2) | (chr3 >> 6);
        enc4 = chr3 & 63;
        if (isNaN(chr2)) {
            enc3 = enc4 = 64;
        } else if (isNaN(chr3)) {
            enc4 = 64;
        }
        output = output + keyStr.charAt(enc1) + keyStr.charAt(enc2) + keyStr.charAt(enc3) + keyStr.charAt(enc4);
    }
    return output;
};

this.decode = function(input) {
    var output = "";
    var chr1, chr2, chr3;
    var enc1, enc2, enc3, enc4;
    var i = 0;
    input = input.replace(/[^A-Za-z0-9\+\/\=]/g, "");
    while (i < input.length) {
        enc1 = keyStr.indexOf(input.charAt(i++));
        enc2 = keyStr.indexOf(input.charAt(i++));
        enc3 = keyStr.indexOf(input.charAt(i++));
        enc4 = keyStr.indexOf(input.charAt(i++));
        chr1 = (enc1 << 2) | (enc2 >> 4);
        chr2 = ((enc2 & 15) << 4) | (enc3 >> 2);
        chr3 = ((enc3 & 3) << 6) | enc4;
        output = output + String.fromCharCode(chr1);
        if (enc3 != 64) {
            output = output + String.fromCharCode(chr2);
        }
        if (enc4 != 64) {
            output = output + String.fromCharCode(chr3);
        }
    }
    return utf8_decode(output);
};

function utf8_encode(string) {
    string = string.replace(/\r\n/g,"\n");
    var utftext = "";
    for (var n = 0; n < string.length; n++) {
        var c = string.charCodeAt(n);
        if (c < 128) {
            utftext += String.fromCharCode(c);
        } else if((c > 127) && (c < 2048)) {
            utftext += String.fromCharCode((c >> 6) | 192);
            utftext += String.fromCharCode((c & 63) | 128);
        } else {
            utftext += String.fromCharCode((c >> 12) | 224);
            utftext += String.fromCharCode(((c >> 6) & 63) | 128);
            utftext += String.fromCharCode((c & 63) | 128);
        }
    }
    return utftext;
};

function utf8_decode(utftext) {
    var string = "";
    var i = 0;
    var c = 0, c1 = 0, c2 = 0;
    while ( i < utftext.length ) {
        c = utftext.charCodeAt(i);
        if (c < 128) {
            string += String.fromCharCode(c);
            i++;
        } else if((c > 191) && (c < 224)) {
            c2 = utftext.charCodeAt(i+1);
            string += String.fromCharCode(((c & 31) << 6) | (c2 & 63));
            i += 2;
        } else {
            c2 = utftext.charCodeAt(i+1);
            c3 = utftext.charCodeAt(i+2);
            string += String.fromCharCode(((c & 15) << 12) | ((c2 & 63) << 6) | (c3 & 63));
            i += 3;
        }
    }
    return string;
};

})();

});

base.directive('monolythForm', ['monolyth.Base64', '$http', '$compile', function(Base64, $http, $compile) {

return {
    replace: true,
    link: function(scope, element, attrs) {
        var url = '/monolyth/form/' + scope.Site.language.current.code + '/' + Base64.encode(attrs.monolythForm) + '/';
        if (attrs.monolythFormView) {
            url += '?view=' + attrs.monolythFormView;
        }
        $http.get(url).success(function(data) {
            var form = Base64.decode(data.form);
            if (attrs.ngModel) {
                form = form.replace(/name="(\w+?)"/g, 'name="$1" ng-model="' + attrs.ngModel + '.$1"');
            }
            form = angular.element(form);
            var select = form.find('selected');
            if (select.length) {
                select.each(function() {
                    var $this = $(this);
                    var opts = '[';
                    $this.find('option').each(function(i, e) {
                        var $e = $(e);
                        if (!i) {
                            return;
                        }
                        if (i > 1) {
                            opts += ',';
                        }
                        opts += JSON.stringify({v: $e.val(), d: $e.html()});
                        $e.remove();
                    });
                    opts += ']';
                    $this.attr('ng-options', 'o.v as o.d for o in ' + opts);
                });
            }
            if (attrs.monolythFormSubmit) {
                form.attr('ng-submit', attrs.monolythFormSubmit);
            }
            if (attrs.monolythFormModify) {
                (scope[attrs.monolythFormModify])(form);
            }
            $compile(form.contents())(scope);
            element.replaceWith(form);
        });
    }
};

}]);

})();

