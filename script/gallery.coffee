###
#
# $ object largely based on 4chan X's $, which is largely based on jQuery.
# non-chainable.
#
# Copyright (c) 2009-2011 James Campos <james.r.campos@gmail.com>
# Copyright (c) 2012-2014 Nicolas Stepien <stepien.nicolas@gmail.com>
#
# Permission is hereby granted, free of charge, to any person
# obtaining a copy of this software and associated documentation
# files (the "Software"), to deal in the Software without
# restriction, including without limitation the rights to use,
# copy, modify, merge, publish, distribute, sublicense, and/or sell
# copies of the Software, and to permit persons to whom the
# Software is furnished to do so, subject to the following
# conditions:
#
# The above copyright notice and this permission notice shall be
# included in all copies or substantial portions of the Software.
# THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
# EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
# OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
# NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
# HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
# WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
# FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
# OTHER DEALINGS IN THE SOFTWARE.
#
###

"use strict"

d = document

# post  = actual post object on gelbooru, complete with metadata
# image = the visual media to be displayed

FNLIMIT    = 255 # filename character limit
PRELOAD    = 1   # Number of images to preload
THRESHOLD  = 3   # Number of posts remaining before loading next set.
LIMIT      = 100 # Number of posts to fetch per request, capped at 100 by API limits.

g =
  nodes: {}
# queryURL
# host
# attr
# images
# currentImageIndex

vidMeta = null

do -> # wrap in an anonymous function to closure z.
  z = 0
  # Fucking CoffeeScript!!
  # https://github.com/jashkenas/coffeescript/wiki/FAQ#unsupported-features
  Object.defineProperty g, "currentImageIndex",
    set: (x) ->
      y = +g.images.length - 1
      z = if x < 0
        y
      else if x > y
        0
      else
        x
    get: -> z

$ = (query, root) ->
  root = d.body unless root
  root.querySelector query

$.$ = (query, root) ->
  root = d.body unless root
  [(root.querySelectorAll query)...]

$.asap = (test, fn) ->
  callback = ->
    try
      fn()
    catch err
      console.log err.message
      console.log err.stack

  if test()
    callback()
  else
    setTimeout $.asap, 25, test, callback

$.throw = (error) ->
  console.log "Something went wrong. Please file a bug report
    @ https://github.com/zixaphir/gelbooru-fork including the following:"
  console.log error.message
  console.log error.stack

$.on = (target, events, fun, once) ->
  fn = -> try
    # Events are difficult to debug enough without appropriate error trappings...
    fun.apply this, arguments
  catch err
    $.throw err

  func = if once then ->
    $.off target, events, func
    fn.apply @, arguments
  else
    fn

  target.addEventListener event, func, false for event in events.split ' '

  return func

$.off = (target, events, fn) ->
  target.removeEventListener event, fn, false for event in events.split ' '
  target

$.el = (type, props) ->
  el = d.createElement type
  el[prop] = props[prop] for prop of props when props.hasOwnProperty prop
  el

$.nodes = (nodes) ->
  return nodes unless nodes instanceof Array
  frag = d.createDocumentFragment()
  frag.appendChild node for node in nodes
  return frag

$.html = (html) ->
  el = $.el 'div',
    innerHTML: html
  $.nodes [(el.children)...]

$.escape = do ->
  encode =
    '&': '&amp;'
    '<': '&lt;'
    '"': '&quot;'
    '>': '&gt;'
    "'": '&#039;'
    '\/': "&#x2F;"
  return (text) ->
    return text.replace(/[&<>"']/g, (t) -> encode[t])

$.add = (root, nodes) ->
  root.appendChild $.nodes nodes
  return root

$.replace = (root, el) ->
  root.parentNode.replaceChild $.nodes(el), root

class SimpleDict
  constructor: ->
    @keys = []

  push: (key, data) ->
    key = "#{key}"
    @keys.push key unless @[key]
    data.key = key if typeof data is 'object' # I'm a jerk and decided data can be numbers & strings
    @[key] = data

  contains: (obj) -> @indexOf(obj) isnt -1

  indexOf: (obj) ->
    {key} = obj
    if key
      # Though unlikely, the SimpleDict may contain a different item with the same ID. In that case,
      # it would not contain *this* object, so return -1. Also this allows us to take a small short
      # cut, since object lookup would also tell us if this object is contained at all, cutting out
      # the slightly more expensive indexOf lookup.
      return -1 unless obj is @[key]

      return @keys.indexOf key

    i = 0
    for key in @keys
      return i if @[key] is obj
      i++
    return -1

  rm: (key) ->
    key = "#{key}"
    i = @keys.indexOf key
    if i isnt -1
      @keys.splice i, 1 # yuck.
      delete @[key]

  first: -> @[@keys[0]]

  forEach: (fn) ->
    fn.call @, @[key] for key in [@keys...]
    return

  forEachKey: (fn) ->
    fn.call @, key for key in [@keys...]
    return

  Object.defineProperty SimpleDict::, 'length',
    get: -> return @keys.length
###
# media, unlike iframes, will download by merely existing as an object,
# and will also be unloaded by garbage collection with no references attached.
preload = (image) ->
  galLength = g.images.length
  i = g.currentImageIndex
  len = Math.min galLength, i + PRELOAD + 1
  $.el 'img', src: g.images[g.images.keys[i]].url while ++i < len
###

preloads = {}
preloaded = {}

preload = () ->
  galLength = g.images.length
  i = g.currentImageIndex
  len = Math.min galLength, i + PRELOAD + 1
  while ++i < len
    image = g.images[g.images.keys[i]]
    return if preloads[image.id] or preloaded[image.id] # already preloaded or preloading
    img = preloads[image.id] = mkImgEl image # Build a usable image object, with metadata and el
    $.asap(
      (->
        return img.img.complete or img.img.readyState > 3 # We want a completely loaded media
      ),
      -> # Cache status & cleanup
        preloaded[image.id] = true
        delete preloads[image.id]
    )

loadGallery = -> try
  g.gallery = gal = $.el 'div',
    id: 'a-gallery'
    innerHTML: """
      <div class="gal-prev"></div>
      <div class="gal-image">
        <div>
          <div class="gal-info">
            INFO
            <span id=\"gal-vid\"></span>
            <div class="gal-ex-info">
            </div>
          </div>
          <div class="gal-close">&#x274c</div>
          <a class="current"></a>
        </div>
      </div>
      <div class="gal-next"></div>
      <div class="gal-thumbnails"></div>
    """

  {nodes} = g

  nodes.prev   = prev   = $ '.gal-prev', gal
  nodes.next   = next   = $ '.gal-next', gal
  nodes.count  = count  = $ '.gal-count', gal
  nodes.thumbs = thumbs = $ '.gal-thumbnails', gal
  nodes.close  = close  = $ '.gal-close', gal

  $.on close, 'click',   cb.hideGallery
  $.on prev,  'click',   cb.prev
  $.on next,  'click',   cb.next
  $.on d,     'keydown', cb.keybinds

  cb.hideGallery()
  d.body.appendChild gal

catch err
  $.throw err

cb =
  next: ->
    ++g.currentImageIndex
    cb.updateImage()
  prev: ->
    --g.currentImageIndex
    cb.updateImage()
  updateImage: -> setImage g.images[g.images.keys[g.currentImageIndex]]
  showGallery: ->
    g.gallery.style.display = 'flex'
    d.body.style.overflow = 'hidden'
  hideGallery: ->
    cb.pause()
    g.gallery.style.display = 'none'
    g.currentImageIndex = 0
    d.body.style.overflow = 'auto'
  highlight: (image) ->
    unless image
      return unless image = g.images[g.images.keys[g.currentImageIndex]]

    gal = g.gallery
    $('.gal-image', gal).scrollTop = 0
    highlight = $ '.gal-highlight', gal
    highlight?.classList.remove 'gal-highlight'

    highlight = $ "[data-id='#{image.id}']", gal
    highlight?.classList.add 'gal-highlight'
    thumbs = g.nodes.thumbs
    thumbs.scrollTop = highlight.offsetTop + highlight.offsetHeight/2 - thumbs.clientHeight/2

  toggleGallery: ->
    cb[if g.gallery.style.display is 'block' then 'hideGallery' else 'showGallery']()

  keybinds: (e) ->
    return unless key = e.keyCode

    fn = switch key
      when 39
        cb.next
      when 37
        cb.prev
      when 27
        cb.hideGallery

    return unless fn

    e.stopPropagation()
    e.preventDefault()
    fn()

  pause: ->
    el = $ '.gal-image video', g.gallery
    el.pause() if el and el.pause

  cleanVid: ->
    el = $ '.gal-image video', g.gallery
    try
      if el
        el.pause()
        el.src = ''
    catch err
      $.throw err

mkImgEl = (image) ->
  switch image.type
    when "jpg", "jpeg", "gif", "png"
      img = $.el 'img', {
        src: image.url
        alt: image.tags
      }
      ready = -> return img.complete
    else
      img = $.el 'video',
        src: image.url
        poster: image.thumb
        autoplay: true
        loop: true
        controls: true

      img.load()
      ready = -> return img.readyState > 2
      image.video = true

  return {
    img: img,
    ready: ready,
  }

setImage = (image) -> try
  gal = g.gallery
  cb.pause()
  el = $ '.gal-image .current', gal

  g.currentImageIndex = i = g.images.indexOf image

  a = $.el 'a',
    href:      image.download
    download:  image.filename
    className: 'current'

  {ready, img} = preloads[image.url] or mkImgEl image

  placeHolder = $.el 'div', className: 'spinner'
  $.add a, placeHolder
  $.asap ready, ->
    return if i isnt g.currentImageIndex # user may have navigated to another image.
    $.replace placeHolder, img
    preload()

  if vidMeta
    clearInterval vidMeta
    vidMeta = false
    $('#gal-vid').textContent = ''

  handleVid img if image.video

  $.replace el, a

  a.focus()

  info = $ '.gal-ex-info', gal
  info.textContent = ''

  meta = $.escape """
    ID: #{image.id}
    Score: #{image.score || 0}
    Posted: #{image.age}
    Width: #{image.width}
    Height: #{image.height}
    Type: #{image.type.toUpperCase()}
  """
  meta = $.html "<p>#{meta.split('\n').join('</p><p>')}</p>"
  $.add info, meta

  tags = $.el 'p',
    textContent: "Tags: "

  for tag in image.tags.split ' '
    $.add tags, $.el 'a',
      href: "#{g.baseURL}page=post&s=list&tags=#{tag}"
      textContent: tag
      target: "_blank"
    $.add tags, d.createTextNode(' ')
  $.add info, tags

  ratingText =
    switch image.rating
      when 'e'
        'explicit'
      when 's'
        'safe'
      else
        'questionable'

  rating = $.el 'p', textContent: "Rating: "
  $.add rating, $.el 'a',
    href: "#{g.baseURL}page=post&s=list&tags=rating:#{ratingText}"
    textContent: ratingText
    target: "_blank"
  $.add info, rating

  if image.source
    source = $.el 'p', textContent: "Source: "
    $.add source, $.el 'a',
      href: image.source
      textContent: image.source
      target: "_blank"
    $.add info, source

  cb.highlight image

  if i + THRESHOLD > g.images.length
    updateImages()

catch err
  $.throw err

updateImages = ->
  queryURL = mkURL g.images.length / LIMIT
  queryImages queryURL

setupImages = -> try
  if @status isnt 200
    g.error = true
    return

  posts = @response.posts

  mkImage post.post for post in posts

catch err
  $.throw err

handleVid = (vid) ->
  el = $ '#gal-vid'
  vidMeta = setInterval(
    (-> try
      el.textContent = "(#{formatTime vid.currentTime} / #{formatTime vid.duration})"
    catch err
      $.throw err
    ), 10
  )

padNum = (num) ->
  num = "#{num}"
  while num.length < 2
    num = "0#{num}"
  return num

formatTime = (t) ->
  f = (x) -> Math.floor x
  g = (x) -> return padNum x % 60

  msec = padNum f(t * 100) % 100
  sec  = g(f(t))
  min  = g(f(t / 60))
  hour = g(f(t / 3600))

  return hour + ":" + min + ":" + sec + "." + msec

mkImage = (p) ->
  download = p.file_url

  type = download.split '.'
  type = "#{type[type.length - 1]}".toLowerCase()

  extension = ".#{type}"

  tags = p.tags.split ' '
  while true
    filename = "#{p.id} - #{tags.join(' ').trim()}"
    tags.pop()
    break unless filename.length + extension.length > FNLIMIT

  filename += extension

  image = {
    thumb:     p.preview_url
    url:       p.sample_url
    rating:    p.rating
    source:    p.source
    width:     p.width
    height:    p.height
    score:     p.score
    tags:      p.tags?.trim()
    id:        p.id
    age:       p.created_at
    filename:  filename
    download:  download
    type:      type
  }

  thumb = $.el 'a',
    href: "index.php?page=post&s=view&id=#{p.id}"
    className: 'gal-thumb'
    innerHTML: "<img src='#{image.thumb}'>"

  thumb.setAttribute 'data-id', image.id

  $.on thumb, 'click', (e) ->
    e.preventDefault()
    g.currentImageIndex = g.images.indexOf image
    setImage image

  $.add g.nodes.thumbs, thumb

  g.images.push p.id, image

  return image

query = (method, URL, callback) ->
  r = new XMLHttpRequest()
  r.open "get", URL, true
  r.responseType = 'json'
  $.on r, "load error abort", callback, true
  r.send()
  r

queryImages = (URL) ->
  return if g.error
  query "get", URL, setupImages

mkURL = (pid) ->
  g.attr.pid = pid if pid
  queryURL = g.baseURL
  queryURL += "#{key}=#{g.attr[key]}&" for key in g.attr.keys
  queryURL[...-1]

setup = ->
  g.images = new SimpleDict()
  g.host   = d.location

  # Generate queries for API access
  g.attr = new SimpleDict()
  for attr in g.host.search[1..].split '&'
    attr = attr.split '='
    g.attr.push attr[0].toLowerCase(), attr[1].toLowerCase()

  return if g.attr.s is 'view'

  path = "#{g.host.pathname.split('/')[...-1].join('/')}/"
  g.baseURL = "#{g.host.protocol}//#{g.host.hostname}/#{path}index.php?"

  # Setup Booru API requirements.
  g.attr.push 'page',  'dapi'
  g.attr.push 'q',     'index'
  g.attr.push 's',     'post'
  g.attr.push 't',     'json'
  g.attr.push 'limit', 100

  # The "all" tag does not work the same on the API as it does on the site.
  if g.attr.tags is 'all'
    g.attr.rm 'tags'

  if g.attr.pid
    # On API, PID is the page offset (pid * limit).
    # On the site, PID is the image offset.
    # The API accepts float values.
    g.attr.pid = ~~(g.attr.pid / 100)
  else
    g.attr.push 'pid', 0

  g.queryURL = mkURL()

  galToggle = $ '#gal-toggle'
  $.on galToggle, 'click', ->
    g.currentImageIndex = 0
    cb.updateImage()
    cb.showGallery()

  loadGallery()
  queryImages g.queryURL

try
  setup()
catch err
  $.throw err
