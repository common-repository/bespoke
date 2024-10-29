// vanilla js ajax handler
var ajax = {};
ajax.x = function () {
    var xhr;
    if (typeof XMLHttpRequest !== 'undefined') {
        xhr = new XMLHttpRequest();
        xhr.withCredentials = true;
        return xhr;
    }
    var versions = [
        "MSXML2.XmlHttp.6.0",
        "MSXML2.XmlHttp.5.0",
        "MSXML2.XmlHttp.4.0",
        "MSXML2.XmlHttp.3.0",
        "MSXML2.XmlHttp.2.0",
        "Microsoft.XmlHttp"
    ];

    for (var i = 0; i < versions.length; i++) {
        try {
            xhr = new ActiveXObject(versions[i]);
            xhr.withCredentials = true;
            break;
        } catch (e) {
        }
    }
    return xhr;
};

ajax.send = function (url, callback, method, data, async) {
    if (async === undefined) {
        async = true;
    }
    var x = ajax.x();
    x.open(method, url, async);
    x.onreadystatechange = function () {
        if (x.readyState == 4) {
            callback(x.responseText)
        }
    };
    if (method == 'POST') {
        x.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
    }
    x.send(data)
};

ajax.get = function (url, data, callback, async) {
    var query = [];
    for (var key in data) {
        query.push(encodeURIComponent(key) + '=' + encodeURIComponent(data[key]));
    }
    ajax.send(url + (query.length ? '?' + query.join('&') : ''), callback, 'GET', null, async)
};

ajax.post = function (url, data, callback, async) {
    var query = [];
    for (var key in data) {
        query.push(encodeURIComponent(key) + '=' + encodeURIComponent(data[key]));
    }
    ajax.send(url, callback, 'POST', query.join('&'), async)
};

function showBanner(config) {
    var div = document.createElement('div');
    div.innerHTML = `
    <style>
      .bespoke-teaserbar {
        z-index: 99999;
        all: initial;
        -webkit-box-sizing: border-box;
                box-sizing: border-box;
        position: fixed;
        top: 0;
        width: 100%;
        font-size: 18px;
        font-family: "Roboto", "Helvetica Neue", "Helvetica", Helvetica, Arial, sans-serif;
      }
      .bespoke-teaserbar__body {
        display: -webkit-box;
        display: -ms-flexbox;
        display: flex;
        min-height: 0px;
        padding: .5em 1.5em;
        -webkit-box-align: center;
            -ms-flex-align: center;
                align-items: center;
        -webkit-box-pack: center;
            -ms-flex-pack: center;
                justify-content: center;
        -ms-flex-wrap: wrap;
            flex-wrap: wrap;
        background-color:#2c2c2c;
        color: #fff;
      }
      .bespoke-teaserbar__body > * {
        margin: 0.25em 0.5em;
      }
      .bespoke-teaserbar__close {
        all: initial;
        padding: .25em;
        display: -webkit-inline-box;
        display: -ms-inline-flexbox;
        display: inline-flex;
        cursor: pointer;
        position: absolute;
        top: 50%;
        right: 1em;
        -webkit-transform: translateY(-50%);
                transform: translateY(-50%);
        background: transparent;
        border: 0;
        color: #9b9b9b;
        font-size: 20px;
      }
      .bespoke-teaserbar__close rect {
        fill: currentcolor;
        stroke: currentcolor;
      }
      .bespoke-teaserbar__text {
        text-align: center;
        font-size: 26px;
        font-weight: 800;
        letter-spacing: .02em;
      }
      .bespoke-teaserbar__countdown {
        display: -webkit-box;
        display: -ms-flexbox;
        display: flex;
        font-size: 1.5em;
        font-weight: 800;
        color: #fd6767;
      }
      .bespoke-teaserbar__button a {
        display: inline-block;
        text-decoration: none;
        color: #fff;
        background: #3ec9cb;
        padding: .5em 1.5em;
        border-radius: 3px;
        font-weight: 500;
      }
    </style>
    <div id="bespoke-teaserbar" class="bespoke-teaserbar">
      <div class="bespoke-teaserbar__body">
        <button id="bespoke-teaser-close" title="close" class="bespoke-teaserbar__close">
        <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" id="Layer_1" x="0px" y="0px" viewBox="0 0 22.6 22.6" style="height: 1em; width: 1em; xml:space=" preserve"=" preserveAspectRatio="none"> <rect fill="currentColor" x="8.3" y="-1.7" transform="matrix(0.7071 0.7071 -0.7071 0.7071 11.3137 -4.6863)" width="6" height="26"></rect> <rect fill="currentColor" x="8.3" y="-1.7" transform="matrix(-0.7071 0.7071 -0.7071 -0.7071 27.3137 11.3137)" width="6" height="26"></rect></svg>
        </button>
        <div class="bespoke-teaserbar__text">
          <span>${config.bannerMessage}</span>
        </div>
        <div class="bespoke-teaserbar__button">
          <a href="${config.bannerLink}">Check it out!</a
          >
        </div>
      </div>
    </div>
    `;
    document.body.appendChild(div);
    document.getElementById("bespoke-teaser-close").onclick = function(){
        div.querySelector("#bespoke-teaserbar").style.display = "none";
        document.cookie = "bespoke-teaser-dismissed=true; max-age=3600"
    }
}

function bannerDismissed() {
    return document.cookie.replace(/(?:(?:^|.*;\s*)bespoke-teaser-dismissed\s*\=\s*([^;]*).*$)|^.*$/, "$1") == "true";
}

function getConfig() {
    return window.bespokeConfig;
}

// bespoke function
window.bespoke = (function () {
    var bespoke = {
        showBanner: showBanner,
        getConfig: getConfig,
        bannerDismissed: bannerDismissed
    };

    window.onload = function() {
        config = this.getConfig();
        if (config.bannerEnabled && !this.bannerDismissed()) {
            bespoke.showBanner(config);
        }
    };

    return bespoke;
})();
