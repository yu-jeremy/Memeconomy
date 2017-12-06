/* global $*/

document.addEventListener('DOMContentLoaded', function () {
    document.getElementById("edit_profile").addEventListener("click", function() {
        $(".profile").prop("readonly", false);
        $("#submit_profile").prop("hidden", false);
        $("#new_password").prop("hidden", false);
        $("#new_pwd_label").prop("hidden", false);
    });
    
    document.getElementById("submit_profile").addEventListener("click", function() {
        $(".profile").prop("readonly", true);
    });
});


