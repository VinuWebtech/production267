/**
 * This ZipCode class is used to render the
 * full adress city,state and country
 * based on customer entered zipcode
 * @type {*}
 */




var zipCodeAdrressLoader = Class.create();
zipCodeAdrressLoader.prototype = {
    initialize: function(zipCodeEl,url,cityEL,stateEL,countryEl){
        this.zipCodeUrl = url;
        this.zipCodeEl  = $(zipCodeEl);
        this.cityEL     = $(cityEL);
        this.stateEL    = $(stateEL);
        this.countryEl  = $(countryEl);
        Event.observe(this.zipCodeEl, 'change', this.fetchRecords.bind(this));
    },

    //this function will update the address fileds
    update: function(transport){
        var response = eval('(' + transport.responseText + ')');
        if(response && response.status){
            if ((typeof (this.countryEl) != 'undefined')) {
                 this.countryEl.setValue('IN');
            }
            if (response.city && (typeof (this.cityEL) != 'undefined')) {

                 this.cityEL.setValue(response.city);
            }
            if (response.state && (typeof (this.stateEL) != 'undefined') ) {
                this.stateEL.setValue(response.state);
            }

        } else {
            if (response.err-msg) {
                alert(response.err-msg);
            }
        }
    },

    //this function will fetch the address fields with respect to zipcode
    fetchRecords: function(element,callback) {
        var request = new Ajax.Request(
            this.zipCodeUrl,
            {
                method:'post',
                parameters: {
                    zipcode:this.zipCodeEl.getValue()
                },
                onComplete: this.update.bind(this)
            }
        );

    }
};


//Class for Customer Email Validation and Checkout method switch
var emailChecker = Class.create();
emailChecker.prototype = {
    initialize: function(emailEl,url,loginEl,registerEl,loginCtrl,passwordCtrl,registerEnv,checkoutMethodBtn){
        this.emailEl = $(emailEl);
        this.Url  = url;
        this.loginEl = $(loginEl);
        this.registerEl = $(registerEl);
        this.registerEnv = $(registerEnv);
        this.loginCtrl = $(loginCtrl);
        this.passwordCtrl = $(passwordCtrl);
        this.checkoutMethodBtn = $(checkoutMethodBtn);
        Event.observe(this.emailEl, 'change', this.checkEmail.bind(this));
        Event.observe(this.emailEl, 'focus', this.checkEmail.bind(this));
        Event.observe(this.emailEl, 'mouseout', this.checkEmail.bind(this));
        Event.observe(this.emailEl, 'blur', this.checkEmail.bind(this));
    },
    checkEmail: function() {
        //this.checkoutMethodBtn.disabled = true;   //to avoid double clicking
        var customerEmail = this.emailEl.getValue();
        var request = new Ajax.Request(
            this.Url,
            {
                method:'post',
                parameters: {email:customerEmail},
                onComplete: this.process.bind(this)
            }
        );
    },
    process: function(transport){
        var data = eval('(' + transport.responseText + ')');
        if(data.status){
            this.loginEl.checked = true;
            this.loginCtrl.show();
            this.passwordCtrl.show();
            this.registerEl.checked = false;
            this.registerEnv.hide();
        } else {
            this.loginEl.checked = false;
            this.loginCtrl.hide();
            this.passwordCtrl.hide();
            this.registerEnv.show();
            this.registerEl.checked = true; //onkar for either of the radio btn to be true
        }
        this.checkoutMethodBtn.disabled = false;
    }
};