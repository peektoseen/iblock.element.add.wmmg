;
(function ($, w) {
    w.dateSelected = function (d) {
        $('.js-time', this.params.node).html(d.getHours() + ':' + d.getMinutes());
        $('.js-date', this.params.node).html(d.getDate() + '.' + d.getMonth() + 1 + '.' + d.getFullYear());
    }
}(jQuery, window));