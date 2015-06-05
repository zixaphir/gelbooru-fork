(function() {
  "use strict";
  var FileDragHover, FileSelectHandler, SIZELIMIT, abort, addEvent, addEvents, append, asap, busy, bytesToString, clean, current, d, dropEl, error, escapeRE, fCount, failedCount, files, form, forms, input, keepGoing, load, logError, mkElement, mkForm, mkInput, mkThumb, myTags, name, prepare, progress, progressCell, progressRow, r, removeForm, roundToPlace, showError, size, total, upload, uploadAll, uploadAllBtn, uploadFail, uploadingAll,
    slice = [].slice;

  SIZELIMIT = 10 * 1024 * 1024;

  d = document;

  forms = [];

  files = {};

  uploadingAll = uploadAllBtn = input = busy = name = size = r = fCount = total = current = progressRow = progressCell = failedCount = dropEl = form = 0;

  mkElement = function(tag, prop) {
    var el, key, val;
    el = d.createElement(tag);
    for (key in prop) {
      val = prop[key];
      if (prop.hasOwnProperty(key)) {
        el[key] = val;
      }
    }
    return el;
  };

  addEvent = function(el, evt, fn) {
    return el.addEventListener(evt, fn, false);
  };

  roundToPlace = function(val, place) {
    var x;
    x = 1;
    while (place > 0) {
      x *= 10;
      place--;
    }
    return Math.round(val * x) / x;
  };

  asap = function(test, cb) {
    if (test()) {
      return cb();
    }
    return setTimeout($.asap, 25, test, cb);
  };

  bytesToString = function(val) {
    var unit;
    unit = 0;
    while (val >= 1024) {
      val /= 1024;
      unit++;
    }
    val = unit >= 1 ? roundToPlace(val, 2) : Math.round(val);
    return val + " " + ['B', 'KB', 'MB', 'GB'][unit];
  };

  escapeRE = function(str) {
    return str.replace(/[\-\[\]\/\{\}\(\)\*\+\?\.\\\^\$\|]/g, "\\$&");
  };

  mkThumb = function(row) {
    var attachID, confirmButton, img;
    attachID = button.getAttribute("onclick").match(/attachment=([0-9]+)/)[1];
    img = row.querySelector("img");
    img.src = "attachment.php?thumbnail=" + attachID;
    confirmButton = false;
  };

  prepare = function(inputFiles) {
    var err, file, j, len;
    try {
      uploadAllBtn.style.display = 'inline-block';
      for (j = 0, len = inputFiles.length; j < len; j++) {
        file = inputFiles[j];
        if (mkForm(file)) {
          total++;
        }
      }
      input.val = '';
    } catch (_error) {
      err = _error;
      console.log(err.message);
      return console.log(err.stack);
    }
  };

  mkInput = function(name) {
    var el, lower;
    lower = name.toLowerCase();
    el = mkElement('input', {
      type: 'text',
      name: lower,
      placeholder: name,
      className: lower
    });
    el.style.width = '350px';
    el.style.margin = '2px';
    return el;
  };

  addEvents = function(tagEl, tagInput) {
    var j, len, results, tag, tags;
    tags = slice.call(tagEl.querySelectorAll('.tag'));
    results = [];
    for (j = 0, len = tags.length; j < len; j++) {
      tag = tags[j];
      results.push(addEvent(tag, 'click', function(e) {
        var k, len1, match, nTags;
        e.preventDefault();
        e.stopPropagation();
        name = this.textContent;
        nTags = [];
        tags = tagInput.value.split(' ');
        for (k = 0, len1 = tags.length; k < len1; k++) {
          tag = tags[k];
          if (tag === name) {
            match = true;
            continue;
          }
          nTags.push(tag);
        }
        if (match) {
          return tagInput.value = nTags.join(' ').trim();
        } else {
          return tagInput.value = (tagInput.value + " " + name).trim();
        }
      }));
    }
    return results;
  };

  mkForm = function(file) {
    var child, div, el, fileURL, isVideo, j, k, label, left, len, len1, mTags, mkbrk, nForm, par, rating, ref, ref1, rem, right, row, source, submit, tags, tbody, tempEl, title, type;
    if (file.size > SIZELIMIT) {
      div = mkElement('div', {
        textContent: file.name + " is too big! Size limit is 10MB."
      });
      div.style.margin = "5px";
      div.style.padding = "5px";
      div.style.border = "1px solid black";
      append(div);
      return false;
    }
    mTags = myTags.cloneNode(true);
    fileURL = URL.createObjectURL(file);
    isVideo = /^video\//.test(file.type);
    el = mkElement('img');
    el.style.maxHeight = '200px';
    el.style.maxWidth = '200px';
    el.style.display = 'inline-block';
    el.style.margin = "auto";

    /*
     * From 4chan X
     */
    tempEl = mkElement(isVideo ? 'video' : 'img');
    addEvent(tempEl, (isVideo ? 'loadeddata' : 'load'), function() {
      var cv, err, height, s, width;
      try {
        s = 90 * 2 * window.devicePixelRatio;
        if (file.type === 'image/gif') {
          s *= 3;
        }
        if (isVideo) {
          height = tempEl.videoHeight;
          width = tempEl.videoWidth;
        } else {
          height = tempEl.height, width = tempEl.width;
          if (height < s || width < s) {
            el.src = fileURL;
            return;
          }
        }
        if (height <= width) {
          width = s / height * width;
          height = s;
        } else {
          height = s / width * height;
          width = s;
        }
        cv = mkElement('canvas', {
          height: tempEl.height = height,
          width: tempEl.width = width
        });
        cv.getContext('2d').drawImage(tempEl, 0, 0, width, height);
        URL.revokeObjectURL(fileURL);
        return cv.toBlob((function(_this) {
          return function(blob) {
            var nURL;
            nURL = URL.createObjectURL(blob);
            return el.src = nURL;
          };
        })(this));
      } catch (_error) {
        err = _error;
        console.log(err.message);
        return console.log(err.stack);
      }
    });
    tempEl.src = fileURL;
    source = mkInput('Source');
    title = mkInput('Title');
    tags = mkInput('Tags');
    addEvents(mTags, tags);
    rating = mkElement('div', {
      textContent: "Rating: ",
      className: 'rating'
    });
    rating.style.margin = '2px';
    ref = ["Explicit", "Questionable", "Safe"];
    for (j = 0, len = ref.length; j < len; j++) {
      type = ref[j];
      label = mkElement('label');
      label.appendChild(mkElement('input', {
        name: 'rating',
        type: 'radio',
        value: type[0].toLowerCase(),
        id: 'r_' + type.toLowerCase(),
        checked: type === "Questionable" ? true : false
      }));
      label.appendChild(d.createTextNode(" " + type + " "));
      rating.appendChild(label);
    }
    submit = mkElement('input', {
      type: 'submit',
      value: 'Upload'
    });
    nForm = mkElement('form', {
      action: '',
      method: 'post',
      enctype: 'multipart/form-data',
      className: 'multi-upload'
    });
    nForm.dataset.file = fCount;
    files[fCount] = file;
    fCount++;
    par = mkElement('table');
    par.style.margin = "5px";
    par.style.width = "593px";
    par.style.position = 'relative';
    rem = mkElement('a', {
      href: 'javascript:;',
      textContent: 'X'
    });
    rem.style.cssText = "position: absolute; right: 10px; bottom: 10px;";
    addEvent(rem, 'click', function() {
      return removeForm(nForm);
    });
    par.appendChild(tbody = mkElement('tbody'));
    tbody.appendChild(row = mkElement('tr'));
    par.style.borderCollapse = "collapse";
    left = mkElement('td', {
      className: 'preview'
    });
    left.style.width = "200px";
    left.style.padding = '5px';
    left.style.textAlign = "center";
    right = mkElement('td', {
      className: 'meta-data'
    });
    right.style.padding = "5px";
    right.style.border = par.style.border = left.style.border = "1px solid black";
    left.appendChild(el);
    left.appendChild(rem);
    mkbrk = function() {
      return mkElement('br');
    };
    ref1 = [source, mkbrk(), title, mkbrk(), tags, mkbrk(), rating, mTags, mkbrk(), submit];
    for (k = 0, len1 = ref1.length; k < len1; k++) {
      child = ref1[k];
      right.appendChild(child);
    }
    par.appendChild(left);
    par.appendChild(right);
    nForm.appendChild(par);
    nForm.normalize();
    append(nForm);
    addEvent(submit, 'click', function(e) {
      e.preventDefault();
      e.stopPropagation();
      return upload(nForm);
    });
    return nForm;
  };

  removeForm = function(formEl) {
    var i;
    if (busy && r.element === formEl) {
      if (confirm('Abort current upload?')) {
        r.abort();
      } else {
        return;
      }
    }
    if ((i = forms.indexOf(form)) > -1) {
      forms.splice(i, 1);
    }
    return formEl.parentElement.removeChild(formEl);
  };

  append = function(el) {
    return form.parentElement.insertBefore(el, form);
  };

  upload = function(data) {
    var error, fdata, submit;
    if (busy) {
      alert('currently uploading. :(');
      return false;
    }
    try {
      busy = true;
      r = new XMLHttpRequest();
      r.element = data;
      submit = data.getAttribute('action');
      r.open("POST", submit, true);
      r.responseType = 'document';
      progressCell = data.querySelector('.meta-data');
      fdata = new FormData(data);
      fdata.append('upload', files[data.dataset.file]);
      addEvent(r.upload, 'progress', progress);
      addEvent(r, 'load', load);
      addEvent(r, 'error', error);
      addEvent(r, 'abort', abort);
      r.send(fdata);
    } catch (_error) {
      error = _error;
      busy = false;
      uploadFail(error);
      if (uploadingAll) {
        setTimeout(keepGoing, 0);
      }
      return false;
    }
    return true;
  };

  load = function(arg1) {
    var body, error, response, target;
    target = arg1.target;
    try {
      response = target.response;
      body = response.body;
      progressCell.textContent = "Upload Complete: " + body.firstChild.textContent;
    } catch (_error) {
      error = _error;
      uploadFail(error, body);
    }
    current++;
    if (uploadingAll) {
      busy = false;
      return keepGoing();
    } else {
      return clean();
    }
  };

  progress = function(e) {
    var el, n, percent, sent;
    n = e.loaded / e.total;
    sent = bytesToString(~~(size * n));
    percent = ~~(n * 100) + "/100%";
    progressCell.textContent = " Current: " + percent + ". Sent: " + sent + "/" + (bytesToString(size)) + " Total: " + current + "/" + total + " ";
    if (!failedCount) {
      return;
    }
    el = mkElement('span', {
      textContent: "Failed: " + failedCount + "/" + total
    });
    progressCell.appendChild(el);
  };

  error = function(e) {
    busy = false;
    current++;
    uploadFail({
      error: "Upload Failed: " + e.status + ": " + e.statusText
    });
    if (uploadingAll) {
      return keepGoing();
    }
  };

  abort = clean;

  keepGoing = function() {
    var cur;
    cur = forms.shift();
    if (!cur) {
      return clean();
    }
    cur.className += " done";
    return upload(cur);
  };

  uploadAll = function() {
    forms = slice.call(d.querySelectorAll('.multi-upload:not(.done)'));
    uploadingAll = true;
    return keepGoing();
  };

  FileDragHover = function(e) {
    e.stopPropagation();
    e.preventDefault();
    return dropEl.style.opacity = e.type === "dragover" ? "1" : "0";
  };

  FileSelectHandler = function(e) {
    var draggedFiles;
    FileDragHover(e);
    draggedFiles = e.target.files || e.dataTransfer.files;
    return prepare(draggedFiles);
  };

  clean = function() {
    busy = false;
    return uploadingAll = false;
  };

  uploadFail = function() {
    var args, error;
    error = arguments[0], args = 2 <= arguments.length ? slice.call(arguments, 1) : [];
    failedCount++;
    showError(error);
    return logError.apply(null, [error].concat(slice.call(args)));
  };

  showError = function(error) {
    var row;
    return row = mkElement('div', {
      innerHTML: "<div>" + error.message + "</div>\n" + (error.stack ? "<pre>" + error.stack + "</pre>" : '')
    });
  };

  logError = function() {
    var arg, args, error, j, len;
    error = arguments[0], args = 2 <= arguments.length ? slice.call(arguments, 1) : [];
    console.error("DEBUG: ");
    console.error(error.message + "\n" + error.stack);
    for (j = 0, len = args.length; j < len; j++) {
      arg = args[j];
      console.error(arg);
    }
    return console.error("END DEBUG");
  };

  myTags = (function() {
    var a, cookie, j, k, len, len1, ref, ref1, span, tag, tags;
    ref = d.cookie.split(';');
    for (j = 0, len = ref.length; j < len; j++) {
      cookie = ref[j];
      if (!(cookie = cookie.trim())) {
        continue;
      }
      cookie = cookie.split('=');
      if (cookie[0] === 'tags') {
        tags = cookie[1];
        break;
      }
    }
    span = mkElement('span', {
      textContent: "My Tags: "
    });
    if (tags) {
      ref1 = tags.trim().split(/(\%2520)|\+/);
      for (k = 0, len1 = ref1.length; k < len1; k++) {
        tag = ref1[k];
        a = mkElement('a', {
          href: "index.php?page=post&s=list&tags=" + tag,
          className: "tag",
          textContent: tag
        });
        span.appendChild(a);
        span.appendChild(d.createTextNode(' '));
      }
    }
    span.appendChild(mkElement('a', {
      href: 'index.php?page=account-options',
      textContent: 'Edit'
    }));
    return span;
  })();

  asap((function() {
    return input = d.querySelector('[name="upload"]');
  }), function() {
    form = d.querySelector('form');
    form.style.display = 'none';
    uploadAllBtn = mkElement('input', {
      type: 'submit',
      value: 'Upload All!'
    });
    uploadAllBtn.style.display = 'none';
    form.parentElement.appendChild(uploadAllBtn);
    addEvent(uploadAllBtn, 'click', uploadAll);
    addEvent(input, 'change', function() {
      return prepare(slice.call(input.files));
    });
    append(input);
    input.multiple = true;
    dropEl = mkElement('div', {
      innerHTML: "<span style='position: relative; top: 50%; transform: translateY(-50%);'>Drop File to Upload!</span>"
    });
    dropEl.style.cssText = "z-index: 9999; position: fixed; top: 0; bottom: 0; left: 0; right: 0; text-align: center; border: double 1cm #cce; background-color: #ddf; color: #aac; font-size: 24pt; font-family: calibri; opacity: 0; pointer-events: none; transition: opacity .5s;";
    d.body.insertBefore(dropEl, d.body.firstElementChild);
    addEvent(d, 'dragover', FileDragHover);
    addEvent(d, 'dragleave', FileDragHover);
    addEvent(d, 'drop', FileSelectHandler);
  });

}).call(this);