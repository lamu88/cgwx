<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1,minimum-scale=1,maximum-scale=1,user-scalable=no">
    <meta name="format-detection" content="telephone=no">
    <title>{if $set['copyright']}{$set['copyright']}{else}{$_W['account']['name']}{/if}</title>
    <link rel="stylesheet" href="{AMOUSE_HUFEN_RES}css/foundation.css">
    <link rel="stylesheet" href="{AMOUSE_HUFEN_RES}css/main.a85c39c492c5.css">
    <link rel="stylesheet" href="{AMOUSE_HUFEN_RES}css/upload.ebe5da1f2569.css">
    <meta class="foundation-data-attribute-namespace">
    <meta class="foundation-mq-xxlarge">
    <meta class="foundation-mq-xlarge-only">
    <meta class="foundation-mq-xlarge">
    <meta class="foundation-mq-large-only">
    <meta class="foundation-mq-large">
    <meta class="foundation-mq-medium-only">
    <meta class="foundation-mq-medium">
    <meta class="foundation-mq-small-only">
    <meta class="foundation-mq-small">
    <meta class="foundation-mq-topbar">
    <script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
    <script src="{AMOUSE_HUFEN_RES}js/modernizr.7710ac5fd1f2.js"></script>
    <script type="text/javascript">
        {php $signPackage=getSignPackage($weid,$set);}
        jssdk="getSignPackage";
        wx.config({
            debug: false,
            appId: '<?php echo $signPackage["appId"];?>',
            timestamp: '<?php echo $signPackage["timestamp"];?>',
            nonceStr: '<?php echo $signPackage["nonceStr"];?>',
            signature: '<?php echo $signPackage["signature"];?>',
            jsApiList: [
                'checkJsApi',
                'chooseImage',
                'previewImage',
                'uploadImage',
                'downloadImage'
            ]
        });
    </script>
</head>
<body>
<div class="wrapper">

    <div class="content">
        <div class="dialog" id="tip">
            <div class="dialog-cnt">
                <div class="dialog-bd">
                    <h3>提示</h3>
                    <p class="text"></p>
                    <a class="button correct-btn" id="alert_ok">确&nbsp;&nbsp;定</a>
                </div>
            </div>
        </div>

        <div class="upload">
            <form method="post" id="personForm" enctype="multipart/form-data">
                <input type="hidden" value="{$fans['id']}" id="fansid">
                <input type="hidden" value="{$fans['openid']}" id="openid">
                <input type="hidden"  value="{$card['id']}" id="cardid">
                <div class="cnt-box">
                    <div class="portrait portrait2">
                        <img src="{$fans['headimgurl']}" alt="">
                    </div>
                    {$fans['nickname']}
                    <p>头像和昵称将会同步您微信的。<br>
                        微信号必须唯一。
                    </p>
                </div>

                <div class="cnt-box second-type">
                    <div class="row">
                        <div class="small-12 medium-8 columns">
                            <label>我的微信号
                                <input id="id_weixin_id" maxlength="30" name="wechatno" placeholder="二维码过期后仍通过微信号来添加" type="text" value="{$card['wechatno']}">
                            </label>
                        </div>
                    </div>
                    <div class="row">
                        <div class="small-12 columns">
                            <label>我的微信二维码
                            </label>

                            <div class="face">
                                <div class="qrcode-img">
                            <img id="imghead"
                                 src="{if $card['qrcode']}{php echo tomedia($card['qrcode'])}{else}{AMOUSE_HUFEN_RES}images/initial.1d7139d9a611.jpg{/if}"
                                 style="height: auto;" >
                                </div>
                            </div>
                            <div style="display:none;">
                                <div style="word-break: break-all; font-size:12px;">
                                    <input type='hidden' id='qrcode' name='qrcode' value="{$card['qrcode']}"/>
                                </div>
                            </div>

                            <div style="color:red;"></div>
                            <p style="line-height: 16px; color: #666;">
                                获取方法：打开微信主界面，选择“我”，点击顶部昵称，再选择“我的二维码”，点击右上角的菜单，选择保存图片。
                            </p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="small-12 columns">
                            <label>选择所属类别
                                <select class="classify" id="id_category" name="pcateid">
                                    <option value="">---------</option>
                                    {loop $cates $cate}
                                    <option value="{$cate['id']}" {if $card['pcateid']==$cate['id']}selected{/if}>{$cate['name']}</option>
                                    {/loop}
                                </select>
                            </label>
                        </div>
                    </div>
                    <div class="row">
                        <div class="small-12 columns">
                            <label>选择所在地区 </label>
                            <div id="div_region">
                                <select id="id_create_province" name="create_province">
                                    <option value="" data-code="">—— 省 ——</option>
                                </select>
                                <select id="id_create_location" name="create_location">
                                    <option value="" data-code="">—— 市 ——</option>
                                </select>
                                <!--<select id="id_create_district" name="create_district">
                                    <option value="" data-code="">—— 市 ——</option>
                                </select>-->
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="small-12 medium-8 columns">
                            <label>个人描述
                                <textarea cols="40" id="id_description" name="intro" rows="10">{$card['intro']}</textarea>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="small-12 columns publish">
                        <a class="button btn btn-publish" href="javascript:void(0)" id="ajaxSave"> 保存并发布</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {template 'footerbar'}

</div>

<script src="{AMOUSE_HUFEN_RES}js/jquery.f5d547e5b88b.js?v2"></script>
<script src="{AMOUSE_HUFEN_RES}js/jquery.cookie.js?v2"></script>
<script src="{AMOUSE_HUFEN_RES}js/foundation.min.dea49fb77ce9.js"></script>
<script src="{AMOUSE_HUFEN_RES}js/main.04f12e56ea6a.js"></script>
<script>
    $(document).foundation();
    alert_ok = null;
    function new_alert(msg, cb) {
        alert_ok = cb;
        $('#tip .text').html(msg);
        $('#tip').show();
    }
    old_alert = window.alert;
    window.alert = new_alert;
    $('#alert_ok').click(function () {
        if (alert_ok) {
            alert_ok();
        }
        $('#tip').hide();
    });
</script>

<script src="{AMOUSE_HUFEN_RES}js/distpicker.data.min.a8276dac4366.js"></script>
<script src="{AMOUSE_HUFEN_RES}js/distpicker.min.ad9fc6eb4bb4.js"></script>
<script>

    $('#div_region').distpicker({
        province: "{$card['location_p']}",
        city:"{$card['location_c']}",
        autoSelect: false,
    });

    var localIds =  [];
    $('.face').click(function () {
        wx.chooseImage({
            count: 1,
            sizeType: ['compressed'], // 可以指定是原图还是压缩图，默认二者都有
            success: function (res) {
                localIds = res.localIds;
                onImageDone();
            }
        });
    });

    function onImageDone(){
        if (localIds.length == 0) {
            alert('请先使用 chooseImage 接口选择图片');
            return;
        }
        for(var k in localIds){
            var localId = localIds[k];
            wx.uploadImage({
                localId: localId, // 需要上传的图片的本地ID，由chooseImage接口获得
                isShowProgressTips: 1, // 默认为1，显示进度提示
                success: function (res) {
                    $.ajax({
                        type : "POST",
                        url : "{php echo $this->createMobileUrl('imgupload')}&mediaId="+res.serverId,
                        data : {serverId:res.serverId},
                        dataType : "json",
                        contentType: "application/x-www-form-urlencoded; charset=utf-8",
                        success : function(data){
                            if(data && data.code == 0){
                                $('#imghead').attr('src',data.picid);
                                $('#qrcode').attr('value',data.picid);
                            }else if(d.code==202){
                                new_alert("请上传正确的微信二维码!", function () {});
                                $('#qrcode').attr('value','');
                                $('#imghead').attr('src','');
                            }else{
                                new_alert("保存失败，请重新!", function () {});
                            }
                        },
                        error : function(data){
                            alert("抱歉,服务器繁忙");
                        }
                    });
                }
            });
        }
    }

    $('#ajaxSave').click(function(){
        if($('#id_weixin_id').val() == ''){
            alert('请设置个人微信号。');
            return false;
        }
        if($('#id_category').val() == ''){
            alert('请设置分类。');
            return false;
        }
        if($('#id_create_province').val() == ''){
            alert('请设置省份。');
            return false;
        }
        if($('#id_create_location').val() == ''){
            alert('请设置城市。');
            return false;
        }
        if($('#id_description').val().length > 500){
            alert('描述不能超过500字。');
            return false;
        }
        if($('#qrcode').val() == ''){
            alert('请必须上传个人微信二维码。');
            return false;
        }
        var $btn = $("#ajaxSave");
        var $form = $("#personForm");
        if($btn.hasClass("disabled")) return;

        var url = "{php echo $this->createMobileUrl('ajaxPerson')}";

        var submitData = {
            wechatno: $("input[name='wechatno']", $form).val(),
            qrcode: $("input[name='qrcode']", $form).val(),
            pcateid: $('#id_category').val() ,
            fansid:$('#fansid').val() ,openid:$('#openid').val() ,
            location_p: $('#id_create_province').val() ,
            location_c: $('#id_create_location').val(),
            intro: $("textarea[name='intro']", $form).val()
        };

        $.post(url, submitData, function (res) {
            if(res && res.code==200){
                new_alert('你的信息提交成功。', function () {
                    window.location = "{php echo $this->createMobileUrl('poster',array('pk'=>pencode($fans['id'])),true)}";
                });
            }else if(res.code==201){
                new_alert(res.msg, function () {
                    window.location ="{php echo $this->createMobileUrl('board',array('type'=>1),true)}";
                });
            }else if(res.code==0){
                $btn.removeClass("disabled");
                new_alert(res.msg);
            }else if(res.code==-2||res.code ==-3){
                $btn.removeClass("disabled");
                new_alert("发送红包出现问题，赶紧联系管理员吧！");
            }

        }, 'json');
        return false;
    });
</script>

{if $set && $set['admin_tpl']==0}
<script>
    $(function(){
        var $free_vip =$.cookie('free_vip_hufen');
        if($free_vip!='0') {
            new_alert('您还不是脉客或者已到期！分享指定文章到朋友圈即可上传二维码，添加好友即可获得{$credittxt}', function(){
                window.location = "{php echo $this->createMobileUrl('poster',array('pk'=>pencode($fans['id'])),true)}";
            });
            return false;
        }
    });
</script>
{/if}
{template 'cnzz'}
</body>
</html>