! function(e, t, n, i) {
    "use strict";
    e.api = e.fn.api = function(n) {
        var o, a = e(e.isFunction(this) ? t : this),
            r = a.selector || "",
            s = (new Date).getTime(),
            c = [],
            l = arguments[0],
            u = "string" == typeof l,
            d = [].slice.call(arguments, 1);
        return a.each(function() {
            var a, m, f, g, p, v, h = e.isPlainObject(n) ? e.extend(!0, {}, e.fn.api.settings, n) : e.extend({}, e.fn.api.settings),
                b = h.namespace,
                y = h.metadata,
                x = h.selector,
                w = h.error,
                C = h.className,
                k = "." + b,
                S = "module-" + b,
                T = e(this),
                A = T.closest(x.form),
                R = h.stateContext ? e(h.stateContext) : T,
                P = this,
                E = R[0],
                F = T.data(S);
            v = {
                initialize: function() {
                    u || v.bind.events(), v.instantiate()
                },
                instantiate: function() {
                    v.verbose("Storing instance of module", v), F = v, T.data(S, F)
                },
                destroy: function() {
                    v.verbose("Destroying previous module for", P), T.removeData(S).off(k)
                },
                bind: {
                    events: function() {
                        var e = v.get.event();
                        e ? (v.verbose("Attaching API events to element", e), T.on(e + k, v.event.trigger)) : "now" == h.on && (v.debug("Querying API endpoint immediately"), v.query())
                    }
                },
                decode: {
                    json: function(e) {
                        if (e !== i && "string" == typeof e) try {
                            e = JSON.parse(e)
                        } catch (t) {}
                        return e
                    }
                },
                read: {
                    cachedResponse: function(e) {
                        var n;
                        return t.Storage === i ? void v.error(w.noStorage) : (n = sessionStorage.getItem(e), v.debug("Using cached response", e, n), n = v.decode.json(n), !1)
                    }
                },
                write: {
                    cachedResponse: function(n, o) {
                        return o && "" === o ? void v.debug("Response empty, not caching", o) : t.Storage === i ? void v.error(w.noStorage) : (e.isPlainObject(o) && (o = JSON.stringify(o)), sessionStorage.setItem(n, o), void v.verbose("Storing cached response for url", n, o))
                    }
                },
                query: function() {
                    if (v.is.disabled()) return void v.debug("Element is disabled API request aborted");
                    if (v.is.loading()) {
                        if (!h.interruptRequests) return void v.debug("Cancelling request, previous request is still pending");
                        v.debug("Interrupting previous request"), v.abort()
                    }
                    return h.defaultData && e.extend(!0, h.urlData, v.get.defaultData()), h.serializeForm && (h.data = v.add.formData(h.data)), m = v.get.settings(), m === !1 ? (v.cancelled = !0, void v.error(w.beforeSend)) : (v.cancelled = !1, f = v.get.templatedURL(), f || v.is.mocked() ? (f = v.add.urlData(f), f || v.is.mocked() ? (a = e.extend(!0, {}, h, {
                        type: h.method || h.type,
                        data: g,
                        url: h.base + f,
                        beforeSend: h.beforeXHR,
                        success: function() {},
                        failure: function() {},
                        complete: function() {}
                    }), v.debug("Querying URL", a.url), v.verbose("Using AJAX settings", a), "local" === h.cache && v.read.cachedResponse(f) ? (v.debug("Response returned from local cache"), v.request = v.create.request(), void v.request.resolveWith(E, [v.read.cachedResponse(f)])) : void(h.throttle ? h.throttleFirstRequest || v.timer ? (v.debug("Throttling request", h.throttle), clearTimeout(v.timer), v.timer = setTimeout(function() {
                        v.timer && delete v.timer, v.debug("Sending throttled request", g, a.method), v.send.request()
                    }, h.throttle)) : (v.debug("Sending request", g, a.method), v.send.request(), v.timer = setTimeout(function() {}, h.throttle)) : (v.debug("Sending request", g, a.method), v.send.request()))) : void 0) : void v.error(w.missingURL))
                },
                should: {
                    removeError: function() {
                        return h.hideError === !0 || "auto" === h.hideError && !v.is.form()
                    }
                },
                is: {
                    disabled: function() {
                        return T.filter(x.disabled).length > 0
                    },
                    form: function() {
                        return T.is("form") || R.is("form")
                    },
                    mocked: function() {
                        return h.mockResponse || h.mockResponseAsync
                    },
                    input: function() {
                        return T.is("input")
                    },
                    loading: function() {
                        return v.request && "pending" == v.request.state()
                    },
                    abortedRequest: function(e) {
                        return e && e.readyState !== i && 0 === e.readyState ? (v.verbose("XHR request determined to be aborted"), !0) : (v.verbose("XHR request was not aborted"), !1)
                    },
                    validResponse: function(t) {
                        return "json" !== h.dataType && "jsonp" !== h.dataType || !e.isFunction(h.successTest) ? (v.verbose("Response is not JSON, skipping validation", h.successTest, t), !0) : (v.debug("Checking JSON returned success", h.successTest, t), h.successTest(t) ? (v.debug("Response passed success test", t), !0) : (v.debug("Response failed success test", t), !1))
                    }
                },
                was: {
                    cancelled: function() {
                        return v.cancelled || !1
                    },
                    succesful: function() {
                        return v.request && "resolved" == v.request.state()
                    },
                    failure: function() {
                        return v.request && "rejected" == v.request.state()
                    },
                    complete: function() {
                        return v.request && ("resolved" == v.request.state() || "rejected" == v.request.state())
                    }
                },
                add: {
                    urlData: function(t, n) {
                        var o, a;
                        return t && (o = t.match(h.regExp.required), a = t.match(h.regExp.optional), n = n || h.urlData, o && (v.debug("Looking for required URL variables", o), e.each(o, function(o, a) {
                            var r = -1 !== a.indexOf("$") ? a.substr(2, a.length - 3) : a.substr(1, a.length - 2),
                                s = e.isPlainObject(n) && n[r] !== i ? n[r] : T.data(r) !== i ? T.data(r) : R.data(r) !== i ? R.data(r) : n[r];
                            return s === i ? (v.error(w.requiredParameter, r, t), t = !1, !1) : (v.verbose("Found required variable", r, s), s = h.encodeParameters ? v.get.urlEncodedValue(s) : s, t = t.replace(a, s), void 0)
                        })), a && (v.debug("Looking for optional URL variables", o), e.each(a, function(o, a) {
                            var r = -1 !== a.indexOf("$") ? a.substr(3, a.length - 4) : a.substr(2, a.length - 3),
                                s = e.isPlainObject(n) && n[r] !== i ? n[r] : T.data(r) !== i ? T.data(r) : R.data(r) !== i ? R.data(r) : n[r];
                            s !== i ? (v.verbose("Optional variable Found", r, s), t = t.replace(a, s)) : (v.verbose("Optional variable not found", r), t = -1 !== t.indexOf("/" + a) ? t.replace("/" + a, "") : t.replace(a, ""))
                        }))), t
                    },
                    formData: function(t) {
                        var n, o = e.fn.serializeObject !== i,
                            a = o ? A.serializeObject() : A.serialize();
                        return t = t || h.data, n = e.isPlainObject(t), n ? o ? (v.debug("Extending existing data with form data", t, a), t = e.extend(!0, {}, t, a)) : (v.error(w.missingSerialize), v.debug("Cant extend data. Replacing data with form data", t, a), t = a) : (v.debug("Adding form data", a), t = a), t
                    }
                },
                send: {
                    request: function() {
                        v.set.loading(), v.request = v.create.request(), v.is.mocked() ? v.mockedXHR = v.create.mockedXHR() : v.xhr = v.create.xhr(), h.onRequest.call(E, v.request, v.xhr)
                    }
                },
                event: {
                    trigger: function(e) {
                        v.query(), ("submit" == e.type || "click" == e.type) && e.preventDefault()
                    },
                    xhr: {
                        always: function() {},
                        done: function(t, n, i) {
                            var o = this,
                                a = (new Date).getTime() - p,
                                r = h.loadingDuration - a,
                                s = e.isFunction(h.onResponse) ? h.onResponse.call(o, e.extend(!0, {}, t)) : !1;
                            r = r > 0 ? r : 0, s && (v.debug("Modified API response in onResponse callback", h.onResponse, s, t), t = s), r > 0 && v.debug("Response completed early delaying state change by", r), setTimeout(function() {
                                v.is.validResponse(t) ? v.request.resolveWith(o, [t, i]) : v.request.rejectWith(o, [i, "invalid"])
                            }, r)
                        },
                        fail: function(e, t, n) {
                            var i = this,
                                o = (new Date).getTime() - p,
                                a = h.loadingDuration - o;
                            a = a > 0 ? a : 0, a > 0 && v.debug("Response completed early delaying state change by", a), setTimeout(function() {
                                v.is.abortedRequest(e) ? v.request.rejectWith(i, [e, "aborted", n]) : v.request.rejectWith(i, [e, "error", t, n])
                            }, a)
                        }
                    },
                    request: {
                        done: function(e, t) {
                            v.debug("Successful API Response", e), "local" === h.cache && f && (v.write.cachedResponse(f, e), v.debug("Saving server response locally", v.cache)), h.onSuccess.call(E, e, T, t)
                        },
                        complete: function(e, t) {
                            var n, i;
                            v.was.succesful() ? (i = e, n = t) : (n = e, i = v.get.responseFromXHR(n)), v.remove.loading(), h.onComplete.call(E, i, T, n)
                        },
                        fail: function(e, t, n) {
                            var o = v.get.responseFromXHR(e),
                                r = v.get.errorFromRequest(o, t, n);
                            "aborted" == t ? (v.debug("XHR Aborted (Most likely caused by page navigation or CORS Policy)", t, n), h.onAbort.call(E, t, T, e)) : "invalid" == t ? v.debug("JSON did not pass success test. A server-side error has most likely occurred", o) : "error" == t && e !== i && (v.debug("XHR produced a server error", t, n), 200 != e.status && n !== i && "" !== n && v.error(w.statusMessage + n, a.url), h.onError.call(E, r, T, e)), h.errorDuration && "aborted" !== t && (v.debug("Adding error state"), v.set.error(), v.should.removeError() && setTimeout(v.remove.error, h.errorDuration)), v.debug("API Request failed", r, e), h.onFailure.call(E, o, T, e)
                        }
                    }
                },
                create: {
                    request: function() {
                        return e.Deferred().always(v.event.request.complete).done(v.event.request.done).fail(v.event.request.fail)
                    },
                    mockedXHR: function() {
                        var t, n, i, o = !1,
                            a = !1,
                            r = !1;
                        return i = e.Deferred().always(v.event.xhr.complete).done(v.event.xhr.done).fail(v.event.xhr.fail), h.mockResponse ? (e.isFunction(h.mockResponse) ? (v.debug("Using mocked callback returning response", h.mockResponse), n = h.mockResponse.call(E, h)) : (v.debug("Using specified response", h.mockResponse), n = h.mockResponse), i.resolveWith(E, [n, o, {
                            responseText: n
                        }])) : e.isFunction(h.mockResponseAsync) && (t = function(e) {
                            v.debug("Async callback returned response", e), e ? i.resolveWith(E, [e, o, {
                                responseText: e
                            }]) : i.rejectWith(E, [{
                                responseText: e
                            }, a, r])
                        }, v.debug("Using async mocked response", h.mockResponseAsync), h.mockResponseAsync.call(E, h, t)), i
                    },
                    xhr: function() {
                        var t;
                        return t = e.ajax(a).always(v.event.xhr.always).done(v.event.xhr.done).fail(v.event.xhr.fail), v.verbose("Created server request", t), t
                    }
                },
                set: {
                    error: function() {
                        v.verbose("Adding error state to element", R), R.addClass(C.error)
                    },
                    loading: function() {
                        v.verbose("Adding loading state to element", R), R.addClass(C.loading), p = (new Date).getTime()
                    }
                },
                remove: {
                    error: function() {
                        v.verbose("Removing error state from element", R), R.removeClass(C.error)
                    },
                    loading: function() {
                        v.verbose("Removing loading state from element", R), R.removeClass(C.loading)
                    }
                },
                get: {
                    responseFromXHR: function(t) {
                        return e.isPlainObject(t) ? "json" == h.dataType || "jsonp" == h.dataType ? v.decode.json(t.responseText) : t.responseText : !1
                    },
                    errorFromRequest: function(t, n, o) {
                        return e.isPlainObject(t) && t.error !== i ? t.error : h.error[n] !== i ? h.error[n] : o
                    },
                    request: function() {
                        return v.request || !1
                    },
                    xhr: function() {
                        return v.xhr || !1
                    },
                    settings: function() {
                        var e;
                        return e = h.beforeSend.call(E, h), e && (e.success !== i && (v.debug("Legacy success callback detected", e), v.error(w.legacyParameters, e.success), e.onSuccess = e.success), e.failure !== i && (v.debug("Legacy failure callback detected", e), v.error(w.legacyParameters, e.failure), e.onFailure = e.failure), e.complete !== i && (v.debug("Legacy complete callback detected", e), v.error(w.legacyParameters, e.complete), e.onComplete = e.complete)), e === i && v.error(w.noReturnedValue), e !== i ? e : h
                    },
                    urlEncodedValue: function(e) {
                        var n = t.decodeURIComponent(e),
                            i = t.encodeURIComponent(e),
                            o = n !== e;
                        return o ? (v.debug("URL value is already encoded, avoiding double encoding", e), e) : (v.verbose("Encoding value using encodeURIComponent", e, i), i)
                    },
                    defaultData: function() {
                        var t = {};
                        return e.isWindow(P) || (v.is.input() ? t.value = T.val() : v.is.form() && (t.text = T.text())), t
                    },
                    event: function() {
                        return e.isWindow(P) || "now" == h.on ? (v.debug("API called without element, no events attached"), !1) : "auto" == h.on ? T.is("input") ? P.oninput !== i ? "input" : P.onpropertychange !== i ? "propertychange" : "keyup" : T.is("form") ? "submit" : "click" : h.on
                    },
                    templatedURL: function(e) {
                        if (e = e || T.data(y.action) || h.action || !1, f = T.data(y.url) || h.url || !1) return v.debug("Using specified url", f), f;
                        if (e) {
                            if (v.debug("Looking up url for action", e, h.api), h.api[e] === i && !v.is.mocked()) return void v.error(w.missingAction, h.action, h.api);
                            f = h.api[e]
                        } else v.is.form() && (f = T.attr("action") || R.attr("action") || !1, v.debug("No url or action specified, defaulting to form action", f));
                        return f
                    }
                },
                abort: function() {
                    var e = v.get.xhr();
                    e && "resolved" !== e.state() && (v.debug("Cancelling API request"), e.abort())
                },
                reset: function() {
                    v.remove.error(), v.remove.loading()
                },
                setting: function(t, n) {
                    if (v.debug("Changing setting", t, n), e.isPlainObject(t)) e.extend(!0, h, t);
                    else {
                        if (n === i) return h[t];
                        h[t] = n
                    }
                },
                internal: function(t, n) {
                    if (e.isPlainObject(t)) e.extend(!0, v, t);
                    else {
                        if (n === i) return v[t];
                        v[t] = n
                    }
                },
                debug: function() {
                    h.debug && (h.performance ? v.performance.log(arguments) : (v.debug = Function.prototype.bind.call(console.info, console, h.name + ":"), v.debug.apply(console, arguments)))
                },
                verbose: function() {
                    h.verbose && h.debug && (h.performance ? v.performance.log(arguments) : (v.verbose = Function.prototype.bind.call(console.info, console, h.name + ":"), v.verbose.apply(console, arguments)))
                },
                error: function() {
                    v.error = Function.prototype.bind.call(console.error, console, h.name + ":"), v.error.apply(console, arguments)
                },
                performance: {
                    log: function(e) {
                        var t, n, i;
                        h.performance && (t = (new Date).getTime(), i = s || t, n = t - i, s = t, c.push({
                            Name: e[0],
                            Arguments: [].slice.call(e, 1) || "",
                            "Execution Time": n
                        })), clearTimeout(v.performance.timer), v.performance.timer = setTimeout(v.performance.display, 500)
                    },
                    display: function() {
                        var t = h.name + ":",
                            n = 0;
                        s = !1, clearTimeout(v.performance.timer), e.each(c, function(e, t) {
                            n += t["Execution Time"]
                        }), t += " " + n + "ms", r && (t += " '" + r + "'"), (console.group !== i || console.table !== i) && c.length > 0 && (console.groupCollapsed(t), console.table ? console.table(c) : e.each(c, function(e, t) {
                            console.log(t.Name + ": " + t["Execution Time"] + "ms")
                        }), console.groupEnd()), c = []
                    }
                },
                invoke: function(t, n, a) {
                    var r, s, c, l = F;
                    return n = n || d, a = P || a, "string" == typeof t && l !== i && (t = t.split(/[\. ]/), r = t.length - 1, e.each(t, function(n, o) {
                        var a = n != r ? o + t[n + 1].charAt(0).toUpperCase() + t[n + 1].slice(1) : t;
                        if (e.isPlainObject(l[a]) && n != r) l = l[a];
                        else {
                            if (l[a] !== i) return s = l[a], !1;
                            if (!e.isPlainObject(l[o]) || n == r) return l[o] !== i ? (s = l[o], !1) : (v.error(w.method, t), !1);
                            l = l[o]
                        }
                    })), e.isFunction(s) ? c = s.apply(a, n) : s !== i && (c = s), e.isArray(o) ? o.push(c) : o !== i ? o = [o, c] : c !== i && (o = c), s
                }
            }, u ? (F === i && v.initialize(), v.invoke(l)) : (F !== i && F.invoke("destroy"), v.initialize())
        }), o !== i ? o : this
    }, e.api.settings = {
        name: "API",
        namespace: "api",
        debug: !1,
        verbose: !1,
        performance: !0,
        api: {},
        cache: !0,
        interruptRequests: !0,
        on: "auto",
        stateContext: !1,
        loadingDuration: 0,
        hideError: "auto",
        errorDuration: 2e3,
        encodeParameters: !0,
        action: !1,
        url: !1,
        base: "",
        urlData: {},
        defaultData: !0,
        serializeForm: !1,
        throttle: 0,
        throttleFirstRequest: !0,
        method: "get",
        data: {},
        dataType: "json",
        mockResponse: !1,
        mockResponseAsync: !1,
        beforeSend: function(e) {
            return e
        },
        beforeXHR: function(e) {},
        onRequest: function(e, t) {},
        onResponse: !1,
        onSuccess: function(e, t) {},
        onComplete: function(e, t) {},
        onFailure: function(e, t) {},
        onError: function(e, t) {},
        onAbort: function(e, t) {},
        successTest: !1,
        error: {
            beforeSend: "The before send function has aborted the request",
            error: "There was an error with your request",
            exitConditions: "API Request Aborted. Exit conditions met",
            JSONParse: "JSON could not be parsed during error handling",
            legacyParameters: "You are using legacy API success callback names",
            method: "The method you called is not defined",
            missingAction: "API action used but no url was defined",
            missingSerialize: "jquery-serialize-object is required to add form data to an existing data object",
            missingURL: "No URL specified for api event",
            noReturnedValue: "The beforeSend callback must return a settings object, beforeSend ignored.",
            noStorage: "Caching respopnses locally requires session storage",
            parseError: "There was an error parsing your request",
            requiredParameter: "Missing a required URL parameter: ",
            statusMessage: "Server gave an error: ",
            timeout: "Your request timed out"
        },
        regExp: {
            required: /\{\$*[A-z0-9]+\}/g,
            optional: /\{\/\$*[A-z0-9]+\}/g
        },
        className: {
            loading: "loading",
            error: "error"
        },
        selector: {
            disabled: ".disabled",
            form: "form"
        },
        metadata: {
            action: "action",
            url: "url"
        }
    }
}(jQuery, window, document),
function(e, t, n, i) {
    "use strict";
    e.fn.colorize = function(t) {
        var n = e.isPlainObject(t) ? e.extend(!0, {}, e.fn.colorize.settings, t) : e.extend({}, e.fn.colorize.settings),
            o = arguments || !1;
        return e(this).each(function(t) {
            var a, r, s, c, l, u, d, m, f = e(this),
                g = e("<canvas />")[0],
                p = e("<canvas />")[0],
                v = e("<canvas />")[0],
                h = new Image,
                b = n.colors,
                y = (n.paths, n.namespace),
                x = n.error,
                w = f.data("module-" + y);
            return m = {
                checkPreconditions: function() {
                    return m.debug("Checking pre-conditions"), !e.isPlainObject(b) || e.isEmptyObject(b) ? (m.error(x.undefinedColors), !1) : !0
                },
                async: function(e) {
                    n.async ? setTimeout(e, 0) : e()
                },
                getMetadata: function() {
                    m.debug("Grabbing metadata"), c = f.data("image") || n.image || i, l = f.data("name") || n.name || t, u = n.width || f.width(), d = n.height || f.height(), (0 === u || 0 === d) && m.error(x.undefinedSize)
                },
                initialize: function() {
                    m.debug("Initializing with colors", b), m.checkPreconditions() && m.async(function() {
                        m.getMetadata(), m.canvas.create(), m.draw.image(function() {
                            m.draw.colors(), m.canvas.merge()
                        }), f.data("module-" + y, m)
                    })
                },
                redraw: function() {
                    m.debug("Redrawing image"), m.async(function() {
                        m.canvas.clear(), m.draw.colors(), m.canvas.merge()
                    })
                },
                change: {
                    color: function(e, t) {
                        return m.debug("Changing color", e), b[e] === i ? (m.error(x.missingColor), !1) : (b[e] = t, void m.redraw())
                    }
                },
                canvas: {
                    create: function() {
                        m.debug("Creating canvases"), g.width = u, g.height = d, p.width = u, p.height = d, v.width = u, v.height = d, a = g.getContext("2d"), r = p.getContext("2d"), s = v.getContext("2d"), f.append(g), a = f.children("canvas")[0].getContext("2d")
                    },
                    clear: function(e) {
                        m.debug("Clearing canvas"), s.fillStyle = "#FFFFFF", s.fillRect(0, 0, u, d)
                    },
                    merge: function() {
                        return e.isFunction(a.blendOnto) ? (a.putImageData(r.getImageData(0, 0, u, d), 0, 0), void s.blendOnto(a, "multiply")) : void m.error(x.missingPlugin)
                    }
                },
                draw: {
                    image: function(e) {
                        m.debug("Drawing image"), e = e || function() {}, c ? (h.src = c, h.onload = function() {
                            r.drawImage(h, 0, 0), e()
                        }) : (m.error(x.noImage), e())
                    },
                    colors: function() {
                        m.debug("Drawing color overlays", b), e.each(b, function(e, t) {
                            n.onDraw(s, l, e, t)
                        })
                    }
                },
                debug: function(e, t) {
                    n.debug && (t !== i ? console.info(n.name + ": " + e, t) : console.info(n.name + ": " + e))
                },
                error: function(e) {
                    console.warn(n.name + ": " + e)
                },
                invoke: function(t, o, a) {
                    var r;
                    return a = a || Array.prototype.slice.call(arguments, 2), "string" == typeof t && w !== i && (t = t.split("."), e.each(t, function(t, i) {
                        return e.isPlainObject(w[i]) ? (w = w[i], !0) : e.isFunction(w[i]) ? (r = w[i], !0) : (m.error(n.error.method), !1)
                    })), e.isFunction(r) ? r.apply(o, a) : !1
                }
            }, w !== i && o ? ("invoke" == o[0] && (o = Array.prototype.slice.call(o, 1)), m.invoke(o[0], this, Array.prototype.slice.call(o, 1))) : void m.initialize()
        }), this
    }, e.fn.colorize.settings = {
        name: "Image Colorizer",
        debug: !0,
        namespace: "colorize",
        onDraw: function(e, t, n, i) {},
        async: !0,
        colors: {},
        metadata: {
            image: "image",
            name: "name"
        },
        error: {
            noImage: "No tracing image specified",
            undefinedColors: "No default colors specified.",
            missingColor: "Attempted to change color that does not exist",
            missingPlugin: "Blend onto plug-in must be included",
            undefinedHeight: "The width or height of image canvas could not be automatically determined. Please specify a height."
        }
    }
}(jQuery, window, document),
function(e, t, n, i) {
    "use strict";
    e.fn.embed = function(n) {
        var o, a = e(this),
            r = a.selector || "",
            s = (new Date).getTime(),
            c = [],
            l = arguments[0],
            u = "string" == typeof l,
            d = [].slice.call(arguments, 1);
        return a.each(function() {
            var m, f = e.isPlainObject(n) ? e.extend(!0, {}, e.fn.embed.settings, n) : e.extend({}, e.fn.embed.settings),
                g = f.selector,
                p = f.className,
                v = f.sources,
                h = f.error,
                b = f.metadata,
                y = f.namespace,
                x = f.templates,
                w = "." + y,
                C = "module-" + y,
                k = (e(t), e(this)),
                S = k.find(g.placeholder),
                T = k.find(g.icon),
                A = k.find(g.embed),
                R = this,
                P = k.data(C);
            m = {
                initialize: function() {
                    m.debug("Initializing embed"), m.determine.autoplay(), m.create(), m.bind.events(), m.instantiate()
                },
                instantiate: function() {
                    m.verbose("Storing instance of module", m), P = m, k.data(C, m)
                },
                destroy: function() {
                    m.verbose("Destroying previous instance of embed"), m.reset(), k.removeData(C).off(w)
                },
                refresh: function() {
                    m.verbose("Refreshing selector cache"), S = k.find(g.placeholder), T = k.find(g.icon), A = k.find(g.embed)
                },
                bind: {
                    events: function() {
                        m.has.placeholder() && (m.debug("Adding placeholder events"), k.on("click" + w, g.placeholder, m.createAndShow).on("click" + w, g.icon, m.createAndShow))
                    }
                },
                create: function() {
                    var e = m.get.placeholder();
                    e ? m.createPlaceholder() : m.createAndShow()
                },
                createPlaceholder: function(e) {
                    {
                        var t = m.get.icon(),
                            n = m.get.url();
                        m.generate.embed(n)
                    }
                    e = e || m.get.placeholder(), k.html(x.placeholder(e, t)), m.debug("Creating placeholder for embed", e, t)
                },
                createEmbed: function(t) {
                    m.refresh(), t = t || m.get.url(), A = e("<div/>").addClass(p.embed).html(m.generate.embed(t)).appendTo(k), f.onCreate.call(R, t), m.debug("Creating embed object", A)
                },
                createAndShow: function() {
                    m.createEmbed(), m.show()
                },
                change: function(e, t, n) {
                    m.debug("Changing video to ", e, t, n), k.data(b.source, e).data(b.id, t).data(b.url, n), m.create()
                },
                reset: function() {
                    m.debug("Clearing embed and showing placeholder"), m.remove.active(), m.remove.embed(), m.showPlaceholder(), f.onReset.call(R)
                },
                show: function() {
                    m.debug("Showing embed"), m.set.active(), f.onDisplay.call(R)
                },
                hide: function() {
                    m.debug("Hiding embed"), m.showPlaceholder()
                },
                showPlaceholder: function() {
                    m.debug("Showing placeholder image"), m.remove.active(), f.onPlaceholderDisplay.call(R)
                },
                get: {
                    id: function() {
                        return f.id || k.data(b.id)
                    },
                    placeholder: function() {
                        return f.placeholder || k.data(b.placeholder)
                    },
                    icon: function() {
                        return f.icon ? f.icon : k.data(b.icon) !== i ? k.data(b.icon) : m.determine.icon()
                    },
                    source: function(e) {
                        return f.source ? f.source : k.data(b.source) !== i ? k.data(b.source) : m.determine.source()
                    },
                    type: function() {
                        var e = m.get.source();
                        return v[e] !== i ? v[e].type : !1
                    },
                    url: function() {
                        return f.url ? f.url : k.data(b.url) !== i ? k.data(b.url) : m.determine.url()
                    }
                },
                determine: {
                    autoplay: function() {
                        m.should.autoplay() && (f.autoplay = !0)
                    },
                    source: function(t) {
                        var n = !1;
                        return t = t || m.get.url(), t && e.each(v, function(e, i) {
                            return -1 !== t.search(i.domain) ? (n = e, !1) : void 0
                        }), n
                    },
                    icon: function() {
                        var e = m.get.source();
                        return v[e] !== i ? v[e].icon : !1
                    },
                    url: function() {
                        var e, t = f.id || k.data(b.id),
                            n = f.source || k.data(b.source);
                        return e = v[n] !== i ? v[n].url.replace("{id}", t) : !1, e && k.data(b.url, e), e
                    }
                },
                set: {
                    active: function() {
                        k.addClass(p.active)
                    }
                },
                remove: {
                    active: function() {
                        k.removeClass(p.active)
                    },
                    embed: function() {
                        A.empty()
                    }
                },
                encode: {
                    parameters: function(e) {
                        var t, n = [];
                        for (t in e) n.push(encodeURIComponent(t) + "=" + encodeURIComponent(e[t]));
                        return n.join("&amp;")
                    }
                },
                generate: {
                    embed: function(e) {
                        m.debug("Generating embed html");
                        var t, n, i = m.get.source();
                        return e = m.get.url(e), e ? (n = m.generate.parameters(i), t = x.iframe(e, n)) : m.error(h.noURL, k), t
                    },
                    parameters: function(t, n) {
                        var o = v[t] && v[t].parameters !== i ? v[t].parameters(f) : {};
                        return n = n || f.parameters, n && (o = e.extend({}, o, n)), o = f.onEmbed(o), m.encode.parameters(o)
                    }
                },
                has: {
                    placeholder: function() {
                        return f.placeholder || k.data(b.placeholder)
                    }
                },
                should: {
                    autoplay: function() {
                        return "auto" === f.autoplay ? f.placeholder || k.data(b.placeholder) !== i : f.autoplay
                    }
                },
                is: {
                    video: function() {
                        return "video" == m.get.type()
                    }
                },
                setting: function(t, n) {
                    if (m.debug("Changing setting", t, n), e.isPlainObject(t)) e.extend(!0, f, t);
                    else {
                        if (n === i) return f[t];
                        f[t] = n
                    }
                },
                internal: function(t, n) {
                    if (e.isPlainObject(t)) e.extend(!0, m, t);
                    else {
                        if (n === i) return m[t];
                        m[t] = n
                    }
                },
                debug: function() {
                    f.debug && (f.performance ? m.performance.log(arguments) : (m.debug = Function.prototype.bind.call(console.info, console, f.name + ":"), m.debug.apply(console, arguments)))
                },
                verbose: function() {
                    f.verbose && f.debug && (f.performance ? m.performance.log(arguments) : (m.verbose = Function.prototype.bind.call(console.info, console, f.name + ":"), m.verbose.apply(console, arguments)))
                },
                error: function() {
                    m.error = Function.prototype.bind.call(console.error, console, f.name + ":"), m.error.apply(console, arguments)
                },
                performance: {
                    log: function(e) {
                        var t, n, i;
                        f.performance && (t = (new Date).getTime(), i = s || t, n = t - i, s = t, c.push({
                            Name: e[0],
                            Arguments: [].slice.call(e, 1) || "",
                            Element: R,
                            "Execution Time": n
                        })), clearTimeout(m.performance.timer), m.performance.timer = setTimeout(m.performance.display, 500)
                    },
                    display: function() {
                        var t = f.name + ":",
                            n = 0;
                        s = !1, clearTimeout(m.performance.timer), e.each(c, function(e, t) {
                            n += t["Execution Time"]
                        }), t += " " + n + "ms", r && (t += " '" + r + "'"), a.length > 1 && (t += " (" + a.length + ")"), (console.group !== i || console.table !== i) && c.length > 0 && (console.groupCollapsed(t), console.table ? console.table(c) : e.each(c, function(e, t) {
                            console.log(t.Name + ": " + t["Execution Time"] + "ms")
                        }), console.groupEnd()), c = []
                    }
                },
                invoke: function(t, n, a) {
                    var r, s, c, l = P;
                    return n = n || d, a = R || a, "string" == typeof t && l !== i && (t = t.split(/[\. ]/), r = t.length - 1, e.each(t, function(n, o) {
                        var a = n != r ? o + t[n + 1].charAt(0).toUpperCase() + t[n + 1].slice(1) : t;
                        if (e.isPlainObject(l[a]) && n != r) l = l[a];
                        else {
                            if (l[a] !== i) return s = l[a], !1;
                            if (!e.isPlainObject(l[o]) || n == r) return l[o] !== i ? (s = l[o], !1) : (m.error(h.method, t), !1);
                            l = l[o]
                        }
                    })), e.isFunction(s) ? c = s.apply(a, n) : s !== i && (c = s), e.isArray(o) ? o.push(c) : o !== i ? o = [o, c] : c !== i && (o = c), s
                }
            }, u ? (P === i && m.initialize(), m.invoke(l)) : (P !== i && P.invoke("destroy"), m.initialize())
        }), o !== i ? o : this
    }, e.fn.embed.settings = {
        name: "Embed",
        namespace: "embed",
        debug: !1,
        verbose: !1,
        performance: !0,
        icon: !1,
        source: !1,
        url: !1,
        id: !1,
        autoplay: "auto",
        color: "#444444",
        hd: !0,
        brandedUI: !1,
        parameters: !1,
        onDisplay: function() {},
        onPlaceholderDisplay: function() {},
        onReset: function() {},
        onCreate: function(e) {},
        onEmbed: function(e) {
            return e
        },
        metadata: {
            id: "id",
            icon: "icon",
            placeholder: "placeholder",
            source: "source",
            url: "url"
        },
        error: {
            noURL: "No URL specified",
            method: "The method you called is not defined"
        },
        className: {
            active: "active",
            embed: "embed"
        },
        selector: {
            embed: ".embed",
            placeholder: ".placeholder",
            icon: ".icon"
        },
        sources: {
            youtube: {
                name: "youtube",
                type: "video",
                icon: "video play",
                domain: "youtube.com",
                url: "//www.youtube.com/embed/{id}",
                parameters: function(e) {
                    return {
                        autohide: !e.brandedUI,
                        autoplay: e.autoplay,
                        color: e.colors || i,
                        hq: e.hd,
                        jsapi: e.api,
                        modestbranding: !e.brandedUI
                    }
                }
            },
            vimeo: {
                name: "vimeo",
                type: "video",
                icon: "video play",
                domain: "vimeo.com",
                url: "//player.vimeo.com/video/{id}",
                parameters: function(e) {
                    return {
                        api: e.api,
                        autoplay: e.autoplay,
                        byline: e.brandedUI,
                        color: e.colors || i,
                        portrait: e.brandedUI,
                        title: e.brandedUI
                    }
                }
            }
        },
        templates: {
            iframe: function(e, t) {
                return '<iframe src="' + e + "?" + t + '" width="100%" height="100%" frameborder="0" scrolling="no" webkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe>'
            },
            placeholder: function(e, t) {
                var n = "";
                return t && (n += '<i class="' + t + ' icon"></i>'), e && (n += '<img class="placeholder" src="' + e + '">'), n
            }
        },
        api: !0,
        onPause: function() {},
        onPlay: function() {},
        onStop: function() {}
    }
}(jQuery, window, document),
function(e, t, n, i) {
    "use strict";
    e.fn.form = function(t) {
        var o, a = e(this),
            r = a.selector || "",
            s = (new Date).getTime(),
            c = [],
            l = arguments[0],
            u = arguments[1],
            d = "string" == typeof l,
            m = [].slice.call(arguments, 1);
        return a.each(function() {
            var f, g, p, v, h, b, y, x, w, C, k, S, T, A, R, P, E, F, D = e(this),
                O = this,
                q = [],
                j = !1;
            F = {
                initialize: function() {
                    F.get.settings(), d ? (E === i && F.instantiate(), F.invoke(l)) : (F.verbose("Initializing form validation", D, x), F.bindEvents(), F.set.defaults(), F.instantiate())
                },
                instantiate: function() {
                    F.verbose("Storing instance of module", F), E = F, D.data(R, F)
                },
                destroy: function() {
                    F.verbose("Destroying previous module", E), F.removeEvents(), D.removeData(R)
                },
                refresh: function() {
                    F.verbose("Refreshing selector cache"), f = D.find(k.field), g = D.find(k.group), p = D.find(k.message), v = D.find(k.prompt), h = D.find(k.submit), b = D.find(k.clear), y = D.find(k.reset)
                },
                submit: function() {
                    F.verbose("Submitting form", D), D.submit()
                },
                attachEvents: function(t, n) {
                    n = n || "submit", e(t).on("click" + P, function(e) {
                        F[n](), e.preventDefault()
                    })
                },
                bindEvents: function() {
                    F.verbose("Attaching form events"), D.on("submit" + P, F.validate.form).on("blur" + P, k.field, F.event.field.blur).on("click" + P, k.submit, F.submit).on("click" + P, k.reset, F.reset).on("click" + P, k.clear, F.clear), x.keyboardShortcuts && D.on("keydown" + P, k.field, F.event.field.keydown), f.each(function() {
                        var t = e(this),
                            n = t.prop("type"),
                            i = F.get.changeEvent(n, t);
                        e(this).on(i + P, F.event.field.change)
                    })
                },
                clear: function() {
                    f.each(function() {
                        var t = e(this),
                            n = t.parent(),
                            i = t.closest(g),
                            o = i.find(k.prompt),
                            a = t.data(C.defaultValue) || "",
                            r = n.is(k.uiCheckbox),
                            s = n.is(k.uiDropdown),
                            c = i.hasClass(S.error);
                        c && (F.verbose("Resetting error on field", i), i.removeClass(S.error), o.remove()), s ? (F.verbose("Resetting dropdown value", n, a), n.dropdown("clear")) : r ? t.prop("checked", !1) : (F.verbose("Resetting field value", t, a), t.val(""))
                    })
                },
                reset: function() {
                    f.each(function() {
                        var t = e(this),
                            n = t.parent(),
                            o = t.closest(g),
                            a = o.find(k.prompt),
                            r = t.data(C.defaultValue),
                            s = n.is(k.uiCheckbox),
                            c = n.is(k.uiDropdown),
                            l = o.hasClass(S.error);
                        r !== i && (l && (F.verbose("Resetting error on field", o), o.removeClass(S.error), a.remove()), c ? (F.verbose("Resetting dropdown value", n, r), n.dropdown("restore defaults")) : s ? (F.verbose("Resetting checkbox value", n, r), t.prop("checked", r)) : (F.verbose("Resetting field value", t, r), t.val(r)))
                    })
                },
                is: {
                    valid: function() {
                        var t = !0;
                        return F.verbose("Checking if form is valid"), e.each(w, function(e, n) {
                            F.validate.field(n) || (t = !1)
                        }), t
                    }
                },
                removeEvents: function() {
                    D.off(P), f.off(P), h.off(P), f.off(P)
                },
                event: {
                    field: {
                        keydown: function(t) {
                            var n = e(this),
                                i = t.which,
                                o = {
                                    enter: 13,
                                    escape: 27
                                };
                            i == o.escape && (F.verbose("Escape key pressed blurring field"), n.blur()), !t.ctrlKey && i == o.enter && n.is(k.input) && n.not(k.checkbox).length > 0 && (j || (n.one("keyup" + P, F.event.field.keyup), F.submit(), F.debug("Enter pressed on input submitting form")), j = !0)
                        },
                        keyup: function() {
                            j = !1
                        },
                        blur: function() {
                            var t = e(this),
                                n = t.closest(g),
                                i = F.get.validation(t);
                            n.hasClass(S.error) ? (F.debug("Revalidating field", t, i), F.validate.field(i)) : ("blur" == x.on || "change" == x.on) && F.validate.field(i)
                        },
                        change: function() {
                            var t = e(this),
                                n = t.closest(g);
                            ("change" == x.on || n.hasClass(S.error) && x.revalidate) && (clearTimeout(F.timer), F.timer = setTimeout(function() {
                                F.debug("Revalidating field", t, F.get.validation(t)), F.validate.field(F.get.validation(t))
                            }, x.delay))
                        }
                    }
                },
                get: {
                    changeEvent: function(e, t) {
                        return "checkbox" == e || "radio" == e || "hidden" == e || t.is("select") ? "change" : F.get.inputEvent()
                    },
                    inputEvent: function() {
                        return n.createElement("input").oninput !== i ? "input" : n.createElement("input").onpropertychange !== i ? "propertychange" : "keyup"
                    },
                    settings: function() {
                        if (e.isPlainObject(t)) {
                            var n = Object.keys(t),
                                o = n.length > 0 ? t[n[0]].identifier !== i && t[n[0]].rules !== i : !1;
                            o ? (x = e.extend(!0, {}, e.fn.form.settings, u), w = e.extend({}, e.fn.form.settings.defaults, t), F.error(x.error.oldSyntax, O), F.verbose("Extending settings from legacy parameters", w, x)) : (x = e.extend(!0, {}, e.fn.form.settings, t), w = e.extend({}, e.fn.form.settings.defaults, x.fields), F.verbose("Extending settings", w, x))
                        } else x = e.fn.form.settings, w = e.fn.form.settings.defaults, F.verbose("Using default form validation", w, x);
                        A = x.namespace, C = x.metadata, k = x.selector, S = x.className, T = x.error, R = "module-" + A, P = "." + A, E = D.data(R), F.refresh()
                    },
                    field: function(t) {
                        return F.verbose("Finding field with identifier", t), f.filter("#" + t).length > 0 ? f.filter("#" + t) : f.filter('[name="' + t + '"]').length > 0 ? f.filter('[name="' + t + '"]') : f.filter('[name="' + t + '[]"]').length > 0 ? f.filter('[name="' + t + '[]"]') : f.filter("[data-" + C.validate + '="' + t + '"]').length > 0 ? f.filter("[data-" + C.validate + '="' + t + '"]') : e("<input/>")
                    },
                    fields: function(t) {
                        var n = e();
                        return e.each(t, function(e, t) {
                            n = n.add(F.get.field(t))
                        }), n
                    },
                    validation: function(t) {
                        var n;
                        return w ? (e.each(w, function(e, i) {
                            F.get.field(i.identifier)[0] == t[0] && (n = i)
                        }), n || !1) : !1
                    },
                    value: function(e) {
                        var t, n = [];
                        return n.push(e), t = F.get.values.call(O, n), t[e]
                    },
                    values: function(t) {
                        var n = e.isArray(t) ? F.get.fields(t) : f,
                            i = {};
                        return n.each(function(t, n) {
                            var o = e(n),
                                a = (o.prop("type"), o.prop("name")),
                                r = o.val(),
                                s = o.is(k.checkbox),
                                c = o.is(k.radio),
                                l = -1 !== a.indexOf("[]"),
                                u = s ? o.is(":checked") : !1;
                            a && (l ? (a = a.replace("[]", ""), i[a] || (i[a] = []), i[a].push(s ? u ? !0 : !1 : r)) : c ? u && (i[a] = r) : s ? u ? i[a] = !0 : i[a] = !1 : i[a] = r)
                        }), i
                    }
                },
                has: {
                    field: function(e) {
                        return F.verbose("Checking for existence of a field with identifier", e), "string" != typeof e && F.error(T.identifier, e), f.filter("#" + e).length > 0 ? !0 : f.filter('[name="' + e + '"]').length > 0 ? !0 : f.filter("[data-" + C.validate + '="' + e + '"]').length > 0 ? !0 : !1
                    }
                },
                add: {
                    prompt: function(t, n) {
                        var o = F.get.field(t),
                            a = o.closest(g),
                            r = a.children(k.prompt),
                            s = 0 !== r.length;
                        n = "string" == typeof n ? [n] : n, F.verbose("Adding field error state", t), a.addClass(S.error), x.inline && (s || (r = x.templates.prompt(n), r.appendTo(a)), r.html(n[0]), s ? F.verbose("Inline errors are disabled, no inline error added", t) : x.transition && e.fn.transition !== i && D.transition("is supported") ? (F.verbose("Displaying error with css transition", x.transition), r.transition(x.transition + " in", x.duration)) : (F.verbose("Displaying error with fallback javascript animation"), r.fadeIn(x.duration)))
                    },
                    errors: function(e) {
                        F.debug("Adding form error messages", e), p.html(x.templates.error(e))
                    }
                },
                remove: {
                    prompt: function(t) {
                        var n = F.get.field(t.identifier),
                            o = n.closest(g),
                            a = o.children(k.prompt);
                        o.removeClass(S.error), x.inline && a.is(":visible") && (F.verbose("Removing prompt for field", t), x.transition && e.fn.transition !== i && D.transition("is supported") ? a.transition(x.transition + " out", x.duration, function() {
                            a.remove()
                        }) : a.fadeOut(x.duration, function() {
                            a.remove()
                        }))
                    }
                },
                set: {
                    success: function() {
                        D.removeClass(S.error).addClass(S.success)
                    },
                    defaults: function() {
                        f.each(function() {
                            var t = e(this),
                                n = t.filter(k.checkbox).length > 0,
                                i = n ? t.is(":checked") : t.val();
                            t.data(C.defaultValue, i)
                        })
                    },
                    error: function() {
                        D.removeClass(S.success).addClass(S.error)
                    },
                    value: function(e, t) {
                        var n = {};
                        return n[e] = t, F.set.values.call(O, n)
                    },
                    values: function(t) {
                        e.isEmptyObject(t) || e.each(t, function(t, n) {
                            var i, o = F.get.field(t),
                                a = o.parent(),
                                r = e.isArray(n),
                                s = a.is(k.uiCheckbox),
                                c = a.is(k.uiDropdown),
                                l = o.is(k.radio) && s,
                                u = o.length > 0;
                            u && (r && s ? (F.verbose("Selecting multiple", n, o), a.checkbox("uncheck"), e.each(n, function(e, t) {
                                i = o.filter('[value="' + t + '"]'), a = i.parent(), i.length > 0 && a.checkbox("check")
                            })) : l ? (F.verbose("Selecting radio value", n, o), o.filter('[value="' + n + '"]').parent(k.uiCheckbox).checkbox("check")) : s ? (F.verbose("Setting checkbox value", n, a),
                                a.checkbox(n === !0 ? "check" : "uncheck")) : c ? (F.verbose("Setting dropdown value", n, a), a.dropdown("set selected", n)) : (F.verbose("Setting field value", n, o), o.val(n)))
                        })
                    }
                },
                validate: {
                    form: function(e) {
                        var t = F.get.values();
                        return j ? !1 : (q = [], F.is.valid() ? (F.debug("Form has no validation errors, submitting"), F.set.success(), x.onSuccess.call(O, e, t)) : (F.debug("Form has errors"), F.set.error(), x.inline || F.add.errors(q), D.data("moduleApi") !== i && e.stopImmediatePropagation(), x.onFailure.call(O, q, t)))
                    },
                    field: function(t) {
                        var n = F.get.field(t.identifier),
                            o = !0,
                            a = [];
                        return n.prop("disabled") ? (F.debug("Field is disabled. Skipping", t.identifier), o = !0) : t.optional && "" === e.trim(n.val()) ? (F.debug("Field is optional and empty. Skipping", t.identifier), o = !0) : t.rules !== i && e.each(t.rules, function(e, n) {
                            F.has.field(t.identifier) && !F.validate.rule(t, n) && (F.debug("Field is invalid", t.identifier, n.type), a.push(n.prompt), o = !1)
                        }), o ? (F.remove.prompt(t, a), x.onValid.call(n), !0) : (q = q.concat(a), F.add.prompt(t.identifier, a), x.onInvalid.call(n, a), !1)
                    },
                    rule: function(t, n) {
                        var o, a, r, s = F.get.field(t.identifier),
                            c = n.type,
                            l = s.val(),
                            u = c.match(x.regExp.bracket),
                            d = !0;
                        if (l = l === i || "" === l || null === l ? "" : e.trim(l + ""), u) {
                            if (a = "" + u[1], r = c.replace(u[0], ""), o = x.rules[r], !e.isFunction(o)) return void F.error(T.noRule, r);
                            d = o.call(s, l, a)
                        } else {
                            if (o = x.rules[c], !e.isFunction(o)) return void F.error(T.noRule, c);
                            d = o.call(s, l)
                        }
                        return d
                    }
                },
                setting: function(t, n) {
                    if (e.isPlainObject(t)) e.extend(!0, x, t);
                    else {
                        if (n === i) return x[t];
                        x[t] = n
                    }
                },
                internal: function(t, n) {
                    if (e.isPlainObject(t)) e.extend(!0, F, t);
                    else {
                        if (n === i) return F[t];
                        F[t] = n
                    }
                },
                debug: function() {
                    x.debug && (x.performance ? F.performance.log(arguments) : (F.debug = Function.prototype.bind.call(console.info, console, x.name + ":"), F.debug.apply(console, arguments)))
                },
                verbose: function() {
                    x.verbose && x.debug && (x.performance ? F.performance.log(arguments) : (F.verbose = Function.prototype.bind.call(console.info, console, x.name + ":"), F.verbose.apply(console, arguments)))
                },
                error: function() {
                    F.error = Function.prototype.bind.call(console.error, console, x.name + ":"), F.error.apply(console, arguments)
                },
                performance: {
                    log: function(e) {
                        var t, n, i;
                        x.performance && (t = (new Date).getTime(), i = s || t, n = t - i, s = t, c.push({
                            Name: e[0],
                            Arguments: [].slice.call(e, 1) || "",
                            Element: O,
                            "Execution Time": n
                        })), clearTimeout(F.performance.timer), F.performance.timer = setTimeout(F.performance.display, 500)
                    },
                    display: function() {
                        var t = x.name + ":",
                            n = 0;
                        s = !1, clearTimeout(F.performance.timer), e.each(c, function(e, t) {
                            n += t["Execution Time"]
                        }), t += " " + n + "ms", r && (t += " '" + r + "'"), a.length > 1 && (t += " (" + a.length + ")"), (console.group !== i || console.table !== i) && c.length > 0 && (console.groupCollapsed(t), console.table ? console.table(c) : e.each(c, function(e, t) {
                            console.log(t.Name + ": " + t["Execution Time"] + "ms")
                        }), console.groupEnd()), c = []
                    }
                },
                invoke: function(t, n, a) {
                    var r, s, c, l = E;
                    return n = n || m, a = O || a, "string" == typeof t && l !== i && (t = t.split(/[\. ]/), r = t.length - 1, e.each(t, function(n, o) {
                        var a = n != r ? o + t[n + 1].charAt(0).toUpperCase() + t[n + 1].slice(1) : t;
                        if (e.isPlainObject(l[a]) && n != r) l = l[a];
                        else {
                            if (l[a] !== i) return s = l[a], !1;
                            if (!e.isPlainObject(l[o]) || n == r) return l[o] !== i ? (s = l[o], !1) : !1;
                            l = l[o]
                        }
                    })), e.isFunction(s) ? c = s.apply(a, n) : s !== i && (c = s), e.isArray(o) ? o.push(c) : o !== i ? o = [o, c] : c !== i && (o = c), s
                }
            }, F.initialize()
        }), o !== i ? o : this
    }, e.fn.form.settings = {
        name: "Form",
        namespace: "form",
        debug: !1,
        verbose: !1,
        performance: !0,
        fields: !1,
        keyboardShortcuts: !0,
        on: "submit",
        inline: !1,
        delay: 200,
        revalidate: !0,
        transition: "scale",
        duration: 200,
        onValid: function() {},
        onInvalid: function() {},
        onSuccess: function() {
            return !0
        },
        onFailure: function() {
            return !1
        },
        metadata: {
            defaultValue: "default",
            validate: "validate"
        },
        regExp: {
            bracket: /\[(.*)\]/i,
            decimal: /^\-?\d*(\.\d+)?$/,
            email: "[a-z0-9!#$%&'*+/=?^_`{|}~-]+(?:\\.[a-z0-9!#$%&'*+/=?^_`{|}~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?",
            escape: /[\-\[\]\/\{\}\(\)\*\+\?\.\\\^\$\|]/g,
            flags: /^\/(.*)\/(.*)?/,
            integer: /^\-?\d+$/,
            number: /^\-?\d*(\.\d+)?$/,
            url: /(https?:\/\/(?:www\.|(?!www))[^\s\.]+\.[^\s]{2,}|www\.[^\s]+\.[^\s]{2,})/i
        },
        selector: {
            checkbox: 'input[type="checkbox"], input[type="radio"]',
            clear: ".clear",
            field: "input, textarea, select",
            group: ".field",
            input: "input",
            message: ".error.message",
            prompt: ".prompt.label",
            radio: 'input[type="radio"]',
            reset: '.reset:not([type="reset"])',
            submit: '.submit:not([type="submit"])',
            uiCheckbox: ".ui.checkbox",
            uiDropdown: ".ui.dropdown"
        },
        className: {
            error: "error",
            label: "ui prompt label",
            pressed: "down",
            success: "success"
        },
        error: {
            identifier: "You must specify a string identifier for each field",
            method: "The method you called is not defined.",
            noRule: "There is no rule matching the one you specified",
            oldSyntax: "Starting in 2.0 forms now only take a single settings object. Validation settings converted to new syntax automatically."
        },
        templates: {
            error: function(t) {
                var n = '<ul class="list">';
                return e.each(t, function(e, t) {
                    n += "<li>" + t + "</li>"
                }), n += "</ul>", e(n)
            },
            prompt: function(t) {
                return e("<div/>").addClass("ui basic red pointing prompt label").html(t[0])
            }
        },
        rules: {
            empty: function(t) {
                return !(t === i || "" === t || e.isArray(t) && 0 === t.length)
            },
            checked: function() {
                return e(this).filter(":checked").length > 0
            },
            email: function(t) {
                var n = new RegExp(e.fn.form.settings.regExp.email, "i");
                return n.test(t)
            },
            url: function(t) {
                return e.fn.form.settings.regExp.url.test(t)
            },
            regExp: function(t, n) {
                var i, o = n.match(e.fn.form.settings.regExp.flags);
                return o && (n = o.length >= 2 ? o[1] : n, i = o.length >= 3 ? o[2] : ""), t.match(new RegExp(n, i))
            },
            integer: function(t, n) {
                var o, a, r, s = e.fn.form.settings.regExp.integer;
                return n === i || "" === n || ".." === n || (-1 == n.indexOf("..") ? s.test(n) && (o = a = n - 0) : (r = n.split("..", 2), s.test(r[0]) && (o = r[0] - 0), s.test(r[1]) && (a = r[1] - 0))), s.test(t) && (o === i || t >= o) && (a === i || a >= t)
            },
            decimal: function(t) {
                return e.fn.form.settings.regExp.decimal.test(t)
            },
            number: function(t) {
                return e.fn.form.settings.regExp.number.test(t)
            },
            is: function(e, t) {
                return t = "string" == typeof t ? t.toLowerCase() : t, e = "string" == typeof e ? e.toLowerCase() : e, e == t
            },
            isExactly: function(e, t) {
                return e == t
            },
            not: function(e, t) {
                return e = "string" == typeof e ? e.toLowerCase() : e, t = "string" == typeof t ? t.toLowerCase() : t, e != t
            },
            notExactly: function(e, t) {
                return e != t
            },
            contains: function(t, n) {
                return n = n.replace(e.fn.form.settings.regExp.escape, "\\$&"), -1 !== t.search(new RegExp(n, "i"))
            },
            containsExactly: function(t, n) {
                return n = n.replace(e.fn.form.settings.regExp.escape, "\\$&"), -1 !== t.search(new RegExp(n))
            },
            doesntContain: function(t, n) {
                return n = n.replace(e.fn.form.settings.regExp.escape, "\\$&"), -1 === t.search(new RegExp(n, "i"))
            },
            doesntContainExactly: function(t, n) {
                return n = n.replace(e.fn.form.settings.regExp.escape, "\\$&"), -1 === t.search(new RegExp(n))
            },
            minLength: function(e, t) {
                return e !== i ? e.length >= t : !1
            },
            length: function(e, t) {
                return e !== i ? e.length >= t : !1
            },
            exactLength: function(e, t) {
                return e !== i ? e.length == t : !1
            },
            maxLength: function(e, t) {
                return e !== i ? e.length <= t : !1
            },
            match: function(t, n) {
                {
                    var o;
                    e(this)
                }
                return e('[data-validate="' + n + '"]').length > 0 ? o = e('[data-validate="' + n + '"]').val() : e("#" + n).length > 0 ? o = e("#" + n).val() : e('[name="' + n + '"]').length > 0 ? o = e('[name="' + n + '"]').val() : e('[name="' + n + '[]"]').length > 0 && (o = e('[name="' + n + '[]"]')), o !== i ? t.toString() == o.toString() : !1
            },
            creditCard: function(t, n) {
                var i, o, a = {
                        visa: {
                            pattern: /^4/,
                            length: [16]
                        },
                        amex: {
                            pattern: /^3[47]/,
                            length: [15]
                        },
                        mastercard: {
                            pattern: /^5[1-5]/,
                            length: [16]
                        },
                        discover: {
                            pattern: /^(6011|622(12[6-9]|1[3-9][0-9]|[2-8][0-9]{2}|9[0-1][0-9]|92[0-5]|64[4-9])|65)/,
                            length: [16]
                        },
                        unionPay: {
                            pattern: /^(62|88)/,
                            length: [16, 17, 18, 19]
                        },
                        jcb: {
                            pattern: /^35(2[89]|[3-8][0-9])/,
                            length: [16]
                        },
                        maestro: {
                            pattern: /^(5018|5020|5038|6304|6759|676[1-3])/,
                            length: [12, 13, 14, 15, 16, 17, 18, 19]
                        },
                        dinersClub: {
                            pattern: /^(30[0-5]|^36)/,
                            length: [14]
                        },
                        laser: {
                            pattern: /^(6304|670[69]|6771)/,
                            length: [16, 17, 18, 19]
                        },
                        visaElectron: {
                            pattern: /^(4026|417500|4508|4844|491(3|7))/,
                            length: [16]
                        }
                    },
                    r = {},
                    s = !1,
                    c = "string" == typeof n ? n.split(",") : !1;
                if ("string" == typeof t && 0 !== t.length) {
                    if (c && (e.each(c, function(n, i) {
                            o = a[i], o && (r = {
                                length: -1 !== e.inArray(t.length, o.length),
                                pattern: -1 !== t.search(o.pattern)
                            }, r.length && r.pattern && (s = !0))
                        }), !s)) return !1;
                    if (i = {
                            number: -1 !== e.inArray(t.length, a.unionPay.length),
                            pattern: -1 !== t.search(a.unionPay.pattern)
                        }, i.number && i.pattern) return !0;
                    for (var l = t.length, u = 0, d = [
                            [0, 1, 2, 3, 4, 5, 6, 7, 8, 9],
                            [0, 2, 4, 6, 8, 1, 3, 5, 7, 9]
                        ], m = 0; l--;) m += d[u][parseInt(t.charAt(l), 10)], u ^= 1;
                    return m % 10 === 0 && m > 0
                }
            },
            different: function(t, n) {
                {
                    var o;
                    e(this)
                }
                return e('[data-validate="' + n + '"]').length > 0 ? o = e('[data-validate="' + n + '"]').val() : e("#" + n).length > 0 ? o = e("#" + n).val() : e('[name="' + n + '"]').length > 0 ? o = e('[name="' + n + '"]').val() : e('[name="' + n + '[]"]').length > 0 && (o = e('[name="' + n + '[]"]')), o !== i ? t.toString() !== o.toString() : !1
            },
            exactCount: function(e, t) {
                return 0 == t ? "" === e : 1 == t ? "" !== e && -1 === e.search(",") : e.split(",").length == t
            },
            minCount: function(e, t) {
                return 0 == t ? !0 : 1 == t ? "" !== e : e.split(",").length >= t
            },
            maxCount: function(e, t) {
                return 0 == t ? !1 : 1 == t ? -1 === e.search(",") : e.split(",").length <= t
            }
        }
    }
}(jQuery, window, document),
function(e, t, n, i) {
    "use strict";
    e.fn.state = function(t) {
        var o, a = e(this),
            r = a.selector || "",
            s = ("ontouchstart" in n.documentElement, (new Date).getTime()),
            c = [],
            l = arguments[0],
            u = "string" == typeof l,
            d = [].slice.call(arguments, 1);
        return a.each(function() {
            var n, m = e.isPlainObject(t) ? e.extend(!0, {}, e.fn.state.settings, t) : e.extend({}, e.fn.state.settings),
                f = m.error,
                g = m.metadata,
                p = m.className,
                v = m.namespace,
                h = m.states,
                b = m.text,
                y = "." + v,
                x = v + "-module",
                w = e(this),
                C = this,
                k = w.data(x);
            n = {
                initialize: function() {
                    n.verbose("Initializing module"), m.automatic && n.add.defaults(), m.context && "" !== r ? e(m.context).on(r, "mouseenter" + y, n.change.text).on(r, "mouseleave" + y, n.reset.text).on(r, "click" + y, n.toggle.state) : w.on("mouseenter" + y, n.change.text).on("mouseleave" + y, n.reset.text).on("click" + y, n.toggle.state), n.instantiate()
                },
                instantiate: function() {
                    n.verbose("Storing instance of module", n), k = n, w.data(x, n)
                },
                destroy: function() {
                    n.verbose("Destroying previous module", k), w.off(y).removeData(x)
                },
                refresh: function() {
                    n.verbose("Refreshing selector cache"), w = e(C)
                },
                add: {
                    defaults: function() {
                        var o = t && e.isPlainObject(t.states) ? t.states : {};
                        e.each(m.defaults, function(t, a) {
                            n.is[t] !== i && n.is[t]() && (n.verbose("Adding default states", t, C), e.extend(m.states, a, o))
                        })
                    }
                },
                is: {
                    active: function() {
                        return w.hasClass(p.active)
                    },
                    loading: function() {
                        return w.hasClass(p.loading)
                    },
                    inactive: function() {
                        return !w.hasClass(p.active)
                    },
                    state: function(e) {
                        return p[e] === i ? !1 : w.hasClass(p[e])
                    },
                    enabled: function() {
                        return !w.is(m.filter.active)
                    },
                    disabled: function() {
                        return w.is(m.filter.active)
                    },
                    textEnabled: function() {
                        return !w.is(m.filter.text)
                    },
                    button: function() {
                        return w.is(".button:not(a, .submit)")
                    },
                    input: function() {
                        return w.is("input")
                    },
                    progress: function() {
                        return w.is(".ui.progress")
                    }
                },
                allow: function(e) {
                    n.debug("Now allowing state", e), h[e] = !0
                },
                disallow: function(e) {
                    n.debug("No longer allowing", e), h[e] = !1
                },
                allows: function(e) {
                    return h[e] || !1
                },
                enable: function() {
                    w.removeClass(p.disabled)
                },
                disable: function() {
                    w.addClass(p.disabled)
                },
                setState: function(e) {
                    n.allows(e) && w.addClass(p[e])
                },
                removeState: function(e) {
                    n.allows(e) && w.removeClass(p[e])
                },
                toggle: {
                    state: function() {
                        var t, o;
                        if (n.allows("active") && n.is.enabled()) {
                            if (n.refresh(), e.fn.api !== i)
                                if (t = w.api("get request"), o = w.api("was cancelled")) n.debug("API Request cancelled by beforesend"), m.activateTest = function() {
                                    return !1
                                }, m.deactivateTest = function() {
                                    return !1
                                };
                                else if (t) return void n.listenTo(t);
                            n.change.state()
                        }
                    }
                },
                listenTo: function(t) {
                    n.debug("API request detected, waiting for state signal", t), t && (b.loading && n.update.text(b.loading), e.when(t).then(function() {
                        "resolved" == t.state() ? (n.debug("API request succeeded"), m.activateTest = function() {
                            return !0
                        }, m.deactivateTest = function() {
                            return !0
                        }) : (n.debug("API request failed"), m.activateTest = function() {
                            return !1
                        }, m.deactivateTest = function() {
                            return !1
                        }), n.change.state()
                    }))
                },
                change: {
                    state: function() {
                        n.debug("Determining state change direction"), n.is.inactive() ? n.activate() : n.deactivate(), m.sync && n.sync(), m.onChange.call(C)
                    },
                    text: function() {
                        n.is.textEnabled() && (n.is.disabled() ? (n.verbose("Changing text to disabled text", b.hover), n.update.text(b.disabled)) : n.is.active() ? b.hover ? (n.verbose("Changing text to hover text", b.hover), n.update.text(b.hover)) : b.deactivate && (n.verbose("Changing text to deactivating text", b.deactivate), n.update.text(b.deactivate)) : b.hover ? (n.verbose("Changing text to hover text", b.hover), n.update.text(b.hover)) : b.activate && (n.verbose("Changing text to activating text", b.activate), n.update.text(b.activate)))
                    }
                },
                activate: function() {
                    m.activateTest.call(C) && (n.debug("Setting state to active"), w.addClass(p.active), n.update.text(b.active), m.onActivate.call(C))
                },
                deactivate: function() {
                    m.deactivateTest.call(C) && (n.debug("Setting state to inactive"), w.removeClass(p.active), n.update.text(b.inactive), m.onDeactivate.call(C))
                },
                sync: function() {
                    n.verbose("Syncing other buttons to current state"), a.not(w).state(n.is.active() ? "activate" : "deactivate")
                },
                get: {
                    text: function() {
                        return m.selector.text ? w.find(m.selector.text).text() : w.html()
                    },
                    textFor: function(e) {
                        return b[e] || !1
                    }
                },
                flash: {
                    text: function(e, t, i) {
                        var o = n.get.text();
                        n.debug("Flashing text message", e, t), e = e || m.text.flash, t = t || m.flashDuration, i = i || function() {}, n.update.text(e), setTimeout(function() {
                            n.update.text(o), i.call(C)
                        }, t)
                    }
                },
                reset: {
                    text: function() {
                        var e = b.active || w.data(g.storedText),
                            t = b.inactive || w.data(g.storedText);
                        n.is.textEnabled() && (n.is.active() && e ? (n.verbose("Resetting active text", e), n.update.text(e)) : t && (n.verbose("Resetting inactive text", e), n.update.text(t)))
                    }
                },
                update: {
                    text: function(e) {
                        var t = n.get.text();
                        e && e !== t ? (n.debug("Updating text", e), m.selector.text ? w.data(g.storedText, e).find(m.selector.text).text(e) : w.data(g.storedText, e).html(e)) : n.debug("Text is already set, ignoring update", e)
                    }
                },
                setting: function(t, o) {
                    if (n.debug("Changing setting", t, o), e.isPlainObject(t)) e.extend(!0, m, t);
                    else {
                        if (o === i) return m[t];
                        m[t] = o
                    }
                },
                internal: function(t, o) {
                    if (e.isPlainObject(t)) e.extend(!0, n, t);
                    else {
                        if (o === i) return n[t];
                        n[t] = o
                    }
                },
                debug: function() {
                    m.debug && (m.performance ? n.performance.log(arguments) : (n.debug = Function.prototype.bind.call(console.info, console, m.name + ":"), n.debug.apply(console, arguments)))
                },
                verbose: function() {
                    m.verbose && m.debug && (m.performance ? n.performance.log(arguments) : (n.verbose = Function.prototype.bind.call(console.info, console, m.name + ":"), n.verbose.apply(console, arguments)))
                },
                error: function() {
                    n.error = Function.prototype.bind.call(console.error, console, m.name + ":"), n.error.apply(console, arguments)
                },
                performance: {
                    log: function(e) {
                        var t, i, o;
                        m.performance && (t = (new Date).getTime(), o = s || t, i = t - o, s = t, c.push({
                            Name: e[0],
                            Arguments: [].slice.call(e, 1) || "",
                            Element: C,
                            "Execution Time": i
                        })), clearTimeout(n.performance.timer), n.performance.timer = setTimeout(n.performance.display, 500)
                    },
                    display: function() {
                        var t = m.name + ":",
                            o = 0;
                        s = !1, clearTimeout(n.performance.timer), e.each(c, function(e, t) {
                            o += t["Execution Time"]
                        }), t += " " + o + "ms", r && (t += " '" + r + "'"), (console.group !== i || console.table !== i) && c.length > 0 && (console.groupCollapsed(t), console.table ? console.table(c) : e.each(c, function(e, t) {
                            console.log(t.Name + ": " + t["Execution Time"] + "ms")
                        }), console.groupEnd()), c = []
                    }
                },
                invoke: function(t, a, r) {
                    var s, c, l, u = k;
                    return a = a || d, r = C || r, "string" == typeof t && u !== i && (t = t.split(/[\. ]/), s = t.length - 1, e.each(t, function(o, a) {
                        var r = o != s ? a + t[o + 1].charAt(0).toUpperCase() + t[o + 1].slice(1) : t;
                        if (e.isPlainObject(u[r]) && o != s) u = u[r];
                        else {
                            if (u[r] !== i) return c = u[r], !1;
                            if (!e.isPlainObject(u[a]) || o == s) return u[a] !== i ? (c = u[a], !1) : (n.error(f.method, t), !1);
                            u = u[a]
                        }
                    })), e.isFunction(c) ? l = c.apply(r, a) : c !== i && (l = c), e.isArray(o) ? o.push(l) : o !== i ? o = [o, l] : l !== i && (o = l), c
                }
            }, u ? (k === i && n.initialize(), n.invoke(l)) : (k !== i && k.invoke("destroy"), n.initialize())
        }), o !== i ? o : this
    }, e.fn.state.settings = {
        name: "State",
        debug: !1,
        verbose: !1,
        namespace: "state",
        performance: !0,
        onActivate: function() {},
        onDeactivate: function() {},
        onChange: function() {},
        activateTest: function() {
            return !0
        },
        deactivateTest: function() {
            return !0
        },
        automatic: !0,
        sync: !1,
        flashDuration: 1e3,
        filter: {
            text: ".loading, .disabled",
            active: ".disabled"
        },
        context: !1,
        error: {
            beforeSend: "The before send function has cancelled state change",
            method: "The method you called is not defined."
        },
        metadata: {
            promise: "promise",
            storedText: "stored-text"
        },
        className: {
            active: "active",
            disabled: "disabled",
            error: "error",
            loading: "loading",
            success: "success",
            warning: "warning"
        },
        selector: {
            text: !1
        },
        defaults: {
            input: {
                disabled: !0,
                loading: !0,
                active: !0
            },
            button: {
                disabled: !0,
                loading: !0,
                active: !0
            },
            progress: {
                active: !0,
                success: !0,
                warning: !0,
                error: !0
            }
        },
        states: {
            active: !0,
            disabled: !0,
            error: !0,
            loading: !0,
            success: !0,
            warning: !0
        },
        text: {
            disabled: !1,
            flash: !1,
            hover: !1,
            active: !1,
            inactive: !1,
            activate: !1,
            deactivate: !1
        }
    }
}(jQuery, window, document),
function(e, t, n, i) {
    "use strict";
    e.fn.visibility = function(o) {
        var a, r = e(this),
            s = r.selector || "",
            c = (new Date).getTime(),
            l = [],
            u = arguments[0],
            d = "string" == typeof u,
            m = [].slice.call(arguments, 1);
        return r.each(function() {
            var r, f, g, p = e.isPlainObject(o) ? e.extend(!0, {}, e.fn.visibility.settings, o) : e.extend({}, e.fn.visibility.settings),
                v = p.className,
                h = p.namespace,
                b = p.error,
                y = p.metadata,
                x = "." + h,
                w = "module-" + h,
                C = e(t),
                k = e(this),
                S = e(p.context),
                T = (k.selector || "", k.data(w)),
                A = t.requestAnimationFrame || t.mozRequestAnimationFrame || t.webkitRequestAnimationFrame || t.msRequestAnimationFrame || function(e) {
                    setTimeout(e, 0)
                },
                R = this,
                P = !1;
            g = {
                initialize: function() {
                    g.debug("Initializing", p), g.setup.cache(), g.should.trackChanges() && ("image" == p.type && g.setup.image(), "fixed" == p.type && g.setup.fixed(), p.observeChanges && g.observeChanges(), g.bind.events()), g.save.position(), g.is.visible() || g.error(b.visible, k), p.initialCheck && g.checkVisibility(), g.instantiate()
                },
                instantiate: function() {
                    g.debug("Storing instance", g), k.data(w, g), T = g
                },
                destroy: function() {
                    g.verbose("Destroying previous module"), f && f.disconnect(), C.off("load" + x, g.event.load).off("resize" + x, g.event.resize), S.off("scrollchange" + x, g.event.scrollchange), k.off(x).removeData(w)
                },
                observeChanges: function() {
                    "MutationObserver" in t && (f = new MutationObserver(function(e) {
                        g.verbose("DOM tree modified, updating visibility calculations"), g.timer = setTimeout(function() {
                            g.verbose("DOM tree modified, updating sticky menu"), g.refresh()
                        }, 100)
                    }), f.observe(R, {
                        childList: !0,
                        subtree: !0
                    }), g.debug("Setting up mutation observer", f))
                },
                bind: {
                    events: function() {
                        g.verbose("Binding visibility events to scroll and resize"), p.refreshOnLoad && C.on("load" + x, g.event.load), C.on("resize" + x, g.event.resize), S.off("scroll" + x).on("scroll" + x, g.event.scroll).on("scrollchange" + x, g.event.scrollchange)
                    }
                },
                event: {
                    resize: function() {
                        g.debug("Window resized"), p.refreshOnResize && A(g.refresh)
                    },
                    load: function() {
                        g.debug("Page finished loading"), A(g.refresh)
                    },
                    scroll: function() {
                        p.throttle ? (clearTimeout(g.timer), g.timer = setTimeout(function() {
                            S.triggerHandler("scrollchange" + x, [S.scrollTop()])
                        }, p.throttle)) : A(function() {
                            S.triggerHandler("scrollchange" + x, [S.scrollTop()])
                        })
                    },
                    scrollchange: function(e, t) {
                        g.checkVisibility(t)
                    }
                },
                precache: function(t, i) {
                    t instanceof Array || (t = [t]);
                    for (var o = t.length, a = 0, r = [], s = n.createElement("img"), c = function() {
                            a++, a >= t.length && e.isFunction(i) && i()
                        }; o--;) s = n.createElement("img"), s.onload = c, s.onerror = c, s.src = t[o], r.push(s)
                },
                enableCallbacks: function() {
                    g.debug("Allowing callbacks to occur"), P = !1
                },
                disableCallbacks: function() {
                    g.debug("Disabling all callbacks temporarily"), P = !0
                },
                should: {
                    trackChanges: function() {
                        return d ? (g.debug("One time query, no need to bind events"), !1) : (g.debug("Callbacks being attached"), !0)
                    }
                },
                setup: {
                    cache: function() {
                        g.cache = {
                            occurred: {},
                            screen: {},
                            element: {}
                        }
                    },
                    image: function() {
                        var e = k.data(y.src);
                        e && (g.verbose("Lazy loading image", e), p.once = !0, p.observeChanges = !1, p.onOnScreen = function() {
                            g.debug("Image on screen", R), g.precache(e, function() {
                                g.set.image(e)
                            })
                        })
                    },
                    fixed: function() {
                        g.debug("Setting up fixed"), p.once = !1, p.observeChanges = !1, p.initialCheck = !0, p.refreshOnLoad = !0, o.transition || (p.transition = !1), g.create.placeholder(), g.debug("Added placeholder", r), p.onTopPassed = function() {
                            g.debug("Element passed, adding fixed position", k), g.show.placeholder(), g.set.fixed(), p.transition && e.fn.transition !== i && k.transition(p.transition, p.duration)
                        }, p.onTopPassedReverse = function() {
                            g.debug("Element returned to position, removing fixed", k), g.hide.placeholder(), g.remove.fixed()
                        }
                    }
                },
                create: {
                    placeholder: function() {
                        g.verbose("Creating fixed position placeholder"), r = k.clone(!1).css("display", "none").addClass(v.placeholder).insertAfter(k)
                    }
                },
                show: {
                    placeholder: function() {
                        g.verbose("Showing placeholder"), r.css("display", "block").css("visibility", "hidden")
                    }
                },
                hide: {
                    placeholder: function() {
                        g.verbose("Hiding placeholder"), r.css("display", "none").css("visibility", "")
                    }
                },
                set: {
                    fixed: function() {
                        g.verbose("Setting element to fixed position"), k.addClass(v.fixed).css({
                            position: "fixed",
                            top: p.offset + "px",
                            left: "auto",
                            zIndex: "1"
                        })
                    },
                    image: function(t) {
                        k.attr("src", t), p.transition ? e.fn.transition !== i ? k.transition(p.transition, p.duration) : k.fadeIn(p.duration) : k.show()
                    }
                },
                is: {
                    onScreen: function() {
                        var e = g.get.elementCalculations();
                        return e.onScreen
                    },
                    offScreen: function() {
                        var e = g.get.elementCalculations();
                        return e.offScreen
                    },
                    visible: function() {
                        return g.cache && g.cache.element ? !(0 === g.cache.element.width && 0 === g.cache.element.offset.top) : !1
                    }
                },
                refresh: function() {
                    g.debug("Refreshing constants (width/height)"), "fixed" == p.type && (g.remove.fixed(), g.remove.occurred()), g.reset(), g.save.position(), p.checkOnRefresh && g.checkVisibility(), p.onRefresh.call(R)
                },
                reset: function() {
                    g.verbose("Reseting all cached values"), e.isPlainObject(g.cache) && (g.cache.screen = {}, g.cache.element = {})
                },
                checkVisibility: function(e) {
                    g.verbose("Checking visibility of element", g.cache.element), !P && g.is.visible() && (g.save.scroll(e), g.save.calculations(), g.passed(), g.passingReverse(), g.topVisibleReverse(), g.bottomVisibleReverse(), g.topPassedReverse(), g.bottomPassedReverse(), g.onScreen(), g.offScreen(), g.passing(), g.topVisible(), g.bottomVisible(), g.topPassed(), g.bottomPassed(), p.onUpdate && p.onUpdate.call(R, g.get.elementCalculations()))
                },
                passed: function(t, n) {
                    var o = g.get.elementCalculations();
                    if (t && n) p.onPassed[t] = n;
                    else {
                        if (t !== i) return g.get.pixelsPassed(t) > o.pixelsPassed;
                        o.passing && e.each(p.onPassed, function(e, t) {
                            o.bottomVisible || o.pixelsPassed > g.get.pixelsPassed(e) ? g.execute(t, e) : p.once || g.remove.occurred(t)
                        })
                    }
                },
                onScreen: function(e) {
                    var t = g.get.elementCalculations(),
                        n = e || p.onOnScreen,
                        o = "onScreen";
                    return e && (g.debug("Adding callback for onScreen", e), p.onOnScreen = e), t.onScreen ? g.execute(n, o) : p.once || g.remove.occurred(o), e !== i ? t.onOnScreen : void 0
                },
                offScreen: function(e) {
                    var t = g.get.elementCalculations(),
                        n = e || p.onOffScreen,
                        o = "offScreen";
                    return e && (g.debug("Adding callback for offScreen", e), p.onOffScreen = e), t.offScreen ? g.execute(n, o) : p.once || g.remove.occurred(o), e !== i ? t.onOffScreen : void 0
                },
                passing: function(e) {
                    var t = g.get.elementCalculations(),
                        n = e || p.onPassing,
                        o = "passing";
                    return e && (g.debug("Adding callback for passing", e), p.onPassing = e), t.passing ? g.execute(n, o) : p.once || g.remove.occurred(o), e !== i ? t.passing : void 0
                },
                topVisible: function(e) {
                    var t = g.get.elementCalculations(),
                        n = e || p.onTopVisible,
                        o = "topVisible";
                    return e && (g.debug("Adding callback for top visible", e), p.onTopVisible = e), t.topVisible ? g.execute(n, o) : p.once || g.remove.occurred(o), e === i ? t.topVisible : void 0
                },
                bottomVisible: function(e) {
                    var t = g.get.elementCalculations(),
                        n = e || p.onBottomVisible,
                        o = "bottomVisible";
                    return e && (g.debug("Adding callback for bottom visible", e), p.onBottomVisible = e), t.bottomVisible ? g.execute(n, o) : p.once || g.remove.occurred(o), e === i ? t.bottomVisible : void 0
                },
                topPassed: function(e) {
                    var t = g.get.elementCalculations(),
                        n = e || p.onTopPassed,
                        o = "topPassed";
                    return e && (g.debug("Adding callback for top passed", e), p.onTopPassed = e), t.topPassed ? g.execute(n, o) : p.once || g.remove.occurred(o), e === i ? t.topPassed : void 0
                },
                bottomPassed: function(e) {
                    var t = g.get.elementCalculations(),
                        n = e || p.onBottomPassed,
                        o = "bottomPassed";
                    return e && (g.debug("Adding callback for bottom passed", e), p.onBottomPassed = e), t.bottomPassed ? g.execute(n, o) : p.once || g.remove.occurred(o), e === i ? t.bottomPassed : void 0
                },
                passingReverse: function(e) {
                    var t = g.get.elementCalculations(),
                        n = e || p.onPassingReverse,
                        o = "passingReverse";
                    return e && (g.debug("Adding callback for passing reverse", e), p.onPassingReverse = e), t.passing ? p.once || g.remove.occurred(o) : g.get.occurred("passing") && g.execute(n, o), e !== i ? !t.passing : void 0
                },
                topVisibleReverse: function(e) {
                    var t = g.get.elementCalculations(),
                        n = e || p.onTopVisibleReverse,
                        o = "topVisibleReverse";
                    return e && (g.debug("Adding callback for top visible reverse", e), p.onTopVisibleReverse = e), t.topVisible ? p.once || g.remove.occurred(o) : g.get.occurred("topVisible") && g.execute(n, o), e === i ? !t.topVisible : void 0
                },
                bottomVisibleReverse: function(e) {
                    var t = g.get.elementCalculations(),
                        n = e || p.onBottomVisibleReverse,
                        o = "bottomVisibleReverse";
                    return e && (g.debug("Adding callback for bottom visible reverse", e), p.onBottomVisibleReverse = e), t.bottomVisible ? p.once || g.remove.occurred(o) : g.get.occurred("bottomVisible") && g.execute(n, o), e === i ? !t.bottomVisible : void 0
                },
                topPassedReverse: function(e) {
                    var t = g.get.elementCalculations(),
                        n = e || p.onTopPassedReverse,
                        o = "topPassedReverse";
                    return e && (g.debug("Adding callback for top passed reverse", e), p.onTopPassedReverse = e), t.topPassed ? p.once || g.remove.occurred(o) : g.get.occurred("topPassed") && g.execute(n, o), e === i ? !t.onTopPassed : void 0
                },
                bottomPassedReverse: function(e) {
                    var t = g.get.elementCalculations(),
                        n = e || p.onBottomPassedReverse,
                        o = "bottomPassedReverse";
                    return e && (g.debug("Adding callback for bottom passed reverse", e), p.onBottomPassedReverse = e), t.bottomPassed ? p.once || g.remove.occurred(o) : g.get.occurred("bottomPassed") && g.execute(n, o), e === i ? !t.bottomPassed : void 0
                },
                execute: function(e, t) {
                    var n = g.get.elementCalculations(),
                        i = g.get.screenCalculations();
                    e = e || !1, e && (p.continuous ? (g.debug("Callback being called continuously", t, n), e.call(R, n, i)) : g.get.occurred(t) || (g.debug("Conditions met", t, n), e.call(R, n, i))), g.save.occurred(t)
                },
                remove: {
                    fixed: function() {
                        g.debug("Removing fixed position"), k.removeClass(v.fixed).css({
                            position: "",
                            top: "",
                            left: "",
                            zIndex: ""
                        })
                    },
                    occurred: function(e) {
                        if (e) {
                            var t = g.cache.occurred;
                            t[e] !== i && t[e] === !0 && (g.debug("Callback can now be called again", e), g.cache.occurred[e] = !1)
                        } else g.cache.occurred = {}
                    }
                },
                save: {
                    calculations: function() {
                        g.verbose("Saving all calculations necessary to determine positioning"), g.save.direction(), g.save.screenCalculations(), g.save.elementCalculations()
                    },
                    occurred: function(e) {
                        e && (g.cache.occurred[e] === i || g.cache.occurred[e] !== !0) && (g.verbose("Saving callback occurred", e), g.cache.occurred[e] = !0)
                    },
                    scroll: function(e) {
                        e = e + p.offset || S.scrollTop() + p.offset, g.cache.scroll = e
                    },
                    direction: function() {
                        var e, t = g.get.scroll(),
                            n = g.get.lastScroll();
                        return e = t > n && n ? "down" : n > t && n ? "up" : "static", g.cache.direction = e, g.cache.direction
                    },
                    elementPosition: function() {
                        var e = g.cache.element,
                            t = g.get.screenSize();
                        return g.verbose("Saving element position"), e.fits = e.height < t.height, e.offset = k.offset(), e.width = k.outerWidth(), e.height = k.outerHeight(), g.cache.element = e, e
                    },
                    elementCalculations: function() {
                        var e = g.get.screenCalculations(),
                            t = g.get.elementPosition();
                        return p.includeMargin ? (t.margin = {}, t.margin.top = parseInt(k.css("margin-top"), 10), t.margin.bottom = parseInt(k.css("margin-bottom"), 10), t.top = t.offset.top - t.margin.top, t.bottom = t.offset.top + t.height + t.margin.bottom) : (t.top = t.offset.top, t.bottom = t.offset.top + t.height), t.topVisible = e.bottom >= t.top, t.topPassed = e.top >= t.top, t.bottomVisible = e.bottom >= t.bottom, t.bottomPassed = e.top >= t.bottom, t.pixelsPassed = 0, t.percentagePassed = 0, t.onScreen = t.topVisible && !t.bottomPassed, t.passing = t.topPassed && !t.bottomPassed, t.offScreen = !t.onScreen, t.passing && (t.pixelsPassed = e.top - t.top, t.percentagePassed = (e.top - t.top) / t.height), g.cache.element = t, g.verbose("Updated element calculations", t), t
                    },
                    screenCalculations: function() {
                        var e = g.get.scroll();
                        return g.save.direction(), g.cache.screen.top = e, g.cache.screen.bottom = e + g.cache.screen.height, g.cache.screen
                    },
                    screenSize: function() {
                        g.verbose("Saving window position"), g.cache.screen = {
                            height: S.height()
                        }
                    },
                    position: function() {
                        g.save.screenSize(), g.save.elementPosition()
                    }
                },
                get: {
                    pixelsPassed: function(e) {
                        var t = g.get.elementCalculations();
                        return e.search("%") > -1 ? t.height * (parseInt(e, 10) / 100) : parseInt(e, 10)
                    },
                    occurred: function(e) {
                        return g.cache.occurred !== i ? g.cache.occurred[e] || !1 : !1
                    },
                    direction: function() {
                        return g.cache.direction === i && g.save.direction(), g.cache.direction
                    },
                    elementPosition: function() {
                        return g.cache.element === i && g.save.elementPosition(), g.cache.element
                    },
                    elementCalculations: function() {
                        return g.cache.element === i && g.save.elementCalculations(), g.cache.element
                    },
                    screenCalculations: function() {
                        return g.cache.screen === i && g.save.screenCalculations(), g.cache.screen
                    },
                    screenSize: function() {
                        return g.cache.screen === i && g.save.screenSize(), g.cache.screen
                    },
                    scroll: function() {
                        return g.cache.scroll === i && g.save.scroll(), g.cache.scroll
                    },
                    lastScroll: function() {
                        return g.cache.screen === i ? (g.debug("First scroll event, no last scroll could be found"), !1) : g.cache.screen.top
                    }
                },
                setting: function(t, n) {
                    if (e.isPlainObject(t)) e.extend(!0, p, t);
                    else {
                        if (n === i) return p[t];
                        p[t] = n
                    }
                },
                internal: function(t, n) {
                    if (e.isPlainObject(t)) e.extend(!0, g, t);
                    else {
                        if (n === i) return g[t];
                        g[t] = n
                    }
                },
                debug: function() {
                    p.debug && (p.performance ? g.performance.log(arguments) : (g.debug = Function.prototype.bind.call(console.info, console, p.name + ":"), g.debug.apply(console, arguments)))
                },
                verbose: function() {
                    p.verbose && p.debug && (p.performance ? g.performance.log(arguments) : (g.verbose = Function.prototype.bind.call(console.info, console, p.name + ":"), g.verbose.apply(console, arguments)))
                },
                error: function() {
                    g.error = Function.prototype.bind.call(console.error, console, p.name + ":"), g.error.apply(console, arguments)
                },
                performance: {
                    log: function(e) {
                        var t, n, i;
                        p.performance && (t = (new Date).getTime(), i = c || t, n = t - i, c = t, l.push({
                            Name: e[0],
                            Arguments: [].slice.call(e, 1) || "",
                            Element: R,
                            "Execution Time": n
                        })), clearTimeout(g.performance.timer), g.performance.timer = setTimeout(g.performance.display, 500)
                    },
                    display: function() {
                        var t = p.name + ":",
                            n = 0;
                        c = !1, clearTimeout(g.performance.timer), e.each(l, function(e, t) {
                            n += t["Execution Time"]
                        }), t += " " + n + "ms", s && (t += " '" + s + "'"), (console.group !== i || console.table !== i) && l.length > 0 && (console.groupCollapsed(t), console.table ? console.table(l) : e.each(l, function(e, t) {
                            console.log(t.Name + ": " + t["Execution Time"] + "ms")
                        }), console.groupEnd()), l = []
                    }
                },
                invoke: function(t, n, o) {
                    var r, s, c, l = T;
                    return n = n || m, o = R || o, "string" == typeof t && l !== i && (t = t.split(/[\. ]/), r = t.length - 1, e.each(t, function(n, o) {
                        var a = n != r ? o + t[n + 1].charAt(0).toUpperCase() + t[n + 1].slice(1) : t;
                        if (e.isPlainObject(l[a]) && n != r) l = l[a];
                        else {
                            if (l[a] !== i) return s = l[a], !1;
                            if (!e.isPlainObject(l[o]) || n == r) return l[o] !== i ? (s = l[o], !1) : (g.error(b.method, t), !1);
                            l = l[o]
                        }
                    })), e.isFunction(s) ? c = s.apply(o, n) : s !== i && (c = s), e.isArray(a) ? a.push(c) : a !== i ? a = [a, c] : c !== i && (a = c), s
                }
            }, d ? (T === i && g.initialize(), T.save.scroll(), T.save.calculations(), g.invoke(u)) : (T !== i && T.invoke("destroy"), g.initialize())
        }), a !== i ? a : this
    }, e.fn.visibility.settings = {
        name: "Visibility",
        namespace: "visibility",
        debug: !1,
        verbose: !1,
        performance: !0,
        observeChanges: !0,
        initialCheck: !0,
        refreshOnLoad: !0,
        refreshOnResize: !0,
        checkOnRefresh: !0,
        once: !0,
        continuous: !1,
        offset: 0,
        includeMargin: !1,
        context: t,
        throttle: !1,
        type: !1,
        transition: "fade in",
        duration: 1e3,
        onPassed: {},
        onOnScreen: !1,
        onOffScreen: !1,
        onPassing: !1,
        onTopVisible: !1,
        onBottomVisible: !1,
        onTopPassed: !1,
        onBottomPassed: !1,
        onPassingReverse: !1,
        onTopVisibleReverse: !1,
        onBottomVisibleReverse: !1,
        onTopPassedReverse: !1,
        onBottomPassedReverse: !1,
        onUpdate: !1,
        onRefresh: function() {},
        metadata: {
            src: "src"
        },
        className: {
            fixed: "fixed",
            placeholder: "placeholder"
        },
        error: {
            method: "The method you called is not defined.",
            visible: "Element is hidden, you must call refresh after element becomes visible"
        }
    }
}(jQuery, window, document),
function(e, t, n, i) {
    "use strict";
    e.visit = e.fn.visit = function(n) {
        var o, a = e(e.isFunction(this) ? t : this),
            r = a.selector || "",
            s = (new Date).getTime(),
            c = [],
            l = arguments[0],
            u = "string" == typeof l,
            d = [].slice.call(arguments, 1);
        return a.each(function() {
            var m, f = e.isPlainObject(n) ? e.extend(!0, {}, e.fn.visit.settings, n) : e.extend({}, e.fn.visit.settings),
                g = f.error,
                p = f.namespace,
                v = p + "-module",
                h = e(this),
                b = e(),
                y = this,
                x = h.data(v);
            m = {
                initialize: function() {
                    f.count ? m.store(f.key.count, f.count) : f.id ? m.add.id(f.id) : f.increment && "increment" !== u && m.increment(), m.add.display(h), m.instantiate()
                },
                instantiate: function() {
                    m.verbose("Storing instance of visit module", m), x = m, h.data(v, m)
                },
                destroy: function() {
                    m.verbose("Destroying instance"), h.removeData(v)
                },
                increment: function(e) {
                    var t = m.get.count(),
                        n = +t + 1;
                    e ? m.add.id(e) : (n > f.limit && !f.surpass && (n = f.limit), m.debug("Incrementing visits", n), m.store(f.key.count, n))
                },
                decrement: function(e) {
                    var t = m.get.count(),
                        n = +t - 1;
                    e ? m.remove.id(e) : (m.debug("Removing visit"), m.store(f.key.count, n))
                },
                get: {
                    count: function() {
                        return +m.retrieve(f.key.count) || 0
                    },
                    idCount: function(e) {
                        return e = e || m.get.ids(), e.length
                    },
                    ids: function(e) {
                        var t = [];
                        return e = e || m.retrieve(f.key.ids), "string" == typeof e && (t = e.split(f.delimiter)), m.verbose("Found visited ID list", t), t
                    },
                    storageOptions: function(e) {
                        var t = {};
                        return f.expires && (t.expires = f.expires), f.domain && (t.domain = f.domain), f.path && (t.path = f.path), t
                    }
                },
                has: {
                    visited: function(t, n) {
                        var o = !1;
                        return n = n || m.get.ids(), t !== i && n && e.each(n, function(e, n) {
                            n == t && (o = !0)
                        }), o
                    }
                },
                set: {
                    count: function(e) {
                        m.store(f.key.count, e)
                    },
                    ids: function(e) {
                        m.store(f.key.ids, e)
                    }
                },
                reset: function() {
                    m.store(f.key.count, 0), m.store(f.key.ids, null)
                },
                add: {
                    id: function(e) {
                        var t = m.retrieve(f.key.ids),
                            n = t === i || "" === t ? e : t + f.delimiter + e;
                        m.has.visited(e) ? m.debug("Unique content already visited, not adding visit", e, t) : e === i ? m.debug("ID is not defined") : (m.debug("Adding visit to unique content", e), m.store(f.key.ids, n)), m.set.count(m.get.idCount())
                    },
                    display: function(t) {
                        var n = e(t);
                        n.length > 0 && !e.isWindow(n[0]) && (m.debug("Updating visit count for element", n), b = b.length > 0 ? b.add(n) : n)
                    }
                },
                remove: {
                    id: function(t) {
                        var n = m.get.ids(),
                            o = [];
                        t !== i && n !== i && (m.debug("Removing visit to unique content", t, n), e.each(n, function(e, n) {
                            n !== t && o.push(n)
                        }), o = o.join(f.delimiter), m.store(f.key.ids, o)), m.set.count(m.get.idCount())
                    }
                },
                check: {
                    limit: function(e) {
                        e = e || m.get.count(), f.limit && (e >= f.limit && (m.debug("Pages viewed exceeded limit, firing callback", e, f.limit), f.onLimit.call(y, e)), m.debug("Limit not reached", e, f.limit), f.onChange.call(y, e)), m.update.display(e)
                    }
                },
                update: {
                    display: function(e) {
                        e = e || m.get.count(), b.length > 0 && (m.debug("Updating displayed view count", b), b.html(e))
                    }
                },
                store: function(n, o) {
                    var a = m.get.storageOptions(o);
                    if ("localstorage" == f.storageMethod && t.localStorage !== i) t.localStorage.setItem(n, o), m.debug("Value stored using local storage", n, o);
                    else {
                        if (e.cookie === i) return void m.error(g.noCookieStorage);
                        e.cookie(n, o, a), m.debug("Value stored using cookie", n, o, a)
                    }
                    n == f.key.count && m.check.limit(o)
                },
                retrieve: function(n, o) {
                    var a;
                    return "localstorage" == f.storageMethod && t.localStorage !== i ? a = t.localStorage.getItem(n) : e.cookie !== i ? a = e.cookie(n) : m.error(g.noCookieStorage), ("undefined" == a || "null" == a || a === i || null === a) && (a = i), a
                },
                setting: function(t, n) {
                    if (e.isPlainObject(t)) e.extend(!0, f, t);
                    else {
                        if (n === i) return f[t];
                        f[t] = n
                    }
                },
                internal: function(t, n) {
                    return m.debug("Changing internal", t, n), n === i ? m[t] : void(e.isPlainObject(t) ? e.extend(!0, m, t) : m[t] = n)
                },
                debug: function() {
                    f.debug && (f.performance ? m.performance.log(arguments) : (m.debug = Function.prototype.bind.call(console.info, console, f.name + ":"), m.debug.apply(console, arguments)))
                },
                verbose: function() {
                    f.verbose && f.debug && (f.performance ? m.performance.log(arguments) : (m.verbose = Function.prototype.bind.call(console.info, console, f.name + ":"), m.verbose.apply(console, arguments)))
                },
                error: function() {
                    m.error = Function.prototype.bind.call(console.error, console, f.name + ":"), m.error.apply(console, arguments)
                },
                performance: {
                    log: function(e) {
                        var t, n, i;
                        f.performance && (t = (new Date).getTime(), i = s || t, n = t - i, s = t, c.push({
                            Name: e[0],
                            Arguments: [].slice.call(e, 1) || "",
                            Element: y,
                            "Execution Time": n
                        })), clearTimeout(m.performance.timer), m.performance.timer = setTimeout(m.performance.display, 500)
                    },
                    display: function() {
                        var t = f.name + ":",
                            n = 0;
                        s = !1, clearTimeout(m.performance.timer), e.each(c, function(e, t) {
                            n += t["Execution Time"]
                        }), t += " " + n + "ms", r && (t += " '" + r + "'"), a.length > 1 && (t += " (" + a.length + ")"), (console.group !== i || console.table !== i) && c.length > 0 && (console.groupCollapsed(t), console.table ? console.table(c) : e.each(c, function(e, t) {
                            console.log(t.Name + ": " + t["Execution Time"] + "ms")
                        }), console.groupEnd()), c = []
                    }
                },
                invoke: function(t, n, a) {
                    var r, s, c, l = x;
                    return n = n || d, a = y || a, "string" == typeof t && l !== i && (t = t.split(/[\. ]/), r = t.length - 1, e.each(t, function(n, o) {
                        var a = n != r ? o + t[n + 1].charAt(0).toUpperCase() + t[n + 1].slice(1) : t;
                        if (e.isPlainObject(l[a]) && n != r) l = l[a];
                        else {
                            if (l[a] !== i) return s = l[a], !1;
                            if (!e.isPlainObject(l[o]) || n == r) return l[o] !== i ? (s = l[o], !1) : !1;
                            l = l[o]
                        }
                    })), e.isFunction(s) ? c = s.apply(a, n) : s !== i && (c = s), e.isArray(o) ? o.push(c) : o !== i ? o = [o, c] : c !== i && (o = c), s
                }
            }, u ? (x === i && m.initialize(), m.invoke(l)) : (x !== i && x.invoke("destroy"), m.initialize())
        }), o !== i ? o : this
    }, e.fn.visit.settings = {
        name: "Visit",
        debug: !1,
        verbose: !1,
        performance: !0,
        namespace: "visit",
        increment: !1,
        surpass: !1,
        count: !1,
        limit: !1,
        delimiter: "&",
        storageMethod: "localstorage",
        key: {
            count: "visit-count",
            ids: "visit-ids"
        },
        expires: 30,
        domain: !1,
        path: "/",
        onLimit: function() {},
        onChange: function() {},
        error: {
            method: "The method you called is not defined",
            missingPersist: "Using the persist setting requires the inclusion of PersistJS",
            noCookieStorage: "The default storage cookie requires $.cookie to be included."
        }
    }
}(jQuery, window, document),
function(e, t, n, i) {
    e.site = e.fn.site = function(o) {
        var a, r, s = (new Date).getTime(),
            c = [],
            l = arguments[0],
            u = "string" == typeof l,
            d = [].slice.call(arguments, 1),
            m = e.isPlainObject(o) ? e.extend(!0, {}, e.site.settings, o) : e.extend({}, e.site.settings),
            f = m.namespace,
            g = m.error,
            p = "module-" + f,
            v = e(n),
            h = v,
            b = this,
            y = h.data(p);
        return a = {
            initialize: function() {
                a.instantiate()
            },
            instantiate: function() {
                a.verbose("Storing instance of site", a), y = a, h.data(p, a)
            },
            normalize: function() {
                a.fix.console(), a.fix.requestAnimationFrame()
            },
            fix: {
                console: function() {
                    a.debug("Normalizing window.console"), (console === i || console.log === i) && (a.verbose("Console not available, normalizing events"), a.disable.console()), ("undefined" == typeof console.group || "undefined" == typeof console.groupEnd || "undefined" == typeof console.groupCollapsed) && (a.verbose("Console group not available, normalizing events"), t.console.group = function() {}, t.console.groupEnd = function() {}, t.console.groupCollapsed = function() {}), "undefined" == typeof console.markTimeline && (a.verbose("Mark timeline not available, normalizing events"), t.console.markTimeline = function() {})
                },
                consoleClear: function() {
                    a.debug("Disabling programmatic console clearing"), t.console.clear = function() {}
                },
                requestAnimationFrame: function() {
                    a.debug("Normalizing requestAnimationFrame"), t.requestAnimationFrame === i && (a.debug("RequestAnimationFrame not available, normailizing event"), t.requestAnimationFrame = t.requestAnimationFrame || t.mozRequestAnimationFrame || t.webkitRequestAnimationFrame || t.msRequestAnimationFrame || function(e) {
                        setTimeout(e, 0)
                    })
                }
            },
            moduleExists: function(t) {
                return e.fn[t] !== i && e.fn[t].settings !== i
            },
            enabled: {
                modules: function(t) {
                    var n = [];
                    return t = t || m.modules, e.each(t, function(e, t) {
                        a.moduleExists(t) && n.push(t)
                    }), n
                }
            },
            disabled: {
                modules: function(t) {
                    var n = [];
                    return t = t || m.modules, e.each(t, function(e, t) {
                        a.moduleExists(t) || n.push(t)
                    }), n
                }
            },
            change: {
                setting: function(t, n, o, r) {
                    o = "string" == typeof o ? "all" === o ? m.modules : [o] : o || m.modules, r = r !== i ? r : !0, e.each(o, function(i, o) {
                        var s, c = a.moduleExists(o) ? e.fn[o].settings.namespace || !1 : !0;
                        a.moduleExists(o) && (a.verbose("Changing default setting", t, n, o), e.fn[o].settings[t] = n, r && c && (s = e(":data(module-" + c + ")"), s.length > 0 && (a.verbose("Modifying existing settings", s), s[o]("setting", t, n))))
                    })
                },
                settings: function(t, n, o) {
                    n = "string" == typeof n ? [n] : n || m.modules, o = o !== i ? o : !0, e.each(n, function(n, i) {
                        var r;
                        a.moduleExists(i) && (a.verbose("Changing default setting", t, i), e.extend(!0, e.fn[i].settings, t), o && f && (r = e(":data(module-" + f + ")"), r.length > 0 && (a.verbose("Modifying existing settings", r), r[i]("setting", t))))
                    })
                }
            },
            enable: {
                console: function() {
                    a.console(!0)
                },
                debug: function(e, t) {
                    e = e || m.modules, a.debug("Enabling debug for modules", e), a.change.setting("debug", !0, e, t)
                },
                verbose: function(e, t) {
                    e = e || m.modules, a.debug("Enabling verbose debug for modules", e), a.change.setting("verbose", !0, e, t)
                }
            },
            disable: {
                console: function() {
                    a.console(!1)
                },
                debug: function(e, t) {
                    e = e || m.modules, a.debug("Disabling debug for modules", e), a.change.setting("debug", !1, e, t)
                },
                verbose: function(e, t) {
                    e = e || m.modules, a.debug("Disabling verbose debug for modules", e), a.change.setting("verbose", !1, e, t)
                }
            },
            console: function(e) {
                if (e) {
                    if (y.cache.console === i) return void a.error(g.console);
                    a.debug("Restoring console function"), t.console = y.cache.console
                } else a.debug("Disabling console function"), y.cache.console = t.console, t.console = {
                    clear: function() {},
                    error: function() {},
                    group: function() {},
                    groupCollapsed: function() {},
                    groupEnd: function() {},
                    info: function() {},
                    log: function() {},
                    markTimeline: function() {},
                    warn: function() {}
                }
            },
            destroy: function() {
                a.verbose("Destroying previous site for", h), h.removeData(p)
            },
            cache: {},
            setting: function(t, n) {
                if (e.isPlainObject(t)) e.extend(!0, m, t);
                else {
                    if (n === i) return m[t];
                    m[t] = n
                }
            },
            internal: function(t, n) {
                if (e.isPlainObject(t)) e.extend(!0, a, t);
                else {
                    if (n === i) return a[t];
                    a[t] = n
                }
            },
            debug: function() {
                m.debug && (m.performance ? a.performance.log(arguments) : (a.debug = Function.prototype.bind.call(console.info, console, m.name + ":"), a.debug.apply(console, arguments)))
            },
            verbose: function() {
                m.verbose && m.debug && (m.performance ? a.performance.log(arguments) : (a.verbose = Function.prototype.bind.call(console.info, console, m.name + ":"), a.verbose.apply(console, arguments)))
            },
            error: function() {
                a.error = Function.prototype.bind.call(console.error, console, m.name + ":"), a.error.apply(console, arguments)
            },
            performance: {
                log: function(e) {
                    var t, n, i;
                    m.performance && (t = (new Date).getTime(), i = s || t, n = t - i, s = t, c.push({
                        Element: b,
                        Name: e[0],
                        Arguments: [].slice.call(e, 1) || "",
                        "Execution Time": n
                    })), clearTimeout(a.performance.timer), a.performance.timer = setTimeout(a.performance.display, 500)
                },
                display: function() {
                    var t = m.name + ":",
                        n = 0;
                    s = !1, clearTimeout(a.performance.timer), e.each(c, function(e, t) {
                        n += t["Execution Time"]
                    }), t += " " + n + "ms", (console.group !== i || console.table !== i) && c.length > 0 && (console.groupCollapsed(t), console.table ? console.table(c) : e.each(c, function(e, t) {
                        console.log(t.Name + ": " + t["Execution Time"] + "ms")
                    }), console.groupEnd()), c = []
                }
            },
            invoke: function(t, n, o) {
                var s, c, l, u = y;
                return n = n || d, o = b || o, "string" == typeof t && u !== i && (t = t.split(/[\. ]/), s = t.length - 1, e.each(t, function(n, o) {
                    var r = n != s ? o + t[n + 1].charAt(0).toUpperCase() + t[n + 1].slice(1) : t;
                    if (e.isPlainObject(u[r]) && n != s) u = u[r];
                    else {
                        if (u[r] !== i) return c = u[r], !1;
                        if (!e.isPlainObject(u[o]) || n == s) return u[o] !== i ? (c = u[o], !1) : (a.error(g.method, t), !1);
                        u = u[o]
                    }
                })), e.isFunction(c) ? l = c.apply(o, n) : c !== i && (l = c), e.isArray(r) ? r.push(l) : r !== i ? r = [r, l] : l !== i && (r = l), c
            }
        }, u ? (y === i && a.initialize(), a.invoke(l)) : (y !== i && a.destroy(), a.initialize()), r !== i ? r : this
    }, e.site.settings = {
        name: "Site",
        namespace: "site",
        error: {
            console: "Console cannot be restored, most likely it was overwritten outside of module",
            method: "The method you called is not defined."
        },
        debug: !1,
        verbose: !1,
        performance: !0,
        modules: ["accordion", "api", "checkbox", "dimmer", "dropdown", "embed", "form", "modal", "nag", "popup", "rating", "shape", "sidebar", "state", "sticky", "tab", "transition", "visit", "visibility"],
        siteNamespace: "site",
        namespaceStub: {
            cache: {},
            config: {},
            sections: {},
            section: {},
            utilities: {}
        }
    }, e.extend(e.expr[":"], {
        data: e.expr.createPseudo ? e.expr.createPseudo(function(t) {
            return function(n) {
                return !!e.data(n, t)
            }
        }) : function(t, n, i) {
            return !!e.data(t, i[3])
        }
    })
}(jQuery, window, document),
function(e, t, n, i) {
    "use strict";
    e.fn.accordion = function(n) {
        {
            var o, a = e(this),
                r = (new Date).getTime(),
                s = [],
                c = arguments[0],
                l = "string" == typeof c,
                u = [].slice.call(arguments, 1);
            t.requestAnimationFrame || t.mozRequestAnimationFrame || t.webkitRequestAnimationFrame || t.msRequestAnimationFrame || function(e) {
                setTimeout(e, 0)
            }
        }
        return a.each(function() {
            var d, m, f = e.isPlainObject(n) ? e.extend(!0, {}, e.fn.accordion.settings, n) : e.extend({}, e.fn.accordion.settings),
                g = f.className,
                p = f.namespace,
                v = f.selector,
                h = f.error,
                b = "." + p,
                y = "module-" + p,
                x = a.selector || "",
                w = e(this),
                C = w.find(v.title),
                k = w.find(v.content),
                S = this,
                T = w.data(y);
            m = {
                initialize: function() {
                    m.debug("Initializing", w), m.bind.events(), f.observeChanges && m.observeChanges(), m.instantiate()
                },
                instantiate: function() {
                    T = m, w.data(y, m)
                },
                destroy: function() {
                    m.debug("Destroying previous instance", w), w.off(b).removeData(y)
                },
                refresh: function() {
                    C = w.find(v.title), k = w.find(v.content)
                },
                observeChanges: function() {
                    "MutationObserver" in t && (d = new MutationObserver(function(e) {
                        m.debug("DOM tree modified, updating selector cache"), m.refresh()
                    }), d.observe(S, {
                        childList: !0,
                        subtree: !0
                    }), m.debug("Setting up mutation observer", d))
                },
                bind: {
                    events: function() {
                        m.debug("Binding delegated events"), w.on(f.on + b, v.trigger, m.event.click)
                    }
                },
                event: {
                    click: function() {
                        m.toggle.call(this)
                    }
                },
                toggle: function(t) {
                    var n = t !== i ? "number" == typeof t ? C.eq(t) : e(t).closest(v.title) : e(this).closest(v.title),
                        o = n.next(k),
                        a = o.hasClass(g.animating),
                        r = o.hasClass(g.active),
                        s = r && !a,
                        c = !r && a;
                    m.debug("Toggling visibility of content", n), s || c ? f.collapsible ? m.close.call(n) : m.debug("Cannot close accordion content collapsing is disabled") : m.open.call(n)
                },
                open: function(t) {
                    var n = t !== i ? "number" == typeof t ? C.eq(t) : e(t).closest(v.title) : e(this).closest(v.title),
                        o = n.next(k),
                        a = o.hasClass(g.animating),
                        r = o.hasClass(g.active),
                        s = r || a;
                    return s ? void m.debug("Accordion already open, skipping", o) : (m.debug("Opening accordion content", n), f.onOpening.call(o), f.exclusive && m.closeOthers.call(n), n.addClass(g.active), o.stop(!0, !0).addClass(g.animating), f.animateChildren && (e.fn.transition !== i && w.transition("is supported") ? o.children().transition({
                        animation: "fade in",
                        queue: !1,
                        useFailSafe: !0,
                        debug: f.debug,
                        verbose: f.verbose,
                        duration: f.duration
                    }) : o.children().stop(!0, !0).animate({
                        opacity: 1
                    }, f.duration, m.resetOpacity)), void o.slideDown(f.duration, f.easing, function() {
                        o.removeClass(g.animating).addClass(g.active), m.reset.display.call(this), f.onOpen.call(this), f.onChange.call(this)
                    }))
                },
                close: function(t) {
                    var n = t !== i ? "number" == typeof t ? C.eq(t) : e(t).closest(v.title) : e(this).closest(v.title),
                        o = n.next(k),
                        a = o.hasClass(g.animating),
                        r = o.hasClass(g.active),
                        s = !r && a,
                        c = r && a;
                    !r && !s || c || (m.debug("Closing accordion content", o), f.onClosing.call(o), n.removeClass(g.active), o.stop(!0, !0).addClass(g.animating), f.animateChildren && (e.fn.transition !== i && w.transition("is supported") ? o.children().transition({
                        animation: "fade out",
                        queue: !1,
                        useFailSafe: !0,
                        debug: f.debug,
                        verbose: f.verbose,
                        duration: f.duration
                    }) : o.children().stop(!0, !0).animate({
                        opacity: 0
                    }, f.duration, m.resetOpacity)), o.slideUp(f.duration, f.easing, function() {
                        o.removeClass(g.animating).removeClass(g.active), m.reset.display.call(this), f.onClose.call(this), f.onChange.call(this)
                    }))
                },
                closeOthers: function(t) {
                    var n, o, a, r = t !== i ? C.eq(t) : e(this).closest(v.title),
                        s = r.parents(v.content).prev(v.title),
                        c = r.closest(v.accordion),
                        l = v.title + "." + g.active + ":visible",
                        u = v.content + "." + g.active + ":visible";
                    f.closeNested ? (n = c.find(l).not(s), a = n.next(k)) : (n = c.find(l).not(s), o = c.find(u).find(l).not(s), n = n.not(o), a = n.next(k)), n.length > 0 && (m.debug("Exclusive enabled, closing other content", n), n.removeClass(g.active), a.removeClass(g.animating).stop(!0, !0), f.animateChildren && (e.fn.transition !== i && w.transition("is supported") ? a.children().transition({
                        animation: "fade out",
                        useFailSafe: !0,
                        debug: f.debug,
                        verbose: f.verbose,
                        duration: f.duration
                    }) : a.children().stop(!0, !0).animate({
                        opacity: 0
                    }, f.duration, m.resetOpacity)), a.slideUp(f.duration, f.easing, function() {
                        e(this).removeClass(g.active), m.reset.display.call(this)
                    }))
                },
                reset: {
                    display: function() {
                        m.verbose("Removing inline display from element", this), e(this).css("display", ""), "" === e(this).attr("style") && e(this).attr("style", "").removeAttr("style")
                    },
                    opacity: function() {
                        m.verbose("Removing inline opacity from element", this), e(this).css("opacity", ""), "" === e(this).attr("style") && e(this).attr("style", "").removeAttr("style")
                    }
                },
                setting: function(t, n) {
                    if (m.debug("Changing setting", t, n), e.isPlainObject(t)) e.extend(!0, f, t);
                    else {
                        if (n === i) return f[t];
                        f[t] = n
                    }
                },
                internal: function(t, n) {
                    return m.debug("Changing internal", t, n), n === i ? m[t] : void(e.isPlainObject(t) ? e.extend(!0, m, t) : m[t] = n)
                },
                debug: function() {
                    f.debug && (f.performance ? m.performance.log(arguments) : (m.debug = Function.prototype.bind.call(console.info, console, f.name + ":"), m.debug.apply(console, arguments)))
                },
                verbose: function() {
                    f.verbose && f.debug && (f.performance ? m.performance.log(arguments) : (m.verbose = Function.prototype.bind.call(console.info, console, f.name + ":"), m.verbose.apply(console, arguments)))
                },
                error: function() {
                    m.error = Function.prototype.bind.call(console.error, console, f.name + ":"), m.error.apply(console, arguments)
                },
                performance: {
                    log: function(e) {
                        var t, n, i;
                        f.performance && (t = (new Date).getTime(), i = r || t, n = t - i, r = t, s.push({
                            Name: e[0],
                            Arguments: [].slice.call(e, 1) || "",
                            Element: S,
                            "Execution Time": n
                        })), clearTimeout(m.performance.timer), m.performance.timer = setTimeout(m.performance.display, 500)
                    },
                    display: function() {
                        var t = f.name + ":",
                            n = 0;
                        r = !1, clearTimeout(m.performance.timer), e.each(s, function(e, t) {
                            n += t["Execution Time"]
                        }), t += " " + n + "ms", x && (t += " '" + x + "'"), (console.group !== i || console.table !== i) && s.length > 0 && (console.groupCollapsed(t), console.table ? console.table(s) : e.each(s, function(e, t) {
                            console.log(t.Name + ": " + t["Execution Time"] + "ms")
                        }), console.groupEnd()), s = []
                    }
                },
                invoke: function(t, n, a) {
                    var r, s, c, l = T;
                    return n = n || u, a = S || a, "string" == typeof t && l !== i && (t = t.split(/[\. ]/), r = t.length - 1, e.each(t, function(n, o) {
                        var a = n != r ? o + t[n + 1].charAt(0).toUpperCase() + t[n + 1].slice(1) : t;
                        if (e.isPlainObject(l[a]) && n != r) l = l[a];
                        else {
                            if (l[a] !== i) return s = l[a], !1;
                            if (!e.isPlainObject(l[o]) || n == r) return l[o] !== i ? (s = l[o], !1) : (m.error(h.method, t), !1);
                            l = l[o]
                        }
                    })), e.isFunction(s) ? c = s.apply(a, n) : s !== i && (c = s), e.isArray(o) ? o.push(c) : o !== i ? o = [o, c] : c !== i && (o = c), s
                }
            }, l ? (T === i && m.initialize(), m.invoke(c)) : (T !== i && T.invoke("destroy"), m.initialize())
        }), o !== i ? o : this
    }, e.fn.accordion.settings = {
        name: "Accordion",
        namespace: "accordion",
        debug: !1,
        verbose: !1,
        performance: !0,
        on: "click",
        observeChanges: !0,
        exclusive: !0,
        collapsible: !0,
        closeNested: !1,
        animateChildren: !0,
        duration: 350,
        easing: "easeOutQuad",
        onOpening: function() {},
        onOpen: function() {},
        onClosing: function() {},
        onClose: function() {},
        onChange: function() {},
        error: {
            method: "The method you called is not defined"
        },
        className: {
            active: "active",
            animating: "animating"
        },
        selector: {
            accordion: ".accordion",
            title: ".title",
            trigger: ".title",
            content: ".content"
        }
    }, e.extend(e.easing, {
        easeOutQuad: function(e, t, n, i, o) {
            return -i * (t /= o) * (t - 2) + n
        }
    })
}(jQuery, window, document),
function(e, t, n, i) {
    "use strict";
    e.fn.checkbox = function(n) {
        var o, a = e(this),
            r = a.selector || "",
            s = (new Date).getTime(),
            c = [],
            l = arguments[0],
            u = "string" == typeof l,
            d = [].slice.call(arguments, 1);
        return a.each(function() {
            var a, m, f = e.extend(!0, {}, e.fn.checkbox.settings, n),
                g = f.className,
                p = f.namespace,
                v = f.selector,
                h = f.error,
                b = "." + p,
                y = "module-" + p,
                x = e(this),
                w = e(this).children(v.label),
                C = e(this).children(v.input),
                k = C[0],
                S = !1,
                T = !1,
                A = x.data(y),
                R = this;
            m = {
                initialize: function() {
                    m.verbose("Initializing checkbox", f), m.create.label(), m.bind.events(), m.set.tabbable(), m.hide.input(), m.observeChanges(), m.instantiate(), m.setup()
                },
                instantiate: function() {
                    m.verbose("Storing instance of module", m), A = m, x.data(y, m)
                },
                destroy: function() {
                    m.verbose("Destroying module"), m.unbind.events(), m.show.input(), x.removeData(y)
                },
                fix: {
                    reference: function() {
                        x.is(v.input) && (m.debug("Behavior called on <input> adjusting invoked element"), x = x.closest(v.checkbox), m.refresh())
                    }
                },
                setup: function() {
                    m.set.initialLoad(), m.is.indeterminate() ? (m.debug("Initial value is indeterminate"), m.indeterminate()) : m.is.checked() ? (m.debug("Initial value is checked"), m.check()) : (m.debug("Initial value is unchecked"), m.uncheck()), m.remove.initialLoad()
                },
                refresh: function() {
                    w = x.children(v.label), C = x.children(v.input), k = C[0]
                },
                hide: {
                    input: function() {
                        m.verbose("Modfying <input> z-index to be unselectable"), C.addClass(g.hidden)
                    }
                },
                show: {
                    input: function() {
                        m.verbose("Modfying <input> z-index to be selectable"), C.removeClass(g.hidden)
                    }
                },
                observeChanges: function() {
                    "MutationObserver" in t && (a = new MutationObserver(function(e) {
                        m.debug("DOM tree modified, updating selector cache"), m.refresh()
                    }), a.observe(R, {
                        childList: !0,
                        subtree: !0
                    }), m.debug("Setting up mutation observer", a))
                },
                attachEvents: function(t, n) {
                    var i = e(t);
                    n = e.isFunction(m[n]) ? m[n] : m.toggle, i.length > 0 ? (m.debug("Attaching checkbox events to element", t, n), i.on("click" + b, n)) : m.error(h.notFound)
                },
                event: {
                    click: function(t) {
                        var n = e(t.target);
                        return n.is(v.input) ? void m.verbose("Using default check action on initialized checkbox") : n.is(v.link) ? void m.debug("Clicking link inside checkbox, skipping toggle") : (m.toggle(), C.focus(), void t.preventDefault())
                    },
                    keydown: function(e) {
                        var t = e.which,
                            n = {
                                enter: 13,
                                space: 32,
                                escape: 27
                            };
                        t == n.escape ? (m.verbose("Escape key pressed blurring field"), C.blur(), T = !0) : e.ctrlKey || t != n.space && t != n.enter ? T = !1 : (m.verbose("Enter/space key pressed, toggling checkbox"), m.toggle(), T = !0)
                    },
                    keyup: function(e) {
                        T && e.preventDefault()
                    }
                },
                check: function() {
                    m.should.allowCheck() && (m.debug("Checking checkbox", C), m.set.checked(), m.should.ignoreCallbacks() || (f.onChecked.call(k), f.onChange.call(k)))
                },
                uncheck: function() {
                    m.should.allowUncheck() && (m.debug("Unchecking checkbox"), m.set.unchecked(), m.should.ignoreCallbacks() || (f.onUnchecked.call(k), f.onChange.call(k)))
                },
                indeterminate: function() {
                    return m.should.allowIndeterminate() ? void m.debug("Checkbox is already indeterminate") : (m.debug("Making checkbox indeterminate"), m.set.indeterminate(), void(m.should.ignoreCallbacks() || (f.onIndeterminate.call(k), f.onChange.call(k))))
                },
                determinate: function() {
                    return m.should.allowDeterminate() ? void m.debug("Checkbox is already determinate") : (m.debug("Making checkbox determinate"), m.set.determinate(), void(m.should.ignoreCallbacks() || (f.onDeterminate.call(k), f.onChange.call(k))))
                },
                enable: function() {
                    return m.is.enabled() ? void m.debug("Checkbox is already enabled") : (m.debug("Enabling checkbox"), m.set.enabled(), void f.onEnable.call(k))
                },
                disable: function() {
                    return m.is.disabled() ? void m.debug("Checkbox is already disabled") : (m.debug("Disabling checkbox"), m.set.disabled(), void f.onDisable.call(k))
                },
                get: {
                    radios: function() {
                        var t = m.get.name();
                        return e('input[name="' + t + '"]').closest(v.checkbox)
                    },
                    otherRadios: function() {
                        return m.get.radios().not(x)
                    },
                    name: function() {
                        return C.attr("name")
                    }
                },
                is: {
                    initialLoad: function() {
                        return S
                    },
                    radio: function() {
                        return C.hasClass(g.radio) || "radio" == C.attr("type")
                    },
                    indeterminate: function() {
                        return C.prop("indeterminate") !== i && C.prop("indeterminate")
                    },
                    checked: function() {
                        return C.prop("checked") !== i && C.prop("checked")
                    },
                    disabled: function() {
                        return C.prop("disabled") !== i && C.prop("disabled")
                    },
                    enabled: function() {
                        return !m.is.disabled()
                    },
                    determinate: function() {
                        return !m.is.indeterminate()
                    },
                    unchecked: function() {
                        return !m.is.checked()
                    }
                },
                should: {
                    allowCheck: function() {
                        return m.is.determinate() && m.is.checked() && !m.should.forceCallbacks() ? (m.debug("Should not allow check, checkbox is already checked"), !1) : f.beforeChecked.apply(k) === !1 ? (m.debug("Should not allow check, beforeChecked cancelled"), !1) : !0
                    },
                    allowUncheck: function() {
                        return m.is.determinate() && m.is.unchecked() && !m.should.forceCallbacks() ? (m.debug("Should not allow uncheck, checkbox is already unchecked"), !1) : f.beforeUnchecked.apply(k) === !1 ? (m.debug("Should not allow uncheck, beforeUnchecked cancelled"), !1) : !0
                    },
                    allowIndeterminate: function() {
                        return m.is.indeterminate() && !m.should.forceCallbacks() ? (m.debug("Should not allow indeterminate, checkbox is already indeterminate"), !1) : f.beforeIndeterminate.apply(k) === !1 ? (m.debug("Should not allow indeterminate, beforeIndeterminate cancelled"), !1) : !0
                    },
                    allowDeterminate: function() {
                        return m.is.determinate() && !m.should.forceCallbacks() ? (m.debug("Should not allow determinate, checkbox is already determinate"), !1) : f.beforeDeterminate.apply(k) === !1 ? (m.debug("Should not allow determinate, beforeDeterminate cancelled"), !1) : !0
                    },
                    forceCallbacks: function() {
                        return m.is.initialLoad() && f.fireOnInit
                    },
                    ignoreCallbacks: function() {
                        return S && !f.fireOnInit
                    }
                },
                can: {
                    change: function() {
                        return !(x.hasClass(g.disabled) || x.hasClass(g.readOnly) || C.prop("disabled") || C.prop("readonly"))
                    },
                    uncheck: function() {
                        return "boolean" == typeof f.uncheckable ? f.uncheckable : !m.is.radio()
                    }
                },
                set: {
                    initialLoad: function() {
                        S = !0
                    },
                    checked: function() {
                        return m.verbose("Setting class to checked"), x.removeClass(g.indeterminate).addClass(g.checked), m.is.radio() && m.uncheckOthers(), !m.is.indeterminate() && m.is.checked() ? void m.debug("Input is already checked, skipping input property change") : (m.verbose("Setting state to checked", k), C.prop("indeterminate", !1).prop("checked", !0), void m.trigger.change())
                    },
                    unchecked: function() {
                        return m.verbose("Removing checked class"), x.removeClass(g.indeterminate).removeClass(g.checked), !m.is.indeterminate() && m.is.unchecked() ? void m.debug("Input is already unchecked") : (m.debug("Setting state to unchecked"), C.prop("indeterminate", !1).prop("checked", !1), void m.trigger.change())
                    },
                    indeterminate: function() {
                        return m.verbose("Setting class to indeterminate"), x.addClass(g.indeterminate), m.is.indeterminate() ? void m.debug("Input is already indeterminate, skipping input property change") : (m.debug("Setting state to indeterminate"), C.prop("indeterminate", !0), void m.trigger.change())
                    },
                    determinate: function() {
                        return m.verbose("Removing indeterminate class"), x.removeClass(g.indeterminate), m.is.determinate() ? void m.debug("Input is already determinate, skipping input property change") : (m.debug("Setting state to determinate"), void C.prop("indeterminate", !1))
                    },
                    disabled: function() {
                        return m.verbose("Setting class to disabled"), x.addClass(g.disabled), m.is.disabled() ? void m.debug("Input is already disabled, skipping input property change") : (m.debug("Setting state to disabled"), C.prop("disabled", "disabled"), void m.trigger.change())
                    },
                    enabled: function() {
                        return m.verbose("Removing disabled class"), x.removeClass(g.disabled), m.is.enabled() ? void m.debug("Input is already enabled, skipping input property change") : (m.debug("Setting state to enabled"), C.prop("disabled", !1), void m.trigger.change())
                    },
                    tabbable: function() {
                        m.verbose("Adding tabindex to checkbox"), C.attr("tabindex") === i && C.attr("tabindex", 0)
                    }
                },
                remove: {
                    initialLoad: function() {
                        S = !1
                    }
                },
                trigger: {
                    change: function() {
                        m.verbose("Triggering change event from programmatic change"), C.trigger("change")
                    }
                },
                create: {
                    label: function() {
                        C.prevAll(v.label).length > 0 ? (C.prev(v.label).detach().insertAfter(C), m.debug("Moving existing label", w)) : m.has.label() || (w = e("<label>").insertAfter(C), m.debug("Creating label", w))
                    }
                },
                has: {
                    label: function() {
                        return w.length > 0
                    }
                },
                bind: {
                    events: function() {
                        m.verbose("Attaching checkbox events"), x.on("click" + b, m.event.click).on("keydown" + b, v.input, m.event.keydown).on("keyup" + b, v.input, m.event.keyup)
                    }
                },
                unbind: {
                    events: function() {
                        m.debug("Removing events"), x.off(b)
                    }
                },
                uncheckOthers: function() {
                    var e = m.get.otherRadios();
                    m.debug("Unchecking other radios", e), e.removeClass(g.checked)
                },
                toggle: function() {
                    return m.can.change() ? void(m.is.indeterminate() || m.is.unchecked() ? (m.debug("Currently unchecked"), m.check()) : m.is.checked() && m.can.uncheck() && (m.debug("Currently checked"), m.uncheck())) : void(m.is.radio() || m.debug("Checkbox is read-only or disabled, ignoring toggle"))
                },
                setting: function(t, n) {
                    if (m.debug("Changing setting", t, n), e.isPlainObject(t)) e.extend(!0, f, t);
                    else {
                        if (n === i) return f[t];
                        f[t] = n
                    }
                },
                internal: function(t, n) {
                    if (e.isPlainObject(t)) e.extend(!0, m, t);
                    else {
                        if (n === i) return m[t];
                        m[t] = n
                    }
                },
                debug: function() {
                    f.debug && (f.performance ? m.performance.log(arguments) : (m.debug = Function.prototype.bind.call(console.info, console, f.name + ":"), m.debug.apply(console, arguments)))
                },
                verbose: function() {
                    f.verbose && f.debug && (f.performance ? m.performance.log(arguments) : (m.verbose = Function.prototype.bind.call(console.info, console, f.name + ":"), m.verbose.apply(console, arguments)))
                },
                error: function() {
                    m.error = Function.prototype.bind.call(console.error, console, f.name + ":"), m.error.apply(console, arguments)
                },
                performance: {
                    log: function(e) {
                        var t, n, i;
                        f.performance && (t = (new Date).getTime(), i = s || t, n = t - i, s = t, c.push({
                            Name: e[0],
                            Arguments: [].slice.call(e, 1) || "",
                            Element: R,
                            "Execution Time": n
                        })), clearTimeout(m.performance.timer), m.performance.timer = setTimeout(m.performance.display, 500)
                    },
                    display: function() {
                        var t = f.name + ":",
                            n = 0;
                        s = !1, clearTimeout(m.performance.timer), e.each(c, function(e, t) {
                            n += t["Execution Time"]
                        }), t += " " + n + "ms", r && (t += " '" + r + "'"), (console.group !== i || console.table !== i) && c.length > 0 && (console.groupCollapsed(t), console.table ? console.table(c) : e.each(c, function(e, t) {
                            console.log(t.Name + ": " + t["Execution Time"] + "ms")
                        }), console.groupEnd()), c = []
                    }
                },
                invoke: function(t, n, a) {
                    var r, s, c, l = A;
                    return n = n || d, a = R || a, "string" == typeof t && l !== i && (t = t.split(/[\. ]/), r = t.length - 1, e.each(t, function(n, o) {
                        var a = n != r ? o + t[n + 1].charAt(0).toUpperCase() + t[n + 1].slice(1) : t;
                        if (e.isPlainObject(l[a]) && n != r) l = l[a];
                        else {
                            if (l[a] !== i) return s = l[a], !1;
                            if (!e.isPlainObject(l[o]) || n == r) return l[o] !== i ? (s = l[o], !1) : (m.error(h.method, t), !1);
                            l = l[o]
                        }
                    })), e.isFunction(s) ? c = s.apply(a, n) : s !== i && (c = s), e.isArray(o) ? o.push(c) : o !== i ? o = [o, c] : c !== i && (o = c), s
                }
            }, u ? (A === i && m.initialize(), m.invoke(l)) : (A !== i && A.invoke("destroy"), m.initialize())
        }), o !== i ? o : this
    }, e.fn.checkbox.settings = {
        name: "Checkbox",
        namespace: "checkbox",
        debug: !1,
        verbose: !0,
        performance: !0,
        uncheckable: "auto",
        fireOnInit: !1,
        onChange: function() {},
        beforeChecked: function() {},
        beforeUnchecked: function() {},
        beforeDeterminate: function() {},
        beforeIndeterminate: function() {},
        onChecked: function() {},
        onUnchecked: function() {},
        onDeterminate: function() {},
        onIndeterminate: function() {},
        onEnabled: function() {},
        onDisabled: function() {},
        className: {
            checked: "checked",
            indeterminate: "indeterminate",
            disabled: "disabled",
            hidden: "hidden",
            radio: "radio",
            readOnly: "read-only"
        },
        error: {
            method: "The method you called is not defined"
        },
        selector: {
            checkbox: ".ui.checkbox",
            label: "label, .box",
            input: 'input[type="checkbox"], input[type="radio"]',
            link: "a[href]"
        }
    }
}(jQuery, window, document),
function(e, t, n, i) {
    "use strict";
    e.fn.dimmer = function(t) {
        var o, a = e(this),
            r = (new Date).getTime(),
            s = [],
            c = arguments[0],
            l = "string" == typeof c,
            u = [].slice.call(arguments, 1);
        return a.each(function() {
            var d, m, f, g = e.isPlainObject(t) ? e.extend(!0, {}, e.fn.dimmer.settings, t) : e.extend({}, e.fn.dimmer.settings),
                p = g.selector,
                v = g.namespace,
                h = g.className,
                b = g.error,
                y = "." + v,
                x = "module-" + v,
                w = a.selector || "",
                C = "ontouchstart" in n.documentElement ? "touchstart" : "click",
                k = e(this),
                S = this,
                T = k.data(x);
            f = {
                preinitialize: function() {
                    f.is.dimmer() ? (m = k.parent(), d = k) : (m = k, d = f.has.dimmer() ? g.dimmerName ? m.find(p.dimmer).filter("." + g.dimmerName) : m.find(p.dimmer) : f.create())
                },
                initialize: function() {
                    f.debug("Initializing dimmer", g), f.bind.events(), f.set.dimmable(), f.instantiate()
                },
                instantiate: function() {
                    f.verbose("Storing instance of module", f), T = f, k.data(x, T)
                },
                destroy: function() {
                    f.verbose("Destroying previous module", d), f.unbind.events(), f.remove.variation(), m.off(y)
                },
                bind: {
                    events: function() {
                        "hover" == g.on ? m.on("mouseenter" + y, f.show).on("mouseleave" + y, f.hide) : "click" == g.on && m.on(C + y, f.toggle), f.is.page() && (f.debug("Setting as a page dimmer", m), f.set.pageDimmer()), f.is.closable() && (f.verbose("Adding dimmer close event", d), m.on(C + y, p.dimmer, f.event.click))
                    }
                },
                unbind: {
                    events: function() {
                        k.removeData(x)
                    }
                },
                event: {
                    click: function(t) {
                        f.verbose("Determining if event occured on dimmer", t), (0 === d.find(t.target).length || e(t.target).is(p.content)) && (f.hide(), t.stopImmediatePropagation())
                    }
                },
                addContent: function(t) {
                    var n = e(t);
                    f.debug("Add content to dimmer", n), n.parent()[0] !== d[0] && n.detach().appendTo(d)
                },
                create: function() {
                    var t = e(g.template.dimmer());
                    return g.variation && (f.debug("Creating dimmer with variation", g.variation), t.addClass(g.variation)), g.dimmerName && (f.debug("Creating named dimmer", g.dimmerName), t.addClass(g.dimmerName)), t.appendTo(m), t
                },
                show: function(t) {
                    t = e.isFunction(t) ? t : function() {}, f.debug("Showing dimmer", d, g), f.is.dimmed() && !f.is.animating() || !f.is.enabled() ? f.debug("Dimmer is already shown or disabled") : (f.animate.show(t), g.onShow.call(S), g.onChange.call(S))
                },
                hide: function(t) {
                    t = e.isFunction(t) ? t : function() {}, f.is.dimmed() || f.is.animating() ? (f.debug("Hiding dimmer", d), f.animate.hide(t), g.onHide.call(S), g.onChange.call(S)) : f.debug("Dimmer is not visible")
                },
                toggle: function() {
                    f.verbose("Toggling dimmer visibility", d), f.is.dimmed() ? f.hide() : f.show()
                },
                animate: {
                    show: function(t) {
                        t = e.isFunction(t) ? t : function() {}, g.useCSS && e.fn.transition !== i && d.transition("is supported") ? ("auto" !== g.opacity && f.set.opacity(), d.transition({
                            animation: g.transition + " in",
                            queue: !1,
                            duration: f.get.duration(),
                            useFailSafe: !0,
                            onStart: function() {
                                f.set.dimmed()
                            },
                            onComplete: function() {
                                f.set.active(), t()
                            }
                        })) : (f.verbose("Showing dimmer animation with javascript"), f.set.dimmed(), "auto" == g.opacity && (g.opacity = .8), d.stop().css({
                            opacity: 0,
                            width: "100%",
                            height: "100%"
                        }).fadeTo(f.get.duration(), g.opacity, function() {
                            d.removeAttr("style"), f.set.active(), t()
                        }))
                    },
                    hide: function(t) {
                        t = e.isFunction(t) ? t : function() {}, g.useCSS && e.fn.transition !== i && d.transition("is supported") ? (f.verbose("Hiding dimmer with css"), d.transition({
                            animation: g.transition + " out",
                            queue: !1,
                            duration: f.get.duration(),
                            useFailSafe: !0,
                            onStart: function() {
                                f.remove.dimmed()
                            },
                            onComplete: function() {
                                f.remove.active(), t()
                            }
                        })) : (f.verbose("Hiding dimmer with javascript"), f.remove.dimmed(), d.stop().fadeOut(f.get.duration(), function() {
                            f.remove.active(), d.removeAttr("style"), t()
                        }))
                    }
                },
                get: {
                    dimmer: function() {
                        return d
                    },
                    duration: function() {
                        return "object" == typeof g.duration ? f.is.active() ? g.duration.hide : g.duration.show : g.duration
                    }
                },
                has: {
                    dimmer: function() {
                        return g.dimmerName ? k.find(p.dimmer).filter("." + g.dimmerName).length > 0 : k.find(p.dimmer).length > 0
                    }
                },
                is: {
                    active: function() {
                        return d.hasClass(h.active)
                    },
                    animating: function() {
                        return d.is(":animated") || d.hasClass(h.animating)
                    },
                    closable: function() {
                        return "auto" == g.closable ? "hover" == g.on ? !1 : !0 : g.closable
                    },
                    dimmer: function() {
                        return k.hasClass(h.dimmer)
                    },
                    dimmable: function() {
                        return k.hasClass(h.dimmable)
                    },
                    dimmed: function() {
                        return m.hasClass(h.dimmed)
                    },
                    disabled: function() {
                        return m.hasClass(h.disabled)
                    },
                    enabled: function() {
                        return !f.is.disabled()
                    },
                    page: function() {
                        return m.is("body")
                    },
                    pageDimmer: function() {
                        return d.hasClass(h.pageDimmer)
                    }
                },
                can: {
                    show: function() {
                        return !d.hasClass(h.disabled)
                    }
                },
                set: {
                    opacity: function(e) {
                        var t = d.css("background-color"),
                            n = t.split(","),
                            i = n && 4 == n.length;
                        e = g.opacity || e, i ? (n[3] = e + ")", t = n.join(",")) : t = "rgba(0, 0, 0, " + e + ")", f.debug("Setting opacity to", e), d.css("background-color", t)
                    },
                    active: function() {
                        d.addClass(h.active)
                    },
                    dimmable: function() {
                        m.addClass(h.dimmable)
                    },
                    dimmed: function() {
                        m.addClass(h.dimmed)
                    },
                    pageDimmer: function() {
                        d.addClass(h.pageDimmer)
                    },
                    disabled: function() {
                        d.addClass(h.disabled)
                    },
                    variation: function(e) {
                        e = e || g.variation, e && d.addClass(e)
                    }
                },
                remove: {
                    active: function() {
                        d.removeClass(h.active)
                    },
                    dimmed: function() {
                        m.removeClass(h.dimmed)
                    },
                    disabled: function() {
                        d.removeClass(h.disabled)
                    },
                    variation: function(e) {
                        e = e || g.variation, e && d.removeClass(e)
                    }
                },
                setting: function(t, n) {
                    if (f.debug("Changing setting", t, n), e.isPlainObject(t)) e.extend(!0, g, t);
                    else {
                        if (n === i) return g[t];
                        g[t] = n
                    }
                },
                internal: function(t, n) {
                    if (e.isPlainObject(t)) e.extend(!0, f, t);
                    else {
                        if (n === i) return f[t];
                        f[t] = n
                    }
                },
                debug: function() {
                    g.debug && (g.performance ? f.performance.log(arguments) : (f.debug = Function.prototype.bind.call(console.info, console, g.name + ":"), f.debug.apply(console, arguments)))
                },
                verbose: function() {
                    g.verbose && g.debug && (g.performance ? f.performance.log(arguments) : (f.verbose = Function.prototype.bind.call(console.info, console, g.name + ":"), f.verbose.apply(console, arguments)))
                },
                error: function() {
                    f.error = Function.prototype.bind.call(console.error, console, g.name + ":"), f.error.apply(console, arguments)
                },
                performance: {
                    log: function(e) {
                        var t, n, i;
                        g.performance && (t = (new Date).getTime(), i = r || t, n = t - i, r = t, s.push({
                            Name: e[0],
                            Arguments: [].slice.call(e, 1) || "",
                            Element: S,
                            "Execution Time": n
                        })), clearTimeout(f.performance.timer), f.performance.timer = setTimeout(f.performance.display, 500)
                    },
                    display: function() {
                        var t = g.name + ":",
                            n = 0;
                        r = !1, clearTimeout(f.performance.timer), e.each(s, function(e, t) {
                            n += t["Execution Time"]
                        }), t += " " + n + "ms", w && (t += " '" + w + "'"), a.length > 1 && (t += " (" + a.length + ")"), (console.group !== i || console.table !== i) && s.length > 0 && (console.groupCollapsed(t), console.table ? console.table(s) : e.each(s, function(e, t) {
                            console.log(t.Name + ": " + t["Execution Time"] + "ms")
                        }), console.groupEnd()), s = []
                    }
                },
                invoke: function(t, n, a) {
                    var r, s, c, l = T;
                    return n = n || u, a = S || a, "string" == typeof t && l !== i && (t = t.split(/[\. ]/), r = t.length - 1, e.each(t, function(n, o) {
                        var a = n != r ? o + t[n + 1].charAt(0).toUpperCase() + t[n + 1].slice(1) : t;
                        if (e.isPlainObject(l[a]) && n != r) l = l[a];
                        else {
                            if (l[a] !== i) return s = l[a], !1;
                            if (!e.isPlainObject(l[o]) || n == r) return l[o] !== i ? (s = l[o], !1) : (f.error(b.method, t), !1);
                            l = l[o]
                        }
                    })), e.isFunction(s) ? c = s.apply(a, n) : s !== i && (c = s), e.isArray(o) ? o.push(c) : o !== i ? o = [o, c] : c !== i && (o = c), s
                }
            }, f.preinitialize(), l ? (T === i && f.initialize(), f.invoke(c)) : (T !== i && T.invoke("destroy"), f.initialize())
        }), o !== i ? o : this
    }, e.fn.dimmer.settings = {
        name: "Dimmer",
        namespace: "dimmer",
        debug: !1,
        verbose: !1,
        performance: !0,
        dimmerName: !1,
        variation: !1,
        closable: "auto",
        useCSS: !0,
        transition: "fade",
        on: !1,
        opacity: "auto",
        duration: {
            show: 500,
            hide: 500
        },
        onChange: function() {},
        onShow: function() {},
        onHide: function() {},
        error: {
            method: "The method you called is not defined."
        },
        className: {
            active: "active",
            animating: "animating",
            dimmable: "dimmable",
            dimmed: "dimmed",
            dimmer: "dimmer",
            disabled: "disabled",
            hide: "hide",
            pageDimmer: "page",
            show: "show"
        },
        selector: {
            dimmer: "> .ui.dimmer",
            content: ".ui.dimmer > .content, .ui.dimmer > .content > .center"
        },
        template: {
            dimmer: function() {
                return e("<div />").attr("class", "ui dimmer")
            }
        }
    }
}(jQuery, window, document),
function(e, t, n, i) {
    "use strict";
    e.fn.dropdown = function(o) {
        var a, r = e(this),
            s = e(n),
            c = r.selector || "",
            l = "ontouchstart" in n.documentElement,
            u = (new Date).getTime(),
            d = [],
            m = arguments[0],
            f = "string" == typeof m,
            g = [].slice.call(arguments, 1);
        return r.each(function(p) {
            var v, h, b, y, x, w, C, k = e.isPlainObject(o) ? e.extend(!0, {}, e.fn.dropdown.settings, o) : e.extend({}, e.fn.dropdown.settings),
                S = k.className,
                T = k.message,
                A = k.fields,
                R = k.metadata,
                P = k.namespace,
                E = k.regExp,
                F = k.selector,
                D = k.error,
                O = k.templates,
                q = "." + P,
                j = "module-" + P,
                z = e(this),
                I = e(k.context),
                L = z.find(F.text),
                N = z.find(F.search),
                V = z.find(F.input),
                H = z.find(F.icon),
                M = z.prev().find(F.text).length > 0 ? z.prev().find(F.text) : z.prev(),
                U = z.children(F.menu),
                W = U.find(F.item),
                B = !1,
                Q = !1,
                X = !1,
                $ = this,
                Y = z.data(j);
            C = {
                initialize: function() {
                    C.debug("Initializing dropdown", k), C.is.alreadySetup() ? C.setup.reference() : (C.setup.layout(), C.refreshData(), C.save.defaults(), C.restore.selected(), C.create.id(), C.bind.events(), C.observeChanges(), C.instantiate())
                },
                instantiate: function() {
                    C.verbose("Storing instance of dropdown", C), Y = C, z.data(j, C)
                },
                destroy: function() {
                    C.verbose("Destroying previous dropdown", z), C.remove.tabbable(), z.off(q).removeData(j), U.off(q), s.off(b), x && x.disconnect(), w && w.disconnect()
                },
                observeChanges: function() {
                    "MutationObserver" in t && (x = new MutationObserver(function(e) {
                        C.debug("<select> modified, recreating menu"), C.setup.select()
                    }), w = new MutationObserver(function(e) {
                        C.debug("Menu modified, updating selector cache"), C.refresh()
                    }), C.has.input() && x.observe(V[0], {
                        childList: !0,
                        subtree: !0
                    }), C.has.menu() && w.observe(U[0], {
                        childList: !0,
                        subtree: !0
                    }), C.debug("Setting up mutation observer", x, w))
                },
                create: {
                    id: function() {
                        y = (Math.random().toString(16) + "000000000").substr(2, 8), b = "." + y, C.verbose("Creating unique id for element", y)
                    },
                    userChoice: function(t) {
                        var n, o, a;
                        return (t = t || C.get.userValues()) ? (t = e.isArray(t) ? t : [t], e.each(t, function(t, r) {
                            C.get.item(r) === !1 && (a = k.templates.addition(C.add.variables(T.addResult, r)), o = e("<div />").html(a).attr("data-" + R.value, r).attr("data-" + R.text, r).addClass(S.addition).addClass(S.item), n = n === i ? o : n.add(o), C.verbose("Creating user choices for value", r, o))
                        }), n) : !1
                    },
                    userLabels: function(t) {
                        var n = C.get.userValues();
                        n && (C.debug("Adding user labels", n), e.each(n, function(e, t) {
                            C.verbose("Adding custom user value"), C.add.label(t, t)
                        }))
                    },
                    menu: function() {
                        U = e("<div />").addClass(S.menu).appendTo(z)
                    }
                },
                search: function(e) {
                    e = e !== i ? e : C.get.query(), C.verbose("Searching for query", e), C.filter(e)
                },
                select: {
                    firstUnfiltered: function() {
                        C.verbose("Selecting first non-filtered element"), C.remove.selectedItem(), W.not(F.unselectable).eq(0).addClass(S.selected)
                    },
                    nextAvailable: function(e) {
                        e = e.eq(0);
                        var t = e.nextAll(F.item).not(F.unselectable).eq(0),
                            n = e.prevAll(F.item).not(F.unselectable).eq(0),
                            i = t.length > 0;
                        i ? (C.verbose("Moving selection to", t), t.addClass(S.selected)) : (C.verbose("Moving selection to", n), n.addClass(S.selected))
                    }
                },
                setup: {
                    api: function() {
                        var e = {
                            debug: k.debug,
                            on: !1
                        };
                        C.verbose("First request, initializing API"), z.api(e)
                    },
                    layout: function() {
                        z.is("select") && (C.setup.select(), C.setup.returnedObject()), C.has.menu() || C.create.menu(), C.is.search() && !C.has.search() && (C.verbose("Adding search input"), N = e("<input />").addClass(S.search).insertBefore(L)), k.allowTab && C.set.tabbable()
                    },
                    select: function() {
                        var t = C.get.selectValues();
                        C.debug("Dropdown initialized on a select", t), z.is("select") && (V = z), V.parent(F.dropdown).length > 0 ? (C.debug("UI dropdown already exists. Creating dropdown menu only"), z = V.closest(F.dropdown), C.has.menu() || C.create.menu(), U = z.children(F.menu), C.setup.menu(t)) : (C.debug("Creating entire dropdown from select"), z = e("<div />").attr("class", V.attr("class")).addClass(S.selection).addClass(S.dropdown).html(O.dropdown(t)).insertBefore(V), V.hasClass(S.multiple) && V.prop("multiple") === !1 && (C.error(D.missingMultiple), V.prop("multiple", !0)), V.is("[multiple]") && C.set.multiple(), V.removeAttr("class").detach().prependTo(z)), C.refresh()
                    },
                    menu: function(e) {
                        U.html(O.menu(e, A)), W = U.find(F.item)
                    },
                    reference: function() {
                        C.debug("Dropdown behavior was called on select, replacing with closest dropdown"), z = z.parent(F.dropdown), C.refresh(), C.setup.returnedObject(), f && (Y = C, C.invoke(m))
                    },
                    returnedObject: function() {
                        var e = r.slice(0, p),
                            t = r.slice(p + 1);
                        r = e.add(z).add(t)
                    }
                },
                refresh: function() {
                    C.refreshSelectors(), C.refreshData()
                },
                refreshSelectors: function() {
                    C.verbose("Refreshing selector cache"), L = z.find(F.text), N = z.find(F.search), V = z.find(F.input), H = z.find(F.icon), M = z.prev().find(F.text).length > 0 ? z.prev().find(F.text) : z.prev(), U = z.children(F.menu), W = U.find(F.item)
                },
                refreshData: function() {
                    C.verbose("Refreshing cached metadata"), W.removeData(R.text).removeData(R.value), z.removeData(R.defaultText).removeData(R.defaultValue).removeData(R.placeholderText)
                },
                toggle: function() {
                    C.verbose("Toggling menu visibility"), C.is.active() ? C.hide() : C.show()
                },
                show: function(t) {
                    if (t = e.isFunction(t) ? t : function() {}, C.can.show() && !C.is.active()) {
                        if (C.debug("Showing dropdown"), C.is.multiple() && !C.has.search() && C.is.allFiltered()) return !0;
                        C.has.message() && !C.has.maxSelections() && C.remove.message(), k.onShow.call($) !== !1 && C.animate.show(function() {
                            C.can.click() && C.bind.intent(), C.set.visible(), t.call($)
                        })
                    }
                },
                hide: function(t) {
                    t = e.isFunction(t) ? t : function() {}, C.is.active() && (C.debug("Hiding dropdown"), k.onHide.call($) !== !1 && C.animate.hide(function() {
                        C.remove.visible(), t.call($)
                    }))
                },
                hideOthers: function() {
                    C.verbose("Finding other dropdowns to hide"), r.not(z).has(F.menu + "." + S.visible).dropdown("hide")
                },
                hideMenu: function() {
                    C.verbose("Hiding menu  instantaneously"), C.remove.active(), C.remove.visible(), U.transition("hide")
                },
                hideSubMenus: function() {
                    var e = U.children(F.item).find(F.menu);
                    C.verbose("Hiding sub menus", e), e.transition("hide")
                },
                bind: {
                    events: function() {
                        l && C.bind.touchEvents(), C.bind.keyboardEvents(), C.bind.inputEvents(), C.bind.mouseEvents()
                    },
                    touchEvents: function() {
                        C.debug("Touch device detected binding additional touch events"), C.is.searchSelection() || C.is.single() && z.on("touchstart" + q, C.event.test.toggle), U.on("touchstart" + q, F.item, C.event.item.mouseenter)
                    },
                    keyboardEvents: function() {
                        C.verbose("Binding keyboard events"), z.on("keydown" + q, C.event.keydown), C.has.search() && z.on(C.get.inputEvent() + q, F.search, C.event.input), C.is.multiple() && s.on("keydown" + b, C.event.document.keydown)
                    },
                    inputEvents: function() {
                        C.verbose("Binding input change events"), z.on("change" + q, F.input, C.event.change)
                    },
                    mouseEvents: function() {
                        C.verbose("Binding mouse events"), C.is.multiple() && z.on("click" + q, F.label, C.event.label.click).on("click" + q, F.remove, C.event.remove.click), C.is.searchSelection() ? (z.on("mousedown" + q, F.menu, C.event.menu.mousedown).on("mouseup" + q, F.menu, C.event.menu.mouseup).on("click" + q, F.icon, C.event.icon.click).on("click" + q, F.search, C.show).on("focus" + q, F.search, C.event.search.focus).on("blur" + q, F.search, C.event.search.blur).on("click" + q, F.text, C.event.text.focus), C.is.multiple() && z.on("click" + q, C.event.click)) : ("click" == k.on ? z.on("click" + q, F.icon, C.event.icon.click).on("click" + q, C.event.test.toggle) : "hover" == k.on ? z.on("mouseenter" + q, C.delay.show).on("mouseleave" + q, C.delay.hide) : z.on(k.on + q, C.toggle), z.on("mousedown" + q, C.event.mousedown).on("mouseup" + q, C.event.mouseup).on("focus" + q, C.event.focus).on("blur" + q, C.event.blur)), U.on("mouseenter" + q, F.item, C.event.item.mouseenter).on("mouseleave" + q, F.item, C.event.item.mouseleave).on("click" + q, F.item, C.event.item.click)
                    },
                    intent: function() {
                        C.verbose("Binding hide intent event to document"), l && s.on("touchstart" + b, C.event.test.touch).on("touchmove" + b, C.event.test.touch), s.on("click" + b, C.event.test.hide)
                    }
                },
                unbind: {
                    intent: function() {
                        C.verbose("Removing hide intent event from document"), l && s.off("touchstart" + b).off("touchmove" + b), s.off("click" + b)
                    }
                },
                filter: function(e) {
                    var t = e !== i ? e : C.get.query(),
                        n = function() {
                            C.is.multiple() && C.filterActive(), C.select.firstUnfiltered(), C.has.allResultsFiltered() ? k.onNoResults.call($, t) ? k.allowAdditions || (C.verbose("All items filtered, showing message", t), C.add.message(T.noResults)) : (C.verbose("All items filtered, hiding dropdown", t), C.hideMenu()) : C.remove.message(), k.allowAdditions && C.add.userSuggestion(e), C.is.searchSelection() && C.can.show() && C.is.focusedOnSearch() && C.show()
                        };
                    k.useLabels && C.has.maxSelections() || (k.apiSettings ? C.can.useAPI() ? C.queryRemote(t, function() {
                        n()
                    }) : C.error(D.noAPI) : (C.filterItems(t), n()))
                },
                queryRemote: function(t, n) {
                    var i = {
                        errorDuration: !1,
                        throttle: k.throttle,
                        urlData: {
                            query: t
                        },
                        onError: function() {
                            C.add.message(T.serverError), n()
                        },
                        onFailure: function() {
                            C.add.message(T.serverError), n()
                        },
                        onSuccess: function(e) {
                            C.remove.message(), C.setup.menu({
                                values: e.results
                            }), n()
                        }
                    };
                    z.api("get request") || C.setup.api(), i = e.extend(!0, {}, i, k.apiSettings), z.api("setting", i).api("query")
                },
                filterItems: function(t) {
                    var n = t !== i ? t : C.get.query(),
                        o = e(),
                        a = C.escape.regExp(n),
                        r = new RegExp("^" + a, "igm");
                    C.has.query() ? (C.verbose("Searching for matching values", n), W.each(function() {
                        var t, i, a = e(this);
                        if ("both" == k.match || "text" == k.match) {
                            if (t = String(C.get.choiceText(a, !1)), -1 !== t.search(r)) return o = o.add(a), !0;
                            if (k.fullTextSearch && C.fuzzySearch(n, t)) return o = o.add(a), !0
                        }
                        if ("both" == k.match || "value" == k.match) {
                            if (i = String(C.get.choiceValue(a, t)), -1 !== i.search(r)) return o = o.add(a), !0;
                            if (k.fullTextSearch && C.fuzzySearch(n, i)) return o = o.add(a), !0
                        }
                    })) : o = W, C.debug("Showing only matched items", n), C.remove.filteredItem(), W.not(o).addClass(S.filtered)
                },
                fuzzySearch: function(e, t) {
                    var n = t.length,
                        i = e.length;
                    if (e = e.toLowerCase(), t = t.toLowerCase(), i > n) return !1;
                    if (i === n) return e === t;
                    e: for (var o = 0, a = 0; i > o; o++) {
                        for (var r = e.charCodeAt(o); n > a;)
                            if (t.charCodeAt(a++) === r) continue e;
                        return !1
                    }
                    return !0
                },
                filterActive: function() {
                    k.useLabels && W.filter("." + S.active).addClass(S.filtered)
                },
                focusSearch: function() {
                    C.is.search() && !C.is.focusedOnSearch() && N[0].focus()
                },
                forceSelection: function() {
                    var e = W.not(S.filtered).filter("." + S.selected).eq(0),
                        t = W.not(S.filtered).filter("." + S.active).eq(0),
                        n = e.length > 0 ? e : t,
                        i = n.size() > 0;
                    i && C.has.query() ? (C.debug("Forcing partial selection to selected item", n), C.event.item.click.call(n)) : C.hide()
                },
                event: {
                    change: function() {
                        X || (C.debug("Input changed, updating selection"), C.set.selected())
                    },
                    focus: function() {
                        k.showOnFocus && !B && C.is.hidden() && !h && C.show()
                    },
                    click: function(t) {
                        var n = e(t.target);
                        n.is(z) && !C.is.focusedOnSearch() && C.focusSearch()
                    },
                    blur: function(e) {
                        h = n.activeElement === this, B || h || (C.remove.activeLabel(), C.hide())
                    },
                    mousedown: function() {
                        B = !0
                    },
                    mouseup: function() {
                        B = !1
                    },
                    search: {
                        focus: function() {
                            B = !0, C.is.multiple() && C.remove.activeLabel(), k.showOnFocus && C.show()
                        },
                        blur: function(e) {
                            h = n.activeElement === this, Q || h ? h && k.forceSelection && C.forceSelection() : C.is.multiple() ? (C.remove.activeLabel(), C.hide()) : k.forceSelection ? C.forceSelection() : C.hide()
                        }
                    },
                    icon: {
                        click: function(e) {
                            C.toggle(), e.stopPropagation()
                        }
                    },
                    text: {
                        focus: function(e) {
                            B = !0, C.focusSearch()
                        }
                    },
                    input: function(e) {
                        (C.is.multiple() || C.is.searchSelection()) && C.set.filtered(), clearTimeout(C.timer), C.timer = setTimeout(C.search, k.delay.search)
                    },
                    label: {
                        click: function(t) {
                            var n = e(this),
                                i = z.find(F.label),
                                o = i.filter("." + S.active),
                                a = n.nextAll("." + S.active),
                                r = n.prevAll("." + S.active),
                                s = a.length > 0 ? n.nextUntil(a).add(o).add(n) : n.prevUntil(r).add(o).add(n);
                            t.shiftKey ? (o.removeClass(S.active), s.addClass(S.active)) : t.ctrlKey ? n.toggleClass(S.active) : (o.removeClass(S.active), n.addClass(S.active)), k.onLabelSelect.apply(this, i.filter("." + S.active))
                        }
                    },
                    remove: {
                        click: function() {
                            var t = e(this).parent();
                            t.hasClass(S.active) ? C.remove.activeLabels() : C.remove.activeLabels(t)
                        }
                    },
                    test: {
                        toggle: function(e) {
                            var t = C.is.multiple() ? C.show : C.toggle;
                            C.determine.eventOnElement(e, t) && e.preventDefault()
                        },
                        touch: function(e) {
                            C.determine.eventOnElement(e, function() {
                                "touchstart" == e.type ? C.timer = setTimeout(function() {
                                    C.hide()
                                }, k.delay.touch) : "touchmove" == e.type && clearTimeout(C.timer)
                            }), e.stopPropagation()
                        },
                        hide: function(e) {
                            C.determine.eventInModule(e, C.hide)
                        }
                    },
                    menu: {
                        mousedown: function() {
                            Q = !0
                        },
                        mouseup: function() {
                            Q = !1
                        }
                    },
                    item: {
                        mouseenter: function(t) {
                            var n = e(this).children(F.menu),
                                i = e(this).siblings(F.item).children(F.menu);
                            n.length > 0 && (clearTimeout(C.itemTimer), C.itemTimer = setTimeout(function() {
                                C.verbose("Showing sub-menu", n), e.each(i, function() {
                                    C.animate.hide(!1, e(this))
                                }), C.animate.show(!1, n)
                            }, k.delay.show), t.preventDefault())
                        },
                        mouseleave: function(t) {
                            var n = e(this).children(F.menu);
                            n.length > 0 && (clearTimeout(C.itemTimer), C.itemTimer = setTimeout(function() {
                                C.verbose("Hiding sub-menu", n), C.animate.hide(!1, n)
                            }, k.delay.hide))
                        },
                        touchend: function() {},
                        click: function(t) {
                            var n = e(this),
                                i = e(t ? t.target : ""),
                                o = n.find(F.menu),
                                a = C.get.choiceText(n),
                                r = C.get.choiceValue(n, a),
                                s = o.length > 0,
                                c = o.find(i).length > 0;
                            c || s && !k.allowCategorySelection || (k.useLabels || (C.remove.filteredItem(), C.remove.searchTerm(), C.set.scrollPosition(n)), C.determine.selectAction.call(this, a, r))
                        }
                    },
                    document: {
                        keydown: function(e) {
                            var t = e.which,
                                n = C.get.shortcutKeys(),
                                i = C.is.inObject(t, n);
                            if (i) {
                                var o = z.find(F.label),
                                    a = o.filter("." + S.active),
                                    r = (a.data(R.value), o.index(a)),
                                    s = o.length,
                                    c = a.length > 0,
                                    l = a.length > 1,
                                    u = 0 === r,
                                    d = r + 1 == s,
                                    m = C.is.searchSelection(),
                                    f = C.is.focusedOnSearch(),
                                    g = C.is.focused(),
                                    p = f && 0 === C.get.caretPosition();
                                if (m && !c && !f) return;
                                t == n.leftArrow ? !g && !p || c ? c && (e.shiftKey ? C.verbose("Adding previous label to selection") : (C.verbose("Selecting previous label"), o.removeClass(S.active)), u && !l ? a.addClass(S.active) : a.prev(F.siblingLabel).addClass(S.active).end(), e.preventDefault()) : (C.verbose("Selecting previous label"), o.last().addClass(S.active)) : t == n.rightArrow ? (g && !c && o.first().addClass(S.active), c && (e.shiftKey ? C.verbose("Adding next label to selection") : (C.verbose("Selecting next label"), o.removeClass(S.active)), d ? m ? f ? o.removeClass(S.active) : C.focusSearch() : l ? a.next(F.siblingLabel).addClass(S.active) : a.addClass(S.active) : a.next(F.siblingLabel).addClass(S.active), e.preventDefault())) : t == n.deleteKey || t == n.backspace ? c ? (C.verbose("Removing active labels"), d && m && !f && C.focusSearch(), a.last().next(F.siblingLabel).addClass(S.active), C.remove.activeLabels(a), e.preventDefault()) : p && !c && t == n.backspace && (C.verbose("Removing last label on input backspace"), a = o.last().addClass(S.active), C.remove.activeLabels(a)) : a.removeClass(S.active)
                            }
                        }
                    },
                    keydown: function(e) {
                        var t = e.which,
                            n = C.get.shortcutKeys(),
                            i = C.is.inObject(t, n);
                        if (i) {
                            var o, a, r = W.not(F.unselectable).filter("." + S.selected).eq(0),
                                s = U.children("." + S.active).eq(0),
                                c = r.length > 0 ? r : s,
                                l = c.length > 0 ? c.siblings(":not(." + S.filtered + ")").andSelf() : U.children(":not(." + S.filtered + ")"),
                                u = c.children(F.menu),
                                d = c.closest(F.menu),
                                m = d.hasClass(S.visible) || d.hasClass(S.animating) || d.parent(F.menu).length > 0,
                                f = u.length > 0,
                                g = c.length > 0,
                                p = c.not(F.unselectable).length > 0;
                            if (C.is.visible()) {
                                if ((t == n.enter || t == n.delimiter) && (t == n.enter && g && f && !k.allowCategorySelection ? (C.verbose("Pressed enter on unselectable category, opening sub menu"), t = n.rightArrow) : p && (C.verbose("Selecting item from keyboard shortcut", c), C.event.item.click.call(c, e), C.is.searchSelection() && C.remove.searchTerm()), e.preventDefault()), t == n.leftArrow && (a = d[0] !== U[0], a && (C.verbose("Left key pressed, closing sub-menu"), C.animate.hide(!1, d), c.removeClass(S.selected), d.closest(F.item).addClass(S.selected), e.preventDefault())), t == n.rightArrow && f && (C.verbose("Right key pressed, opening sub-menu"), C.animate.show(!1, u), c.removeClass(S.selected), u.find(F.item).eq(0).addClass(S.selected), e.preventDefault()), t == n.upArrow) {
                                    if (o = g && m ? c.prevAll(F.item + ":not(" + F.unselectable + ")").eq(0) : W.eq(0), l.index(o) < 0) return C.verbose("Up key pressed but reached top of current menu"), void e.preventDefault();
                                    C.verbose("Up key pressed, changing active item"), c.removeClass(S.selected), o.addClass(S.selected), C.set.scrollPosition(o), e.preventDefault()
                                }
                                if (t == n.downArrow) {
                                    if (o = g && m ? o = c.nextAll(F.item + ":not(" + F.unselectable + ")").eq(0) : W.eq(0), 0 === o.length) return C.verbose("Down key pressed but reached bottom of current menu"), void e.preventDefault();
                                    C.verbose("Down key pressed, changing active item"), W.removeClass(S.selected), o.addClass(S.selected), C.set.scrollPosition(o), e.preventDefault()
                                }
                                t == n.pageUp && (C.scrollPage("up"), e.preventDefault()), t == n.pageDown && (C.scrollPage("down"), e.preventDefault()), t == n.escape && (C.verbose("Escape key pressed, closing dropdown"), C.hide())
                            } else t == n.delimiter && e.preventDefault(), t == n.downArrow && (C.verbose("Down key pressed, showing dropdown"), C.show(), e.preventDefault())
                        } else C.is.selection() && !C.is.search() && C.set.selectedLetter(String.fromCharCode(t))
                    }
                },
                determine: {
                    selectAction: function(t, n) {
                        C.verbose("Determining action", k.action), e.isFunction(C.action[k.action]) ? (C.verbose("Triggering preset action", k.action, t, n), C.action[k.action].call(this, t, n)) : e.isFunction(k.action) ? (C.verbose("Triggering user action", k.action, t, n), k.action.call(this, t, n)) : C.error(D.action, k.action)
                    },
                    eventInModule: function(t, i) {
                        var o = e(t.target),
                            a = o.closest(n.documentElement).length > 0,
                            r = o.closest(z).length > 0;
                        return i = e.isFunction(i) ? i : function() {}, a && !r ? (C.verbose("Triggering event", i), i(), !0) : (C.verbose("Event occurred in dropdown, canceling callback"), !1)
                    },
                    eventOnElement: function(t, n) {
                        var i = e(t.target),
                            o = i.closest(F.siblingLabel),
                            a = 0 === z.find(o).length,
                            r = 0 === i.closest(U).length;
                        return n = e.isFunction(n) ? n : function() {}, a && r ? (C.verbose("Triggering event", n), n(), !0) : (C.verbose("Event occurred in dropdown menu, canceling callback"), !1)
                    }
                },
                action: {
                    nothing: function() {},
                    activate: function(t, n) {
                        if (n = n !== i ? n : t, C.can.activate(e(this))) {
                            if (C.set.selected(n, e(this)), C.is.multiple() && !C.is.allFiltered()) return;
                            C.hideAndClear()
                        }
                    },
                    select: function(e, t) {
                        C.action.activate.call(this)
                    },
                    combo: function(t, n) {
                        n = n !== i ? n : t, C.set.selected(n, e(this)), C.hideAndClear()
                    },
                    hide: function(e, t) {
                        C.set.value(t), C.hideAndClear()
                    }
                },
                get: {
                    id: function() {
                        return y
                    },
                    defaultText: function() {
                        return z.data(R.defaultText)
                    },
                    defaultValue: function() {
                        return z.data(R.defaultValue)
                    },
                    placeholderText: function() {
                        return z.data(R.placeholderText) || ""
                    },
                    text: function() {
                        return L.text()
                    },
                    query: function() {
                        return e.trim(N.val())
                    },
                    searchWidth: function(e) {
                        return e * k.glyphWidth + "em"
                    },
                    selectionCount: function() {
                        var t, n = C.get.values();
                        return t = C.is.multiple() ? e.isArray(n) ? n.length : 0 : "" !== C.get.value() ? 1 : 0
                    },
                    transition: function(e) {
                        return "auto" == k.transition ? C.is.upward(e) ? "slide up" : "slide down" : k.transition
                    },
                    userValues: function() {
                        var t = C.get.values();
                        return t ? (t = e.isArray(t) ? t : [t], e.grep(t, function(e) {
                            return C.get.item(e) === !1
                        })) : !1
                    },
                    uniqueArray: function(t) {
                        return e.grep(t, function(n, i) {
                            return e.inArray(n, t) === i
                        })
                    },
                    caretPosition: function() {
                        var e, t, i = N.get(0);
                        return "selectionStart" in i ? i.selectionStart : n.selection ? (i.focus(), e = n.selection.createRange(), t = e.text.length, e.moveStart("character", -i.value.length), e.text.length - t) : void 0
                    },
                    shortcutKeys: function() {
                        return {
                            backspace: 8,
                            delimiter: 188,
                            deleteKey: 46,
                            enter: 13,
                            escape: 27,
                            pageUp: 33,
                            pageDown: 34,
                            leftArrow: 37,
                            upArrow: 38,
                            rightArrow: 39,
                            downArrow: 40
                        }
                    },
                    value: function() {
                        var t = V.length > 0 ? V.val() : z.data(R.value);
                        return e.isArray(t) && 1 === t.length && "" === t[0] ? "" : t
                    },
                    values: function() {
                        var e = C.get.value();
                        return "" === e ? "" : !C.has.selectInput() && C.is.multiple() ? "string" == typeof e ? e.split(k.delimiter) : "" : e
                    },
                    remoteValues: function() {
                        var t = C.get.values(),
                            n = !1;
                        return t && ("string" == typeof t && (t = [t]), n = {}, e.each(t, function(e, t) {
                            var i = C.read.remoteData(t);
                            C.verbose("Restoring value from session data", i, t), n[t] = i ? i : t
                        })), n
                    },
                    choiceText: function(t, n) {
                        return n = n !== i ? n : k.preserveHTML, t ? (t.find(F.menu).length > 0 && (C.verbose("Retreiving text of element with sub-menu"), t = t.clone(), t.find(F.menu).remove(), t.find(F.menuIcon).remove()), t.data(R.text) !== i ? t.data(R.text) : e.trim(n ? t.html() : t.text())) : void 0
                    },
                    choiceValue: function(t, n) {
                        return n = n || C.get.choiceText(t), t ? t.data(R.value) !== i ? String(t.data(R.value)) : "string" == typeof n ? e.trim(n.toLowerCase()) : String(n) : !1
                    },
                    inputEvent: function() {
                        var e = N[0];
                        return e ? e.oninput !== i ? "input" : e.onpropertychange !== i ? "propertychange" : "keyup" : !1
                    },
                    selectValues: function() {
                        var t = {};
                        return t.values = [], z.find("option").each(function() {
                            var n = e(this),
                                o = n.html(),
                                a = n.attr("disabled"),
                                r = n.attr("value") !== i ? n.attr("value") : o;
                            "auto" === k.placeholder && "" === r ? t.placeholder = o : t.values.push({
                                name: o,
                                value: r,
                                disabled: a
                            })
                        }), k.placeholder && "auto" !== k.placeholder && (C.debug("Setting placeholder value to", k.placeholder), t.placeholder = k.placeholder), k.sortSelect ? (t.values.sort(function(e, t) {
                            return e.name > t.name ? 1 : -1
                        }), C.debug("Retrieved and sorted values from select", t)) : C.debug("Retreived values from select", t), t
                    },
                    activeItem: function() {
                        return W.filter("." + S.active)
                    },
                    selectedItem: function() {
                        var e = W.not(F.unselectable).filter("." + S.selected);
                        return e.length > 0 ? e : W.eq(0)
                    },
                    itemWithAdditions: function(e) {
                        var t = C.get.item(e),
                            n = C.create.userChoice(e),
                            i = n && n.length > 0;
                        return i && (t = t.length > 0 ? t.add(n) : n), t
                    },
                    item: function(t, n) {
                        var o, a, r = !1;
                        return t = t !== i ? t : C.get.values() !== i ? C.get.values() : C.get.text(), o = a ? t.length > 0 : t !== i && null !== t, a = C.is.multiple() && e.isArray(t), n = "" === t || 0 === t ? !0 : n || !1, o && W.each(function() {
                            var o = e(this),
                                s = C.get.choiceText(o),
                                c = C.get.choiceValue(o, s);
                            if (null !== c && c !== i)
                                if (a)(-1 !== e.inArray(String(c), t) || -1 !== e.inArray(s, t)) && (r = r ? r.add(o) : o);
                                else if (n) {
                                if (C.verbose("Ambiguous dropdown value using strict type check", o, t), c === t || s === t) return r = o, !0
                            } else if (String(c) == String(t) || s == t) return C.verbose("Found select item by value", c, t), r = o, !0
                        }), r
                    }
                },
                check: {
                    maxSelections: function(e) {
                        return k.maxSelections ? (e = e !== i ? e : C.get.selectionCount(), e >= k.maxSelections ? (C.debug("Maximum selection count reached"), k.useLabels && (W.addClass(S.filtered), C.add.message(T.maxSelections)), !0) : (C.verbose("No longer at maximum selection count"), C.remove.message(), C.remove.filteredItem(), C.is.searchSelection() && C.filterItems(), !1)) : !0
                    }
                },
                restore: {
                    defaults: function() {
                        C.clear(), C.restore.defaultText(), C.restore.defaultValue()
                    },
                    defaultText: function() {
                        var e = C.get.defaultText(),
                            t = C.get.placeholderText;
                        e === t ? (C.debug("Restoring default placeholder text", e), C.set.placeholderText(e)) : (C.debug("Restoring default text", e), C.set.text(e))
                    },
                    defaultValue: function() {
                        var e = C.get.defaultValue();
                        e !== i && (C.debug("Restoring default value", e), "" !== e ? (C.set.value(e), C.set.selected()) : (C.remove.activeItem(), C.remove.selectedItem()))
                    },
                    labels: function() {
                        k.allowAdditions && (k.useLabels || (C.error(D.labels), k.useLabels = !0), C.debug("Restoring selected values"), C.create.userLabels()), C.check.maxSelections()
                    },
                    selected: function() {
                        C.restore.values(), C.is.multiple() ? (C.debug("Restoring previously selected values and labels"), C.restore.labels()) : C.debug("Restoring previously selected values")
                    },
                    values: function() {
                        C.set.initialLoad(), k.apiSettings ? k.saveRemoteData ? C.restore.remoteValues() : C.clearValue() : C.set.selected(), C.remove.initialLoad()
                    },
                    remoteValues: function() {
                        var t = C.get.remoteValues();
                        C.debug("Recreating selected from session data", t), t && (C.is.single() ? e.each(t, function(e, t) {
                            C.set.text(t)
                        }) : e.each(t, function(e, t) {
                            C.add.label(e, t)
                        }))
                    }
                },
                read: {
                    remoteData: function(e) {
                        var n;
                        return t.Storage === i ? void C.error(D.noStorage) : (n = sessionStorage.getItem(e), n !== i ? n : !1)
                    }
                },
                save: {
                    defaults: function() {
                        C.save.defaultText(), C.save.placeholderText(), C.save.defaultValue()
                    },
                    defaultValue: function() {
                        var e = C.get.value();
                        C.verbose("Saving default value as", e), z.data(R.defaultValue, e)
                    },
                    defaultText: function() {
                        var e = C.get.text();
                        C.verbose("Saving default text as", e), z.data(R.defaultText, e)
                    },
                    placeholderText: function() {
                        var e;
                        k.placeholder !== !1 && L.hasClass(S.placeholder) && (e = C.get.text(), C.verbose("Saving placeholder text as", e), z.data(R.placeholderText, e))
                    },
                    remoteData: function(e, n) {
                        return t.Storage === i ? void C.error(D.noStorage) : (C.verbose("Saving remote data to session storage", n, e), void sessionStorage.setItem(n, e))
                    }
                },
                clear: function() {
                    C.is.multiple() ? C.remove.labels() : (C.remove.activeItem(), C.remove.selectedItem()), C.set.placeholderText(), C.clearValue()
                },
                clearValue: function() {
                    C.set.value("")
                },
                scrollPage: function(e, t) {
                    var n, i, o, a = t || C.get.selectedItem(),
                        r = a.closest(F.menu),
                        s = r.outerHeight(),
                        c = r.scrollTop(),
                        l = W.eq(0).outerHeight(),
                        u = Math.floor(s / l),
                        d = (r.prop("scrollHeight"), "up" == e ? c - l * u : c + l * u),
                        m = W.not(F.unselectable);
                    o = "up" == e ? m.index(a) - u : m.index(a) + u, n = "up" == e ? o >= 0 : o < m.length, i = n ? m.eq(o) : "up" == e ? m.first() : m.last(), i.length > 0 && (C.debug("Scrolling page", e, i), a.removeClass(S.selected), i.addClass(S.selected), r.scrollTop(d))
                },
                set: {
                    filtered: function() {
                        var e = C.is.multiple(),
                            t = C.is.searchSelection(),
                            n = e && t,
                            i = t ? C.get.query() : "",
                            o = "string" == typeof i && i.length > 0,
                            a = C.get.searchWidth(i.length),
                            r = "" !== i;
                        e && o && (C.verbose("Adjusting input width", a, k.glyphWidth), N.css("width", a)), o || n && r ? (C.verbose("Hiding placeholder text"), L.addClass(S.filtered)) : (!e || n && !r) && (C.verbose("Showing placeholder text"), L.removeClass(S.filtered))
                    },
                    loading: function() {
                        z.addClass(S.loading)
                    },
                    placeholderText: function(e) {
                        e = e || C.get.placeholderText(), C.debug("Setting placeholder text", e), C.set.text(e), L.addClass(S.placeholder)
                    },
                    tabbable: function() {
                        C.has.search() ? (C.debug("Added tabindex to searchable dropdown"), N.val("").attr("tabindex", 0), U.attr("tabindex", -1)) : (C.debug("Added tabindex to dropdown"), z.attr("tabindex") || (z.attr("tabindex", 0), U.attr("tabindex", -1)))
                    },
                    initialLoad: function() {
                        C.verbose("Setting initial load"), v = !0
                    },
                    activeItem: function(e) {
                        e.addClass(k.allowAdditions && e.filter(F.addition).length > 0 ? S.filtered : S.active)
                    },
                    scrollPosition: function(e, t) {
                        var n, o, a, r, s, c, l, u, d, m = 5;
                        e = e || C.get.selectedItem(), n = e.closest(F.menu), o = e && e.length > 0, t = t !== i ? t : !1, e && n.length > 0 && o && (r = e.position().top, n.addClass(S.loading), c = n.scrollTop(), s = n.offset().top, r = e.offset().top, a = c - s + r, t || (l = n.height(), d = a + m > c + l, u = c > a - m), C.debug("Scrolling to active item", a), (t || u || d) && n.scrollTop(a), n.removeClass(S.loading))
                    },
                    text: function(e) {
                        "select" !== k.action && ("combo" == k.action ? (C.debug("Changing combo button text", e, M), k.preserveHTML ? M.html(e) : M.text(e)) : (e !== C.get.placeholderText() && L.removeClass(S.placeholder), C.debug("Changing text", e, L), L.removeClass(S.filtered), k.preserveHTML ? L.html(e) : L.text(e)))
                    },
                    selectedLetter: function(t) {
                        var n, i = W.filter("." + S.selected),
                            o = i.length > 0 && C.has.firstLetter(i, t),
                            a = !1;
                        o && (n = i.nextAll(W).eq(0), C.has.firstLetter(n, t) && (a = n)), a || W.each(function() {
                            return C.has.firstLetter(e(this), t) ? (a = e(this), !1) : void 0
                        }), a && (C.verbose("Scrolling to next value with letter", t), C.set.scrollPosition(a), i.removeClass(S.selected), a.addClass(S.selected))
                    },
                    direction: function(e) {
                        "auto" == k.direction ? C.is.onScreen(e) ? C.remove.upward(e) : C.set.upward(e) : "upward" == k.direction && C.set.upward(e)
                    },
                    upward: function(e) {
                        var t = e || z;
                        t.addClass(S.upward)
                    },
                    value: function(e, t, n) {
                        var o = V.length > 0,
                            a = (!C.has.value(e), C.get.values()),
                            r = e !== i ? String(e) : e;
                        if (o) {
                            if (r == a && (C.verbose("Skipping value update already same value", e, a), !C.is.initialLoad())) return;
                            C.is.single() && C.has.selectInput() && C.can.extendSelect() && (C.debug("Adding user option", e), C.add.optionValue(e)), C.debug("Updating input value", e, a), X = !0, V.val(e), k.fireOnInit === !1 && C.is.initialLoad() ? C.debug("Input native change event ignored on initial load") : V.trigger("change"), X = !1
                        } else C.verbose("Storing value in metadata", e, V), e !== a && z.data(R.value, r);
                        k.fireOnInit === !1 && C.is.initialLoad() ? C.verbose("No callback on initial load", k.onChange) : k.onChange.call($, e, t, n)
                    },
                    active: function() {
                        z.addClass(S.active)
                    },
                    multiple: function() {
                        z.addClass(S.multiple);

                    },
                    visible: function() {
                        z.addClass(S.visible)
                    },
                    exactly: function(e, t) {
                        C.debug("Setting selected to exact values"), C.clear(), C.set.selected(e, t)
                    },
                    selected: function(t, n) {
                        var i = C.is.multiple();
                        n = k.allowAdditions ? n || C.get.itemWithAdditions(t) : n || C.get.item(t), n && (C.debug("Setting selected menu item to", n), C.is.single() ? (C.remove.activeItem(), C.remove.selectedItem()) : k.useLabels && C.remove.selectedItem(), n.each(function() {
                            var t = e(this),
                                o = C.get.choiceText(t),
                                a = C.get.choiceValue(t, o),
                                r = t.hasClass(S.filtered),
                                s = t.hasClass(S.active),
                                c = t.hasClass(S.addition),
                                l = i && 1 == n.length;
                            i ? !s || c ? (k.apiSettings && k.saveRemoteData && C.save.remoteData(o, a), k.useLabels ? (C.add.value(a, o, t), C.add.label(a, o, l), C.set.activeItem(t), C.filterActive(), C.select.nextAvailable(n)) : (C.add.value(a, o, t), C.set.text(C.add.variables(T.count)), C.set.activeItem(t))) : r || (C.debug("Selected active value, removing label"), C.remove.selected(a)) : (k.apiSettings && k.saveRemoteData && C.save.remoteData(o, a), C.set.text(o), C.set.value(a, o, t), t.addClass(S.active).addClass(S.selected))
                        }))
                    }
                },
                add: {
                    label: function(t, n, i) {
                        var o, a = C.is.searchSelection() ? N : L;
                        return o = e("<a />").addClass(S.label).attr("data-value", t).html(O.label(t, n)), o = k.onLabelCreate.call(o, t, n), C.has.label(t) ? void C.debug("Label already exists, skipping", t) : (k.label.variation && o.addClass(k.label.variation), void(i === !0 ? (C.debug("Animating in label", o), o.addClass(S.hidden).insertBefore(a).transition(k.label.transition, k.label.duration)) : (C.debug("Adding selection label", o), o.insertBefore(a))))
                    },
                    message: function(t) {
                        var n = U.children(F.message),
                            i = k.templates.message(C.add.variables(t));
                        n.length > 0 ? n.html(i) : n = e("<div/>").html(i).addClass(S.message).appendTo(U)
                    },
                    optionValue: function(t) {
                        var n = V.find('option[value="' + t + '"]'),
                            i = n.length > 0;
                        i || (x && (x.disconnect(), C.verbose("Temporarily disconnecting mutation observer", t)), C.is.single() && (C.verbose("Removing previous user addition"), V.find("option." + S.addition).remove()), e("<option/>").prop("value", t).addClass(S.addition).html(t).appendTo(V), C.verbose("Adding user addition as an <option>", t), x && x.observe(V[0], {
                            childList: !0,
                            subtree: !0
                        }))
                    },
                    userSuggestion: function(e) {
                        var t, n = U.children(F.addition),
                            i = C.get.item(e),
                            o = i && i.not(F.addition).length,
                            a = n.length > 0;
                        if (!k.useLabels || !C.has.maxSelections()) {
                            if ("" === e || o) return void n.remove();
                            W.removeClass(S.selected), a ? (t = k.templates.addition(C.add.variables(T.addResult, e)), n.html(t).attr("data-" + R.value, e).attr("data-" + R.text, e).removeClass(S.filtered).addClass(S.selected), C.verbose("Replacing user suggestion with new value", n)) : (n = C.create.userChoice(e), n.prependTo(U).addClass(S.selected), C.verbose("Adding item choice to menu corresponding with user choice addition", n))
                        }
                    },
                    variables: function(e, t) {
                        var n, i, o = -1 !== e.search("{count}"),
                            a = -1 !== e.search("{maxCount}"),
                            r = -1 !== e.search("{term}");
                        return C.verbose("Adding templated variables to message", e), o && (n = C.get.selectionCount(), e = e.replace("{count}", n)), a && (n = C.get.selectionCount(), e = e.replace("{maxCount}", k.maxSelections)), r && (i = t || C.get.query(), e = e.replace("{term}", i)), e
                    },
                    value: function(t, n, i) {
                        var o, a = C.get.values();
                        return "" === t ? void C.debug("Cannot select blank values from multiselect") : (e.isArray(a) ? (o = a.concat([t]), o = C.get.uniqueArray(o)) : o = [t], C.has.selectInput() ? C.can.extendSelect() && (C.debug("Adding value to select", t, o, V), C.add.optionValue(t)) : (o = o.join(k.delimiter), C.debug("Setting hidden input to delimited value", o, V)), k.fireOnInit === !1 && C.is.initialLoad() ? C.verbose("Skipping onadd callback on initial load", k.onAdd) : k.onAdd.call($, t, n, i), C.set.value(o, t, n, i), void C.check.maxSelections())
                    }
                },
                remove: {
                    active: function() {
                        z.removeClass(S.active)
                    },
                    activeLabel: function() {
                        z.find(F.label).removeClass(S.active)
                    },
                    loading: function() {
                        z.removeClass(S.loading)
                    },
                    initialLoad: function() {
                        v = !1
                    },
                    upward: function(e) {
                        var t = e || z;
                        t.removeClass(S.upward)
                    },
                    visible: function() {
                        z.removeClass(S.visible)
                    },
                    activeItem: function() {
                        W.removeClass(S.active)
                    },
                    filteredItem: function() {
                        k.useLabels && C.has.maxSelections() || (k.useLabels && C.is.multiple() ? W.not("." + S.active).removeClass(S.filtered) : W.removeClass(S.filtered))
                    },
                    optionValue: function(e) {
                        var t = V.find('option[value="' + e + '"]'),
                            n = t.length > 0;
                        n && t.hasClass(S.addition) && (x && (x.disconnect(), C.verbose("Temporarily disconnecting mutation observer", e)), t.remove(), C.verbose("Removing user addition as an <option>", e), x && x.observe(V[0], {
                            childList: !0,
                            subtree: !0
                        }))
                    },
                    message: function() {
                        U.children(F.message).remove()
                    },
                    searchTerm: function() {
                        C.verbose("Cleared search term"), N.val(""), C.set.filtered()
                    },
                    selected: function(t, n) {
                        return (n = k.allowAdditions ? n || C.get.itemWithAdditions(t) : n || C.get.item(t)) ? void n.each(function() {
                            var t = e(this),
                                n = C.get.choiceText(t),
                                i = C.get.choiceValue(t, n);
                            C.is.multiple() ? k.useLabels ? (C.remove.value(i, n, t), C.remove.label(i)) : (C.remove.value(i, n, t), 0 === C.get.selectionCount() ? C.set.placeholderText() : C.set.text(C.add.variables(T.count))) : C.remove.value(i, n, t), t.removeClass(S.filtered).removeClass(S.active), k.useLabels && t.removeClass(S.selected)
                        }) : !1
                    },
                    selectedItem: function() {
                        W.removeClass(S.selected)
                    },
                    value: function(e, t, n) {
                        var i, o = C.get.values();
                        C.has.selectInput() ? (C.verbose("Input is <select> removing selected option", e), i = C.remove.arrayValue(e, o), C.remove.optionValue(e)) : (C.verbose("Removing from delimited values", e), i = C.remove.arrayValue(e, o), i = i.join(k.delimiter)), k.fireOnInit === !1 && C.is.initialLoad() ? C.verbose("No callback on initial load", k.onRemove) : k.onRemove.call($, e, t, n), C.set.value(i, t, n), C.check.maxSelections()
                    },
                    arrayValue: function(t, n) {
                        return e.isArray(n) || (n = [n]), n = e.grep(n, function(e) {
                            return t != e
                        }), C.verbose("Removed value from delimited string", t, n), n
                    },
                    label: function(e, t) {
                        var n = z.find(F.label),
                            i = n.filter('[data-value="' + e + '"]');
                        C.verbose("Removing label", i), i.remove()
                    },
                    activeLabels: function(e) {
                        e = e || z.find(F.label).filter("." + S.active), C.verbose("Removing active label selections", e), C.remove.labels(e)
                    },
                    labels: function(t) {
                        t = t || z.find(F.label), C.verbose("Removing labels", t), t.each(function() {
                            var t = e(this).data(R.value),
                                n = t !== i ? String(t) : t,
                                o = C.is.userValue(n);
                            o ? (C.remove.value(n), C.remove.label(n)) : C.remove.selected(n)
                        })
                    },
                    tabbable: function() {
                        C.has.search() ? (C.debug("Searchable dropdown initialized"), N.attr("tabindex", "-1"), U.attr("tabindex", "-1")) : (C.debug("Simple selection dropdown initialized"), z.attr("tabindex", "-1"), U.attr("tabindex", "-1"))
                    }
                },
                has: {
                    search: function() {
                        return N.length > 0
                    },
                    selectInput: function() {
                        return V.is("select")
                    },
                    firstLetter: function(e, t) {
                        var n, i;
                        return e && 0 !== e.length && "string" == typeof t ? (n = C.get.choiceText(e, !1), t = t.toLowerCase(), i = String(n).charAt(0).toLowerCase(), t == i) : !1
                    },
                    input: function() {
                        return V.length > 0
                    },
                    items: function() {
                        return W.length > 0
                    },
                    menu: function() {
                        return U.length > 0
                    },
                    message: function() {
                        return 0 !== U.children(F.message).length
                    },
                    label: function(e) {
                        var t = z.find(F.label);
                        return t.filter('[data-value="' + e + '"]').length > 0
                    },
                    maxSelections: function() {
                        return k.maxSelections && C.get.selectionCount() >= k.maxSelections
                    },
                    allResultsFiltered: function() {
                        return W.filter(F.unselectable).length === W.length
                    },
                    query: function() {
                        return "" !== C.get.query()
                    },
                    value: function(t) {
                        var n = C.get.values(),
                            i = e.isArray(n) ? n && -1 !== e.inArray(t, n) : n == t;
                        return i ? !0 : !1
                    }
                },
                is: {
                    active: function() {
                        return z.hasClass(S.active)
                    },
                    alreadySetup: function() {
                        return z.is("select") && z.parent(F.dropdown).length > 0 && 0 === z.prev().length
                    },
                    animating: function(e) {
                        return e ? e.transition && e.transition("is animating") : U.transition && U.transition("is animating")
                    },
                    disabled: function() {
                        return z.hasClass(S.disabled)
                    },
                    focused: function() {
                        return n.activeElement === z[0]
                    },
                    focusedOnSearch: function() {
                        return n.activeElement === N[0]
                    },
                    allFiltered: function() {
                        return (C.is.multiple() || C.has.search()) && !C.has.message() && C.has.allResultsFiltered()
                    },
                    hidden: function(e) {
                        return !C.is.visible(e)
                    },
                    initialLoad: function() {
                        return v
                    },
                    onScreen: function(e) {
                        var t, n = e || U,
                            i = !0,
                            o = {};
                        return n.addClass(S.loading), t = {
                            context: {
                                scrollTop: I.scrollTop(),
                                height: I.outerHeight()
                            },
                            menu: {
                                offset: n.offset(),
                                height: n.outerHeight()
                            }
                        }, o = {
                            above: t.context.scrollTop <= t.menu.offset.top - t.menu.height,
                            below: t.context.scrollTop + t.context.height >= t.menu.offset.top + t.menu.height
                        }, o.below ? (C.verbose("Dropdown can fit in context downward", o), i = !0) : o.below || o.above ? (C.verbose("Dropdown cannot fit below, opening upward", o), i = !1) : (C.verbose("Dropdown cannot fit in either direction, favoring downward", o), i = !0), n.removeClass(S.loading), i
                    },
                    inObject: function(t, n) {
                        var i = !1;
                        return e.each(n, function(e, n) {
                            return n == t ? (i = !0, !0) : void 0
                        }), i
                    },
                    multiple: function() {
                        return z.hasClass(S.multiple)
                    },
                    single: function() {
                        return !C.is.multiple()
                    },
                    selectMutation: function(t) {
                        var n = !1;
                        return e.each(t, function(t, i) {
                            return i.target && e(i.target).is("select") ? (n = !0, !0) : void 0
                        }), n
                    },
                    search: function() {
                        return z.hasClass(S.search)
                    },
                    searchSelection: function() {
                        return C.has.search() && 1 === N.parent(F.dropdown).length
                    },
                    selection: function() {
                        return z.hasClass(S.selection)
                    },
                    userValue: function(t) {
                        return -1 !== e.inArray(t, C.get.userValues())
                    },
                    upward: function(e) {
                        var t = e || z;
                        return t.hasClass(S.upward)
                    },
                    visible: function(e) {
                        return e ? e.hasClass(S.visible) : U.hasClass(S.visible)
                    }
                },
                can: {
                    activate: function(e) {
                        return k.useLabels ? !0 : C.has.maxSelections() ? C.has.maxSelections() && e.hasClass(S.active) ? !0 : !1 : !0
                    },
                    click: function() {
                        return l || "click" == k.on
                    },
                    extendSelect: function() {
                        return k.allowAdditions || k.apiSettings
                    },
                    show: function() {
                        return !C.is.disabled() && (C.has.items() || C.has.message())
                    },
                    useAPI: function() {
                        return e.fn.api !== i
                    }
                },
                animate: {
                    show: function(t, n) {
                        var o, a = n || U,
                            r = n ? function() {} : function() {
                                C.hideSubMenus(), C.hideOthers(), C.set.active()
                            };
                        t = e.isFunction(t) ? t : function() {}, C.verbose("Doing menu show animation", a), C.set.direction(n), o = C.get.transition(n), C.is.selection() && C.set.scrollPosition(C.get.selectedItem(), !0), (C.is.hidden(a) || C.is.animating(a)) && ("none" == o ? (r(), a.transition("show"), t.call($)) : e.fn.transition !== i && z.transition("is supported") ? a.transition({
                            animation: o + " in",
                            debug: k.debug,
                            verbose: k.verbose,
                            duration: k.duration,
                            queue: !0,
                            onStart: r,
                            onComplete: function() {
                                t.call($)
                            }
                        }) : C.error(D.noTransition, o))
                    },
                    hide: function(t, n) {
                        var o = n || U,
                            a = (n ? .9 * k.duration : k.duration, n ? function() {} : function() {
                                C.can.click() && C.unbind.intent(), C.remove.active()
                            }),
                            r = C.get.transition(n);
                        t = e.isFunction(t) ? t : function() {}, (C.is.visible(o) || C.is.animating(o)) && (C.verbose("Doing menu hide animation", o), "none" == r ? (a(), o.transition("hide"), t.call($)) : e.fn.transition !== i && z.transition("is supported") ? o.transition({
                            animation: r + " out",
                            duration: k.duration,
                            debug: k.debug,
                            verbose: k.verbose,
                            queue: !0,
                            onStart: a,
                            onComplete: function() {
                                "auto" == k.direction && C.remove.upward(n), t.call($)
                            }
                        }) : C.error(D.transition))
                    }
                },
                hideAndClear: function() {
                    C.remove.searchTerm(), C.has.maxSelections() || (C.has.search() ? C.hide(function() {
                        C.remove.filteredItem()
                    }) : C.hide())
                },
                delay: {
                    show: function() {
                        C.verbose("Delaying show event to ensure user intent"), clearTimeout(C.timer), C.timer = setTimeout(C.show, k.delay.show)
                    },
                    hide: function() {
                        C.verbose("Delaying hide event to ensure user intent"), clearTimeout(C.timer), C.timer = setTimeout(C.hide, k.delay.hide)
                    }
                },
                escape: {
                    regExp: function(e) {
                        return e = String(e), e.replace(E.escape, "\\$&")
                    }
                },
                setting: function(t, n) {
                    if (C.debug("Changing setting", t, n), e.isPlainObject(t)) e.extend(!0, k, t);
                    else {
                        if (n === i) return k[t];
                        k[t] = n
                    }
                },
                internal: function(t, n) {
                    if (e.isPlainObject(t)) e.extend(!0, C, t);
                    else {
                        if (n === i) return C[t];
                        C[t] = n
                    }
                },
                debug: function() {
                    k.debug && (k.performance ? C.performance.log(arguments) : (C.debug = Function.prototype.bind.call(console.info, console, k.name + ":"), C.debug.apply(console, arguments)))
                },
                verbose: function() {
                    k.verbose && k.debug && (k.performance ? C.performance.log(arguments) : (C.verbose = Function.prototype.bind.call(console.info, console, k.name + ":"), C.verbose.apply(console, arguments)))
                },
                error: function() {
                    C.error = Function.prototype.bind.call(console.error, console, k.name + ":"), C.error.apply(console, arguments)
                },
                performance: {
                    log: function(e) {
                        var t, n, i;
                        k.performance && (t = (new Date).getTime(), i = u || t, n = t - i, u = t, d.push({
                            Name: e[0],
                            Arguments: [].slice.call(e, 1) || "",
                            Element: $,
                            "Execution Time": n
                        })), clearTimeout(C.performance.timer), C.performance.timer = setTimeout(C.performance.display, 500)
                    },
                    display: function() {
                        var t = k.name + ":",
                            n = 0;
                        u = !1, clearTimeout(C.performance.timer), e.each(d, function(e, t) {
                            n += t["Execution Time"]
                        }), t += " " + n + "ms", c && (t += " '" + c + "'"), (console.group !== i || console.table !== i) && d.length > 0 && (console.groupCollapsed(t), console.table ? console.table(d) : e.each(d, function(e, t) {
                            console.log(t.Name + ": " + t["Execution Time"] + "ms")
                        }), console.groupEnd()), d = []
                    }
                },
                invoke: function(t, n, o) {
                    var r, s, c, l = Y;
                    return n = n || g, o = $ || o, "string" == typeof t && l !== i && (t = t.split(/[\. ]/), r = t.length - 1, e.each(t, function(n, o) {
                        var a = n != r ? o + t[n + 1].charAt(0).toUpperCase() + t[n + 1].slice(1) : t;
                        if (e.isPlainObject(l[a]) && n != r) l = l[a];
                        else {
                            if (l[a] !== i) return s = l[a], !1;
                            if (!e.isPlainObject(l[o]) || n == r) return l[o] !== i ? (s = l[o], !1) : (C.error(D.method, t), !1);
                            l = l[o]
                        }
                    })), e.isFunction(s) ? c = s.apply(o, n) : s !== i && (c = s), e.isArray(a) ? a.push(c) : a !== i ? a = [a, c] : c !== i && (a = c), s
                }
            }, f ? (Y === i && C.initialize(), C.invoke(m)) : (Y !== i && Y.invoke("destroy"), C.initialize())
        }), a !== i ? a : r
    }, e.fn.dropdown.settings = {
        debug: !1,
        verbose: !1,
        performance: !0,
        on: "click",
        action: "activate",
        apiSettings: !1,
        saveRemoteData: !0,
        throttle: 200,
        context: t,
        direction: "auto",
        keepOnScreen: !0,
        match: "both",
        fullTextSearch: !1,
        placeholder: "auto",
        preserveHTML: !0,
        sortSelect: !1,
        forceSelection: !0,
        allowAdditions: !1,
        maxSelections: !1,
        useLabels: !0,
        delimiter: ",",
        showOnFocus: !0,
        allowTab: !0,
        allowCategorySelection: !1,
        fireOnInit: !1,
        transition: "auto",
        duration: 200,
        glyphWidth: 1.0714,
        label: {
            transition: "scale",
            duration: 200,
            variation: !1
        },
        delay: {
            hide: 300,
            show: 200,
            search: 20,
            touch: 50
        },
        onChange: function(e, t, n) {},
        onAdd: function(e, t, n) {},
        onRemove: function(e, t, n) {},
        onLabelSelect: function(e) {},
        onLabelCreate: function(t, n) {
            return e(this)
        },
        onNoResults: function(e) {
            return !0
        },
        onShow: function() {},
        onHide: function() {},
        name: "Dropdown",
        namespace: "dropdown",
        message: {
            addResult: "Add <b>{term}</b>",
            count: "{count} selected",
            maxSelections: "Max {maxCount} selections",
            noResults: "No results found.",
            serverError: "There was an error contacting the server"
        },
        error: {
            action: "You called a dropdown action that was not defined",
            alreadySetup: "Once a select has been initialized behaviors must be called on the created ui dropdown",
            labels: "Allowing user additions currently requires the use of labels.",
            missingMultiple: "<select> requires multiple property to be set to correctly preserve multiple values",
            method: "The method you called is not defined.",
            noAPI: "The API module is required to load resources remotely",
            noStorage: "Saving remote data requires session storage",
            noTransition: "This module requires ui transitions <https://github.com/Semantic-Org/UI-Transition>"
        },
        regExp: {
            escape: /[-[\]{}()*+?.,\\^$|#\s]/g
        },
        metadata: {
            defaultText: "defaultText",
            defaultValue: "defaultValue",
            placeholderText: "placeholder",
            text: "text",
            value: "value"
        },
        fields: {
            values: "values",
            name: "name",
            value: "value"
        },
        selector: {
            addition: ".addition",
            dropdown: ".ui.dropdown",
            icon: "> .dropdown.icon",
            input: '> input[type="hidden"], > select',
            item: ".item",
            label: "> .label",
            remove: "> .label > .delete.icon",
            siblingLabel: ".label",
            menu: ".menu",
            message: ".message",
            menuIcon: ".dropdown.icon",
            search: "input.search, .menu > .search > input",
            text: "> .text:not(.icon)",
            unselectable: ".disabled, .filtered"
        },
        className: {
            active: "active",
            addition: "addition",
            animating: "animating",
            disabled: "disabled",
            dropdown: "ui dropdown",
            filtered: "filtered",
            hidden: "hidden transition",
            item: "item",
            label: "ui label",
            loading: "loading",
            menu: "menu",
            message: "message",
            multiple: "multiple",
            placeholder: "default",
            search: "search",
            selected: "selected",
            selection: "selection",
            upward: "upward",
            visible: "visible"
        }
    }, e.fn.dropdown.settings.templates = {
        dropdown: function(t) {
            var n = t.placeholder || !1,
                i = (t.values || {}, "");
            return i += '<i class="dropdown icon"></i>', i += t.placeholder ? '<div class="default text">' + n + "</div>" : '<div class="text"></div>', i += '<div class="menu">', e.each(t.values, function(e, t) {
                i += t.disabled ? '<div class="disabled item" data-value="' + t.value + '">' + t.name + "</div>" : '<div class="item" data-value="' + t.value + '">' + t.name + "</div>"
            }), i += "</div>"
        },
        menu: function(t, n) {
            var i = (t.values || {}, "");
            return e.each(t[n.values], function(e, t) {
                i += '<div class="item" data-value="' + t[n.value] + '">' + t[n.name] + "</div>"
            }), i
        },
        label: function(e, t) {
            return t + '<i class="delete icon"></i>'
        },
        message: function(e) {
            return e
        },
        addition: function(e) {
            return e
        }
    }
}(jQuery, window, document),
function(e, t, n, i) {
    "use strict";
    e.fn.modal = function(o) {
        var a, r = e(this),
            s = e(t),
            c = e(n),
            l = e("body"),
            u = r.selector || "",
            d = (new Date).getTime(),
            m = [],
            f = arguments[0],
            g = "string" == typeof f,
            p = [].slice.call(arguments, 1),
            v = t.requestAnimationFrame || t.mozRequestAnimationFrame || t.webkitRequestAnimationFrame || t.msRequestAnimationFrame || function(e) {
                setTimeout(e, 0)
            };
        return r.each(function() {
            var r, h, b, y, x, w, C, k, S, T = e.isPlainObject(o) ? e.extend(!0, {}, e.fn.modal.settings, o) : e.extend({}, e.fn.modal.settings),
                A = T.selector,
                R = T.className,
                P = T.namespace,
                E = T.error,
                F = "." + P,
                D = "module-" + P,
                O = e(this),
                q = e(T.context),
                j = O.find(A.close),
                z = this,
                I = O.data(D);
            S = {
                initialize: function() {
                    S.verbose("Initializing dimmer", q), S.create.id(), S.create.dimmer(), S.refreshModals(), S.bind.events(), T.observeChanges && S.observeChanges(), S.instantiate()
                },
                instantiate: function() {
                    S.verbose("Storing instance of modal"), I = S, O.data(D, I)
                },
                create: {
                    dimmer: function() {
                        var t = {
                                debug: T.debug,
                                dimmerName: "modals",
                                duration: {
                                    show: T.duration,
                                    hide: T.duration
                                }
                            },
                            n = e.extend(!0, t, T.dimmerSettings);
                        return T.inverted && (n.variation = n.variation !== i ? n.variation + " inverted" : "inverted"), e.fn.dimmer === i ? void S.error(E.dimmer) : (S.debug("Creating dimmer with settings", n), y = q.dimmer(n), T.detachable ? (S.verbose("Modal is detachable, moving content into dimmer"), y.dimmer("add content", O)) : S.set.undetached(), T.blurring && y.addClass(R.blurring), void(x = y.dimmer("get dimmer")))
                    },
                    id: function() {
                        C = (Math.random().toString(16) + "000000000").substr(2, 8), w = "." + C, S.verbose("Creating unique id for element", C)
                    }
                },
                destroy: function() {
                    S.verbose("Destroying previous modal"), O.removeData(D).off(F), s.off(w), j.off(F), q.dimmer("destroy")
                },
                observeChanges: function() {
                    "MutationObserver" in t && (k = new MutationObserver(function(e) {
                        S.debug("DOM tree modified, refreshing"), S.refresh()
                    }), k.observe(z, {
                        childList: !0,
                        subtree: !0
                    }), S.debug("Setting up mutation observer", k))
                },
                refresh: function() {
                    S.remove.scrolling(), S.cacheSizes(), S.set.screenHeight(), S.set.type(), S.set.position()
                },
                refreshModals: function() {
                    h = O.siblings(A.modal), r = h.add(O)
                },
                attachEvents: function(t, n) {
                    var i = e(t);
                    n = e.isFunction(S[n]) ? S[n] : S.toggle, i.length > 0 ? (S.debug("Attaching modal events to element", t, n), i.off(F).on("click" + F, n)) : S.error(E.notFound, t)
                },
                bind: {
                    events: function() {
                        S.verbose("Attaching events"), O.on("click" + F, A.close, S.event.close).on("click" + F, A.approve, S.event.approve).on("click" + F, A.deny, S.event.deny), s.on("resize" + w, S.event.resize)
                    }
                },
                get: {
                    id: function() {
                        return (Math.random().toString(16) + "000000000").substr(2, 8)
                    }
                },
                event: {
                    approve: function() {
                        return T.onApprove.call(z, e(this)) === !1 ? void S.verbose("Approve callback returned false cancelling hide") : void S.hide()
                    },
                    deny: function() {
                        return T.onDeny.call(z, e(this)) === !1 ? void S.verbose("Deny callback returned false cancelling hide") : void S.hide()
                    },
                    close: function() {
                        S.hide()
                    },
                    click: function(t) {
                        var i = e(t.target),
                            o = i.closest(A.modal).length > 0,
                            a = e.contains(n.documentElement, t.target);
                        !o && a && (S.debug("Dimmer clicked, hiding all modals"), S.is.active() && (S.remove.clickaway(), T.allowMultiple ? S.hide() : S.hideAll()))
                    },
                    debounce: function(e, t) {
                        clearTimeout(S.timer), S.timer = setTimeout(e, t)
                    },
                    keyboard: function(e) {
                        var t = e.which,
                            n = 27;
                        t == n && (T.closable ? (S.debug("Escape key pressed hiding modal"), S.hide()) : S.debug("Escape key pressed, but closable is set to false"), e.preventDefault())
                    },
                    resize: function() {
                        y.dimmer("is active") && v(S.refresh)
                    }
                },
                toggle: function() {
                    S.is.active() || S.is.animating() ? S.hide() : S.show()
                },
                show: function(t) {
                    t = e.isFunction(t) ? t : function() {}, S.refreshModals(), S.showModal(t)
                },
                hide: function(t) {
                    t = e.isFunction(t) ? t : function() {}, S.refreshModals(), S.hideModal(t)
                },
                showModal: function(t) {
                    t = e.isFunction(t) ? t : function() {}, S.is.animating() || !S.is.active() ? (S.showDimmer(), S.cacheSizes(), S.set.position(), S.set.screenHeight(), S.set.type(), S.set.clickaway(), !T.allowMultiple && S.others.active() ? S.hideOthers(S.showModal) : (T.onShow.call(z), T.transition && e.fn.transition !== i && O.transition("is supported") ? (S.debug("Showing modal with css animations"), O.transition({
                        debug: T.debug,
                        animation: T.transition + " in",
                        queue: T.queue,
                        duration: T.duration,
                        useFailSafe: !0,
                        onComplete: function() {
                            T.onVisible.apply(z), S.add.keyboardShortcuts(), S.save.focus(), S.set.active(), T.autofocus && S.set.autofocus(), t()
                        }
                    })) : S.error(E.noTransition))) : S.debug("Modal is already visible")
                },
                hideModal: function(t, n) {
                    t = e.isFunction(t) ? t : function() {}, S.debug("Hiding modal"), T.onHide.call(z), (S.is.animating() || S.is.active()) && (T.transition && e.fn.transition !== i && O.transition("is supported") ? (S.remove.active(), O.transition({
                        debug: T.debug,
                        animation: T.transition + " out",
                        queue: T.queue,
                        duration: T.duration,
                        useFailSafe: !0,
                        onStart: function() {
                            S.others.active() || n || S.hideDimmer(), S.remove.keyboardShortcuts()
                        },
                        onComplete: function() {
                            T.onHidden.call(z), S.restore.focus(), t()
                        }
                    })) : S.error(E.noTransition))
                },
                showDimmer: function() {
                    y.dimmer("is animating") || !y.dimmer("is active") ? (S.debug("Showing dimmer"), y.dimmer("show")) : S.debug("Dimmer already visible")
                },
                hideDimmer: function() {
                    return y.dimmer("is animating") || y.dimmer("is active") ? void y.dimmer("hide", function() {
                        S.remove.clickaway(), S.remove.screenHeight()
                    }) : void S.debug("Dimmer is not visible cannot hide")
                },
                hideAll: function(t) {
                    var n = r.filter("." + R.active + ", ." + R.animating);
                    t = e.isFunction(t) ? t : function() {}, n.length > 0 && (S.debug("Hiding all visible modals"), S.hideDimmer(), n.modal("hide modal", t))
                },
                hideOthers: function(t) {
                    var n = h.filter("." + R.active + ", ." + R.animating);
                    t = e.isFunction(t) ? t : function() {}, n.length > 0 && (S.debug("Hiding other modals", h), n.modal("hide modal", t, !0))
                },
                others: {
                    active: function() {
                        return h.filter("." + R.active).length > 0
                    },
                    animating: function() {
                        return h.filter("." + R.animating).length > 0
                    }
                },
                add: {
                    keyboardShortcuts: function() {
                        S.verbose("Adding keyboard shortcuts"), c.on("keyup" + F, S.event.keyboard)
                    }
                },
                save: {
                    focus: function() {
                        b = e(n.activeElement).blur()
                    }
                },
                restore: {
                    focus: function() {
                        b && b.length > 0 && b.focus()
                    }
                },
                remove: {
                    active: function() {
                        O.removeClass(R.active)
                    },
                    clickaway: function() {
                        T.closable && x.off("click" + w)
                    },
                    bodyStyle: function() {
                        "" === l.attr("style") && (S.verbose("Removing style attribute"), l.removeAttr("style"))
                    },
                    screenHeight: function() {
                        S.debug("Removing page height"), l.css("height", "")
                    },
                    keyboardShortcuts: function() {
                        S.verbose("Removing keyboard shortcuts"), c.off("keyup" + F)
                    },
                    scrolling: function() {
                        y.removeClass(R.scrolling), O.removeClass(R.scrolling)
                    }
                },
                cacheSizes: function() {
                    var o = O.outerHeight();
                    (S.cache === i || 0 !== o) && (S.cache = {
                        pageHeight: e(n).outerHeight(),
                        height: o + T.offset,
                        contextHeight: "body" == T.context ? e(t).height() : y.height()
                    }), S.debug("Caching modal and container sizes", S.cache)
                },
                can: {
                    fit: function() {
                        return S.cache.height + 2 * T.padding < S.cache.contextHeight
                    }
                },
                is: {
                    active: function() {
                        return O.hasClass(R.active)
                    },
                    animating: function() {
                        return O.transition("is supported") ? O.transition("is animating") : O.is(":visible")
                    },
                    scrolling: function() {
                        return y.hasClass(R.scrolling)
                    },
                    modernBrowser: function() {
                        return !(t.ActiveXObject || "ActiveXObject" in t)
                    }
                },
                set: {
                    autofocus: function() {
                        var e = O.find(":input").filter(":visible"),
                            t = e.filter("[autofocus]"),
                            n = t.length > 0 ? t.first() : e.first();
                        n.length > 0 && n.focus()
                    },
                    clickaway: function() {
                        T.closable && x.on("click" + w, S.event.click)
                    },
                    screenHeight: function() {
                        S.can.fit() ? l.css("height", "") : (S.debug("Modal is taller than page content, resizing page height"), l.css("height", S.cache.height + 2 * T.padding))
                    },
                    active: function() {
                        O.addClass(R.active)
                    },
                    scrolling: function() {
                        y.addClass(R.scrolling), O.addClass(R.scrolling)
                    },
                    type: function() {
                        S.can.fit() ? (S.verbose("Modal fits on screen"), S.others.active() || S.others.animating() || S.remove.scrolling()) : (S.verbose("Modal cannot fit on screen setting to scrolling"), S.set.scrolling())
                    },
                    position: function() {
                        S.verbose("Centering modal on page", S.cache), O.css(S.can.fit() ? {
                            top: "",
                            marginTop: -(S.cache.height / 2)
                        } : {
                            marginTop: "",
                            top: c.scrollTop()
                        })
                    },
                    undetached: function() {
                        y.addClass(R.undetached)
                    }
                },
                setting: function(t, n) {
                    if (S.debug("Changing setting", t, n), e.isPlainObject(t)) e.extend(!0, T, t);
                    else {
                        if (n === i) return T[t];
                        T[t] = n
                    }
                },
                internal: function(t, n) {
                    if (e.isPlainObject(t)) e.extend(!0, S, t);
                    else {
                        if (n === i) return S[t];
                        S[t] = n
                    }
                },
                debug: function() {
                    T.debug && (T.performance ? S.performance.log(arguments) : (S.debug = Function.prototype.bind.call(console.info, console, T.name + ":"), S.debug.apply(console, arguments)))
                },
                verbose: function() {
                    T.verbose && T.debug && (T.performance ? S.performance.log(arguments) : (S.verbose = Function.prototype.bind.call(console.info, console, T.name + ":"), S.verbose.apply(console, arguments)))
                },
                error: function() {
                    S.error = Function.prototype.bind.call(console.error, console, T.name + ":"), S.error.apply(console, arguments)
                },
                performance: {
                    log: function(e) {
                        var t, n, i;
                        T.performance && (t = (new Date).getTime(), i = d || t, n = t - i, d = t, m.push({
                            Name: e[0],
                            Arguments: [].slice.call(e, 1) || "",
                            Element: z,
                            "Execution Time": n
                        })), clearTimeout(S.performance.timer), S.performance.timer = setTimeout(S.performance.display, 500)
                    },
                    display: function() {
                        var t = T.name + ":",
                            n = 0;
                        d = !1, clearTimeout(S.performance.timer), e.each(m, function(e, t) {
                            n += t["Execution Time"]
                        }), t += " " + n + "ms", u && (t += " '" + u + "'"), (console.group !== i || console.table !== i) && m.length > 0 && (console.groupCollapsed(t), console.table ? console.table(m) : e.each(m, function(e, t) {
                            console.log(t.Name + ": " + t["Execution Time"] + "ms")
                        }), console.groupEnd()), m = []
                    }
                },
                invoke: function(t, n, o) {
                    var r, s, c, l = I;
                    return n = n || p, o = z || o, "string" == typeof t && l !== i && (t = t.split(/[\. ]/), r = t.length - 1, e.each(t, function(n, o) {
                        var a = n != r ? o + t[n + 1].charAt(0).toUpperCase() + t[n + 1].slice(1) : t;
                        if (e.isPlainObject(l[a]) && n != r) l = l[a];
                        else {
                            if (l[a] !== i) return s = l[a], !1;
                            if (!e.isPlainObject(l[o]) || n == r) return l[o] !== i ? (s = l[o], !1) : !1;
                            l = l[o]
                        }
                    })), e.isFunction(s) ? c = s.apply(o, n) : s !== i && (c = s), e.isArray(a) ? a.push(c) : a !== i ? a = [a, c] : c !== i && (a = c), s
                }
            }, g ? (I === i && S.initialize(), S.invoke(f)) : (I !== i && I.invoke("destroy"), S.initialize())
        }), a !== i ? a : this
    }, e.fn.modal.settings = {
        name: "Modal",
        namespace: "modal",
        debug: !1,
        verbose: !1,
        performance: !0,
        observeChanges: !1,
        allowMultiple: !1,
        detachable: !0,
        closable: !0,
        autofocus: !0,
        inverted: !1,
        blurring: !1,
        dimmerSettings: {
            closable: !1,
            useCSS: !0
        },
        context: "body",
        queue: !1,
        duration: 500,
        offset: 0,
        transition: "scale",
        padding: 50,
        onShow: function() {},
        onVisible: function() {},
        onHide: function() {},
        onHidden: function() {},
        onApprove: function() {
            return !0
        },
        onDeny: function() {
            return !0
        },
        selector: {
            close: "> .close",
            approve: ".actions .positive, .actions .approve, .actions .ok",
            deny: ".actions .negative, .actions .deny, .actions .cancel",
            modal: ".ui.modal"
        },
        error: {
            dimmer: "UI Dimmer, a required component is not included in this page",
            method: "The method you called is not defined.",
            notFound: "The element you specified could not be found"
        },
        className: {
            active: "active",
            animating: "animating",
            blurring: "blurring",
            scrolling: "scrolling",
            undetached: "undetached"
        }
    }
}(jQuery, window, document),
function(e, t, n, i) {
    "use strict";
    e.fn.nag = function(n) {
        var o, a = e(this),
            r = a.selector || "",
            s = (new Date).getTime(),
            c = [],
            l = arguments[0],
            u = "string" == typeof l,
            d = [].slice.call(arguments, 1);
        return a.each(function() {
            {
                var a, m = e.isPlainObject(n) ? e.extend(!0, {}, e.fn.nag.settings, n) : e.extend({}, e.fn.nag.settings),
                    f = (m.className, m.selector),
                    g = m.error,
                    p = m.namespace,
                    v = "." + p,
                    h = p + "-module",
                    b = e(this),
                    y = (b.find(f.close), e(m.context ? m.context : "body")),
                    x = this,
                    w = b.data(h);
                t.requestAnimationFrame || t.mozRequestAnimationFrame || t.webkitRequestAnimationFrame || t.msRequestAnimationFrame || function(e) {
                    setTimeout(e, 0)
                }
            }
            a = {
                initialize: function() {
                    a.verbose("Initializing element"), b.on("click" + v, f.close, a.dismiss).data(h, a), m.detachable && b.parent()[0] !== y[0] && b.detach().prependTo(y), m.displayTime > 0 && setTimeout(a.hide, m.displayTime), a.show()
                },
                destroy: function() {
                    a.verbose("Destroying instance"), b.removeData(h).off(v)
                },
                show: function() {
                    a.should.show() && !b.is(":visible") && (a.debug("Showing nag", m.animation.show), "fade" == m.animation.show ? b.fadeIn(m.duration, m.easing) : b.slideDown(m.duration, m.easing))
                },
                hide: function() {
                    a.debug("Showing nag", m.animation.hide), "fade" == m.animation.show ? b.fadeIn(m.duration, m.easing) : b.slideUp(m.duration, m.easing)
                },
                onHide: function() {
                    a.debug("Removing nag", m.animation.hide), b.remove(), m.onHide && m.onHide()
                },
                dismiss: function(e) {
                    m.storageMethod && a.storage.set(m.key, m.value), a.hide(), e.stopImmediatePropagation(), e.preventDefault()
                },
                should: {
                    show: function() {
                        return m.persist ? (a.debug("Persistent nag is set, can show nag"), !0) : a.storage.get(m.key) != m.value.toString() ? (a.debug("Stored value is not set, can show nag", a.storage.get(m.key)), !0) : (a.debug("Stored value is set, cannot show nag", a.storage.get(m.key)), !1)
                    }
                },
                get: {
                    storageOptions: function() {
                        var e = {};
                        return m.expires && (e.expires = m.expires), m.domain && (e.domain = m.domain), m.path && (e.path = m.path), e
                    }
                },
                clear: function() {
                    a.storage.remove(m.key)
                },
                storage: {
                    set: function(n, o) {
                        var r = a.get.storageOptions();
                        if ("localstorage" == m.storageMethod && t.localStorage !== i) t.localStorage.setItem(n, o), a.debug("Value stored using local storage", n, o);
                        else if ("sessionstorage" == m.storageMethod && t.sessionStorage !== i) t.sessionStorage.setItem(n, o), a.debug("Value stored using session storage", n, o);
                        else {
                            if (e.cookie === i) return void a.error(g.noCookieStorage);
                            e.cookie(n, o, r), a.debug("Value stored using cookie", n, o, r)
                        }
                    },
                    get: function(n, o) {
                        var r;
                        return "localstorage" == m.storageMethod && t.localStorage !== i ? r = t.localStorage.getItem(n) : "sessionstorage" == m.storageMethod && t.sessionStorage !== i ? r = t.sessionStorage.getItem(n) : e.cookie !== i ? r = e.cookie(n) : a.error(g.noCookieStorage), ("undefined" == r || "null" == r || r === i || null === r) && (r = i), r
                    },
                    remove: function(n) {
                        var o = a.get.storageOptions();
                        "localstorage" == m.storageMethod && t.localStorage !== i ? t.localStorage.removeItem(n) : "sessionstorage" == m.storageMethod && t.sessionStorage !== i ? t.sessionStorage.removeItem(n) : e.cookie !== i ? e.removeCookie(n, o) : a.error(g.noStorage)
                    }
                },
                setting: function(t, n) {
                    if (a.debug("Changing setting", t, n), e.isPlainObject(t)) e.extend(!0, m, t);
                    else {
                        if (n === i) return m[t];
                        m[t] = n
                    }
                },
                internal: function(t, n) {
                    if (e.isPlainObject(t)) e.extend(!0, a, t);
                    else {
                        if (n === i) return a[t];
                        a[t] = n
                    }
                },
                debug: function() {
                    m.debug && (m.performance ? a.performance.log(arguments) : (a.debug = Function.prototype.bind.call(console.info, console, m.name + ":"), a.debug.apply(console, arguments)))
                },
                verbose: function() {
                    m.verbose && m.debug && (m.performance ? a.performance.log(arguments) : (a.verbose = Function.prototype.bind.call(console.info, console, m.name + ":"), a.verbose.apply(console, arguments)))
                },
                error: function() {
                    a.error = Function.prototype.bind.call(console.error, console, m.name + ":"), a.error.apply(console, arguments)
                },
                performance: {
                    log: function(e) {
                        var t, n, i;
                        m.performance && (t = (new Date).getTime(), i = s || t, n = t - i, s = t, c.push({
                            Name: e[0],
                            Arguments: [].slice.call(e, 1) || "",
                            Element: x,
                            "Execution Time": n
                        })), clearTimeout(a.performance.timer), a.performance.timer = setTimeout(a.performance.display, 500)
                    },
                    display: function() {
                        var t = m.name + ":",
                            n = 0;
                        s = !1, clearTimeout(a.performance.timer), e.each(c, function(e, t) {
                            n += t["Execution Time"]
                        }), t += " " + n + "ms", r && (t += " '" + r + "'"), (console.group !== i || console.table !== i) && c.length > 0 && (console.groupCollapsed(t), console.table ? console.table(c) : e.each(c, function(e, t) {
                            console.log(t.Name + ": " + t["Execution Time"] + "ms")
                        }), console.groupEnd()), c = []
                    }
                },
                invoke: function(t, n, r) {
                    var s, c, l, u = w;
                    return n = n || d, r = x || r, "string" == typeof t && u !== i && (t = t.split(/[\. ]/), s = t.length - 1, e.each(t, function(n, o) {
                        var r = n != s ? o + t[n + 1].charAt(0).toUpperCase() + t[n + 1].slice(1) : t;
                        if (e.isPlainObject(u[r]) && n != s) u = u[r];
                        else {
                            if (u[r] !== i) return c = u[r], !1;
                            if (!e.isPlainObject(u[o]) || n == s) return u[o] !== i ? (c = u[o], !1) : (a.error(g.method, t), !1);
                            u = u[o]
                        }
                    })), e.isFunction(c) ? l = c.apply(r, n) : c !== i && (l = c), e.isArray(o) ? o.push(l) : o !== i ? o = [o, l] : l !== i && (o = l), c
                }
            }, u ? (w === i && a.initialize(), a.invoke(l)) : (w !== i && w.invoke("destroy"), a.initialize())
        }), o !== i ? o : this
    }, e.fn.nag.settings = {
        name: "Nag",
        debug: !1,
        verbose: !1,
        performance: !0,
        namespace: "Nag",
        persist: !1,
        displayTime: 0,
        animation: {
            show: "slide",
            hide: "slide"
        },
        context: !1,
        detachable: !1,
        expires: 30,
        domain: !1,
        path: "/",
        storageMethod: "cookie",
        key: "nag",
        value: "dismiss",
        error: {
            noCookieStorage: "$.cookie is not included. A storage solution is required.",
            noStorage: "Neither $.cookie or store is defined. A storage solution is required for storing state",
            method: "The method you called is not defined."
        },
        className: {
            bottom: "bottom",
            fixed: "fixed"
        },
        selector: {
            close: ".close.icon"
        },
        speed: 500,
        easing: "easeOutQuad",
        onHide: function() {}
    }
}(jQuery, window, document),
function(e, t, n, i) {
    "use strict";
    e.fn.popup = function(o) {
        var a, r = e(this),
            s = e(n),
            c = e(t),
            l = e("body"),
            u = r.selector || "",
            d = !0,
            m = (new Date).getTime(),
            f = [],
            g = arguments[0],
            p = "string" == typeof g,
            v = [].slice.call(arguments, 1);
        return r.each(function() {
            var n, r, h, b, y, x = e.isPlainObject(o) ? e.extend(!0, {}, e.fn.popup.settings, o) : e.extend({}, e.fn.popup.settings),
                w = x.selector,
                C = x.className,
                k = x.error,
                S = x.metadata,
                T = x.namespace,
                A = "." + x.namespace,
                R = "module-" + T,
                P = e(this),
                E = e(x.context),
                F = x.target ? e(x.target) : P,
                D = 0,
                O = !1,
                q = !1,
                j = this,
                z = P.data(R);
            y = {
                initialize: function() {
                    y.debug("Initializing", P), y.createID(), y.bind.events(), !y.exists() && x.preserve && y.create(), y.instantiate()
                },
                instantiate: function() {
                    y.verbose("Storing instance", y), z = y, P.data(R, z)
                },
                refresh: function() {
                    x.popup ? n = e(x.popup).eq(0) : x.inline && (n = F.nextAll(w.popup).eq(0), x.popup = n), x.popup ? (n.addClass(C.loading), r = y.get.offsetParent(), n.removeClass(C.loading), x.movePopup && y.has.popup() && y.get.offsetParent(n)[0] !== r[0] && (y.debug("Moving popup to the same offset parent as activating element"), n.detach().appendTo(r))) : r = x.inline ? y.get.offsetParent(F) : y.has.popup() ? y.get.offsetParent(n) : l, r.is("html") && r[0] !== l[0] && (y.debug("Setting page as offset parent"), r = l), y.get.variation() && y.set.variation()
                },
                reposition: function() {
                    y.refresh(), y.set.position()
                },
                destroy: function() {
                    y.debug("Destroying previous module"), n && !x.preserve && y.removePopup(), clearTimeout(y.hideTimer), clearTimeout(y.showTimer), c.off(h), P.off(A).removeData(R)
                },
                event: {
                    start: function(t) {
                        var n = e.isPlainObject(x.delay) ? x.delay.show : x.delay;
                        clearTimeout(y.hideTimer), q || (y.showTimer = setTimeout(y.show, n))
                    },
                    end: function() {
                        var t = e.isPlainObject(x.delay) ? x.delay.hide : x.delay;
                        clearTimeout(y.showTimer), y.hideTimer = setTimeout(y.hide, t)
                    },
                    touchstart: function(e) {
                        q = !0, y.show()
                    },
                    resize: function() {
                        y.is.visible() && y.set.position()
                    },
                    hideGracefully: function(t) {
                        t && 0 === e(t.target).closest(w.popup).length ? (y.debug("Click occurred outside popup hiding popup"), y.hide()) : y.debug("Click was inside popup, keeping popup open")
                    }
                },
                create: function() {
                    var t = y.get.html(),
                        i = y.get.title(),
                        o = y.get.content();
                    t || o || i ? (y.debug("Creating pop-up html"), t || (t = x.templates.popup({
                        title: i,
                        content: o
                    })), n = e("<div/>").addClass(C.popup).data(S.activator, P).html(t), x.inline ? (y.verbose("Inserting popup element inline", n), n.insertAfter(P)) : (y.verbose("Appending popup element to body", n), n.appendTo(E)), y.refresh(), y.set.variation(), x.hoverable && y.bind.popup(), x.onCreate.call(n, j)) : 0 !== F.next(w.popup).length ? (y.verbose("Pre-existing popup found"), x.inline = !0, x.popups = F.next(w.popup).data(S.activator, P), y.refresh(), x.hoverable && y.bind.popup()) : x.popup ? (e(x.popup).data(S.activator, P), y.verbose("Used popup specified in settings"), y.refresh(), x.hoverable && y.bind.popup()) : y.debug("No content specified skipping display", j)
                },
                createID: function() {
                    b = (Math.random().toString(16) + "000000000").substr(2, 8), h = "." + b, y.verbose("Creating unique id for element", b)
                },
                toggle: function() {
                    y.debug("Toggling pop-up"), y.is.hidden() ? (y.debug("Popup is hidden, showing pop-up"), y.unbind.close(), y.show()) : (y.debug("Popup is visible, hiding pop-up"), y.hide())
                },
                show: function(e) {
                    if (e = e || function() {}, y.debug("Showing pop-up", x.transition), y.is.hidden() && (!y.is.active() || !y.is.dropdown())) {
                        if (y.exists() || y.create(), x.onShow.call(n, j) === !1) return void y.debug("onShow callback returned false, cancelling popup animation");
                        x.preserve || x.popup || y.refresh(), n && y.set.position() && (y.save.conditions(), x.exclusive && y.hideAll(), y.animate.show(e))
                    }
                },
                hide: function(e) {
                    if (e = e || function() {}, y.is.visible() || y.is.animating()) {
                        if (x.onHide.call(n, j) === !1) return void y.debug("onHide callback returned false, cancelling popup animation");
                        y.remove.visible(), y.unbind.close(), y.restore.conditions(), y.animate.hide(e)
                    }
                },
                hideAll: function() {
                    e(w.popup).filter("." + C.visible).each(function() {
                        e(this).data(S.activator).popup("hide")
                    })
                },
                exists: function() {
                    return n ? x.inline || x.popup ? y.has.popup() : n.closest(E).length >= 1 ? !0 : !1 : !1
                },
                removePopup: function() {
                    y.has.popup() && !x.popup && (y.debug("Removing popup", n), n.remove(), n = i, x.onRemove.call(n, j))
                },
                save: {
                    conditions: function() {
                        y.cache = {
                            title: P.attr("title")
                        }, y.cache.title && P.removeAttr("title"), y.verbose("Saving original attributes", y.cache.title)
                    }
                },
                restore: {
                    conditions: function() {
                        return y.cache && y.cache.title && (P.attr("title", y.cache.title), y.verbose("Restoring original attributes", y.cache.title)), !0
                    }
                },
                animate: {
                    show: function(t) {
                        t = e.isFunction(t) ? t : function() {}, x.transition && e.fn.transition !== i && P.transition("is supported") ? (y.set.visible(), n.transition({
                            animation: x.transition + " in",
                            queue: !1,
                            debug: x.debug,
                            verbose: x.verbose,
                            duration: x.duration,
                            onComplete: function() {
                                y.bind.close(), t.call(n, j), x.onVisible.call(n, j)
                            }
                        })) : y.error(k.noTransition)
                    },
                    hide: function(t) {
                        return t = e.isFunction(t) ? t : function() {}, y.debug("Hiding pop-up"), x.onHide.call(n, j) === !1 ? void y.debug("onHide callback returned false, cancelling popup animation") : void(x.transition && e.fn.transition !== i && P.transition("is supported") ? n.transition({
                            animation: x.transition + " out",
                            queue: !1,
                            duration: x.duration,
                            debug: x.debug,
                            verbose: x.verbose,
                            onComplete: function() {
                                y.reset(), t.call(n, j), x.onHidden.call(n, j)
                            }
                        }) : y.error(k.noTransition))
                    }
                },
                get: {
                    html: function() {
                        return P.removeData(S.html), P.data(S.html) || x.html
                    },
                    title: function() {
                        return P.removeData(S.title), P.data(S.title) || x.title
                    },
                    content: function() {
                        return P.removeData(S.content), P.data(S.content) || P.attr("title") || x.content
                    },
                    variation: function() {
                        return P.removeData(S.variation), P.data(S.variation) || x.variation
                    },
                    popupOffset: function() {
                        return n.offset()
                    },
                    calculations: function() {
                        var e, i = F[0],
                            o = x.inline || x.popup ? F.position() : F.offset(),
                            a = {};
                        return a = {
                            target: {
                                element: F[0],
                                width: F.outerWidth(),
                                height: F.outerHeight(),
                                top: o.top,
                                left: o.left,
                                margin: {}
                            },
                            popup: {
                                width: n.outerWidth(),
                                height: n.outerHeight()
                            },
                            parent: {
                                width: r.outerWidth(),
                                height: r.outerHeight()
                            },
                            screen: {
                                scroll: {
                                    top: c.scrollTop(),
                                    left: c.scrollLeft()
                                },
                                width: c.width(),
                                height: c.height()
                            }
                        }, x.setFluidWidth && y.is.fluid() && (a.container = {
                            width: n.parent().outerWidth()
                        }, a.popup.width = a.container.width), a.target.margin.top = x.inline ? parseInt(t.getComputedStyle(i).getPropertyValue("margin-top"), 10) : 0, a.target.margin.left = x.inline ? y.is.rtl() ? parseInt(t.getComputedStyle(i).getPropertyValue("margin-right"), 10) : parseInt(t.getComputedStyle(i).getPropertyValue("margin-left"), 10) : 0, e = a.screen, a.boundary = {
                            top: e.scroll.top,
                            bottom: e.scroll.top + e.height,
                            left: e.scroll.left,
                            right: e.scroll.left + e.width
                        }, a
                    },
                    id: function() {
                        return b
                    },
                    startEvent: function() {
                        return "hover" == x.on ? "mouseenter" : "focus" == x.on ? "focus" : !1
                    },
                    scrollEvent: function() {
                        return "scroll"
                    },
                    endEvent: function() {
                        return "hover" == x.on ? "mouseleave" : "focus" == x.on ? "blur" : !1
                    },
                    distanceFromBoundary: function(e, t) {
                        var n, i, o = {};
                        return e = e || y.get.offset(), t = t || y.get.calculations(), n = t.popup, i = t.boundary, e && (o = {
                            top: e.top - i.top,
                            left: e.left - i.left,
                            right: i.right - (e.left + n.width),
                            bottom: i.bottom - (e.top + n.height)
                        }, y.verbose("Distance from boundaries determined", e, o)), o
                    },
                    offsetParent: function(t) {
                        var n = t !== i ? t[0] : P[0],
                            o = n.parentNode,
                            a = e(o);
                        if (o)
                            for (var r = "none" === a.css("transform"), s = "static" === a.css("position"), c = a.is("html"); o && !c && s && r;) o = o.parentNode, a = e(o), r = "none" === a.css("transform"), s = "static" === a.css("position"), c = a.is("html");
                        return a && a.length > 0 ? a : e()
                    },
                    positions: function() {
                        return {
                            "top left": !1,
                            "top center": !1,
                            "top right": !1,
                            "bottom left": !1,
                            "bottom center": !1,
                            "bottom right": !1,
                            "left center": !1,
                            "right center": !1
                        }
                    },
                    nextPosition: function(e) {
                        var t = e.split(" "),
                            n = t[0],
                            i = t[1],
                            o = {
                                top: "bottom",
                                bottom: "top",
                                left: "right",
                                right: "left"
                            },
                            a = {
                                left: "center",
                                center: "right",
                                right: "left"
                            },
                            r = {
                                "top left": "top center",
                                "top center": "top right",
                                "top right": "right center",
                                "right center": "bottom right",
                                "bottom right": "bottom center",
                                "bottom center": "bottom left",
                                "bottom left": "left center",
                                "left center": "top left"
                            },
                            s = "top" == n || "bottom" == n,
                            c = !1,
                            l = !1,
                            u = !1;
                        return O || (y.verbose("All available positions available"), O = y.get.positions()), y.debug("Recording last position tried", e), O[e] = !0, "opposite" === x.prefer && (u = [o[n], i], u = u.join(" "), c = O[u] === !0, y.debug("Trying opposite strategy", u)), "adjacent" === x.prefer && s && (u = [n, a[i]], u = u.join(" "), l = O[u] === !0, y.debug("Trying adjacent strategy", u)), (l || c) && (y.debug("Using backup position", u), u = r[e]), u
                    }
                },
                set: {
                    position: function(e, t) {
                        if (0 === F.length || 0 === n.length) return void y.error(k.notFound);
                        var o, a, r, s, c, l, u, d;
                        if (t = t || y.get.calculations(), e = e || P.data(S.position) || x.position, o = P.data(S.offset) || x.offset, a = x.distanceAway, r = t.target, s = t.popup, c = t.parent, 0 === r.width && 0 === r.height) return y.debug("Popup target is hidden, no action taken"), !1;
                        switch (x.inline && (y.debug("Adding margin to calculation", r.margin), "left center" == e || "right center" == e ? (o += r.margin.top, a += -r.margin.left) : "top left" == e || "top center" == e || "top right" == e ? (o += r.margin.left, a -= r.margin.top) : (o += r.margin.left, a += r.margin.top)), y.debug("Determining popup position from calculations", e, t), y.is.rtl() && (e = e.replace(/left|right/g, function(e) {
                            return "left" == e ? "right" : "left"
                        }), y.debug("RTL: Popup position updated", e)), D == x.maxSearchDepth && "string" == typeof x.lastResort && (e = x.lastResort), e) {
                            case "top left":
                                l = {
                                    top: "auto",
                                    bottom: c.height - r.top + a,
                                    left: r.left + o,
                                    right: "auto"
                                };
                                break;
                            case "top center":
                                l = {
                                    bottom: c.height - r.top + a,
                                    left: r.left + r.width / 2 - s.width / 2 + o,
                                    top: "auto",
                                    right: "auto"
                                };
                                break;
                            case "top right":
                                l = {
                                    bottom: c.height - r.top + a,
                                    right: c.width - r.left - r.width - o,
                                    top: "auto",
                                    left: "auto"
                                };
                                break;
                            case "left center":
                                l = {
                                    top: r.top + r.height / 2 - s.height / 2 + o,
                                    right: c.width - r.left + a,
                                    left: "auto",
                                    bottom: "auto"
                                };
                                break;
                            case "right center":
                                l = {
                                    top: r.top + r.height / 2 - s.height / 2 + o,
                                    left: r.left + r.width + a,
                                    bottom: "auto",
                                    right: "auto"
                                };
                                break;
                            case "bottom left":
                                l = {
                                    top: r.top + r.height + a,
                                    left: r.left + o,
                                    bottom: "auto",
                                    right: "auto"
                                };
                                break;
                            case "bottom center":
                                l = {
                                    top: r.top + r.height + a,
                                    left: r.left + r.width / 2 - s.width / 2 + o,
                                    bottom: "auto",
                                    right: "auto"
                                };
                                break;
                            case "bottom right":
                                l = {
                                    top: r.top + r.height + a,
                                    right: c.width - r.left - r.width - o,
                                    left: "auto",
                                    bottom: "auto"
                                }
                        }
                        if (l === i && y.error(k.invalidPosition, e), y.debug("Calculated popup positioning values", l), n.css(l).removeClass(C.position).addClass(e).addClass(C.loading), u = y.get.popupOffset(), d = y.get.distanceFromBoundary(u, t), y.is.offstage(d, e)) {
                            if (y.debug("Position is outside viewport", e), D < x.maxSearchDepth) return D++, e = y.get.nextPosition(e), y.debug("Trying new position", e), n ? y.set.position(e, t) : !1;
                            if (!x.lastResort) return y.debug("Popup could not find a position to display", n), y.error(k.cannotPlace, j), y.remove.attempts(), y.remove.loading(), y.reset(), !1;
                            y.debug("No position found, showing with last position")
                        }
                        return y.debug("Position is on stage", e), y.remove.attempts(), y.remove.loading(), x.setFluidWidth && y.is.fluid() && y.set.fluidWidth(t), !0
                    },
                    fluidWidth: function(e) {
                        e = e || y.get.calculations(), y.debug("Automatically setting element width to parent width", e.parent.width), n.css("width", e.container.width)
                    },
                    variation: function(e) {
                        e = e || y.get.variation(), e && y.has.popup() && (y.verbose("Adding variation to popup", e), n.addClass(e))
                    },
                    visible: function() {
                        P.addClass(C.visible)
                    }
                },
                remove: {
                    loading: function() {
                        n.removeClass(C.loading)
                    },
                    variation: function(e) {
                        e = e || y.get.variation(), e && (y.verbose("Removing variation", e), n.removeClass(e))
                    },
                    visible: function() {
                        P.removeClass(C.visible)
                    },
                    attempts: function() {
                        y.verbose("Resetting all searched positions"), D = 0, O = !1
                    }
                },
                bind: {
                    events: function() {
                        y.debug("Binding popup events to module"), "click" == x.on && P.on("click" + A, y.toggle), "hover" == x.on && d && P.on("touchstart" + A, y.event.touchstart), y.get.startEvent() && P.on(y.get.startEvent() + A, y.event.start).on(y.get.endEvent() + A, y.event.end), x.target && y.debug("Target set to element", F), c.on("resize" + h, y.event.resize)
                    },
                    popup: function() {
                        y.verbose("Allowing hover events on popup to prevent closing"), n && y.has.popup() && n.on("mouseenter" + A, y.event.start).on("mouseleave" + A, y.event.end)
                    },
                    close: function() {
                        (x.hideOnScroll === !0 || "auto" == x.hideOnScroll && "click" != x.on) && (s.one(y.get.scrollEvent() + h, y.event.hideGracefully), E.one(y.get.scrollEvent() + h, y.event.hideGracefully)), "hover" == x.on && q && (y.verbose("Binding popup close event to document"), s.on("touchstart" + h, function(e) {
                            y.verbose("Touched away from popup"), y.event.hideGracefully.call(j, e)
                        })), "click" == x.on && x.closable && (y.verbose("Binding popup close event to document"), s.on("click" + h, function(e) {
                            y.verbose("Clicked away from popup"), y.event.hideGracefully.call(j, e)
                        }))
                    }
                },
                unbind: {
                    close: function() {
                        (x.hideOnScroll === !0 || "auto" == x.hideOnScroll && "click" != x.on) && (s.off("scroll" + h, y.hide), E.off("scroll" + h, y.hide)), "hover" == x.on && q && (s.off("touchstart" + h), q = !1), "click" == x.on && x.closable && (y.verbose("Removing close event from document"), s.off("click" + h))
                    }
                },
                has: {
                    popup: function() {
                        return n && n.length > 0
                    }
                },
                is: {
                    offstage: function(t, n) {
                        var i = [];
                        return e.each(t, function(e, t) {
                            t < -x.jitter && (y.debug("Position exceeds allowable distance from edge", e, t, n), i.push(e))
                        }), i.length > 0 ? !0 : !1
                    },
                    active: function() {
                        return P.hasClass(C.active)
                    },
                    animating: function() {
                        return n && n.hasClass(C.animating)
                    },
                    fluid: function() {
                        return n && n.hasClass(C.fluid)
                    },
                    visible: function() {
                        return n && n.hasClass(C.visible)
                    },
                    dropdown: function() {
                        return P.hasClass(C.dropdown)
                    },
                    hidden: function() {
                        return !y.is.visible()
                    },
                    rtl: function() {
                        return "rtl" == P.css("direction")
                    }
                },
                reset: function() {
                    y.remove.visible(), x.preserve ? e.fn.transition !== i && n.transition("remove transition") : y.removePopup()
                },
                setting: function(t, n) {
                    if (e.isPlainObject(t)) e.extend(!0, x, t);
                    else {
                        if (n === i) return x[t];
                        x[t] = n
                    }
                },
                internal: function(t, n) {
                    if (e.isPlainObject(t)) e.extend(!0, y, t);
                    else {
                        if (n === i) return y[t];
                        y[t] = n
                    }
                },
                debug: function() {
                    x.debug && (x.performance ? y.performance.log(arguments) : (y.debug = Function.prototype.bind.call(console.info, console, x.name + ":"), y.debug.apply(console, arguments)))
                },
                verbose: function() {
                    x.verbose && x.debug && (x.performance ? y.performance.log(arguments) : (y.verbose = Function.prototype.bind.call(console.info, console, x.name + ":"), y.verbose.apply(console, arguments)))
                },
                error: function() {
                    y.error = Function.prototype.bind.call(console.error, console, x.name + ":"), y.error.apply(console, arguments)
                },
                performance: {
                    log: function(e) {
                        var t, n, i;
                        x.performance && (t = (new Date).getTime(), i = m || t, n = t - i, m = t, f.push({
                            Name: e[0],
                            Arguments: [].slice.call(e, 1) || "",
                            Element: j,
                            "Execution Time": n
                        })), clearTimeout(y.performance.timer), y.performance.timer = setTimeout(y.performance.display, 500)
                    },
                    display: function() {
                        var t = x.name + ":",
                            n = 0;
                        m = !1, clearTimeout(y.performance.timer), e.each(f, function(e, t) {
                            n += t["Execution Time"]
                        }), t += " " + n + "ms", u && (t += " '" + u + "'"), (console.group !== i || console.table !== i) && f.length > 0 && (console.groupCollapsed(t), console.table ? console.table(f) : e.each(f, function(e, t) {
                            console.log(t.Name + ": " + t["Execution Time"] + "ms")
                        }), console.groupEnd()), f = []
                    }
                },
                invoke: function(t, n, o) {
                    var r, s, c, l = z;
                    return n = n || v, o = j || o, "string" == typeof t && l !== i && (t = t.split(/[\. ]/), r = t.length - 1, e.each(t, function(n, o) {
                        var a = n != r ? o + t[n + 1].charAt(0).toUpperCase() + t[n + 1].slice(1) : t;
                        if (e.isPlainObject(l[a]) && n != r) l = l[a];
                        else {
                            if (l[a] !== i) return s = l[a], !1;
                            if (!e.isPlainObject(l[o]) || n == r) return l[o] !== i ? (s = l[o], !1) : !1;
                            l = l[o]
                        }
                    })), e.isFunction(s) ? c = s.apply(o, n) : s !== i && (c = s), e.isArray(a) ? a.push(c) : a !== i ? a = [a, c] : c !== i && (a = c), s
                }
            }, p ? (z === i && y.initialize(), y.invoke(g)) : (z !== i && z.invoke("destroy"), y.initialize())
        }), a !== i ? a : this
    }, e.fn.popup.settings = {
        name: "Popup",
        debug: !1,
        verbose: !1,
        performance: !0,
        namespace: "popup",
        onCreate: function() {},
        onRemove: function() {},
        onShow: function() {},
        onVisible: function() {},
        onHide: function() {},
        onHidden: function() {},
        on: "hover",
        addTouchEvents: !0,
        position: "top left",
        variation: "",
        movePopup: !0,
        target: !1,
        popup: !1,
        inline: !1,
        preserve: !1,
        hoverable: !1,
        content: !1,
        html: !1,
        title: !1,
        closable: !0,
        hideOnScroll: "auto",
        exclusive: !1,
        context: "body",
        prefer: "opposite",
        lastResort: !1,
        delay: {
            show: 50,
            hide: 70
        },
        setFluidWidth: !0,
        duration: 200,
        transition: "scale",
        distanceAway: 0,
        jitter: 2,
        offset: 0,
        maxSearchDepth: 15,
        error: {
            invalidPosition: "The position you specified is not a valid position",
            cannotPlace: "Popup does not fit within the boundaries of the viewport",
            method: "The method you called is not defined.",
            noTransition: "This module requires ui transitions <https://github.com/Semantic-Org/UI-Transition>",
            notFound: "The target or popup you specified does not exist on the page"
        },
        metadata: {
            activator: "activator",
            content: "content",
            html: "html",
            offset: "offset",
            position: "position",
            title: "title",
            variation: "variation"
        },
        className: {
            active: "active",
            animating: "animating",
            dropdown: "dropdown",
            fluid: "fluid",
            loading: "loading",
            popup: "ui popup",
            position: "top left center bottom right",
            visible: "visible"
        },
        selector: {
            popup: ".ui.popup"
        },
        templates: {
            escape: function(e) {
                var t = /[&<>"'`]/g,
                    n = /[&<>"'`]/,
                    i = {
                        "&": "&amp;",
                        "<": "&lt;",
                        ">": "&gt;",
                        '"': "&quot;",
                        "'": "&#x27;",
                        "`": "&#x60;"
                    },
                    o = function(e) {
                        return i[e]
                    };
                return n.test(e) ? e.replace(t, o) : e
            },
            popup: function(t) {
                var n = "",
                    o = e.fn.popup.settings.templates.escape;
                return typeof t !== i && (typeof t.title !== i && t.title && (t.title = o(t.title), n += '<div class="header">' + t.title + "</div>"), typeof t.content !== i && t.content && (t.content = o(t.content), n += '<div class="content">' + t.content + "</div>")), n
            }
        }
    }
}(jQuery, window, document),
function(e, t, n, i) {
    "use strict";
    e.fn.progress = function(t) {
        var o, a = e(this),
            r = a.selector || "",
            s = (new Date).getTime(),
            c = [],
            l = arguments[0],
            u = "string" == typeof l,
            d = [].slice.call(arguments, 1);
        return a.each(function() {
            var a, m, f = e.isPlainObject(t) ? e.extend(!0, {}, e.fn.progress.settings, t) : e.extend({}, e.fn.progress.settings),
                g = f.className,
                p = f.metadata,
                v = f.namespace,
                h = f.selector,
                b = f.error,
                y = "." + v,
                x = "module-" + v,
                w = e(this),
                C = e(this).find(h.bar),
                k = e(this).find(h.progress),
                S = e(this).find(h.label),
                T = this,
                A = w.data(x),
                R = !1;
            m = {
                initialize: function() {
                    m.debug("Initializing progress bar", f), m.set.duration(), m.set.transitionEvent(), m.read.metadata(), m.read.settings(), m.instantiate()
                },
                instantiate: function() {
                    m.verbose("Storing instance of progress", m), A = m, w.data(x, m)
                },
                destroy: function() {
                    m.verbose("Destroying previous progress for", w), clearInterval(A.interval), m.remove.state(), w.removeData(x), A = i
                },
                reset: function() {
                    m.set.percent(0)
                },
                complete: function() {
                    (m.percent === i || m.percent < 100) && m.set.percent(100)
                },
                read: {
                    metadata: function() {
                        var e = {
                            percent: w.data(p.percent),
                            total: w.data(p.total),
                            value: w.data(p.value)
                        };
                        e.percent && (m.debug("Current percent value set from metadata", e.percent), m.set.percent(e.percent)), e.total && (m.debug("Total value set from metadata", e.total), m.set.total(e.total)), e.value && (m.debug("Current value set from metadata", e.value), m.set.value(e.value), m.set.progress(e.value))
                    },
                    settings: function() {
                        f.total !== !1 && (m.debug("Current total set in settings", f.total), m.set.total(f.total)), f.value !== !1 && (m.debug("Current value set in settings", f.value), m.set.value(f.value), m.set.progress(m.value)), f.percent !== !1 && (m.debug("Current percent set in settings", f.percent), m.set.percent(f.percent))
                    }
                },
                increment: function(e) {
                    var t, n, i;
                    m.has.total() ? (n = m.get.value(), e = e || 1, i = n + e, t = m.get.total(), m.debug("Incrementing value", n, i, t), i > t && (m.debug("Value cannot increment above total", t), i = t)) : (n = m.get.percent(), e = e || m.get.randomValue(), i = n + e, t = 100, m.debug("Incrementing percentage by", n, i), i > t && (m.debug("Value cannot increment above 100 percent"), i = t)), m.set.progress(i)
                },
                decrement: function(e) {
                    var t, n, i = m.get.total();
                    i ? (t = m.get.value(), e = e || 1, n = t - e, m.debug("Decrementing value by", e, t)) : (t = m.get.percent(), e = e || m.get.randomValue(), n = t - e, m.debug("Decrementing percentage by", e, t)), 0 > n && (m.debug("Value cannot decrement below 0"), n = 0), m.set.progress(n)
                },
                has: {
                    total: function() {
                        return m.get.total() !== !1
                    }
                },
                get: {
                    text: function(e) {
                        var t = m.value || 0,
                            n = m.total || 0,
                            i = R ? m.get.displayPercent() : m.percent || 0,
                            o = m.total > 0 ? n - t : 100 - i;
                        return e = e || "", e = e.replace("{value}", t).replace("{total}", n).replace("{left}", o).replace("{percent}", i), m.debug("Adding variables to progress bar text", e), e
                    },
                    randomValue: function() {
                        return m.debug("Generating random increment percentage"), Math.floor(Math.random() * f.random.max + f.random.min)
                    },
                    numericValue: function(e) {
                        return "string" == typeof e ? "" !== e.replace(/[^\d.]/g, "") ? +e.replace(/[^\d.]/g, "") : !1 : e
                    },
                    transitionEnd: function() {
                        var e, t = n.createElement("element"),
                            o = {
                                transition: "transitionend",
                                OTransition: "oTransitionEnd",
                                MozTransition: "transitionend",
                                WebkitTransition: "webkitTransitionEnd"
                            };
                        for (e in o)
                            if (t.style[e] !== i) return o[e]
                    },
                    displayPercent: function() {
                        var e = C.width(),
                            t = w.width(),
                            n = parseInt(C.css("min-width"), 10),
                            i = e > n ? e / t * 100 : m.percent;
                        return f.precision > 0 ? Math.round(10 * i * f.precision) / (10 * f.precision) : Math.round(i)
                    },
                    percent: function() {
                        return m.percent || 0
                    },
                    value: function() {
                        return m.value || 0
                    },
                    total: function() {
                        return m.total || !1
                    }
                },
                is: {
                    success: function() {
                        return w.hasClass(g.success)
                    },
                    warning: function() {
                        return w.hasClass(g.warning)
                    },
                    error: function() {
                        return w.hasClass(g.error)
                    },
                    active: function() {
                        return w.hasClass(g.active)
                    },
                    visible: function() {
                        return w.is(":visible")
                    }
                },
                remove: {
                    state: function() {
                        m.verbose("Removing stored state"), delete m.total, delete m.percent, delete m.value
                    },
                    active: function() {
                        m.verbose("Removing active state"), w.removeClass(g.active)
                    },
                    success: function() {
                        m.verbose("Removing success state"), w.removeClass(g.success)
                    },
                    warning: function() {
                        m.verbose("Removing warning state"), w.removeClass(g.warning)
                    },
                    error: function() {
                        m.verbose("Removing error state"), w.removeClass(g.error)
                    }
                },
                set: {
                    barWidth: function(e) {
                        e > 100 ? m.error(b.tooHigh, e) : 0 > e ? m.error(b.tooLow, e) : (C.css("width", e + "%"), w.attr("data-percent", parseInt(e, 10)))
                    },
                    duration: function(e) {
                        e = e || f.duration, e = "number" == typeof e ? e + "ms" : e, m.verbose("Setting progress bar transition duration", e), C.css({
                            "transition-duration": e
                        })
                    },
                    percent: function(e) {
                        e = "string" == typeof e ? +e.replace("%", "") : e, e = f.precision > 0 ? Math.round(10 * e * f.precision) / (10 * f.precision) : Math.round(e), m.percent = e, m.has.total() || (m.value = f.precision > 0 ? Math.round(e / 100 * m.total * 10 * f.precision) / (10 * f.precision) : Math.round(e / 100 * m.total * 10) / 10, f.limitValues && (m.value = m.value > 100 ? 100 : m.value < 0 ? 0 : m.value)), m.set.barWidth(e), m.set.labelInterval(), m.set.labels(), f.onChange.call(T, e, m.value, m.total)
                    },
                    labelInterval: function() {
                        var e = function() {
                            m.verbose("Bar finished animating, removing continuous label updates"), clearInterval(m.interval), R = !1, m.set.labels()
                        };
                        clearInterval(m.interval), C.one(a + y, e), m.timer = setTimeout(e, f.duration + 100), R = !0, m.interval = setInterval(m.set.labels, f.framerate)
                    },
                    labels: function() {
                        m.verbose("Setting both bar progress and outer label text"), m.set.barLabel(), m.set.state()
                    },
                    label: function(e) {
                        e = e || "", e && (e = m.get.text(e), m.debug("Setting label to text", e), S.text(e))
                    },
                    state: function(e) {
                        e = e !== i ? e : m.percent, 100 === e ? !f.autoSuccess || m.is.warning() || m.is.error() ? (m.verbose("Reached 100% removing active state"), m.remove.active()) : (m.set.success(), m.debug("Automatically triggering success at 100%")) : e > 0 ? (m.verbose("Adjusting active progress bar label", e), m.set.active()) : (m.remove.active(), m.set.label(f.text.active))
                    },
                    barLabel: function(e) {
                        e !== i ? k.text(m.get.text(e)) : "ratio" == f.label && m.total ? (m.debug("Adding ratio to bar label"), k.text(m.get.text(f.text.ratio))) : "percent" == f.label && (m.debug("Adding percentage to bar label"), k.text(m.get.text(f.text.percent)))
                    },
                    active: function(e) {
                        e = e || f.text.active, m.debug("Setting active state"), f.showActivity && !m.is.active() && w.addClass(g.active), m.remove.warning(), m.remove.error(), m.remove.success(), e && m.set.label(e), f.onActive.call(T, m.value, m.total)
                    },
                    success: function(e) {
                        e = e || f.text.success, m.debug("Setting success state"), w.addClass(g.success), m.remove.active(), m.remove.warning(), m.remove.error(), m.complete(), e && m.set.label(e), f.onSuccess.call(T, m.total)
                    },
                    warning: function(e) {
                        e = e || f.text.warning, m.debug("Setting warning state"), w.addClass(g.warning), m.remove.active(), m.remove.success(), m.remove.error(), m.complete(), e && m.set.label(e), f.onWarning.call(T, m.value, m.total)
                    },
                    error: function(e) {
                        e = e || f.text.error, m.debug("Setting error state"), w.addClass(g.error), m.remove.active(), m.remove.success(), m.remove.warning(), m.complete(), e && m.set.label(e), f.onError.call(T, m.value, m.total)
                    },
                    transitionEvent: function() {
                        a = m.get.transitionEnd()
                    },
                    total: function(e) {
                        m.total = e
                    },
                    value: function(e) {
                        m.value = e
                    },
                    progress: function(e) {
                        var t, n = m.get.numericValue(e);
                        n === !1 && m.error(b.nonNumeric, e), m.has.total() ? (m.set.value(n), t = n / m.total * 100, m.debug("Calculating percent complete from total", t), m.set.percent(t)) : (t = n, m.debug("Setting value to exact percentage value", t), m.set.percent(t))
                    }
                },
                setting: function(t, n) {
                    if (m.debug("Changing setting", t, n), e.isPlainObject(t)) e.extend(!0, f, t);
                    else {
                        if (n === i) return f[t];
                        f[t] = n
                    }
                },
                internal: function(t, n) {
                    if (e.isPlainObject(t)) e.extend(!0, m, t);
                    else {
                        if (n === i) return m[t];
                        m[t] = n
                    }
                },
                debug: function() {
                    f.debug && (f.performance ? m.performance.log(arguments) : (m.debug = Function.prototype.bind.call(console.info, console, f.name + ":"), m.debug.apply(console, arguments)))
                },
                verbose: function() {
                    f.verbose && f.debug && (f.performance ? m.performance.log(arguments) : (m.verbose = Function.prototype.bind.call(console.info, console, f.name + ":"), m.verbose.apply(console, arguments)))
                },
                error: function() {
                    m.error = Function.prototype.bind.call(console.error, console, f.name + ":"), m.error.apply(console, arguments)
                },
                performance: {
                    log: function(e) {
                        var t, n, i;
                        f.performance && (t = (new Date).getTime(), i = s || t, n = t - i, s = t, c.push({
                            Name: e[0],
                            Arguments: [].slice.call(e, 1) || "",
                            Element: T,
                            "Execution Time": n
                        })), clearTimeout(m.performance.timer), m.performance.timer = setTimeout(m.performance.display, 500)
                    },
                    display: function() {
                        var t = f.name + ":",
                            n = 0;
                        s = !1, clearTimeout(m.performance.timer), e.each(c, function(e, t) {
                            n += t["Execution Time"]
                        }), t += " " + n + "ms", r && (t += " '" + r + "'"), (console.group !== i || console.table !== i) && c.length > 0 && (console.groupCollapsed(t), console.table ? console.table(c) : e.each(c, function(e, t) {
                            console.log(t.Name + ": " + t["Execution Time"] + "ms")
                        }), console.groupEnd()), c = []
                    }
                },
                invoke: function(t, n, a) {
                    var r, s, c, l = A;
                    return n = n || d, a = T || a, "string" == typeof t && l !== i && (t = t.split(/[\. ]/), r = t.length - 1, e.each(t, function(n, o) {
                        var a = n != r ? o + t[n + 1].charAt(0).toUpperCase() + t[n + 1].slice(1) : t;
                        if (e.isPlainObject(l[a]) && n != r) l = l[a];
                        else {
                            if (l[a] !== i) return s = l[a], !1;
                            if (!e.isPlainObject(l[o]) || n == r) return l[o] !== i ? (s = l[o], !1) : (m.error(b.method, t), !1);
                            l = l[o]
                        }
                    })), e.isFunction(s) ? c = s.apply(a, n) : s !== i && (c = s), e.isArray(o) ? o.push(c) : o !== i ? o = [o, c] : c !== i && (o = c), s
                }
            }, u ? (A === i && m.initialize(), m.invoke(l)) : (A !== i && A.invoke("destroy"), m.initialize())
        }), o !== i ? o : this
    }, e.fn.progress.settings = {
        name: "Progress",
        namespace: "progress",
        debug: !1,
        verbose: !1,
        performance: !0,
        random: {
            min: 2,
            max: 5
        },
        duration: 300,
        autoSuccess: !0,
        showActivity: !0,
        limitValues: !0,
        label: "percent",
        precision: 0,
        framerate: 1e3 / 30,
        percent: !1,
        total: !1,
        value: !1,
        onChange: function(e, t, n) {},
        onSuccess: function(e) {},
        onActive: function(e, t) {},
        onError: function(e, t) {},
        onWarning: function(e, t) {},
        error: {
            method: "The method you called is not defined.",
            nonNumeric: "Progress value is non numeric",
            tooHigh: "Value specified is above 100%",
            tooLow: "Value specified is below 0%"
        },
        regExp: {
            variable: /\{\$*[A-z0-9]+\}/g
        },
        metadata: {
            percent: "percent",
            total: "total",
            value: "value"
        },
        selector: {
            bar: "> .bar",
            label: "> .label",
            progress: ".bar > .progress"
        },
        text: {
            active: !1,
            error: !1,
            success: !1,
            warning: !1,
            percent: "{percent}%",
            ratio: "{value} of {total}"
        },
        className: {
            active: "active",
            error: "error",
            success: "success",
            warning: "warning"
        }
    }
}(jQuery, window, document),
function(e, t, n, i) {
    "use strict";
    e.fn.rating = function(t) {
        var n, o = e(this),
            a = o.selector || "",
            r = (new Date).getTime(),
            s = [],
            c = arguments[0],
            l = "string" == typeof c,
            u = [].slice.call(arguments, 1);
        return o.each(function() {
            var d, m = e.isPlainObject(t) ? e.extend(!0, {}, e.fn.rating.settings, t) : e.extend({}, e.fn.rating.settings),
                f = m.namespace,
                g = m.className,
                p = m.metadata,
                v = m.selector,
                h = (m.error, "." + f),
                b = "module-" + f,
                y = this,
                x = e(this).data(b),
                w = e(this),
                C = w.find(v.icon);
            d = {
                initialize: function() {
                    d.verbose("Initializing rating module", m), 0 === C.length && d.setup.layout(), m.interactive ? d.enable() : d.disable(), d.set.rating(d.get.initialRating()), d.instantiate()
                },
                instantiate: function() {
                    d.verbose("Instantiating module", m), x = d, w.data(b, d)
                },
                destroy: function() {
                    d.verbose("Destroying previous instance", x), d.remove.events(), w.removeData(b)
                },
                refresh: function() {
                    C = w.find(v.icon)
                },
                setup: {
                    layout: function() {
                        var t = d.get.maxRating(),
                            n = e.fn.rating.settings.templates.icon(t);
                        d.debug("Generating icon html dynamically"), w.html(n), d.refresh()
                    }
                },
                event: {
                    mouseenter: function() {
                        var t = e(this);
                        t.nextAll().removeClass(g.selected), w.addClass(g.selected), t.addClass(g.selected).prevAll().addClass(g.selected)
                    },
                    mouseleave: function() {
                        w.removeClass(g.selected), C.removeClass(g.selected)
                    },
                    click: function() {
                        var t = e(this),
                            n = d.get.rating(),
                            i = C.index(t) + 1,
                            o = "auto" == m.clearable ? 1 === C.length : m.clearable;
                        o && n == i ? d.clearRating() : d.set.rating(i)
                    }
                },
                clearRating: function() {
                    d.debug("Clearing current rating"), d.set.rating(0)
                },
                bind: {
                    events: function() {
                        d.verbose("Binding events"), w.on("mouseenter" + h, v.icon, d.event.mouseenter).on("mouseleave" + h, v.icon, d.event.mouseleave).on("click" + h, v.icon, d.event.click)
                    }
                },
                remove: {
                    events: function() {
                        d.verbose("Removing events"), w.off(h)
                    }
                },
                enable: function() {
                    d.debug("Setting rating to interactive mode"), d.bind.events(), w.removeClass(g.disabled)
                },
                disable: function() {
                    d.debug("Setting rating to read-only mode"), d.remove.events(), w.addClass(g.disabled)
                },
                get: {
                    initialRating: function() {
                        return w.data(p.rating) !== i ? (w.removeData(p.rating), w.data(p.rating)) : m.initialRating
                    },
                    maxRating: function() {
                        return w.data(p.maxRating) !== i ? (w.removeData(p.maxRating), w.data(p.maxRating)) : m.maxRating
                    },
                    rating: function() {
                        var e = C.filter("." + g.active).length;
                        return d.verbose("Current rating retrieved", e), e
                    }
                },
                set: {
                    rating: function(e) {
                        var t = e - 1 >= 0 ? e - 1 : 0,
                            n = C.eq(t);
                        w.removeClass(g.selected), C.removeClass(g.selected).removeClass(g.active), e > 0 && (d.verbose("Setting current rating to", e), n.prevAll().andSelf().addClass(g.active)), m.onRate.call(y, e)
                    }
                },
                setting: function(t, n) {
                    if (d.debug("Changing setting", t, n), e.isPlainObject(t)) e.extend(!0, m, t);
                    else {
                        if (n === i) return m[t];
                        m[t] = n
                    }
                },
                internal: function(t, n) {
                    if (e.isPlainObject(t)) e.extend(!0, d, t);
                    else {
                        if (n === i) return d[t];
                        d[t] = n
                    }
                },
                debug: function() {
                    m.debug && (m.performance ? d.performance.log(arguments) : (d.debug = Function.prototype.bind.call(console.info, console, m.name + ":"), d.debug.apply(console, arguments)))
                },
                verbose: function() {
                    m.verbose && m.debug && (m.performance ? d.performance.log(arguments) : (d.verbose = Function.prototype.bind.call(console.info, console, m.name + ":"), d.verbose.apply(console, arguments)))
                },
                error: function() {
                    d.error = Function.prototype.bind.call(console.error, console, m.name + ":"), d.error.apply(console, arguments)
                },
                performance: {
                    log: function(e) {
                        var t, n, i;
                        m.performance && (t = (new Date).getTime(), i = r || t, n = t - i, r = t, s.push({
                            Name: e[0],
                            Arguments: [].slice.call(e, 1) || "",
                            Element: y,
                            "Execution Time": n
                        })), clearTimeout(d.performance.timer), d.performance.timer = setTimeout(d.performance.display, 500)
                    },
                    display: function() {
                        var t = m.name + ":",
                            n = 0;
                        r = !1, clearTimeout(d.performance.timer), e.each(s, function(e, t) {
                            n += t["Execution Time"]
                        }), t += " " + n + "ms", a && (t += " '" + a + "'"), o.length > 1 && (t += " (" + o.length + ")"), (console.group !== i || console.table !== i) && s.length > 0 && (console.groupCollapsed(t), console.table ? console.table(s) : e.each(s, function(e, t) {
                            console.log(t.Name + ": " + t["Execution Time"] + "ms")
                        }), console.groupEnd()), s = []
                    }
                },
                invoke: function(t, o, a) {
                    var r, s, c, l = x;
                    return o = o || u, a = y || a, "string" == typeof t && l !== i && (t = t.split(/[\. ]/), r = t.length - 1, e.each(t, function(n, o) {
                        var a = n != r ? o + t[n + 1].charAt(0).toUpperCase() + t[n + 1].slice(1) : t;
                        if (e.isPlainObject(l[a]) && n != r) l = l[a];
                        else {
                            if (l[a] !== i) return s = l[a], !1;
                            if (!e.isPlainObject(l[o]) || n == r) return l[o] !== i ? (s = l[o], !1) : !1;
                            l = l[o]
                        }
                    })), e.isFunction(s) ? c = s.apply(a, o) : s !== i && (c = s), e.isArray(n) ? n.push(c) : n !== i ? n = [n, c] : c !== i && (n = c), s
                }
            }, l ? (x === i && d.initialize(), d.invoke(c)) : (x !== i && x.invoke("destroy"), d.initialize())
        }), n !== i ? n : this
    }, e.fn.rating.settings = {
        name: "Rating",
        namespace: "rating",
        debug: !1,
        verbose: !1,
        performance: !0,
        initialRating: 0,
        interactive: !0,
        maxRating: 4,
        clearable: "auto",
        onRate: function(e) {},
        error: {
            method: "The method you called is not defined",
            noMaximum: "No maximum rating specified. Cannot generate HTML automatically"
        },
        metadata: {
            rating: "rating",
            maxRating: "maxRating"
        },
        className: {
            active: "active",
            disabled: "disabled",
            selected: "selected",
            loading: "loading"
        },
        selector: {
            icon: ".icon"
        },
        templates: {
            icon: function(e) {
                for (var t = 1, n = ""; e >= t;) n += '<i class="icon"></i>', t++;
                return n
            }
        }
    }
}(jQuery, window, document),
function(e, t, n, i) {
    "use strict";
    e.fn.search = function(o) {
        var a, r = e(this),
            s = r.selector || "",
            c = (new Date).getTime(),
            l = [],
            u = arguments[0],
            d = "string" == typeof u,
            m = [].slice.call(arguments, 1);
        return e(this).each(function() {
            var f, g = e.isPlainObject(o) ? e.extend(!0, {}, e.fn.search.settings, o) : e.extend({}, e.fn.search.settings),
                p = g.className,
                v = g.metadata,
                h = g.regExp,
                b = g.fields,
                y = g.selector,
                x = g.error,
                w = g.namespace,
                C = "." + w,
                k = w + "-module",
                S = e(this),
                T = S.find(y.prompt),
                A = S.find(y.searchButton),
                R = S.find(y.results),
                P = (S.find(y.result), S.find(y.category), this),
                E = S.data(k);
            f = {
                initialize: function() {
                    f.verbose("Initializing module"), f.determine.searchFields(), f.bind.events(), f.set.type(), f.create.results(), f.instantiate()
                },
                instantiate: function() {
                    f.verbose("Storing instance of module", f), E = f, S.data(k, f)
                },
                destroy: function() {
                    f.verbose("Destroying instance"), S.off(C).removeData(k)
                },
                bind: {
                    events: function() {
                        f.verbose("Binding events to search"), g.automatic && (S.on(f.get.inputEvent() + C, y.prompt, f.event.input), T.attr("autocomplete", "off")), S.on("focus" + C, y.prompt, f.event.focus).on("blur" + C, y.prompt, f.event.blur).on("keydown" + C, y.prompt, f.handleKeyboard).on("click" + C, y.searchButton, f.query).on("mousedown" + C, y.results, f.event.result.mousedown).on("mouseup" + C, y.results, f.event.result.mouseup).on("click" + C, y.result, f.event.result.click)
                    }
                },
                determine: {
                    searchFields: function() {
                        o && o.searchFields !== i && (g.searchFields = o.searchFields)
                    }
                },
                event: {
                    input: function() {
                        clearTimeout(f.timer), f.timer = setTimeout(f.query, g.searchDelay)
                    },
                    focus: function() {
                        f.set.focus(), f.has.minimumCharacters() && (f.query(), f.can.show() && f.showResults())
                    },
                    blur: function(e) {
                        var t = n.activeElement === this;
                        t || f.resultsClicked || (f.cancel.query(), f.remove.focus(), f.timer = setTimeout(f.hideResults, g.hideDelay))
                    },
                    result: {
                        mousedown: function() {
                            f.resultsClicked = !0
                        },
                        mouseup: function() {
                            f.resultsClicked = !1
                        },
                        click: function(n) {
                            f.debug("Search result selected");
                            var i = e(this),
                                o = i.find(y.title).eq(0),
                                a = i.find("a[href]").eq(0),
                                r = a.attr("href") || !1,
                                s = a.attr("target") || !1,
                                c = (o.html(), o.length > 0 ? o.text() : !1),
                                l = f.get.results(),
                                u = i.data(v.result) || f.get.result(c, l);
                            return e.isFunction(g.onSelect) && g.onSelect.call(P, u, l) === !1 ? void f.debug("Custom onSelect callback cancelled default select action") : (f.hideResults(), c && f.set.value(c), void(r && (f.verbose("Opening search link found in result", a), "_blank" == s || n.ctrlKey ? t.open(r) : t.location.href = r)))
                        }
                    }
                },
                handleKeyboard: function(e) {
                    var t, n = S.find(y.result),
                        i = S.find(y.category),
                        o = n.index(n.filter("." + p.active)),
                        a = n.length,
                        r = e.which,
                        s = {
                            backspace: 8,
                            enter: 13,
                            escape: 27,
                            upArrow: 38,
                            downArrow: 40
                        };
                    if (r == s.escape && (f.verbose("Escape key pressed, blurring search field"), T.trigger("blur")), f.is.visible())
                        if (r == s.enter) {
                            if (f.verbose("Enter key pressed, selecting active result"), n.filter("." + p.active).length > 0) return f.event.result.click.call(n.filter("." + p.active), e), e.preventDefault(), !1
                        } else r == s.upArrow ? (f.verbose("Up key pressed, changing active result"), t = 0 > o - 1 ? o : o - 1, i.removeClass(p.active), n.removeClass(p.active).eq(t).addClass(p.active).closest(i).addClass(p.active), e.preventDefault()) : r == s.downArrow && (f.verbose("Down key pressed, changing active result"), t = o + 1 >= a ? o : o + 1, i.removeClass(p.active), n.removeClass(p.active).eq(t).addClass(p.active).closest(i).addClass(p.active), e.preventDefault());
                    else r == s.enter && (f.verbose("Enter key pressed, executing query"), f.query(), f.set.buttonPressed(), T.one("keyup", f.remove.buttonFocus))
                },
                setup: {
                    api: function() {
                        var e = {
                            debug: g.debug,
                            on: !1,
                            cache: "local",
                            action: "search",
                            onError: f.error
                        };
                        f.verbose("First request, initializing API"), S.api(e)
                    }
                },
                can: {
                    useAPI: function() {
                        return e.fn.api !== i
                    },
                    show: function() {
                        return f.is.focused() && !f.is.visible() && !f.is.empty()
                    },
                    transition: function() {
                        return g.transition && e.fn.transition !== i && S.transition("is supported")
                    }
                },
                is: {
                    empty: function() {
                        return "" === R.html()
                    },
                    visible: function() {
                        return R.filter(":visible").length > 0
                    },
                    focused: function() {
                        return T.filter(":focus").length > 0
                    }
                },
                get: {
                    inputEvent: function() {
                        var e = T[0],
                            t = e !== i && e.oninput !== i ? "input" : e !== i && e.onpropertychange !== i ? "propertychange" : "keyup";
                        return t
                    },
                    value: function() {
                        return T.val()
                    },
                    results: function() {
                        var e = S.data(v.results);
                        return e
                    },
                    result: function(t, n) {
                        var o = ["title", "id"],
                            a = !1;
                        return t = t !== i ? t : f.get.value(), n = n !== i ? n : f.get.results(), "category" === g.type ? (f.debug("Finding result that matches", t), e.each(n, function(n, i) {
                            return e.isArray(i.results) && (a = f.search.object(t, i.results, o)[0]) ? !1 : void 0
                        })) : (f.debug("Finding result in results object", t), a = f.search.object(t, n, o)[0]), a || !1
                    }
                },
                set: {
                    focus: function() {
                        S.addClass(p.focus)
                    },
                    loading: function() {
                        S.addClass(p.loading)
                    },
                    value: function(e) {
                        f.verbose("Setting search input value", e), T.val(e)
                    },
                    type: function(e) {
                        e = e || g.type, "category" == g.type && S.addClass(g.type)
                    },
                    buttonPressed: function() {
                        A.addClass(p.pressed)
                    }
                },
                remove: {
                    loading: function() {
                        S.removeClass(p.loading)
                    },
                    focus: function() {
                        S.removeClass(p.focus)
                    },
                    buttonPressed: function() {
                        A.removeClass(p.pressed)
                    }
                },
                query: function() {
                    var t = f.get.value(),
                        n = f.read.cache(t);
                    f.has.minimumCharacters() ? n ? (f.debug("Reading result from cache", t), f.save.results(n.results), f.addResults(n.html), f.inject.id(n.results)) : (f.debug("Querying for", t), e.isPlainObject(g.source) || e.isArray(g.source) ? f.search.local(t) : f.can.useAPI() ? f.search.remote(t) : f.error(x.source), g.onSearchQuery.call(P, t)) : f.hideResults()
                },
                search: {
                    local: function(e) {
                        var t, n = f.search.object(e, g.content);
                        f.set.loading(), f.save.results(n), f.debug("Returned local search results", n), t = f.generateResults({
                            results: n
                        }), f.remove.loading(), f.addResults(t), f.inject.id(n), f.write.cache(e, {
                            html: t,
                            results: n
                        })
                    },
                    remote: function(t) {
                        var n = {
                            onSuccess: function(e) {
                                f.parse.response.call(P, e, t)
                            },
                            onFailure: function() {
                                f.displayMessage(x.serverError)
                            },
                            urlData: {
                                query: t
                            }
                        };
                        S.api("get request") || f.setup.api(), e.extend(!0, n, g.apiSettings), f.debug("Executing search", n), f.cancel.query(), S.api("setting", n).api("query")
                    },
                    object: function(t, n, o) {
                        var a = [],
                            r = [],
                            s = t.toString().replace(h.escape, "\\$&"),
                            c = new RegExp(h.beginsWith + s, "i"),
                            l = function(t, n) {
                                var i = -1 == e.inArray(n, a),
                                    o = -1 == e.inArray(n, r);
                                i && o && t.push(n)
                            };
                        return n = n || g.source, o = o !== i ? o : g.searchFields, e.isArray(o) || (o = [o]), n === i || n === !1 ? (f.error(x.source), []) : (e.each(o, function(i, o) {
                            e.each(n, function(e, n) {
                                var i = "string" == typeof n[o];
                                i && (-1 !== n[o].search(c) ? l(a, n) : g.searchFullText && f.fuzzySearch(t, n[o]) && l(r, n))
                            })
                        }), e.merge(a, r))
                    }
                },
                fuzzySearch: function(e, t) {
                    var n = t.length,
                        i = e.length;
                    if ("string" != typeof e) return !1;
                    if (e = e.toLowerCase(), t = t.toLowerCase(), i > n) return !1;
                    if (i === n) return e === t;
                    e: for (var o = 0, a = 0; i > o; o++) {
                        for (var r = e.charCodeAt(o); n > a;)
                            if (t.charCodeAt(a++) === r) continue e;
                        return !1
                    }
                    return !0
                },
                parse: {
                    response: function(e, t) {
                        var n = f.generateResults(e);
                        f.verbose("Parsing server response", e), e !== i && t !== i && e[b.results] !== i && (f.addResults(n), f.inject.id(e[b.results]), f.write.cache(t, {
                            html: n,
                            results: e[b.results]
                        }), f.save.results(e[b.results]))
                    }
                },
                cancel: {
                    query: function() {
                        f.can.useAPI() && S.api("abort")
                    }
                },
                has: {
                    minimumCharacters: function() {
                        var e = f.get.value(),
                            t = e.length;
                        return t >= g.minCharacters
                    }
                },
                clear: {
                    cache: function(e) {
                        var t = S.data(v.cache);
                        e ? e && t && t[e] && (f.debug("Removing value from cache", e), delete t[e], S.data(v.cache, t)) : (f.debug("Clearing cache", e), S.removeData(v.cache))
                    }
                },
                read: {
                    cache: function(e) {
                        var t = S.data(v.cache);
                        return g.cache ? (f.verbose("Checking cache for generated html for query", e), "object" == typeof t && t[e] !== i ? t[e] : !1) : !1
                    }
                },
                create: {
                    id: function(e, t) {
                        var n, o, a = e + 1;
                        return t !== i ? (n = String.fromCharCode(97 + t), o = n + a, f.verbose("Creating category result id", o)) : (o = a, f.verbose("Creating result id", o)), o
                    },
                    results: function() {
                        0 === R.length && (R = e("<div />").addClass(p.results).appendTo(S))
                    }
                },
                inject: {
                    result: function(e, t, n) {
                        f.verbose("Injecting result into results");
                        var o = n !== i ? R.children().eq(n).children(y.result).eq(t) : R.children(y.result).eq(t);
                        f.verbose("Injecting results metadata", o), o.data(v.result, e)
                    },
                    id: function(t) {
                        f.debug("Injecting unique ids into results");
                        var n = 0,
                            o = 0;
                        return "category" === g.type ? e.each(t, function(t, a) {
                            o = 0, e.each(a.results, function(e, t) {
                                var r = a.results[e];
                                r.id === i && (r.id = f.create.id(o, n)), f.inject.result(r, o, n), o++
                            }), n++
                        }) : e.each(t, function(e, n) {
                            var a = t[e];
                            a.id === i && (a.id = f.create.id(o)), f.inject.result(a, o), o++
                        }), t
                    }
                },
                save: {
                    results: function(e) {
                        f.verbose("Saving current search results to metadata", e), S.data(v.results, e)
                    }
                },
                write: {
                    cache: function(e, t) {
                        var n = S.data(v.cache) !== i ? S.data(v.cache) : {};
                        g.cache && (f.verbose("Writing generated html to cache", e, t), n[e] = t, S.data(v.cache, n))
                    }
                },
                addResults: function(t) {
                    return e.isFunction(g.onResultsAdd) && g.onResultsAdd.call(R, t) === !1 ? (f.debug("onResultsAdd callback cancelled default action"), !1) : (R.html(t), void(f.can.show() && f.showResults()))
                },
                showResults: function() {
                    f.is.visible() || (f.can.transition() ? (f.debug("Showing results with css animations"), R.transition({
                        animation: g.transition + " in",
                        debug: g.debug,
                        verbose: g.verbose,
                        duration: g.duration,
                        queue: !0
                    })) : (f.debug("Showing results with javascript"), R.stop().fadeIn(g.duration, g.easing)), g.onResultsOpen.call(R))
                },
                hideResults: function() {
                    f.is.visible() && (f.can.transition() ? (f.debug("Hiding results with css animations"), R.transition({
                        animation: g.transition + " out",
                        debug: g.debug,
                        verbose: g.verbose,
                        duration: g.duration,
                        queue: !0
                    })) : (f.debug("Hiding results with javascript"), R.stop().fadeOut(g.duration, g.easing)), g.onResultsClose.call(R))
                },
                generateResults: function(t) {
                    f.debug("Generating html from response", t);
                    var n = g.templates[g.type],
                        i = e.isPlainObject(t[b.results]) && !e.isEmptyObject(t[b.results]),
                        o = e.isArray(t[b.results]) && t[b.results].length > 0,
                        a = "";
                    return i || o ? (g.maxResults > 0 && (i ? "standard" == g.type && f.error(x.maxResults) : t[b.results] = t[b.results].slice(0, g.maxResults)), e.isFunction(n) ? a = n(t, b) : f.error(x.noTemplate, !1)) : a = f.displayMessage(x.noResults, "empty"), g.onResults.call(P, t), a
                },
                displayMessage: function(e, t) {
                    return t = t || "standard", f.debug("Displaying message", e, t), f.addResults(g.templates.message(e, t)), g.templates.message(e, t)
                },
                setting: function(t, n) {
                    if (e.isPlainObject(t)) e.extend(!0, g, t);
                    else {
                        if (n === i) return g[t];
                        g[t] = n
                    }
                },
                internal: function(t, n) {
                    if (e.isPlainObject(t)) e.extend(!0, f, t);
                    else {
                        if (n === i) return f[t];
                        f[t] = n
                    }
                },
                debug: function() {
                    g.debug && (g.performance ? f.performance.log(arguments) : (f.debug = Function.prototype.bind.call(console.info, console, g.name + ":"), f.debug.apply(console, arguments)))
                },
                verbose: function() {
                    g.verbose && g.debug && (g.performance ? f.performance.log(arguments) : (f.verbose = Function.prototype.bind.call(console.info, console, g.name + ":"), f.verbose.apply(console, arguments)))
                },
                error: function() {
                    f.error = Function.prototype.bind.call(console.error, console, g.name + ":"), f.error.apply(console, arguments)
                },
                performance: {
                    log: function(e) {
                        var t, n, i;
                        g.performance && (t = (new Date).getTime(), i = c || t, n = t - i, c = t, l.push({
                            Name: e[0],
                            Arguments: [].slice.call(e, 1) || "",
                            Element: P,
                            "Execution Time": n
                        })), clearTimeout(f.performance.timer), f.performance.timer = setTimeout(f.performance.display, 500)
                    },
                    display: function() {
                        var t = g.name + ":",
                            n = 0;
                        c = !1, clearTimeout(f.performance.timer), e.each(l, function(e, t) {
                            n += t["Execution Time"]
                        }), t += " " + n + "ms", s && (t += " '" + s + "'"), r.length > 1 && (t += " (" + r.length + ")"), (console.group !== i || console.table !== i) && l.length > 0 && (console.groupCollapsed(t), console.table ? console.table(l) : e.each(l, function(e, t) {
                            console.log(t.Name + ": " + t["Execution Time"] + "ms")
                        }), console.groupEnd()), l = []
                    }
                },
                invoke: function(t, n, o) {
                    var r, s, c, l = E;
                    return n = n || m, o = P || o, "string" == typeof t && l !== i && (t = t.split(/[\. ]/), r = t.length - 1, e.each(t, function(n, o) {
                        var a = n != r ? o + t[n + 1].charAt(0).toUpperCase() + t[n + 1].slice(1) : t;
                        if (e.isPlainObject(l[a]) && n != r) l = l[a];
                        else {
                            if (l[a] !== i) return s = l[a], !1;
                            if (!e.isPlainObject(l[o]) || n == r) return l[o] !== i ? (s = l[o], !1) : !1;
                            l = l[o]
                        }
                    })), e.isFunction(s) ? c = s.apply(o, n) : s !== i && (c = s), e.isArray(a) ? a.push(c) : a !== i ? a = [a, c] : c !== i && (a = c), s
                }
            }, d ? (E === i && f.initialize(), f.invoke(u)) : (E !== i && E.invoke("destroy"), f.initialize())
        }), a !== i ? a : this
    }, e.fn.search.settings = {
        name: "Search",
        namespace: "search",
        debug: !1,
        verbose: !1,
        performance: !0,
        type: "standard",
        minCharacters: 1,
        apiSettings: !1,
        source: !1,
        searchFields: ["title", "description"],
        displayField: "",
        searchFullText: !0,
        automatic: !0,
        hideDelay: 0,
        searchDelay: 200,
        maxResults: 7,
        cache: !0,
        transition: "scale",
        duration: 200,
        easing: "easeOutExpo",
        onSelect: !1,
        onResultsAdd: !1,
        onSearchQuery: function(e) {},
        onResults: function(e) {},
        onResultsOpen: function() {},
        onResultsClose: function() {},
        className: {
            active: "active",
            empty: "empty",
            focus: "focus",
            loading: "loading",
            results: "results",
            pressed: "down"
        },
        error: {
            source: "Cannot search. No source used, and Semantic API module was not included",
            noResults: "Your search returned no results",
            logging: "Error in debug logging, exiting.",
            noEndpoint: "No search endpoint was specified",
            noTemplate: "A valid template name was not specified.",
            serverError: "There was an issue querying the server.",
            maxResults: "Results must be an array to use maxResults setting",
            method: "The method you called is not defined."
        },
        metadata: {
            cache: "cache",
            results: "results",
            result: "result"
        },
        regExp: {
            escape: /[\-\[\]\/\{\}\(\)\*\+\?\.\\\^\$\|]/g,
            beginsWith: "(?:s|^)"
        },
        fields: {
            categories: "results",
            categoryName: "name",
            categoryResults: "results",
            description: "description",
            image: "image",
            price: "price",
            results: "results",
            title: "title",
            action: "action",
            actionText: "text",
            actionURL: "url"
        },
        selector: {
            prompt: ".prompt",
            searchButton: ".search.button",
            results: ".results",
            category: ".category",
            result: ".result",
            title: ".title, .name"
        },
        templates: {
            escape: function(e) {
                var t = /[&<>"'`]/g,
                    n = /[&<>"'`]/,
                    i = {
                        "&": "&amp;",
                        "<": "&lt;",
                        ">": "&gt;",
                        '"': "&quot;",
                        "'": "&#x27;",
                        "`": "&#x60;"
                    },
                    o = function(e) {
                        return i[e]
                    };
                return n.test(e) ? e.replace(t, o) : e
            },
            message: function(e, t) {
                var n = "";
                return e !== i && t !== i && (n += '<div class="message ' + t + '">', n += "empty" == t ? '<div class="header">No Results</div class="header"><div class="description">' + e + '</div class="description">' : ' <div class="description">' + e + "</div>", n += "</div>"), n
            },
            category: function(t, n) {
                {
                    var o = "";
                    e.fn.search.settings.templates.escape
                }
                return t[n.categoryResults] !== i ? (e.each(t[n.categoryResults], function(a, r) {
                    r[n.results] !== i && r.results.length > 0 && (o += '<div class="category">', r[n.categoryName] !== i && (o += '<div class="name">' + r[n.categoryName] + "</div>"), e.each(r.results, function(e, a) {
                        o += t[n.url] ? '<a class="result" href="' + t[n.url] + '">' : '<a class="result">', a[n.image] !== i && (o += '<div class="image"> <img src="' + a[n.image] + '"></div>'), o += '<div class="content">', a[n.price] !== i && (o += '<div class="price">' + a[n.price] + "</div>"), a[n.title] !== i && (o += '<div class="title">' + a[n.title] + "</div>"), a[n.description] !== i && (o += '<div class="description">' + a[n.description] + "</div>"), o += "</div>", o += "</a>"
                    }), o += "</div>")
                }), t[n.action] && (o += '<a href="' + t[n.action][n.actionURL] + '" class="action">' + t[n.action][n.actionText] + "</a>"), o) : !1
            },
            standard: function(t, n) {
                var o = "";
                return t[n.results] !== i ? (e.each(t[n.results], function(e, a) {
                    o += t[n.url] ? '<a class="result" href="' + t[n.url] + '">' : '<a class="result">', a[n.image] !== i && (o += '<div class="image"> <img src="' + a[n.image] + '"></div>'), o += '<div class="content">', a[n.price] !== i && (o += '<div class="price">' + a[n.price] + "</div>"), a[n.title] !== i && (o += '<div class="title">' + a[n.title] + "</div>"), a[n.description] !== i && (o += '<div class="description">' + a[n.description] + "</div>"), o += "</div>", o += "</a>"
                }), t[n.action] && (o += '<a href="' + t[n.action][n.actionURL] + '" class="action">' + t[n.action][n.actionText] + "</a>"), o) : !1
            }
        }
    }
}(jQuery, window, document),
function(e, t, n, i) {
    "use strict";
    e.fn.shape = function(o) {
        var a, r = e(this),
            s = (e("body"), (new Date).getTime()),
            c = [],
            l = arguments[0],
            u = "string" == typeof l,
            d = [].slice.call(arguments, 1),
            m = t.requestAnimationFrame || t.mozRequestAnimationFrame || t.webkitRequestAnimationFrame || t.msRequestAnimationFrame || function(e) {
                setTimeout(e, 0)
            };
        return r.each(function() {
            var t, f, g, p = r.selector || "",
                v = e.isPlainObject(o) ? e.extend(!0, {}, e.fn.shape.settings, o) : e.extend({}, e.fn.shape.settings),
                h = v.namespace,
                b = v.selector,
                y = v.error,
                x = v.className,
                w = "." + h,
                C = "module-" + h,
                k = e(this),
                S = k.find(b.sides),
                T = k.find(b.side),
                A = !1,
                R = this,
                P = k.data(C);
            g = {
                initialize: function() {
                    g.verbose("Initializing module for", R), g.set.defaultSide(), g.instantiate()
                },
                instantiate: function() {
                    g.verbose("Storing instance of module", g), P = g, k.data(C, P)
                },
                destroy: function() {
                    g.verbose("Destroying previous module for", R), k.removeData(C).off(w)
                },
                refresh: function() {
                    g.verbose("Refreshing selector cache for", R), k = e(R), S = e(this).find(b.shape), T = e(this).find(b.side)
                },
                repaint: function() {
                    g.verbose("Forcing repaint event"); {
                        var e = S[0] || n.createElement("div");
                        e.offsetWidth
                    }
                },
                animate: function(e, n) {
                    g.verbose("Animating box with properties", e), n = n || function(e) {
                        g.verbose("Executing animation callback"), e !== i && e.stopPropagation(), g.reset(), g.set.active()
                    }, v.beforeChange.call(f[0]), g.get.transitionEvent() ? (g.verbose("Starting CSS animation"), k.addClass(x.animating), S.css(e).one(g.get.transitionEvent(), n), g.set.duration(v.duration), m(function() {
                        k.addClass(x.animating), t.addClass(x.hidden)
                    })) : n()
                },
                queue: function(e) {
                    g.debug("Queueing animation of", e), S.one(g.get.transitionEvent(), function() {
                        g.debug("Executing queued animation"), setTimeout(function() {
                            k.shape(e)
                        }, 0)
                    })
                },
                reset: function() {
                    g.verbose("Animating states reset"), k.removeClass(x.animating).attr("style", "").removeAttr("style"), S.attr("style", "").removeAttr("style"), T.attr("style", "").removeAttr("style").removeClass(x.hidden), f.removeClass(x.animating).attr("style", "").removeAttr("style")
                },
                is: {
                    complete: function() {
                        return T.filter("." + x.active)[0] == f[0]
                    },
                    animating: function() {
                        return k.hasClass(x.animating)
                    }
                },
                set: {
                    defaultSide: function() {
                        t = k.find("." + v.className.active), f = t.next(b.side).length > 0 ? t.next(b.side) : k.find(b.side).first(), A = !1, g.verbose("Active side set to", t), g.verbose("Next side set to", f)
                    },
                    duration: function(e) {
                        e = e || v.duration, e = "number" == typeof e ? e + "ms" : e, g.verbose("Setting animation duration", e), (v.duration || 0 === v.duration) && S.add(T).css({
                            "-webkit-transition-duration": e,
                            "-moz-transition-duration": e,
                            "-ms-transition-duration": e,
                            "-o-transition-duration": e,
                            "transition-duration": e
                        })
                    },
                    currentStageSize: function() {
                        var e = k.find("." + v.className.active),
                            t = e.outerWidth(!0),
                            n = e.outerHeight(!0);
                        k.css({
                            width: t,
                            height: n
                        })
                    },
                    stageSize: function() {
                        var e = k.clone().addClass(x.loading),
                            t = e.find("." + v.className.active),
                            n = A ? e.find(b.side).eq(A) : t.next(b.side).length > 0 ? t.next(b.side) : e.find(b.side).first(),
                            i = {};
                        g.set.currentStageSize(), t.removeClass(x.active), n.addClass(x.active), e.insertAfter(k), i = {
                            width: n.outerWidth(!0),
                            height: n.outerHeight(!0)
                        }, e.remove(), k.css(i), g.verbose("Resizing stage to fit new content", i)
                    },
                    nextSide: function(e) {
                        A = e, f = T.filter(e), A = T.index(f), 0 === f.length && (g.set.defaultSide(), g.error(y.side)), g.verbose("Next side manually set to", f)
                    },
                    active: function() {
                        g.verbose("Setting new side to active", f), T.removeClass(x.active), f.addClass(x.active), v.onChange.call(f[0]), g.set.defaultSide()
                    }
                },
                flip: {
                    up: function() {
                        return !g.is.complete() || g.is.animating() || v.allowRepeats ? void(g.is.animating() ? g.queue("flip up") : (g.debug("Flipping up", f), g.set.stageSize(), g.stage.above(), g.animate(g.get.transform.up()))) : void g.debug("Side already visible", f)
                    },
                    down: function() {
                        return !g.is.complete() || g.is.animating() || v.allowRepeats ? void(g.is.animating() ? g.queue("flip down") : (g.debug("Flipping down", f), g.set.stageSize(), g.stage.below(), g.animate(g.get.transform.down()))) : void g.debug("Side already visible", f)
                    },
                    left: function() {
                        return !g.is.complete() || g.is.animating() || v.allowRepeats ? void(g.is.animating() ? g.queue("flip left") : (g.debug("Flipping left", f), g.set.stageSize(), g.stage.left(), g.animate(g.get.transform.left()))) : void g.debug("Side already visible", f)
                    },
                    right: function() {
                        return !g.is.complete() || g.is.animating() || v.allowRepeats ? void(g.is.animating() ? g.queue("flip right") : (g.debug("Flipping right", f), g.set.stageSize(), g.stage.right(), g.animate(g.get.transform.right()))) : void g.debug("Side already visible", f)
                    },
                    over: function() {
                        return !g.is.complete() || g.is.animating() || v.allowRepeats ? void(g.is.animating() ? g.queue("flip over") : (g.debug("Flipping over", f), g.set.stageSize(), g.stage.behind(), g.animate(g.get.transform.over()))) : void g.debug("Side already visible", f)
                    },
                    back: function() {
                        return !g.is.complete() || g.is.animating() || v.allowRepeats ? void(g.is.animating() ? g.queue("flip back") : (g.debug("Flipping back", f), g.set.stageSize(), g.stage.behind(), g.animate(g.get.transform.back()))) : void g.debug("Side already visible", f)
                    }
                },
                get: {
                    transform: {
                        up: function() {
                            var e = {
                                y: -((t.outerHeight(!0) - f.outerHeight(!0)) / 2),
                                z: -(t.outerHeight(!0) / 2)
                            };
                            return {
                                transform: "translateY(" + e.y + "px) translateZ(" + e.z + "px) rotateX(-90deg)"
                            }
                        },
                        down: function() {
                            var e = {
                                y: -((t.outerHeight(!0) - f.outerHeight(!0)) / 2),
                                z: -(t.outerHeight(!0) / 2)
                            };
                            return {
                                transform: "translateY(" + e.y + "px) translateZ(" + e.z + "px) rotateX(90deg)"
                            }
                        },
                        left: function() {
                            var e = {
                                x: -((t.outerWidth(!0) - f.outerWidth(!0)) / 2),
                                z: -(t.outerWidth(!0) / 2)
                            };
                            return {
                                transform: "translateX(" + e.x + "px) translateZ(" + e.z + "px) rotateY(90deg)"
                            }
                        },
                        right: function() {
                            var e = {
                                x: -((t.outerWidth(!0) - f.outerWidth(!0)) / 2),
                                z: -(t.outerWidth(!0) / 2)
                            };
                            return {
                                transform: "translateX(" + e.x + "px) translateZ(" + e.z + "px) rotateY(-90deg)"
                            }
                        },
                        over: function() {
                            var e = {
                                x: -((t.outerWidth(!0) - f.outerWidth(!0)) / 2)
                            };
                            return {
                                transform: "translateX(" + e.x + "px) rotateY(180deg)"
                            }
                        },
                        back: function() {
                            var e = {
                                x: -((t.outerWidth(!0) - f.outerWidth(!0)) / 2)
                            };
                            return {
                                transform: "translateX(" + e.x + "px) rotateY(-180deg)"
                            }
                        }
                    },
                    transitionEvent: function() {
                        var e, t = n.createElement("element"),
                            o = {
                                transition: "transitionend",
                                OTransition: "oTransitionEnd",
                                MozTransition: "transitionend",
                                WebkitTransition: "webkitTransitionEnd"
                            };
                        for (e in o)
                            if (t.style[e] !== i) return o[e]
                    },
                    nextSide: function() {
                        return t.next(b.side).length > 0 ? t.next(b.side) : k.find(b.side).first()
                    }
                },
                stage: {
                    above: function() {
                        var e = {
                            origin: (t.outerHeight(!0) - f.outerHeight(!0)) / 2,
                            depth: {
                                active: f.outerHeight(!0) / 2,
                                next: t.outerHeight(!0) / 2
                            }
                        };
                        g.verbose("Setting the initial animation position as above", f, e), S.css({
                            transform: "translateZ(-" + e.depth.active + "px)"
                        }), t.css({
                            transform: "rotateY(0deg) translateZ(" + e.depth.active + "px)"
                        }), f.addClass(x.animating).css({
                            top: e.origin + "px",
                            transform: "rotateX(90deg) translateZ(" + e.depth.next + "px)"
                        })
                    },
                    below: function() {
                        var e = {
                            origin: (t.outerHeight(!0) - f.outerHeight(!0)) / 2,
                            depth: {
                                active: f.outerHeight(!0) / 2,
                                next: t.outerHeight(!0) / 2
                            }
                        };
                        g.verbose("Setting the initial animation position as below", f, e), S.css({
                            transform: "translateZ(-" + e.depth.active + "px)"
                        }), t.css({
                            transform: "rotateY(0deg) translateZ(" + e.depth.active + "px)"
                        }), f.addClass(x.animating).css({
                            top: e.origin + "px",
                            transform: "rotateX(-90deg) translateZ(" + e.depth.next + "px)"
                        })
                    },
                    left: function() {
                        var e = {
                                active: t.outerWidth(!0),
                                next: f.outerWidth(!0)
                            },
                            n = {
                                origin: (e.active - e.next) / 2,
                                depth: {
                                    active: e.next / 2,
                                    next: e.active / 2
                                }
                            };
                        g.verbose("Setting the initial animation position as left", f, n), S.css({
                            transform: "translateZ(-" + n.depth.active + "px)"
                        }), t.css({
                            transform: "rotateY(0deg) translateZ(" + n.depth.active + "px)"
                        }), f.addClass(x.animating).css({
                            left: n.origin + "px",
                            transform: "rotateY(-90deg) translateZ(" + n.depth.next + "px)"
                        })
                    },
                    right: function() {
                        var e = {
                                active: t.outerWidth(!0),
                                next: f.outerWidth(!0)
                            },
                            n = {
                                origin: (e.active - e.next) / 2,
                                depth: {
                                    active: e.next / 2,
                                    next: e.active / 2
                                }
                            };
                        g.verbose("Setting the initial animation position as left", f, n), S.css({
                            transform: "translateZ(-" + n.depth.active + "px)"
                        }), t.css({
                            transform: "rotateY(0deg) translateZ(" + n.depth.active + "px)"
                        }), f.addClass(x.animating).css({
                            left: n.origin + "px",
                            transform: "rotateY(90deg) translateZ(" + n.depth.next + "px)"
                        })
                    },
                    behind: function() {
                        var e = {
                                active: t.outerWidth(!0),
                                next: f.outerWidth(!0)
                            },
                            n = {
                                origin: (e.active - e.next) / 2,
                                depth: {
                                    active: e.next / 2,
                                    next: e.active / 2
                                }
                            };
                        g.verbose("Setting the initial animation position as behind", f, n), t.css({
                            transform: "rotateY(0deg)"
                        }), f.addClass(x.animating).css({
                            left: n.origin + "px",
                            transform: "rotateY(-180deg)"
                        })
                    }
                },
                setting: function(t, n) {
                    if (g.debug("Changing setting", t, n), e.isPlainObject(t)) e.extend(!0, v, t);
                    else {
                        if (n === i) return v[t];
                        v[t] = n
                    }
                },
                internal: function(t, n) {
                    if (e.isPlainObject(t)) e.extend(!0, g, t);
                    else {
                        if (n === i) return g[t];
                        g[t] = n
                    }
                },
                debug: function() {
                    v.debug && (v.performance ? g.performance.log(arguments) : (g.debug = Function.prototype.bind.call(console.info, console, v.name + ":"), g.debug.apply(console, arguments)))
                },
                verbose: function() {
                    v.verbose && v.debug && (v.performance ? g.performance.log(arguments) : (g.verbose = Function.prototype.bind.call(console.info, console, v.name + ":"), g.verbose.apply(console, arguments)))
                },
                error: function() {
                    g.error = Function.prototype.bind.call(console.error, console, v.name + ":"), g.error.apply(console, arguments)
                },
                performance: {
                    log: function(e) {
                        var t, n, i;
                        v.performance && (t = (new Date).getTime(), i = s || t, n = t - i, s = t, c.push({
                            Name: e[0],
                            Arguments: [].slice.call(e, 1) || "",
                            Element: R,
                            "Execution Time": n
                        })), clearTimeout(g.performance.timer), g.performance.timer = setTimeout(g.performance.display, 500)
                    },
                    display: function() {
                        var t = v.name + ":",
                            n = 0;
                        s = !1, clearTimeout(g.performance.timer), e.each(c, function(e, t) {
                            n += t["Execution Time"]
                        }), t += " " + n + "ms", p && (t += " '" + p + "'"), r.length > 1 && (t += " (" + r.length + ")"), (console.group !== i || console.table !== i) && c.length > 0 && (console.groupCollapsed(t), console.table ? console.table(c) : e.each(c, function(e, t) {
                            console.log(t.Name + ": " + t["Execution Time"] + "ms")
                        }), console.groupEnd()), c = []
                    }
                },
                invoke: function(t, n, o) {
                    var r, s, c, l = P;
                    return n = n || d, o = R || o, "string" == typeof t && l !== i && (t = t.split(/[\. ]/), r = t.length - 1, e.each(t, function(n, o) {
                        var a = n != r ? o + t[n + 1].charAt(0).toUpperCase() + t[n + 1].slice(1) : t;
                        if (e.isPlainObject(l[a]) && n != r) l = l[a];
                        else {
                            if (l[a] !== i) return s = l[a], !1;
                            if (!e.isPlainObject(l[o]) || n == r) return l[o] !== i ? (s = l[o], !1) : !1;
                            l = l[o]
                        }
                    })), e.isFunction(s) ? c = s.apply(o, n) : s !== i && (c = s), e.isArray(a) ? a.push(c) : a !== i ? a = [a, c] : c !== i && (a = c), s
                }
            }, u ? (P === i && g.initialize(), g.invoke(l)) : (P !== i && P.invoke("destroy"), g.initialize())
        }), a !== i ? a : this
    }, e.fn.shape.settings = {
        name: "Shape",
        debug: !1,
        verbose: !1,
        performance: !0,
        namespace: "shape",
        beforeChange: function() {},
        onChange: function() {},
        allowRepeats: !1,
        duration: !1,
        error: {
            side: "You tried to switch to a side that does not exist.",
            method: "The method you called is not defined"
        },
        className: {
            animating: "animating",
            hidden: "hidden",
            loading: "loading",
            active: "active"
        },
        selector: {
            sides: ".sides",
            side: ".side"
        }
    }
}(jQuery, window, document),
function(e, t, n, i) {
    "use strict";
    e.fn.sidebar = function(o) {
        var a, r = e(this),
            s = e(t),
            c = e(n),
            l = e("html"),
            u = e("head"),
            d = r.selector || "",
            m = (new Date).getTime(),
            f = [],
            g = arguments[0],
            p = "string" == typeof g,
            v = [].slice.call(arguments, 1),
            h = t.requestAnimationFrame || t.mozRequestAnimationFrame || t.webkitRequestAnimationFrame || t.msRequestAnimationFrame || function(e) {
                setTimeout(e, 0)
            };
        return r.each(function() {
            var r, b, y, x, w, C, k = e.isPlainObject(o) ? e.extend(!0, {}, e.fn.sidebar.settings, o) : e.extend({}, e.fn.sidebar.settings),
                S = k.selector,
                T = k.className,
                A = k.namespace,
                R = k.regExp,
                P = k.error,
                E = "." + A,
                F = "module-" + A,
                D = e(this),
                O = e(k.context),
                q = D.children(S.sidebar),
                j = O.children(S.fixed),
                z = O.children(S.pusher),
                I = this,
                L = D.data(F);
            C = {
                initialize: function() {
                    C.debug("Initializing sidebar", o), C.create.id(), w = C.get.transitionEvent(), C.is.ios() && C.set.ios(), k.delaySetup ? h(C.setup.layout) : C.setup.layout(), h(function() {
                        C.setup.cache()
                    }), C.instantiate()
                },
                instantiate: function() {
                    C.verbose("Storing instance of module", C), L = C, D.data(F, C)
                },
                create: {
                    id: function() {
                        y = (Math.random().toString(16) + "000000000").substr(2, 8), b = "." + y, C.verbose("Creating unique id for element", y)
                    }
                },
                destroy: function() {
                    C.verbose("Destroying previous module for", D), D.off(E).removeData(F), C.is.ios() && C.remove.ios(), O.off(b), s.off(b), c.off(b)
                },
                event: {
                    clickaway: function(e) {
                        var t = z.find(e.target).length > 0 || z.is(e.target),
                            n = O.is(e.target);
                        t && (C.verbose("User clicked on dimmed page"), C.hide()), n && (C.verbose("User clicked on dimmable context (scaled out page)"), C.hide())
                    },
                    touch: function(e) {},
                    containScroll: function(e) {
                        I.scrollTop <= 0 && (I.scrollTop = 1), I.scrollTop + I.offsetHeight >= I.scrollHeight && (I.scrollTop = I.scrollHeight - I.offsetHeight - 1)
                    },
                    scroll: function(t) {
                        0 === e(t.target).closest(S.sidebar).length && t.preventDefault()
                    }
                },
                bind: {
                    clickaway: function() {
                        C.verbose("Adding clickaway events to context", O), k.closable && O.on("click" + b, C.event.clickaway).on("touchend" + b, C.event.clickaway)
                    },
                    scrollLock: function() {
                        k.scrollLock && (C.debug("Disabling page scroll"), s.on("DOMMouseScroll" + b, C.event.scroll)), C.verbose("Adding events to contain sidebar scroll"), c.on("touchmove" + b, C.event.touch), D.on("scroll" + E, C.event.containScroll)
                    }
                },
                unbind: {
                    clickaway: function() {
                        C.verbose("Removing clickaway events from context", O), O.off(b)
                    },
                    scrollLock: function() {
                        C.verbose("Removing scroll lock from page"), c.off(b), s.off(b), D.off("scroll" + E)
                    }
                },
                add: {
                    inlineCSS: function() {
                        var t, n = C.cache.width || D.outerWidth(),
                            i = C.cache.height || D.outerHeight(),
                            o = C.is.rtl(),
                            a = C.get.direction(),
                            s = {
                                left: n,
                                right: -n,
                                top: i,
                                bottom: -i
                            };
                        o && (C.verbose("RTL detected, flipping widths"), s.left = -n, s.right = n), t = "<style>", "left" === a || "right" === a ? (C.debug("Adding CSS rules for animation distance", n), t += " .ui.visible." + a + ".sidebar ~ .fixed, .ui.visible." + a + ".sidebar ~ .pusher {   -webkit-transform: translate3d(" + s[a] + "px, 0, 0);           transform: translate3d(" + s[a] + "px, 0, 0); }") : ("top" === a || "bottom" == a) && (t += " .ui.visible." + a + ".sidebar ~ .fixed, .ui.visible." + a + ".sidebar ~ .pusher {   -webkit-transform: translate3d(0, " + s[a] + "px, 0);           transform: translate3d(0, " + s[a] + "px, 0); }"), C.is.ie() && ("left" === a || "right" === a ? (C.debug("Adding CSS rules for animation distance", n), t += " body.pushable > .ui.visible." + a + ".sidebar ~ .pusher:after {   -webkit-transform: translate3d(" + s[a] + "px, 0, 0);           transform: translate3d(" + s[a] + "px, 0, 0); }") : ("top" === a || "bottom" == a) && (t += " body.pushable > .ui.visible." + a + ".sidebar ~ .pusher:after {   -webkit-transform: translate3d(0, " + s[a] + "px, 0);           transform: translate3d(0, " + s[a] + "px, 0); }"), t += " body.pushable > .ui.visible.left.sidebar ~ .ui.visible.right.sidebar ~ .pusher:after, body.pushable > .ui.visible.right.sidebar ~ .ui.visible.left.sidebar ~ .pusher:after {   -webkit-transform: translate3d(0px, 0, 0);           transform: translate3d(0px, 0, 0); }"), t += "</style>", r = e(t).appendTo(u), C.debug("Adding sizing css to head", r)
                    }
                },
                refresh: function() {
                    C.verbose("Refreshing selector cache"), O = e(k.context), q = O.children(S.sidebar), z = O.children(S.pusher), j = O.children(S.fixed), C.clear.cache()
                },
                refreshSidebars: function() {
                    C.verbose("Refreshing other sidebars"), q = O.children(S.sidebar)
                },
                repaint: function() {
                    C.verbose("Forcing repaint event"), I.style.display = "none";
                    I.offsetHeight;
                    I.scrollTop = I.scrollTop, I.style.display = ""
                },
                setup: {
                    cache: function() {
                        C.cache = {
                            width: D.outerWidth(),
                            height: D.outerHeight(),
                            rtl: "rtl" == D.css("direction")
                        }
                    },
                    layout: function() {
                        0 === O.children(S.pusher).length && (C.debug("Adding wrapper element for sidebar"), C.error(P.pusher), z = e('<div class="pusher" />'), O.children().not(S.omitted).not(q).wrapAll(z), C.refresh()), (0 === D.nextAll(S.pusher).length || D.nextAll(S.pusher)[0] !== z[0]) && (C.debug("Moved sidebar to correct parent element"), C.error(P.movedSidebar, I), D.detach().prependTo(O), C.refresh()), C.clear.cache(), C.set.pushable(), C.set.direction()
                    }
                },
                attachEvents: function(t, n) {
                    var i = e(t);
                    n = e.isFunction(C[n]) ? C[n] : C.toggle, i.length > 0 ? (C.debug("Attaching sidebar events to element", t, n), i.on("click" + E, n)) : C.error(P.notFound, t)
                },
                show: function(t) {
                    if (t = e.isFunction(t) ? t : function() {}, C.is.hidden()) {
                        if (C.refreshSidebars(), k.overlay && (C.error(P.overlay), k.transition = "overlay"), C.refresh(), C.othersActive())
                            if (C.debug("Other sidebars currently visible"), k.exclusive) {
                                if ("overlay" != k.transition) return void C.hideOthers(C.show);
                                C.hideOthers()
                            } else k.transition = "overlay";
                        C.pushPage(function() {
                            t.call(I), k.onShow.call(I)
                        }), k.onChange.call(I), k.onVisible.call(I)
                    } else C.debug("Sidebar is already visible")
                },
                hide: function(t) {
                    t = e.isFunction(t) ? t : function() {}, (C.is.visible() || C.is.animating()) && (C.debug("Hiding sidebar", t), C.refreshSidebars(), C.pullPage(function() {
                        t.call(I), k.onHidden.call(I)
                    }), k.onChange.call(I), k.onHide.call(I))
                },
                othersAnimating: function() {
                    return q.not(D).filter("." + T.animating).length > 0;

                },
                othersVisible: function() {
                    return q.not(D).filter("." + T.visible).length > 0
                },
                othersActive: function() {
                    return C.othersVisible() || C.othersAnimating()
                },
                hideOthers: function(e) {
                    var t = q.not(D).filter("." + T.visible),
                        n = t.length,
                        i = 0;
                    e = e || function() {}, t.sidebar("hide", function() {
                        i++, i == n && e()
                    })
                },
                toggle: function() {
                    C.verbose("Determining toggled direction"), C.is.hidden() ? C.show() : C.hide()
                },
                pushPage: function(t) {
                    var n, i, o, a = C.get.transition(),
                        r = "overlay" === a || C.othersActive() ? D : z;
                    t = e.isFunction(t) ? t : function() {}, "scale down" == k.transition && C.scrollToTop(), C.set.transition(a), C.repaint(), n = function() {
                        C.bind.clickaway(), C.add.inlineCSS(), C.set.animating(), C.set.visible()
                    }, i = function() {
                        C.set.dimmed()
                    }, o = function(e) {
                        e.target == r[0] && (r.off(w + b, o), C.remove.animating(), C.bind.scrollLock(), t.call(I))
                    }, r.off(w + b), r.on(w + b, o), h(n), k.dimPage && !C.othersVisible() && h(i)
                },
                pullPage: function(t) {
                    var n, i, o = C.get.transition(),
                        a = "overlay" == o || C.othersActive() ? D : z;
                    t = e.isFunction(t) ? t : function() {}, C.verbose("Removing context push state", C.get.direction()), C.unbind.clickaway(), C.unbind.scrollLock(), n = function() {
                        C.set.transition(o), C.set.animating(), C.remove.visible(), k.dimPage && !C.othersVisible() && z.removeClass(T.dimmed)
                    }, i = function(e) {
                        e.target == a[0] && (a.off(w + b, i), C.remove.animating(), C.remove.transition(), C.remove.inlineCSS(), ("scale down" == o || k.returnScroll && C.is.mobile()) && C.scrollBack(), t.call(I))
                    }, a.off(w + b), a.on(w + b, i), h(n)
                },
                scrollToTop: function() {
                    C.verbose("Scrolling to top of page to avoid animation issues"), x = e(t).scrollTop(), D.scrollTop(0), t.scrollTo(0, 0)
                },
                scrollBack: function() {
                    C.verbose("Scrolling back to original page position"), t.scrollTo(0, x)
                },
                clear: {
                    cache: function() {
                        C.verbose("Clearing cached dimensions"), C.cache = {}
                    }
                },
                set: {
                    ios: function() {
                        l.addClass(T.ios)
                    },
                    pushed: function() {
                        O.addClass(T.pushed)
                    },
                    pushable: function() {
                        O.addClass(T.pushable)
                    },
                    dimmed: function() {
                        z.addClass(T.dimmed)
                    },
                    active: function() {
                        D.addClass(T.active)
                    },
                    animating: function() {
                        D.addClass(T.animating)
                    },
                    transition: function(e) {
                        e = e || C.get.transition(), D.addClass(e)
                    },
                    direction: function(e) {
                        e = e || C.get.direction(), D.addClass(T[e])
                    },
                    visible: function() {
                        D.addClass(T.visible)
                    },
                    overlay: function() {
                        D.addClass(T.overlay)
                    }
                },
                remove: {
                    inlineCSS: function() {
                        C.debug("Removing inline css styles", r), r && r.length > 0 && r.remove()
                    },
                    ios: function() {
                        l.removeClass(T.ios)
                    },
                    pushed: function() {
                        O.removeClass(T.pushed)
                    },
                    pushable: function() {
                        O.removeClass(T.pushable)
                    },
                    active: function() {
                        D.removeClass(T.active)
                    },
                    animating: function() {
                        D.removeClass(T.animating)
                    },
                    transition: function(e) {
                        e = e || C.get.transition(), D.removeClass(e)
                    },
                    direction: function(e) {
                        e = e || C.get.direction(), D.removeClass(T[e])
                    },
                    visible: function() {
                        D.removeClass(T.visible)
                    },
                    overlay: function() {
                        D.removeClass(T.overlay)
                    }
                },
                get: {
                    direction: function() {
                        return D.hasClass(T.top) ? T.top : D.hasClass(T.right) ? T.right : D.hasClass(T.bottom) ? T.bottom : T.left
                    },
                    transition: function() {
                        var e, t = C.get.direction();
                        return e = C.is.mobile() ? "auto" == k.mobileTransition ? k.defaultTransition.mobile[t] : k.mobileTransition : "auto" == k.transition ? k.defaultTransition.computer[t] : k.transition, C.verbose("Determined transition", e), e
                    },
                    transitionEvent: function() {
                        var e, t = n.createElement("element"),
                            o = {
                                transition: "transitionend",
                                OTransition: "oTransitionEnd",
                                MozTransition: "transitionend",
                                WebkitTransition: "webkitTransitionEnd"
                            };
                        for (e in o)
                            if (t.style[e] !== i) return o[e]
                    }
                },
                is: {
                    ie: function() {
                        var e = !t.ActiveXObject && "ActiveXObject" in t,
                            n = "ActiveXObject" in t;
                        return e || n
                    },
                    ios: function() {
                        var e = navigator.userAgent,
                            t = e.match(R.ios),
                            n = e.match(R.mobileChrome);
                        return t && !n ? (C.verbose("Browser was found to be iOS", e), !0) : !1
                    },
                    mobile: function() {
                        var e = navigator.userAgent,
                            t = e.match(R.mobile);
                        return t ? (C.verbose("Browser was found to be mobile", e), !0) : (C.verbose("Browser is not mobile, using regular transition", e), !1)
                    },
                    hidden: function() {
                        return !C.is.visible()
                    },
                    visible: function() {
                        return D.hasClass(T.visible)
                    },
                    open: function() {
                        return C.is.visible()
                    },
                    closed: function() {
                        return C.is.hidden()
                    },
                    vertical: function() {
                        return D.hasClass(T.top)
                    },
                    animating: function() {
                        return O.hasClass(T.animating)
                    },
                    rtl: function() {
                        return C.cache.rtl === i && (C.cache.rtl = "rtl" == D.css("direction")), C.cache.rtl
                    }
                },
                setting: function(t, n) {
                    if (C.debug("Changing setting", t, n), e.isPlainObject(t)) e.extend(!0, k, t);
                    else {
                        if (n === i) return k[t];
                        k[t] = n
                    }
                },
                internal: function(t, n) {
                    if (e.isPlainObject(t)) e.extend(!0, C, t);
                    else {
                        if (n === i) return C[t];
                        C[t] = n
                    }
                },
                debug: function() {
                    k.debug && (k.performance ? C.performance.log(arguments) : (C.debug = Function.prototype.bind.call(console.info, console, k.name + ":"), C.debug.apply(console, arguments)))
                },
                verbose: function() {
                    k.verbose && k.debug && (k.performance ? C.performance.log(arguments) : (C.verbose = Function.prototype.bind.call(console.info, console, k.name + ":"), C.verbose.apply(console, arguments)))
                },
                error: function() {
                    C.error = Function.prototype.bind.call(console.error, console, k.name + ":"), C.error.apply(console, arguments)
                },
                performance: {
                    log: function(e) {
                        var t, n, i;
                        k.performance && (t = (new Date).getTime(), i = m || t, n = t - i, m = t, f.push({
                            Name: e[0],
                            Arguments: [].slice.call(e, 1) || "",
                            Element: I,
                            "Execution Time": n
                        })), clearTimeout(C.performance.timer), C.performance.timer = setTimeout(C.performance.display, 500)
                    },
                    display: function() {
                        var t = k.name + ":",
                            n = 0;
                        m = !1, clearTimeout(C.performance.timer), e.each(f, function(e, t) {
                            n += t["Execution Time"]
                        }), t += " " + n + "ms", d && (t += " '" + d + "'"), (console.group !== i || console.table !== i) && f.length > 0 && (console.groupCollapsed(t), console.table ? console.table(f) : e.each(f, function(e, t) {
                            console.log(t.Name + ": " + t["Execution Time"] + "ms")
                        }), console.groupEnd()), f = []
                    }
                },
                invoke: function(t, n, o) {
                    var r, s, c, l = L;
                    return n = n || v, o = I || o, "string" == typeof t && l !== i && (t = t.split(/[\. ]/), r = t.length - 1, e.each(t, function(n, o) {
                        var a = n != r ? o + t[n + 1].charAt(0).toUpperCase() + t[n + 1].slice(1) : t;
                        if (e.isPlainObject(l[a]) && n != r) l = l[a];
                        else {
                            if (l[a] !== i) return s = l[a], !1;
                            if (!e.isPlainObject(l[o]) || n == r) return l[o] !== i ? (s = l[o], !1) : (C.error(P.method, t), !1);
                            l = l[o]
                        }
                    })), e.isFunction(s) ? c = s.apply(o, n) : s !== i && (c = s), e.isArray(a) ? a.push(c) : a !== i ? a = [a, c] : c !== i && (a = c), s
                }
            }, p ? (L === i && C.initialize(), C.invoke(g)) : (L !== i && C.invoke("destroy"), C.initialize())
        }), a !== i ? a : this
    }, e.fn.sidebar.settings = {
        name: "Sidebar",
        namespace: "sidebar",
        debug: !1,
        verbose: !1,
        performance: !0,
        transition: "auto",
        mobileTransition: "auto",
        defaultTransition: {
            computer: {
                left: "uncover",
                right: "uncover",
                top: "overlay",
                bottom: "overlay"
            },
            mobile: {
                left: "uncover",
                right: "uncover",
                top: "overlay",
                bottom: "overlay"
            }
        },
        context: "body",
        exclusive: !1,
        closable: !0,
        dimPage: !0,
        scrollLock: !1,
        returnScroll: !1,
        delaySetup: !1,
        duration: 500,
        onChange: function() {},
        onShow: function() {},
        onHide: function() {},
        onHidden: function() {},
        onVisible: function() {},
        className: {
            active: "active",
            animating: "animating",
            dimmed: "dimmed",
            ios: "ios",
            pushable: "pushable",
            pushed: "pushed",
            right: "right",
            top: "top",
            left: "left",
            bottom: "bottom",
            visible: "visible"
        },
        selector: {
            fixed: ".fixed",
            omitted: "script, link, style, .ui.modal, .ui.dimmer, .ui.nag, .ui.fixed",
            pusher: ".pusher",
            sidebar: ".ui.sidebar"
        },
        regExp: {
            ios: /(iPad|iPhone|iPod)/g,
            mobileChrome: /(CriOS)/g,
            mobile: /Mobile|iP(hone|od|ad)|Android|BlackBerry|IEMobile|Kindle|NetFront|Silk-Accelerated|(hpw|web)OS|Fennec|Minimo|Opera M(obi|ini)|Blazer|Dolfin|Dolphin|Skyfire|Zune/g
        },
        error: {
            method: "The method you called is not defined.",
            pusher: "Had to add pusher element. For optimal performance make sure body content is inside a pusher element",
            movedSidebar: "Had to move sidebar. For optimal performance make sure sidebar and pusher are direct children of your body tag",
            overlay: "The overlay setting is no longer supported, use animation: overlay",
            notFound: "There were no elements that matched the specified selector"
        }
    }
}(jQuery, window, document),
function(e, t, n, i) {
    "use strict";
    e.fn.sticky = function(n) {
        var o, a = e(this),
            r = a.selector || "",
            s = (new Date).getTime(),
            c = [],
            l = arguments[0],
            u = "string" == typeof l,
            d = [].slice.call(arguments, 1);
        return a.each(function() {
            var a, m, f, g, p = e.isPlainObject(n) ? e.extend(!0, {}, e.fn.sticky.settings, n) : e.extend({}, e.fn.sticky.settings),
                v = p.className,
                h = p.namespace,
                b = p.error,
                y = "." + h,
                x = "module-" + h,
                w = e(this),
                C = e(t),
                k = e(p.scrollContext),
                S = (w.selector || "", w.data(x)),
                T = t.requestAnimationFrame || t.mozRequestAnimationFrame || t.webkitRequestAnimationFrame || t.msRequestAnimationFrame || function(e) {
                    setTimeout(e, 0)
                },
                A = this;
            g = {
                initialize: function() {
                    g.determineContainer(), g.determineContext(), g.verbose("Initializing sticky", p, a), g.save.positions(), g.checkErrors(), g.bind.events(), p.observeChanges && g.observeChanges(), g.instantiate()
                },
                instantiate: function() {
                    g.verbose("Storing instance of module", g), S = g, w.data(x, g)
                },
                destroy: function() {
                    g.verbose("Destroying previous instance"), g.reset(), f && f.disconnect(), C.off("load" + y, g.event.load).off("resize" + y, g.event.resize), k.off("scrollchange" + y, g.event.scrollchange), w.removeData(x)
                },
                observeChanges: function() {
                    var e = m[0];
                    "MutationObserver" in t && (f = new MutationObserver(function(e) {
                        clearTimeout(g.timer), g.timer = setTimeout(function() {
                            g.verbose("DOM tree modified, updating sticky menu", e), g.refresh()
                        }, 100)
                    }), f.observe(A, {
                        childList: !0,
                        subtree: !0
                    }), f.observe(e, {
                        childList: !0,
                        subtree: !0
                    }), g.debug("Setting up mutation observer", f))
                },
                determineContainer: function() {
                    a = w.offsetParent()
                },
                determineContext: function() {
                    return m = p.context ? e(p.context) : a, 0 === m.length ? void g.error(b.invalidContext, p.context, w) : void 0
                },
                checkErrors: function() {
                    return g.is.hidden() && g.error(b.visible, w), g.cache.element.height > g.cache.context.height ? (g.reset(), void g.error(b.elementSize, w)) : void 0
                },
                bind: {
                    events: function() {
                        C.on("load" + y, g.event.load).on("resize" + y, g.event.resize), k.off("scroll" + y).on("scroll" + y, g.event.scroll).on("scrollchange" + y, g.event.scrollchange)
                    }
                },
                event: {
                    load: function() {
                        g.verbose("Page contents finished loading"), T(g.refresh)
                    },
                    resize: function() {
                        g.verbose("Window resized"), T(g.refresh)
                    },
                    scroll: function() {
                        T(function() {
                            k.triggerHandler("scrollchange" + y, k.scrollTop())
                        })
                    },
                    scrollchange: function(e, t) {
                        g.stick(t), p.onScroll.call(A)
                    }
                },
                refresh: function(e) {
                    g.reset(), p.context || g.determineContext(), e && g.determineContainer(), g.save.positions(), g.stick(), p.onReposition.call(A)
                },
                supports: {
                    sticky: function() {
                        {
                            var t = e("<div/>");
                            t[0]
                        }
                        return t.addClass(v.supported), t.css("position").match("sticky")
                    }
                },
                save: {
                    lastScroll: function(e) {
                        g.lastScroll = e
                    },
                    elementScroll: function(e) {
                        g.elementScroll = e
                    },
                    positions: function() {
                        {
                            var e = {
                                    height: C.height()
                                },
                                t = {
                                    margin: {
                                        top: parseInt(w.css("margin-top"), 10),
                                        bottom: parseInt(w.css("margin-bottom"), 10)
                                    },
                                    offset: w.offset(),
                                    width: w.outerWidth(),
                                    height: w.outerHeight()
                                },
                                n = {
                                    offset: m.offset(),
                                    height: m.outerHeight()
                                };
                            ({
                                height: a.outerHeight()
                            })
                        }
                        g.cache = {
                            fits: t.height < e.height,
                            window: {
                                height: e.height
                            },
                            element: {
                                margin: t.margin,
                                top: t.offset.top - t.margin.top,
                                left: t.offset.left,
                                width: t.width,
                                height: t.height,
                                bottom: t.offset.top + t.height
                            },
                            context: {
                                top: n.offset.top,
                                height: n.height,
                                bottom: n.offset.top + n.height
                            }
                        }, g.set.containerSize(), g.set.size(), g.stick(), g.debug("Caching element positions", g.cache)
                    }
                },
                get: {
                    direction: function(e) {
                        var t = "down";
                        return e = e || k.scrollTop(), g.lastScroll !== i && (g.lastScroll < e ? t = "down" : g.lastScroll > e && (t = "up")), t
                    },
                    scrollChange: function(e) {
                        return e = e || k.scrollTop(), g.lastScroll ? e - g.lastScroll : 0
                    },
                    currentElementScroll: function() {
                        return g.elementScroll ? g.elementScroll : g.is.top() ? Math.abs(parseInt(w.css("top"), 10)) || 0 : Math.abs(parseInt(w.css("bottom"), 10)) || 0
                    },
                    elementScroll: function(e) {
                        e = e || k.scrollTop();
                        var t = g.cache.element,
                            n = g.cache.window,
                            i = g.get.scrollChange(e),
                            o = t.height - n.height + p.offset,
                            a = g.get.currentElementScroll(),
                            r = a + i;
                        return a = g.cache.fits || 0 > r ? 0 : r > o ? o : r
                    }
                },
                remove: {
                    lastScroll: function() {
                        delete g.lastScroll
                    },
                    elementScroll: function(e) {
                        delete g.elementScroll
                    },
                    offset: function() {
                        w.css("margin-top", "")
                    }
                },
                set: {
                    offset: function() {
                        g.verbose("Setting offset on element", p.offset), w.css("margin-top", p.offset)
                    },
                    containerSize: function() {
                        var e = a.get(0).tagName;
                        "HTML" === e || "body" == e ? g.determineContainer() : Math.abs(a.outerHeight() - g.cache.context.height) > p.jitter && (g.debug("Context has padding, specifying exact height for container", g.cache.context.height), a.css({
                            height: g.cache.context.height
                        }))
                    },
                    minimumSize: function() {
                        var e = g.cache.element;
                        a.css("min-height", e.height)
                    },
                    scroll: function(e) {
                        g.debug("Setting scroll on element", e), g.elementScroll != e && (g.is.top() && w.css("bottom", "").css("top", -e), g.is.bottom() && w.css("top", "").css("bottom", e))
                    },
                    size: function() {
                        0 !== g.cache.element.height && 0 !== g.cache.element.width && (A.style.setProperty("width", g.cache.element.width + "px", "important"), A.style.setProperty("height", g.cache.element.height + "px", "important"))
                    }
                },
                is: {
                    top: function() {
                        return w.hasClass(v.top)
                    },
                    bottom: function() {
                        return w.hasClass(v.bottom)
                    },
                    initialPosition: function() {
                        return !g.is.fixed() && !g.is.bound()
                    },
                    hidden: function() {
                        return !w.is(":visible")
                    },
                    bound: function() {
                        return w.hasClass(v.bound)
                    },
                    fixed: function() {
                        return w.hasClass(v.fixed)
                    }
                },
                stick: function(e) {
                    var t = e || k.scrollTop(),
                        n = g.cache,
                        i = n.fits,
                        o = n.element,
                        a = n.window,
                        r = n.context,
                        s = g.is.bottom() && p.pushing ? p.bottomOffset : p.offset,
                        e = {
                            top: t + s,
                            bottom: t + s + a.height
                        },
                        c = (g.get.direction(e.top), i ? 0 : g.get.elementScroll(e.top)),
                        l = !i,
                        u = 0 !== o.height;
                    u && (g.is.initialPosition() ? e.top >= r.bottom ? (g.debug("Initial element position is bottom of container"), g.bindBottom()) : e.top > o.top && (o.height + e.top - c >= r.bottom ? (g.debug("Initial element position is bottom of container"), g.bindBottom()) : (g.debug("Initial element position is fixed"), g.fixTop())) : g.is.fixed() ? g.is.top() ? e.top <= o.top ? (g.debug("Fixed element reached top of container"), g.setInitialPosition()) : o.height + e.top - c >= r.bottom ? (g.debug("Fixed element reached bottom of container"), g.bindBottom()) : l && (g.set.scroll(c), g.save.lastScroll(e.top), g.save.elementScroll(c)) : g.is.bottom() && (e.bottom - o.height <= o.top ? (g.debug("Bottom fixed rail has reached top of container"), g.setInitialPosition()) : e.bottom >= r.bottom ? (g.debug("Bottom fixed rail has reached bottom of container"), g.bindBottom()) : l && (g.set.scroll(c), g.save.lastScroll(e.top), g.save.elementScroll(c))) : g.is.bottom() && (p.pushing ? g.is.bound() && e.bottom <= r.bottom && (g.debug("Fixing bottom attached element to bottom of browser."), g.fixBottom()) : g.is.bound() && e.top <= r.bottom - o.height && (g.debug("Fixing bottom attached element to top of browser."), g.fixTop())))
                },
                bindTop: function() {
                    g.debug("Binding element to top of parent container"), g.remove.offset(), w.css({
                        left: "",
                        top: "",
                        marginBottom: ""
                    }).removeClass(v.fixed).removeClass(v.bottom).addClass(v.bound).addClass(v.top), p.onTop.call(A), p.onUnstick.call(A)
                },
                bindBottom: function() {
                    g.debug("Binding element to bottom of parent container"), g.remove.offset(), w.css({
                        left: "",
                        top: ""
                    }).removeClass(v.fixed).removeClass(v.top).addClass(v.bound).addClass(v.bottom), p.onBottom.call(A), p.onUnstick.call(A)
                },
                setInitialPosition: function() {
                    g.debug("Returning to initial position"), g.unfix(), g.unbind()
                },
                fixTop: function() {
                    g.debug("Fixing element to top of page"), g.set.minimumSize(), g.set.offset(), w.css({
                        left: g.cache.element.left,
                        bottom: "",
                        marginBottom: ""
                    }).removeClass(v.bound).removeClass(v.bottom).addClass(v.fixed).addClass(v.top), p.onStick.call(A)
                },
                fixBottom: function() {
                    g.debug("Sticking element to bottom of page"), g.set.minimumSize(), g.set.offset(), w.css({
                        left: g.cache.element.left,
                        bottom: "",
                        marginBottom: ""
                    }).removeClass(v.bound).removeClass(v.top).addClass(v.fixed).addClass(v.bottom), p.onStick.call(A)
                },
                unbind: function() {
                    g.is.bound() && (g.debug("Removing container bound position on element"), g.remove.offset(), w.removeClass(v.bound).removeClass(v.top).removeClass(v.bottom))
                },
                unfix: function() {
                    g.is.fixed() && (g.debug("Removing fixed position on element"), g.remove.offset(), w.removeClass(v.fixed).removeClass(v.top).removeClass(v.bottom), p.onUnstick.call(A))
                },
                reset: function() {
                    g.debug("Reseting elements position"), g.unbind(), g.unfix(), g.resetCSS(), g.remove.offset(), g.remove.lastScroll()
                },
                resetCSS: function() {
                    w.css({
                        width: "",
                        height: ""
                    }), a.css({
                        height: ""
                    })
                },
                setting: function(t, n) {
                    if (e.isPlainObject(t)) e.extend(!0, p, t);
                    else {
                        if (n === i) return p[t];
                        p[t] = n
                    }
                },
                internal: function(t, n) {
                    if (e.isPlainObject(t)) e.extend(!0, g, t);
                    else {
                        if (n === i) return g[t];
                        g[t] = n
                    }
                },
                debug: function() {
                    p.debug && (p.performance ? g.performance.log(arguments) : (g.debug = Function.prototype.bind.call(console.info, console, p.name + ":"), g.debug.apply(console, arguments)))
                },
                verbose: function() {
                    p.verbose && p.debug && (p.performance ? g.performance.log(arguments) : (g.verbose = Function.prototype.bind.call(console.info, console, p.name + ":"), g.verbose.apply(console, arguments)))
                },
                error: function() {
                    g.error = Function.prototype.bind.call(console.error, console, p.name + ":"), g.error.apply(console, arguments)
                },
                performance: {
                    log: function(e) {
                        var t, n, i;
                        p.performance && (t = (new Date).getTime(), i = s || t, n = t - i, s = t, c.push({
                            Name: e[0],
                            Arguments: [].slice.call(e, 1) || "",
                            Element: A,
                            "Execution Time": n
                        })), clearTimeout(g.performance.timer), g.performance.timer = setTimeout(g.performance.display, 0)
                    },
                    display: function() {
                        var t = p.name + ":",
                            n = 0;
                        s = !1, clearTimeout(g.performance.timer), e.each(c, function(e, t) {
                            n += t["Execution Time"]
                        }), t += " " + n + "ms", r && (t += " '" + r + "'"), (console.group !== i || console.table !== i) && c.length > 0 && (console.groupCollapsed(t), console.table ? console.table(c) : e.each(c, function(e, t) {
                            console.log(t.Name + ": " + t["Execution Time"] + "ms")
                        }), console.groupEnd()), c = []
                    }
                },
                invoke: function(t, n, a) {
                    var r, s, c, l = S;
                    return n = n || d, a = A || a, "string" == typeof t && l !== i && (t = t.split(/[\. ]/), r = t.length - 1, e.each(t, function(n, o) {
                        var a = n != r ? o + t[n + 1].charAt(0).toUpperCase() + t[n + 1].slice(1) : t;
                        if (e.isPlainObject(l[a]) && n != r) l = l[a];
                        else {
                            if (l[a] !== i) return s = l[a], !1;
                            if (!e.isPlainObject(l[o]) || n == r) return l[o] !== i ? (s = l[o], !1) : !1;
                            l = l[o]
                        }
                    })), e.isFunction(s) ? c = s.apply(a, n) : s !== i && (c = s), e.isArray(o) ? o.push(c) : o !== i ? o = [o, c] : c !== i && (o = c), s
                }
            }, u ? (S === i && g.initialize(), g.invoke(l)) : (S !== i && S.invoke("destroy"), g.initialize())
        }), o !== i ? o : this
    }, e.fn.sticky.settings = {
        name: "Sticky",
        namespace: "sticky",
        debug: !1,
        verbose: !0,
        performance: !0,
        pushing: !1,
        context: !1,
        scrollContext: t,
        offset: 0,
        bottomOffset: 0,
        jitter: 5,
        observeChanges: !1,
        onReposition: function() {},
        onScroll: function() {},
        onStick: function() {},
        onUnstick: function() {},
        onTop: function() {},
        onBottom: function() {},
        error: {
            container: "Sticky element must be inside a relative container",
            visible: "Element is hidden, you must call refresh after element becomes visible",
            method: "The method you called is not defined.",
            invalidContext: "Context specified does not exist",
            elementSize: "Sticky element is larger than its container, cannot create sticky."
        },
        className: {
            bound: "bound",
            fixed: "fixed",
            supported: "native",
            top: "top",
            bottom: "bottom"
        }
    }
}(jQuery, window, document),
function(e, t, n, i) {
    "use strict";
    e.fn.tab = function(o) {
        var a, r = e(e.isFunction(this) ? t : this),
            s = r.selector || "",
            c = (new Date).getTime(),
            l = [],
            u = arguments[0],
            d = "string" == typeof u,
            m = [].slice.call(arguments, 1),
            f = !1;
        return r.each(function() {
            var g, p, v, h, b, y, x = e.isPlainObject(o) ? e.extend(!0, {}, e.fn.tab.settings, o) : e.extend({}, e.fn.tab.settings),
                w = x.className,
                C = x.metadata,
                k = x.selector,
                S = x.error,
                T = "." + x.namespace,
                A = "module-" + x.namespace,
                R = e(this),
                P = {},
                E = !0,
                F = 0,
                D = this,
                O = R.data(A);
            b = {
                initialize: function() {
                    b.debug("Initializing tab menu item", R), b.fix.callbacks(), b.determineTabs(), b.debug("Determining tabs", x.context, p), x.auto && b.set.auto(), b.bind.events(), x.history && !f && (b.initializeHistory(), f = !0), b.instantiate()
                },
                instantiate: function() {
                    b.verbose("Storing instance of module", b), O = b, R.data(A, b)
                },
                destroy: function() {
                    b.debug("Destroying tabs", R), R.removeData(A).off(T)
                },
                bind: {
                    events: function() {
                        e.isWindow(D) || (b.debug("Attaching tab activation events to element", R), R.on("click" + T, b.event.click))
                    }
                },
                determineTabs: function() {
                    var t;
                    "parent" === x.context ? (R.closest(k.ui).length > 0 ? (t = R.closest(k.ui), b.verbose("Using closest UI element as parent", t)) : t = R, g = t.parent(), b.verbose("Determined parent element for creating context", g)) : x.context ? (g = e(x.context), b.verbose("Using selector for tab context", x.context, g)) : g = e("body"), x.childrenOnly ? (p = g.children(k.tabs), b.debug("Searching tab context children for tabs", g, p)) : (p = g.find(k.tabs), b.debug("Searching tab context for tabs", g, p))
                },
                fix: {
                    callbacks: function() {
                        e.isPlainObject(o) && (o.onTabLoad || o.onTabInit) && (o.onTabLoad && (o.onLoad = o.onTabLoad, delete o.onTabLoad, b.error(S.legacyLoad, o.onLoad)), o.onTabInit && (o.onFirstLoad = o.onTabInit, delete o.onTabInit, b.error(S.legacyInit, o.onFirstLoad)), x = e.extend(!0, {}, e.fn.tab.settings, o))
                    }
                },
                initializeHistory: function() {
                    if (b.debug("Initializing page state"), e.address === i) return b.error(S.state), !1;
                    if ("state" == x.historyType) {
                        if (b.debug("Using HTML5 to manage state"), x.path === !1) return b.error(S.path), !1;
                        e.address.history(!0).state(x.path)
                    }
                    e.address.bind("change", b.event.history.change)
                },
                event: {
                    click: function(t) {
                        var n = e(this).data(C.tab);
                        n !== i ? (x.history ? (b.verbose("Updating page state", t), e.address.value(n)) : (b.verbose("Changing tab", t), b.changeTab(n)), t.preventDefault()) : b.debug("No tab specified")
                    },
                    history: {
                        change: function(t) {
                            var n = t.pathNames.join("/") || b.get.initialPath(),
                                o = x.templates.determineTitle(n) || !1;
                            b.performance.display(), b.debug("History change event", n, t), y = t, n !== i && b.changeTab(n), o && e.address.title(o)
                        }
                    }
                },
                refresh: function() {
                    v && (b.debug("Refreshing tab", v), b.changeTab(v))
                },
                cache: {
                    read: function(e) {
                        return e !== i ? P[e] : !1
                    },
                    add: function(e, t) {
                        e = e || v, b.debug("Adding cached content for", e), P[e] = t
                    },
                    remove: function(e) {
                        e = e || v, b.debug("Removing cached content for", e), delete P[e]
                    }
                },
                set: {
                    auto: function() {
                        var t = "string" == typeof x.path ? x.path.replace(/\/$/, "") + "/{$tab}" : "/{$tab}";
                        b.verbose("Setting up automatic tab retrieval from server", t), e.isPlainObject(x.apiSettings) ? x.apiSettings.url = t : x.apiSettings = {
                            url: t
                        }
                    },
                    loading: function(e) {
                        var t = b.get.tabElement(e),
                            n = t.hasClass(w.loading);
                        n || (b.verbose("Setting loading state for", t), t.addClass(w.loading).siblings(p).removeClass(w.active + " " + w.loading), t.length > 0 && x.onRequest.call(t[0], e))
                    },
                    state: function(t) {
                        e.address.value(t)
                    }
                },
                changeTab: function(n) {
                    var i = t.history && t.history.pushState,
                        o = i && x.ignoreFirstLoad && E,
                        a = x.auto || e.isPlainObject(x.apiSettings),
                        r = a && !o ? b.utilities.pathToArray(n) : b.get.defaultPathArray(n);
                    n = b.utilities.arrayToPath(r), e.each(r, function(t, i) {
                        var s, c, l, u, d = r.slice(0, t + 1),
                            m = b.utilities.arrayToPath(d),
                            f = b.is.tab(m),
                            p = t + 1 == r.length,
                            k = b.get.tabElement(m);
                        if (b.verbose("Looking for tab", i), f) {
                            if (b.verbose("Tab was found", i), v = m, h = b.utilities.filterArray(r, d), p ? u = !0 : (c = r.slice(0, t + 2), l = b.utilities.arrayToPath(c), u = !b.is.tab(l), u && b.verbose("Tab parameters found", c)), u && a) return o ? (b.debug("Ignoring remote content on first tab load", m), E = !1, b.cache.add(n, k.html()), b.activate.all(m), x.onFirstLoad.call(k[0], m, h, y), x.onLoad.call(k[0], m, h, y)) : (b.activate.navigation(m), b.fetch.content(m, n)), !1;
                            b.debug("Opened local tab", m), b.activate.all(m), b.cache.read(m) || (b.cache.add(m, !0), b.debug("First time tab loaded calling tab init"), x.onFirstLoad.call(k[0], m, h, y)), x.onLoad.call(k[0], m, h, y)
                        } else {
                            if (-1 != n.search("/") || "" === n) return b.error(S.missingTab, R, g, m), !1;
                            if (s = e("#" + n + ', a[name="' + n + '"]'), m = s.closest("[data-tab]").data(C.tab), k = b.get.tabElement(m), s && s.length > 0 && m) return b.debug("Anchor link used, opening parent tab", k, s), k.hasClass(w.active) || setTimeout(function() {
                                b.scrollTo(s)
                            }, 0), b.activate.all(m), b.cache.read(m) || (b.cache.add(m, !0), b.debug("First time tab loaded calling tab init"), x.onFirstLoad.call(k[0], m, h, y)), x.onLoad.call(k[0], m, h, y), !1
                        }
                    })
                },
                scrollTo: function(t) {
                    var i = t && t.length > 0 ? t.offset().top : !1;
                    i !== !1 && (b.debug("Forcing scroll to an in-page link in a hidden tab", i, t), e(n).scrollTop(i))
                },
                update: {
                    content: function(e, t, n) {
                        var o = b.get.tabElement(e),
                            a = o[0];
                        n = n !== i ? n : x.evaluateScripts, n ? (b.debug("Updating HTML and evaluating inline scripts", e, t), o.html(t)) : (b.debug("Updating HTML", e, t), a.innerHTML = t)
                    }
                },
                fetch: {
                    content: function(t, n) {
                        var o, a, r = b.get.tabElement(t),
                            s = {
                                dataType: "html",
                                encodeParameters: !1,
                                on: "now",
                                cache: x.alwaysRefresh,
                                headers: {
                                    "X-Remote": !0
                                },
                                onSuccess: function(e) {
                                    b.cache.add(n, e), b.update.content(t, e), t == v ? (b.debug("Content loaded", t), b.activate.tab(t)) : b.debug("Content loaded in background", t), x.onFirstLoad.call(r[0], t, h, y), x.onLoad.call(r[0], t, h, y)
                                },
                                urlData: {
                                    tab: n
                                }
                            },
                            c = r.api("get request") || !1,
                            l = c && "pending" === c.state();
                        n = n || t, a = b.cache.read(n), x.cache && a ? (b.activate.tab(t), b.debug("Adding cached content", n), "once" == x.evaluateScripts ? b.update.content(t, a, !1) : b.update.content(t, a), x.onLoad.call(r[0], t, h, y)) : l ? (b.set.loading(t), b.debug("Content is already loading", n)) : e.api !== i ? (o = e.extend(!0, {}, x.apiSettings, s), b.debug("Retrieving remote content", n, o), b.set.loading(t), r.api(o)) : b.error(S.api)
                    }
                },
                activate: {
                    all: function(e) {
                        b.activate.tab(e), b.activate.navigation(e)
                    },
                    tab: function(e) {
                        var t = b.get.tabElement(e),
                            n = t.hasClass(w.active);
                        b.verbose("Showing tab content for", t), n || (t.addClass(w.active).siblings(p).removeClass(w.active + " " + w.loading), t.length > 0 && x.onVisible.call(t[0], e))
                    },
                    navigation: function(e) {
                        var t = b.get.navElement(e),
                            n = t.hasClass(w.active);
                        b.verbose("Activating tab navigation for", t, e), n || t.addClass(w.active).siblings(r).removeClass(w.active + " " + w.loading)
                    }
                },
                deactivate: {
                    all: function() {
                        b.deactivate.navigation(), b.deactivate.tabs()
                    },
                    navigation: function() {
                        r.removeClass(w.active)
                    },
                    tabs: function() {
                        p.removeClass(w.active + " " + w.loading)
                    }
                },
                is: {
                    tab: function(e) {
                        return e !== i ? b.get.tabElement(e).length > 0 : !1
                    }
                },
                get: {
                    initialPath: function() {
                        return r.eq(0).data(C.tab) || p.eq(0).data(C.tab)
                    },
                    path: function() {
                        return e.address.value()
                    },
                    defaultPathArray: function(e) {
                        return b.utilities.pathToArray(b.get.defaultPath(e))
                    },
                    defaultPath: function(e) {
                        var t = r.filter("[data-" + C.tab + '^="' + e + '/"]').eq(0),
                            n = t.data(C.tab) || !1;
                        if (n) {
                            if (b.debug("Found default tab", n), F < x.maxDepth) return F++, b.get.defaultPath(n);
                            b.error(S.recursion)
                        } else b.debug("No default tabs found for", e, p);
                        return F = 0, e
                    },
                    navElement: function(e) {
                        return e = e || v, r.filter("[data-" + C.tab + '="' + e + '"]')
                    },
                    tabElement: function(e) {
                        var t, n, i, o;
                        return e = e || v, i = b.utilities.pathToArray(e), o = b.utilities.last(i), t = p.filter("[data-" + C.tab + '="' + e + '"]'), n = p.filter("[data-" + C.tab + '="' + o + '"]'), t.length > 0 ? t : n
                    },
                    tab: function() {
                        return v
                    }
                },
                utilities: {
                    filterArray: function(t, n) {
                        return e.grep(t, function(t) {
                            return -1 == e.inArray(t, n)
                        })
                    },
                    last: function(t) {
                        return e.isArray(t) ? t[t.length - 1] : !1
                    },
                    pathToArray: function(e) {
                        return e === i && (e = v), "string" == typeof e ? e.split("/") : [e]
                    },
                    arrayToPath: function(t) {
                        return e.isArray(t) ? t.join("/") : !1
                    }
                },
                setting: function(t, n) {
                    if (b.debug("Changing setting", t, n), e.isPlainObject(t)) e.extend(!0, x, t);
                    else {
                        if (n === i) return x[t];
                        x[t] = n
                    }
                },
                internal: function(t, n) {
                    if (e.isPlainObject(t)) e.extend(!0, b, t);
                    else {
                        if (n === i) return b[t];
                        b[t] = n
                    }
                },
                debug: function() {
                    x.debug && (x.performance ? b.performance.log(arguments) : (b.debug = Function.prototype.bind.call(console.info, console, x.name + ":"), b.debug.apply(console, arguments)))
                },
                verbose: function() {
                    x.verbose && x.debug && (x.performance ? b.performance.log(arguments) : (b.verbose = Function.prototype.bind.call(console.info, console, x.name + ":"), b.verbose.apply(console, arguments)))
                },
                error: function() {
                    b.error = Function.prototype.bind.call(console.error, console, x.name + ":"), b.error.apply(console, arguments)
                },
                performance: {
                    log: function(e) {
                        var t, n, i;
                        x.performance && (t = (new Date).getTime(), i = c || t, n = t - i, c = t, l.push({
                            Name: e[0],
                            Arguments: [].slice.call(e, 1) || "",
                            Element: D,
                            "Execution Time": n
                        })), clearTimeout(b.performance.timer), b.performance.timer = setTimeout(b.performance.display, 500)
                    },
                    display: function() {
                        var t = x.name + ":",
                            n = 0;
                        c = !1, clearTimeout(b.performance.timer), e.each(l, function(e, t) {
                            n += t["Execution Time"]
                        }), t += " " + n + "ms", s && (t += " '" + s + "'"), (console.group !== i || console.table !== i) && l.length > 0 && (console.groupCollapsed(t), console.table ? console.table(l) : e.each(l, function(e, t) {
                            console.log(t.Name + ": " + t["Execution Time"] + "ms")
                        }), console.groupEnd()), l = []
                    }
                },
                invoke: function(t, n, o) {
                    var r, s, c, l = O;
                    return n = n || m, o = D || o, "string" == typeof t && l !== i && (t = t.split(/[\. ]/), r = t.length - 1, e.each(t, function(n, o) {
                        var a = n != r ? o + t[n + 1].charAt(0).toUpperCase() + t[n + 1].slice(1) : t;
                        if (e.isPlainObject(l[a]) && n != r) l = l[a];
                        else {
                            if (l[a] !== i) return s = l[a], !1;
                            if (!e.isPlainObject(l[o]) || n == r) return l[o] !== i ? (s = l[o], !1) : (b.error(S.method, t), !1);
                            l = l[o]
                        }
                    })), e.isFunction(s) ? c = s.apply(o, n) : s !== i && (c = s), e.isArray(a) ? a.push(c) : a !== i ? a = [a, c] : c !== i && (a = c), s
                }
            }, d ? (O === i && b.initialize(), b.invoke(u)) : (O !== i && O.invoke("destroy"), b.initialize())
        }), a !== i ? a : this
    }, e.tab = function() {
        e(t).tab.apply(this, arguments)
    }, e.fn.tab.settings = {
        name: "Tab",
        namespace: "tab",
        debug: !1,
        verbose: !1,
        performance: !0,
        auto: !1,
        history: !1,
        historyType: "hash",
        path: !1,
        context: !1,
        childrenOnly: !1,
        maxDepth: 25,
        alwaysRefresh: !1,
        cache: !0,
        ignoreFirstLoad: !1,
        apiSettings: !1,
        evaluateScripts: "once",
        onFirstLoad: function(e, t, n) {},
        onLoad: function(e, t, n) {},
        onVisible: function(e, t, n) {},
        onRequest: function(e, t, n) {},
        templates: {
            determineTitle: function(e) {}
        },
        error: {
            api: "You attempted to load content without API module",
            method: "The method you called is not defined",
            missingTab: "Activated tab cannot be found. Tabs are case-sensitive.",
            noContent: "The tab you specified is missing a content url.",
            path: "History enabled, but no path was specified",
            recursion: "Max recursive depth reached",
            legacyInit: "onTabInit has been renamed to onFirstLoad in 2.0, please adjust your code.",
            legacyLoad: "onTabLoad has been renamed to onLoad in 2.0. Please adjust your code",
            state: "History requires Asual's Address library <https://github.com/asual/jquery-address>"
        },
        metadata: {
            tab: "tab",
            loaded: "loaded",
            promise: "promise"
        },
        className: {
            loading: "loading",
            active: "active"
        },
        selector: {
            tabs: ".ui.tab",
            ui: ".ui"
        }
    }
}(jQuery, window, document),
function(e, t, n, i) {
    "use strict";
    e.fn.transition = function() {
        {
            var o, a = e(this),
                r = a.selector || "",
                s = (new Date).getTime(),
                c = [],
                l = arguments,
                u = l[0],
                d = [].slice.call(arguments, 1),
                m = "string" == typeof u;
            t.requestAnimationFrame || t.mozRequestAnimationFrame || t.webkitRequestAnimationFrame || t.msRequestAnimationFrame || function(e) {
                setTimeout(e, 0)
            }
        }
        return a.each(function(t) {
            var f, g, p, v, h, b, y, x, w, C = e(this),
                k = this;
            w = {
                initialize: function() {
                    f = w.get.settings.apply(k, l), v = f.className, p = f.error, h = f.metadata, x = "." + f.namespace, y = "module-" + f.namespace, g = C.data(y) || w, b = w.get.animationEndEvent(), m && (m = w.invoke(u)), m === !1 && (w.verbose("Converted arguments into settings object", f), f.interval ? w.delay(f.animate) : w.animate(), w.instantiate())
                },
                instantiate: function() {
                    w.verbose("Storing instance of module", w), g = w, C.data(y, g)
                },
                destroy: function() {
                    w.verbose("Destroying previous module for", k), C.removeData(y)
                },
                refresh: function() {
                    w.verbose("Refreshing display type on next animation"), delete w.displayType
                },
                forceRepaint: function() {
                    w.verbose("Forcing element repaint");
                    var e = C.parent(),
                        t = C.next();
                    0 === t.length ? C.detach().appendTo(e) : C.detach().insertBefore(t)
                },
                repaint: function() {
                    w.verbose("Repainting element");
                    k.offsetWidth
                },
                delay: function(e) {
                    var n, o, r = w.get.animationDirection();
                    r || (r = w.can.transition() ? w.get.direction() : "static"), e = e !== i ? e : f.interval, n = "auto" == f.reverse && r == v.outward, o = n || 1 == f.reverse ? (a.length - t) * f.interval : t * f.interval, w.debug("Delaying animation by", o), setTimeout(w.animate, o)
                },
                animate: function(e) {
                    if (f = e || f, !w.is.supported()) return w.error(p.support), !1;
                    if (w.debug("Preparing animation", f.animation), w.is.animating()) {
                        if (f.queue) return !f.allowRepeats && w.has.direction() && w.is.occurring() && w.queuing !== !0 ? w.debug("Animation is currently occurring, preventing queueing same animation", f.animation) : w.queue(f.animation), !1;
                        if (!f.allowRepeats && w.is.occurring()) return w.debug("Animation is already occurring, will not execute repeated animation", f.animation), !1;
                        w.debug("New animation started, completing previous early", f.animation), g.complete()
                    }
                    w.can.animate() ? w.set.animating(f.animation) : w.error(p.noAnimation, f.animation, k)
                },
                reset: function() {
                    w.debug("Resetting animation to beginning conditions"), w.remove.animationCallbacks(), w.restore.conditions(), w.remove.animating()
                },
                queue: function(e) {
                    w.debug("Queueing animation of", e), w.queuing = !0, C.one(b + ".queue" + x, function() {
                        w.queuing = !1, w.repaint(), w.animate.apply(this, f)
                    })
                },
                complete: function(e) {
                    w.debug("Animation complete", f.animation), w.remove.completeCallback(), w.remove.failSafe(), w.is.looping() || (w.is.outward() ? (w.verbose("Animation is outward, hiding element"), w.restore.conditions(), w.hide()) : w.is.inward() ? (w.verbose("Animation is outward, showing element"), w.restore.conditions(), w.show()) : w.restore.conditions())
                },
                force: {
                    visible: function() {
                        var e = C.attr("style"),
                            t = w.get.userStyle(),
                            n = w.get.displayType(),
                            o = t + "display: " + n + " !important;",
                            a = C.css("display"),
                            r = e === i || "" === e;

                        a !== n ? (w.verbose("Overriding default display to show element", n), C.attr("style", o)) : r && C.removeAttr("style")
                    },
                    hidden: function() {
                        var e = C.attr("style"),
                            t = C.css("display"),
                            n = e === i || "" === e;
                        "none" === t || w.is.hidden() ? n && C.removeAttr("style") : (w.verbose("Overriding default display to hide element"), C.css("display", "none"))
                    }
                },
                has: {
                    direction: function(t) {
                        var n = !1;
                        return t = t || f.animation, "string" == typeof t && (t = t.split(" "), e.each(t, function(e, t) {
                            (t === v.inward || t === v.outward) && (n = !0)
                        })), n
                    },
                    inlineDisplay: function() {
                        var t = C.attr("style") || "";
                        return e.isArray(t.match(/display.*?;/, ""))
                    }
                },
                set: {
                    animating: function(e) {
                        var t;
                        w.remove.completeCallback(), e = e || f.animation, t = w.get.animationClass(e), w.save.animation(t), w.force.visible(), w.remove.hidden(), w.remove.direction(), w.start.animation(t)
                    },
                    duration: function(e, t) {
                        t = t || f.duration, t = "number" == typeof t ? t + "ms" : t, (t || 0 === t) && (w.verbose("Setting animation duration", t), C.css({
                            "animation-duration": t
                        }))
                    },
                    direction: function(e) {
                        e = e || w.get.direction(), e == v.inward ? w.set.inward() : w.set.outward()
                    },
                    looping: function() {
                        w.debug("Transition set to loop"), C.addClass(v.looping)
                    },
                    hidden: function() {
                        C.addClass(v.transition).addClass(v.hidden)
                    },
                    inward: function() {
                        w.debug("Setting direction to inward"), C.removeClass(v.outward).addClass(v.inward)
                    },
                    outward: function() {
                        w.debug("Setting direction to outward"), C.removeClass(v.inward).addClass(v.outward)
                    },
                    visible: function() {
                        C.addClass(v.transition).addClass(v.visible)
                    }
                },
                start: {
                    animation: function(e) {
                        e = e || w.get.animationClass(), w.debug("Starting tween", e), C.addClass(e).one(b + ".complete" + x, w.complete), f.useFailSafe && w.add.failSafe(), w.set.duration(f.duration), f.onStart.call(k)
                    }
                },
                save: {
                    animation: function(e) {
                        w.cache || (w.cache = {}), w.cache.animation = e
                    },
                    displayType: function(e) {
                        "none" !== e && C.data(h.displayType, e)
                    },
                    transitionExists: function(t, n) {
                        e.fn.transition.exists[t] = n, w.verbose("Saving existence of transition", t, n)
                    }
                },
                restore: {
                    conditions: function() {
                        var e = w.get.currentAnimation();
                        e && (C.removeClass(e), w.verbose("Removing animation class", w.cache)), w.remove.duration()
                    }
                },
                add: {
                    failSafe: function() {
                        var e = w.get.duration();
                        w.timer = setTimeout(function() {
                            C.triggerHandler(b)
                        }, e + f.failSafeDelay), w.verbose("Adding fail safe timer", w.timer)
                    }
                },
                remove: {
                    animating: function() {
                        C.removeClass(v.animating)
                    },
                    animationCallbacks: function() {
                        w.remove.queueCallback(), w.remove.completeCallback()
                    },
                    queueCallback: function() {
                        C.off(".queue" + x)
                    },
                    completeCallback: function() {
                        C.off(".complete" + x)
                    },
                    display: function() {
                        C.css("display", "")
                    },
                    direction: function() {
                        C.removeClass(v.inward).removeClass(v.outward)
                    },
                    duration: function() {
                        C.css("animation-duration", "")
                    },
                    failSafe: function() {
                        w.verbose("Removing fail safe timer", w.timer), w.timer && clearTimeout(w.timer)
                    },
                    hidden: function() {
                        C.removeClass(v.hidden)
                    },
                    visible: function() {
                        C.removeClass(v.visible)
                    },
                    looping: function() {
                        w.debug("Transitions are no longer looping"), w.is.looping() && (w.reset(), C.removeClass(v.looping))
                    },
                    transition: function() {
                        C.removeClass(v.visible).removeClass(v.hidden)
                    }
                },
                get: {
                    settings: function(t, n, i) {
                        return "object" == typeof t ? e.extend(!0, {}, e.fn.transition.settings, t) : "function" == typeof i ? e.extend({}, e.fn.transition.settings, {
                            animation: t,
                            onComplete: i,
                            duration: n
                        }) : "string" == typeof n || "number" == typeof n ? e.extend({}, e.fn.transition.settings, {
                            animation: t,
                            duration: n
                        }) : "object" == typeof n ? e.extend({}, e.fn.transition.settings, n, {
                            animation: t
                        }) : "function" == typeof n ? e.extend({}, e.fn.transition.settings, {
                            animation: t,
                            onComplete: n
                        }) : e.extend({}, e.fn.transition.settings, {
                            animation: t
                        })
                    },
                    animationClass: function(e) {
                        var t = e || f.animation,
                            n = w.can.transition() && !w.has.direction() ? w.get.direction() + " " : "";
                        return v.animating + " " + v.transition + " " + n + t
                    },
                    currentAnimation: function() {
                        return w.cache && w.cache.animation !== i ? w.cache.animation : !1
                    },
                    currentDirection: function() {
                        return w.is.inward() ? v.inward : v.outward
                    },
                    direction: function() {
                        return w.is.hidden() || !w.is.visible() ? v.inward : v.outward
                    },
                    animationDirection: function(t) {
                        var n;
                        return t = t || f.animation, "string" == typeof t && (t = t.split(" "), e.each(t, function(e, t) {
                            t === v.inward ? n = v.inward : t === v.outward && (n = v.outward)
                        })), n ? n : !1
                    },
                    duration: function(e) {
                        return e = e || f.duration, e === !1 && (e = C.css("animation-duration") || 0), "string" == typeof e ? e.indexOf("ms") > -1 ? parseFloat(e) : 1e3 * parseFloat(e) : e
                    },
                    displayType: function() {
                        return f.displayType ? f.displayType : (C.data(h.displayType) === i && w.can.transition(!0), C.data(h.displayType))
                    },
                    userStyle: function(e) {
                        return e = e || C.attr("style") || "", e.replace(/display.*?;/, "")
                    },
                    transitionExists: function(t) {
                        return e.fn.transition.exists[t]
                    },
                    animationStartEvent: function() {
                        var e, t = n.createElement("div"),
                            o = {
                                animation: "animationstart",
                                OAnimation: "oAnimationStart",
                                MozAnimation: "mozAnimationStart",
                                WebkitAnimation: "webkitAnimationStart"
                            };
                        for (e in o)
                            if (t.style[e] !== i) return o[e];
                        return !1
                    },
                    animationEndEvent: function() {
                        var e, t = n.createElement("div"),
                            o = {
                                animation: "animationend",
                                OAnimation: "oAnimationEnd",
                                MozAnimation: "mozAnimationEnd",
                                WebkitAnimation: "webkitAnimationEnd"
                            };
                        for (e in o)
                            if (t.style[e] !== i) return o[e];
                        return !1
                    }
                },
                can: {
                    transition: function(t) {
                        var n, o, a, r, s, c, l, u = f.animation,
                            d = w.get.transitionExists(u);
                        if (d === i || t) {
                            if (w.verbose("Determining whether animation exists"), n = C.attr("class"), o = C.prop("tagName"), a = e("<" + o + " />").addClass(n).insertAfter(C), r = a.addClass(u).removeClass(v.inward).removeClass(v.outward).addClass(v.animating).addClass(v.transition).css("animationName"), s = a.addClass(v.inward).css("animationName"), l = a.attr("class", n).removeAttr("style").removeClass(v.hidden).removeClass(v.visible).show().css("display"), w.verbose("Determining final display state", l), w.save.displayType(l), a.remove(), r != s) w.debug("Direction exists for animation", u), c = !0;
                            else {
                                if ("none" == r || !r) return void w.debug("No animation defined in css", u);
                                w.debug("Static animation found", u, l), c = !1
                            }
                            w.save.transitionExists(u, c)
                        }
                        return d !== i ? d : c
                    },
                    animate: function() {
                        return w.can.transition() !== i
                    }
                },
                is: {
                    animating: function() {
                        return C.hasClass(v.animating)
                    },
                    inward: function() {
                        return C.hasClass(v.inward)
                    },
                    outward: function() {
                        return C.hasClass(v.outward)
                    },
                    looping: function() {
                        return C.hasClass(v.looping)
                    },
                    occurring: function(e) {
                        return e = e || f.animation, e = "." + e.replace(" ", "."), C.filter(e).length > 0
                    },
                    visible: function() {
                        return C.is(":visible")
                    },
                    hidden: function() {
                        return "hidden" === C.css("visibility")
                    },
                    supported: function() {
                        return b !== !1
                    }
                },
                hide: function() {
                    w.verbose("Hiding element"), w.is.animating() && w.reset(), k.blur(), w.remove.display(), w.remove.visible(), w.set.hidden(), w.force.hidden(), f.onHide.call(k), f.onComplete.call(k)
                },
                show: function(e) {
                    w.verbose("Showing element", e), w.remove.hidden(), w.set.visible(), w.force.visible(), f.onShow.call(k), f.onComplete.call(k)
                },
                toggle: function() {
                    w.is.visible() ? w.hide() : w.show()
                },
                stop: function() {
                    w.debug("Stopping current animation"), C.triggerHandler(b)
                },
                stopAll: function() {
                    w.debug("Stopping all animation"), w.remove.queueCallback(), C.triggerHandler(b)
                },
                clear: {
                    queue: function() {
                        w.debug("Clearing animation queue"), w.remove.queueCallback()
                    }
                },
                enable: function() {
                    w.verbose("Starting animation"), C.removeClass(v.disabled)
                },
                disable: function() {
                    w.debug("Stopping animation"), C.addClass(v.disabled)
                },
                setting: function(t, n) {
                    if (w.debug("Changing setting", t, n), e.isPlainObject(t)) e.extend(!0, f, t);
                    else {
                        if (n === i) return f[t];
                        f[t] = n
                    }
                },
                internal: function(t, n) {
                    if (e.isPlainObject(t)) e.extend(!0, w, t);
                    else {
                        if (n === i) return w[t];
                        w[t] = n
                    }
                },
                debug: function() {
                    f.debug && (f.performance ? w.performance.log(arguments) : (w.debug = Function.prototype.bind.call(console.info, console, f.name + ":"), w.debug.apply(console, arguments)))
                },
                verbose: function() {
                    f.verbose && f.debug && (f.performance ? w.performance.log(arguments) : (w.verbose = Function.prototype.bind.call(console.info, console, f.name + ":"), w.verbose.apply(console, arguments)))
                },
                error: function() {
                    w.error = Function.prototype.bind.call(console.error, console, f.name + ":"), w.error.apply(console, arguments)
                },
                performance: {
                    log: function(e) {
                        var t, n, i;
                        f.performance && (t = (new Date).getTime(), i = s || t, n = t - i, s = t, c.push({
                            Name: e[0],
                            Arguments: [].slice.call(e, 1) || "",
                            Element: k,
                            "Execution Time": n
                        })), clearTimeout(w.performance.timer), w.performance.timer = setTimeout(w.performance.display, 500)
                    },
                    display: function() {
                        var t = f.name + ":",
                            n = 0;
                        s = !1, clearTimeout(w.performance.timer), e.each(c, function(e, t) {
                            n += t["Execution Time"]
                        }), t += " " + n + "ms", r && (t += " '" + r + "'"), a.length > 1 && (t += " (" + a.length + ")"), (console.group !== i || console.table !== i) && c.length > 0 && (console.groupCollapsed(t), console.table ? console.table(c) : e.each(c, function(e, t) {
                            console.log(t.Name + ": " + t["Execution Time"] + "ms")
                        }), console.groupEnd()), c = []
                    }
                },
                invoke: function(t, n, a) {
                    var r, s, c, l = g;
                    return n = n || d, a = k || a, "string" == typeof t && l !== i && (t = t.split(/[\. ]/), r = t.length - 1, e.each(t, function(n, o) {
                        var a = n != r ? o + t[n + 1].charAt(0).toUpperCase() + t[n + 1].slice(1) : t;
                        if (e.isPlainObject(l[a]) && n != r) l = l[a];
                        else {
                            if (l[a] !== i) return s = l[a], !1;
                            if (!e.isPlainObject(l[o]) || n == r) return l[o] !== i ? (s = l[o], !1) : !1;
                            l = l[o]
                        }
                    })), e.isFunction(s) ? c = s.apply(a, n) : s !== i && (c = s), e.isArray(o) ? o.push(c) : o !== i ? o = [o, c] : c !== i && (o = c), s !== i ? s : !1
                }
            }, w.initialize()
        }), o !== i ? o : this
    }, e.fn.transition.exists = {}, e.fn.transition.settings = {
        name: "Transition",
        debug: !1,
        verbose: !1,
        performance: !0,
        namespace: "transition",
        interval: 0,
        reverse: "auto",
        onStart: function() {},
        onComplete: function() {},
        onShow: function() {},
        onHide: function() {},
        useFailSafe: !0,
        failSafeDelay: 100,
        allowRepeats: !1,
        displayType: !1,
        animation: "fade",
        duration: !1,
        queue: !0,
        metadata: {
            displayType: "display"
        },
        className: {
            animating: "animating",
            disabled: "disabled",
            hidden: "hidden",
            inward: "in",
            loading: "loading",
            looping: "looping",
            outward: "out",
            transition: "transition",
            visible: "visible"
        },
        error: {
            noAnimation: "There is no css animation matching the one you specified. Please make sure your css is vendor prefixed, and you have included transition css.",
            repeated: "That animation is already occurring, cancelling repeated animation",
            method: "The method you called is not defined",
            support: "This browser does not support CSS animations"
        }
    }
}(jQuery, window, document),
function(e, t, n, i) {
    "use strict";
    e.fn.video = function(n) {
        {
            var o, a = e(this),
                r = a.selector || "",
                s = (new Date).getTime(),
                c = [],
                l = arguments[0],
                u = "string" == typeof l,
                d = [].slice.call(arguments, 1);
            t.requestAnimationFrame || t.mozRequestAnimationFrame || t.webkitRequestAnimationFrame || t.msRequestAnimationFrame || function(e) {
                setTimeout(e, 0)
            }
        }
        return a.each(function() {
            var m, f = e.isPlainObject(n) ? e.extend(!0, {}, e.fn.video.settings, n) : e.extend({}, e.fn.video.settings),
                g = f.selector,
                p = f.className,
                v = f.error,
                h = f.metadata,
                b = f.namespace,
                y = f.templates,
                x = "." + b,
                w = "module-" + b,
                C = (e(t), e(this)),
                k = C.find(g.placeholder),
                S = C.find(g.playButton),
                T = C.find(g.embed),
                A = this,
                R = C.data(w);
            m = {
                initialize: function() {
                    m.debug("Initializing video"), m.create(), k.on("click" + x, m.play), S.on("click" + x, m.play), m.instantiate()
                },
                instantiate: function() {
                    m.verbose("Storing instance of module", m), R = m, C.data(w, m)
                },
                create: function() {
                    var e = C.data(h.image),
                        t = y.video(e);
                    C.html(t), m.refresh(), e || m.play(), m.debug("Creating html for video element", t)
                },
                destroy: function() {
                    m.verbose("Destroying previous instance of video"), m.reset(), C.removeData(w).off(x), k.off(x), S.off(x)
                },
                refresh: function() {
                    m.verbose("Refreshing selector cache"), k = C.find(g.placeholder), S = C.find(g.playButton), T = C.find(g.embed)
                },
                change: function(e, t, n) {
                    m.debug("Changing video to ", e, t, n), C.data(h.source, e).data(h.id, t).data(h.url, n), f.onChange()
                },
                reset: function() {
                    m.debug("Clearing video embed and showing placeholder"), C.removeClass(p.active), T.html(" "), k.show(), f.onReset()
                },
                play: function() {
                    m.debug("Playing video");
                    var e = C.data(h.source) || !1,
                        t = C.data(h.url) || !1,
                        n = C.data(h.id) || !1;
                    T.html(m.generate.html(e, n, t)), C.addClass(p.active), f.onPlay()
                },
                get: {
                    source: function(e) {
                        return "string" != typeof e ? !1 : -1 !== e.search("youtube.com") ? "youtube" : -1 !== e.search("vimeo.com") ? "vimeo" : !1
                    },
                    id: function(e) {
                        return e.match(f.regExp.youtube) ? e.match(f.regExp.youtube)[1] : e.match(f.regExp.vimeo) ? e.match(f.regExp.vimeo)[2] : !1
                    }
                },
                generate: {
                    html: function(e, t, n) {
                        m.debug("Generating embed html");
                        var i;
                        return e = e || f.source, t = t || f.id, e && t || n ? (e && t || (e = m.get.source(n), t = m.get.id(n)), "vimeo" == e ? i = '<iframe src="//player.vimeo.com/video/' + t + "?=" + m.generate.url(e) + '" width="100%" height="100%" frameborder="0" webkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe>' : "youtube" == e && (i = '<iframe src="//www.youtube.com/embed/' + t + "?=" + m.generate.url(e) + '" width="100%" height="100%" frameborder="0" webkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe>')) : m.error(v.noVideo), i
                    },
                    url: function(e) {
                        var t = f.api ? 1 : 0,
                            n = "auto" === f.autoplay ? C.data("image") !== i : f.autoplay,
                            o = f.hd ? 1 : 0,
                            a = f.showUI ? 1 : 0,
                            r = f.showUI ? 0 : 1,
                            s = "";
                        return "vimeo" == e && (s = "api=" + t + "&amp;title=" + a + "&amp;byline=" + a + "&amp;portrait=" + a + "&amp;autoplay=" + n, f.color && (s += "&amp;color=" + f.color)), "ustream" == e ? (s = "autoplay=" + n, f.color && (s += "&amp;color=" + f.color)) : "youtube" == e && (s = "enablejsapi=" + t + "&amp;autoplay=" + n + "&amp;autohide=" + r + "&amp;hq=" + o + "&amp;modestbranding=1", f.color && (s += "&amp;color=" + f.color)), s
                    }
                },
                setting: function(t, n) {
                    if (m.debug("Changing setting", t, n), e.isPlainObject(t)) e.extend(!0, f, t);
                    else {
                        if (n === i) return f[t];
                        f[t] = n
                    }
                },
                internal: function(t, n) {
                    if (e.isPlainObject(t)) e.extend(!0, m, t);
                    else {
                        if (n === i) return m[t];
                        m[t] = n
                    }
                },
                debug: function() {
                    f.debug && (f.performance ? m.performance.log(arguments) : (m.debug = Function.prototype.bind.call(console.info, console, f.name + ":"), m.debug.apply(console, arguments)))
                },
                verbose: function() {
                    f.verbose && f.debug && (f.performance ? m.performance.log(arguments) : (m.verbose = Function.prototype.bind.call(console.info, console, f.name + ":"), m.verbose.apply(console, arguments)))
                },
                error: function() {
                    m.error = Function.prototype.bind.call(console.error, console, f.name + ":"), m.error.apply(console, arguments)
                },
                performance: {
                    log: function(e) {
                        var t, n, i;
                        f.performance && (t = (new Date).getTime(), i = s || t, n = t - i, s = t, c.push({
                            Name: e[0],
                            Arguments: [].slice.call(e, 1) || "",
                            Element: A,
                            "Execution Time": n
                        })), clearTimeout(m.performance.timer), m.performance.timer = setTimeout(m.performance.display, 100)
                    },
                    display: function() {
                        var t = f.name + ":",
                            n = 0;
                        s = !1, clearTimeout(m.performance.timer), e.each(c, function(e, t) {
                            n += t["Execution Time"]
                        }), t += " " + n + "ms", r && (t += " '" + r + "'"), a.length > 1 && (t += " (" + a.length + ")"), (console.group !== i || console.table !== i) && c.length > 0 && (console.groupCollapsed(t), console.table ? console.table(c) : e.each(c, function(e, t) {
                            console.log(t.Name + ": " + t["Execution Time"] + "ms")
                        }), console.groupEnd()), c = []
                    }
                },
                invoke: function(t, n, a) {
                    var r, s, c, l = R;
                    return n = n || d, a = A || a, "string" == typeof t && l !== i && (t = t.split(/[\. ]/), r = t.length - 1, e.each(t, function(n, o) {
                        var a = n != r ? o + t[n + 1].charAt(0).toUpperCase() + t[n + 1].slice(1) : t;
                        if (e.isPlainObject(l[a]) && n != r) l = l[a];
                        else {
                            if (l[a] !== i) return s = l[a], !1;
                            if (!e.isPlainObject(l[o]) || n == r) return l[o] !== i ? (s = l[o], !1) : (m.error(v.method, t), !1);
                            l = l[o]
                        }
                    })), e.isFunction(s) ? c = s.apply(a, n) : s !== i && (c = s), e.isArray(o) ? o.push(c) : o !== i ? o = [o, c] : c !== i && (o = c), s
                }
            }, u ? (R === i && m.initialize(), m.invoke(l)) : (R !== i && R.invoke("destroy"), m.initialize())
        }), o !== i ? o : this
    }, e.fn.video.settings = {
        name: "Video",
        namespace: "video",
        debug: !1,
        verbose: !0,
        performance: !0,
        metadata: {
            id: "id",
            image: "image",
            source: "source",
            url: "url"
        },
        source: !1,
        url: !1,
        id: !1,
        aspectRatio: 16 / 9,
        onPlay: function() {},
        onReset: function() {},
        onChange: function() {},
        onPause: function() {},
        onStop: function() {},
        width: "auto",
        height: "auto",
        autoplay: "auto",
        color: "#442359",
        hd: !0,
        showUI: !1,
        api: !0,
        regExp: {
            youtube: /^(?:https?:\/\/)?(?:www\.)?(?:youtu\.be\/|youtube\.com\/(?:embed\/|v\/|watch\?v=|watch\?.+&v=))((\w|-){11})(?:\S+)?$/,
            vimeo: /http:\/\/(www\.)?vimeo.com\/(\d+)($|\/)/
        },
        error: {
            noVideo: "No video specified",
            method: "The method you called is not defined"
        },
        className: {
            active: "active"
        },
        selector: {
            embed: ".embed",
            placeholder: ".placeholder",
            playButton: ".play"
        }
    }, e.fn.video.settings.templates = {
        video: function(e) {
            var t = "";
            return e && (t += '<i class="video play icon"></i><img class="placeholder" src="' + e + '">'), t += '<div class="embed"></div>'
        }
    }
}(jQuery, window, document);