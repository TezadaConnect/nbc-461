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

// $imageRecord = PartnershipDocument::where('partnership_id', $partnership->id)->get();

// $imageChecker =  $this->commonService->imageCheckerWithResponseMsg(1, $imageRecord, $request);

// if($imageChecker) return redirect()->route('partnership.index')->with('warning', 'Need to attach supporting documents to enable submission');
