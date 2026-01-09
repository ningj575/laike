(function (doc, win) {
    var docEl = doc.documentElement,
      resizeEvt = "orientationchange" in window ? "orientationchange" : "resize",
      recalc = function () {
        var clientWidth = docEl.clientWidth;
        console.log(clientWidth);
        if (!clientWidth) return;
        let _size = (100 / 750) * clientWidth;
        if (_size > 75) _size = 75;
        docEl.style.fontSize = _size + "px";
      };
    if (!doc.addEventListener) return;
    win.addEventListener(resizeEvt, recalc, false);
    doc.addEventListener("DOMContentLoaded", recalc, false);
  })(document, window);
  (function () {
    if (
      typeof WeixinJSBridge == "object" &&
      typeof WeixinJSBridge.invoke == "function"
    ) {
      handleFontSize();
    } else {
      if (document.addEventListener) {
        document.addEventListener("WeixinJSBridgeReady", handleFontSize, false);
      } else if (document.attachEvent) {
        document.attachEvent("WeixinJSBridgeReady", handleFontSize);
        document.attachEvent("onWeixinJSBridgeReady", handleFontSize);
      }
    }
    function handleFontSize() {
      // 设置网页字体为默认大小
      WeixinJSBridge.invoke("setFontSizeCallback", { fontSize: 0 });
      // 重写设置网页字体大小的事件
      WeixinJSBridge.on("menu:setfont", function () {
        WeixinJSBridge.invoke("setFontSizeCallback", { fontSize: 0 });
      });
    }
  })();