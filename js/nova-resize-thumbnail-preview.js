jQuery(document).ready(function($) {
    var thumbnail = $(".nrt-thumbnail");
    var radioButtons = $("input[name='image_position']");
    var futureImageMetaBox = $('#postimagediv .inside')

    function updateThumbnailPreview() {
        if (thumbnail.length) {
            var selectedPosition = "";
            radioButtons.each(function() {
                if ($(this).is(":checked")) {
                    selectedPosition = $(this).val();
                }
            });
            thumbnail.css("background-position", selectedPosition.replace("-", " "));
        }
    }

    function updateThumbnailImage() {
        var futureImage = futureImageMetaBox.find('img');
        if (futureImage.length) {
            var futureImageUrl = futureImage.attr('src');
            thumbnail.css('background-image', 'url(' + futureImageUrl + ')');
            thumbnail.show();
        } else {
            thumbnail.css('background-image', 'url()');
            thumbnail.hide();
        }
    }

    radioButtons.change(updateThumbnailPreview);
    updateThumbnailPreview();

    // Detect changes in the postimagediv for featured image updates
    $('#postimagediv').on('DOMSubtreeModified', function() {
        updateThumbnailImage();
        updateThumbnailPreview();
    });
});
