jQuery(document).ready(function ($) {
  var ajax_url = civi_service_review_vars.ajax_url;
  var service_review = $(".service-review-details");

  service_review.find("input:file").on('change', function () {
    $(".fileList span").remove();
    for (var i = 0; i < this.files.length; i++) {
      var fileName = this.files[i].name;
      $(".fileList").append("<span>" + fileName + "</span>");
    }
  });

  service_review.find(".entry-nav .reply").on("click", function (e) {
    e.preventDefault();
    $(".author-review").removeClass("active");
    $(".author-review .form-reply").html("");
    var $this = $(this);
    var form_reply = $(".duplicate-form-reply").html();
    var comment_id = $this
      .parents(".author-review")
      .find(".form-reply")
      .data("id");
    $(".add-new-review").hide();
    $this.parents(".author-review").addClass("active");
    $this.parents(".author-review").find(".form-reply").html(form_reply);
    $this
      .parents(".author-review")
      .find('.form-reply input[name="comment_id"]')
      .val(comment_id);
  });

  $("body").on("click", ".form-reply .civi-submit-service-reply", function (e) {
    e.preventDefault();
    var $this = $(this);
    var $form = $this.parents("form");
    var message = $form.find("textarea").val();
    if (message == "") {
      $form.find("#message-error").fadeIn();
    } else {
      $form.find("#message-error").fadeOut();

      $.ajax({
        type: "POST",
        url: ajax_url,
        data: $form.serialize(),
        dataType: "json",
        beforeSend: function () {
          $this.attr("disabled", true);
          $this.children("i").remove();
          $this.append('<i class="fa-left fal fa-spinner fa-spin large"></i>');
        },
        success: function () {
          window.location.reload();
        },
        complete: function () {
          $this.children("i").removeClass("fal fa-spinner fa-spin large");
          $this.children("i").addClass("fa fa-check");
        },
      });
    }
  });

  $("body").on("click", ".cancel-reply", function (e) {
    e.preventDefault();
    service_review.find(".author-review").removeClass("active");
    service_review.find(".author-review .form-reply").html("");
    service_review.find(".add-new-review").show();
  });

  $.validator.setDefaults({
    debug: true,
    success: "valid",
  });

  $(".reviewForm").validate({
    rules: {
      message: {
        required: true,
      },
    },
    messages: {
      message: {
        required: "This field is required",
      },
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
    submitHandler: function (form) {
      var $this = $(".reviewForm").find(".civi-submit-service-rating");
      var $form = $(".reviewForm");

      var formdata = false;
      if (window.FormData) {
        formdata = new FormData($form[0]);
      }

      $.ajax({
        type: "POST",
        url: ajax_url,
        data: formdata ? formdata : $form.serialize(),
        enctype: "multipart/form-data",
        dataType: "json",
        processData: false,
        contentType: false,
        beforeSend: function () {
          $this.children("i").remove();
          $this.append('<i class="fa-left fal fa-spinner fa-spin large"></i>');
        },
        success: function (data) {
          if (data.success) {
            window.location.reload();
          } else {
            alert(data.data.message);
          }
        },
        complete: function () {
          $this.children("i").removeClass("fal fa-spinner fa-spin large");
          $this.children("i").addClass("fa fa-check");
        },
      });
    },
  });
});
