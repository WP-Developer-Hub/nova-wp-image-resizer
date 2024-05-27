jQuery(document).ready(function($) {
    function updateThumbnailPreview() {
        var thumbnail = $(".nrt-thumbnail");
        // Check if the thumbnail element exists
        if (thumbnail.length) {
            console.log("Thumbnail element found.");

            var radioButtons = $("input[name='image_position']");
            var selectedPosition = "";
            radioButtons.each(function() {
                if ($(this).is(":checked")) {
                    selectedPosition = $(this).val();
                }
            });

            thumbnail.css("background-position", selectedPosition.replace("-", " "));
        } else {
            console.log("Thumbnail element not found.");
        }
    }

    var radioButtons = $("input[name='image_position']");
    radioButtons.change(updateThumbnailPreview);
    updateThumbnailPreview(); // Initial update
});
