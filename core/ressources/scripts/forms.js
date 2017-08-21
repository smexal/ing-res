var forms = {
    init : function() {
        forms.ajax();
        forms.tags();
        forms.helperlinks();
        forms.readOnlyInput();
        forms.additionalNavigationForm();
        forms.focusToggle();
    },

    tags : function() {
      var self = this;
      $("input.tags").each(function() {
        self.init_tag(this);
      });
    },

    focusToggle : function() {
        $("input[type='text'], input[type='password'], input[type='email'], input[type='input'], textarea, input[type='datetime'], input[type='number']").each(function() {
            if($(this).val().length > 0) {
                $(this).parent().addClass('focus');
                if($(this).parent().hasClass("input-group")) {
                    $(this).parent().parent().addClass('focus');
                }
            }

            $(this).on('focus', function() {
                if(! $(this).parent().hasClass('focus')) {
                    $(this).parent().addClass('focus');
                    if($(this).parent().hasClass("input-group")) {
                        $(this).parent().parent().addClass('focus');
                    }
                }
            }).on('blur', function() {
                if($(this).val().length == 0) {
                    $(this).parent().removeClass('focus');
                    if($(this).parent().hasClass("input-group")) {
                        $(this).parent().parent().removeClass('focus');
                    }
                }
            })
        });
    },

    init_tag : function(element) {
      if($(element).data('tagsinited') == '1') {
        return;
      }
      var values = $(element).data('values');
      var getter = $(element).data('getter');
      if( Object.prototype.toString.call( values ) === '[object Array]') {
          forms.tagsInputByValues($(element), values);
      } else if (typeof getter === "string") {
          forms.tagsInputByGetter($(element));
      }
      $(element).data('tagsinited', 1);

    },

    additionalNavigationForm : function() {
      $('form[action*="navigation/itemedit"').each(function() {
        $(this).find("select#item").each(function() {
          forms.getAdditionalNavigationItemForm($(this));

          $(this).on("change", function() {
            forms.getAdditionalNavigationItemForm($(this));
          });
        });
      })
    },

    getAdditionalNavigationItemForm : function(select) {
      var url = select.closest("form").find("input[name='additional-form-url']").val();
      var value = select.val();
      select.closest("form").find(".additional-view-form").remove();
      if(value.indexOf("view##") == 0) {
        var view = value.split('##');
        view = view[1];
        $.ajax({
          method: 'GET',
          url: url + '/' + view
        }).done(function(data) {
          if(data.form != '') {
            var additional = $('<div class="additional-view-form">' + data.form + '</div>');
            select.closest(".form-group").after(additional);
          }
        });
      }
    },

    readOnlyInput : function() {
      $("label").each(function() {
        $(this).on('click', function() {
          var target = $('input#' + $(this).attr('for'));
          target.removeAttr('readonly');
          target.focus();
        });
      });
    },

    tagsInputByGetter : function(element) {
        var self = this;
        var geturl = element.data('getter');
        geturl += (geturl.indexOf("%QUERY") == -1 ) ? '/search/%QUERY' : '';
        var getter_convert = element.data('getter-convert');
        var loadingcontext = element.data('loadingcontext');
        var context = element;

        if(loadingcontext) {
          context = $(element).parent(loadingcontext);
          if(!context) {
            context = element;
          }
        }

        // is overwritten if defined via getter-convert
        var transform = function(data) {
          return data;
        }

        if(getter_convert) {
          try {
            var func = eval(getter_convert);
            transform = function() {
              context.removeClass("loading");
              var args = [];
              for(var i = 0; i < arguments.length; i++) {
                args.push(arguments[i]);
              }
              args.push(self);
              return func.apply(this, args);
            };
          } catch (e) {

          }
        }

        var remote = {
            url: geturl,
            prepare : function(query, settings) {
              context.addClass('loading');
              settings.url = settings.url.replace('%QUERY', query);
              return settings;
            },
            transform : transform
          };

        var engine = new Bloodhound({
          datumTokenizer: Bloodhound.tokenizers.whitespace,
          queryTokenizer: Bloodhound.tokenizers.whitespace,
          remote: remote
        });
        engine.initialize();

        var typeahead = element.tagsinput({
            allowDuplicates: false,
            freeInput: false,
            itemValue: element.data('getter-value'),
            itemText: element.data('getter-name'),
            inputSize: 10,
            maxTags : element.data('data-single'),
            typeaheadjs: {
              hint: true,
              highlight: true,
              minLength: 2,
              name: "name_"+element.data('getter-name'),
              displayKey: element.data('getter-name'),
              source: engine.ttAdapter()
            }
        })[0];

        var labels = element.data('tag-labels');
        if(!labels || labels.length == 0)
          return;

        var values = element.val().split(",");
        for(var i = 0; i < values.length; i++) {
          typeahead.add({id: values[i], name: labels[values[i]]})
        }

      },

    tagsInputByValues : function(element, values) {
      element.tagsinput({
          allowDuplicates: false,
          freeInput: false,
          typeaheadjs: {
              source: substringMatcher(values)
          }
      });
    },

    helperlinks : function() {
        $("a.set-value").unbind("click").on('click', function() {
            $("input#" + $(this).data('target')).val($(this).data('value'));
            $(this).parent().find(".active").removeClass('active');
            $(this).addClass('active');
        });
    },

    ajax : function() {
        $("form.ajax").each(function() {
            $(this).unbind('submit').on("submit", function(e) {
                e.preventDefault();
                e.stopImmediatePropagation();
                var button = $(this).find("button");
                // don't go any further if the button is still loading...
                if(button.hasClass('loading')) {
                  return;
                }
                button.addClass('loading');
                e.preventDefault();
                var target = $(this).closest($(this).data('target'));
                setLoading(target);
                var form_data = new FormData(this);
                $.ajax({
                    method: 'POST',
                    data: form_data,
                    url: $(this).attr("action"),
                    cache: false,
                    contentType: false,
                    processData: false,
                    complete: function() {
                        button.removeClass('loading');
                    },
                    error: function (jqXHR, exception) {
                        var msg = '';
                        if (jqXHR.status === 0) {
                            msg = 'Not connect.\n Verify Network.';
                        } else if (jqXHR.status == 404) {
                            msg = 'Requested page not found. [404]';
                        } else if (jqXHR.status == 500) {
                            msg = 'Internal Server Error [500].';
                        } else if (exception === 'parsererror') {
                            msg = 'Requested JSON parse failed.';
                        } else if (exception === 'timeout') {
                            msg = 'Time out error.';
                        } else if (exception === 'abort') {
                            msg = 'Ajax request aborted.';
                        } else {
                            msg = 'Uncaught Error.\n' + jqXHR.responseText;
                        }
                        console.error(msg);
                    },
                }).done(function(data) {
                  // if the form has a callback defined, run it with the response
                  var callback = button.closest('form').attr('callback');
                  if(typeof(callback) !== 'undefined') {
                    eval(callback+'('+JSON.stringify(data)+')');
                  }
                  hideLoading(target, function() {
                      try {
                          json = $.parseJSON(data);
                          if(json.action == 'redirect') {
                              redirect(json.target);
                          }
                      } catch (e) {
                          if(data.action == 'reload-specific') {
                            forms.reloadSpecificContainer(data.target);
                          }
                          if(data.errors) {
                            var form = button.closest("form");
                            console.log(data.errors);
                            for(var index = 0; index < data.errors.length; index++) {
                              var field = form.find("#" + data.errors[index].field);
                              if(! field.hasClass("error")) {
                                field.addClass("error");
                              }
                              field.parent().find(".message").remove();
                              field.parent()
                                .append("<p class='message error'>"+data.errors[index].message+"</p>");
                            }
                          } else {
                            var form = button.closest("form");
                            form.find(".message").each(function()  {
                              $(this).remove();
                            })
                            form.find(".form-control").each(function() {
                              $(this).removeClass("error");
                            });
                          }
                          target.addClass("fadeIn");
                          target.html(data);
                          $(document).trigger("ajaxReload");
                          setTimeout(function() {
                              target.addClass("now");
                          }, 30);
                      }
                  });
              });
            });
        });
    },

    reloadSpecificContainer : function(container) {
      $(container).addClass('loading');
      $.ajax({
        method: 'POST',
        url: $(container).attr("ajax-url")
      }).done(function(data) {
        var data = $(data.content);
        data.addClass("loading");
        $(container).replaceWith(data);
        $(container).removeClass('loading');
        $(document).trigger("ajaxReload");
      }).complete(function() {
        $(container).removeClass('loading');
        $(document).trigger("ajaxReload");
      });
      // TODO RELOAD TARGET CONTAINER WITH AJAX
    }
};

$(document).ready(forms.init);
$(document).on("ajaxReload", forms.init);
