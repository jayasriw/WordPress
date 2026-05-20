(function ($) {
  ("use strict");
  var ajax_url = civi_mess_file_vars.ajax_url,
    title = civi_mess_file_vars.title,
    file_type = civi_mess_file_vars.file_type,
    max_file_size = civi_mess_file_vars.max_file_size,
    file_url = civi_mess_file_vars.file_url,
    file_upload_nonce = civi_mess_file_vars.file_upload_nonce;

  $(document).ready(function () {
    var featured_image = function () {
      var uploader_featured_image = new plupload.Uploader({
        browse_button: "civi_select_file",
        file_data_name: "civi_thumbnail_upload_file",
        drop_element: "civi_select_file",
        container: "civi_file_container",
        url: file_url,
        filters: {
          mime_types: [
            {
              title: title,
              extensions: file_type,
            },
          ],
          max_file_size: max_file_size,
          prevent_duplicates: true,
        },
      });
      uploader_featured_image.init();

      uploader_featured_image.bind("UploadProgress", function (up, file) {
        $("#civi_select_file i").removeClass("far fa-arrow-from-bottom large");
        $("#civi_select_file i").addClass("fal fa-spinner fa-spin large");
      });

      uploader_featured_image.bind("FilesAdded", function (up, files) {
        var maxfiles = 1;
        up.refresh();
        uploader_featured_image.start();
      });
      uploader_featured_image.bind("Error", function (up, err) {
        alert("Error #" + err.code + ": " + err.message);
      });
      uploader_featured_image.bind(
        "FileUploaded",
        function (up, file, ajax_response) {
          document.getElementById("civi_drop_file").innerHTML =
            '<button type="button" class="tooltip" id="civi_select_file" data-title="Upload File"><i class="far fa-file-upload"></i></button>';
          var response = $.parseJSON(ajax_response.response);
          if (response.success) {
            $(".file_url").val(response.url);
            $("#civi_drop_file").attr(
              "data-attachment-id",
              response.attachment_id
            );
            $("#civi_drop_file .cv-icon-delete").remove();
            var $html =
              '<button><i class="far fa-arrow-from-bottom large"></i><span>' +
              response.title +
              "</span>" +
              '<a class="icon cv-icon-delete" data-attachment-id="' +
              response.attachment_id +
              '" href="#" ><i class="far fa-trash-alt large"></i></a></button>';

            $("#civi_select_file i").addClass("far fa-arrow-from-bottom large");
            $("#civi_mess_file_view").html($html);
          }
        }
      );
    };
    featured_image();

    // Run featured_image after ajax call
    $(document).ajaxComplete(function () {
      setTimeout(function () {
        featured_image();
      }, 1000);
    });

    var civi_jobs_thumb_event = function ($type) {
      $("body").on("click", ".cv-icon-delete", function (e) {
        e.preventDefault();
        var $this = $(this),
          icon_delete = $this,
          jobs_id = $this.data("jobs-id"),
          attachment_id = $this.data("attachment-id");
        icon_delete.html('<i class="fal fa-spinner fa-spin large"></i>');

        $.ajax({
          type: "post",
          url: ajax_url,
          dataType: "json",
          data: {
            action: "civi_thumbnail_remove_ajax",
            jobs_id: jobs_id,
            attachment_id: attachment_id,
            type: $type,
            removeNonce: file_upload_nonce,
          },
          beforeSend: function () {
            icon_delete.html('<i class="fal fa-spinner fa-spin large"></i>');
          },
          success: function (response) {
            if (response.success) {
              $(".civi_file_type").show();
            }
            $("#civi_drop_file").attr("data-attachment-id", "");
            icon_delete.remove();
            $("#civi_mess_file_view").html("");
          },
          error: function () {
            icon_delete.html('<i class="far fa-trash-alt large"></i>');
          },
        });
      });
    };
    civi_jobs_thumb_event("thumb");
  });
})(jQuery);
