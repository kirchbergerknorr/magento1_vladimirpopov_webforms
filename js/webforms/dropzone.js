var JsWebFormsDropzone = (function () {
    function JsWebFormsDropzone(options) {
        var o = {
            url: '',
            fieldId: '',
            fieldName: '',
            dropZone: 1,
            dropZoneText: 'Add file or drop here',
            maxFiles: 1,
            allowedSize: 0,
            previewMaxWidth: '26px',
            previewMaxHeight: '26px',
            allowedExtensions: [],
            restrictedExtensions: [],
            validationCssClass: '',
            errorMsgAllowedExtensions: 'Selected file has none of allowed extensions: %s',
            errorMsgRestrictedExtensions: 'Uploading of potentially dangerous files is not allowed.',
            errorMsgAllowedSize: 'Selected file exceeds allowed size: %s kB',
            errorMsgUploading: 'Error uploading file',
            errorMsgNotReady: 'Please wait... the upload is in progress.'
        };
        for (var k in options) {
            if (options.hasOwnProperty(k)) o[k] = options[k];
        }
        if (!o.fieldId) return;
        var field = document.getElementById(o.fieldId);
        var previewZone = document.getElementById(o.fieldId + '_preview');
        if (!previewZone) {
            previewZone = document.createElement('div');
            field.parentNode.appendChild(previewZone);
        }
        var parentNode = field.parentNode;

        var fileCnt = 0;

        var fieldName = o.fieldName ? o.fieldName : 'hash' + field.name;
        var inputHash = document.createElement('input');
        inputHash.setAttribute('type', 'hidden');
        inputHash.setAttribute('name', fieldName);
        inputHash.setAttribute('class', o.validationCssClass);

        var inputDropzone = document.createElement('input');
        inputDropzone.setAttribute('type', 'file');
        inputDropzone.setAttribute('style', 'display:none');
        inputDropzone.setAttribute('multiple', 'multiple');

        if (o.dropZone) {
            var dropZone = document.createElement('div');
            dropZone.setAttribute('class', 'drop-zone');

            var dropZoneText = document.createElement('div');
            dropZoneText.setAttribute('class', 'text-center');
            dropZone.appendChild(dropZoneText);

            var dropZoneIconPaperclip = document.createElement('span');
            dropZoneIconPaperclip.setAttribute('class', 'icon-paperclip');
            dropZoneIconPaperclip.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 14 14"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"' +
                'd="M6 4.6v5c0 .6.5 1.1 1.1 1.1s1.1-.5 1.1-1.1V2.7C8.2 1.5 7.2.5 6 .5s-2.2 1-2.2 2.2v7.5c0 1.8 1.5 3.3 3.3 3.3s3.3-1.5 3.3-3.3V4.6"></path></svg>';
            dropZoneText.appendChild(dropZoneIconPaperclip);

            var dropZoneLabel = document.createElement('div');
            dropZoneLabel.setAttribute('class', 'drop-zone-label');
            dropZoneLabel.innerHTML = o.dropZoneText;
            dropZoneText.appendChild(dropZoneLabel);

            dropZone.addEventListener('click', function () {
                inputDropzone.click();
            });

            var handleFileDrop = function (evt) {
                evt.stopPropagation();
                evt.preventDefault();

                var files = evt.dataTransfer.files;

                fileCnt = dropZoneFiles.length;

                for (var i = 0, f; f = files[i]; i++) {
                    processFile(files[i]);
                }
            };

            var handleDragOver = function (evt) {
                evt.stopPropagation();
                evt.preventDefault();
                evt.dataTransfer.dropEffect = 'copy';
            };

            dropZone.addEventListener('dragover', handleDragOver, false);
            dropZone.addEventListener('drop', handleFileDrop, false);

            field.setAttribute('style', 'display:none');
            parentNode.appendChild(dropZone);

            parentNode.appendChild(inputHash);
            parentNode.appendChild(inputDropzone);
        } else {
            o.maxFiles = 1;
        }

        var uploadField = function () {
            if (o.dropZone) return dropZone;
            return field;
        };

        var dropZoneFiles = [];

        var processFile = function (file) {
            if (fileCnt >= o.maxFiles) {
                return;
            }

            fileCnt++;

            var preview = document.createElement('div');
            var hash = "";
            previewZone.appendChild(preview);
            preview.setAttribute('id', o.fieldId + '_preview');
            preview.setAttribute('class', 'drop-zone-preview');
            preview.setAttribute('style', 'display:none');
            preview.setAttribute('style', 'display:block');
            preview.innerHTML = "";
            var errors = [];
            var fileName = file.name;
            var fileExt = fileName.substr(fileName.lastIndexOf('.') + 1).toLowerCase();
            var fileSize = file.size;
            var fileType = file.type;
            var fileSizeKB = (fileSize / 1024).toFixed(2);

            if (o.allowedExtensions.indexOf(fileExt) < 0 && o.allowedExtensions.length) {
                errors.push(o.errorMsgAllowedExtensions.replace('%s', o.allowedExtensions.join()));
            }
            if (o.restrictedExtensions.indexOf(fileExt) >= 0 && o.restrictedExtensions.length) {
                errors.push(o.errorMsgRestrictedExtensions);
            }
            if (fileSizeKB > o.allowedSize && o.allowedSize > 0) {
                errors.push(o.errorMsgAllowedSize.replace('%s', o.allowedSize.toString()));
            }

            var cancelFile = function () {
                uploadField().setAttribute('style', 'display:block');
                field.value = '';
                for (var i = 0; i < dropZoneFiles.length; i++) {
                    if (hash === dropZoneFiles[i]) {
                        dropZoneFiles.splice(i, 1);
                        inputHash.value = dropZoneFiles.join(';');
                    }
                }
                previewZone.removeChild(preview);
            };

            if (errors.length && !o.dropZone) {
                field.value = '';
                preview.setAttribute('style', 'display:none');
                alert(errors.join("\n\n"));
            } else {
                var divAttachementContainer = document.createElement('div');
                divAttachementContainer.setAttribute('class', 'drop-zone-attachement-container');
                preview.appendChild(divAttachementContainer);

                var divAttachement = document.createElement('div');
                divAttachement.setAttribute('class', 'drop-zone-attachment');

                var spanIconFile = document.createElement('span');
                spanIconFile.setAttribute('class', 'drop-zone-preview-icon-file');
                divAttachement.appendChild(spanIconFile);

                var divPreviewFile = document.createElement('div');
                divPreviewFile.setAttribute('class', 'drop-zone-preview-file');
                divAttachement.appendChild(divPreviewFile);

                var divPreviewFilename = document.createElement('div');
                divPreviewFilename.setAttribute('class', 'drop-zone-preview-filename');
                divPreviewFilename.innerHTML = fileName.substr(0, fileName.length - 7);
                divPreviewFile.appendChild(divPreviewFilename);

                var divPreviewFilenameEnd = document.createElement('div');
                divPreviewFilenameEnd.setAttribute('class', 'drop-zone-preview-filename-end');
                divPreviewFilenameEnd.innerHTML = fileName.substr(-7);
                divPreviewFile.appendChild(divPreviewFilenameEnd);

                var divPreviewInfo = document.createElement('div');
                divPreviewInfo.setAttribute('class', 'drop-zone-preview-size');
                divPreviewFile.appendChild(divPreviewInfo);

                var spanIconClose = document.createElement('span');
                spanIconClose.setAttribute('class', 'drop-zone-preview-icon-close');
                spanIconClose.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="7" height="7" viewBox="0 0 10 10">\n' +
                    '<path d="M6.4,5l3.3-3.3c0.4-0.4,0.4-1,0-1.4s-1-0.4-1.4,0L5,3.6L1.7,0.3c-0.4-0.4-1-0.4-1.4,0s-0.4,1,0,1.4L3.6,5L0.3,8.3 c-0.4,0.4-0.4,1,0,1.4C0.5,9.9,0.7,10,1,10s0.5-0.1,0.7-0.3L5,6.4l3.3,3.3C8.5,9.9,8.7,10,9,10s0.5-0.1,0.7-0.3 c0.4-0.4,0.4-1,0-1.4L6.4,5z"></path>\n' +
                    '</svg>';
                spanIconClose.addEventListener('click', cancelFile);
                divAttachement.appendChild(spanIconClose);

                var divProgress = document.createElement('div');
                divProgress.setAttribute('class', 'drop-zone-progress');
                divProgress.setAttribute('style', 'width:0%');
                preview.appendChild(divProgress);

                divAttachementContainer.appendChild(divAttachement);

                var validImageTypes = ["image/gif", "image/jpeg", "image/png"];
                if (validImageTypes.indexOf(fileType) >= 0) {
                    var img = document.createElement('img');
                    img.setAttribute('style', 'width: 100%; max-width: ' + o.previewMaxWidth + '; max-height: ' + o.previewMaxHeight);

                    spanIconFile.appendChild(img);
                    var reader = new FileReader();

                    reader.onload = function (e) {
                        img.setAttribute('src', e.target.result);
                    };

                    reader.readAsDataURL(file);
                } else {
                    spanIconFile.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 20 26">\n' +
                        '<path fill="currentColor" d="M13.41 0H2a2 2 0 0 0-2 2v22a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V6.58L13.41 0zM15 7a2 2 0 0 1-2-2V1l6 6h-4z"></path>\n' +
                        '</svg>';
                }

                if (o.dropZone) {
                    var divReadyState = document.createElement('div');

                    var inputHiddenReady = document.createElement('input');
                    var inputHiddenReadyId = Math.random().toString(36).substring(2, 15) + Math.random().toString(36).substring(2, 15);
                    inputHiddenReady.setAttribute('id', inputHiddenReadyId);
                    inputHiddenReady.setAttribute('type', 'hidden');
                    inputHiddenReady.setAttribute('class', 'required-entry');
                    divReadyState.appendChild(inputHiddenReady);

                    var divReadyError = document.createElement('div');
                    divReadyError.setAttribute('style', 'display:none');
                    divReadyError.setAttribute('class', 'validation-advice');
                    divReadyError.setAttribute('id', 'advice-required-entry-' + inputHiddenReadyId);
                    divReadyError.innerHTML = o.errorMsgNotReady;
                    divReadyState.appendChild(divReadyError);

                    divPreviewFile.appendChild(divReadyState);

                    if (errors.length) {
                        divPreviewInfo.setAttribute('class', 'drop-zone-error');
                        divPreviewInfo.innerHTML = errors.join('<br>');
                        return;
                    }
                    var uploadProgress = function (event) {
                        var percent = parseInt(event.loaded / event.total * 99);
                        divProgress.setAttribute('style', 'width:' + percent + '%');
                        divPreviewInfo.innerHTML = percent + '%';
                    };

                    var stateChange = function (event) {
                        if (event.target.readyState === 4) {
                            if (event.target.status === 200) {
                                inputDropzone.value = '';
                                divProgress.setAttribute('class', 'drop-zone-progress-success');
                                divPreviewInfo.innerHTML = fileSizeKB + 'KB';
                                var result = JSON.parse(event.target.responseText);
                                var error = result.error.join('<br>');
                                hash = result.hash;

                                divPreviewFile.removeChild(divReadyState);

                                if (hash) {
                                    dropZoneFiles.push(hash);
                                    inputHash.value = dropZoneFiles.join(';');
                                }
                                if (error){
                                    divPreviewInfo.setAttribute('class', 'drop-zone-error');
                                    divPreviewInfo.innerHTML = error;
                                }
                            } else {
                                alert(o.errorMsgUploading);
                            }
                        }
                    };

                    var xhr = new XMLHttpRequest();
                    xhr.upload.addEventListener('progress', uploadProgress, false);
                    xhr.onreadystatechange = stateChange;
                    xhr.open('POST', o.url);

                    var formData = new FormData();
                    if(typeof FORM_KEY !== 'undefined')
                        formData.append('form_key', FORM_KEY);
                    formData.append('file_id', field.getAttribute('name'));
                    formData.append(field.getAttribute('name'), file);

                    xhr.send(formData);
                } else {
                    divPreviewInfo.innerHTML = fileSizeKB + 'KB';
                }
                if (fileCnt >= o.maxFiles) {
                    uploadField().setAttribute('style', 'display:none');
                }
            }

        };

        var handleFileSelect = function (evt) {
            var files = evt.element().files;
            fileCnt = dropZoneFiles.length;
            for (var i = 0, f; f = files[i]; i++) {
                processFile(files[i]);
            }
        };

        inputDropzone.addEventListener('change', handleFileSelect);
        field.addEventListener('change', handleFileSelect);
    }

    return JsWebFormsDropzone;
})();

(function () {
    if (typeof define === 'function' && define.amd)
        define('JsWebFormsDropzone', function () {
            return JsWebFormsDropzone;
        });
    else if (typeof module !== 'undefined' && moddivAttachemente.exports)
        module.exports = JsWebFormsDropzone;
    else
        window.JsWebFormsDropzone = JsWebFormsDropzone;
})();