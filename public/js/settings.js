$(document).ready(() => {
    $(".custom-file-input").on("change", function() {
        var fileName = $(this).val().split("\\").pop();
        $(this).siblings(".custom-file-label").addClass("text-primary").html("New: "+fileName);
    });
});
