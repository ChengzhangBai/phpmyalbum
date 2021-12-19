$('input[type="reset"]').click(function () {
    $('input[type="text"]').removeAttr('value');
    $('input[type="password"]').removeAttr('value');
    //$('input[type="password"]').val('');
    $("#pwd").val('');
    $('input[type="checkbox"] input[type="radio"]').removeAttr('checked');
    $('select option').removeAttr('selected');
    $("label[id$='Error']").text("");
});

function loading() {
  $(".btn .fa-spinner").show();
  $(".btn .btn-text").html("Loading");
}

function commenting() {
  $(".btn .fa-spinner").show();
  $(".btn .btn-text").html("Now adding...");
}

function showPwd() {
  var x = document.getElementById("pwd");
  if (x.type === "password") {
    x.type = "text";
  } else {
    x.type = "password";
  }
}
//album select list
document.getElementById("chooseAlbum").addEventListener("change",function(){
    document.getElementById("btnSubmit").click();
});