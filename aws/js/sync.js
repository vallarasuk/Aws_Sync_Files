jQuery(document).ready(function ($) {
  $("#sync-to-s3-btn-edit-page").click(function (event) {
    event.preventDefault(); // Prevent default form submission behavior

    var $button = $(this);
    var originalLabel = $button.text();
    var postId = $("#post_ID").val();
    var nonce = $("#sync_images_to_s3_nonce_field").val();

    // Change button label to "Uploading..." and disable it
    $button.text("Uploading...");
    $button.prop("disabled", true);

    $.ajax({
      url: ajaxurl,
      type: "POST",
      data: {
        action: "sync_images_to_s3",
        post_id: postId,
        security: nonce,
      },
      dataType: "json",
      success: function (response) {
        if (response.success) {
          alert("Images Uploaded successfully");
          location.reload(); // Reload the page after successful upload
        } else {
          alert("Error: " + response.data.message);
          if (response.data.errors) {
            console.error("Errors: ", response.data.errors);
          }
          // Restore button state on error
          $button.text(originalLabel);
          $button.prop("disabled", false);
        }
      },
      error: function (jqXHR, textStatus, errorThrown) {
        console.error("AJAX error: " + textStatus + " : " + errorThrown);
        console.error("Response: " + jqXHR.responseText);
        $.ajax({
          type: "POST",
          url: ajaxurl,
          data: {
            action: "log_ajax_error",
            error_message: errorThrown,
          },
        });
        // Restore button state on AJAX error
        $button.text(originalLabel);
        $button.prop("disabled", false);
      },
      complete: function () {
        // Ensure button state is restored if not reloading page
        $button.text(originalLabel);
        $button.prop("disabled", false);
      }
    });
  });
});
