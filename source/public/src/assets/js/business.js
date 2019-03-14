/* global jQuery, feather */

window.addEventListener('load', function() {
    feather.replace( {class: '', 'stroke-width': 2});
}) 
/**
 * windowを閉じるメソッド
 */

function close_window() {
    if (/Chrome/i.test(navigator.userAgent)) {
        window.close();
    }
    else {
        window.open('about:blank', '_self').close();
    }
}