var Note = Class.create()
Note.zindex = 0
Note.counter = -1
Note.all = []
Note.display = true

Note.show = function() {
	for (var i=0; i<Note.all.length; ++i) {
		Note.all[i].bodyHide()
		Note.all[i].elements.box.style.display = "block"
	}
}

Note.hide = function() {
	for (var i=0; i<Note.all.length; ++i) {
		Note.all[i].bodyHide()
		Note.all[i].elements.box.style.display = "none"
	}
}

Note.find = function(id) {
	for (var i = 0; i<Note.all.length; ++i) {
		if (Note.all[i].id == id) {
			return Note.all[i]
		}
	}

	return null
}

Note.toggle = function() {
	if (Note.display) {
		Note.hide()
		Note.display = false
	} else {
		Note.show()
		Note.display = true
	}
}

Note.updateNoteCount = function() {
	if (Note.all.length > 0) {
		var label = ""

		if (Note.all.length == 1)
			label = "note"
		else
			label = "notes"

		$('note-count').innerHTML = "This post has <a href=\"#\" onclick=\"document.location='index.php?page=history&type=page_notes&id=" + Note.post_id + "'\">" + Note.all.length + " " + label + "</a>"
	} else {
		$('note-count').innerHTML = ""
	}
};

Note.create = function() {
	var note = ''
	note += '<div class="note-box" style="width: 150px; height: 150px; '
	note += 'top: ' + ($('image').clientHeight / 2 - 75) + 'px; '
	note += 'left: ' + ($('image').clientWidth / 2 - 75) + 'px;" '
	note += 'id="note-box-' + Note.counter + '">'
	note += '<div class="note-corner" id="note-corner-' + Note.counter + '"></div>'
	note += '</div>'
	note += '<div class="note-body" title="Click to edit" id="note-body-' + Note.counter + '"></div>'
	new Insertion.Bottom('note-container', note)
	Note.all.push(new Note(Note.counter, true))
	Note.counter -= 1
};

Note.prototype = {
	// Necessary because addEventListener/removeEventListener don't play nice with
	// different instantiations of the same method.
	bind: function(method_name) {
		if (!this.bound_methods) {
			this.bound_methods = new Object()
		}

		if (!this.bound_methods[method_name]) {
			this.bound_methods[method_name] = this[method_name].bindAsEventListener(this)
		}

		return this.bound_methods[method_name]
	},

	initialize: function(id, is_new) {
		this.id = id
		this.is_new = is_new

		// get the elements
		this.elements = {
			box:		$('note-box-' + this.id),
			corner:		$('note-corner-' + this.id),
			body:		$('note-body-' + this.id),
			image:		$('image')
		}

		// store the data
		this.old = {
			left:		this.elements.box.offsetLeft,
			top:		this.elements.box.offsetTop,
			width:		this.elements.box.clientWidth,
			height:		this.elements.box.clientHeight,
			body:		this.elements.body.innerHTML
		}

		// reposition the box to be relative to the image

		this.elements.box.style.top = this.elements.box.offsetTop + "px"
		this.elements.box.style.left = this.elements.box.offsetLeft + "px"

		// attach the event listeners
		Event.observe(this.elements.box, "mousedown", this.bind("dragStart"), true)
		Event.observe(this.elements.box, "mouseout", this.bind("bodyHideTimer"), true)
		Event.observe(this.elements.box, "mouseover", this.bind("bodyShow"), true)
		Event.observe(this.elements.corner, "mousedown", this.bind("resizeStart"), true)
		Event.observe(this.elements.body, "mouseover", this.bind("bodyShow"), true)
		Event.observe(this.elements.body, "mouseout", this.bind("bodyHideTimer"), true)
		Event.observe(this.elements.body, "click", this.bind("showEditBox"), true)
	},

	textValue: function() {
		return this.old.body.replace(/(?:^\s+|\s+$)/, '')
	},

	hideEditBox: function(e) {
		var editBox = $('edit-box')

		if (editBox != null) {
			// redundant?
			Event.stopObserving('note-save-' + this.id, 'click', this.bind("save"), true)
			Event.stopObserving('note-cancel-' + this.id, 'click', this.bind("cancel"), true)
			Event.stopObserving('note-remove-' + this.id, 'click', this.bind("remove"), true)
			Event.stopObserving('note-history-' + this.id, 'click', this.bind("history"), true)

			Element.remove('edit-box')
		}

	},

	showEditBox: function(e) {
		var editBox = $('edit-box')

		this.hideEditBox(e)

		var inject = ''
		Position.prepare()

		var top = Position.deltaY
		var left = Position.deltaX

		inject += '<div id="edit-box" style="width: 350px; height: 150px; top: '+top+'px; left: '+left+'px;">'
		inject += '<form onsubmit="return false;">'
		inject += '<textarea rows="6" id="edit-box-text">' + this.textValue() + '</textarea>'
		inject += '<input type="submit" value="Save" name="save" id="note-save-' + this.id + '" />'
		inject += '<input type="submit" value="Cancel" name="cancel" id="note-cancel-' + this.id + '" />'
		inject += '<input type="submit" value="Remove" name="remove" id="note-remove-' + this.id + '" />'
		inject += '<input type="submit" value="History" name="history" id="note-history-' + this.id + '" />'
		inject += '</form>'
		inject += '</div>'

		new Insertion.Bottom('note-container', inject)

		Event.observe('note-save-' + this.id, 'click', this.bind("save"), true)
		Event.observe('note-cancel-' + this.id, 'click', this.bind("cancel"), true)
		Event.observe('note-remove-' + this.id, 'click', this.bind("remove"), true)
		Event.observe('note-history-' + this.id, 'click', this.bind("history"), true)
	},

	bodyShow: function(e) {
		if (this.dragging)
			return

		if (this.hideTimer) {
			clearTimeout(this.hideTimer)
			this.hideTimer = null
		}

		// hide the other notes
		if (Note.all) {
			for (var i=0; i<Note.all.length; ++i) {
				if (Note.all[i].id != this.id) {
					Note.all[i].bodyHide()
				}
			}
		}

		this.elements.box.style.zIndex = ++Note.zindex
		this.elements.body.style.zIndex = Note.zindex
		this.elements.body.style.top = (this.elements.box.offsetTop + this.elements.box.clientHeight + 5) + "px"
		this.elements.body.style.left = this.elements.box.offsetLeft + "px"
		this.elements.body.style.display = "block"
	},

	bodyHideTimer: function(e) {
		this.hideTimer = setTimeout(this.bind("bodyHide"), 250)
	},

	bodyHide: function(e) {
		this.elements.body.style.display = "none"
	},

	dragStart: function(e) {
		Event.observe(document.documentElement, 'mousemove', this.bind("drag"), true)
		Event.observe(document.documentElement, 'mouseup', this.bind("dragStop"), true)
		document.onselectstart = function() {return false}

		this.cursorStartX = Event.pointerX(e)
		this.cursorStartY = Event.pointerY(e)
		this.boxStartX = this.elements.box.offsetLeft
		this.boxStartY = this.elements.box.offsetTop
		this.dragging = true

		this.bodyHide()
	},

	dragStop: function(e) {
		Event.stopObserving(document.documentElement, 'mousemove', this.bind("drag"), true)
		Event.stopObserving(document.documentElement, 'mouseup', this.bind("dragStop"), true)
		document.onselectstart = function() {return true}

		this.cursorStartX = null
		this.cursorStartY = null
		this.boxStartX = null
		this.boxStartY = null
		this.dragging = false

		this.bodyShow()
	},

	drag: function(e) {
		var left = this.boxStartX + Event.pointerX(e) - this.cursorStartX
		var top = this.boxStartY + Event.pointerY(e) - this.cursorStartY
		var bound

		bound = 5
		if (left < bound)
			left = bound

		bound = this.elements.image.clientWidth - this.elements.box.clientWidth - 5
		if (left > bound)
			left = bound

		bound = 5
		if (top < bound)
			top = bound

		bound = this.elements.image.clientHeight - this.elements.box.clientHeight - 5
		if (top > bound)
			top = bound

		this.elements.box.style.left = left + 'px'
		this.elements.box.style.top = top + 'px'

		Event.stop(e)
	},

	resizeStart: function(e) {
		this.cursorStartX = Event.pointerX(e)
		this.cursorStartY = Event.pointerY(e)
		this.boxStartWidth = this.elements.box.clientWidth
		this.boxStartHeight = this.elements.box.clientHeight
		this.boxStartX = this.elements.box.offsetLeft
		this.boxStartY = this.elements.box.offsetTop
		this.dragging = true

		Event.stopObserving(document.documentElement, 'mousemove', this.bind("drag"), true)
		Event.stopObserving(document.documentElement, 'mouseup', this.bind("dragStop"), true)
		Event.observe(document.documentElement, 'mousemove', this.bind("resize"), true)
		Event.observe(document.documentElement, 'mouseup', this.bind("resizeStop"), true)

		this.bodyHide()
	},

	resizeStop: function(e) {
		Event.stopObserving(document.documentElement, 'mousemove', this.bind("resize"), true)
		Event.stopObserving(document.documentElement, 'mouseup', this.bind("resizeStop"), true)

		this.boxCursorStartX = null
		this.boxCursorStartY = null
		this.boxStartWidth = null
		this.boxStartHeight = null
		this.boxStartX = null
		this.boxStartY = null
		this.dragging = false
	},

	resize: function(e) {
		var w = this.boxStartWidth + Event.pointerX(e) - this.cursorStartX
		var h = this.boxStartHeight + Event.pointerY(e) - this.cursorStartY
		var bound

		if (w < 30)
			w = 30

		bound = this.elements.image.clientWidth - this.boxStartX - 5
		if (w > bound)
			w = bound

		if (h < 30)
			h = 30

		bound = this.elements.image.clientHeight - this.boxStartY - 5
		if (h > bound)
			h = bound

		this.elements.box.style.width = w + "px"
		this.elements.box.style.height = h + "px"
		this.elements.box.style.left = this.boxStartX + "px"
		this.elements.box.style.top = this.boxStartY + "px"
	},

	save: function(e) {
		this.old.left = this.elements.box.offsetLeft
		this.old.top = this.elements.box.offsetTop
		this.old.width = this.elements.box.clientWidth
		this.old.height = this.elements.box.clientHeight
		this.old.body = $('edit-box-text').value
		this.elements.body.innerHTML = this.textValue()

		this.hideEditBox(e)
		this.bodyHide()

		var params = []
		params.push("note%5Bx%5D=" + this.old.left)
		params.push("note%5By%5D=" + this.old.top)
		params.push("note%5Bwidth%5D=" + this.old.width)
		params.push("note%5Bheight%5D=" + this.old.height)
		params.push("note%5Bbody%5D=" + encodeURIComponent(this.old.body))
		params.push("note%5Bpost_id%5D=" + Note.post_id)
		
		notice("Saving note...")
		new Ajax.Request('./public/note_save.php?id=' + this.id, {
			asynchronous: true,
			method: 'get',
			parameters: params.join("&"),
			onComplete: function(req) {
				if (req.status == 200) {
					notice("Note saved")
					//var response = eval(req.responseText)
					var response = req.responseText.split(":");
					if (response[1] < 0) {
						var n = Note.find(response[1])
						n.id = response[0]
						n.elements.box.id = 'note-box-' + n.id
						n.elements.body.id = 'note-body-' + n.id
						n.elements.corner.id = 'note-corner-' + n.id
						n.is_new = false;
					}
				} else {
					notice("Error: " + req.responseText)
					//alert(req.status);
				}
			}
		})

		Event.stop(e)
	},

	cancel: function(e) {
		this.hideEditBox(e)
		this.bodyHide()

		this.elements.box.style.top = this.old.top + "px"
		this.elements.box.style.left = this.old.left + "px"
		this.elements.box.style.width = this.old.width + "px"
		this.elements.box.style.height = this.old.height + "px"
		this.elements.body.innerHTML = this.old.body

		Event.stop(e)
	},

	removeCleanup: function() {
		Element.remove(this.elements.box)
		Element.remove(this.elements.body)

		var allTemp = []
		for (i=0; i<Note.all.length; ++i) {
			if (Note.all[i].id != this.id) {
				allTemp.push(Note.all[i])
			}
		}

		Note.all = allTemp
		Note.updateNoteCount()
	},

	remove: function(e) {
		this.hideEditBox(e)
		this.bodyHide()
		if (this.is_new) {
			this.removeCleanup()
			notice("Note removed")
		} else {
			notice("Removing note...")

			new Ajax.Request('./public/remove.php?id=' + Note.post_id + "&note_id=" + this.id, {
				asynchronous: true,
				method: 'get',
				onComplete: function(req) {
					if (req.status == 403) {
						notice("Access denied")
					} else if (req.status == 500) {
						notice("Error: " + req.responseText)
					} else {
						Note.find(parseInt(req.responseText)).removeCleanup()
						notice("Note removed")
					}
				}
			})
		}

		Event.stop(e)
	},

	history: function(e) {
		this.hideEditBox(e)

		if (this.is_new) {
			notice("This note has no history")
		} else {
			document.location='index.php?page=history&type=note&id=' + this.id + "&pid=" + Note.post_id;
		}
	}
}
