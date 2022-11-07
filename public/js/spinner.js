// $("form").on("submit", function () {
//     var errorElements = document.querySelectorAll(".form-control:invalid");
//     $(errorElements[0]).focus();
//     if ($(errorElements[0]).length != 0) {
//         $("#submit").html("Save");
//         $("#submit").removeAttr("disabled");
//     } else {
//         $("#submit").html(
//             '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...'
//         );
//         $("#submit").attr("disabled", "disabled");
//     }
// });

$("form").on("submit", () => {
    $("#loading").show();
    return true;
});

// TODO TO ALL ACCOMPLISHMENT VIEWS MUST BE EDITED AND ALL CURRENT CONTROLLERS
$("#accomplishment-form").on("submit", async function (e) {
    e.preventDefault();

    const formData = new FormData(this);
    const url = this.action;
    $("#loading").hide();

    const dataConfig = {
        url: url,
        type: "POST",
        dataType: "json",
        data: formData,
        processData: false,
        contentType: false,
        cache: true,
        async: true,
    };

    $.ajax({
        ...dataConfig,
        beforeSend: function () {
            $("#loading").show();
        },
        complete: function () {
            $("#loading").hide();
        },
        success: function (data) {
            Swal.fire({
                icon: "success",
                title: "Successfully Saved!",
                text: "Accomplishment is ready for submission!",
                showConfirmButton: false,
                // timer: 2200,
            });
        },
    });
});
