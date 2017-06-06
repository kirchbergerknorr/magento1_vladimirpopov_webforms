function JsWebformsStepNext(el, formId){
    if(!el) return;
    var form = new VarienForm('webform_'+formId);
    var current_fs = $(el).up().up();
    var next_fs = current_fs.next();

    // get the fieldset
    var fieldset = next_fs.down();

    // if fieldset has no displayed items it has a style attribute
    if(fieldset.getAttribute("style")){
        if(fieldset.getAttribute("style").indexOf("display: none") != -1){
            if(form.validator && form.validator.validate()){
                var nextEl = next_fs.select('.action-next')[0];
                JsWebformsStepNext(nextEl,formId);
            }
        }
    }

    if(form.validator && form.validator.validate()){
        Effect.Appear(next_fs,{duration:0.5});
        current_fs.setStyle({'position' : 'absolute','visibility' : 'hidden'});
    }
}

function JsWebformsStepPrevious(el){
    var current_fs = $(el).up().up();
    if(current_fs.className != 'form-step') current_fs = current_fs.up();
    var previous_fs = current_fs.previous();

    // get the fieldset
    var fieldset = previous_fs.down();

    // if fieldset has no displayed items it has a style attribute
    if(fieldset.getAttribute("style")){
        if(fieldset.getAttribute("style").indexOf("display: none") != -1){
            var prevEl = previous_fs.select('.action-previous')[0];
            JsWebformsStepPrevious(prevEl);
        }
    }

    previous_fs.setStyle({'position' : 'inherit','visibility' : 'visible'});
    current_fs.hide();
}