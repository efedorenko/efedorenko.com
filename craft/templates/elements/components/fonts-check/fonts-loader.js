var FontFaceObserver = require('fontfaceobserver/fontfaceobserver.js');
var cookie = require('fg-cookie');

(function(w){
  // If the class is already set, we're good.
  if (w.document.documentElement.className.indexOf("fonts-loaded") > -1) {
    return;
  }

  var sentinelA = new FontFaceObserver('Sentinel SSm A');
  var sentinelB = new FontFaceObserver('Sentinel SSm B');

  Promise.all([sentinelA.load(), sentinelB.load()]).then(function () {
    console.log('Sentinel is loaded');
    w.document.documentElement.className += " fonts-loaded";
    cookie("fonts-loaded", "Sentinel", 7);
  });
}(window));
