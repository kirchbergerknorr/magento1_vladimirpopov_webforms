function JsWebFormsLogicRuleCheck(logic, uid) {
    var flag = false;
    var field = $$(GetFormContainerId(uid) + ' [name="field[' + logic["field_id"] + ']"]');
    var field_type = 'select';
    var selected = 'selected';
    if (typeof(field[0]) != 'object') {
        input = $$(GetFormContainerId(uid) + ' [name="field[' + logic['field_id'] + '][]"]');
        field_type = 'checkbox';
        selected = 'checked';
    } else {
        if (field[0].type == 'radio') {
            field_type = 'radio';
            input = field;
            selected = 'checked';
        }
    }
    var value;
    if (field_type == 'select') {
        var input = {'option': {'value': field[0].getValue(), selected: true}};
    }
    if (logic['aggregation'] == 'any' || (logic['aggregation'] == 'all' && logic['logic_condition'] == 'notequal')) {
        if (logic['logic_condition'] == 'equal') {
            for (var k in input) {
                if (typeof(input[k]) == 'object' && input[k]) {
                    if (input[k][selected]) {
                        for (var j in logic['value']) {
                            FieldIsVisible(logic["field_id"], uid) ? value = input[k].value : value = false;
                            if (value == logic['value'][j]) flag = true;
                        }
                    }
                }
            }
        } else {
            flag = true;
            var checked = false;
            for (var k in logic['value']) {
                for (var j in input) {
                    if (typeof(input[j]) == 'object' && input[j])
                        if (input[j][selected]) {
                            checked = true;
                            FieldIsVisible(logic["field_id"], uid) ? value = input[j].value : value = false;
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
                    FieldIsVisible(logic["field_id"], uid) ? value = input[j].value : value = false;
                if (!input[j][selected] && value == logic['value'][k])
                    flag = false;
            }
        }
    }
    return flag;
}

function JsWebFormsLogicTargetCheck(target, logicRules, fieldMap, uid) {
    if (typeof(target) != 'object') return false;
    var flag = false;
    for (var i in logicRules) {
        if (typeof(logicRules[i]) == 'object')
            for (var j in logicRules[i]['target']) {
                if (typeof(target) == 'object')
                    if (target["id"] == logicRules[i]['target'][j]) {
                        if (JsWebFormsLogicRuleCheck(logicRules[i], uid)) {
                            flag = true;
                            var config = logicRules[i];
                            break;
                        }
                    }

            }
    }
    var initState = "none";
    if (target["logic_visibility"] == 'visible')
        initState = "block";
    var changeState = "block";
    var display = initState;
    if (flag) {
        if (config['action'] == "hide") {
            changeState = "none";
        }
        display = changeState;
    }
    if ($(target["id"]) !== null && $(target["id"]).style !== undefined)
        $(target["id"]).style.display = display;

    if ($(target["id"] + '_row') !== null && $(target["id"] + '_row').style !== undefined)
        $(target["id"] + '_row').style.display = display;

    if (flag)
        for (var i in logicRules) {
            if (typeof(logicRules[i]) == 'object' && logicRules[i] != config)
                if (typeof(target) == 'object')
                    if (target["id"] == 'field_' + logicRules[i]['field_id'] || FieldInFieldset(logicRules[i]['field_id'], target["id"], fieldMap)) {
                        for (var j in logicRules[i]['target']) {
                            var visibility;
                            if (logicRules[i]['action'] == 'show') visibility = 'hidden';
                            if (logicRules[i]['action'] == 'hide') visibility = 'visible';
                            if (typeof(logicRules[i]['target'][j]) == 'string') {
                                var newTarget = {
                                    'id': logicRules[i]['target'][j],
                                    'logic_visibility': visibility
                                };
                                JsWebFormsLogicTargetCheck(newTarget, logicRules, fieldMap, uid);
                            }
                        }
                    }
        }

    return flag;
}

function JSWebFormsLogic(targets, logicRules, fieldMap, uid) {
    for (var n in logicRules) {
        var config = logicRules[n];
        if (typeof(config) == 'object') {
            var input = $$(GetFormContainerId(uid) + ' [name="field[' + config["field_id"] + ']"]');
            var trigger_function = 'onchange';
            if (typeof(input[0]) != 'object') {
                input = $$(GetFormContainerId(uid) + ' [name="field[' + config['field_id'] + '][]"]');
                trigger_function = 'onclick';
            } else {
                if (input[0].type == 'radio') {
                    trigger_function = 'onclick';
                }
            }
            for (var i in input) {
                if (trigger_function == 'onchange')
                    input[i].onchange = function () {
                        for (var k in targets)
                            JsWebFormsLogicTargetCheck(targets[k], logicRules, fieldMap, uid);
                    }
                else
                    input[i].onclick = function () {
                        for (var k in targets)
                            JsWebFormsLogicTargetCheck(targets[k], logicRules, fieldMap, uid);
                    }
            }
        }
    }
}
function GetFormContainerId(uid){
    if(uid){
        return '#webform_' + uid;
    }
    return '';
}

function FieldIsVisible(fieldId, uid) {
    var el = $('field_' + uid + fieldId);
    if (el !== null) {
        if (el.offsetWidth == 0 || el.offsetWidth == undefined) return false;
    } else {
        return false;
    }
    return true;
}

function FieldInFieldset(fieldId, fieldsetId, fieldMap) {
    if (typeof fieldsetId != 'number') return false;
    return fieldMap['fieldset_' + fieldsetId].include(fieldId);
}