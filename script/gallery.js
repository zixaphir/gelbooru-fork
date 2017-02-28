/*
 *
 * $ object largely based on 4chan X's $, which is largely based on jQuery.
 * non-chainable.
 *
 * Copyright (c) 2009-2011 James Campos <james.r.campos@gmail.com>
 * Copyright (c) 2012-2014 Nicolas Stepien <stepien.nicolas@gmail.com>
 *
 * Permission is hereby granted, free of charge, to any person
 * obtaining a copy of this software and associated documentation
 * files (the "Software"), to deal in the Software without
 * restriction, including without limitation the rights to use,
 * copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the
 * Software is furnished to do so, subject to the following
 * conditions:
 *
 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
 * OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
 * HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
 * WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
 * OTHER DEALINGS IN THE SOFTWARE.
 *
 */

(function() {
  "use strict";
  var $, FNLIMIT, LIMIT, PRELOAD, SimpleDict, THRESHOLD, cb, d, err, formatTime, g, handleVid, loadGallery, mkImage, mkImgEl, mkURL, padNum, preload, preloaded, preloads, query, queryImages, setImage, setup, setupImages, updateImages, vidMeta,
    slice = [].slice;

  d = document;

  FNLIMIT = 255;

  PRELOAD = 1;

  THRESHOLD = 3;

  LIMIT = 100;

  g = {
    nodes: {}
  };

  vidMeta = null;

  (function() {
    var z;
    z = 0;
    return Object.defineProperty(g, "currentImageIndex", {
      set: function(x) {
        var y;
        y = +g.images.length - 1;
        return z = x < 0 ? y : x > y ? 0 : x;
      },
      get: function() {
        return z;
      }
    });
  })();

  $ = function(query, root) {
    if (!root) {
      root = d.body;
    }
    return root.querySelector(query);
  };

  $.$ = function(query, root) {
    if (!root) {
      root = d.body;
    }
    return slice.call(root.querySelectorAll(query));
  };

  $.asap = function(test, fn) {
    var callback;
    callback = function() {
      var err;
      try {
        return fn();
      } catch (error1) {
        err = error1;
        console.log(err.message);
        return console.log(err.stack);
      }
    };
    if (test()) {
      return callback();
    } else {
      return setTimeout($.asap, 25, test, callback);
    }
  };

  $["throw"] = function(error) {
    console.log("Something went wrong. Please file a bug report @ https://github.com/zixaphir/gelbooru-fork including the following:");
    console.log(error.message);
    return console.log(error.stack);
  };

  $.on = function(target, events, fun, once) {
    var event, fn, func, j, len1, ref;
    fn = function() {
      var err;
      try {
        return fun.apply(this, arguments);
      } catch (error1) {
        err = error1;
        return $["throw"](err);
      }
    };
    func = once ? function() {
      $.off(target, events, func);
      return fn.apply(this, arguments);
    } : fn;
    ref = events.split(' ');
    for (j = 0, len1 = ref.length; j < len1; j++) {
      event = ref[j];
      target.addEventListener(event, func, false);
    }
    return func;
  };

  $.off = function(target, events, fn) {
    var event, j, len1, ref;
    ref = events.split(' ');
    for (j = 0, len1 = ref.length; j < len1; j++) {
      event = ref[j];
      target.removeEventListener(event, fn, false);
    }
    return target;
  };

  $.el = function(type, props) {
    var el, prop;
    el = d.createElement(type);
    for (prop in props) {
      if (props.hasOwnProperty(prop)) {
        el[prop] = props[prop];
      }
    }
    return el;
  };

  $.nodes = function(nodes) {
    var frag, j, len1, node;
    if (!(nodes instanceof Array)) {
      return nodes;
    }
    frag = d.createDocumentFragment();
    for (j = 0, len1 = nodes.length; j < len1; j++) {
      node = nodes[j];
      frag.appendChild(node);
    }
    return frag;
  };

  $.html = function(html) {
    var el;
    el = $.el('div', {
      innerHTML: html
    });
    return $.nodes(slice.call(el.children));
  };

  $.escape = (function() {
    var encode;
    encode = {
      '&': '&amp;',
      '<': '&lt;',
      '"': '&quot;',
      '>': '&gt;',
      "'": '&#039;',
      '\/': "&#x2F;"
    };
    return function(text) {
      return text.replace(/[&<>"']/g, function(t) {
        return encode[t];
      });
    };
  })();

  $.add = function(root, nodes) {
    root.appendChild($.nodes(nodes));
    return root;
  };

  $.replace = function(root, el) {
    return root.parentNode.replaceChild($.nodes(el), root);
  };

  SimpleDict = (function() {
    function SimpleDict() {
      this.keys = [];
    }

    SimpleDict.prototype.push = function(key, data) {
      key = "" + key;
      if (!this[key]) {
        this.keys.push(key);
      }
      if (typeof data === 'object') {
        data.key = key;
      }
      return this[key] = data;
    };

    SimpleDict.prototype.contains = function(obj) {
      return this.indexOf(obj) !== -1;
    };

    SimpleDict.prototype.indexOf = function(obj) {
      var i, j, key, len1, ref;
      key = obj.key;
      if (key) {
        if (obj !== this[key]) {
          return -1;
        }
        return this.keys.indexOf(key);
      }
      i = 0;
      ref = this.keys;
      for (j = 0, len1 = ref.length; j < len1; j++) {
        key = ref[j];
        if (this[key] === obj) {
          return i;
        }
        i++;
      }
      return -1;
    };

    SimpleDict.prototype.rm = function(key) {
      var i;
      key = "" + key;
      i = this.keys.indexOf(key);
      if (i !== -1) {
        this.keys.splice(i, 1);
        return delete this[key];
      }
    };

    SimpleDict.prototype.first = function() {
      return this[this.keys[0]];
    };

    SimpleDict.prototype.forEach = function(fn) {
      var j, key, len1, ref;
      ref = slice.call(this.keys);
      for (j = 0, len1 = ref.length; j < len1; j++) {
        key = ref[j];
        fn.call(this, this[key]);
      }
    };

    SimpleDict.prototype.forEachKey = function(fn) {
      var j, key, len1, ref;
      ref = slice.call(this.keys);
      for (j = 0, len1 = ref.length; j < len1; j++) {
        key = ref[j];
        fn.call(this, key);
      }
    };

    Object.defineProperty(SimpleDict.prototype, 'length', {
      get: function() {
        return this.keys.length;
      }
    });

    return SimpleDict;

  })();


  /*
   * media, unlike iframes, will download by merely existing as an object,
   * and will also be unloaded by garbage collection with no references attached.
  preload = (image) ->
    galLength = g.images.length
    i = g.currentImageIndex
    len = Math.min galLength, i + PRELOAD + 1
    $.el 'img', src: g.images[g.images.keys[i]].url while ++i < len
   */

  preloads = {};

  preloaded = {};

  preload = function() {
    var galLength, i, image, img, len;
    galLength = g.images.length;
    i = g.currentImageIndex;
    len = Math.min(galLength, i + PRELOAD + 1);
    while (++i < len) {
      image = g.images[g.images.keys[i]];
      if (preloads[image.id] || preloaded[image.id]) {
        return;
      }
      img = preloads[image.id] = mkImgEl(image);
      $.asap((function() {
        return img.img.complete || img.img.readyState > 3;
      }), function() {
        preloaded[image.id] = true;
        return delete preloads[image.id];
      });
    }
  };

  loadGallery = function() {
    var close, count, err, gal, next, nodes, prev, thumbs;
    try {
      g.gallery = gal = $.el('div', {
        id: 'a-gallery',
        innerHTML: "<div class=\"gal-prev\"></div>\n<div class=\"gal-image\">\n  <div>\n    <div class=\"gal-info\">\n      INFO\n      <span id=\"gal-vid\"></span>\n      <div class=\"gal-ex-info\">\n      </div>\n    </div>\n    <div class=\"gal-close\">&#x274c</div>\n    <a class=\"current\"></a>\n  </div>\n</div>\n<div class=\"gal-next\"></div>\n<div class=\"gal-thumbnails\"></div>"
      });
      nodes = g.nodes;
      nodes.prev = prev = $('.gal-prev', gal);
      nodes.next = next = $('.gal-next', gal);
      nodes.count = count = $('.gal-count', gal);
      nodes.thumbs = thumbs = $('.gal-thumbnails', gal);
      nodes.close = close = $('.gal-close', gal);
      $.on(close, 'click', cb.hideGallery);
      $.on(prev, 'click', cb.prev);
      $.on(next, 'click', cb.next);
      $.on(d, 'keydown', cb.keybinds);
      cb.hideGallery();
      return d.body.appendChild(gal);
    } catch (error1) {
      err = error1;
      return $["throw"](err);
    }
  };

  cb = {
    next: function() {
      ++g.currentImageIndex;
      return cb.updateImage();
    },
    prev: function() {
      --g.currentImageIndex;
      return cb.updateImage();
    },
    updateImage: function() {
      return setImage(g.images[g.images.keys[g.currentImageIndex]]);
    },
    showGallery: function() {
      g.gallery.style.display = 'flex';
      return d.body.style.overflow = 'hidden';
    },
    hideGallery: function() {
      cb.pause();
      g.gallery.style.display = 'none';
      g.currentImageIndex = 0;
      return d.body.style.overflow = 'auto';
    },
    highlight: function(image) {
      var gal, highlight, thumbs;
      if (!image) {
        if (!(image = g.images[g.images.keys[g.currentImageIndex]])) {
          return;
        }
      }
      gal = g.gallery;
      $('.gal-image', gal).scrollTop = 0;
      highlight = $('.gal-highlight', gal);
      if (highlight != null) {
        highlight.classList.remove('gal-highlight');
      }
      highlight = $("[data-id='" + image.id + "']", gal);
      if (highlight != null) {
        highlight.classList.add('gal-highlight');
      }
      thumbs = g.nodes.thumbs;
      return thumbs.scrollTop = highlight.offsetTop + highlight.offsetHeight / 2 - thumbs.clientHeight / 2;
    },
    toggleGallery: function() {
      return cb[g.gallery.style.display === 'block' ? 'hideGallery' : 'showGallery']();
    },
    keybinds: function(e) {
      var fn, key;
      if (!(key = e.keyCode)) {
        return;
      }
      fn = (function() {
        switch (key) {
          case 39:
            return cb.next;
          case 37:
            return cb.prev;
          case 27:
            return cb.hideGallery;
        }
      })();
      if (!fn) {
        return;
      }
      e.stopPropagation();
      e.preventDefault();
      return fn();
    },
    pause: function() {
      var el;
      el = $('.gal-image video', g.gallery);
      if (el && el.pause) {
        return el.pause();
      }
    },
    cleanVid: function() {
      var el, err;
      el = $('.gal-image video', g.gallery);
      try {
        if (el) {
          el.pause();
          return el.src = '';
        }
      } catch (error1) {
        err = error1;
        return $["throw"](err);
      }
    }
  };

  mkImgEl = function(image) {
    var img, ready;
    switch (image.type) {
      case "jpg":
      case "jpeg":
      case "gif":
      case "png":
        img = $.el('img', {
          src: image.url,
          alt: image.tags
        });
        ready = function() {
          return img.complete;
        };
        break;
      default:
        img = $.el('video', {
          src: image.url,
          poster: image.thumb,
          autoplay: true,
          loop: true,
          controls: true
        });
        img.load();
        ready = function() {
          return img.readyState > 2;
        };
        image.video = true;
    }
    return {
      img: img,
      ready: ready
    };
  };

  setImage = function(image) {
    var a, el, err, gal, i, img, info, j, len1, meta, placeHolder, rating, ratingText, ready, ref, ref1, source, tag, tags;
    try {
      gal = g.gallery;
      cb.pause();
      el = $('.gal-image .current', gal);
      g.currentImageIndex = i = g.images.indexOf(image);
      a = $.el('a', {
        href: image.download,
        download: image.filename,
        className: 'current'
      });
      ref = preloads[image.url] || mkImgEl(image), ready = ref.ready, img = ref.img;
      placeHolder = $.el('div', {
        className: 'spinner'
      });
      $.add(a, placeHolder);
      $.asap(ready, function() {
        if (i !== g.currentImageIndex) {
          return;
        }
        $.replace(placeHolder, img);
        return preload();
      });
      if (vidMeta) {
        clearInterval(vidMeta);
        vidMeta = false;
        $('#gal-vid').textContent = '';
      }
      if (image.video) {
        handleVid(img);
      }
      $.replace(el, a);
      a.focus();
      info = $('.gal-ex-info', gal);
      info.textContent = '';
      meta = $.escape("ID: " + image.id + "\nScore: " + (image.score || 0) + "\nPosted: " + image.age + "\nWidth: " + image.width + "\nHeight: " + image.height + "\nType: " + (image.type.toUpperCase()));
      meta = $.html("<p>" + (meta.split('\n').join('</p><p>')) + "</p>");
      $.add(info, meta);
      tags = $.el('p', {
        textContent: "Tags: "
      });
      ref1 = image.tags.split(' ');
      for (j = 0, len1 = ref1.length; j < len1; j++) {
        tag = ref1[j];
        $.add(tags, $.el('a', {
          href: g.baseURL + "page=post&s=list&tags=" + tag,
          textContent: tag,
          target: "_blank"
        }));
        $.add(tags, d.createTextNode(' '));
      }
      $.add(info, tags);
      ratingText = (function() {
        switch (image.rating) {
          case 'e':
            return 'explicit';
          case 's':
            return 'safe';
          default:
            return 'questionable';
        }
      })();
      rating = $.el('p', {
        textContent: "Rating: "
      });
      $.add(rating, $.el('a', {
        href: g.baseURL + "page=post&s=list&tags=rating:" + ratingText,
        textContent: ratingText,
        target: "_blank"
      }));
      $.add(info, rating);
      if (image.source) {
        source = $.el('p', {
          textContent: "Source: "
        });
        $.add(source, $.el('a', {
          href: image.source,
          textContent: image.source,
          target: "_blank"
        }));
        $.add(info, source);
      }
      cb.highlight(image);
      if (i + THRESHOLD > g.images.length) {
        return updateImages();
      }
    } catch (error1) {
      err = error1;
      return $["throw"](err);
    }
  };

  updateImages = function() {
    var queryURL;
    queryURL = mkURL(g.images.length / LIMIT);
    return queryImages(queryURL);
  };

  setupImages = function() {
    var err, j, len1, post, posts, results;
    try {
      if (this.status !== 200) {
        g.error = true;
        return;
      }
      posts = this.response.posts;
      results = [];
      for (j = 0, len1 = posts.length; j < len1; j++) {
        post = posts[j];
        results.push(mkImage(post.post));
      }
      return results;
    } catch (error1) {
      err = error1;
      return $["throw"](err);
    }
  };

  handleVid = function(vid) {
    var el;
    el = $('#gal-vid');
    return vidMeta = setInterval((function() {
      var err;
      try {
        return el.textContent = "(" + (formatTime(vid.currentTime)) + " / " + (formatTime(vid.duration)) + ")";
      } catch (error1) {
        err = error1;
        return $["throw"](err);
      }
    }), 10);
  };

  padNum = function(num) {
    num = "" + num;
    while (num.length < 2) {
      num = "0" + num;
    }
    return num;
  };

  formatTime = function(t) {
    var f, hour, min, msec, sec;
    f = function(x) {
      return Math.floor(x);
    };
    g = function(x) {
      return padNum(x % 60);
    };
    msec = padNum(f(t * 100) % 100);
    sec = g(f(t));
    min = g(f(t / 60));
    hour = g(f(t / 3600));
    return hour + ":" + min + ":" + sec + "." + msec;
  };

  mkImage = function(p) {
    var download, extension, filename, image, ref, tags, thumb, type;
    download = p.file_url;
    type = download.split('.');
    type = ("" + type[type.length - 1]).toLowerCase();
    extension = "." + type;
    tags = p.tags.split(' ');
    while (true) {
      filename = p.id + " - " + (tags.join(' ').trim());
      tags.pop();
      if (!(filename.length + extension.length > FNLIMIT)) {
        break;
      }
    }
    filename += extension;
    image = {
      thumb: p.preview_url,
      url: p.sample_url,
      rating: p.rating,
      source: p.source,
      width: p.width,
      height: p.height,
      score: p.score,
      tags: (ref = p.tags) != null ? ref.trim() : void 0,
      id: p.id,
      age: p.created_at,
      filename: filename,
      download: download,
      type: type
    };
    thumb = $.el('a', {
      href: "index.php?page=post&s=view&id=" + p.id,
      className: 'gal-thumb',
      innerHTML: "<img src='" + image.thumb + "'>"
    });
    thumb.setAttribute('data-id', image.id);
    $.on(thumb, 'click', function(e) {
      e.preventDefault();
      g.currentImageIndex = g.images.indexOf(image);
      return setImage(image);
    });
    $.add(g.nodes.thumbs, thumb);
    g.images.push(p.id, image);
    return image;
  };

  query = function(method, URL, callback) {
    var r;
    r = new XMLHttpRequest();
    r.open("get", URL, true);
    r.responseType = 'json';
    $.on(r, "load error abort", callback, true);
    r.send();
    return r;
  };

  queryImages = function(URL) {
    if (g.error) {
      return;
    }
    return query("get", URL, setupImages);
  };

  mkURL = function(pid) {
    var j, key, len1, queryURL, ref;
    if (pid) {
      g.attr.pid = pid;
    }
    queryURL = g.baseURL;
    ref = g.attr.keys;
    for (j = 0, len1 = ref.length; j < len1; j++) {
      key = ref[j];
      queryURL += key + "=" + g.attr[key] + "&";
    }
    return queryURL.slice(0, -1);
  };

  setup = function() {
    var attr, galToggle, j, len1, path, ref;
    g.images = new SimpleDict();
    g.host = d.location;
    g.attr = new SimpleDict();
    ref = g.host.search.slice(1).split('&');
    for (j = 0, len1 = ref.length; j < len1; j++) {
      attr = ref[j];
      attr = attr.split('=');
      g.attr.push(attr[0].toLowerCase(), attr[1].toLowerCase());
    }
    if (g.attr.s === 'view') {
      return;
    }
    path = (g.host.pathname.split('/').slice(0, -1).join('/')) + "/";
    g.baseURL = g.host.protocol + "//" + g.host.hostname + "/" + path + "index.php?";
    g.attr.push('page', 'dapi');
    g.attr.push('q', 'index');
    g.attr.push('s', 'post');
    g.attr.push('t', 'json');
    g.attr.push('limit', 100);
    if (g.attr.tags === 'all') {
      g.attr.rm('tags');
    }
    if (g.attr.pid) {
      g.attr.pid = ~~(g.attr.pid / 100);
    } else {
      g.attr.push('pid', 0);
    }
    g.queryURL = mkURL();
    galToggle = $('#gal-toggle');
    $.on(galToggle, 'click', function() {
      g.currentImageIndex = 0;
      cb.updateImage();
      return cb.showGallery();
    });
    loadGallery();
    return queryImages(g.queryURL);
  };

  try {
    setup();
  } catch (error1) {
    err = error1;
    $["throw"](err);
  }

}).call(this);
