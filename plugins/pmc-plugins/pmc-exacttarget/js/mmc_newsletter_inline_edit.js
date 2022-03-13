jQuery(document).ready(function () {
    var pluginbase = '/wp-content/plugins/MMC_newsletter';
    var mmcnws_nonce = jQuery('#mmcnws_nonce').html();
    //newsletters: make subject customisable
    jQuery('.editablecontainerNWS .iseditable').each(function () {
        var objEditable = new MMC_JEditable(this, pluginbase + '/ajax.php?rnd=' + mmc_get_random_number());
        objEditable.je_onblur = 'cancel';
        objEditable.je_type = 'textarea';
        objEditable.je_submitdata = {ajax_action:'nwsltr_edit_subject', _mmcnws_ajax_nonce:mmcnws_nonce};
        objEditable.je_name = 'nwsltr_sub';
        objEditable.je_width = '180';
        objEditable.je_height = '40';
        objEditable.je_cancel = '';	//no need for cancel button
        objEditable.makeEditable();
    });

    //breaking news alerts: make subject customisable
    jQuery('.editablecontainerBNA .iseditable').each(function () {
        var objEditable = new MMC_JEditable(this, pluginbase + '/ajax.php?rnd=' + mmc_get_random_number());
        objEditable.je_onblur = 'cancel';
        objEditable.je_type = 'textarea';
        objEditable.je_submitdata = {ajax_action:'bna_edit_subject', _mmcnws_ajax_nonce:mmcnws_nonce};
        objEditable.je_name = 'bna_sub';
        objEditable.je_width = '180';
        objEditable.je_height = '40';
        objEditable.je_cancel = '';	//no need for cancel button
        objEditable.makeEditable();
    });
});


//Class to make field editable
function MMC_JEditable(elemId, ajaxURL) {
    this.elemId = elemId;		//object reference to the element to edit
    this.url = ajaxURL;			//url where to post request
    this.je_event = 'click';	//click|dblclick
    this.je_onblur = 'cancel';	//cancel|submit|ignore
    this.je_type = 'text';		//text|textarea
    this.je_submitdata = '';	//additional data to submit - needs to be JSON object
    this.je_name = '';			//name of field in post array which contains the modified data
    this.je_cssclass = 'editableBox';			//name of css class to apply on form
    this.je_width = 'none';		//width of textbox|textarea|select
    this.je_height = 'none';	//height of textbox|textarea|select
    this.je_submit = 'OK';		//label to show on submit botton - button not displayed if left blank
    this.je_cancel = 'CANCEL';	//label to show on cancel botton - button not displayed if left blank
    this.je_indicator = 'Saving..... <img src="' + pluginbase + '/ajax-loader.gif" />';		//indicator shown while data is being sent via AJAX
    this.je_tooltip = 'Click to edit...';		//text showin in tooltip on editable field
}
//MMC_JEditable.makeEditable() - called after all settings have been done - to finally make the field(s) editable
MMC_JEditable.prototype.makeEditable = function () {
    jQuery(this.elemId).editable(this.url, {
        event:this.je_event,
        onblur:this.je_onblur,
        type:this.je_type,
        submitdata:this.je_submitdata,
        name:this.je_name,
        cssclass:this.je_cssclass,
        width:this.je_width,
        height:this.je_height,
        submit:this.je_submit,
        cancel:this.je_cancel,
        indicator:this.je_indicator,
        tooltip:this.je_tooltip,
        onerror:function (settings, original, xhr) {
            original.reset();
            alert(xhr.responseText);
        }
    });
};


