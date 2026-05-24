
/**
 * Exopite Simple Options Framework Trumbowyg
 */
; (function ($, window, document, undefined) {

    var pluginName = "exopiteSOFCompass";

    // The actual plugin constructor
    function Plugin(element, options) {

        this.element = element;
        this._name = pluginName;
        this.$element = $(element);
        this.init();

    }

    Plugin.prototype = {

        init: function () {

            var plugin = this;
            plugin.$element.find('.compass-control').each(function (index, el) {
                if ($(el).parents('.exopite-sof-cloneable__muster').length) return;
                if ($(el).hasClass('.disabled')) return;
                var points = el.getElementsByClassName('compassdir'),
                    selected = el.querySelector('.selected-direction');

                for (var i = 0; i < points.length; i++) {
                    points[i].style.transform = 'rotate(' + (i * 22.5) + 'deg) translateY(-2.5em)';
                    points[i].addEventListener('change', function() {
                        selected.innerHTML = el.querySelector('input:checked').value;
                    });
                }


            });

            plugin.$element.closest('.exopite-sof-wrapper').on('exopite-sof-field-group-item-added-after', function (event, $cloned) {

                $cloned.find('.compass-control').each(function (index, el) {


                });

            });

        },

    };

    $.fn[pluginName] = function (options) {
        return this.each(function () {
            if (!$.data(this, "plugin_" + pluginName)) {
                $.data(this, "plugin_" + pluginName,
                    new Plugin(this, options));
            }
        });
    };

})(jQuery, window, document);

; (function ($) {
    "use strict";

    $(document).ready(function () {

        $('.exopite-sof-field-compass').exopiteSOFCompass();

    });

}(jQuery));
