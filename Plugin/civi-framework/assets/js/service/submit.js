(function ($) {
    "use strict";

    var ajax_url = civi_submit_vars.ajax_url,
        service_dashboard = civi_submit_vars.service_dashboard,
        submit_form = $("#submit_service_form"),
        service_title_error = submit_form.data("titleerror"),
        service_des_error = submit_form.data("deserror"),
        service_cat_error = submit_form.data("caterror"),
        service_price_error = submit_form.data("priceerror"),
        service_time_error = submit_form.data("timeerror");

    $(document).ready(function () {

        //More Section
        var $rowActive = submit_form.find(".civi-addons-wrapper > .row:first-child");
        $rowActive.find(".group-title i").removeClass("delete-group");

        submit_form.on("click", "i.delete-group", function () {
            var groupToRemove = $(this).closest(".group-title").closest(".row");
            var groupSiblings = groupToRemove.siblings(".row");
            var template = groupToRemove.siblings("template");

            groupToRemove.remove();

            $.each(groupSiblings, function renumberGroups(index) {
                $(this)
                    .find(".group-title h6 span")
                    .text(index + 1);
            });

            template.data("size", groupSiblings.size());
        });

        submit_form.find(".btn-more.service-fields").on("click", function () {
            var template = $(this).siblings("template");
            var html = $(template.html().trim());
            var row = $(this).closest(".civi-addons-wrapper").find('.row');
            var index = parseInt(row.length) + 1;

            html.find(".group-title h6 span").text(index);

            html.insertBefore($(this));

            template.data("size", index);
        });

        submit_form.on("click", ".group-title", function () {
            if (!$(this).hasClass("up")) {
                $(this).addClass("up");
            } else {
                $(this).removeClass("up");
            }
        });

        //Submit
        $.validator.setDefaults({ ignore: ":hidden:not(select)" });
        submit_form.validate({
            ignore: [],
            rules: {
                service_title: {
                    required: true,
                },
                service_categories: {
                    required: true,
                },
                service_des: {
                    required: true,
                },
                service_price: {
                    required: true,
                },
                service_time: {
                    required: true,
                },
            },
            messages: {
                service_title: service_title_error,
                service_des: service_des_error,
                service_categories: service_cat_error,
                service_price: service_price_error,
                service_time: service_time_error,
            },
            submitHandler: function (form) {
                ajax_load();
            },
            errorPlacement: function (error, element) {
                if (element.attr("name") === "civi_gallery_ids") {
                    error.insertAfter("#civi_gallery_errors");
                } else if (element.is(":radio")) {
                    error.appendTo(element.parents("fieldset"));
                } else if (element.is("select")) {
                    error.insertAfter(element.next(".select2"));
                } else if (element.is(":checkbox")) {
                    error.appendTo(element.parents(".checkbox-group"));
                } else if (element.hasClass("custom-file-input")) {
                    error.insertAfter(element.parents(".custom-file"));
                } else {
                    error.insertAfter(element);
                }
            },
            invalidHandler: function () {
                if ($(".error:visible").length > 0) {
                    var firstError = $(".error:visible").first();
                    var errorElement = firstError;

                    if (firstError.prev('.select2').length > 0) {
                        errorElement = firstError.prev('.select2');
                    }
                    if (firstError.closest('.form-group').length > 0) {
                        errorElement = firstError.closest('.form-group');
                    }

                    $("html, body").animate(
                        {
                            scrollTop: errorElement.offset().top - 150,
                        },
                        800
                    );

                    var inputField = errorElement.find('input, textarea, select').first();
                    if (inputField.length > 0 && inputField.is(':visible')) {
                        setTimeout(function() {
                            inputField.focus();
                        }, 100);
                    }
                }
            },
        });

        function ajax_load() {
            var service_form = submit_form.find('input[name="service_form"]').val(),
                service_id = submit_form.find('input[name="service_id"]').val(),
                service_title = submit_form.find('input[name="service_title"]').val(),
                service_categories = submit_form.find('select[name="service_categories"]').val(),
                service_skills = submit_form.find('select[name="service_skills"]').val(),

                service_price = submit_form.find('input[name="service_price"]').val(),
                service_currency = submit_form.find('select[name="currency_type"]').val(),
                service_time = submit_form.find('input[name="service_time"]').val(),
                service_time_type = submit_form.find('select[name="service_time_type"]').val(),
                service_des = tinymce.get("service_des").getContent(),
                service_languages = submit_form.find('select[name="service_languages"]').val(),
                service_languages_level = submit_form.find('select[name="service_languages_level"]').val(),
                civi_security_service_submit = submit_form.find('#civi_security_service_submit').val(),

                service_location = submit_form
                    .find('select[name="service_location"]')
                    .val(),
                service_map_address = submit_form
                    .find('input[name="civi_map_address"]')
                    .val(),
                service_map_location = submit_form
                    .find('input[name="civi_map_location"]')
                    .val(),
                service_latitude = submit_form.find('input[name="civi_latitude"]').val(),
                service_longtitude = submit_form
                    .find('input[name="civi_longtitude"]')
                    .val(),

                service_thumbnail_url = submit_form
                    .find('input[name="service_thumbnail_url"]')
                    .val(),
                service_thumbnail_id = submit_form
                    .find('input[name="service_thumbnail_id"]')
                    .val(),
                civi_gallery_ids = submit_form
                    .find('input[name="civi_gallery_ids[]"]')
                    .map(function () {
                        return $(this).val();
                    })
                    .get(),
                service_video_url = submit_form
                    .find('input[name="service_video_url"]')
                    .val(),

                service_addons_title = submit_form
                    .find('input[name="service_addons_title[]"]')
                    .map(function () {
                        return $(this).val();
                    })
                    .get(),
                service_addons_price = submit_form
                    .find('input[name="service_addons_price[]"]')
                    .map(function () {
                        return $(this).val();
                    })
                    .get(),
                service_addons_description = submit_form
                    .find('textarea[name="service_addons_description[]"]')
                    .map(function () {
                        return $(this).val();
                    })
                    .get(),

                service_faq_title = submit_form
                    .find('input[name="service_faq_title[]"]')
                    .map(function () {
                        return $(this).val();
                    })
                    .get(),
                service_faq_description = submit_form
                    .find('textarea[name="service_faq_description[]"]')
                    .map(function () {
                        return $(this).val();
                    })
                    .get();

            $.ajax({
                dataType: "json",
                url: ajax_url,
                data: {
                    action: "service_submit_ajax",
                    service_form: service_form,
                    service_id: service_id,
                    service_title: service_title,
                    service_categories: service_categories,
                    service_skills: service_skills,
                    service_price: service_price,
                    service_currency: service_currency,
                    service_time: service_time,
                    service_time_type: service_time_type,
                    service_des: service_des,
                    service_languages: service_languages,
                    service_languages_level: service_languages_level,
                    civi_security_service_submit: civi_security_service_submit,

                    service_location: service_location,
                    service_map_address: service_map_address,
                    service_map_location: service_map_location,
                    service_latitude: service_latitude,
                    service_longtitude: service_longtitude,

                    service_thumbnail_url: service_thumbnail_url,
                    service_thumbnail_id: service_thumbnail_id,
                    civi_gallery_ids: civi_gallery_ids,
                    service_video_url: service_video_url,

                    service_addons_title: service_addons_title,
                    service_addons_price: service_addons_price,
                    service_addons_description: service_addons_description,

                    service_faq_title: service_faq_title,
                    service_faq_description: service_faq_description,
                },
                beforeSend: function () {
                    $(".btn-submit-service .btn-loading").fadeIn();
                },
                success: function (data) {
                    $(".btn-submit-service .btn-loading").fadeOut();
                    if (data.success === true) {
                        window.location.href = service_dashboard;
                    }
                },
            });
        }
    });
})(jQuery);
