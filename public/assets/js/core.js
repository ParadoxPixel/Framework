$(function() {
    $("code i").click(function() {
        const parent = $(this).parent();
        parent.parent().find("code.hidden").removeClass("hidden");
        parent.addClass("hidden");
    });
});
