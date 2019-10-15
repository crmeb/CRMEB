/**
 * Cookie plugin
 *
 * Copyright (c) 2006 Klaus Hartl (stilbuero.de)
 * Dual licensed under the MIT and GPL licenses:
 * http://www.opensource.org/licenses/mit-license.php
 * http://www.gnu.org/licenses/gpl.html
 *
 */

/**
 * Create a Cookie with the given name and value and other optional parameters.
 *
 * @example $.Cookie('the_cookie', 'the_value');
 * @desc Set the value of a Cookie.
 * @example $.Cookie('the_cookie', 'the_value', { expires: 7, path: '/', domain: 'jquery.com', secure: true });
 * @desc Create a Cookie with all available options.
 * @example $.Cookie('the_cookie', 'the_value');
 * @desc Create a session Cookie.
 * @example $.Cookie('the_cookie', null);
 * @desc Delete a Cookie by passing null as value. Keep in mind that you have to use the same path and domain
 *       used when the Cookie was set.
 *
 * @param String name The name of the Cookie.
 * @param String value The value of the Cookie.
 * @param Object options An object literal containing key/value pairs to provide optional Cookie attributes.
 * @option Number|Date expires Either an integer specifying the expiration date from now on in days or a Date object.
 *                             If a negative value is specified (e.g. a date in the past), the Cookie will be deleted.
 *                             If set to null or omitted, the Cookie will be a session Cookie and will not be retained
 *                             when the the browser exits.
 * @option String path The value of the path atribute of the Cookie (default: path of page that created the Cookie).
 * @option String domain The value of the domain attribute of the Cookie (default: domain of page that created the Cookie).
 * @option Boolean secure If true, the secure attribute of the Cookie will be set and the Cookie transmission will
 *                        require a secure protocol (like HTTPS).
 * @type undefined
 *
 * @name $.Cookie
 * @cat Plugins/Cookie
 * @author Klaus Hartl/klaus.hartl@stilbuero.de
 */

/**
 * Get the value of a Cookie with the given name.
 *
 * @example $.Cookie('the_cookie');
 * @desc Get the value of a Cookie.
 *
 * @param String name The name of the Cookie.
 * @return The value of the Cookie.
 * @type String
 *
 * @name $.Cookie
 * @cat Plugins/Cookie
 * @author Klaus Hartl/klaus.hartl@stilbuero.de
 */
$.Cookie = function(name, value, options) {
    if (typeof value != 'undefined') { // name and value given, set Cookie
        options = options || {};
        if (value === null) {
            value = '';
            options.expires = -1;
        }
        var expires = '';
        if (options.expires && (typeof options.expires == 'number' || options.expires.toUTCString)) {
            var date;
            if (typeof options.expires == 'number') {
                date = new Date();
                date.setTime(date.getTime() + (options.expires * 24 * 60 * 60 * 1000));
            } else {
                date = options.expires;
            }
            expires = '; expires=' + date.toUTCString(); // use expires attribute, max-age is not supported by IE
        }
        // CAUTION: Needed to parenthesize options.path and options.domain
        // in the following expressions, otherwise they evaluate to undefined
        // in the packed version for some reason...
        var path = options.path ? '; path=' + (options.path) : '';
        var domain = options.domain ? '; domain=' + (options.domain) : '';
        var secure = options.secure ? '; secure' : '';
        document.cookie = [name, '=', encodeURIComponent(value), expires, path, domain, secure].join('');
    } else { // only name given, get Cookie
        var cookieValue = null;
        if (document.cookie && document.cookie != '') {
            var cookies = document.cookie.split(';');
            for (var i = 0; i < cookies.length; i++) {
                var cookie = jQuery.trim(cookies[i]);
                // Does this cookie string begin with the name we want?
                if (cookie.substring(0, name.length + 1) == (name + '=')) {
                    cookieValue = decodeURIComponent(cookie.substring(name.length + 1));
                    break;
                }
            }
        }
        return cookieValue;
    }
};
