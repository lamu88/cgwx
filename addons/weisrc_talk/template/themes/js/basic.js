// JavaScript Document
var showMenu = function(){
    if($('.phone_bottomMenu').css('display')!='none')
        $('.phone_bottomMenu').slideUp('fast');
    else
        $('.phone_bottomMenu').slideDown('fast');
}
$(function(){
    //分享给朋友的提示
    $(".btnShareGuide").click(function(){
        var screenH = screen.height;
        $div = $('<div class="shareGuide" style="height:'+screenH+'px;"><img src="/wemew/images/shareToFriend.png"/></div>')
        $div.appendTo($("body")).click(function(){$(this).remove();});

    });
    //input激活与默认
    $("input,textarea").focus(function(){
        $(this).addClass("inputFocus");
    });
    $("input,textarea").blur(function(){
        $(this).removeClass("inputFocus");
    });

});