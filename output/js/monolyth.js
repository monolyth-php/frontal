
/**
 * Core Monolyth javascript.
 *
 * @package monolyth
 * @author Marijn Ophorst <marijn@monomelodies.nl>
 * @copyright 2011, 2012, 2013 Monomelodies <http://www.monomelodies.nl>
 */

if (!Date.prototype.isLeapYear) {
    /**
     * Helper method that returns true if the year represented in the Date
     * object is a leap year, false otherwise.
     *
     * @return bool True if we are a leap year, else false.
     */
    Date.prototype.isLeapYear = function()
    {
        var y = this.getFullYear();
        if (y % 4) {
            return false;
        }
        return new Date(y, 1, 29).getDate() == 29;
    };
}

if (!String.prototype.removeEntities) {
    /**
     * Remove all HTML entities from a string - basically a Javascript
     * implementation of PHP's html_entity_decode.
     *
     * @return string The decoded string.
     */
    String.prototype.removeEntities = function() {
        var temp = document.createElement("div");
        temp.innerHTML = this;
        var result = temp.childNodes[0].nodeValue;
        temp.removeChild(temp.firstChild);
        return result;
    };
}

/**
 * Define Array.indexOf for browsers that don't implement it
 * (I'm looking at you here, Internet Explorer...)
 */
if (!Array.prototype.indexOf) {
    /**
     * @param mixed obj Whatever we want to check for.
     * @param integer start Optional index to start at.
     * @return integer The position obj was found, or -1.
     */
    Array.prototype.indexOf = function(obj, start) {
        for (var i = (start || 0), j = this.length; i < j; i++) {
            if (this[i] === obj) {
                return i;
            }
        }
        return -1;
    };
}

/**
 * Shorthand to get the last x characters from a string.
 */
if (!String.prototype.last) {
    /**
     * @param integer length How many chars to return from the end.
     * @return string The substring.
     */
    String.prototype.last = function(length) {
        return this.substring(this.length - length);
    };
}

