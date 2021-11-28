// EVENTS

function add_event(el, types, handler, useCapture) {
    // vars
    if (typeof useCapture === 'undefined') useCapture = false;
    el = ge(el);
    // for IE
    if (el.setInterval && el !== window) el = window;
    // add listeners
    types.split(/\s+/).forEach(function(type, index) {
        if (el.addEventListener) el.addEventListener(type, handler, useCapture);
        else if (el.attachEvent) el.attachEvent('on' + type, handler);
    });
    // clear
    el = null;
}

function remove_event(el, types, handler, useCapture) {
    // vars
    if (typeof useCapture === 'undefined') useCapture = false;
    el = ge(el);
    // validate
    if (!el) return;
    // remove listeners
    types.split(/\s+/).forEach(function(type, index) {
        if (el.removeEventListener) el.removeEventListener(type, handler, useCapture);
        else if (el.detachEvent) el.detachEvent('on' + type, handler);
    });
}

function cancel_event(event) {
    event = (event || window.event);
    if (!event) return false;
    while (event.originalEvent) {
        event = event.originalEvent;
    }
    if (event.preventDefault) event.preventDefault();
    if (event.stopPropagation) event.stopPropagation();
    if (event.stopImmediatePropagation) event.stopImmediatePropagation();
    event.cancelBubble = true; // for IE
    event.returnValue = false;
    return false;
}

// DOM

function ge(id) {
    return 'string' == typeof id || 'number' == typeof id ? document.getElementById(id) : id
}

function re(el) {
    if ((el = ge(el)) && el.parentNode) el.parentNode.removeChild(el);
    return el;
}

function re_by_class(name) {
    var paras = document.getElementsByClassName(name);
    while (paras[0]) paras[0].parentNode.removeChild(paras[0]);
}

function html(el, html) {
    if (el = ge(el)) el.innerHTML = html;
}

function gv(el) {
    return (el = ge(el)) ? el.value : '';
}

function sv(el, value) {
    if (el = ge(el)) el.value = value;
}

function show(el) {
    set_style(el, 'display', 'block');
}

function hide(el) {
    set_style(el, 'display', 'none');
}

function gc(el) {
    return (document.getElementsByClassName) ? document.getElementsByClassName(el) : document.querySelectorAll('.' + el);
}

function qs(el, node) {
    return (node || document).querySelector(el);
}

function qs_all(el, node) {
    return (ge(node) || document).querySelectorAll(el)
}

function ef(el) {
    var el = ge(el);
    el.focus();
    el.selectionStart = el.value.length;
}

function prepend(el, data) {
    if (el = ge(el)) el.insertAdjacentHTML('afterBegin', data);
}

function append(el, data) {
    if (el = ge(el)) el.insertAdjacentHTML('beforeEnd', data);
}

function after(el, data) {
    if (el = ge(el)) el.insertAdjacentHTML('afterEnd', data);
}

function before(el, data) {
    if (el = ge(el)) el.insertAdjacentHTML('beforeBegin', data);
}

function el_prev(el) {
    var el_prev = el.previousSibling;
    while (el_prev && el_prev.nodeType !== 1) el_prev = el_prev.previousSibling;
    return el_prev;
}

function el_next(el) {
    var el_next = el.nextSibling;
    while (el_next && el_next.nodeType !== 1) el_next = el_next.nextSibling;
    return el_next;
}

// SIZES

function scroll_top() {
    return window.pageYOffset ||
        document.body.scrollTop ||
        document.documentElement.scrollTop || 0;
}

function w_height() {
    return Math.max(
        window.innerHeight || 0,
        document.documentElement.clientHeight || 0,
        document.body.clientHeight || 0
    );
}

function d_height() {
    return Math.max(
        document.body.scrollHeight || 0,
        document.documentElement.scrollHeight || 0,
        document.body.offsetHeight || 0,
        document.documentElement.offsetHeight || 0,
        document.body.clientHeight || 0,
        document.documentElement.clientHeight || 0
    );
}

function w_width() {
    return Math.max(
        window.innerWidth || 0,
        document.documentElement.clientWidth || 0,
        document.body.clientWidth || 0
    );
}

// CSS

function has_class(el, name) {
    el = ge(el);
    return el && 1 === el.nodeType && (' ' + el.className + ' ').replace(/[\t\r\n\f]/g, ' ').indexOf(' ' + name + ' ') >= 0;
}

function add_class(el, name) {
    el = ge(el);
    el && !has_class(el, name) && (el.className = (el.className ? el.className + ' ' : '') + name);
}

function add_class_delayed(el, name, timeout) {
    return setTimeout(function() {add_class(el, name);}, timeout);
}

function add_classes(el, names) {
    names.split(/\s+/).forEach(function(name) {add_class(el, name);});
}

function add_class_by_class(search_name, names) {
    var els = document.getElementsByClassName(search_name);
    for (var i = 0; i < els.length; i++) add_class(els[i], names);
}

function remove_class(el, name) {
    el = ge(el);
    el && (el.className = trim((el.className || '').replace((new RegExp('(\\s|^)' + name + '(\\s|$)')), ' ')));
}

function remove_classes(el, names) {
    names.split(/\s+/).forEach(function(name) {
        remove_class(el, name);
    });
}

function remove_class_delayed(el, name, timeout) {
    return setTimeout(function() {remove_class(el, name);}, timeout);
}

function remove_class_by_class(search_name, names) {
    var els = document.getElementsByClassName(search_name);
    for (var i = 0; i < els.length; i++) remove_class(els[i], names);
}

function replace_class(el, old_name, new_name) {
    remove_class(el, old_name);
    add_class(el, new_name);
}

function replace_classes(el, old_names, new_names) {
    remove_classes(el, old_names);
    add_classes(el, new_names);
}

function replace_class_by_class(search_name, old_names, new_names) {
    var els = document.getElementsByClassName(search_name);
    for (var i = 0; i < els.length; i++) replace_class(els[i], old_names, new_names);
}

function toggle_class(el, name) {
    has_class(el, name) ? remove_class(el, name) : add_class(el, name);
}

function attr(el, attr_name, value) {
    return el = ge(el), void 0 === value ? el.getAttribute(attr_name) : (el.setAttribute(attr_name, value), value);
}

function remove_attr(el, attr_name) {
    ge(el).removeAttribute(attr_name);
}

function set_style(el, name, value) {
    // vars
    el = ge(el);
    if (!el) return;
    var is_number = typeof (value) == 'number';
    // actions
    if (is_number && (/height|width/i).test(name)) value = Math.abs(value);
    el.style[name] = is_number && !(/font-?weight|line-?height|opacity|z-?index|zoom/i).test(name) ? value + 'px' : value;
}

// REQUESTS

function request(data, callback) {
    var xhr = new XMLHttpRequest();
    if (!xhr) return;
    xhr.open('POST', '/call.php', true);
    xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
    xhr.send(request_serialize(data));
    xhr.onreadystatechange = function() {
        if (xhr.readyState !== 4) return;
        if (xhr.status === 200) callback(JSON.parse(xhr.responseText));
        xhr = null;
    }
}

function request_file(data, callback) {
    var xhr = new XMLHttpRequest();
    if (!xhr) return;
    xhr.open('POST', '/call.php', true);
    xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
    xhr.responseType = 'arraybuffer';
    xhr.onload = function(e) {
        callback(xhr.response);
    };
    xhr.send(request_serialize(data));
}

function s2ab(s) {
    var buf = new ArrayBuffer(s.length);
    var view = new Uint8Array(buf);
    for (var i = 0; i !== s.length; ++i) view[i] = s.charCodeAt(i) & 0xFF;
    return buf;
}

function request_serialize(data, prefix) {
    // vars
    var str = [], p;
    // serialize
    for (p in data) {
        if (data.hasOwnProperty(p)) {
            var k = prefix ? prefix + "[" + p + "]" : p;
            var v = data[p];
            str.push((v !== null && typeof v === "object") ? request_serialize(v, k) : encodeURIComponent(k) + "=" + encodeURIComponent(v));
        }
    }
    // output
    return str.join("&");
}

function create_upload(callback, filename, location, data, multiple) {
    // vars
    var html = '';
    // html
    html += '<form id="tmp_form" enctype="multipart/form-data">';
    if (multiple === true) html += '<input id="tmp_file" name="' + filename + '[]" multiple type="file" onchange="' + callback + '();">';
    else html += '<input id="tmp_file" name="' + filename + '" type="file" onchange="' + callback + '();">';
    if (location) {
        Object.keys(location).forEach(function(key) {
            html += '<input type="hidden" name="location[' + key + ']" value="' + location[key] + '">';
        });
    }
    if (data) {
        Object.keys(data).forEach(function(key) {
            html += '<input type="hidden" name="data[' + key + ']" value="' + data[key] + '">';
        });
    }
    html += '</form>';
    // actions
    re('tmp_form');
    append(document.body, html);
    on_click('tmp_file');
}

function request_upload(callback) {
    var formData = new FormData(ge('tmp_form'));
    var XHR = "onload" in new XMLHttpRequest() ? XMLHttpRequest : XDomainRequest;
    var xhr = new XHR();
    xhr.open('POST', '/call.php', true);
    xhr.onreadystatechange = function() {
        if (xhr.readyState !== 4) return;
        if (xhr.status === 200) {
            re('tmp_form');
            callback(JSON.parse(xhr.responseText));
        }
        xhr = null;
    }
    xhr.send(formData);
}

function download_file(name, type, data) {
    var html = document.createElement('a');
    document.body.appendChild(html);
    var blob = new Blob([data], {type: type});
    var url = window.URL.createObjectURL(blob);
    html.href = url;
    html.download = name;
    html.click();
    setTimeout(function() {
        window.URL.revokeObjectURL(url);
        document.body.removeChild(html);
    }, 0);
}

// COOKIES

function get_cookie(name) {
    var begin = document.cookie.indexOf(name + '=');
    if (-1 == begin) return null;
    begin += name.length + 1;
    var end = document.cookie.indexOf('; ', begin);
    if (-1 == end) end = document.cookie.length;
    return document.cookie.substring(begin, end);
}

function set_cookie(name, value, expires_sec, path, domain, secure) {
    // expires
    var now = new Date();
    var expires = new Date();
    expires.setTime(now.getTime() + expires_sec * 1000);
    // handling
    expires instanceof Date ? expires = expires.toGMTString() : typeof (expires) == 'number' && (expires = (new Date(+(new Date) + expires * 1e3)).toGMTString());
    var r = [name + '=' + escape(value)], s, i;
    for (i in s = {expires: expires, path: path, domain: domain}) {
        s[i] && r.push(i + '=' + s[i]);
    }
    // output
    return secure && r.push('secure'), document.cookie = r.join(';'), true;
}

function delete_cookie(name) {
    var date = new Date();
    date.setTime(date.getTime() - 1);
    document.cookie = name += "=; expires=" + date.toGMTString();
}

// ERRORS

function input_error_show(el) {
    add_class(el, 'error');
}

function input_error_hide(el) {
    remove_class(el, 'error');
}

function m_errors(errors) {
    for (var key in errors) m_error_show(key, errors[key]);
}

function m_error_show(el, text) {
    add_class(el, 'error');
    html('e_' + el, text);
}

function m_error_hide(el) {
    remove_class(el, 'error');
    html('e_' + el, '');
}

function errors(errors) {
    for (var key in errors) error_show(key, errors[key]);
}

function error_show(el, text) {
    html(el + '_error', text);
    add_class(el + '_error', 'active');
}

function error_hide(el) {
    html(el + '_error', '');
    remove_class(el + '_error', 'active');
}

// SCROLL

function scrollbar_width() {
    // add outer div
    var outer = document.createElement('div');
    outer.style.visibility = 'hidden';
    outer.style.width = '100px';
    outer.style.msOverflowStyle = 'scrollbar'; // needed for WinJS apps
    document.body.appendChild(outer);
    var width_no_scroll = outer.offsetWidth;
    // force scrollbars
    outer.style.overflow = 'scroll';
    // add innerdiv
    var inner = document.createElement('div');
    inner.style.width = '100%';
    outer.appendChild(inner);
    var width_with_scroll = inner.offsetWidth;
    // remove divs
    outer.parentNode.removeChild(outer);
    // output
    return width_no_scroll - width_with_scroll;
}

function disable_scrolling() {
    var x = window.scrollX;
    var y = window.scrollY;
    window.onscroll = function() {
        window.scrollTo(x, y);
    };
}

function enable_scrolling() {
    window.onscroll = function() {
    };
}

function scroll_to(el) {
    document.querySelector('#' + el).scrollIntoView({
        behavior: 'smooth'
    });
}

function insertAfter(newNode, referenceNode) {
    referenceNode.parentNode.insertBefore(newNode, referenceNode.nextSibling);
}

function scroll_textarea(e, el) {
    //vars
    var rows = +el.getAttribute('rows');
    var min_rows = +el.getAttribute('min-rows');
    var max_rows = +el.getAttribute('max-rows');
    var min_height = parseInt(el.style.minHeight);
    var el_height = parseInt(el.style.height);
    var scroll_height = el.scrollHeight;
    var min_scrol_height = min_height / min_rows - 6;

    if (e.code === 'Backspace') {
        if (el_height >= scroll_height) {
            var clear_height;
            if (rows > 3) {
                el.setAttribute('rows', `${rows - 1}`);
                clear_height = el_height - min_scrol_height;
            }
            if (rows === 3) {
                clear_height = min_height;
            }
            el.style.height = clear_height + 'px';
            el.style.overflow = 'hidden';
        }
    } else {
        if (scroll_height > el_height) {
            if (rows < max_rows) {
                el.style.height = el.scrollHeight + 'px';
                el.setAttribute('rows', `${rows + 1}`);
            } else {
                el.style.overflow = 'visible';
            }
        }
    }
}

// SERVICE

function clear_num(el) {
    sv(el, gv(el).replace(/[^\d]/g, ''));
}

function trim(text) {
    return (text || '').replace(/^\s+|\s+$/g, '');
}

function on_click(el) {
    if (el = ge(el)) {
        if (el.click) el.click();
        else if (document.createEvent) {
            var event_obj = document.createEvent('MouseEvents');
            event_obj.initEvent('click', true, true);
            el.dispatchEvent(event_obj);
        }
    } else return false;
}

function tab_change(el) {
    // vars
    var tabs = qs_all('.tab');
    var pages = qs_all('.tab_content');
    var page = el.getAttribute('data-tab');
    var display = el.getAttribute('data-display');
    // actions
    if (!has_class(el, 'disabled')) {
        pages.forEach(page => hide(page));
        tabs.forEach(tab => remove_class(tab, 'active'));
        set_style(ge(page), 'display', display);
        add_class(el, 'active');
    }
}

// VALIDATE

function is_int(value) {
    if (value === true) return 1;
    return parseInt(value) || 0;
}

function is_float(value) {
    if (value === true) return 1;
    return parseFloat(value) || 0;
}

function is_positive(value) {
    value = is_int(value);
    return value < 0 ? 0 : value;
}

function is_numeric(value) {
    return !isNaN(value);
}

function is_email(value) {
    if (value.match(/[a-z0-9][a-z0-9_.\-]+[@]([a-z0-9]{1,2}|[a-z0-9][a-z0-9\-]+[a-z0-9])([.][a-z]+){1,3}/i)) return true;
    return false;
}

function is_phone(value) {
    if (value.match(/^[\d\s+\-() ]+$/i)) return true;
    return false;
}

function is_name(value) {
    if (value.match(/^[a-zа-яё\s\'\-]+$/i)) return true;
    return false;
}

function is_date(d, m, y) {
    if (!is_numeric(d) || !is_numeric(m) || !is_numeric(y)) return false;
    if (y < 1900 || y > 2100) return false;
    if (m < 1 || m > 12) return false;
    if (d < 1 || d > 31) return false;
    if (d === 31 && (m === 2 || m === 4 || m === 6 || m === 9 || m === 11)) return false;
    if (d === 30 && m === 2) return false;
    if (d === 29 && m === 2) return (((y % 4 === 0) && (y % 100 !== 0)) || (y % 400 === 0));
    return true;
}

function is_future_date(d, m, y) {
    // now
    var now = new Date();
    // dates
    var date = new Date(m + '/' + d + '/' + y);
    var now_date = new Date((now.getMonth() + 1) + '/' + now.getDate() + '/' + now.getFullYear());
    // output
    return now_date <= date;
}

function is_length(value, n) {
    return value.length > n;
}

// POLYFILLS

if (!Element.prototype.closest) {
    Element.prototype.closest = function(css) {
        var node = this;
        while (node) {
            if (node.matches(css)) return node;
            else node = node.parentElement;
        }
        return null;
    };
}

if (!Element.prototype.matches) {
    Element.prototype.matches = Element.prototype.matchesSelector ||
        Element.prototype.webkitMatchesSelector ||
        Element.prototype.mozMatchesSelector ||
        Element.prototype.msMatchesSelector;
}

if (window.NodeList && !NodeList.prototype.forEach) {
    NodeList.prototype.forEach = function(callback, thisArg) {
        thisArg = thisArg || window;
        for (var i = 0; i < this.length; i++) {
            callback.call(thisArg, this[i], i, this);
        }
    };
}

Array.prototype.remove_value = function(val) {
    for (var i = 0; i < this.length; i++) {
        var c = this[i];
        if (c == val || (val.equals && val.equals(c))) {
            this.splice(i, 1);
            break;
        }
    }
};

