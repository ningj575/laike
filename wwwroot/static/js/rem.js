(function(doc, win) {
  var docEl = doc.documentElement,
    resizeEvt = "orientationchange" in window ? "orientationchange" : "resize",
    recalc = function() {
      var clientWidth = docEl.clientWidth;
      // console.log(clientWidth)
      if (!clientWidth) return;
      let _size = (100 / 750) * clientWidth;
      console.log(clientWidth);
      console.log(_size);
      if (_size > 75) _size = 75;
      docEl.style.fontSize = _size + "px";
    };
  if (!doc.addEventListener) return;
  win.addEventListener(resizeEvt, recalc, false);
  doc.addEventListener("DOMContentLoaded", recalc, false);
})(document, window);
