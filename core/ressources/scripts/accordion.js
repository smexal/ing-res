var accordion = {
    init : function(context) {
        var context = $(document);
        
        context.find('.panel-group.ajax').each(function() {
            var ajaxurl = $(this).data('ajax');

            // initial open 
            // TODO: need to find a resolution for initial & ajax reload, currently infinte loading...
            /*if($(this).find(".forge-placeholder").length > 0) {
                accordion.ajaxLoad($(this), ajaxurl);
            }*/

            // on open new panels
            $(this).on('shown.bs.collapse', function () {
                accordion.ajaxLoad($(this), ajaxurl);
            });
        });
    },

    ajaxLoad : function(panelGroup, ajaxurl) {
        panelGroup.find(".collapse.in").each(function() {
            accordion.loadFor($(this).data('id'), $(this).find(".panel-body").filter(":first"), ajaxurl);
        });
    },

    loadFor: function(id, target, ajaxurl) {
        $.ajax({
            headers: {          
                Accept: "text/html; charset=utf-8", "Content-Type": "text/html; charset=utf-8"   
            }, 
            method: 'POST',
            url: ajaxurl + '/'+id
        }).done(function(data) {
            $(target).html(data);
            $(document).trigger("ajaxReload", $(target));
        });
    }
}

$(document).ready(accordion.init);
$(document).on("ajaxReload", function(evt, context) {
    accordion.init();
});
