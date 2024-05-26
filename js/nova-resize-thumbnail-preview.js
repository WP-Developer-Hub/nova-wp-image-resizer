jQuery(document).ready(function($) {
    function updateThumbnailPreview() {
        console.log("Updating thumbnail preview...");

        var thumbnail = $(".nrt-thumbnail");
        // Check if the thumbnail element exists
        if (thumbnail.length) {
            console.log("Thumbnail element found.");

            var radioButtons = $("input[name='image_position']");
            var selectedPosition = "";
            radioButtons.each(function() {
                console.log("Checking radio button...");
                if ($(this).is(":checked")) {
                    console.log("Radio button checked.");
                    selectedPosition = $(this).val();
                }
            });

            console.log("Selected position:", selectedPosition);
            thumbnail.css("background-position", selectedPosition.replace("-", " "));
            console.log("Thumbnail preview updated.");
        } else {
            console.log("Thumbnail element not found.");
        }
    }

    var radioButtons = $("input[name='image_position']");
    radioButtons.change(updateThumbnailPreview);
    updateThumbnailPreview(); // Initial update
});
