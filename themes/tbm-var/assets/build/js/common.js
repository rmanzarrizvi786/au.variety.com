// @ts-nocheck
!(function (t) {
  var e = {};
  function i(s) {
    if (e[s]) return e[s].exports;
    var n = (e[s] = { i: s, l: !1, exports: {} });
    return t[s].call(n.exports, n, n.exports, i), (n.l = !0), n.exports;
  }
  (i.m = t),
    (i.c = e),
    (i.d = function (t, e, s) {
      i.o(t, e) || Object.defineProperty(t, e, { enumerable: !0, get: s });
    }),
    (i.r = function (t) {
      "undefined" != typeof Symbol &&
        Symbol.toStringTag &&
        Object.defineProperty(t, Symbol.toStringTag, { value: "Module" }),
        Object.defineProperty(t, "__esModule", { value: !0 });
    }),
    (i.t = function (t, e) {
      if ((1 & e && (t = i(t)), 8 & e)) return t;
      if (4 & e && "object" == typeof t && t && t.__esModule) return t;
      var s = Object.create(null);
      if (
        (i.r(s),
        Object.defineProperty(s, "default", { enumerable: !0, value: t }),
        2 & e && "string" != typeof t)
      )
        for (var n in t)
          i.d(
            s,
            n,
            function (e) {
              return t[e];
            }.bind(null, n)
          );
      return s;
    }),
    (i.n = function (t) {
      var e =
        t && t.__esModule
          ? function () {
              return t.default;
            }
          : function () {
              return t;
            };
      return i.d(e, "a", e), e;
    }),
    (i.o = function (t, e) {
      return Object.prototype.hasOwnProperty.call(t, e);
    }),
    (i.p = ""),
    i((i.s = 30));
})([
  function (t, e, i) {
    var s, n;
    !(function (o, r) {
      (s = [i(14)]),
        void 0 ===
          (n = function (t) {
            return (function (t, e) {
              "use strict";
              var i = {
                  extend: function (t, e) {
                    for (var i in e) t[i] = e[i];
                    return t;
                  },
                  modulo: function (t, e) {
                    return ((t % e) + e) % e;
                  },
                },
                s = Array.prototype.slice;
              (i.makeArray = function (t) {
                return Array.isArray(t)
                  ? t
                  : null == t
                  ? []
                  : "object" == typeof t && "number" == typeof t.length
                  ? s.call(t)
                  : [t];
              }),
                (i.removeFrom = function (t, e) {
                  var i = t.indexOf(e);
                  -1 != i && t.splice(i, 1);
                }),
                (i.getParent = function (t, i) {
                  for (; t.parentNode && t != document.body; )
                    if (((t = t.parentNode), e(t, i))) return t;
                }),
                (i.getQueryElement = function (t) {
                  return "string" == typeof t ? document.querySelector(t) : t;
                }),
                (i.handleEvent = function (t) {
                  var e = "on" + t.type;
                  this[e] && this[e](t);
                }),
                (i.filterFindElements = function (t, s) {
                  t = i.makeArray(t);
                  var n = [];
                  return (
                    t.forEach(function (t) {
                      if (t instanceof HTMLElement)
                        if (s) {
                          e(t, s) && n.push(t);
                          for (
                            var i = t.querySelectorAll(s), o = 0;
                            o < i.length;
                            o++
                          )
                            n.push(i[o]);
                        } else n.push(t);
                    }),
                    n
                  );
                }),
                (i.debounceMethod = function (t, e, i) {
                  i = i || 100;
                  var s = t.prototype[e],
                    n = e + "Timeout";
                  t.prototype[e] = function () {
                    var t = this[n];
                    clearTimeout(t);
                    var e = arguments,
                      o = this;
                    this[n] = setTimeout(function () {
                      s.apply(o, e), delete o[n];
                    }, i);
                  };
                }),
                (i.docReady = function (t) {
                  var e = document.readyState;
                  "complete" == e || "interactive" == e
                    ? setTimeout(t)
                    : document.addEventListener("DOMContentLoaded", t);
                }),
                (i.toDashed = function (t) {
                  return t
                    .replace(/(.)([A-Z])/g, function (t, e, i) {
                      return e + "-" + i;
                    })
                    .toLowerCase();
                });
              var n = t.console;
              return (
                (i.htmlInit = function (e, s) {
                  i.docReady(function () {
                    var o = i.toDashed(s),
                      r = "data-" + o,
                      a = document.querySelectorAll("[" + r + "]"),
                      l = document.querySelectorAll(".js-" + o),
                      h = i.makeArray(a).concat(i.makeArray(l)),
                      c = r + "-options",
                      d = t.jQuery;
                    h.forEach(function (t) {
                      var i,
                        o = t.getAttribute(r) || t.getAttribute(c);
                      try {
                        i = o && JSON.parse(o);
                      } catch (e) {
                        return void (
                          n &&
                          n.error(
                            "Error parsing " +
                              r +
                              " on " +
                              t.className +
                              ": " +
                              e
                          )
                        );
                      }
                      var a = new e(t, i);
                      d && d.data(t, s, a);
                    });
                  });
                }),
                i
              );
            })(o, t);
          }.apply(e, s)) || (t.exports = n);
    })(window);
  },
  function (t, e, i) {
    var s, n;
    !(function (o, r) {
      (s = [i(5), i(7), i(0), i(15), i(16), i(17)]),
        void 0 ===
          (n = function (t, e, i, s, n, r) {
            return (function (t, e, i, s, n, o, r) {
              "use strict";
              var a = t.jQuery,
                l = t.getComputedStyle,
                h = t.console;
              function c(t, e) {
                for (t = s.makeArray(t); t.length; ) e.appendChild(t.shift());
              }
              var d = 0,
                u = {};
              function p(t, e) {
                var i = s.getQueryElement(t);
                if (i) {
                  if (((this.element = i), this.element.flickityGUID)) {
                    var n = u[this.element.flickityGUID];
                    return n && n.option(e), n;
                  }
                  a && (this.$element = a(this.element)),
                    (this.options = s.extend({}, this.constructor.defaults)),
                    this.option(e),
                    this._create();
                } else h && h.error("Bad element for Flickity: " + (i || t));
              }
              (p.defaults = {
                accessibility: !0,
                cellAlign: "center",
                freeScrollFriction: 0.075,
                friction: 0.28,
                namespaceJQueryEvents: !0,
                percentPosition: !0,
                resize: !0,
                selectedAttraction: 0.025,
                setGallerySize: !0,
              }),
                (p.createMethods = []);
              var g = p.prototype;
              s.extend(g, e.prototype),
                (g._create = function () {
                  var e = (this.guid = ++d);
                  for (var i in ((this.element.flickityGUID = e),
                  (u[e] = this),
                  (this.selectedIndex = 0),
                  (this.restingFrames = 0),
                  (this.x = 0),
                  (this.velocity = 0),
                  (this.originSide = this.options.rightToLeft
                    ? "right"
                    : "left"),
                  (this.viewport = document.createElement("div")),
                  (this.viewport.className = "flickity-viewport"),
                  this._createSlider(),
                  (this.options.resize || this.options.watchCSS) &&
                    t.addEventListener("resize", this),
                  this.options.on)) {
                    var s = this.options.on[i];
                    this.on(i, s);
                  }
                  p.createMethods.forEach(function (t) {
                    this[t]();
                  }, this),
                    this.options.watchCSS ? this.watchCSS() : this.activate();
                }),
                (g.option = function (t) {
                  s.extend(this.options, t);
                }),
                (g.activate = function () {
                  this.isActive ||
                    ((this.isActive = !0),
                    this.element.classList.add("flickity-enabled"),
                    this.options.rightToLeft &&
                      this.element.classList.add("flickity-rtl"),
                    this.getSize(),
                    c(
                      this._filterFindCellElements(this.element.children),
                      this.slider
                    ),
                    this.viewport.appendChild(this.slider),
                    this.element.appendChild(this.viewport),
                    this.reloadCells(),
                    this.options.accessibility &&
                      ((this.element.tabIndex = 0),
                      this.element.addEventListener("keydown", this)),
                    this.emitEvent("activate"),
                    this.selectInitialIndex(),
                    (this.isInitActivated = !0),
                    this.dispatchEvent("ready"));
                }),
                (g._createSlider = function () {
                  var t = document.createElement("div");
                  (t.className = "flickity-slider"),
                    (t.style[this.originSide] = 0),
                    (this.slider = t);
                }),
                (g._filterFindCellElements = function (t) {
                  return s.filterFindElements(t, this.options.cellSelector);
                }),
                (g.reloadCells = function () {
                  (this.cells = this._makeCells(this.slider.children)),
                    this.positionCells(),
                    this._getWrapShiftCells(),
                    this.setGallerySize();
                }),
                (g._makeCells = function (t) {
                  return this._filterFindCellElements(t).map(function (t) {
                    return new n(t, this);
                  }, this);
                }),
                (g.getLastCell = function () {
                  return this.cells[this.cells.length - 1];
                }),
                (g.getLastSlide = function () {
                  return this.slides[this.slides.length - 1];
                }),
                (g.positionCells = function () {
                  this._sizeCells(this.cells), this._positionCells(0);
                }),
                (g._positionCells = function (t) {
                  (t = t || 0),
                    (this.maxCellHeight = (t && this.maxCellHeight) || 0);
                  var e = 0;
                  if (t > 0) {
                    var i = this.cells[t - 1];
                    e = i.x + i.size.outerWidth;
                  }
                  for (var s = this.cells.length, n = t; n < s; n++) {
                    var o = this.cells[n];
                    o.setPosition(e),
                      (e += o.size.outerWidth),
                      (this.maxCellHeight = Math.max(
                        o.size.outerHeight,
                        this.maxCellHeight
                      ));
                  }
                  (this.slideableWidth = e),
                    this.updateSlides(),
                    this._containSlides(),
                    (this.slidesWidth = s
                      ? this.getLastSlide().target - this.slides[0].target
                      : 0);
                }),
                (g._sizeCells = function (t) {
                  t.forEach(function (t) {
                    t.getSize();
                  });
                }),
                (g.updateSlides = function () {
                  if (((this.slides = []), this.cells.length)) {
                    var t = new o(this);
                    this.slides.push(t);
                    var e =
                        "left" == this.originSide
                          ? "marginRight"
                          : "marginLeft",
                      i = this._getCanCellFit();
                    this.cells.forEach(function (s, n) {
                      if (t.cells.length) {
                        var r =
                          t.outerWidth -
                          t.firstMargin +
                          (s.size.outerWidth - s.size[e]);
                        i.call(this, n, r) ||
                          (t.updateTarget(),
                          (t = new o(this)),
                          this.slides.push(t)),
                          t.addCell(s);
                      } else t.addCell(s);
                    }, this),
                      t.updateTarget(),
                      this.updateSelectedSlide();
                  }
                }),
                (g._getCanCellFit = function () {
                  var t = this.options.groupCells;
                  if (!t)
                    return function () {
                      return !1;
                    };
                  if ("number" == typeof t) {
                    var e = parseInt(t, 10);
                    return function (t) {
                      return t % e != 0;
                    };
                  }
                  var i = "string" == typeof t && t.match(/^(\d+)%$/),
                    s = i ? parseInt(i[1], 10) / 100 : 1;
                  return function (t, e) {
                    return e <= (this.size.innerWidth + 1) * s;
                  };
                }),
                (g._init = g.reposition =
                  function () {
                    this.positionCells(), this.positionSliderAtSelected();
                  }),
                (g.getSize = function () {
                  (this.size = i(this.element)),
                    this.setCellAlign(),
                    (this.cursorPosition =
                      this.size.innerWidth * this.cellAlign);
                });
              var f = {
                center: { left: 0.5, right: 0.5 },
                left: { left: 0, right: 1 },
                right: { right: 0, left: 1 },
              };
              (g.setCellAlign = function () {
                var t = f[this.options.cellAlign];
                this.cellAlign = t
                  ? t[this.originSide]
                  : this.options.cellAlign;
              }),
                (g.setGallerySize = function () {
                  if (this.options.setGallerySize) {
                    var t =
                      this.options.adaptiveHeight && this.selectedSlide
                        ? this.selectedSlide.height
                        : this.maxCellHeight;
                    this.viewport.style.height = t + "px";
                  }
                }),
                (g._getWrapShiftCells = function () {
                  if (this.options.wrapAround) {
                    this._unshiftCells(this.beforeShiftCells),
                      this._unshiftCells(this.afterShiftCells);
                    var t = this.cursorPosition,
                      e = this.cells.length - 1;
                    (this.beforeShiftCells = this._getGapCells(t, e, -1)),
                      (t = this.size.innerWidth - this.cursorPosition),
                      (this.afterShiftCells = this._getGapCells(t, 0, 1));
                  }
                }),
                (g._getGapCells = function (t, e, i) {
                  for (var s = []; t > 0; ) {
                    var n = this.cells[e];
                    if (!n) break;
                    s.push(n), (e += i), (t -= n.size.outerWidth);
                  }
                  return s;
                }),
                (g._containSlides = function () {
                  if (
                    this.options.contain &&
                    !this.options.wrapAround &&
                    this.cells.length
                  ) {
                    var t = this.options.rightToLeft,
                      e = t ? "marginRight" : "marginLeft",
                      i = t ? "marginLeft" : "marginRight",
                      s = this.slideableWidth - this.getLastCell().size[i],
                      n = s < this.size.innerWidth,
                      o = this.cursorPosition + this.cells[0].size[e],
                      r = s - this.size.innerWidth * (1 - this.cellAlign);
                    this.slides.forEach(function (t) {
                      n
                        ? (t.target = s * this.cellAlign)
                        : ((t.target = Math.max(t.target, o)),
                          (t.target = Math.min(t.target, r)));
                    }, this);
                  }
                }),
                (g.dispatchEvent = function (t, e, i) {
                  var s = e ? [e].concat(i) : i;
                  if ((this.emitEvent(t, s), a && this.$element)) {
                    var n = (t += this.options.namespaceJQueryEvents
                      ? ".flickity"
                      : "");
                    if (e) {
                      var o = new a.Event(e);
                      (o.type = t), (n = o);
                    }
                    this.$element.trigger(n, i);
                  }
                }),
                (g.select = function (t, e, i) {
                  if (
                    this.isActive &&
                    ((t = parseInt(t, 10)),
                    this._wrapSelect(t),
                    (this.options.wrapAround || e) &&
                      (t = s.modulo(t, this.slides.length)),
                    this.slides[t])
                  ) {
                    var n = this.selectedIndex;
                    (this.selectedIndex = t),
                      this.updateSelectedSlide(),
                      i
                        ? this.positionSliderAtSelected()
                        : this.startAnimation(),
                      this.options.adaptiveHeight && this.setGallerySize(),
                      this.dispatchEvent("select", null, [t]),
                      t != n && this.dispatchEvent("change", null, [t]),
                      this.dispatchEvent("cellSelect");
                  }
                }),
                (g._wrapSelect = function (t) {
                  var e = this.slides.length;
                  if (!(this.options.wrapAround && e > 1)) return t;
                  var i = s.modulo(t, e),
                    n = Math.abs(i - this.selectedIndex),
                    o = Math.abs(i + e - this.selectedIndex),
                    r = Math.abs(i - e - this.selectedIndex);
                  !this.isDragSelect && o < n
                    ? (t += e)
                    : !this.isDragSelect && r < n && (t -= e),
                    t < 0
                      ? (this.x -= this.slideableWidth)
                      : t >= e && (this.x += this.slideableWidth);
                }),
                (g.previous = function (t, e) {
                  this.select(this.selectedIndex - 1, t, e);
                }),
                (g.next = function (t, e) {
                  this.select(this.selectedIndex + 1, t, e);
                }),
                (g.updateSelectedSlide = function () {
                  var t = this.slides[this.selectedIndex];
                  t &&
                    (this.unselectSelectedSlide(),
                    (this.selectedSlide = t),
                    t.select(),
                    (this.selectedCells = t.cells),
                    (this.selectedElements = t.getCellElements()),
                    (this.selectedCell = t.cells[0]),
                    (this.selectedElement = this.selectedElements[0]));
                }),
                (g.unselectSelectedSlide = function () {
                  this.selectedSlide && this.selectedSlide.unselect();
                }),
                (g.selectInitialIndex = function () {
                  var t = this.options.initialIndex;
                  if (this.isInitActivated)
                    this.select(this.selectedIndex, !1, !0);
                  else {
                    if (t && "string" == typeof t)
                      if (this.queryCell(t))
                        return void this.selectCell(t, !1, !0);
                    var e = 0;
                    t && this.slides[t] && (e = t), this.select(e, !1, !0);
                  }
                }),
                (g.selectCell = function (t, e, i) {
                  var s = this.queryCell(t);
                  if (s) {
                    var n = this.getCellSlideIndex(s);
                    this.select(n, e, i);
                  }
                }),
                (g.getCellSlideIndex = function (t) {
                  for (var e = 0; e < this.slides.length; e++) {
                    if (-1 != this.slides[e].cells.indexOf(t)) return e;
                  }
                }),
                (g.getCell = function (t) {
                  for (var e = 0; e < this.cells.length; e++) {
                    var i = this.cells[e];
                    if (i.element == t) return i;
                  }
                }),
                (g.getCells = function (t) {
                  t = s.makeArray(t);
                  var e = [];
                  return (
                    t.forEach(function (t) {
                      var i = this.getCell(t);
                      i && e.push(i);
                    }, this),
                    e
                  );
                }),
                (g.getCellElements = function () {
                  return this.cells.map(function (t) {
                    return t.element;
                  });
                }),
                (g.getParentCell = function (t) {
                  var e = this.getCell(t);
                  return (
                    e ||
                    ((t = s.getParent(t, ".flickity-slider > *")),
                    this.getCell(t))
                  );
                }),
                (g.getAdjacentCellElements = function (t, e) {
                  if (!t) return this.selectedSlide.getCellElements();
                  e = void 0 === e ? this.selectedIndex : e;
                  var i = this.slides.length;
                  if (1 + 2 * t >= i) return this.getCellElements();
                  for (var n = [], o = e - t; o <= e + t; o++) {
                    var r = this.options.wrapAround ? s.modulo(o, i) : o,
                      a = this.slides[r];
                    a && (n = n.concat(a.getCellElements()));
                  }
                  return n;
                }),
                (g.queryCell = function (t) {
                  if ("number" == typeof t) return this.cells[t];
                  if ("string" == typeof t) {
                    if (t.match(/^[#.]?[\d/]/)) return;
                    t = this.element.querySelector(t);
                  }
                  return this.getCell(t);
                }),
                (g.uiChange = function () {
                  this.emitEvent("uiChange");
                }),
                (g.childUIPointerDown = function (t) {
                  "touchstart" != t.type && t.preventDefault(), this.focus();
                }),
                (g.onresize = function () {
                  this.watchCSS(), this.resize();
                }),
                s.debounceMethod(p, "onresize", 150),
                (g.resize = function () {
                  if (this.isActive) {
                    this.getSize(),
                      this.options.wrapAround &&
                        (this.x = s.modulo(this.x, this.slideableWidth)),
                      this.positionCells(),
                      this._getWrapShiftCells(),
                      this.setGallerySize(),
                      this.emitEvent("resize");
                    var t = this.selectedElements && this.selectedElements[0];
                    this.selectCell(t, !1, !0);
                  }
                }),
                (g.watchCSS = function () {
                  this.options.watchCSS &&
                    (-1 != l(this.element, ":after").content.indexOf("flickity")
                      ? this.activate()
                      : this.deactivate());
                }),
                (g.onkeydown = function (t) {
                  var e =
                    document.activeElement &&
                    document.activeElement != this.element;
                  if (this.options.accessibility && !e) {
                    var i = p.keyboardHandlers[t.keyCode];
                    i && i.call(this);
                  }
                }),
                (p.keyboardHandlers = {
                  37: function () {
                    var t = this.options.rightToLeft ? "next" : "previous";
                    this.uiChange(), this[t]();
                  },
                  39: function () {
                    var t = this.options.rightToLeft ? "previous" : "next";
                    this.uiChange(), this[t]();
                  },
                }),
                (g.focus = function () {
                  var e = t.pageYOffset;
                  this.element.focus({ preventScroll: !0 }),
                    t.pageYOffset != e && t.scrollTo(t.pageXOffset, e);
                }),
                (g.deactivate = function () {
                  this.isActive &&
                    (this.element.classList.remove("flickity-enabled"),
                    this.element.classList.remove("flickity-rtl"),
                    this.unselectSelectedSlide(),
                    this.cells.forEach(function (t) {
                      t.destroy();
                    }),
                    this.element.removeChild(this.viewport),
                    c(this.slider.children, this.element),
                    this.options.accessibility &&
                      (this.element.removeAttribute("tabIndex"),
                      this.element.removeEventListener("keydown", this)),
                    (this.isActive = !1),
                    this.emitEvent("deactivate"));
                }),
                (g.destroy = function () {
                  this.deactivate(),
                    t.removeEventListener("resize", this),
                    this.allOff(),
                    this.emitEvent("destroy"),
                    a &&
                      this.$element &&
                      a.removeData(this.element, "flickity"),
                    delete this.element.flickityGUID,
                    delete u[this.guid];
                }),
                s.extend(g, r),
                (p.data = function (t) {
                  var e = (t = s.getQueryElement(t)) && t.flickityGUID;
                  return e && u[e];
                }),
                s.htmlInit(p, "flickity"),
                a && a.bridget && a.bridget("flickity", p);
              return (
                (p.setJQuery = function (t) {
                  a = t;
                }),
                (p.Cell = n),
                (p.Slide = o),
                p
              );
            })(o, t, e, i, s, n, r);
          }.apply(e, s)) || (t.exports = n);
    })(window);
  },
  function (t, e, i) {
    "use strict";
    i.d(e, "a", function () {
      return s;
    });
    class s {
      constructor(t) {
        (this.el = t),
          (this.toggleEl = this.el.querySelectorAll(
            "[data-collapsible-toggle]"
          )),
          (this.panels = [
            ...this.el.querySelectorAll("[data-collapsible-panel]"),
          ]),
          (this.group = this.findGroup()),
          (this.toggle = this.toggle.bind(this)),
          (this.onClick = this.onClick.bind(this)),
          this.toggleEl.forEach((t) =>
            t.addEventListener("click", this.onClick)
          );
      }
      destroy() {
        this.toggleEl.forEach((t) =>
          t.removeEventListener("click", this.onClick)
        ),
          this.isCollapsed || this.toggle();
      }
      get state() {
        return this.el.dataset.collapsible;
      }
      set state(t) {
        (this.el.dataset.collapsible = t),
          this.isCollapsed
            ? this.el.classList.remove("is-expanded")
            : this.el.classList.add("is-expanded");
      }
      get isCollapsed() {
        return "collapsed" === this.state;
      }
      get container() {
        return document.querySelector(this.el.dataset.collapsibleContainer);
      }
      get closeOnClick() {
        return void 0 !== this.el.dataset.collapsibleCloseOnClick;
      }
      findGroup() {
        return (
          [...document.body.querySelectorAll("[data-collapsible-group]")].find(
            (t) => t.contains(this.el)
          ) || null
        );
      }
      onClick(t) {
        t.preventDefault(), this.toggle();
      }
      toggle() {
        (this.state = this.isCollapsed ? "expanded" : "collapsed"),
          this.maybeRepositionPanel(),
          this.maybeCloseOnClick(),
          "expanded" === this.state &&
            null !== this.group &&
            this.closeOthersInGroup();
      }
      collapse() {
        "expanded" === this.state && this.toggle();
      }
      closeOthersInGroup() {
        [...this.group.querySelectorAll("[data-collapsible]")].forEach((t) => {
          t !== this.el && t.pmcCollapsible.collapse();
        });
      }
      maybeRepositionPanel() {
        if (this.container)
          if (this.isCollapsed)
            this.panels.forEach((t) => (t.style.marginLeft = ""));
          else {
            const t = this.container.getBoundingClientRect().left;
            this.panels.forEach((e) => {
              const i = e.getBoundingClientRect();
              if (0 === i.width && 0 === i.height) return;
              const s = parseInt(window.getComputedStyle(e).marginLeft, 10),
                n = i.left - 2 * s;
              n < t && (e.style.marginLeft = t - n + "px");
            });
          }
      }
      maybeCloseOnClick() {
        this.closeOnClick &&
          (this.isCollapsed
            ? document.body.removeEventListener("click", this.toggle)
            : setTimeout(
                () => document.body.addEventListener("click", this.toggle),
                1
              ));
      }
    }
  },
  function (t, e, i) {
    "use strict";
    i.d(e, "a", function () {
      return o;
    });
    var s = i(2);
    class n {
      constructor(t) {
        var e, i, s, n;
        (this.el = t),
          (this.triggers = [
            ...t.querySelectorAll("[data-video-showcase-trigger]"),
          ]),
          (this.player = t.querySelector("[data-video-showcase-player]")),
          (this.elementsToHide = [
            ...this.el.querySelectorAll(".is-to-be-hidden"),
          ]),
          (this.attributesToRemoveFromPlayer = [
            "data-video-showcase-trigger",
            "data-video-showcase-title",
            "data-video-showcase-dek",
            "data-video-showcase-permalink",
            "data-video-showcase-type",
            "href",
          ]),
          (this.state = {
            isPlayerSetup: !1,
            hasSocialShare: !1,
            videoID: "",
            videoType: "",
          }),
          (this.playerUI = {
            heading: t.querySelector(
              "[data-video-showcase-player-heading], .js-VideoShowcasePlayerHeading"
            ),
            sponsoredBadge: t.querySelector(
              ".js-video-showcase-sponsored-badge"
            ),
            dek: t.querySelector(
              "[data-video-showcase-player-dek], .js-VideoShowcasePlayerDek"
            ),
            iframe: t.querySelector(
              "[data-video-showcase-iframe], .js-VideoShowcasePlayerIframe"
            ),
            jwplayerContainer: t.querySelector("#jwplayerContainer"),
            social: t.querySelector(
              "[data-video-showcase-player-social-share], .js-VideoShowcasePlayerSocialShare"
            ),
            oembedContainer: t.querySelector(
              "[data-video-showcase-oembed], .js-VideoShowcasePlayerOembed"
            ),
            time: t.querySelector(".js-VideoShowcasePlayerTime"),
          }),
          this.init(),
          this.player.dataset.videoShowcaseAutoplay
            ? this.handleTriggerClick(null, this.triggers[0])
            : ((e = this.el),
              (i = "click"),
              (s = "[data-video-showcase-trigger]"),
              (n = this.handleTriggerClick.bind(this)),
              e.addEventListener(i, (t) => {
                const e = ((t, e) => e.matches && e.matches(t))(s, t.target)
                  ? t.target
                  : t.target.closest(s);
                e && n(t, e);
              }));
      }
      init() {
        null !== this.playerUI.social && (this.state.hasSocialShare = !0);
      }
      getPlayerCardData(t) {
        const e = t.dataset.videoShowcaseTrigger,
          i = this.state.hasSocialShare;
        return {
          title: t.dataset.videoShowcaseTitle,
          sponsored: t.dataset.videoShowcaseSponsored,
          dek: t.dataset.videoShowcaseDek,
          permalink: t.dataset.videoShowcasePermalink,
          time: t.dataset.videoShowcaseTime,
          socialString: (function (t) {
            if (window.wp && i) {
              return wp.template("trigger-social-share-" + e)(void 0);
            }
          })(),
        };
      }
      updatePlayerCardData(t, e) {
        this.playerUI.heading &&
          e.title &&
          (this.playerUI.heading.innerText = e.title),
          this.playerUI.heading &&
            e.permalink &&
            this.playerUI.heading.setAttribute("href", e.permalink),
          this.playerUI.dek && e.dek && (this.playerUI.dek.innerText = e.dek),
          this.playerUI.time &&
            e.time &&
            (this.playerUI.time.innerText = e.time),
          e.socialString &&
            this.state.hasSocialShare &&
            this.updateCardSocialShare(e.socialString),
          this.playerUI.sponsoredBadge &&
            (e.sponsored
              ? this.playerUI.sponsoredBadge.classList.remove("u-hidden")
              : this.playerUI.sponsoredBadge.classList.add("u-hidden"));
      }
      updateCardSocialShare(t) {
        this.playerUI.social.removeChild(
          this.playerUI.social.querySelector("ul")
        ),
          this.playerUI.social.insertAdjacentHTML("beforeend", t),
          this.initCollapsible(
            this.playerUI.social.querySelector("[data-collapsible]")
          );
      }
      initCollapsible(t) {
        t.pmcCollapsible = new s.a(t);
      }
      returnUrl(t, e) {
        return "youtube" === e
          ? "https://www.youtube.com/embed/" + t
          : "jwplayer" === e
          ? `https://content.jwplatform.com/feeds/${t}.json`
          : "oembed" === e
          ? t
          : void 0;
      }
      playYoutube(t) {
        this.playerUI.iframe.removeAttribute("hidden"),
          this.playerUI.iframe.setAttribute(
            "src",
            t + "?rel=0&autoplay=1&showinfo=0&controls=2&rel=0&modestbranding=0"
          ),
          this.playerUI.iframe.setAttribute(
            "allow",
            "accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture"
          );
      }
      playJW(t) {
        let e;
        this.playerUI.jwplayerContainer.removeAttribute("hidden"),
          window.pmc_jwplayer
            ? (e = window.pmc_jwplayer(
                this.playerUI.jwplayerContainer.id,
                "default"
              ))
            : window.jwplayer &&
              (e = window.jwplayer(this.playerUI.jwplayerContainer.id)),
          e && (e.setup({ playlist: t, aspectratio: "16:9" }), e.play());
      }
      playEmbed(t) {
        this.playerUI.oembedContainer.removeAttribute("hidden"),
          (this.playerUI.oembedContainer.innerHTML = ""),
          this.playerUI.oembedContainer.insertAdjacentHTML("beforeend", t);
      }
      handleTriggerClick(t, e) {
        t && t.preventDefault();
        const i = this.state.videoType;
        (this.state.videoType = e.dataset.videoShowcaseType),
          (this.state.videoID = e.dataset.videoShowcaseTrigger),
          this.resetPlayer(i),
          this.playVideo(this.state.videoID, this.state.videoType),
          this.updatePlayerUI(this.state.videoID),
          this.onFirstTimePlay();
      }
      playVideo(t, e) {
        const i = this.returnUrl(t, e);
        "youtube" === e && this.playYoutube(i),
          "jwplayer" === e && this.playJW(i),
          "oembed" === e && this.playEmbed(i);
      }
      onFirstTimePlay() {
        !1 === this.state.isPlayerSetup &&
          (this.elementsToHide.forEach((t) => t.setAttribute("hidden", "")),
          this.attributesToRemoveFromPlayer.forEach((t) =>
            this.player.parentNode.removeAttribute(t)
          ),
          (this.state.isPlayerSetup = !0));
      }
      updatePlayerUI(t) {
        const e = this.el.querySelector(`[data-video-showcase-trigger="${t}"]`),
          i = this.getPlayerCardData(e);
        this.setActiveTrigger(t), this.updatePlayerCardData(e, i);
      }
      resetPlayer(t) {
        "jwplayer" === t &&
          window.jwplayer &&
          (window.jwplayer("jwplayerContainer").remove(),
          this.playerUI.jwplayerContainer.setAttribute("hidden", "")),
          "youtube" === t &&
            (this.playerUI.iframe.setAttribute("src", ""),
            this.playerUI.iframe.setAttribute("hidden", ""));
      }
      resetAllTriggers() {
        this.triggers.forEach((t) => t.classList.remove("is-playing"));
      }
      setActiveTrigger(t) {
        const e = this.el.querySelector(
          `.related-videos [data-video-showcase-trigger="${t}"]`
        );
        this.resetAllTriggers(), null !== e && e.classList.add("is-playing");
      }
    }
    function o() {
      const t = [...document.querySelectorAll("[data-video-showcase]")];
      t.length && t.forEach((t) => (t.pmcVideoShowcase = new n(t)));
    }
  },
  ,
  function (t, e, i) {
    var s, n;
    "undefined" != typeof window && window,
      void 0 ===
        (n =
          "function" ==
          typeof (s = function () {
            "use strict";
            function t() {}
            var e = t.prototype;
            return (
              (e.on = function (t, e) {
                if (t && e) {
                  var i = (this._events = this._events || {}),
                    s = (i[t] = i[t] || []);
                  return -1 == s.indexOf(e) && s.push(e), this;
                }
              }),
              (e.once = function (t, e) {
                if (t && e) {
                  this.on(t, e);
                  var i = (this._onceEvents = this._onceEvents || {});
                  return ((i[t] = i[t] || {})[e] = !0), this;
                }
              }),
              (e.off = function (t, e) {
                var i = this._events && this._events[t];
                if (i && i.length) {
                  var s = i.indexOf(e);
                  return -1 != s && i.splice(s, 1), this;
                }
              }),
              (e.emitEvent = function (t, e) {
                var i = this._events && this._events[t];
                if (i && i.length) {
                  (i = i.slice(0)), (e = e || []);
                  for (
                    var s = this._onceEvents && this._onceEvents[t], n = 0;
                    n < i.length;
                    n++
                  ) {
                    var o = i[n];
                    s && s[o] && (this.off(t, o), delete s[o]),
                      o.apply(this, e);
                  }
                  return this;
                }
              }),
              (e.allOff = function () {
                delete this._events, delete this._onceEvents;
              }),
              t
            );
          })
            ? s.call(e, i, e, t)
            : s) || (t.exports = n);
  },
  function (t, e, i) {
    var s, n;
    /*!
     * Unipointer v2.3.0
     * base class for doing one thing with pointer event
     * MIT license
     */ !(function (o, r) {
      (s = [i(5)]),
        void 0 ===
          (n = function (t) {
            return (function (t, e) {
              "use strict";
              function i() {}
              var s = (i.prototype = Object.create(e.prototype));
              (s.bindStartEvent = function (t) {
                this._bindStartEvent(t, !0);
              }),
                (s.unbindStartEvent = function (t) {
                  this._bindStartEvent(t, !1);
                }),
                (s._bindStartEvent = function (e, i) {
                  var s = (i = void 0 === i || i)
                      ? "addEventListener"
                      : "removeEventListener",
                    n = "mousedown";
                  t.PointerEvent
                    ? (n = "pointerdown")
                    : "ontouchstart" in t && (n = "touchstart"),
                    e[s](n, this);
                }),
                (s.handleEvent = function (t) {
                  var e = "on" + t.type;
                  this[e] && this[e](t);
                }),
                (s.getTouch = function (t) {
                  for (var e = 0; e < t.length; e++) {
                    var i = t[e];
                    if (i.identifier == this.pointerIdentifier) return i;
                  }
                }),
                (s.onmousedown = function (t) {
                  var e = t.button;
                  (e && 0 !== e && 1 !== e) || this._pointerDown(t, t);
                }),
                (s.ontouchstart = function (t) {
                  this._pointerDown(t, t.changedTouches[0]);
                }),
                (s.onpointerdown = function (t) {
                  this._pointerDown(t, t);
                }),
                (s._pointerDown = function (t, e) {
                  t.button ||
                    this.isPointerDown ||
                    ((this.isPointerDown = !0),
                    (this.pointerIdentifier =
                      void 0 !== e.pointerId ? e.pointerId : e.identifier),
                    this.pointerDown(t, e));
                }),
                (s.pointerDown = function (t, e) {
                  this._bindPostStartEvents(t),
                    this.emitEvent("pointerDown", [t, e]);
                });
              var n = {
                mousedown: ["mousemove", "mouseup"],
                touchstart: ["touchmove", "touchend", "touchcancel"],
                pointerdown: ["pointermove", "pointerup", "pointercancel"],
              };
              return (
                (s._bindPostStartEvents = function (e) {
                  if (e) {
                    var i = n[e.type];
                    i.forEach(function (e) {
                      t.addEventListener(e, this);
                    }, this),
                      (this._boundPointerEvents = i);
                  }
                }),
                (s._unbindPostStartEvents = function () {
                  this._boundPointerEvents &&
                    (this._boundPointerEvents.forEach(function (e) {
                      t.removeEventListener(e, this);
                    }, this),
                    delete this._boundPointerEvents);
                }),
                (s.onmousemove = function (t) {
                  this._pointerMove(t, t);
                }),
                (s.onpointermove = function (t) {
                  t.pointerId == this.pointerIdentifier &&
                    this._pointerMove(t, t);
                }),
                (s.ontouchmove = function (t) {
                  var e = this.getTouch(t.changedTouches);
                  e && this._pointerMove(t, e);
                }),
                (s._pointerMove = function (t, e) {
                  this.pointerMove(t, e);
                }),
                (s.pointerMove = function (t, e) {
                  this.emitEvent("pointerMove", [t, e]);
                }),
                (s.onmouseup = function (t) {
                  this._pointerUp(t, t);
                }),
                (s.onpointerup = function (t) {
                  t.pointerId == this.pointerIdentifier &&
                    this._pointerUp(t, t);
                }),
                (s.ontouchend = function (t) {
                  var e = this.getTouch(t.changedTouches);
                  e && this._pointerUp(t, e);
                }),
                (s._pointerUp = function (t, e) {
                  this._pointerDone(), this.pointerUp(t, e);
                }),
                (s.pointerUp = function (t, e) {
                  this.emitEvent("pointerUp", [t, e]);
                }),
                (s._pointerDone = function () {
                  this._pointerReset(),
                    this._unbindPostStartEvents(),
                    this.pointerDone();
                }),
                (s._pointerReset = function () {
                  (this.isPointerDown = !1), delete this.pointerIdentifier;
                }),
                (s.pointerDone = function () {}),
                (s.onpointercancel = function (t) {
                  t.pointerId == this.pointerIdentifier &&
                    this._pointerCancel(t, t);
                }),
                (s.ontouchcancel = function (t) {
                  var e = this.getTouch(t.changedTouches);
                  e && this._pointerCancel(t, e);
                }),
                (s._pointerCancel = function (t, e) {
                  this._pointerDone(), this.pointerCancel(t, e);
                }),
                (s.pointerCancel = function (t, e) {
                  this.emitEvent("pointerCancel", [t, e]);
                }),
                (i.getPointerPoint = function (t) {
                  return { x: t.pageX, y: t.pageY };
                }),
                i
              );
            })(o, t);
          }.apply(e, s)) || (t.exports = n);
    })(window);
  },
  function (t, e, i) {
    var s, n;
    /*!
     * getSize v2.0.3
     * measure size of elements
     * MIT license
     */ window,
      void 0 ===
        (n =
          "function" ==
          typeof (s = function () {
            "use strict";
            function t(t) {
              var e = parseFloat(t);
              return -1 == t.indexOf("%") && !isNaN(e) && e;
            }
            var e =
                "undefined" == typeof console
                  ? function () {}
                  : function (t) {
                      console.error(t);
                    },
              i = [
                "paddingLeft",
                "paddingRight",
                "paddingTop",
                "paddingBottom",
                "marginLeft",
                "marginRight",
                "marginTop",
                "marginBottom",
                "borderLeftWidth",
                "borderRightWidth",
                "borderTopWidth",
                "borderBottomWidth",
              ],
              s = i.length;
            function n(t) {
              var i = getComputedStyle(t);
              return (
                i ||
                  e(
                    "Style returned " +
                      i +
                      ". Are you running this code in a hidden iframe on Firefox? See https://bit.ly/getsizebug1"
                  ),
                i
              );
            }
            var o,
              r = !1;
            function a(e) {
              if (
                ((function () {
                  if (!r) {
                    r = !0;
                    var e = document.createElement("div");
                    (e.style.width = "200px"),
                      (e.style.padding = "1px 2px 3px 4px"),
                      (e.style.borderStyle = "solid"),
                      (e.style.borderWidth = "1px 2px 3px 4px"),
                      (e.style.boxSizing = "border-box");
                    var i = document.body || document.documentElement;
                    i.appendChild(e);
                    var s = n(e);
                    (o = 200 == Math.round(t(s.width))),
                      (a.isBoxSizeOuter = o),
                      i.removeChild(e);
                  }
                })(),
                "string" == typeof e && (e = document.querySelector(e)),
                e && "object" == typeof e && e.nodeType)
              ) {
                var l = n(e);
                if ("none" == l.display)
                  return (function () {
                    for (
                      var t = {
                          width: 0,
                          height: 0,
                          innerWidth: 0,
                          innerHeight: 0,
                          outerWidth: 0,
                          outerHeight: 0,
                        },
                        e = 0;
                      e < s;
                      e++
                    )
                      t[i[e]] = 0;
                    return t;
                  })();
                var h = {};
                (h.width = e.offsetWidth), (h.height = e.offsetHeight);
                for (
                  var c = (h.isBorderBox = "border-box" == l.boxSizing), d = 0;
                  d < s;
                  d++
                ) {
                  var u = i[d],
                    p = l[u],
                    g = parseFloat(p);
                  h[u] = isNaN(g) ? 0 : g;
                }
                var f = h.paddingLeft + h.paddingRight,
                  v = h.paddingTop + h.paddingBottom,
                  m = h.marginLeft + h.marginRight,
                  y = h.marginTop + h.marginBottom,
                  S = h.borderLeftWidth + h.borderRightWidth,
                  b = h.borderTopWidth + h.borderBottomWidth,
                  w = c && o,
                  E = t(l.width);
                !1 !== E && (h.width = E + (w ? 0 : f + S));
                var C = t(l.height);
                return (
                  !1 !== C && (h.height = C + (w ? 0 : v + b)),
                  (h.innerWidth = h.width - (f + S)),
                  (h.innerHeight = h.height - (v + b)),
                  (h.outerWidth = h.width + m),
                  (h.outerHeight = h.height + y),
                  h
                );
              }
            }
            return a;
          })
            ? s.call(e, i, e, t)
            : s) || (t.exports = n);
  },
  function (t, e, i) {
    var s, n, o;
    /*!
     * Flickity v2.2.2
     * Touch, responsive, flickable carousels
     *
     * Licensed GPLv3 for open source use
     * or Flickity Commercial License for commercial use
     *
     * https://flickity.metafizzy.co
     * Copyright 2015-2021 Metafizzy
     */ window,
      (n = [i(1), i(18), i(20), i(21), i(22), i(23), i(24)]),
      void 0 ===
        (o =
          "function" ==
          typeof (s = function (t) {
            return t;
          })
            ? s.apply(e, n)
            : s) || (t.exports = o);
  },
  ,
  ,
  ,
  ,
  ,
  function (t, e, i) {
    var s, n;
    !(function (o, r) {
      "use strict";
      void 0 === (n = "function" == typeof (s = r) ? s.call(e, i, e, t) : s) ||
        (t.exports = n);
    })(window, function () {
      "use strict";
      var t = (function () {
        var t = window.Element.prototype;
        if (t.matches) return "matches";
        if (t.matchesSelector) return "matchesSelector";
        for (var e = ["webkit", "moz", "ms", "o"], i = 0; i < e.length; i++) {
          var s = e[i] + "MatchesSelector";
          if (t[s]) return s;
        }
      })();
      return function (e, i) {
        return e[t](i);
      };
    });
  },
  function (t, e, i) {
    var s, n;
    window,
      (s = [i(7)]),
      void 0 ===
        (n = function (t) {
          return (function (t, e) {
            "use strict";
            function i(t, e) {
              (this.element = t), (this.parent = e), this.create();
            }
            var s = i.prototype;
            return (
              (s.create = function () {
                (this.element.style.position = "absolute"),
                  this.element.setAttribute("aria-hidden", "true"),
                  (this.x = 0),
                  (this.shift = 0);
              }),
              (s.destroy = function () {
                this.unselect(), (this.element.style.position = "");
                var t = this.parent.originSide;
                (this.element.style[t] = ""),
                  this.element.removeAttribute("aria-hidden");
              }),
              (s.getSize = function () {
                this.size = e(this.element);
              }),
              (s.setPosition = function (t) {
                (this.x = t), this.updateTarget(), this.renderPosition(t);
              }),
              (s.updateTarget = s.setDefaultTarget =
                function () {
                  var t =
                    "left" == this.parent.originSide
                      ? "marginLeft"
                      : "marginRight";
                  this.target =
                    this.x +
                    this.size[t] +
                    this.size.width * this.parent.cellAlign;
                }),
              (s.renderPosition = function (t) {
                var e = this.parent.originSide;
                this.element.style[e] = this.parent.getPositionValue(t);
              }),
              (s.select = function () {
                this.element.classList.add("is-selected"),
                  this.element.removeAttribute("aria-hidden");
              }),
              (s.unselect = function () {
                this.element.classList.remove("is-selected"),
                  this.element.setAttribute("aria-hidden", "true");
              }),
              (s.wrapShift = function (t) {
                (this.shift = t),
                  this.renderPosition(this.x + this.parent.slideableWidth * t);
              }),
              (s.remove = function () {
                this.element.parentNode.removeChild(this.element);
              }),
              i
            );
          })(0, t);
        }.apply(e, s)) || (t.exports = n);
  },
  function (t, e, i) {
    var s, n;
    window,
      void 0 ===
        (n =
          "function" ==
          typeof (s = function () {
            "use strict";
            function t(t) {
              (this.parent = t),
                (this.isOriginLeft = "left" == t.originSide),
                (this.cells = []),
                (this.outerWidth = 0),
                (this.height = 0);
            }
            var e = t.prototype;
            return (
              (e.addCell = function (t) {
                if (
                  (this.cells.push(t),
                  (this.outerWidth += t.size.outerWidth),
                  (this.height = Math.max(t.size.outerHeight, this.height)),
                  1 == this.cells.length)
                ) {
                  this.x = t.x;
                  var e = this.isOriginLeft ? "marginLeft" : "marginRight";
                  this.firstMargin = t.size[e];
                }
              }),
              (e.updateTarget = function () {
                var t = this.isOriginLeft ? "marginRight" : "marginLeft",
                  e = this.getLastCell(),
                  i = e ? e.size[t] : 0,
                  s = this.outerWidth - (this.firstMargin + i);
                this.target =
                  this.x + this.firstMargin + s * this.parent.cellAlign;
              }),
              (e.getLastCell = function () {
                return this.cells[this.cells.length - 1];
              }),
              (e.select = function () {
                this.cells.forEach(function (t) {
                  t.select();
                });
              }),
              (e.unselect = function () {
                this.cells.forEach(function (t) {
                  t.unselect();
                });
              }),
              (e.getCellElements = function () {
                return this.cells.map(function (t) {
                  return t.element;
                });
              }),
              t
            );
          })
            ? s.call(e, i, e, t)
            : s) || (t.exports = n);
  },
  function (t, e, i) {
    var s, n;
    window,
      (s = [i(0)]),
      void 0 ===
        (n = function (t) {
          return (function (t, e) {
            "use strict";
            var i = {
              startAnimation: function () {
                this.isAnimating ||
                  ((this.isAnimating = !0),
                  (this.restingFrames = 0),
                  this.animate());
              },
              animate: function () {
                this.applyDragForce(), this.applySelectedAttraction();
                var t = this.x;
                if (
                  (this.integratePhysics(),
                  this.positionSlider(),
                  this.settle(t),
                  this.isAnimating)
                ) {
                  var e = this;
                  requestAnimationFrame(function () {
                    e.animate();
                  });
                }
              },
              positionSlider: function () {
                var t = this.x;
                this.options.wrapAround &&
                  this.cells.length > 1 &&
                  ((t = e.modulo(t, this.slideableWidth)),
                  (t -= this.slideableWidth),
                  this.shiftWrapCells(t)),
                  this.setTranslateX(t, this.isAnimating),
                  this.dispatchScrollEvent();
              },
              setTranslateX: function (t, e) {
                (t += this.cursorPosition),
                  (t = this.options.rightToLeft ? -t : t);
                var i = this.getPositionValue(t);
                this.slider.style.transform = e
                  ? "translate3d(" + i + ",0,0)"
                  : "translateX(" + i + ")";
              },
              dispatchScrollEvent: function () {
                var t = this.slides[0];
                if (t) {
                  var e = -this.x - t.target,
                    i = e / this.slidesWidth;
                  this.dispatchEvent("scroll", null, [i, e]);
                }
              },
              positionSliderAtSelected: function () {
                this.cells.length &&
                  ((this.x = -this.selectedSlide.target),
                  (this.velocity = 0),
                  this.positionSlider());
              },
              getPositionValue: function (t) {
                return this.options.percentPosition
                  ? 0.01 * Math.round((t / this.size.innerWidth) * 1e4) + "%"
                  : Math.round(t) + "px";
              },
              settle: function (t) {
                !this.isPointerDown &&
                  Math.round(100 * this.x) == Math.round(100 * t) &&
                  this.restingFrames++,
                  this.restingFrames > 2 &&
                    ((this.isAnimating = !1),
                    delete this.isFreeScrolling,
                    this.positionSlider(),
                    this.dispatchEvent("settle", null, [this.selectedIndex]));
              },
              shiftWrapCells: function (t) {
                var e = this.cursorPosition + t;
                this._shiftCells(this.beforeShiftCells, e, -1);
                var i =
                  this.size.innerWidth -
                  (t + this.slideableWidth + this.cursorPosition);
                this._shiftCells(this.afterShiftCells, i, 1);
              },
              _shiftCells: function (t, e, i) {
                for (var s = 0; s < t.length; s++) {
                  var n = t[s],
                    o = e > 0 ? i : 0;
                  n.wrapShift(o), (e -= n.size.outerWidth);
                }
              },
              _unshiftCells: function (t) {
                if (t && t.length)
                  for (var e = 0; e < t.length; e++) t[e].wrapShift(0);
              },
              integratePhysics: function () {
                (this.x += this.velocity),
                  (this.velocity *= this.getFrictionFactor());
              },
              applyForce: function (t) {
                this.velocity += t;
              },
              getFrictionFactor: function () {
                return (
                  1 -
                  this.options[
                    this.isFreeScrolling ? "freeScrollFriction" : "friction"
                  ]
                );
              },
              getRestingPosition: function () {
                return this.x + this.velocity / (1 - this.getFrictionFactor());
              },
              applyDragForce: function () {
                if (this.isDraggable && this.isPointerDown) {
                  var t = this.dragX - this.x - this.velocity;
                  this.applyForce(t);
                }
              },
              applySelectedAttraction: function () {
                if (
                  (!this.isDraggable || !this.isPointerDown) &&
                  !this.isFreeScrolling &&
                  this.slides.length
                ) {
                  var t =
                    (-1 * this.selectedSlide.target - this.x) *
                    this.options.selectedAttraction;
                  this.applyForce(t);
                }
              },
            };
            return i;
          })(0, t);
        }.apply(e, s)) || (t.exports = n);
  },
  function (t, e, i) {
    var s, n;
    !(function (o, r) {
      (s = [i(1), i(19), i(0)]),
        void 0 ===
          (n = function (t, e, i) {
            return (function (t, e, i, s) {
              "use strict";
              s.extend(e.defaults, { draggable: ">1", dragThreshold: 3 }),
                e.createMethods.push("_createDrag");
              var n = e.prototype;
              s.extend(n, i.prototype), (n._touchActionValue = "pan-y");
              var o = "createTouch" in document,
                r = !1;
              (n._createDrag = function () {
                this.on("activate", this.onActivateDrag),
                  this.on("uiChange", this._uiChangeDrag),
                  this.on("deactivate", this.onDeactivateDrag),
                  this.on("cellChange", this.updateDraggable),
                  o &&
                    !r &&
                    (t.addEventListener("touchmove", function () {}), (r = !0));
              }),
                (n.onActivateDrag = function () {
                  (this.handles = [this.viewport]),
                    this.bindHandles(),
                    this.updateDraggable();
                }),
                (n.onDeactivateDrag = function () {
                  this.unbindHandles(),
                    this.element.classList.remove("is-draggable");
                }),
                (n.updateDraggable = function () {
                  ">1" == this.options.draggable
                    ? (this.isDraggable = this.slides.length > 1)
                    : (this.isDraggable = this.options.draggable),
                    this.isDraggable
                      ? this.element.classList.add("is-draggable")
                      : this.element.classList.remove("is-draggable");
                }),
                (n.bindDrag = function () {
                  (this.options.draggable = !0), this.updateDraggable();
                }),
                (n.unbindDrag = function () {
                  (this.options.draggable = !1), this.updateDraggable();
                }),
                (n._uiChangeDrag = function () {
                  delete this.isFreeScrolling;
                }),
                (n.pointerDown = function (e, i) {
                  this.isDraggable
                    ? this.okayPointerDown(e) &&
                      (this._pointerDownPreventDefault(e),
                      this.pointerDownFocus(e),
                      document.activeElement != this.element &&
                        this.pointerDownBlur(),
                      (this.dragX = this.x),
                      this.viewport.classList.add("is-pointer-down"),
                      (this.pointerDownScroll = l()),
                      t.addEventListener("scroll", this),
                      this._pointerDownDefault(e, i))
                    : this._pointerDownDefault(e, i);
                }),
                (n._pointerDownDefault = function (t, e) {
                  (this.pointerDownPointer = {
                    pageX: e.pageX,
                    pageY: e.pageY,
                  }),
                    this._bindPostStartEvents(t),
                    this.dispatchEvent("pointerDown", t, [e]);
                });
              var a = { INPUT: !0, TEXTAREA: !0, SELECT: !0 };
              function l() {
                return { x: t.pageXOffset, y: t.pageYOffset };
              }
              return (
                (n.pointerDownFocus = function (t) {
                  a[t.target.nodeName] || this.focus();
                }),
                (n._pointerDownPreventDefault = function (t) {
                  var e = "touchstart" == t.type,
                    i = "touch" == t.pointerType,
                    s = a[t.target.nodeName];
                  e || i || s || t.preventDefault();
                }),
                (n.hasDragStarted = function (t) {
                  return Math.abs(t.x) > this.options.dragThreshold;
                }),
                (n.pointerUp = function (t, e) {
                  delete this.isTouchScrolling,
                    this.viewport.classList.remove("is-pointer-down"),
                    this.dispatchEvent("pointerUp", t, [e]),
                    this._dragPointerUp(t, e);
                }),
                (n.pointerDone = function () {
                  t.removeEventListener("scroll", this),
                    delete this.pointerDownScroll;
                }),
                (n.dragStart = function (e, i) {
                  this.isDraggable &&
                    ((this.dragStartPosition = this.x),
                    this.startAnimation(),
                    t.removeEventListener("scroll", this),
                    this.dispatchEvent("dragStart", e, [i]));
                }),
                (n.pointerMove = function (t, e) {
                  var i = this._dragPointerMove(t, e);
                  this.dispatchEvent("pointerMove", t, [e, i]),
                    this._dragMove(t, e, i);
                }),
                (n.dragMove = function (t, e, i) {
                  if (this.isDraggable) {
                    t.preventDefault(), (this.previousDragX = this.dragX);
                    var s = this.options.rightToLeft ? -1 : 1;
                    this.options.wrapAround && (i.x %= this.slideableWidth);
                    var n = this.dragStartPosition + i.x * s;
                    if (!this.options.wrapAround && this.slides.length) {
                      var o = Math.max(
                        -this.slides[0].target,
                        this.dragStartPosition
                      );
                      n = n > o ? 0.5 * (n + o) : n;
                      var r = Math.min(
                        -this.getLastSlide().target,
                        this.dragStartPosition
                      );
                      n = n < r ? 0.5 * (n + r) : n;
                    }
                    (this.dragX = n),
                      (this.dragMoveTime = new Date()),
                      this.dispatchEvent("dragMove", t, [e, i]);
                  }
                }),
                (n.dragEnd = function (t, e) {
                  if (this.isDraggable) {
                    this.options.freeScroll && (this.isFreeScrolling = !0);
                    var i = this.dragEndRestingSelect();
                    if (this.options.freeScroll && !this.options.wrapAround) {
                      var s = this.getRestingPosition();
                      this.isFreeScrolling =
                        -s > this.slides[0].target &&
                        -s < this.getLastSlide().target;
                    } else
                      this.options.freeScroll ||
                        i != this.selectedIndex ||
                        (i += this.dragEndBoostSelect());
                    delete this.previousDragX,
                      (this.isDragSelect = this.options.wrapAround),
                      this.select(i),
                      delete this.isDragSelect,
                      this.dispatchEvent("dragEnd", t, [e]);
                  }
                }),
                (n.dragEndRestingSelect = function () {
                  var t = this.getRestingPosition(),
                    e = Math.abs(this.getSlideDistance(-t, this.selectedIndex)),
                    i = this._getClosestResting(t, e, 1),
                    s = this._getClosestResting(t, e, -1);
                  return i.distance < s.distance ? i.index : s.index;
                }),
                (n._getClosestResting = function (t, e, i) {
                  for (
                    var s = this.selectedIndex,
                      n = 1 / 0,
                      o =
                        this.options.contain && !this.options.wrapAround
                          ? function (t, e) {
                              return t <= e;
                            }
                          : function (t, e) {
                              return t < e;
                            };
                    o(e, n) &&
                    ((s += i),
                    (n = e),
                    null !== (e = this.getSlideDistance(-t, s)));

                  )
                    e = Math.abs(e);
                  return { distance: n, index: s - i };
                }),
                (n.getSlideDistance = function (t, e) {
                  var i = this.slides.length,
                    n = this.options.wrapAround && i > 1,
                    o = n ? s.modulo(e, i) : e,
                    r = this.slides[o];
                  if (!r) return null;
                  var a = n ? this.slideableWidth * Math.floor(e / i) : 0;
                  return t - (r.target + a);
                }),
                (n.dragEndBoostSelect = function () {
                  if (
                    void 0 === this.previousDragX ||
                    !this.dragMoveTime ||
                    new Date() - this.dragMoveTime > 100
                  )
                    return 0;
                  var t = this.getSlideDistance(
                      -this.dragX,
                      this.selectedIndex
                    ),
                    e = this.previousDragX - this.dragX;
                  return t > 0 && e > 0 ? 1 : t < 0 && e < 0 ? -1 : 0;
                }),
                (n.staticClick = function (t, e) {
                  var i = this.getParentCell(t.target),
                    s = i && i.element,
                    n = i && this.cells.indexOf(i);
                  this.dispatchEvent("staticClick", t, [e, s, n]);
                }),
                (n.onscroll = function () {
                  var t = l(),
                    e = this.pointerDownScroll.x - t.x,
                    i = this.pointerDownScroll.y - t.y;
                  (Math.abs(e) > 3 || Math.abs(i) > 3) && this._pointerDone();
                }),
                e
              );
            })(o, t, e, i);
          }.apply(e, s)) || (t.exports = n);
    })(window);
  },
  function (t, e, i) {
    var s, n;
    /*!
     * Unidragger v2.3.1
     * Draggable base class
     * MIT license
     */ !(function (o, r) {
      (s = [i(6)]),
        void 0 ===
          (n = function (t) {
            return (function (t, e) {
              "use strict";
              function i() {}
              var s = (i.prototype = Object.create(e.prototype));
              (s.bindHandles = function () {
                this._bindHandles(!0);
              }),
                (s.unbindHandles = function () {
                  this._bindHandles(!1);
                }),
                (s._bindHandles = function (e) {
                  for (
                    var i = (e = void 0 === e || e)
                        ? "addEventListener"
                        : "removeEventListener",
                      s = e ? this._touchActionValue : "",
                      n = 0;
                    n < this.handles.length;
                    n++
                  ) {
                    var o = this.handles[n];
                    this._bindStartEvent(o, e),
                      o[i]("click", this),
                      t.PointerEvent && (o.style.touchAction = s);
                  }
                }),
                (s._touchActionValue = "none"),
                (s.pointerDown = function (t, e) {
                  this.okayPointerDown(t) &&
                    ((this.pointerDownPointer = {
                      pageX: e.pageX,
                      pageY: e.pageY,
                    }),
                    t.preventDefault(),
                    this.pointerDownBlur(),
                    this._bindPostStartEvents(t),
                    this.emitEvent("pointerDown", [t, e]));
                });
              var n = { TEXTAREA: !0, INPUT: !0, SELECT: !0, OPTION: !0 },
                o = {
                  radio: !0,
                  checkbox: !0,
                  button: !0,
                  submit: !0,
                  image: !0,
                  file: !0,
                };
              return (
                (s.okayPointerDown = function (t) {
                  var e = n[t.target.nodeName],
                    i = o[t.target.type],
                    s = !e || i;
                  return s || this._pointerReset(), s;
                }),
                (s.pointerDownBlur = function () {
                  var t = document.activeElement;
                  t && t.blur && t != document.body && t.blur();
                }),
                (s.pointerMove = function (t, e) {
                  var i = this._dragPointerMove(t, e);
                  this.emitEvent("pointerMove", [t, e, i]),
                    this._dragMove(t, e, i);
                }),
                (s._dragPointerMove = function (t, e) {
                  var i = {
                    x: e.pageX - this.pointerDownPointer.pageX,
                    y: e.pageY - this.pointerDownPointer.pageY,
                  };
                  return (
                    !this.isDragging &&
                      this.hasDragStarted(i) &&
                      this._dragStart(t, e),
                    i
                  );
                }),
                (s.hasDragStarted = function (t) {
                  return Math.abs(t.x) > 3 || Math.abs(t.y) > 3;
                }),
                (s.pointerUp = function (t, e) {
                  this.emitEvent("pointerUp", [t, e]),
                    this._dragPointerUp(t, e);
                }),
                (s._dragPointerUp = function (t, e) {
                  this.isDragging
                    ? this._dragEnd(t, e)
                    : this._staticClick(t, e);
                }),
                (s._dragStart = function (t, e) {
                  (this.isDragging = !0),
                    (this.isPreventingClicks = !0),
                    this.dragStart(t, e);
                }),
                (s.dragStart = function (t, e) {
                  this.emitEvent("dragStart", [t, e]);
                }),
                (s._dragMove = function (t, e, i) {
                  this.isDragging && this.dragMove(t, e, i);
                }),
                (s.dragMove = function (t, e, i) {
                  t.preventDefault(), this.emitEvent("dragMove", [t, e, i]);
                }),
                (s._dragEnd = function (t, e) {
                  (this.isDragging = !1),
                    setTimeout(
                      function () {
                        delete this.isPreventingClicks;
                      }.bind(this)
                    ),
                    this.dragEnd(t, e);
                }),
                (s.dragEnd = function (t, e) {
                  this.emitEvent("dragEnd", [t, e]);
                }),
                (s.onclick = function (t) {
                  this.isPreventingClicks && t.preventDefault();
                }),
                (s._staticClick = function (t, e) {
                  (this.isIgnoringMouseUp && "mouseup" == t.type) ||
                    (this.staticClick(t, e),
                    "mouseup" != t.type &&
                      ((this.isIgnoringMouseUp = !0),
                      setTimeout(
                        function () {
                          delete this.isIgnoringMouseUp;
                        }.bind(this),
                        400
                      )));
                }),
                (s.staticClick = function (t, e) {
                  this.emitEvent("staticClick", [t, e]);
                }),
                (i.getPointerPoint = e.getPointerPoint),
                i
              );
            })(o, t);
          }.apply(e, s)) || (t.exports = n);
    })(window);
  },
  function (t, e, i) {
    var s, n;
    window,
      (s = [i(1), i(6), i(0)]),
      void 0 ===
        (n = function (t, e, i) {
          return (function (t, e, i, s) {
            "use strict";
            var n = "http://www.w3.org/2000/svg";
            function o(t, e) {
              (this.direction = t), (this.parent = e), this._create();
            }
            (o.prototype = Object.create(i.prototype)),
              (o.prototype._create = function () {
                (this.isEnabled = !0), (this.isPrevious = -1 == this.direction);
                var t = this.parent.options.rightToLeft ? 1 : -1;
                this.isLeft = this.direction == t;
                var e = (this.element = document.createElement("button"));
                (e.className = "flickity-button flickity-prev-next-button"),
                  (e.className += this.isPrevious ? " previous" : " next"),
                  e.setAttribute("type", "button"),
                  this.disable(),
                  e.setAttribute(
                    "aria-label",
                    this.isPrevious ? "Previous" : "Next"
                  );
                var i = this.createSVG();
                e.appendChild(i),
                  this.parent.on("select", this.update.bind(this)),
                  this.on(
                    "pointerDown",
                    this.parent.childUIPointerDown.bind(this.parent)
                  );
              }),
              (o.prototype.activate = function () {
                this.bindStartEvent(this.element),
                  this.element.addEventListener("click", this),
                  this.parent.element.appendChild(this.element);
              }),
              (o.prototype.deactivate = function () {
                this.parent.element.removeChild(this.element),
                  this.unbindStartEvent(this.element),
                  this.element.removeEventListener("click", this);
              }),
              (o.prototype.createSVG = function () {
                var t = document.createElementNS(n, "svg");
                t.setAttribute("class", "flickity-button-icon"),
                  t.setAttribute("viewBox", "0 0 100 100");
                var e,
                  i = document.createElementNS(n, "path"),
                  s =
                    "string" == typeof (e = this.parent.options.arrowShape)
                      ? e
                      : "M " +
                        e.x0 +
                        ",50 L " +
                        e.x1 +
                        "," +
                        (e.y1 + 50) +
                        " L " +
                        e.x2 +
                        "," +
                        (e.y2 + 50) +
                        " L " +
                        e.x3 +
                        ",50  L " +
                        e.x2 +
                        "," +
                        (50 - e.y2) +
                        " L " +
                        e.x1 +
                        "," +
                        (50 - e.y1) +
                        " Z";
                return (
                  i.setAttribute("d", s),
                  i.setAttribute("class", "arrow"),
                  this.isLeft ||
                    i.setAttribute(
                      "transform",
                      "translate(100, 100) rotate(180) "
                    ),
                  t.appendChild(i),
                  t
                );
              }),
              (o.prototype.handleEvent = s.handleEvent),
              (o.prototype.onclick = function () {
                if (this.isEnabled) {
                  this.parent.uiChange();
                  var t = this.isPrevious ? "previous" : "next";
                  this.parent[t]();
                }
              }),
              (o.prototype.enable = function () {
                this.isEnabled ||
                  ((this.element.disabled = !1), (this.isEnabled = !0));
              }),
              (o.prototype.disable = function () {
                this.isEnabled &&
                  ((this.element.disabled = !0), (this.isEnabled = !1));
              }),
              (o.prototype.update = function () {
                var t = this.parent.slides;
                if (this.parent.options.wrapAround && t.length > 1)
                  this.enable();
                else {
                  var e = t.length ? t.length - 1 : 0,
                    i = this.isPrevious ? 0 : e;
                  this[this.parent.selectedIndex == i ? "disable" : "enable"]();
                }
              }),
              (o.prototype.destroy = function () {
                this.deactivate(), this.allOff();
              }),
              s.extend(e.defaults, {
                prevNextButtons: !0,
                arrowShape: { x0: 10, x1: 60, y1: 50, x2: 70, y2: 40, x3: 30 },
              }),
              e.createMethods.push("_createPrevNextButtons");
            var r = e.prototype;
            return (
              (r._createPrevNextButtons = function () {
                this.options.prevNextButtons &&
                  ((this.prevButton = new o(-1, this)),
                  (this.nextButton = new o(1, this)),
                  this.on("activate", this.activatePrevNextButtons));
              }),
              (r.activatePrevNextButtons = function () {
                this.prevButton.activate(),
                  this.nextButton.activate(),
                  this.on("deactivate", this.deactivatePrevNextButtons);
              }),
              (r.deactivatePrevNextButtons = function () {
                this.prevButton.deactivate(),
                  this.nextButton.deactivate(),
                  this.off("deactivate", this.deactivatePrevNextButtons);
              }),
              (e.PrevNextButton = o),
              e
            );
          })(0, t, e, i);
        }.apply(e, s)) || (t.exports = n);
  },
  function (t, e, i) {
    var s, n;
    window,
      (s = [i(1), i(6), i(0)]),
      void 0 ===
        (n = function (t, e, i) {
          return (function (t, e, i, s) {
            "use strict";
            function n(t) {
              (this.parent = t), this._create();
            }
            (n.prototype = Object.create(i.prototype)),
              (n.prototype._create = function () {
                (this.holder = document.createElement("ol")),
                  (this.holder.className = "flickity-page-dots"),
                  (this.dots = []),
                  (this.handleClick = this.onClick.bind(this)),
                  this.on(
                    "pointerDown",
                    this.parent.childUIPointerDown.bind(this.parent)
                  );
              }),
              (n.prototype.activate = function () {
                this.setDots(),
                  this.holder.addEventListener("click", this.handleClick),
                  this.bindStartEvent(this.holder),
                  this.parent.element.appendChild(this.holder);
              }),
              (n.prototype.deactivate = function () {
                this.holder.removeEventListener("click", this.handleClick),
                  this.unbindStartEvent(this.holder),
                  this.parent.element.removeChild(this.holder);
              }),
              (n.prototype.setDots = function () {
                var t = this.parent.slides.length - this.dots.length;
                t > 0 ? this.addDots(t) : t < 0 && this.removeDots(-t);
              }),
              (n.prototype.addDots = function (t) {
                for (
                  var e = document.createDocumentFragment(),
                    i = [],
                    s = this.dots.length,
                    n = s + t,
                    o = s;
                  o < n;
                  o++
                ) {
                  var r = document.createElement("li");
                  (r.className = "dot"),
                    r.setAttribute("aria-label", "Page dot " + (o + 1)),
                    e.appendChild(r),
                    i.push(r);
                }
                this.holder.appendChild(e), (this.dots = this.dots.concat(i));
              }),
              (n.prototype.removeDots = function (t) {
                this.dots.splice(this.dots.length - t, t).forEach(function (t) {
                  this.holder.removeChild(t);
                }, this);
              }),
              (n.prototype.updateSelected = function () {
                this.selectedDot &&
                  ((this.selectedDot.className = "dot"),
                  this.selectedDot.removeAttribute("aria-current")),
                  this.dots.length &&
                    ((this.selectedDot = this.dots[this.parent.selectedIndex]),
                    (this.selectedDot.className = "dot is-selected"),
                    this.selectedDot.setAttribute("aria-current", "step"));
              }),
              (n.prototype.onTap = n.prototype.onClick =
                function (t) {
                  var e = t.target;
                  if ("LI" == e.nodeName) {
                    this.parent.uiChange();
                    var i = this.dots.indexOf(e);
                    this.parent.select(i);
                  }
                }),
              (n.prototype.destroy = function () {
                this.deactivate(), this.allOff();
              }),
              (e.PageDots = n),
              s.extend(e.defaults, { pageDots: !0 }),
              e.createMethods.push("_createPageDots");
            var o = e.prototype;
            return (
              (o._createPageDots = function () {
                this.options.pageDots &&
                  ((this.pageDots = new n(this)),
                  this.on("activate", this.activatePageDots),
                  this.on("select", this.updateSelectedPageDots),
                  this.on("cellChange", this.updatePageDots),
                  this.on("resize", this.updatePageDots),
                  this.on("deactivate", this.deactivatePageDots));
              }),
              (o.activatePageDots = function () {
                this.pageDots.activate();
              }),
              (o.updateSelectedPageDots = function () {
                this.pageDots.updateSelected();
              }),
              (o.updatePageDots = function () {
                this.pageDots.setDots();
              }),
              (o.deactivatePageDots = function () {
                this.pageDots.deactivate();
              }),
              (e.PageDots = n),
              e
            );
          })(0, t, e, i);
        }.apply(e, s)) || (t.exports = n);
  },
  function (t, e, i) {
    var s, n;
    window,
      (s = [i(5), i(0), i(1)]),
      void 0 ===
        (n = function (t, e, i) {
          return (function (t, e, i) {
            "use strict";
            function s(t) {
              (this.parent = t),
                (this.state = "stopped"),
                (this.onVisibilityChange = this.visibilityChange.bind(this)),
                (this.onVisibilityPlay = this.visibilityPlay.bind(this));
            }
            (s.prototype = Object.create(t.prototype)),
              (s.prototype.play = function () {
                "playing" != this.state &&
                  (document.hidden
                    ? document.addEventListener(
                        "visibilitychange",
                        this.onVisibilityPlay
                      )
                    : ((this.state = "playing"),
                      document.addEventListener(
                        "visibilitychange",
                        this.onVisibilityChange
                      ),
                      this.tick()));
              }),
              (s.prototype.tick = function () {
                if ("playing" == this.state) {
                  var t = this.parent.options.autoPlay;
                  t = "number" == typeof t ? t : 3e3;
                  var e = this;
                  this.clear(),
                    (this.timeout = setTimeout(function () {
                      e.parent.next(!0), e.tick();
                    }, t));
                }
              }),
              (s.prototype.stop = function () {
                (this.state = "stopped"),
                  this.clear(),
                  document.removeEventListener(
                    "visibilitychange",
                    this.onVisibilityChange
                  );
              }),
              (s.prototype.clear = function () {
                clearTimeout(this.timeout);
              }),
              (s.prototype.pause = function () {
                "playing" == this.state &&
                  ((this.state = "paused"), this.clear());
              }),
              (s.prototype.unpause = function () {
                "paused" == this.state && this.play();
              }),
              (s.prototype.visibilityChange = function () {
                this[document.hidden ? "pause" : "unpause"]();
              }),
              (s.prototype.visibilityPlay = function () {
                this.play(),
                  document.removeEventListener(
                    "visibilitychange",
                    this.onVisibilityPlay
                  );
              }),
              e.extend(i.defaults, { pauseAutoPlayOnHover: !0 }),
              i.createMethods.push("_createPlayer");
            var n = i.prototype;
            return (
              (n._createPlayer = function () {
                (this.player = new s(this)),
                  this.on("activate", this.activatePlayer),
                  this.on("uiChange", this.stopPlayer),
                  this.on("pointerDown", this.stopPlayer),
                  this.on("deactivate", this.deactivatePlayer);
              }),
              (n.activatePlayer = function () {
                this.options.autoPlay &&
                  (this.player.play(),
                  this.element.addEventListener("mouseenter", this));
              }),
              (n.playPlayer = function () {
                this.player.play();
              }),
              (n.stopPlayer = function () {
                this.player.stop();
              }),
              (n.pausePlayer = function () {
                this.player.pause();
              }),
              (n.unpausePlayer = function () {
                this.player.unpause();
              }),
              (n.deactivatePlayer = function () {
                this.player.stop(),
                  this.element.removeEventListener("mouseenter", this);
              }),
              (n.onmouseenter = function () {
                this.options.pauseAutoPlayOnHover &&
                  (this.player.pause(),
                  this.element.addEventListener("mouseleave", this));
              }),
              (n.onmouseleave = function () {
                this.player.unpause(),
                  this.element.removeEventListener("mouseleave", this);
              }),
              (i.Player = s),
              i
            );
          })(t, e, i);
        }.apply(e, s)) || (t.exports = n);
  },
  function (t, e, i) {
    var s, n;
    window,
      (s = [i(1), i(0)]),
      void 0 ===
        (n = function (t, e) {
          return (function (t, e, i) {
            "use strict";
            var s = e.prototype;
            return (
              (s.insert = function (t, e) {
                var i = this._makeCells(t);
                if (i && i.length) {
                  var s = this.cells.length;
                  e = void 0 === e ? s : e;
                  var n = (function (t) {
                      var e = document.createDocumentFragment();
                      return (
                        t.forEach(function (t) {
                          e.appendChild(t.element);
                        }),
                        e
                      );
                    })(i),
                    o = e == s;
                  if (o) this.slider.appendChild(n);
                  else {
                    var r = this.cells[e].element;
                    this.slider.insertBefore(n, r);
                  }
                  if (0 === e) this.cells = i.concat(this.cells);
                  else if (o) this.cells = this.cells.concat(i);
                  else {
                    var a = this.cells.splice(e, s - e);
                    this.cells = this.cells.concat(i).concat(a);
                  }
                  this._sizeCells(i), this.cellChange(e, !0);
                }
              }),
              (s.append = function (t) {
                this.insert(t, this.cells.length);
              }),
              (s.prepend = function (t) {
                this.insert(t, 0);
              }),
              (s.remove = function (t) {
                var e = this.getCells(t);
                if (e && e.length) {
                  var s = this.cells.length - 1;
                  e.forEach(function (t) {
                    t.remove();
                    var e = this.cells.indexOf(t);
                    (s = Math.min(e, s)), i.removeFrom(this.cells, t);
                  }, this),
                    this.cellChange(s, !0);
                }
              }),
              (s.cellSizeChange = function (t) {
                var e = this.getCell(t);
                if (e) {
                  e.getSize();
                  var i = this.cells.indexOf(e);
                  this.cellChange(i);
                }
              }),
              (s.cellChange = function (t, e) {
                var i = this.selectedElement;
                this._positionCells(t),
                  this._getWrapShiftCells(),
                  this.setGallerySize();
                var s = this.getCell(i);
                s && (this.selectedIndex = this.getCellSlideIndex(s)),
                  (this.selectedIndex = Math.min(
                    this.slides.length - 1,
                    this.selectedIndex
                  )),
                  this.emitEvent("cellChange", [t]),
                  this.select(this.selectedIndex),
                  e && this.positionSliderAtSelected();
              }),
              e
            );
          })(0, t, e);
        }.apply(e, s)) || (t.exports = n);
  },
  function (t, e, i) {
    var s, n;
    window,
      (s = [i(1), i(0)]),
      void 0 ===
        (n = function (t, e) {
          return (function (t, e, i) {
            "use strict";
            e.createMethods.push("_createLazyload");
            var s = e.prototype;
            function n(t, e) {
              (this.img = t), (this.flickity = e), this.load();
            }
            return (
              (s._createLazyload = function () {
                this.on("select", this.lazyLoad);
              }),
              (s.lazyLoad = function () {
                var t = this.options.lazyLoad;
                if (t) {
                  var e = "number" == typeof t ? t : 0,
                    s = this.getAdjacentCellElements(e),
                    o = [];
                  s.forEach(function (t) {
                    var e = (function (t) {
                      if ("IMG" == t.nodeName) {
                        var e = t.getAttribute("data-flickity-lazyload"),
                          s = t.getAttribute("data-flickity-lazyload-src"),
                          n = t.getAttribute("data-flickity-lazyload-srcset");
                        if (e || s || n) return [t];
                      }
                      var o = t.querySelectorAll(
                        "img[data-flickity-lazyload], img[data-flickity-lazyload-src], img[data-flickity-lazyload-srcset]"
                      );
                      return i.makeArray(o);
                    })(t);
                    o = o.concat(e);
                  }),
                    o.forEach(function (t) {
                      new n(t, this);
                    }, this);
                }
              }),
              (n.prototype.handleEvent = i.handleEvent),
              (n.prototype.load = function () {
                this.img.addEventListener("load", this),
                  this.img.addEventListener("error", this);
                var t =
                    this.img.getAttribute("data-flickity-lazyload") ||
                    this.img.getAttribute("data-flickity-lazyload-src"),
                  e = this.img.getAttribute("data-flickity-lazyload-srcset");
                (this.img.src = t),
                  e && this.img.setAttribute("srcset", e),
                  this.img.removeAttribute("data-flickity-lazyload"),
                  this.img.removeAttribute("data-flickity-lazyload-src"),
                  this.img.removeAttribute("data-flickity-lazyload-srcset");
              }),
              (n.prototype.onload = function (t) {
                this.complete(t, "flickity-lazyloaded");
              }),
              (n.prototype.onerror = function (t) {
                this.complete(t, "flickity-lazyerror");
              }),
              (n.prototype.complete = function (t, e) {
                this.img.removeEventListener("load", this),
                  this.img.removeEventListener("error", this);
                var i = this.flickity.getParentCell(this.img),
                  s = i && i.element;
                this.flickity.cellSizeChange(s),
                  this.img.classList.add(e),
                  this.flickity.dispatchEvent("lazyLoad", t, s);
              }),
              (e.LazyLoader = n),
              e
            );
          })(0, t, e);
        }.apply(e, s)) || (t.exports = n);
  },
  ,
  ,
  ,
  ,
  ,
  function (t, e, i) {
    "use strict";
    i.r(e);
    class s {
      constructor(t) {
        (this.el = t),
          (this.emailInput = this.el.querySelector('input[type="email"]')),
          (this.successEl = this.el.querySelector(
            "[data-email-capture-success-url]"
          )),
          (this.updateSuccessUrlInputValue =
            this.updateSuccessUrlInputValue.bind(this)),
          (this.successUrlBase = this.getSuccessUrlBase()),
          this.emailInput.addEventListener(
            "blur",
            this.updateSuccessUrlInputValue
          ),
          this.emailInput.addEventListener(
            "keyup",
            this.updateSuccessUrlInputValue
          );
      }
      getSuccessUrlBase() {
        return null !== this.successEl
          ? this.successEl.dataset.emailCaptureSuccessUrl
          : void 0;
      }
      updateSuccessUrlInputValue(t) {
        let e = encodeURIComponent(t.target.value);
        void 0 !== this.successUrlBase &&
          (this.successEl.value = `${this.successUrlBase}&email=${e}`);
      }
    }
    class n {
      constructor(t) {
        (this.el = t),
          (this.inputs = [...this.el.querySelectorAll("input")]),
          (this.container = document.documentElement),
          this.inputs.forEach((t) => {
            t.addEventListener("focus", () => this.handleFocus(t));
          }),
          this.trapFocus(t);
      }
      handleFocus(t) {
        this.el.scrollTo(0, t.offsetTop);
      }
      trapFocus(t) {
        if (t.classList.contains("mega-menu__main")) return;
        const e =
            'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])',
          i = t.querySelectorAll(e)[0],
          s = t.querySelectorAll(e),
          n = s[s.length - 1];
        document.addEventListener("keydown", (t) => {
          ("Tab" === t.key || 9 === t.keyCode) &&
            (t.shiftKey
              ? document.activeElement === i &&
                n &&
                (n.focus(), t.preventDefault())
              : document.activeElement === n &&
                i &&
                (i.focus(), t.preventDefault()));
        });
      }
    }
    class o {
      constructor(t) {
        (this.el = t),
          (this.el.pmcMobileHeightToggle = this),
          (this.isExpanded = !1),
          (this.toggle = this.toggle.bind(this)),
          (this.onClick = this.onClick.bind(this)),
          (this.onFocus = this.onFocus.bind(this)),
          (this.onBlur = this.onBlur.bind(this)),
          (this.keyDown = this.keyDown.bind(this)),
          this.el.addEventListener("click", this.onClick),
          this.el.addEventListener("focus", this.onFocus),
          this.el.addEventListener("blur", this.onBlur);
      }
      destroy() {
        this.el.classList.remove("is-expanded"),
          (this.isExpanded = !1),
          delete this.el.pmcMobileHeightToggle;
      }
      onBlur(t) {
        document.removeEventListener("keydown", this.keyDown);
      }
      keyDown(t) {
        "Enter" === t.key && this.toggle(t);
      }
      onFocus(t) {
        document.addEventListener("keydown", this.keyDown);
      }
      onClick(t) {
        void 0 !== this.el.pmcMobileHeightToggle &&
          t.target.classList.contains("lrv-js-MobileHeightToggle-trigger") &&
          this.toggle(t);
      }
      toggle(t) {
        this.isExpanded
          ? (this.el.classList.remove("is-expanded"), (this.isExpanded = !1))
          : (t.preventDefault(),
            this.el.classList.add("is-expanded"),
            (this.isExpanded = !0));
      }
    }
    var r = {
        init: function () {
          let t = pmc.cookie.get("vy_fonts_loaded");
          void 0 !== t && t
            ? this.load("directload")
            : window.addEventListener("load", () => {
                this.load("load");
              });
        },
        load: function (t) {
          this.getThemeUrl(), this.loadcount++;
          WebFont.load({
            custom: {
              families: [
                "IBM Plex Mono",
                "IBM Plex Sans:n4,n7",
                "IBM Plex Serif",
                "Graphik XX Cond",
                "Para Supreme Regular",
              ],
            },
            active: function () {
              try {
                "load" == t && pmc.cookie.set("vy_fonts_loaded", 1, 604800),
                  console.log("fonts loaded " + t);
              } catch (t) {}
            },
          });
        },
        getThemeUrl: function () {
          return "undefined" == typeof pmc_common_urls
            ? ""
            : pmc_common_urls.current_theme_uri;
        },
      },
      a = i(2);
    class l {
      constructor(t) {
        (this.el = t),
          (this.isOpen = !1),
          (this.trigger = this.el.querySelector(
            ".js-ExpandableSearch-trigger"
          )),
          (this.target = this.el.querySelector(".js-ExpandableSearch-target")),
          (this.targetInput = this.target.querySelector("input")),
          this.trigger.addEventListener("click", () => {
            this.toggleSearch();
          }),
          document.addEventListener("keydown", (t) => {
            27 === t.keyCode && this.collapseSearch();
          }),
          document.body.addEventListener("click", (t) => {
            this.el.contains(t.target) || this.collapseSearch();
          }),
          document.addEventListener("focusin", () => {
            !this.el.contains(document.activeElement) &&
              this.isOpen &&
              this.collapseSearch();
          });
      }
      updateState() {
        this.isOpen = !this.isOpen;
      }
      toggleSearch() {
        this.el.classList.toggle("is-ExpandableSearch-open"),
          this.target.toggleAttribute("hidden"),
          this.target.classList.toggle("js-fade-is-out"),
          this.target.classList.toggle("js-fade-is-in"),
          this.updateState(),
          this.isOpen && this.targetInput.focus();
      }
      collapseSearch() {
        this.isOpen && (this.toggleSearch(), this.trigger.focus());
      }
    }
    var h = i(8),
      c = i.n(h);
    class d {
      constructor(t) {
        (this.el = t),
          (this.selectEl = t.querySelector(".js-SelectNav-select")),
          t.classList.contains("js-SelectNav-redirect") &&
            this.selectEl.addEventListener("change", this.onChange.bind(this));
      }
      onChange() {
        const t =
          this.selectEl.options[this.selectEl.selectedIndex].dataset.selectUrl;
        t && (window.location.href = t);
      }
    }
    class u {
      constructor(t) {
        (this.el = t),
          (this.el.pmcHeader = this),
          (this.container = document.documentElement),
          this.initSticky(),
          this.initSearch();
      }
      destroy() {
        this.destroySticky(), this.destroySearch(), delete this.el.pmcHeader;
      }
      get stickyClass() {
        return this.el.dataset.headerStickyClass || "is-sticky";
      }
      get readyClass() {
        return this.el.dataset.headerReadyClass || "is-header-ready";
      }
      get searchClass() {
        return this.el.dataset.headerSearchClass || "is-search-expanded";
      }
      initSticky() {
        window.IntersectionObserver &&
          ((this.observerOptions = {
            root: null,
            rootMargin: "80px",
            threshold: [1],
          }),
          (this.toggleSticky = this.toggleSticky.bind(this)),
          (this.observer = new IntersectionObserver(
            this.toggleSticky,
            this.observerOptions
          )),
          this.observer.observe(this.el),
          this.container.classList.add(this.readyClass));
      }
      destroySticky() {
        window.IntersectionObserver &&
          (this.observer.disconnect(),
          this.container.classList.remove(this.stickyClass, this.readyClass));
      }
      toggleSticky(t) {
        window.IntersectionObserver &&
          t.forEach((t) => {
            const e = t.intersectionRatio;
            1 === e
              ? this.container.classList.remove(this.stickyClass)
              : 1 > e && this.container.classList.add(this.stickyClass);
          });
      }
      initSearch() {
        (this.searchTrigger = this.el.querySelector(
          "[data-header-search-trigger]"
        )),
          null !== this.searchTrigger &&
            ((this.expandSearch = this.expandSearch.bind(this)),
            (this.collapseSearch = this.collapseSearch.bind(this)),
            this.searchTrigger.addEventListener("click", this.expandSearch));
      }
      destroySearch() {
        document.body.removeEventListener("click", this.collapseSearch),
          this.searchTrigger.removeEventListener("click", this.expandSearch),
          this.container.classList.remove(this.searchClass);
      }
      expandSearch(t) {
        t.preventDefault(),
          t.stopPropagation(),
          this.container.classList.add(this.searchClass),
          this.searchTrigger.removeEventListener("click", this.expandSearch),
          setTimeout(
            () => document.body.addEventListener("click", this.collapseSearch),
            1
          );
      }
      collapseSearch(t) {
        t.target === this.searchTrigger ||
          this.searchTrigger.contains(t.target) ||
          (this.container.classList.remove(this.searchClass),
          this.searchTrigger.addEventListener("click", this.expandSearch),
          document.body.removeEventListener("click", this.collapseSearch));
      }
    }
    function p() {
      void 0 === window.pmc_side_skin_classes_removed &&
        (document.documentElement.classList.add("has-side-skins"),
        [...document.querySelectorAll('[class*="@desktop-xl"]')].forEach(
          (t) => {
            const e = t.className.split(" ");
            (t.className = ""),
              e.forEach((e) => {
                e.includes("@desktop-xl") || (t.className += " " + e);
              });
          }
        ),
        (window.pmc_side_skin_classes_removed = !0)),
        window.dispatchEvent(new Event("resize"));
    }
    // p();

    var g = i(3);
    class f {
      constructor(t) {
        null !== t &&
          ((t.src = t.dataset.lazySrc), (t.dataset.lazySrc = "lazyloaded"));
      }
    }
    /* class v {
      constructor() {
        (this.subscriptionBox = document.querySelector(
          ".a-subscription-banner"
        )),
          (this.toggleSubscriptionBox = this.toggleSubscriptionBox.bind(this)),
          !0 === cxpmc.initialized &&
            ((this.subsHeaderBtn = document.querySelectorAll(".cx-hdr-link")),
            this.subsHeaderBtn.forEach((t) => {
              t.addEventListener("mouseenter", this.toggleSubscriptionBox, !1);
            }),
            this.subscriptionBox.addEventListener(
              "mouseleave",
              this.toggleSubscriptionBox,
              !1
            )),
          void 0 !== window.pmc &&
            void 0 !== window.pmc.subscription_v2 &&
            void 0 !== typeof OneSignal &&
            pmc.subscription_v2.send_onesignal_tags();
      }
      toggleSubscriptionBox() {
        this.subscriptionBox.classList.toggle("lrv-a-hidden");
      }
    } */
    class m {
      constructor(t) {
        (this.el = t),
          (this.eventTitle = this.el.dataset.title),
          (this.eventStart = this.el.dataset.start),
          (this.eventLocation = this.el.dataset.location),
          this.eventTitle && this.eventStart
            ? ((this.dtstart = this.getDateFormat()),
              (this.dtend = this.getEventEndDate()),
              this.dtstart && this.dtend
                ? ((this.makeIcsFile = this.makeIcsFile.bind(this)),
                  this.el.addEventListener("click", this.makeIcsFile))
                : console.error("Event start date or end date missing."))
            : console.error("Event title or start date missing.");
      }
      getDateFormat() {
        let t = new Date(this.eventStart);
        return this.isValidDate(t)
          ? ((t = t.toISOString()),
            (t = t.split("T")[0]),
            (t = t.split("-")),
            (t = t.join("")),
            t)
          : (console.error("Invalid event date found"), "");
      }
      getEventEndDate() {
        const t = new Date(this.eventStart);
        if (!this.isValidDate(t))
          return console.error("Invalid event date found"), "";
        const e = new Date(t);
        return this.isValidDate(e)
          ? (e.setDate(t.getDate() + 1), this.getDateFormat(e))
          : "";
      }
      isValidDate(t) {
        return t instanceof Date && !isNaN(t);
      }
      makeIcsFile(t) {
        t.preventDefault();
        const e =
            "BEGIN:VCALENDAR\nVERSION:2.0\nBEGIN:VEVENT\nSUMMARY:" +
            this.eventTitle +
            "\nDTSTART;VALUE=DATE:" +
            this.dtstart +
            "\nDTEND;VALUE=DATE:" +
            this.dtend +
            "\nLOCATION:" +
            this.eventLocation +
            "\nEND:VEVENT\nEND:VCALENDAR",
          i = document.createElement("a");
        i.setAttribute(
          "href",
          "data:text/plain;charset=utf-8," + encodeURIComponent(e)
        ),
          i.setAttribute("download", "event.ics"),
          (i.style.display = "none"),
          document.body.appendChild(i),
          i.click(),
          document.body.removeChild(i);
      }
    }
    var y =
        void 0 !== window.pmc_common_urls
          ? window.pmc_common_urls.current_theme_uri +
            "/assets/build/svg/defs/sprite.defs.svg"
          : "/assets/build/svg/defs/sprite.defs.svg",
      S = function () {
        var t = window.innerWidth;
        !(function (t) {
          [...document.querySelectorAll(".lrv-js-MobileHeightToggle")].forEach(
            (e) => {
              768 > t && void 0 === e.pmcMobileHeightToggle && new o(e),
                768 <= t &&
                  void 0 !== e.pmcMobileHeightToggle &&
                  e.pmcMobileHeightToggle.destroy();
            }
          );
        })(t),
          [...document.querySelectorAll(".js-Header")].forEach((t) => {
            void 0 === t.pmcHeader && new u(t);
          });
      };
    window.addEventListener("message", function (t) {
      !(function (t) {
        let e = "";
        "string" == typeof t.data &&
          "tbmadm:dfp:skinad" ===
            t.data.substring(0, "tbmadm:dfp:skinad".length) &&
          ((e = t.data.substring("tbmadm:dfp:skinad".length)),
          e && (p()));
        /* let e = "";
        "string" == typeof t.data &&
          "object" == typeof window.pmc.skinAds &&
          "pmcadm:dfp:skinad:parameters" ===
            t.data.substring(0, "pmcadm:dfp:skinad:parameters".length) &&
          ((e = t.data.substring("pmcadm:dfp:skinad:parameters".length)),
          e && (p(), window.pmc.skinAds.refresh_skin_rails())); */
      })(t);
    }),
      window.addEventListener("resize", function () {
        S();
      }),
      window.addEventListener("load", function () {
        !(function () {
          const t = document.querySelector('meta[name="viewport"]'),
            e = window.innerWidth;
          1e3 > e && 767 < e
            ? t.setAttribute(
                "content",
                "width=device-width, initial-scale=0.76, maximum-scale=1.0, user-scalable=0"
              )
            : t.setAttribute(
                "content",
                "width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0"
              );
        })(),
          [...document.querySelectorAll("[data-collapsible]")].forEach(
            (t) => (t.pmcCollapsible = new a.a(t))
          ),
          [...document.querySelectorAll(".cxense-widget-div")].forEach((t) => {
            "undefined" != typeof cX &&
              "" !== t.dataset.widget_id &&
              cX.CCE.run({
                widgetId: t.dataset.widget_id,
                targetElementId: t.id,
              });
          }),
          [...document.querySelectorAll(".lrv-js-EmailCapture")].forEach(
            (t) => (t.pmcEmailCapture = new s(t))
          ),
          [...document.querySelectorAll(".js-ExpandableSearch")].forEach(
            (t) => new l(t)
          ),
          Object(g.a)(),
          S(),
          [...document.querySelectorAll(".js-Flickity")].forEach((t) => {
            let e = {};
            try {
              e = JSON.parse(t.dataset.flickity);
            } catch (t) {
              "undefined" != typeof console && console.log("Invalid JSON");
            }
            const i = !!t.classList.contains("js-Flickity--isContained");
            return new c.a(
              t,
              Object.assign(
                {
                  cellSelector: ".js-Flickity-cell",
                  pageDots: !1,
                  imagesLoaded: !0,
                  groupCells: !0,
                  contain: i,
                  arrowShape: {
                    x0: 10,
                    x1: 60,
                    y1: 50,
                    x2: 65,
                    y2: 45,
                    x3: 20,
                  },
                },
                e
              )
            );
          }),
          [...document.querySelectorAll(".js-SelectNav")].forEach(
            (t) => new d(t)
          ),
          (function () {
            const t = [...document.querySelectorAll("iframe[data-lazy-src]")];
            t.length &&
              t.forEach((t) => {
                new f(t);
              });
          })(),
          //   new v(),
          [...document.querySelectorAll(".js-AddToCalendar")].forEach(
            (t) => (t.pmcAddToCalendar = new m(t))
          );
      }),
      /* window.addEventListener("DOMContentLoaded", function () {
        !(function () {
          const t = [...document.querySelectorAll(".js-MegaMenu")],
            e = [...document.querySelectorAll(".js-MegaMenu-Trigger")];
          let i;
          function s() {
            let t = document.documentElement.classList.contains("is-mega-open");
            const e = [
              ...document
                .querySelector(".js-MegaMenu")
                .querySelectorAll('input, [tabindex="0"]'),
            ];
            t
              ? (document.documentElement.classList.remove("is-mega-open"),
                i.focus())
              : (document.documentElement.classList.add("is-mega-open"),
                e[0] !== document.activeElement &&
                  setTimeout(function () {
                    e[0].focus();
                  }, 100),
                (i = document.activeElement));
          }
          t.forEach((t) => (t.pmcMegaMenu = new n(t))),
            e.forEach((t) => {
              t.addEventListener("click", s);
            }),
            document.addEventListener("keydown", (t) => {
              "Escape" === t.key &&
                document.documentElement.classList.remove("is-mega-open");
            });
        })();
      }), */
      r.init(),
      (function (t) {
        const e = new XMLHttpRequest(),
          i = document.createElement("div");
        e.open("GET", t, !0),
          e.send(),
          (e.onload = function () {
            (i.id = "icon-sprite"),
              (i.innerHTML = e.responseText),
              document.body.insertBefore(i, document.body.childNodes[0]);
          });
      })(y);
  },
]);
