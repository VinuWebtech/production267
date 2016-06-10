/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE_AFL.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magento.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magento.com for more information.
 *
 * @category    design
 * @package     rwd_default
 * @copyright   Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
function Miniquote(options) {
    this.formKey = options.formKey;
    this.previousVal = null;

    this.defaultErrorMessage = 'Error occurred. Try to refresh page.';

    this.selectors = {
        itemRemove:           '#miniquote-sidebar .remove',
        container:            '#header-miniquote',
        inputQty:             '.cart-item-quantity',
        qty:                  'div.header-miniquote span.count',
        overlay:              '.minicart-wrapper',
        error:                '#minicart-error-message',
        success:              '#minicart-success-message',
        quantityButtonPrefix: '#qbutton-',
        quantityInputPrefix:  '#qinput-',
        quantityButtonClass:  '.quantity-button'
    };

    if (options.selectors) {
        $jpprR4q.extend(this.selectors, options.selectors);
    }
}

Miniquote.prototype = {
    initAfterEvents : {},
    removeItemAfterEvents : {},
    init: function() {
        var cart = this;
        // bind remove event
        $jpprR4q(this.selectors.itemRemove).unbind('click.miniquote').bind('click.miniquote', function(e) {
            e.preventDefault();
            cart.removeItem($jpprR4q(this));
        });

        // bind update qty event
        $jpprR4q(this.selectors.inputQty)
            .unbind('blur.miniquote')
            .unbind('focus.miniquote')
            .bind('focus.miniquote', function() {
                cart.previousVal = $jpprR4q(this).val();
                cart.displayQuantityButton($jpprR4q(this))
            })
            .bind('blur.miniquote', function() {
                cart.revertInvalidValue(this);
            });

        $jpprR4q(this.selectors.quantityButtonClass)
            .unbind('click.quantity')
            .bind('click.quantity', function() {
                cart.processUpdateQuantity(this);
        });

        for (var i in this.initAfterEvents) {
            if (this.initAfterEvents.hasOwnProperty(i) && typeof(this.initAfterEvents[i]) === "function") {
                this.initAfterEvents[i]();
            }
        }

    },

    removeItem: function(el) {
        var cart = this;
        if (confirm(el.data('confirm'))) {
            cart.hideMessage();
            cart.showOverlay();
            $jpprR4q.ajax({
                type: 'POST',
                dataType: 'json',
                data: {form_key: cart.formKey},
                url: el.attr('href')
            }).done(function(result) {
                cart.hideOverlay();
                if (result.success) {
                    cart.updateCartQty(result.qty);
                    cart.updateContentOnRemove(result, el.closest('li'));
                } else {
                    cart.showMessage(result);
                }
            }).error(function() {
                cart.hideOverlay();
                cart.showError(cart.defaultErrorMessage);
            });
        }
        for (var i in this.removeItemAfterEvents) {
            if (this.removeItemAfterEvents.hasOwnProperty(i) && typeof(this.removeItemAfterEvents[i]) === "function") {
                this.removeItemAfterEvents[i]();
            }
        }
    },

    revertInvalidValue: function(el) {
        if (!this.isValidQty($jpprR4q(el).val()) || $jpprR4q(el).val() == this.previousVal) {
            $jpprR4q(el).val(this.previousVal);
            this.hideQuantityButton(el);
        }
    },

    displayQuantityButton: function(el) {
        var buttonId = this.selectors.quantityButtonPrefix + $jpprR4q(el).data('item-id');
        $jpprR4q(buttonId).addClass('visible').attr('disabled',null);
    },

    hideQuantityButton: function(el) {
        var buttonId = this.selectors.quantityButtonPrefix + $jpprR4q(el).data('item-id');
        $jpprR4q(buttonId).removeClass('visible').attr('disabled','disabled');
    },

    processUpdateQuantity: function(el) {
        var input = $jpprR4q(this.selectors.quantityInputPrefix + $jpprR4q(el).data('item-id'));
        if (this.isValidQty(input.val()) && input.val() != this.previousVal) {
            this.updateItem(el);
        } else {
            this.revertInvalidValue(input);
        }
    },

    updateItem: function(el) {
        var cart = this;
        var input = $jpprR4q(this.selectors.quantityInputPrefix + $jpprR4q(el).data('item-id'));
        var quantity = parseInt(input.val(), 10);
        cart.hideMessage();
        cart.showOverlay();
        $jpprR4q.ajax({
            type: 'POST',
            dataType: 'json',
            url: input.data('link'),
            data: {qty: quantity, form_key: cart.formKey}
        }).done(function(result) {
            cart.hideOverlay();
            if (result.success) {
                cart.updateCartQty(result.qty);
                if (quantity !== 0) {
                    cart.updateContentOnUpdate(result);
                } else {
                    cart.updateContentOnRemove(result, input.closest('li'));
                }
            } else {
                cart.showMessage(result);
            }
        }).error(function() {
            cart.hideOverlay();
            cart.showError(cart.defaultErrorMessage);
        });
        return false;
    },

    updateContentOnRemove: function(result, el) {
        var cart = this;
        el.hide('slow', function() {
            $jpprR4q(cart.selectors.container).html(result.content);
            cart.showMessage(result);

        });
    },

    updateContentOnUpdate: function(result) {
        $jpprR4q(this.selectors.container).html(result.content);
        this.showMessage(result);
    },

    updateCartQty: function(qty) {
        if (typeof qty != 'undefined') {
            $jpprR4q(this.selectors.qty).text(qty);
        }
    },

    isValidQty: function(val) {
        return (val.length > 0) && (val - 0 == val) && (val - 0 > 0);
    },

    showOverlay: function() {
        $jpprR4q(this.selectors.overlay).addClass('loading');
    },

    hideOverlay: function() {
        $jpprR4q(this.selectors.overlay).removeClass('loading');
    },

    showMessage: function(result) {
        if (typeof result.notice != 'undefined') {
            this.showError(result.notice);
        } else if (typeof result.error != 'undefined') {
            this.showError(result.error);
        } else if (typeof result.message != 'undefined') {
            this.showSuccess(result.message);
        }
    },

    hideMessage: function() {
        $jpprR4q(this.selectors.error).fadeOut('slow');
        $jpprR4q(this.selectors.success).fadeOut('slow');
    },

    showError: function(message) {
        $jpprR4q(this.selectors.error).text(message).fadeIn('slow');
    },

    showSuccess: function(message) {
        $jpprR4q(this.selectors.success).text(message).fadeIn('slow');
    }
};
