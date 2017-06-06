function Admin_JsWebFormsLogicRuleCheck(logic, prefix) {
    var flag = false;
    var field = $$('[name="' + prefix + '[field][' + logic['field_id'] + ']"]');
    var field_type = 'select';
    var selected = 'selected';
    if (typeof(field[0]) != 'object') {
        var input = $$('[name="' + prefix + '[field][' + logic['field_id'] + '][]"]');

        field_type = 'checkbox';
        selected = 'checked';

        if (typeof(input[0]) == 'object') {
            input = input[0].options;
            field_type = 'multiselect';
            selected = 'selected';
        }
    }
    var value;
    if (field_type == 'select') {
        var input = {'option': {'value': field[0].getValue(), selected: true}};
    }

    if (logic['aggregation'] == 'any' || (logic['aggregation'] == 'all' && logic['logic_condition'] == 'notequal')) {
        if (logic['logic_condition'] == 'equal') {
            for (var k in input) {
                if (input[k][selected]) {
                    for (var j in logic['value']) {
                        Admin_FieldIsVisible(logic["field_id"]) ? value = input[k].value : value = false;
                        if (value == logic['value'][j]) flag = true;
                    }
                }
            }
        } else {
            flag = true;
            var checked = false;
            for (var k in logic['value']) {
                for (var j in input) {
                    if (input[j][selected]) {
                        checked = true;
                        Admin_FieldIsVisible(logic["field_id"]) ? value = input[j].value : value = false;
                        if (value == logic['value'][k])
                            flag = false;
                    }
                }
            }
            if (!checked) flag = false;
        }
    } else {
        flag = true;
        for (var k in logic['value']) {
            for (var j in input) {
                if (typeof(input[j]) == 'object' && input[j])
                    Admin_FieldIsVisible(logic["field_id"]) ? value = input[j].value : value = false;
                if (!input[j][selected] && value == logic['value'][k])
                    flag = false;
            }
        }
    }
    return flag;
}

function Admin_JsWebFormsLogicTargetCheck(target, logicRules, prefix) {
    if (typeof(target["id"]) != 'string') return false;
    var flag = false;
    for (var i in logicRules) {
        for (var j in logicRules[i]['target']) {
            if (target["id"] == logicRules[i]['target'][j]) {
                if (Admin_JsWebFormsLogicRuleCheck(logicRules[i], prefix)) {
                    flag = true;
                    var config = logicRules[i];
                    break;
                }
            }
        }
    }
    var initState = "none";
    var styleDisplay = "block";
    if (target["id"].match('field_')) {
        styleDisplay = "table-row";
    }
    if (target["logic_visibility"] == 'visible')
        initState = styleDisplay;
    var changeState = styleDisplay;
    var display = initState;
    if (flag) {
        if (config['action'] == "hide") {
            changeState = "none";
        }
        display = changeState;
    }
    if ($(target["id"] + '_container') !== null && typeof($(target["id"] + '_container')) == 'object' && $(target["id"] + '_container').style) {
        $(target["id"] + '_container').style.display = display;
        if (display == 'none') {
            $(target["id"] + '_container').getElementsBySelector('.required-entry').each(function (s, i) {
                s.disable();
            });
        } else {
            $(target["id"] + '_container').getElementsBySelector('.required-entry').each(function (s, i) {
                s.enable();
            });
        }
    }

    for (var i in logicRules) {
        if (typeof(logicRules[i]) == 'object')
            if (typeof(target) == 'object')
                if (target["id"] == 'field_' + logicRules[i]['field_id'] || Admin_FieldInFieldset(logicRules[i]['field_id'], target["id"])) {
                    for (var j in logicRules[i]['target']) {
                        var visibility;
                        if (logicRules[i]['action'] == 'show') visibility = 'hidden';
                        if (logicRules[i]['action'] == 'hide') visibility = 'visible';
                        var newTarget = {
                            'id': logicRules[i]['target'][j],
                            'logic_visibility': visibility
                        };
                        Admin_JsWebFormsLogicTargetCheck(newTarget, logicRules);
                    }
                }
    }

    return flag;
}

function Admin_JSWebFormsLogic(targets, logicRules, prefix) {
    for (var n in logicRules) {
        var config = logicRules[n];
        if (typeof(config) == 'object') {
            var input = $$('[name="' + prefix + '[field][' + config['field_id'] + ']"]');
            var trigger_function = 'onchange';
            if (typeof(input[0]) != 'object') {
                input = $$('[name="' + prefix + '[field][' + config['field_id'] + '][]"]');
                trigger_function = 'onclick';
            }
            for (var i in input) {
                if (trigger_function == 'onchange')
                    input[i].onchange = function () {
                        for (var k in targets)
                            Admin_JsWebFormsLogicTargetCheck(targets[k], logicRules, prefix);
                    }
                else
                    input[i].onclick = function () {
                        for (var k in targets)
                            Admin_JsWebFormsLogicTargetCheck(targets[k], logicRules, prefix);
                    }
            }
        }
    }
}

function Admin_FieldIsVisible(fieldId) {
    var el = $('field_' + fieldId + '_container');
    if (el !== null) {
        if (el.offsetWidth == 0 || el.offsetWidth == undefined) return false;
    } else {
        return false;
    }
    return true;
}

function Admin_FieldInFieldset(fieldId, fieldsetId) {
    if (typeof fieldsetId != 'string') return false;
    var el = $$('#fieldset_' + fieldsetId.replace('fieldset_', '') + '_container #field_' + fieldId);
    if (el.length > 0) return true;
    return false;
}