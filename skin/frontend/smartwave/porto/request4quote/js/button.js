/*
 * Request for quote button
 */
var ITWebExpersR4QButton = Class.create();
ITWebExpersR4QButton.prototype = {
    initialize: function() {
        // collections
        this.FLAGS = {};
        this.DOM = {};
        // add to cart buttons
        
    }
}


document.observe('dom:loaded', function() {
    new ITWebExpersR4QButton();
});